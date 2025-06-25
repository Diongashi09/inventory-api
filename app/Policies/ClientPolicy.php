<?php

namespace App\Policies;

use App\Models\Client;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;
use Illuminate\Auth\Access\Response;
use App\Policies\BasePolicy;  // import it!


class ClientPolicy extends BasePolicy
{
    use HandlesAuthorization;

    public function before(User $user, $ability)
    {
        if (optional($user->role)->name === 'Admin') {
            return true;
        }
    }

    public function viewAny(User $user)
    {
        return $this->allowIf($user, 'read', 'clients');
    }

    public function view(User $user, Client $client)
    {
        return $this->allowIf($user, 'read', 'clients');
    }

    public function create(User $user)
    {
        return $this->allowIf($user, 'create', 'clients');
    }

    public function update(User $user, Client $client)
    {
        return $this->allowIf($user, 'update', 'clients');
    }

    public function delete(User $user, Client $client)
    {
        return $this->allowIf($user, 'delete', 'clients');
    }

    // public function viewAny(User $user)    { return true; }
    // public function view(User $user, Client $c) { return true; }

    // // Only Admin
    // public function create(User $user)     { return $user->role->name === 'Manager'; }
    // public function update(User $user, Client $c) { return $user->role->name === 'Manager'; }
    // public function delete(User $user, Client $c) { return false; }
}