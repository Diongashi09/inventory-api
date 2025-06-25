<?php

namespace App\Policies;

use App\Models\Category;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;
use Illuminate\Auth\Access\Response;
use App\Policies\BasePolicy;  // import it!


class CategoryPolicy extends BasePolicy
{
    use HandlesAuthorization;

    // Admin can do everything
    public function before(User $user, $ability)
    {
        if (optional($user->role)->name === 'Admin') {
            return true;
        }
    }

    public function viewAny(User $user)
    {
        return $this->allowIf($user, 'read', 'categories');
    }

    public function view(User $user, Category $category)
    {
        return $this->allowIf($user, 'read', 'categories');
    }

    public function create(User $user)
    {
        return $this->allowIf($user, 'create', 'categories');
    }

    public function update(User $user, Category $category)
    {
        return $this->allowIf($user, 'update', 'categories');
    }

    public function delete(User $user, Category $category)
    {
        return $this->allowIf($user, 'delete', 'categories');
    }

    // // Everyone authenticated can view
    // public function viewAny(User $user){ 
    //     return true; 
    // }
    // public function view(User $user, Category $category) { return true; }

    // // Only Admin (handled above) — others denied
    // public function create(User $user)     { return false; }
    // public function update(User $user, Category $cat) { return false; }
    // public function delete(User $user, Category $cat) { return false; }
}