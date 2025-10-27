<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    use HasFactory;

    protected $table = 'orders';

    protected $fillable = [
        'user_id',
        'customer_id',
        'restaurant_id',
        'vendor_id',
        'table_id',
        'order_number',
        'status',
        'subtotal',
        'delivery_fee',
        'tax',
        'total',
        'delivery_type',
        'delivery_address',
        'payment_method',
        'payment_status',
        'special_instructions',
        'estimated_delivery_time',
        'rating',
        'review',
        'rated_at',
        'cancelled_at',
        'cancellation_reason',
    ];

    protected $casts = [
        'subtotal' => 'decimal:2',
        'delivery_fee' => 'decimal:2',
        'tax' => 'decimal:2',
        'total' => 'decimal:2',
        'estimated_delivery_time' => 'datetime',
        'rated_at' => 'datetime',
        'cancelled_at' => 'datetime',
        'rating' => 'integer',
    ];

    public function vendorinfo()
    {
        return $this->hasOne('App\Models\User', 'id', 'vendor_id')->select('id', 'name');
    }

    public function tableqr()
    {
        return $this->hasOne('App\Models\TableQR', 'id', 'table_id');
    }

    /**
     * Get the customer
     */
    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }

    /**
     * Get the restaurant
     */
    public function restaurant()
    {
        return $this->belongsTo(Restaurant::class);
    }

    /**
     * Get the user (restaurant owner)
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get order items
     */
    public function orderItems()
    {
        return $this->hasMany(OrderItem::class);
    }

    /**
     * Check if order can be cancelled
     */
    public function canBeCancelled()
    {
        return in_array($this->status, ['pending', 'confirmed']);
    }

    /**
     * Check if order can be rated
     */
    public function canBeRated()
    {
        return $this->status === 'delivered' && is_null($this->rating);
    }
}
