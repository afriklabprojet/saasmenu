<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LoyaltyNotification extends Model
{
    use HasFactory;

    protected $fillable = [
        'member_id',
        'restaurant_id',
        'type',
        'title',
        'message',
        'data',
        'read_at',
        'sent_at',
    ];

    protected $casts = [
        'data' => 'array',
        'read_at' => 'datetime',
        'sent_at' => 'datetime',
    ];

    /**
     * Get the loyalty member
     */
    public function member()
    {
        return $this->belongsTo(LoyaltyMember::class);
    }

    /**
     * Get the restaurant
     */
    public function restaurant()
    {
        return $this->belongsTo(Restaurant::class);
    }

    /**
     * Check if notification is read
     */
    public function isRead()
    {
        return !is_null($this->read_at);
    }

    /**
     * Mark as read
     */
    public function markAsRead()
    {
        $this->update(['read_at' => now()]);
        return $this;
    }
}
