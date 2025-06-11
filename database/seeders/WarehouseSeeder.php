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
        //It is used to retrieve the first record from the database that matches the given attributes. If no such record exists, it creates a new record with the attributes.
        // Warehouse::firstOrCreate([
        //     'name' => 'Main Warehouse',
        // ], [
        //     'location' => 'Default Location',
        // ]);


        $warehouse = Warehouse::first();
        $product = Product::first(); // or loop through multiple

        if ($warehouse && $product) {
            $warehouse->products()->syncWithoutDetaching([
                $product->id => ['quantity' => 50],
            ]);
        }
    }
}