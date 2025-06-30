<?php

namespace App\Models;
use App\Models\Role;
use App\Models\Supply;
use App\Models\Invoice;
use App\Models\Client;
// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Laravel\Sanctum\HasApiTokens;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasApiTokens,HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'role_id',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];


    public function role(){
        return $this->belongsTo(Role::class);
    }

    public function client(): HasOne
    {
        return $this->hasOne(Client::class,'user_id');
    }


    public function isAdmin(): bool
    {
        return optional($this->role)->name === 'Admin';
    }

    /**
     * Helper method to check if the user has the 'Manager' role.
     *
     * @return bool
     */
    public function isManager(): bool
    {
        return optional($this->role)->name === 'Manager';
    }

    /**
     * Helper method to check if the user has the 'Client' role.
     *
     * @return bool
     */
    public function isClient(): bool
    {
        return optional($this->role)->name === 'Client';
    }

    // public function supplies(){
    //     return $this->hasMany(Supply::class,'created_by');
    // }

    // public function invoices(){
    //     return $this->hasMany(Invoice::class,'created_by');
    // }

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }
}
