<?php

namespace App\Policies;

use App\Models\User;
use App\Models\VendorCompany;
use Illuminate\Auth\Access\HandlesAuthorization;
use Illuminate\Auth\Access\Response;
use App\Policies\BasePolicy; // Import your BasePolicy


class VendorCompanyPolicy
{
    use HandlesAuthorization;

    public function before(User $user, $ability)
    {
        // Assuming 'Admin' role always has full access
        if (optional($user->role)->name === 'Admin') {
            return true;
        }
    }

    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): Response
    {
        return $this->allowIf($user, 'read', 'vendor_companies');
    }

    /**
     * Determine whether the user can view the vendor company.
     */
    public function view(User $user, VendorCompany $vendorCompany): Response
    {
        return $this->allowIf($user, 'read', 'vendor_companies');
    }

    /**
     * Determine whether the user can create vendor companies.
     */
    public function create(User $user): Response
    {
        return $this->allowIf($user, 'create', 'vendor_companies');
    }

    /**
     * Determine whether the user can update the vendor company.
     */
    public function update(User $user, VendorCompany $vendorCompany): Response
    {
        return $this->allowIf($user, 'update', 'vendor_companies');
    }

    /**
     * Determine whether the user can delete the vendor company.
     */
    public function delete(User $user, VendorCompany $vendorCompany): Response
    {
        return $this->allowIf($user, 'delete', 'vendor_companies');
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, VendorCompany $vendorCompany): bool
    {
        return false;
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, VendorCompany $vendorCompany): bool
    {
        return false;
    }
}
