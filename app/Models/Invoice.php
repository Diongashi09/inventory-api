<?php

namespace App\Models;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use App\Models\Client;
use App\Models\InvoiceItem;
use App\Models\InvoiceLog;
use App\Models\User;
use App\Models\Shipping;


class Invoice extends Model
{
    use HasFactory;

    protected $fillable = [
        'reference_number',
        'date',
        'customer_type',
        'customer_id',
        'created_by',
        'status'
    ];

    public function client()
    {
        return $this->belongsTo(Client::class, 'customer_id');
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function items()
    {
        return $this->hasMany(InvoiceItem::class);
    }

    public function shipping()
    {
        return $this->hasOne(Shipping::class);
    }

    public function logs()
    {
        return $this->hasMany(InvoiceLog::class);
    }

    // ... existing code ...
public function order()
{
    return $this->belongsTo(\App\Models\Order::class);
}
}
