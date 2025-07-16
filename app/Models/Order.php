<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\Client;
use App\Models\User;
use App\Models\OrderItem;
use App\Models\Shipping;

class Order extends Model
{
    protected $fillable = [
       'reference_number','date','customer_type','customer_id','created_by','status'
    ];
  
    protected $casts = [
        'date' => 'date',
    ];

    
    public function items()
    {
     return $this->hasMany(OrderItem::class);
    }
    public function client()
    {
     return $this->belongsTo(Client::class, 'customer_id');
    }
      // app/Models/Order.php
    public function shipping()
    {
     return $this->hasOne(Shipping::class);
    }

    public function creator()
    {
     return $this->belongsTo(User::class, 'created_by');
    }

    // ... existing code ...
    public function invoice()
    {
        return $this->hasOne(\App\Models\Invoice::class);
    }
}