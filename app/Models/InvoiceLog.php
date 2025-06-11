<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class InvoiceLog extends Model
{
    protected $fillable = ['invoice_id', 'message'];
}