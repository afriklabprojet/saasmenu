<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Payment extends Model
{
    protected $fillable = [
        'vendor_id',
        'payment_name',
        'payment_type',
        'test_public_key',
        'test_secret_key',
        'live_public_key',
        'live_secret_key',
        'public_key',
        'secret_key',
        'environment',
        'status',
        'is_available',
        'is_activate',
        'reorder_id',
        'currency',
        'encryption_key'
    ];
}
