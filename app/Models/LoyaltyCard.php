<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LoyaltyCard extends Model
{
    use HasFactory;

    protected $fillable = [
        'customer_id',
        'restaurant_id',
        'user_id',
        'card_number',
        'points',
        'total_spent',
        'visits_count',
        'status',
        'expires_at',
    ];

    protected $casts = [
        'points' => 'integer',
        'total_spent' => 'decimal:2',
        'visits_count' => 'integer',
        'expires_at' => 'datetime',
        'status' => 'boolean',
    ];

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
     * Get loyalty transactions
     */
    public function transactions()
    {
        return $this->hasMany(LoyaltyTransaction::class);
    }

    /**
     * Check if card is active
     */
    public function isActive()
    {
        return $this->status && (!$this->expires_at || $this->expires_at->isFuture());
    }

    /**
     * Add points
     */
    public function addPoints($points, $description = null)
    {
        $this->increment('points', $points);

        $this->transactions()->create([
            'type' => 'earned',
            'points' => $points,
            'description' => $description ?: 'Points gagnés',
        ]);

        return $this;
    }

    /**
     * Redeem points
     */
    public function redeemPoints($points, $description = null)
    {
        if ($this->points < $points) {
            throw new \Exception('Points insuffisants');
        }

        $this->decrement('points', $points);

        $this->transactions()->create([
            'type' => 'redeemed',
            'points' => $points,
            'description' => $description ?: 'Points utilisés',
        ]);

        return $this;
    }
}
