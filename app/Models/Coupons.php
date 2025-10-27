<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Coupons extends Model
{
    use HasFactory;

    protected $table = 'coupons';

    protected $fillable = [
        'vendor_id',
        'name',
        'code',
        'type',
        'price',
        'active_from',
        'active_to',
        'limit',
        'reorder_id',
        'is_available',
        'is_deleted'
    ];

    protected $casts = [
        'active_from' => 'datetime',
        'active_to' => 'datetime',
        'is_available' => 'boolean',
        'is_deleted' => 'boolean',
    ];
}
