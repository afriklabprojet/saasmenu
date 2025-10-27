<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MenuItem extends Model
{
    use HasFactory;

    protected $table = 'items'; // Utilise la table items existante

    protected $fillable = [
        'user_id',
        'cat_id',
        'item_name',
        'item_price',
        'item_description',
        'item_image',
        'is_available',
        'is_featured',
        'order_count',
        'preparation_time',
        'calories',
        'allergens',
        'ingredients',
    ];

    protected $casts = [
        'item_price' => 'decimal:2',
        'is_available' => 'boolean',
        'is_featured' => 'boolean',
        'order_count' => 'integer',
        'preparation_time' => 'integer',
        'allergens' => 'array',
        'ingredients' => 'array',
    ];

    /**
     * Get the restaurant/user
     */
    public function restaurant()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    /**
     * Get the category
     */
    public function category()
    {
        return $this->belongsTo(Category::class, 'cat_id');
    }

    /**
     * Get cart items
     */
    public function cartItems()
    {
        return $this->hasMany(POSCart::class, 'menu_item_id');
    }

    /**
     * Get order items
     */
    public function orderItems()
    {
        return $this->hasMany(OrderItem::class, 'item_id');
    }

    /**
     * Scope for available items
     */
    public function scopeAvailable($query)
    {
        return $query->where('is_available', true);
    }

    /**
     * Scope for featured items
     */
    public function scopeFeatured($query)
    {
        return $query->where('is_featured', true);
    }

    /**
     * Scope by restaurant
     */
    public function scopeByRestaurant($query, $restaurantId)
    {
        return $query->where('user_id', $restaurantId);
    }

    /**
     * Scope by category
     */
    public function scopeByCategory($query, $categoryId)
    {
        return $query->where('cat_id', $categoryId);
    }

    /**
     * Get formatted price
     */
    public function getFormattedPriceAttribute()
    {
        $symbol = config('pos.currency_symbol', '€');
        $position = config('pos.currency_position', 'after');

        if ($position === 'before') {
            return $symbol . ' ' . number_format($this->item_price, 2);
        } else {
            return number_format($this->item_price, 2) . ' ' . $symbol;
        }
    }

    /**
     * Check if item is available
     */
    public function isAvailable()
    {
        return $this->is_available;
    }

    /**
     * Get image URL
     */
    public function getImageUrlAttribute()
    {
        if ($this->item_image) {
            return asset('storage/' . $this->item_image);
        }

        return asset('images/default-food.png');
    }

    /**
     * Increment order count
     */
    public function incrementOrderCount($quantity = 1)
    {
        $this->increment('order_count', $quantity);
    }
}
