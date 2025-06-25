<?php
namespace App\Policies;

use App\Models\User;

abstract class BasePolicy
{
    protected function allowIf(User $user, string $verb, string $resource):bool
    {
        return $user->role
                    ->permissions
                    ->where('verb',$verb)
                    ->where('resource',$resource)
                    ->isNotEmpty();
    }
}