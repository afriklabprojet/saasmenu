<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Promocode extends Model
{
    use HasFactory;

    protected $table = 'promocodes';

    protected $fillable = [
        'vendor_id',
        'offer_name',
        'offer_code',
        'offer_type',
        'offer_amount',
        'min_amount',
        'usage_type',
        'usage_limit',
        'start_date',
        'exp_date',
    ];

    protected $casts = [
        'offer_type' => 'integer',
        'offer_amount' => 'decimal:2',
        'min_amount' => 'integer',
        'usage_type' => 'integer',
        'usage_limit' => 'integer',
        'start_date' => 'date',
        'exp_date' => 'date',
    ];
}
