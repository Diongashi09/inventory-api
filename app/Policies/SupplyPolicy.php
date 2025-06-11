<?php

namespace App\Policies;

use App\Models\Supply;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;
use Illuminate\Auth\Access\Response;

class SupplyPolicy
{
    use HandlesAuthorization;

    public function before(User $u, $ability)
    {
        if (optional($u->role)->name === 'Admin') {
            return true;
        }
    }

    public function viewAny(User $u) { return true; }
    public function view(User $u, Supply $s) { return true; }

    // Manager & Staff can create
    public function create(User $u) { return in_array($u->role->name, ['Manager','Staff']); }

    // Only Manager (and above) can update
    public function update(User $u, Supply $s) { return $u->role->name === 'Manager'; }

    // Only Admin
    public function delete(User $u, Supply $s) { return false; }
}
