<?php

namespace App\Policies;

use App\Models\Announcement;
use App\Models\User;
use Illuminate\Auth\Access\Response;
use Illuminate\Auth\Access\HandlesAuthorization; 
use App\Policies\BasePolicy;

class AnnouncementPolicy extends BasePolicy
{
    use HandlesAuthorization;

    public function before(User $user, $ability)
    {
        // If the user is an Admin, grant all access
        if ($user->isAdmin()) { // Using the helper method, which should return true for 'Admin'
            return true; // Return true to bypass all other checks
        }
        // For other roles, return null to allow other policy methods to be checked
        return null;
    }

    public function viewAny(User $user): Response
    {
        // Managers should be able to view all, Clients only published (handled in controller)
        // Here, we just check if they have general 'read' permission for announcements resource
        return $this->allowIf($user, 'read', 'announcement');
    }

    public function view(User $user, Announcement $announcement): Response
    {
        // Managers can view any announcement
        if ($user->isManager()) {
            return $this->allowIf($user, 'read', 'announcement');
        }

        // Clients can view announcements only if they are published
        if ($user->isClient()) {
            // Check if the specific announcement instance is currently published
            // This relies on the 'published' scope on the Announcement model
            if ($announcement->published()->exists()) {
                return $this->allowIf($user, 'read', 'announcement');
            }
        }

        return Response::deny('You do not have permission to view this announcement.');
    }

    public function create(User $user): Response
    {
        return $this->allowIf($user, 'create', 'announcement');
    }

    public function update(User $user, Announcement $announcement): Response
    {
        return $this->allowIf($user, 'update', 'announcement');
    }

    public function delete(User $user, Announcement $announcement): Response
    {
        return $this->allowIf($user, 'delete', 'announcement');
    }
}

// class AnnouncementPolicy extends BasePolicy
// {
//     use HandlesAuthorization;
    
//     public function before(User $user, $ability)
//     {
//         if (optional($user->role)->name === 'admin') {
//             return true;
//         }
        
//     }

//     public function viewAny(User $user)
//     {
//         return $this->allowIf($user,'read','announcements');
//     }

//     public function view(User $user, Announcement $ann)
//     {
//         return $this->allowIf($user,'read','announcements');
//     }

//     public function create(User $user)
//     {
//         logger('Manager perms for announcements: ', 
//            $user->role->permissions
//                  ->where('resource','announcements')
//                  ->pluck('verb')
//                  ->toArray()
//         );
//         return $this->allowIf($user,'create','announcements');
//     }

//     public function update(User $user, Announcement $ann)
//     {
//         return $this->allowIf($user,'update','announcements');
//     }

//     public function delete(User $user, Announcement $ann)
//     {
//         return $this->allowIf($user,'delete','announcements');
//     }
// }