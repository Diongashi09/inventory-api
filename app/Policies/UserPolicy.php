<?php

namespace App\Policies;

use App\Models\User;
use Illuminate\Auth\Access\Response;
use Illuminate\Auth\Access\HandlesAuthorization;


class UserPolicy
{
    use HandlesAuthorization;

    public function before(User $u, $ability)
    {
        if (optional($u->role)->name === 'Admin') {
            return true;
        }
    }

    // Only Admin can list users
    public function viewAny(User $u)    { return false; }
    public function view(User $u, User $model)//first arg osht the currently authenticated user e second arg osht the user model being accessed p.sh profile ose data of another user 
    {
        // Admin (above) or the owner themselves
        return $u->id === $model->id;//mundet me view useri qe sosht admin veq veten e tij dmth his profile.
    }

    public function create(User $u)     { return false; }
    public function update(User $u, User $m)
    {
        return $u->id === $m->id;
    }
    public function delete(User $u, User $m) { return false; }
}
