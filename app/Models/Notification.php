<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Notification extends Model
{
    use HasFactory;

    protected $fillable = [
        'customer_id',
        'user_id',
        'type',
        'title',
        'message',
        'data',
        'read_at',
        'sent_at',
        'action_url',
        'priority',
    ];

    protected $casts = [
        'data' => 'array',
        'read_at' => 'datetime',
        'sent_at' => 'datetime',
    ];

    /**
     * Get the customer
     */
    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }

    /**
     * Get the user (restaurant owner)
     */
    public function user()
    {
        return $this->belongsTo(User::class);
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

    /**
     * Mark as unread
     */
    public function markAsUnread()
    {
        $this->update(['read_at' => null]);
        return $this;
    }

    /**
     * Scope for unread notifications
     */
    public function scopeUnread($query)
    {
        return $query->whereNull('read_at');
    }

    /**
     * Scope for read notifications
     */
    public function scopeRead($query)
    {
        return $query->whereNotNull('read_at');
    }

    /**
     * Scope by type
     */
    public function scopeByType($query, $type)
    {
        return $query->where('type', $type);
    }

    /**
     * Get notification types
     */
    public static function getTypes()
    {
        return [
            'order_status' => 'Statut de commande',
            'promotion' => 'Promotion',
            'newsletter' => 'Newsletter',
            'loyalty' => 'Programme de fidélité',
            'system' => 'Système',
            'test' => 'Test',
        ];
    }
}
