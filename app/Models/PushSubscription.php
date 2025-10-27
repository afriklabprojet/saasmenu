<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PushSubscription extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'endpoint',
        'auth_key',
        'p256dh_key',
        'is_active',
        'user_agent',
        'last_used_at'
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'last_used_at' => 'datetime'
    ];

    /**
     * Relation avec l'utilisateur
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Convertir en format JSON pour les notifications push
     */
    public function toSubscriptionArray(): array
    {
        return [
            'endpoint' => $this->endpoint,
            'keys' => [
                'auth' => $this->auth_key,
                'p256dh' => $this->p256dh_key
            ]
        ];
    }

    /**
     * Marquer comme utilisée
     */
    public function markAsUsed(): void
    {
        $this->update([
            'last_used_at' => now(),
            'is_active' => true
        ]);
    }

    /**
     * Désactiver la souscription
     */
    public function deactivate(): void
    {
        $this->update(['is_active' => false]);
    }

    /**
     * Scope pour les souscriptions actives
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope pour un utilisateur spécifique
     */
    public function scopeForUser($query, $userId)
    {
        return $query->where('user_id', $userId);
    }
}
