<?php
namespace App\Policies;

use App\Models\User;
use Illuminate\Auth\Access\Response;

abstract class BasePolicy
{
    /**
     * Helper method to check if a user's role has a specific permission.
     * Assumes user->role->permissions relationship is loaded.
     *
     * @param User $user The authenticated user.
     * @param string $verb The action (e.g., 'read', 'create', 'update', 'delete').
     * @param string $resource The resource (e.g., 'products', 'invoices', 'announcements').
     * @return \Illuminate\Auth\Access\Response
     */
    protected function allowIf(User $user, string $verb, string $resource): Response
    {
        // Ensure the user has a role and the role has permissions loaded
        // If user has no role or the role has no permissions loaded, deny access explicitly.
        // This prevents "Attempt to read property 'permissions' on null" errors.
        if (!$user->role || !$user->role->permissions) {
            return Response::deny('User role or permissions not found.');
        }

        // Check if the user's role has a permission matching the verb and resource
        $hasPermission = $user->role->permissions
                                     ->where('verb', $verb)
                                     ->where('resource', $resource)
                                     ->isNotEmpty();

        return $hasPermission
            ? Response::allow()
            : Response::deny('You do not have permission to perform this action on ' . $resource . '.');
    }
}


// namespace App\Policies;

// use App\Models\User;

// abstract class BasePolicy
// {
//     protected function allowIf(User $user, string $verb, string $resource):bool
//     {
//         return $user->role
//                     ->permissions
//                     ->where('verb',$verb)
//                     ->where('resource',$resource)
//                     ->isNotEmpty();
//     }
// }