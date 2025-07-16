<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Role;
use App\Models\Permission;

class AddClientReadInvoicesPermission extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // 1. Find the 'Client' role
        $clientRole = Role::where('name', 'Client')->first();

        if (!$clientRole) {
            $this->command->error("Client role not found. Please ensure 'Client' role exists in your 'roles' table.");
            return; // Stop if the role doesn't exist
        }

        // 2. Find the 'Read Invoices' permission
        // Using the exact ID 15 for 'Read Invoices' as you specified, but it's safer
        // to find it by verb and resource if the ID might change in future migrations.
        $readInvoicesPermission = Permission::where('verb', 'read')
                                            ->where('resource', 'invoices')
                                            ->first();

        if (!$readInvoicesPermission) {
            $this->command->error("Permission 'Read Invoices' (verb: read, resource: invoices) not found. Please ensure it exists in your 'permissions' table.");
            return; // Stop if the permission doesn't exist
        }

        // You confirmed ID 15 is 'Read Invoices', so you could also use:
        // $readInvoicesPermission = Permission::find(15);
        // if (!$readInvoicesPermission) {
        //     $this->command->error("Permission with ID 15 not found.");
        //     return;
        // }


        // 3. Attach the 'Read Invoices' permission to the 'Client' role
        // syncWithoutDetaching will attach it if it's not already attached,
        // without detaching any other existing permissions.
        $clientRole->permissions()->syncWithoutDetaching([
            $readInvoicesPermission->id,
        ]);

        $this->command->info("Permission 'Read Invoices' assigned to 'Client' role successfully.");
    }
}
