<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\Warehouse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;


class WarehouseTransferController extends Controller
{
    public function moveAllToMainWarehouse()
    {
        $mainWarehouse = Warehouse::firstOrFail(); // assuming it's the main one

        DB::transaction(function () use ($mainWarehouse) {
            $products = Product::all();

            foreach ($products as $product) {
                // Check if product already exists in warehouse pivot
                $existing = $mainWarehouse->products()->find($product->id);

                if ($existing) {
                    // If already in warehouse, increment quantity
                    $mainWarehouse->products()->updateExistingPivot($product->id, [
                        'quantity' => $existing->pivot->quantity + $product->stock_quantity,
                    ]);
                } else {
                    // If not, attach with the quantity
                    $mainWarehouse->products()->attach($product->id, [
                        'quantity' => $product->stock_quantity,
                    ]);
                }

                // Optional: reset product stock to 0 if you treat pivot as the only stock source
                // $product->stock_quantity = 0;
                // $product->save();
            }
        });

        return response()->json(['message' => 'All products moved to main warehouse successfully.']);
    }

    public function refillProductStockFromWarehouse()
    {
        $mainWarehouse = \App\Models\Warehouse::firstOrFail(); // Assuming this is your only warehouse

        DB::transaction(function () use ($mainWarehouse) {
            $products = $mainWarehouse->products()->withPivot('quantity')->get();

            foreach ($products as $product) {
                $product->stock_quantity = $product->pivot->quantity;
                $product->save();
            }
        });

        return response()->json(['message' => 'Products stock_quantity column refilled from warehouse successfully.']);
    }
}
