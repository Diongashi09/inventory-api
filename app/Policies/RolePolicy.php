<?php

namespace App\Policies;

use App\Models\Role;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;
use Illuminate\Auth\Access\Response;

class RolePolicy
{
    use HandlesAuthorization;

    public function before(User $u, $ability)
    {
        if (optional($u->role)->name === 'Admin') {
            return true;
        }
    }

    public function viewAny(User $u)    { return false; }
    public function view(User $u, Role $r)      { return false; }
    public function create(User $u)     { return false; }
    public function update(User $u, Role $r)    { return false; }
    public function delete(User $u, Role $r)    { return false; }
}