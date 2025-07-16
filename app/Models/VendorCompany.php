<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;


class VendorCompany extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'contact_person',
        'phone',
        'email',
        'address',
        'additional_info',
    ];

    /**
     * Get the supplies for the vendor company.
     */
    public function supplies(): HasMany
    {
        return $this->hasMany(Supply::class, 'supplier_id');
    }
}