<?php

namespace App\Policies;

use App\Models\Transaction;
use App\Models\User;
use Illuminate\Auth\Access\Response;
use Illuminate\Auth\Access\HandlesAuthorization;


class TransactionPolicy
{
    use HandlesAuthorization;

    public function before(User $u, $ability)
    {
        if (optional($u->role)->name === 'Admin') {
            return true;
        }
    }

    // Only Manager and Admin can view transactions
    public function viewAny(User $u)    { return $u->role->name === 'Manager'; }
    public function view(User $u, Transaction $t) { return $u->role->name === 'Manager'; }

    public function create(User $u)     { return $u->role->name === 'Manager'; }
    public function update(User $u, Transaction $t) { return false; }
    public function delete(User $u, Transaction $t) { return false; }
}
