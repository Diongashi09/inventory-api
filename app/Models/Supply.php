<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
//use App\Models\Client;
use App\Models\SupplyItem;
use App\Models\User;
use App\Models\VendorCompany;

class Supply extends Model
{
    use HasFactory;

    protected $fillable = [
        'reference_number',
        'date',
        'supplier_type',
        'supplier_id',
        'tariff_fee',
        'import_cost',
        'created_by',
        'status',
        'order_id', 
    ];

    protected $casts = [
        'date' => 'datetime',
    ];

    public function vendorCompany(): BelongsTo
    {
        return $this->belongsTo(VendorCompany::class, 'supplier_id');
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function items(): HasMany
    {
        return $this->hasMany(SupplyItem::class);
    }
}
