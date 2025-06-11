<?php

namespace App\Models;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use App\Models\Supply;
use App\Models\Product;

class SupplyItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'supply_id',
        'product_id',
        'quantity',
        'unit_price',
        'total_price',
    ];

    public function supply()
    {
        return $this->belongsTo(Supply::class);
    }

    public function product()
    {
        return $this->belongsTo(Product::class);
    }
}
