<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Role;


class RoleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Role::create(['name'=>'Admin',   'description'=>'Full access']);
        Role::create(['name'=>'Manager', 'description'=>'Manage products/supplies/invoices']);
        Role::create(['name'=>'Staff',   'description'=>'Limited (create-only)']);
    }
}
