<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    use HasFactory;

    protected $table = 'orders';

    /**
     * The attributes that are mass assignable.
     * 
     * Security: Reduced from 22 to 10 fields
     * Sensitive financial and status fields moved to $guarded
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'user_id',           // Who placed the order
        'customer_id',       // Customer reference
        'vendor_id',         // Which restaurant
        'table_id',          // For QR table orders
        'delivery_type',     // delivery/pickup
        'delivery_address',  // Customer provided
        'special_instructions', // Customer notes
        'rating',            // Customer can rate
        'review',            // Customer can review
        'cancellation_reason', // Why order was cancelled
    ];

    /**
     * The attributes that are NOT mass assignable.
     * These fields should only be modified through specific business logic.
     *
     * @var array<int, string>
     */
    protected $guarded = [
        'id',
        'restaurant_id',     // Business logic: Set from vendor relationship
        'order_number',      // Security: Auto-generated, should be unique
        'status',            // Business logic: Status transitions should be controlled
        'subtotal',          // Security: Calculated from cart, not user input
        'delivery_fee',      // Security: Calculated from zone, not user input
        'tax',               // Security: Calculated from items, not user input
        'total',             // Security: Calculated sum, not user input
        'payment_method',    // Security: Validated gateway only
        'payment_status',    // Security: Updated by payment gateway callback
        'estimated_delivery_time', // Business logic: Calculated
        'rated_at',          // System timestamp
        'cancelled_at',      // System timestamp
        'created_at',
        'updated_at',
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
