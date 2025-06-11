<?php

namespace App\Policies;

use App\Models\Invoice;
use App\Models\User;
use Illuminate\Auth\Access\Response;
use Illuminate\Auth\Access\HandlesAuthorization;


class InvoicePolicy
{
    use HandlesAuthorization;

    public function before(User $u, $ability)
    {
        if (optional($u->role)->name === 'Admin') {
            return true;
        }
    }

    public function viewAny(User $u)    { return true; }
    public function view(User $u, Invoice $i) { return true; }

    public function create(User $u)     { return in_array($u->role->name, ['Manager','Staff']); }
    public function update(User $u, Invoice $i) { return $u->role->name === 'Manager'; }
    public function delete(User $u, Invoice $i) { return false; }
}