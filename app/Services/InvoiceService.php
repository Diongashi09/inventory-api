<?php

namespace App\Services;

use App\Models\{Client, Invoice, InvoiceItem, Product, Transaction, Warehouse, InvoiceLog};
use App\Mail\LowStockAlert;
use App\Mail\ClientShippingNotification;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class InvoiceService {
    public function createInvoice(array $data): Invoice
    {
        return DB::transaction(function () use ($data) {
            $client = Client::findOrFail($data['customer_id']);
            $products = $data['products'];
            $warehouse = Warehouse::first(); // For now, main warehouse

            $isStockAvailable = true;
            $productsMissing = [];

            // Check stock availability
            foreach ($products as $productData) {
                $product = Product::findOrFail($productData['id']);
                $available = $warehouse->products()
                ->where('product_id', $product->id)
                ->first()?->pivot->quantity ?? 0;

                if ($available < $productData['quantity']) {
                    $isStockAvailable = false;
                    $productsMissing[] = $product;
                }
            }

            // Create the invoice
            $invoice = Invoice::create([
                'reference_number' => 'INV-' . strtoupper(uniqid()),
                'date'            => now(),
                'customer_type'   => $client->client_type,
                'customer_id'     => $client->id,
                'created_by'      => auth()->id(),
                'status' => $isStockAvailable ? 'on_delivery' : 'pending',
            ]);

            foreach ($products as $productData) {
                $product = Product::findOrFail($productData['id']);
                $quantity = $productData['quantity'];
                $unitPrice = $product->price_incl_vat;
                $totalPrice = $quantity * $unitPrice;

                // Create invoice item
                InvoiceItem::create([
                    'invoice_id' => $invoice->id,
                    'product_id' => $product->id,
                    'quantity'   => $quantity,
                    'unit_price' => $unitPrice,
                    'total_price' => $totalPrice,
                ]);

                // Create transaction (direction: out)
                Transaction::create([
                    'transaction_type' => 'invoice',
                    'reference_id'     => $invoice->id,
                    'product_id'       => $product->id,
                    'quantity'         => $quantity,
                    'unit_price'       => $unitPrice,
                    'total_price'      => $totalPrice,
                    'direction'        => 'out',
                    'created_by'       => auth()->id(),
                ]);
            }

            if ($isStockAvailable) {
                // Deduct stock & notify if below zero
                foreach ($products as $productData) {
                    $product = Product::findOrFail($productData['id']);
                    $quantity = $productData['quantity'];

                    $currentQty = $warehouse->products()
                    ->where('product_id', $product->id)
                    ->first()?->pivot->quantity ?? 0;

                    $warehouse->products()->updateExistingPivot($product->id, [
                        'quantity' => $currentQty - $quantity,
                    ]);

                    if (($currentQty - $quantity) <= 0) {
                        $this->sendReplenishmentNotification([$product]);
                    }
                }

                // Start shipping process
                if ($isStockAvailable) {
                    $this->startShipping(
                        $invoice,
                        $data['shipping_company'] ?? 'FedEx',
                        $data['shipping_cost'] ?? 0
                    );
                }
            
            } else {
                // Create placeholder shipping record so cost/company can be reused later (me na qit shipping_cost masnej te qatij invoice)
                $invoice->shipping()->create([
                    'shipping_company' => $data['shipping_company'] ?? 'FedEx',
                    'shipping_cost' => $data['shipping_cost'] ?? 0,
                    'tracking_id' => null,
                    'status' => 'awaiting_stock',
                ]);
                // Stock not available: send low stock alert
                $this->sendReplenishmentNotification($productsMissing);
            }

            return $invoice;
        });
    }


    protected function sendReplenishmentNotification(array $products)
    {
       Mail::to('dion.gashi2@student.uni-pr.edu')->send(new LowStockAlert($products));
    }

    public function startShipping(Invoice $invoice, ?string $shippingCompany = 'FedEx', ?float $shippingCost = 0): void
    {
        $trackingId = strtoupper(Str::uuid());

        $shipping = $invoice->shipping;

        if (!$shipping) {
            // Create shipping record if not exists
            $shipping = $invoice->shipping()->create([
                'shipping_company' => $shippingCompany,
                'shipping_cost' => $shippingCost, 
                'tracking_id' => $trackingId,
                'status' => 'on_delivery',
            ]);
        } else {
            // $shipping->update([
            //     'tracking_id' => $trackingId,
            //     'status' => 'on_delivery',
            // ]);
            $shipping->update([
                'shipping_company' => $shippingCompany,
                'shipping_cost' => $shippingCost,
                'tracking_id' => $trackingId,
                'status' => 'on_delivery',
            ]);
            
        }

        $invoice->update(['status' => 'on_delivery']);

        // Notify client
        $invoice->refresh();
        $this->notifyClientShipping($invoice);
    }


    //case when we don't have stock, but once it's restocked and invoice was pending we ship it automatically
    //kur bohmi restock. 
    public function attemptShippingPendingInvoicesForProduct(int $productId): void
{
    $pendingInvoices = Invoice::where('status', 'pending')
        ->whereHas('items', function ($query) use ($productId) {
            $query->where('product_id', $productId);
        })->get();

    foreach ($pendingInvoices as $invoice) {
        $canShip = true;

        foreach ($invoice->items as $item) {
            $available = Warehouse::first()
                ->products()
                ->where('product_id', $item->product_id)
                ->first()?->pivot->quantity ?? 0;

            if ($available < $item->quantity) {
                $canShip = false;
                break;
            }
        }

        if ($canShip) {
            // $this->startShipping($invoice);
            $this->startShipping(
                $invoice,
                $invoice->shipping?->shipping_company ?? 'FedEx',
                $invoice->shipping?->shipping_cost ?? 0
            );

            InvoiceLog::create([
                'invoice_id' => $invoice->id,
                'message' => 'Shipping started automatically after stock replenishment.'
            ]);
        }
    }
}



    protected function notifyClientShipping(Invoice $invoice): void
    {
        $email = $invoice->client->email ?? null;

        if ($email) {
            Mail::to($email)->send(new ClientShippingNotification($invoice));
        }
    }

}