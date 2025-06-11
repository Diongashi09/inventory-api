<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Shipping extends Model
{
    use HasFactory;

    protected $fillable = [
        'invoice_id', 'tracking_id', 'shipping_company', 'shipping_cost', 'status'
    ];

    public function invoice() {
        return $this->belongsTo(Invoice::class);
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
