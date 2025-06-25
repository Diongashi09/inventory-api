<?php

namespace App\Policies;

use App\Models\Product;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;
use Illuminate\Auth\Access\Response;
use App\Policies\BasePolicy;  // import it!


class ProductPolicy extends BasePolicy
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
        return $this->allowIf($user, 'read', 'products');
    }

    public function view(User $user, Product $product)
    {
        return $this->allowIf($user, 'read', 'products');
    }

    public function create(User $user)
    {
        return $this->allowIf($user, 'create', 'products');
    }

    public function update(User $user, Product $product)
    {
        return $this->allowIf($user, 'update', 'products');
    }

    public function delete(User $user, Product $product)
    {
        return $this->allowIf($user, 'delete', 'products');
    }

    // public function viewAny(User $user)    { return true; }
    // public function view(User $user, Product $p) { return true; }

    // // Manager can create & update
    // public function create(User $user)     { return $user->role->name === 'Manager'; }
    // public function update(User $user, Product $p) { return $user->role->name === 'Manager'; }

    // // Only Admin can delete
    // public function delete(User $user, Product $p) { return false; }
}