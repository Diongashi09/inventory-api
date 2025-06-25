<?php

namespace App\Policies;

use App\Models\Transaction;
use App\Models\User;
use Illuminate\Auth\Access\Response;
use Illuminate\Auth\Access\HandlesAuthorization;
use App\Policies\BasePolicy;  // import it!


class TransactionPolicy extends BasePolicy
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
        return $this->allowIf($user, 'read', 'transactions');
    }

    public function view(User $user, Transaction $transaction)
    {
        return $this->allowIf($user, 'read', 'transactions');
    }

    public function create(User $user)
    {
        return $this->allowIf($user, 'create', 'transactions');
    }

    public function update(User $user, Transaction $transaction)
    {
        return $this->allowIf($user, 'update', 'transactions');
    }

    public function delete(User $user, Transaction $transaction)
    {
        return $this->allowIf($user, 'delete', 'transactions');
    }

    // Only Manager and Admin can view transactions
    // public function viewAny(User $u)    { return $u->role->name === 'Manager'; }
    // public function view(User $u, Transaction $t) { return $u->role->name === 'Manager'; }

    // public function create(User $u)     { return $u->role->name === 'Manager'; }
    // public function update(User $u, Transaction $t) { return false; }
    // public function delete(User $u, Transaction $t) { return false; }
}
