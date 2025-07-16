<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Product;
use App\Models\Warehouse;
use Illuminate\Support\Facades\DB;


class ReconcileStockQuantitiesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $this->command->info('Starting stock quantity reconciliation...');

        $mainWarehouse = Warehouse::first(); // Assuming a single main warehouse

        if (!$mainWarehouse) {
            $this->command->error('No main warehouse found. Skipping reconciliation.');
            return;
        }

        $products = Product::all();

        foreach ($products as $product) {
            $warehouseProduct = $mainWarehouse->products()->where('product_id', $product->id)->first();

            $warehouseQuantity = $warehouseProduct ? $warehouseProduct->pivot->quantity : 0;

            if ($product->stock_quantity != $warehouseQuantity) {
                $this->command->warn("Product {$product->id} ({$product->name}): Global stock ({$product->stock_quantity}) differs from warehouse stock ({$warehouseQuantity}). Reconciling...");

                // Update the global stock_quantity to match the warehouse quantity
                // (Assuming your system's global stock is derived from the main warehouse for simplicity)
                $product->update(['stock_quantity' => $warehouseQuantity]);
                $this->command->info("Updated global stock for Product {$product->id} to {$warehouseQuantity}.");
            } else {
                $this->command->info("Product {$product->id} ({$product->name}): Stock quantities already in sync ({$product->stock_quantity}).");
            }
        }

        $this->command->info('Stock quantity reconciliation finished.');
    }
}