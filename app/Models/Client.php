<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use App\Models\Supply;
use App\Models\Invoice;

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
    ];

    public function supplies(){
        return $this->hasMany(Supply::class,'supplier_id');
    }

    public function invoices(){
        return $this->hasMany(Invoice::class,'customer_id');
    }
}
