<?php

namespace Database\Seeders;

use App\Models\User;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Database\Seeders\RoleSeeder;
use Database\Seeders\UserSeeder;
use Database\Seeders\CategorySeeder;
use Database\Seeders\ProductSeeder;
use Database\Seeders\ClientSeeder;
use Database\Seeders\SupplySeeder;
use Database\Seeders\InvoiceSeeder;
use Database\Seeders\WarehouseSeeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // User::factory(10)->create();

        // $this->call([
        //     RoleSeeder::class,
        //     UserSeeder::class,
        //     CategorySeeder::class,
        //     ProductSeeder::class,
        //     ClientSeeder::class,
        //     SupplySeeder::class,   
        //     InvoiceSeeder::class,   
        // ]);

        $this->call([
            WarehouseSeeder::class,
        ]);
        
    }
}