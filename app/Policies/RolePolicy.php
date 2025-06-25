<?php

namespace App\Policies;

use App\Models\Role;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;
use Illuminate\Auth\Access\Response;
use App\Policies\BasePolicy;  // import it!

class RolePolicy extends BasePolicy
{
    use HandlesAuthorization;

    public function before(User $u, $ability)
    {
        if (optional($u->role)->name === 'Admin') {
            return true;
        }
    }

    public function viewAny(User $user)
    {
        return $this->allowIf($user, 'read', 'roles');
    }

    public function view(User $user, Role $role)
    {
        return $this->allowIf($user, 'read', 'roles');
    }

    public function create(User $user)
    {
        return $this->allowIf($user, 'create', 'roles');
    }

    public function update(User $user, Role $role)
    {
        return $this->allowIf($user, 'update', 'roles');
    }

    public function delete(User $user, Role $role)
    {
        return $this->allowIf($user, 'delete', 'roles');
    }

    // public function viewAny(User $u)    { return false; }
    // public function view(User $u, Role $r)      { return false; }
    // public function create(User $u)     { return false; }
    // public function update(User $u, Role $r)    { return false; }
    // public function delete(User $u, Role $r)    { return false; }
}