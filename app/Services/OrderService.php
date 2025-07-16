<?php

namespace App\Services;

use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Transaction;
use App\Models\Product;
use App\Models\Warehouse;
use App\Services\SupplieService;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use App\Mail\LowStockAlert;
use App\Mail\ClientShippingNotification;


class OrderService
{
    protected SupplieService $supplySvc;

    public function __construct(SupplieService $supplySvc)
    {
        $this->supplySvc = $supplySvc;
    }

    /**
     * Place a new order: persist order + items + intent‐to‐ship transactions,
     * then either start shipping immediately (if stock) or mark awaiting_stock.
     *
     * @param  array  $data  [
     *     'customer_id'     => (int),
     *     'customer_type'   => 'person'|'company',
     *     'products'        => [
     *         ['id' => (int), 'quantity' => (int)],
     *         …
     *     ],
     *     'shipping_company'=> (string|null),
     *     'shipping_cost'   => (float|null),
     * ]
     * @return Order
     */
    public function placeOrder(array $data): Order
    {
        return DB::transaction(function () use ($data) {
            // 1) create the order record
            $order = Order::create([
                'reference_number' => 'ORD-' . strtoupper(Str::uuid()),
                'date'             => now(),
                // 'customer_type'    => $data['customer_type'],
                'customer_id'      => $data['customer_id'],
                'created_by'       => auth()->id(),
                'status'           => 'pending',
            ]);

            // 2) create each OrderItem + an "order" transaction (direction: out)
            foreach ($data['products'] as $prod) {
                $order->items()->create([
                    'product_id'  => $prod['id'],
                    'quantity'    => $prod['quantity'],
                    'unit_price'  => Product::findOrFail($prod['id'])->price_incl_vat,
                    'total_price' => $prod['quantity'] * Product::findOrFail($prod['id'])->price_incl_vat,
                ]);

                // Transaction::create([
                //     'transaction_type' => 'invoice',
                //     'reference_id'     => $order->id,
                //     'product_id'       => $prod['id'],
                //     'quantity'         => $prod['quantity'],
                //     'unit_price'       => Product::findOrFail($prod['id'])->price_incl_vat,
                //     'total_price'      => $prod['quantity'] * Product::findOrFail($prod['id'])->price_incl_vat,
                //     'direction'        => 'out',
                //     'created_by'       => auth()->id(),
                // ]);
            }

            // 3) decide shipping immediately vs awaiting_stock
            $warehouse = Warehouse::first();
            $hasStock  = $this->isStockAvailable($order, $warehouse);

            $shippingCompany = $data['shipping_company'] ?? 'FedEx';
            $shippingCost    = $data['shipping_cost'] ?? null;
            if (!is_numeric($shippingCost) || $shippingCost < 0) {
                $shippingCost = match ($shippingCompany) {
                    'DHL' => 12.5,
                    'UPS' => 15,
                    default => 10,
                };
            }

            if ($hasStock) {
                $order->status = 'pending';
                $order->save();

                // Generate a tracking ID
                $trackingId = strtoupper(Str::uuid());

                // start shipping now
                $order->shipping()->create([
                    'order_id' => $order->id,
                    'shipping_company' => $shippingCompany,
                    'shipping_cost'    => $shippingCost,
                    'tracking_id'      => $trackingId,
                    'status'           => 'on_delivery',    
                ]);
                //deduct stockun
                $this->startShipping($order, $warehouse);

                // notify client
                // $this->notifyClientShipping($order);
            } else {
                $order->status = 'awaiting_stock';
                $order->save();

                $order->shipping()->create([
                    'order_id'         => $order->id,
                    'shipping_company' => $shippingCompany,
                    'shipping_cost'    => $shippingCost,
                    'tracking_id'      => null,
                    'status'           => 'awaiting_stock',
                  ]);

                $outOfStock = [];
                foreach($order->items as $item){
                    $avail = $warehouse->products()
                              ->where('product_id',$item->product_id)
                              ->first()?->pivot->quantity ?? 0;
                    if($avail < $item->quantity){
                        $outOfStock[$item->product_id] = $item->quantity - $avail;
                    }
                }

                // Fire supply request for missing quantities
                $supplyItems = array_map(
                    function($pid, $qty) {
                        return [
                            'product_id' => $pid,
                            'quantity'   => $qty,
                            'unit_price' => Product::findOrFail($pid)->price_incl_vat,
                        ];
                    },
                    array_keys($outOfStock),
                    $outOfStock
                );

                $this->supplySvc->createSupplyRequest([
                    'supplier_type' => 'company',
                    'supplier_id'   => 1,
                    'tariff_fee'    => 0,
                    'import_cost'   => 0,
                    'order_id'      => $order->id,
                    'items'         => $supplyItems,
                ]);

                // create awaiting_stock record
                // $order->shipping()->create([
                //     'shipping_company' => $shippingCompany,
                //     'shipping_cost'    => $shippingCost,
                //     'tracking_id'      => null,
                //     'status'           => 'awaiting_stock',
                // ]);
                // you can still notify vendor here via your SupplieService
            }

            return $order;
        });
    }

    /**
     * Check if every item in the order can be fulfilled from the given warehouse.
     */
    protected function isStockAvailable(Order $order, Warehouse $warehouse): bool
    {
        foreach ($order->items as $item) {
            $pivot = $warehouse
                ->products()
                ->where('product_id', $item->product_id)
                ->first()
                ?->pivot
                ->quantity
                ?? 0;

            if ($pivot < $item->quantity) {
                return false;
            }
        }
        return true;
    }

    /**
     * Deduct stock from both global & warehouse, once shipping starts.
     */
    public function startShipping(Order $order, Warehouse $warehouse): void
    {
        DB::transaction(function () use ($order, $warehouse) {
            $lowStockProducts = [];

            foreach ($order->items as $item) {
                $product = Product::findOrFail($item->product_id);
                // global stock
                $product->decrement('stock_quantity', $item->quantity);
                // warehouse pivot
                $warehouse->products()->updateExistingPivot(
                    $product->id,
                    ['quantity' => DB::raw("quantity - {$item->quantity}")]
                );

                // if now at or below threshold, queue for alert
                if ($product->stock_quantity <= $product->low_stock_threshold) {
                    $lowStockProducts[] = $product;
                }
            }

            // Only send if there are products to alert about
            if (! empty($lowStockProducts)) {
                // $this->sendReplenishmentNotification($lowStockProducts);
            }
        });
    }

    protected function sendReplenishmentNotification(array $products): void
    {
        if (empty($products)) {
            Log::info('OrderService: No low-stock products to alert.');
            return;
        }

        Log::info('OrderService: Sending low-stock alert for ' . count($products) . ' products.');
        Mail::to('dion.gashi2@student.uni-pr.edu')
            ->send(new LowStockAlert($products));
    }

    protected function notifyClientShipping(Order $order):void 
    {
        $email = $order->client->email ?? null;

        if($email){
            Log::info("OrderService: Sending shipping notification to client email: {$email} for Order ID: {$order->id}.");
            Mail::to($email)->send(new ClientShippingNotification($order));
        } else {
            Log::warning("OrderService: No email found for client of Order ID: {$order->id}. Shipping notification skipped.");
        }
    }

    // public function attemptShippingPendingOrdersForProduct(int $productId):void 
    // {
    //     $warehouse = Warehouse::first();
    //     if(!$warehouse){
    //         Log::error("OrderService: no warehouse for restock shipping.");
    //         return;
    //     }

    //     // Pull all orders that are awaiting_stock and include this product
    //     $orders = Order::where('status','awaiting_stock')
    //          ->whereHas('items',fn($q) => $q->where('product_id',$productId))
    //          ->get();
        
    //         foreach($orders as $order){
    //             //re-check all items
                
    //         }
    // }


    public function shipOrder(int $orderId): void
    {
        $order = Order::with(['items','client','shipping'])->findOrFail($orderId);
        $warehouse = Warehouse::first();

        // guard: only ship orders that are still on awaiting_stock
        if($order->status !== 'awaiting_stock'){
            return;
        }

        
    //  check all items again
        $ok = $order->items->every(fn($i)=>
          $warehouse->products()
            ->where('product_id',$i->product_id)
            ->first()?->pivot->quantity >= $i->quantity
        );
        if (! $ok) {
         return;
        }

        DB::transaction(function() use ($order,$warehouse){
            $order->update(['status' => 'pending']);

            $tracking = strtoupper(Str::uuid());
            // $order->shipping()->create([
            //     'order_id'         => $order->id,
            //     'shipping_company' => $order->shipping->shipping_company,
            //     'shipping_cost'    => $order->shipping->shipping_cost,
            //     'tracking_id'      => $tracking,
            //     'status'           => 'on_delivery',
            // ]);
            $order->shipping()->update([
                'tracking_id' => $tracking,
                'status'      => 'on_delivery',
              ]);
          
        });

        // deduct stock globally & per-warehouse
        foreach ($order->items as $item) {
            $p = Product::findOrFail($item->product_id);
            $p->decrement('stock_quantity',$item->quantity);
            $warehouse
              ->products()
              ->updateExistingPivot(
                 $p->id,
                 ['quantity'=>DB::raw("quantity - {$item->quantity}")]
              );
        }

        // notify the client
        $email = $order->client->email;
        if ($email) {
            // Mail::to($email)
            //     ->send(new ClientShippingNotification($order));
        }

    }

    // public function createInvoiceFromOrder(Order $order): Invoice
    // {
    //     if ($order->status !== 'delivered') {
    //         throw new \Exception('Order must be delivered before invoicing.');
    //     }
    //     if ($order->invoice) {
    //         throw new \Exception('Invoice already exists for this order.');
    //     }

    //     $invoice = Invoice::create([
    //         'reference_number' => 'INV-' . strtoupper(Str::uuid()),
    //         'date'            => now(),
    //         'customer_type'   => $order->customer_type,
    //         'customer_id'     => $order->customer_id,
    //         'created_by'      => $order->created_by,
    //         'order_id'        => $order->id,
    //     ]);

    //     foreach ($order->items as $item) {
    //         InvoiceItem::create([
    //             'invoice_id'  => $invoice->id,
    //             'product_id'  => $item->product_id,
    //             'quantity'    => $item->quantity,
    //             'unit_price'  => $item->unit_price,
    //             'total_price' => $item->total_price,
    //         ]);
    //     }

    //     return $invoice;
    // }
}