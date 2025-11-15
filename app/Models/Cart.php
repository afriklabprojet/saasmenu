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
        'item_id',
        'item_name',
        'item_image',
        'item_price',
        'attribute',
        'variation_id',
        'variation_name',
        'qty',
        'price',
        'tax',
        'product_price',
        'product_tax',
        'extras_id',
        'extras_name',
        'extras_price',
        'variants_id',
        'variants_name',
        'variants_price',
        'buynow',
    ];

    protected $casts = [
        'vendor_id' => 'integer',
        'user_id' => 'integer',
        'product_id' => 'integer',
        'item_id' => 'integer',
        'variation_id' => 'integer',
        'qty' => 'integer',
        'price' => 'decimal:2',
        'tax' => 'decimal:2',
        'product_price' => 'decimal:2',
        'product_tax' => 'decimal:2',
        'item_price' => 'decimal:2',
        'extras_price' => 'decimal:2',
        'variants_price' => 'decimal:2',
        'buynow' => 'boolean',
    ];

    /**
     * Relations
     */
    public function item()
    {
        return $this->belongsTo(Item::class, 'item_id');
    }

    public function product()
    {
        return $this->belongsTo(Item::class, 'product_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function vendor()
    {
        return $this->belongsTo(User::class, 'vendor_id');
    }

    public function variants()
    {
        return $this->belongsTo(Variants::class, 'variants_id');
    }
}
