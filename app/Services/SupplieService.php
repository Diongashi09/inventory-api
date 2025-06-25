<?php

namespace App\Services;

use App\Models\Supply;
use App\Models\Transaction;
use App\Services\InvoiceService;
use Illuminate\Support\Facades\DB;

class SupplieService
{
    protected $invoiceService;

    public function __construct(InvoiceService $invoiceService)
    {
        $this->invoiceService = $invoiceService;
    }


    public function createSupply(array $data): Supply
    {
        return DB::transaction(function () use ($data) {
            $supply = Supply::create([
                'reference_number' => 'SUP-' . strtoupper(uniqid()),
                'date' => now(),
                'supplier_type'    => $data['supplier_type'],
                'supplier_id'      => $data['supplier_id'] ?? null,
                'tariff_fee'       => $data['tariff_fee'] ?? 0,
                'import_cost'      => $data['import_cost'] ?? 0,
                'status'           => $data['status'] ?? 'pending',
                'created_by'       => auth()->id(),
            ]);

            $warehouseId = 1; // You currently only have one warehouse

            foreach ($data['items'] as $i) {
                $item = $supply->items()->create([
                    'product_id'  => $i['product_id'],
                    'quantity'    => $i['quantity'],
                    'unit_price'  => $i['unit_price'],
                    'total_price' => $i['quantity'] * $i['unit_price'],
                ]);

                $product = $item->product;

            // Update global stock on the product (optional or for reporting)
                $product->increment('stock_quantity', $item->quantity);

            // Update stock in the warehouse_product pivot table
                $this->addStockToWarehouse($product->id, $warehouseId, $item->quantity);

            // Log transaction
                Transaction::create([
                    'transaction_type' => 'supply',
                    'reference_id'     => $supply->id,
                    'product_id'       => $product->id,
                    'quantity'         => $item->quantity,
                    'unit_price'       => $item->unit_price,
                    'total_price'      => $item->total_price,
                    'direction'        => 'in',
                    'tariff_fee'       => $supply->tariff_fee,
                    'import_cost'      => $supply->import_cost,
                    'created_by'       => auth()->id(),
                ]);

                $this->invoiceService->attemptShippingPendingInvoicesForProduct($product->id);
            }

            return $supply;
        });
    }

    private function addStockToWarehouse($productId, $warehouseId, $quantity)
    {
        $existing = DB::table('warehouse_product')
            ->where('warehouse_id',$warehouseId)
            ->where('product_id',$productId)
            ->first();

        if($existing){
            DB::table('warehouse_product')
               ->where('warehouse_id',$warehouseId)
               ->where('product_id',$productId)
               ->increment('quantity',$quantity);
        } else {
            DB::table('warehouse_product')->insert([//insert new row otherwise
                'warehouse_id' => $warehouseId,
                'product_id'   => $productId,
                'quantity'     => $quantity,
                'created_at'   => now(),
                'updated_at'   => now(),
            ]);
        }
    }
}