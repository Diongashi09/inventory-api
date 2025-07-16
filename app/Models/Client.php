<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
//use App\Models\Supply;
use App\Models\Invoice;
use App\Models\User;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;


class Client extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'client_type',
        'contact_person',
        'phone',
        'email',
        'address',
        'additional_info',
        'user_id',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    // public function supplies(){
    //     return $this->hasMany(Supply::class,'supplier_id');
    // }

    public function invoices(): HasMany
    {
        return $this->hasMany(Invoice::class,'customer_id');
    }
}
