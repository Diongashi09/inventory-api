<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Invoice;
use App\Models\Order;

class Shipping extends Model
{
    use HasFactory;

    protected $fillable = [
        'invoice_id', 
        'order_id',
        'tracking_id', 
        'shipping_company', 
        'shipping_cost', 
        'status',
    ];

    public function invoice() {
        return $this->belongsTo(Invoice::class);
    }

    public function order()
    {
        return $this->belongsTo(Order::class);
    }

    // public function client()
    // {
    //     return $this->belongsTo(Client::class);
    // }

    // public function product()
    // {
    //     return $this->belongsTo(Product::class);
    // }
}
