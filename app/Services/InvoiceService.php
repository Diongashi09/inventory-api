<?php

namespace App\Services;

use App\Models\{Client, Invoice, InvoiceItem, Product, Transaction, Warehouse, InvoiceLog};
use App\Mail\LowStockAlert;
use App\Mail\ClientShippingNotification;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Facades\Log; // Import the Log facade

class InvoiceService {
    public function createInvoice(array $data): Invoice 
    {
        return DB::transaction(function() use ($data){
            //1. Create the Invoice record
            $invoice = Invoice::create([
                'reference_number' => 'INV-' . strtoupper(Str::uuid()),
                'date'  => now(),
                'customer_type' => $data['customer_type'],
                'customer_id' => $data['customer_id'],
                'created_by' => auth()->id(),
            ]);

            //2) create each item + a matching "invoice" transaction
            foreach($data['products'] as $prod){
                $quantity  = $prod['quantity'];
                $unitPrice = \App\Models\Product::findOrFail($prod['id'])->price_incl_vat;
                $total     = $quantity * $unitPrice;

                InvoiceItem::create([
                    'invoice_id'  => $invoice->id,
                    'product_id'  => $prod['id'],
                    'quantity'    => $quantity,
                    'unit_price'  => $unitPrice,
                    'total_price' => $total,
                ]);

                Transaction::create([
                    'transaction_type' => 'invoice',
                    'reference_id'     => $invoice->id,
                    'product_id'       => $prod['id'],
                    'quantity'         => $quantity,
                    'unit_price'       => $unitPrice,
                    'total_price'      => $total,
                    'direction'        => 'out',
                    'created_by'       => auth()->id(),
                ]);
            }

            return $invoice;
        });
    }


    // public function createInvoice(array $data): Invoice
    // {
    //     Log::info('InvoiceService: createInvoice method started.');

    //     return DB::transaction(function () use ($data) {
    //         $client = Client::findOrFail($data['customer_id']);
    //         $productsData = $data['products'];
    //         $warehouse = Warehouse::first(); // Assuming this is the main warehouse for stock checks

    //         // Check if a warehouse exists before proceeding with stock checks
    //         if (!$warehouse) {
    //             Log::error('InvoiceService: No default warehouse found. Cannot process invoice stock checks.');
    //             // Depending on your application's requirements, you might throw an exception here
    //             // or have a more sophisticated way to handle missing warehouses.
    //             throw new \Exception('No default warehouse configured for stock management.');
    //         }

    //         $isStockAvailable = true;
    //         $productsMissing = []; // Collect products that are missing or low for potential alerts

    //         Log::info('InvoiceService: Starting stock availability check for products.');

    //         // Check stock availability for all products in the order
    //         foreach ($productsData as $productData) {
    //             $product = Product::find($productData['id']); // Use find to avoid immediate exception if product doesn't exist
    //             if (!$product) {
    //                 Log::error("InvoiceService: Product ID {$productData['id']} not found during stock check for new invoice. Skipping.");
    //                 // You might want to throw an exception here or mark the invoice as invalid
    //                 $isStockAvailable = false; // Mark as not available if a product is missing
    //                 continue; // Skip to next product
    //             }

    //             $availableInWarehouse = $warehouse->products()
    //                                 ->where('product_id', $product->id)
    //                                 ->first()?->pivot->quantity ?? 0;

    //             Log::info("InvoiceService: Product ID {$product->id} (Name: {$product->name}), Requested Quantity: {$productData['quantity']}, Available in Warehouse: {$availableInWarehouse}");

    //             if ($availableInWarehouse < $productData['quantity']) {
    //                 $isStockAvailable = false;
    //                 $productsMissing[] = $product;
    //                 Log::warning("InvoiceService: Insufficient stock for Product ID {$product->id}. Requested: {$productData['quantity']}, Available: {$availableInWarehouse}. Invoice will be pending.");
    //             }
    //         }

    //         Log::info("InvoiceService: Final decision on stock availability for invoice: " . ($isStockAvailable ? 'TRUE (will be on_delivery)' : 'FALSE (will be pending)'));

    //         $customerTypeForInvoice = ($client->client_type === 'individual') ? 'person' : $client->client_type;

    //         // Determine initial invoice status
    //         $invoiceStatus = $isStockAvailable ? 'on_delivery' : 'pending';

    //         // Create the invoice record
    //         $invoice = Invoice::create([
    //             'reference_number' => 'INV-' . strtoupper(uniqid()),
    //             'date'             => now(),
    //             'customer_type'    => $customerTypeForInvoice,
    //             'customer_id'      => $client->id,
    //             'created_by'       => auth()->id(),
    //             'status'           => $invoiceStatus,
    //         ]);
    //         Log::info("InvoiceService: Invoice ID {$invoice->id} created with status: {$invoice->status}.");


    //         // Create invoice items and corresponding transactions (always created when an invoice is made)
    //         foreach ($productsData as $productData) {
    //             $product = Product::findOrFail($productData['id']); // Find or fail here since validation passed
    //             $quantity = $productData['quantity'];
    //             $unitPrice = $product->price_incl_vat;
    //             $totalPrice = $quantity * $unitPrice;

    //             // Create invoice item (details of what was ordered)
    //             InvoiceItem::create([
    //                 'invoice_id' => $invoice->id,
    //                 'product_id' => $product->id,
    //                 'quantity'   => $quantity,
    //                 'unit_price' => $unitPrice,
    //                 'total_price' => $totalPrice,
    //             ]);
    //             Log::info("InvoiceService: Invoice Item created for Product ID {$product->id}, Quantity: {$quantity}.");


    //             // Create transaction (record of stock movement, even if pending)
    //             // Direction is 'out' as it's an order, but physical movement depends on 'on_delivery'
    //             Transaction::create([
    //                 'transaction_type' => 'invoice',
    //                 'reference_id'     => $invoice->id,
    //                 'product_id'       => $product->id,
    //                 'quantity'         => $quantity,
    //                 'unit_price'       => $unitPrice,
    //                 'total_price'      => $totalPrice,
    //                 'direction'        => 'out', // Marks intent to move out
    //                 'created_by'       => auth()->id(),
    //             ]);
    //             Log::info("InvoiceService: Transaction created for Product ID {$product->id}, Direction: out.");
    //         }

    //         // Determine shipping details from request or defaults
    //         $shippingCompany = $data['shipping_company'] ?? 'FedEx';
    //         $shippingCost = $data['shipping_cost'] ?? null;

    //         if ($shippingCost === null || !is_numeric($shippingCost) || $shippingCost < 0) {
    //             $shippingCost = match ($shippingCompany) {
    //                 'DHL' => 12.5,
    //                 'UPS' => 15,
    //                 default => 10,
    //             };
    //             Log::info("InvoiceService: Calculated shipping cost for {$shippingCompany}: {$shippingCost}.");
    //         } else {
    //             Log::info("InvoiceService: Using provided shipping cost: {$shippingCost} for {$shippingCompany}.");
    //         }


    //         // Handle immediate shipping or pending state based on stock availability
    //         if ($isStockAvailable) {
    //             Log::info("InvoiceService: Stock is available. Calling startShipping for Invoice ID: {$invoice->id}.");
    //             // If stock is available, proceed to start shipping immediately.
    //             // Stock deduction and final status update happen inside startShipping.
    //             $this->startShipping(
    //                 $invoice,
    //                 $shippingCompany,
    //                 $shippingCost
    //             );
    //         } else {
    //             Log::info("InvoiceService: Stock is NOT available. Creating awaiting_stock shipping record for Invoice ID: {$invoice->id}.");
    //             // If stock is NOT available, create a pending shipping record.
    //             // Stock will be deducted later when shipping actually starts.
    //             $invoice->shipping()->create([
    //                 'shipping_company' => $shippingCompany,
    //                 'shipping_cost' => $shippingCost,
    //                 'tracking_id' => null, // No tracking ID yet as it's not shipped
    //                 'status' => 'awaiting_stock', // Shipping is awaiting stock
    //             ]);
    //             // Send an alert for products that caused the invoice to be pending
    //             $this->sendReplenishmentNotification($productsMissing);
    //         }

    //         Log::info('InvoiceService: createInvoice method finished.');
    //         return $invoice;
    //     });
    // }

    /**
     * Sends a notification for products with low stock.
     *
     * @param array $products
     * @return void
     */
    // protected function sendReplenishmentNotification(array $products): void
    // {
    //     // Ensure there are products to alert about to avoid sending empty emails
    //     if (!empty($products)) {
    //         Log::info('InvoiceService: Sending low stock alert for ' . count($products) . ' products.');
    //         Mail::to('dion.gashi2@student.uni-pr.edu')->send(new LowStockAlert($products));
    //     } else {
    //         Log::info('InvoiceService: No products to alert for low stock.');
    //     }
    // }

    // /**
    //  * Initiates the shipping process for an invoice, deducting stock.
    //  * This method is called when an invoice transitions to 'on_delivery'.
    //  *
    //  * @param Invoice $invoice
    //  * @param string|null $shippingCompany
    //  * @param float|null $shippingCost
    //  * @return void
    //  */
    // public function startShipping(Invoice $invoice, ?string $shippingCompany = 'FedEx', ?float $shippingCost = 0): void
    // {
    //     Log::info("InvoiceService: startShipping called for Invoice ID: {$invoice->id}. Current invoice status: {$invoice->status}.");

    //     // Use a transaction to ensure atomicity for stock deduction and status updates
    //     DB::transaction(function () use ($invoice, $shippingCompany, $shippingCost) {
    //         $trackingId = strtoupper(Str::uuid());
    //         $warehouse = Warehouse::first(); // Assuming this is the main warehouse for deductions

    //         if (!$warehouse) {
    //             Log::error("InvoiceService: No default warehouse found when trying to ship Invoice ID: {$invoice->id}. Stock deduction skipped.");
    //             throw new \Exception('No default warehouse configured for stock deduction.'); // Halt if no warehouse
    //         }

    //         $productsToAlert = []; // To collect products that fall below threshold after deduction

    //         Log::info("InvoiceService: Starting stock deduction for items in Invoice ID: {$invoice->id}.");
    //         // Deduct stock for each item in the invoice
    //         foreach ($invoice->items as $item) {
    //             $product = Product::find($item->product_id); // Use find to gracefully handle missing products
    //             if (!$product) {
    //                 Log::error("InvoiceService: Product ID {$item->product_id} not found for Invoice ID: {$invoice->id}. Skipping stock deduction for this item.");
    //                 continue; // Skip to next item
    //             }
    //             $quantity = $item->quantity;

    //             // --- START OF THE FIX FOR UNDEFINED VARIABLE ---
    //             // Get current stock levels *before* potential deduction
    //             $currentGlobalStock = $product->stock_quantity;
    //             $currentWarehouseQuantity = $warehouse->products()->where('product_id', $product->id)->first()?->pivot->quantity ?? 0;

    //             Log::info("InvoiceService: Pre-deduction check for Product ID {$product->id} (Name: {$product->name}). Requested: {$quantity}, Global Available: {$currentGlobalStock}, Warehouse Available: {$currentWarehouseQuantity}.");

    //             // Important: This check serves as a final safeguard to prevent negative stock.
    //             // The primary check is in createInvoice, but this ensures robustness if startShipping is called directly.
    //             if ($currentGlobalStock < $quantity || $currentWarehouseQuantity < $quantity) {
    //                 Log::critical("InvoiceService: Attempting to ship Invoice ID {$invoice->id} with insufficient stock for Product ID {$product->id}. Requested: {$quantity}, Global Available: {$currentGlobalStock}, Warehouse Available: {$currentWarehouseQuantity}. Stock will NOT be decremented to prevent negative values.");
    //                 // You might want to throw a specific exception here that can be caught by the controller
    //                 throw new \Exception("Insufficient stock to ship product ID {$product->id} for invoice {$invoice->id}. Please check inventory.");
    //             }
    //             // --- END OF THE FIX ---


    //             // Log before decrement
    //             Log::info("InvoiceService: Deducting stock for Product ID {$product->id} (Name: {$product->name}): {$quantity} units. Current Global Stock (pre-deduction): {$currentGlobalStock}, Current Warehouse Stock (pre-deduction): {$currentWarehouseQuantity}.");

    //             // Decrement the global products.stock_quantity
    //             $product->decrement('stock_quantity', $quantity);
    //             $product->refresh(); // Crucial to get the new quantity immediately

    //             Log::info("InvoiceService: Global Stock for Product ID {$product->id} after decrement: {$product->stock_quantity}.");

    //             // Decrement the quantity in the warehouse_product pivot table
    //             $warehouse->products()->updateExistingPivot($product->id, [
    //                 'quantity' => DB::raw('quantity - ' . $quantity),
    //             ]);

    //             // Re-fetch pivot data to confirm the update
    //             $updatedPivotData = $warehouse->products()->where('product_id', $product->id)->first()?->pivot;
    //             $newWarehouseQuantity = $updatedPivotData ? $updatedPivotData->quantity : 0;
    //             Log::info("InvoiceService: Warehouse stock for Product ID {$product->id} after pivot update: {$newWarehouseQuantity}.");


    //             // Check if the NEW GLOBAL stock quantity is at or below the low_stock_threshold
    //             if ($product->stock_quantity <= $product->low_stock_threshold) {
    //                 $productsToAlert[] = $product;
    //                 Log::warning("InvoiceService: Product ID {$product->id} is now at or below low stock threshold ({$product->low_stock_threshold}). Current global stock: {$product->stock_quantity}.");
    //             }
    //         }

    //         // Send low stock alert after all deductions for the current shipment
    //         $this->sendReplenishmentNotification($productsToAlert);

    //         // Update or create the shipping record for the invoice
    //         $shipping = $invoice->shipping;

    //         if (!$shipping) {
    //             // This case handles a direct call to startShipping for an invoice that didn't have a shipping record yet.
    //             $shipping = $invoice->shipping()->create([
    //                 'shipping_company' => $shippingCompany,
    //                 'shipping_cost' => $shippingCost,
    //                 'tracking_id' => $trackingId,
    //                 'status' => 'on_delivery',
    //             ]);
    //             Log::info("InvoiceService: Created new shipping record for Invoice ID {$invoice->id}. Tracking ID: {$trackingId}. Status: on_delivery.");
    //         } else {
    //             // This case handles updating an existing 'awaiting_stock' shipping record
    //             // or updating details of an already existing shipping record.
    //             $shipping->update([
    //                 'shipping_company' => $shippingCompany,
    //                 'shipping_cost' => $shippingCost,
    //                 'tracking_id' => $trackingId,
    //                 'status' => 'on_delivery',
    //             ]);
    //             Log::info("InvoiceService: Updated existing shipping record for Invoice ID {$invoice->id}. Tracking ID: {$trackingId}. Status: on_delivery.");
    //         }

    //         // Update the main invoice status to 'on_delivery'
    //         $invoice->update(['status' => 'on_delivery']);
    //         Log::info("InvoiceService: Invoice ID {$invoice->id} main status updated to 'on_delivery'.");

    //         // Notify the client about the shipment
    //         $invoice->refresh(); // Ensure the invoice has the latest data before sending notification
    //         $this->notifyClientShipping($invoice);
    //         Log::info("InvoiceService: Client shipping notification initiated for Invoice ID {$invoice->id}.");
    //     });
    //     Log::info("InvoiceService: startShipping method finished for Invoice ID: {$invoice->id}.");
    // }

    // /**
    //  * Attempts to ship pending invoices for a specific product after it has been restocked.
    //  *
    //  * @param int $productId The ID of the product that has been restocked.
    //  * @return void
    //  */
    // public function attemptShippingPendingInvoicesForProduct(int $productId): void
    // {
    //     Log::info("InvoiceService: attemptShippingPendingInvoicesForProduct called for Product ID: {$productId}.");

    //     $warehouse = Warehouse::first(); // Assuming this is the main warehouse where stock is replenished

    //     if (!$warehouse) {
    //         Log::error('InvoiceService: No default warehouse found. Cannot attempt shipping pending invoices.');
    //         return;
    //     }

    //     // Find invoices that are pending and include the restocked product
    //     $pendingInvoices = Invoice::where('status', 'pending')
    //         ->whereHas('items', function ($query) use ($productId) {
    //             $query->where('product_id', $productId);
    //         })->get();

    //     Log::info("InvoiceService: Found " . $pendingInvoices->count() . " pending invoices for Product ID {$productId}.");


    //     foreach ($pendingInvoices as $invoice) {
    //         $canShip = true; // Flag to determine if this invoice can now be shipped
    //         Log::info("InvoiceService: Checking Invoice ID {$invoice->id} for shipment eligibility.");

    //         // Re-check stock for ALL items in the pending invoice against the current warehouse stock
    //         foreach ($invoice->items as $item) {
    //             $product = Product::find($item->product_id); // Use find
    //             if (!$product) {
    //                 Log::error("InvoiceService: Product ID {$item->product_id} not found for pending Invoice ID: {$invoice->id}. Cannot ship this invoice.");
    //                 $canShip = false;
    //                 break;
    //             }

    //             $availableInWarehouse = $warehouse->products()
    //                                     ->where('product_id', $product->id)
    //                                     ->first()?->pivot->quantity ?? 0;

    //             Log::info("InvoiceService: Item Product ID {$product->id} (Name: {$product->name}) in Invoice {$invoice->id}. Requested: {$item->quantity}, Available: {$availableInWarehouse}.");


    //             // If any item in the invoice is still short, this invoice cannot ship yet
    //             if ($availableInWarehouse < $item->quantity) {
    //                 $canShip = false;
    //                 Log::info("InvoiceService: Invoice ID {$invoice->id} cannot ship yet. Insufficient stock for Product ID {$product->id}.");
    //                 break;
    //             }
    //         }

    //         // If all items for this pending invoice are now available
    //         if ($canShip) {
    //             Log::info("InvoiceService: Invoice ID {$invoice->id} is now eligible for shipment. Calling startShipping.");
    //             // Call startShipping, which will deduct stock and update statuses
    //             $this->startShipping(
    //                 $invoice,
    //                 $invoice->shipping?->shipping_company ?? 'FedEx',
    //                 $invoice->shipping?->shipping_cost ?? 0
    //             );

    //             // Log the automatic shipment
    //             InvoiceLog::create([
    //                 'invoice_id' => $invoice->id,
    //                 'message' => 'Shipping started automatically after stock replenishment.'
    //             ]);
    //             Log::info("InvoiceService: InvoiceLog created for Invoice ID {$invoice->id}: 'Shipping started automatically after stock replenishment.'");
    //         } else {
    //             Log::info("InvoiceService: Invoice ID {$invoice->id} remains pending. Not all stock available yet.");
    //         }
    //     }
    //     Log::info("InvoiceService: attemptShippingPendingInvoicesForProduct finished for Product ID: {$productId}.");
    // }

    // /**
    //  * Notifies the client that their invoice has been shipped.
    //  *
    //  * @param Invoice $invoice
    //  * @return void
    //  */
    // protected function notifyClientShipping(Invoice $invoice): void
    // {
    //     $email = $invoice->client->email ?? null;

    //     if ($email) {
    //         Log::info("InvoiceService: Sending shipping notification to client email: {$email} for Invoice ID: {$invoice->id}.");
    //         Mail::to($email)->send(new ClientShippingNotification($invoice));
    //     } else {
    //         Log::warning("InvoiceService: No email found for client of Invoice ID: {$invoice->id}. Shipping notification skipped.");
    //     }
    // }
}

///



// namespace App\Services;

// use App\Models\{Client, Invoice, InvoiceItem, Product, Transaction, Warehouse, InvoiceLog};
// use App\Mail\LowStockAlert;
// use App\Mail\ClientShippingNotification;
// use Illuminate\Support\Str;
// use Illuminate\Support\Facades\Mail;
// use Illuminate\Support\Facades\DB;
// use Illuminate\Validation\ValidationException;

// class InvoiceService {
//     public function createInvoice(array $data): Invoice
//     {
//         return DB::transaction(function () use ($data) {
//             $client = Client::findOrFail($data['customer_id']);
//             $productsData = $data['products'];
//             $warehouse = Warehouse::first(); // For now, main warehouse

//             $isStockAvailable = true;
//             $productsMissing = [];//Products that fall below threshold ose out of stock

//             // Check stock availability
//             foreach ($productsData as $productData) {
//                 $product = Product::findOrFail($productData['id']);
//                 $availableInWarehouse = $warehouse->products()
//                 ->where('product_id', $product->id)
//                 ->first()?->pivot->quantity ?? 0;

                
//                 if ($availableInWarehouse < $productData['quantity']) {
//                     $isStockAvailable = false;
//                     $productsMissing[] = $product;
//                 }
//             }

//             $customerTypeForInvoice = ($client->client_type === 'individual') ? 'person' : $client->client_type;

//             $invoiceStatus = $isStockAvailable ? 'on_delivery' : 'pending';

//             // Create the invoice
//             $invoice = Invoice::create([
//                 'reference_number' => 'INV-' . strtoupper(uniqid()),
//                 'date'            => now(),
//                 'customer_type'   => $customerTypeForInvoice,
//                 'customer_id'     => $client->id,
//                 'created_by'      => auth()->id(),
//                 'status' => $invoiceStatus,
//             ]);

//             foreach ($productsData as $productData) {
//                 $product = Product::findOrFail($productData['id']);
//                 $quantity = $productData['quantity'];
//                 $unitPrice = $product->price_incl_vat;
//                 $totalPrice = $quantity * $unitPrice;

//                 // Create invoice item
//                 InvoiceItem::create([
//                     'invoice_id' => $invoice->id,
//                     'product_id' => $product->id,
//                     'quantity'   => $quantity,
//                     'unit_price' => $unitPrice,
//                     'total_price' => $totalPrice,
//                 ]);

//                 // Create transaction (direction: out)
//                 Transaction::create([
//                     'transaction_type' => 'invoice',
//                     'reference_id'     => $invoice->id,
//                     'product_id'       => $product->id,
//                     'quantity'         => $quantity,
//                     'unit_price'       => $unitPrice,
//                     'total_price'      => $totalPrice,
//                     'direction'        => 'out',
//                     'created_by'       => auth()->id(),
//                 ]);
//             }

//             if ($isStockAvailable) {
//                 $productsToAlert = [];
//                 // Deduct stock & notify if below zero
//                 foreach ($products as $productData) {
//                     $product = Product::findOrFail($productData['id']);
//                     $quantity = $productData['quantity'];



//                     // $currentQty = $warehouse->products()
//                     // ->where('product_id', $product->id)
//                     // ->first()?->pivot->quantity ?? 0;

//                     // //new lines
//                     // $pivot = $warehouse->products()
//                     // ->where('product_id', $product->id)
//                     // ->first();
//                     // $currentQty = $pivot?->pivot->quantity ?? 0;

//                     //end of new lines

//                     //decrement the warehouse pivot
//                     // $warehouse->products()->updateExistingPivot($product->id, [
//                     //     'quantity' => $currentQty - $quantity,
//                     // ]);

//                     //new line-decrement the global products.stock_quantity
//                     // $product->decrement('stock_quantity',$quantity);

//                     // if (($currentQty - $quantity) <= 0) {
//                     //     $this->sendReplenishmentNotification([$product]);
//                     // }


//                     $product->decrement('stock_quantity',$quantity);
//                     $product->refresh();

//                     $warehouse->products()->updateExistingPivot($product->id,[
//                         'quantity' => DB::raw('quantity - ' . $quantity),
//                     ]);

//                     if($product->stock_quantity <= $product->low_stock_threshold){
//                         $productsToAlert[] = $product;
//                     }
//                 }


//                 if(!empty($productsToAlert)){
//                     $this->sendReplenishmentNotification($productsToAlert);
//                 }

//                 $shippingCompany = $data['shipping_company'] ?? 'FedEx';
//                 // Prioritize shipping_cost from frontend if provided and valid
//                 $shippingCost = $data['shipping_cost'] ?? null; 

//                 if($shippingCost === null || !is_numeric($shippingCost) || $shippingCost < 0){
//                     $shippingCost = match ($shippingCompany) {
//                         'DHL' => 12.5,
//                         'UPS' => 15,
//                         default => 10,
//                     };
//                 }

//                 // Start shipping process
//                 if ($isStockAvailable) {
//                     $this->startShipping(
//                         $invoice,
//                         $shippingCompany,
//                         $shippingCost
//                     );
//                 }
            
//             } else {
//                 // Create placeholder shipping record so cost/company can be reused later (me na qit shipping_cost masnej te qatij invoice)
//                 $invoice->shipping()->create([
//                     'shipping_company' => $data['shipping_company'] ?? 'FedEx',
//                     'shipping_cost' => $data['shipping_cost'] ?? 0,
//                     'tracking_id' => null,
//                     'status' => 'awaiting_stock',
//                 ]);
//                 // Stock not available: send low stock alert
//                 $this->sendReplenishmentNotification($productsMissing);
//             }

//             return $invoice;
//         });
//     }


//     protected function sendReplenishmentNotification(array $products)
//     {
//        Mail::to('dion.gashi2@student.uni-pr.edu')->send(new LowStockAlert($products));
//     }

//     public function startShipping(Invoice $invoice, ?string $shippingCompany = 'FedEx', ?float $shippingCost = 0): void
//     {
//         $trackingId = strtoupper(Str::uuid());

//         $shipping = $invoice->shipping;

//         if (!$shipping) {
//             // Create shipping record if not exists
//             $shipping = $invoice->shipping()->create([
//                 'shipping_company' => $shippingCompany,
//                 'shipping_cost' => $shippingCost, 
//                 'tracking_id' => $trackingId,
//                 'status' => 'on_delivery',
//             ]);
//         } else {
//             // $shipping->update([
//             //     'tracking_id' => $trackingId,
//             //     'status' => 'on_delivery',
//             // ]);
//             $shipping->update([
//                 'shipping_company' => $shippingCompany,
//                 'shipping_cost' => $shippingCost,
//                 'tracking_id' => $trackingId,
//                 'status' => 'on_delivery',
//             ]);
            
//         }

//         $invoice->update(['status' => 'on_delivery']);

//         // Notify client
//         $invoice->refresh();
//         $this->notifyClientShipping($invoice);
//     }


//     //case when we don't have stock, but once it's restocked and invoice was pending we ship it automatically
//     //kur bohmi restock. 
//     public function attemptShippingPendingInvoicesForProduct(int $productId): void
// {
//     $pendingInvoices = Invoice::where('status', 'pending')
//         ->whereHas('items', function ($query) use ($productId) {
//             $query->where('product_id', $productId);
//         })->get();

//     foreach ($pendingInvoices as $invoice) {
//         $canShip = true;

//         foreach ($invoice->items as $item) {
//             $available = Warehouse::first()
//                 ->products()
//                 ->where('product_id', $item->product_id)
//                 ->first()?->pivot->quantity ?? 0;

//             if ($available < $item->quantity) {
//                 $canShip = false;
//                 break;
//             }
//         }

//         if ($canShip) {
//             // $this->startShipping($invoice);
//             $this->startShipping(
//                 $invoice,
//                 $invoice->shipping?->shipping_company ?? 'FedEx',
//                 $invoice->shipping?->shipping_cost ?? 0
//             );

//             InvoiceLog::create([
//                 'invoice_id' => $invoice->id,
//                 'message' => 'Shipping started automatically after stock replenishment.'
//             ]);
//         }
//     }
// }

//     protected function notifyClientShipping(Invoice $invoice): void
//     {
//         $email = $invoice->client->email ?? null;

//         if ($email) {
//             Mail::to($email)->send(new ClientShippingNotification($invoice));
//         }
//     }
// }