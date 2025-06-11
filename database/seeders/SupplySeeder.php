<?php

namespace Database\Seeders;

use App\Models\Supply;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class SupplySeeder extends Seeder
{
    
    public function run(): void
    {
        Supply::factory()->count(20)->create();
    }
}
