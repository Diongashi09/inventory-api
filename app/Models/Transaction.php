<?php

namespace App\Models;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use App\Models\Product;


class Transaction extends Model
{
    use HasFactory;

    protected $fillable = [
        'transaction_type',
        'reference_id',
        'product_id',
        'quantity',
        'unit_price',
        'total_price',
        'direction',
        'tariff_fee',
        'import_cost',
        'created_by',
    ];

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    public function creator()
    {
        return $this->belongsTo(User::class,'created_by');
    }
}