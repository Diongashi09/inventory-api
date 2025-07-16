<?php

namespace App\Models;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
// use App\Models\Category;
use App\Models\SupplyItem;
use App\Models\InvoiceItem;
use App\Models\Transaction;
use App\Models\Warehouse;

class Product extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'description',
        'category_id',
        'stock_quantity',
        'price_excl_vat',
        'vat_rate',
        'unit',
        'low_stock_threshold',
    ];

    public function category(){
        return $this->belongsTo(\App\Models\Category::class);
    }
    
    public function supplyItems(){
        return $this->hasMany(SupplyItem::class);
    }

    public function invoiceItems(){
        return $this->hasMany(InvoiceItem::class);
    }

    public function transactions(){
        return $this->hasMany(Transaction::class);
    }

    public function warehouses()
    {
        return $this->belongsToMany(Warehouse::class, 'warehouse_product')
                ->withPivot('quantity')
                ->withTimestamps();//e kem tregu se kemi many to many relationship mes ktynev dyjav edhe si pivot table e kemi warehouse_product e me withPivot method e bojm include quantity column prej pivot table
    }


    // Accessor: price including VAT
    public function getPriceInclVatAttribute()
    {
        return $this->price_excl_vat * (1 + $this->vat_rate/100);
    }
}
