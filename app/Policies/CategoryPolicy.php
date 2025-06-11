<?php

namespace App\Policies;

use App\Models\Category;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;
use Illuminate\Auth\Access\Response;

class CategoryPolicy
{
    use HandlesAuthorization;

    // Admin can do everything
    public function before(User $user, $ability)
    {
        if (optional($user->role)->name === 'Admin') {
            return true;
        }
    }

    // Everyone authenticated can view
    public function viewAny(User $user)    { return true; }
    public function view(User $user, Category $category) { return true; }

    // Only Admin (handled above) — others denied
    public function create(User $user)     { return false; }
    public function update(User $user, Category $cat) { return false; }
    public function delete(User $user, Category $cat) { return false; }
}
