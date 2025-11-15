<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Cart extends Model
{
    use HasFactory;

    protected $table = 'carts';

    protected $fillable = [
        'vendor_id',
        'user_id',
        'session_id',
        'product_id',
        'product_name',
        'product_slug',
        'product_image',
        'attribute',
        'variation_id',
        'variation_name',
        'qty',
        'product_price',
        'product_tax',
    ];

    protected $casts = [
        'vendor_id' => 'integer',
        'user_id' => 'integer',
        'product_id' => 'integer',
        'variation_id' => 'integer',
        'qty' => 'integer',
        'product_price' => 'decimal:2',
        'product_tax' => 'decimal:2',
    ];
}
