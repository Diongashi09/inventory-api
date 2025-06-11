<?php

namespace App\Policies;

use App\Models\Product;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;
use Illuminate\Auth\Access\Response;

class ProductPolicy
{
    use HandlesAuthorization;

    public function before(User $user, $ability)
    {
        if (optional($user->role)->name === 'Admin') {
            return true;
        }
    }

    public function viewAny(User $user)    { return true; }
    public function view(User $user, Product $p) { return true; }

    // Manager can create & update
    public function create(User $user)     { return $user->role->name === 'Manager'; }
    public function update(User $user, Product $p) { return $user->role->name === 'Manager'; }

    // Only Admin can delete
    public function delete(User $user, Product $p) { return false; }
}