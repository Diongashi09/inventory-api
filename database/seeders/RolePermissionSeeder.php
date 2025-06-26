<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Role;
use App\Models\Permission;


class RolePermissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $admin   = Role::where('name','Admin')->first();
        $manager = Role::where('name','Manager')->first();
        $client  = Role::where('name','Client')->first();

        // 1) Admin: all perms
        $admin->permissions()->sync(
            Permission::all()->pluck('id')
        );

        // 2) Manager: read/create/update on most resources, but no deletes
        $managerPerms = Permission::whereIn('resource', [
                'categories','clients','invoices','products','supplies','announcements'
            ])
            ->whereIn('verb',['read','create','update'])
            ->pluck('id')
            ->toArray();
        // + on transactions only read/create
        $managerPerms = array_merge(
            $managerPerms,
            Permission::where('resource','transactions')
                      ->whereIn('verb',['read','create'])
                      ->pluck('id')
                      ->toArray()
        );
        // + on users read/update (you may already have these)
        $managerPerms = array_merge(
            $managerPerms,
            Permission::where('resource','users')
                      ->whereIn('verb',['read','update'])
                      ->pluck('id')
                      ->toArray()
        );
        // Specific: Announcements (read/create/update) - as requested, kept separate
        $managerPerms = array_merge(
            $managerPerms,
            Permission::where('resource', 'announcement')
                      ->whereIn('verb', ['read', 'create', 'update'])
                      ->pluck('id')
                      ->toArray()
        );

        $manager->permissions()->sync(array_unique($managerPerms));

        // 3) Client: only read categories & clients, and “update self” on users
        $clientPerms = Permission::whereIn('resource',['categories','clients'])
                                 ->where('verb','read')
                                 ->pluck('id')
                                 ->toArray();
        // If you want Clients to also create their own invoice request:
        $clientPerms = array_merge(
            $clientPerms,
            Permission::where('resource','invoices')
                      ->where('verb','create')
                      ->pluck('id')
                      ->toArray()
        );
        // + users.update (self)
        $clientPerms[] = Permission::where('name','Update Self')->first()->id;

        // *** ADD THIS: give clients read access to announcements ***
        $clientPerms = array_merge(
            $clientPerms,
            Permission::where('resource','announcement')
                      ->where('verb','read')
                      ->pluck('id')
                      ->toArray()
        );

        $client->permissions()->sync(array_unique($clientPerms));   
    }
}
