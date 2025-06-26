<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Role;
use App\Models\Permission;

class AddManagerAnnouncementsPermission extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $managerRole = Role::where('name','Manager')->first();

        if(!$managerRole){
            $this->command->error("Manager role not found. Please ensure 'Manager' role exists in your 'roles' table ");
        }


        $announcementsPermissions = Permission::where('resource','announcements')
                                              ->whereIn('verb',['read','create','update'])
                                              ->pluck('id')
                                              ->toArray();
        
        if(empty($announcementsPermissions)){
            $this->command->warn("No 'read', 'create', or 'update' permissions found for 'announcements' resource. Please ensure PermissionSeeder has run correctly");
            return;
        }
    }
}
