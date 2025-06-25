<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Role;
use App\Models\Permission;
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
use Database\Seeders\PermissionSeeder;
use Database\Seeders\RolePermissionSeeder;

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

        // $this->call([
        //     WarehouseSeeder::class,
        // ]);
        
        /////////////////

        // // 1) Define base roles
        // $admin   = Role::firstOrCreate(['name'=>'admin']);
        // $manager = Role::firstOrCreate(['name'=>'manager']);
        // $client  = Role::firstOrCreate(['name'=>'client']);

        // // 2) Define all permissions
        // $perms = [
        //     ['name'=>'Create Users',   'verb'=>'create','resource'=>'users'],
        //     ['name'=>'Read Users',     'verb'=>'read',  'resource'=>'users'],
        //     ['name'=>'Update Users',   'verb'=>'update','resource'=>'users'],
        //     ['name'=>'Delete Users',   'verb'=>'delete','resource'=>'users'],
        //     ['name'=>'Update Self',    'verb'=>'update','resource'=>'users'], 
        //     // … repeat for other resources …
        //     ];
        //     foreach($perms as $p) {
        //         Permission::firstOrCreate($p);
        //     }

        //     // 3) Attach permissions to roles
        //     // Admin: everything
        //     $admin->permissions()->sync(Permission::all()->pluck('id'));

        //     // Manager: read & update users
        //     $manager->permissions()->sync(
        //         Permission::whereIn('verb',['read','update'])
        //           ->where('resource','users')
        //           ->pluck('id')
        //         );

        //         // Client: only “Update Self”
        //         $client->permissions()->sync(
        //             Permission::where('name','Update Self')->pluck('id')
        //         );

        ////////////////////////////////////////////

        // inside run()
        // $resources = ['users','categories','clients','invoices','products','supplies','transactions'];
        // $verbs     = ['create','read','update','delete'];

        // foreach ($resources as $res) {
        //     foreach ($verbs as $verb) {
        //         Permission::firstOrCreate([
        //             'resource' => $res,
        //             'verb'     => $verb,
        //             'name'     => ucfirst($verb).' '.ucfirst($res),
        //         ]);
        //     }
        // }

        // $this->call([
        //     PermissionSeeder::class,
        // ]);

        $this->call([
            RolePermissionSeeder::class,
        ]);
    }
}