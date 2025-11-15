<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OrderDetails extends Model
{
    use HasFactory;

    protected $table = 'order_details';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'vendor_id',
        'user_id',
        'session_id',
        'order_id',
        'product_id',
        'product_name',
        'product_slug',
        'product_image',
        'attribute',
        'variation_id',
        'variation_name',
        'product_price',
        'product_tax',
        'qty',
    ];

    /**
     * Get the order that owns the detail.
     */
    public function order()
    {
        return $this->belongsTo(Order::class);
    }

    /**
     * Get the product.
     */
    public function product()
    {
        return $this->belongsTo(Item::class, 'product_id');
    }
}
