<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OrderItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'order_id',
        'item_id',
        'quantity',
        'price',
        'subtotal',
        'item_name',
        'item_options',
        'special_instructions',
    ];

    protected $casts = [
        'quantity' => 'integer',
        'price' => 'decimal:2',
        'subtotal' => 'decimal:2',
        'item_options' => 'array',
    ];

    /**
     * Get the order
     */
    public function order()
    {
        return $this->belongsTo(Order::class);
    }

    /**
     * Get the item
     */
    public function item()
    {
        return $this->belongsTo(Item::class);
    }

    /**
     * Calculate subtotal
     */
    public function calculateSubtotal()
    {
        return $this->price * $this->quantity;
    }
}