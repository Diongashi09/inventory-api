<?php

namespace App\Services;

use App\Models\Supply;
use App\Models\Transaction;
use App\Models\Product;
use App\Services\OrderService;
use App\Services\InvoiceService;
use App\Mail\SupplyRequestMailable;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;


class SupplieService {
    // protected $invoiceService;
    // protected OrderService $orderSvc;


    // public function __construct(OrderService $orderSvc)
    // {
    //     $this->orderSvc = $orderSvc;
    // }

    public function __construct()
    {

    }

    public function createSupplyRequest(array $data): Supply
    {
        return DB::transaction(function () use ($data) {
            $supply = Supply::create([
                'reference_number' => 'SUP-' . strtoupper(uniqid()),
                'date'             => now(),
                'supplier_type'    => $data['supplier_type'],
                'supplier_id'      => $data['supplier_id'] ?? null,
                'tariff_fee'       => $data['tariff_fee'] ?? 0,
                'import_cost'      => $data['import_cost'] ?? 0,
                'status'           => 'pending', // Default status for a new request
                'created_by'       => auth()->id(),
                'order_id'         => $data['order_id'] ?? null,
            ]);

            foreach ($data['items'] as $itemData) {
                $supply->items()->create([
                    'product_id'  => $itemData['product_id'],
                    'quantity'    => $itemData['quantity'],
                    'unit_price'  => $itemData['unit_price'],
                    'total_price' => $itemData['quantity'] * $itemData['unit_price'],
                ]);
            }
            
            // Send the email notification if a vendor is specified
            if ($supply->supplier_type === 'company' && $supply->vendorCompany && $supply->vendorCompany->email) {
                // Mail::to($supply->vendorCompany->email)
                //     ->send(new SupplyRequestMailable($supply));
            }

            return $supply;
        });
    }

    public function receiveSupply(Supply $supply)
    {
        return DB::transaction(function() use ($supply) {
            $warehouseId = 1;


            foreach ($supply->items as $item){
                $product = $item->product;

                //update global stock on the product
                $product->increment('stock_quantity',$item->quantity);

                //update stock in the warehouse_product pivot table
                $this->addStockToWarehouse($product->id,$warehouseId,$item->quantity);

                Transaction::create([
                    'transaction_type' => 'supply',
                    'reference_id' => $supply->id,
                    'product_id' => $product->id,
                    'quantity' => $item->quantity,
                    'unit_price' => $item->unit_price,
                    'total_price' => $item->total_price,
                    'direction' => 'in',
                    'tariff_fee' => $supply->tariff_fee,
                    'import_cost' => $supply->import_cost,
                    'created_by' => auth()->id(),
                ]);


                if($supply->order_id){
                    // $this->orderSvc->shipOrder($supply->order_id);
                    app(\App\Services\OrderService::class)->shipOrder($supply->order_id);
                }

                // $this->orderSvc->attemptShippingPendingOrdersForProduct($product->id);
            }
        });
    }


    private function addStockToWarehouse($productId,$warehouseId,$quantity)
    {
        $existing = DB::table('warehouse_product')
            ->where('warehouse_id', $warehouseId)
            ->where('product_id', $productId)
            ->first();

        if ($existing) {
            DB::table('warehouse_product')
                ->where('warehouse_id', $warehouseId)
                ->where('product_id', $productId)
                ->increment('quantity', $quantity);
        } else {
            DB::table('warehouse_product')->insert([
                'warehouse_id' => $warehouseId,
                'product_id'   => $productId,
                'quantity'     => $quantity,
                'created_at'   => now(),
                'updated_at'   => now(),
            ]);
        }
    }

}

// class SupplieService
// {
//     protected $invoiceService;

//     public function __construct(InvoiceService $invoiceService)
//     {
//         $this->invoiceService = $invoiceService;
//     }


//     public function createSupply(array $data): Supply
//     {
//         return DB::transaction(function () use ($data) {
//             $supply = Supply::create([
//                 'reference_number' => 'SUP-' . strtoupper(uniqid()),
//                 'date' => now(),
//                 'supplier_type'    => $data['supplier_type'],
//                 'supplier_id'      => $data['supplier_id'] ?? null,
//                 'tariff_fee'       => $data['tariff_fee'] ?? 0,
//                 'import_cost'      => $data['import_cost'] ?? 0,
//                 'status'           => $data['status'] ?? 'pending',
//                 'created_by'       => auth()->id(),
//             ]);

//             $warehouseId = 1; // You currently only have one warehouse

//             foreach ($data['items'] as $i) {
//                 $item = $supply->items()->create([
//                     'product_id'  => $i['product_id'],
//                     'quantity'    => $i['quantity'],
//                     'unit_price'  => $i['unit_price'],
//                     'total_price' => $i['quantity'] * $i['unit_price'],
//                 ]);

//                 $product = $item->product;

//                 \Log::info("About to increment product #{$product->id}", [
//                     'before'   => $product->stock_quantity,
//                     'quantity' => $i['quantity'],
//                 ]);

//             //  Update global stock on the product (optional or for reporting)
//                 $product->increment('stock_quantity', $item->quantity);
//                 // $product->increment('stock_quantity', $i['quantity']);

//                 \Log::info("After increment, fetched fresh", [
//                     'after' => Product::find($product->id)->stock_quantity,
//                 ]);

//             //  Update stock in the warehouse_product pivot table
//             $this->addStockToWarehouse($product->id, $warehouseId, $item->quantity);
//                 // $this->addStockToWarehouse($product->id, $warehouseId, $i['quantity']);

//             // Log transaction
//                 Transaction::create([
//                     'transaction_type' => 'supply',
//                     'reference_id'     => $supply->id,
//                     'product_id'       => $product->id,
//                     'quantity'         => $item->quantity,
//                     'unit_price'       => $item->unit_price,
//                     'total_price'      => $item->total_price,
//                     'direction'        => 'in',
//                     'tariff_fee'       => $supply->tariff_fee,
//                     'import_cost'      => $supply->import_cost,
//                     'created_by'       => auth()->id(),
//                 ]);

//                 $this->invoiceService->attemptShippingPendingInvoicesForProduct($product->id);
//             }

//             return $supply;
//         });
//     }

//     private function addStockToWarehouse($productId, $warehouseId, $quantity)
//     {
//         $existing = DB::table('warehouse_product')
//             ->where('warehouse_id',$warehouseId)
//             ->where('product_id',$productId)
//             ->first();

//         if($existing){
//             DB::table('warehouse_product')
//                ->where('warehouse_id',$warehouseId)
//                ->where('product_id',$productId)
//                ->increment('quantity',$quantity);
//         } else {
//             DB::table('warehouse_product')->insert([//insert new row otherwise
//                 'warehouse_id' => $warehouseId,
//                 'product_id'   => $productId,
//                 'quantity'     => $quantity,
//                 'created_at'   => now(),
//                 'updated_at'   => now(),
//             ]);
//         }
//     }
// }