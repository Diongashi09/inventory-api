<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Warehouse;
use App\Models\Product;

class WarehouseSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Ensure a main warehouse exists
        $mainWarehouse = Warehouse::firstOrCreate(
            ['name' => 'Main Warehouse'],
            ['location' => 'Default Location']
        );

        // Option A: Get a specific product by ID (e.g., your Lenovo product)
        $lenovoProduct = Product::find(56); // Replace 56 with the actual ID of your Lenovo product

        if ($mainWarehouse && $lenovoProduct) {
            $mainWarehouse->products()->syncWithoutDetaching([
                $lenovoProduct->id => ['quantity' => 100], // Add a sufficient quantity for Lenovo
            ]);
            $lenovoProduct->update(['stock_quantity' => $lenovoProduct->stock_quantity + 100]); // Also update global stock_quantity
            $this->command->info("Added 100 units of Product ID {$lenovoProduct->id} ({$lenovoProduct->name}) to Main Warehouse.");
        } else {
             $this->command->warn("Could not find Main Warehouse or Product ID 56 to seed stock.");
        }


        // Option B (Alternative/Addition): Loop through ALL products and add initial stock
        // This is good for populating all products with some default stock
        $allProducts = Product::all();
        foreach ($allProducts as $product) {
            $initialQuantity = 50; // Or a random quantity if you prefer
            $mainWarehouse->products()->syncWithoutDetaching([
                $product->id => ['quantity' => $initialQuantity],
            ]);
            // Also update the global stock_quantity on the product model
            // This assumes stock_quantity tracks total across all warehouses, or is just a redundant sum.
            // If stock_quantity is ONLY the sum of warehouse quantities, then this increment might be done differently.
            // For now, let's assume it's also a direct inventory count.
            $product->update(['stock_quantity' => $product->stock_quantity + $initialQuantity]);
            $this->command->info("Added {$initialQuantity} units of Product ID {$product->id} ({$product->name}) to Main Warehouse.");
        }
    }
}