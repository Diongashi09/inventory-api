<?php

namespace App\Models;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;

class Permission extends Model
{
    protected $fillable = ['name','verb','resource'];

    public function roles()
    {
        return $this->belongsToMany(Role::class, 'role_permission');
    }
}
