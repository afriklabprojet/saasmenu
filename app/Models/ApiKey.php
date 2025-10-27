<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class ApiKey extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'hashed_key',
        'permissions',
        'restaurant_id',
        'user_id',
        'is_active',
        'usage_count',
        'last_used_at',
        'expires_at'
    ];

    protected $casts = [
        'permissions' => 'array',
        'is_active' => 'boolean',
        'usage_count' => 'integer',
        'last_used_at' => 'datetime',
        'expires_at' => 'datetime'
    ];

    protected $hidden = [
        'hashed_key'
    ];

    /**
     * Relation avec Restaurant
     */
    public function restaurant()
    {
        return $this->belongsTo(Restaurant::class);
    }

    /**
     * Relation avec User
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Générer une nouvelle clé API
     */
    public static function generateKey(): string
    {
        return 'rsk_' . Str::random(32); // RestroSaaS Key
    }

    /**
     * Créer une nouvelle clé API avec hash sécurisé
     */
    public static function createKey(array $data): array
    {
        $plainKey = self::generateKey();
        $hashedKey = hash('sha256', $plainKey);

        $apiKey = self::create(array_merge($data, [
            'hashed_key' => $hashedKey
        ]));

        return [
            'model' => $apiKey,
            'plain_key' => $plainKey
        ];
    }

    /**
     * Scope pour les clés actives
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope pour les clés non expirées
     */
    public function scopeNotExpired($query)
    {
        return $query->where(function($q) {
            $q->whereNull('expires_at')
              ->orWhere('expires_at', '>', now());
        });
    }

    /**
     * Vérifier si la clé est expirée
     */
    public function isExpired(): bool
    {
        return $this->expires_at && $this->expires_at->isPast();
    }

    /**
     * Vérifier si la clé a une permission spécifique
     */
    public function hasPermission(string $permission): bool
    {
        $permissions = $this->permissions ?? [];
        return in_array($permission, $permissions);
    }
}
