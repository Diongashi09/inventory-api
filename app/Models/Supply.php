<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use App\Models\Client;
use App\Models\SupplyItem;
use App\Models\User;

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
    ];

    protected $casts = [
        'date' => 'datetime',
    ];

    public function client()
    {
        return $this->belongsTo(Client::class, 'supplier_id');
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function items()
    {
        return $this->hasMany(SupplyItem::class);
    }
}
