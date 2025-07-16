<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Permission;

class PermissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $resources = [
            'users','categories','clients',
            'invoices','products','supplies','transactions','announcement','vendor_companies'
        ];
        $verbs = ['create','read','update','delete'];

        foreach ($resources as $res) {
            foreach ($verbs as $verb) {
                Permission::firstOrCreate([
                    'resource' => $res,
                    'verb'     => $verb,
                    'name'     => ucfirst($verb).' '.ucfirst($res),
                ]);
            }
        }

        // And don’t forget the special “Update Self” on users:
        Permission::firstOrCreate([
            'resource'=>'users',
            'verb'=>'update',
            'name'=>'Update Self',
        ]);
    }
}
