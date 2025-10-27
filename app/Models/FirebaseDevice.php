<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class FirebaseDevice extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'firebase_devices';

    protected $fillable = [
        'user_id',
        'device_token',
        'device_type',
        'device_name',
        'device_model',
        'device_os',
        'app_version',
        'os_version',
        'is_active',
        'last_seen_at',
        'topics',
        'preferences',
        'timezone',
        'language',
        'metadata',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'last_seen_at' => 'datetime',
        'topics' => 'array',
        'preferences' => 'array',
        'metadata' => 'array',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime',
    ];

    protected $attributes = [
        'is_active' => true,
        'topics' => '[]',
        'preferences' => '[]',
        'metadata' => '[]',
    ];

    /**
     * Relation avec l'utilisateur
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Relation avec les notifications
     */
    public function notifications()
    {
        return $this->hasMany(FirebaseNotification::class, 'device_id');
    }

    /**
     * Obtenir les appareils actifs
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Obtenir les appareils par type
     */
    public function scopeByType($query, $type)
    {
        return $query->where('device_type', $type);
    }

    /**
     * Obtenir les appareils récents
     */
    public function scopeRecent($query, $days = 30)
    {
        return $query->where('last_seen_at', '>=', now()->subDays($days));
    }

    /**
     * Obtenir les appareils souscrits à un topic
     */
    public function scopeSubscribedToTopic($query, $topic)
    {
        return $query->whereJsonContains('topics', $topic);
    }

    /**
     * Marquer l'appareil comme vu
     */
    public function markAsSeen()
    {
        $this->update(['last_seen_at' => now()]);
    }

    /**
     * Activer l'appareil
     */
    public function activate()
    {
        $this->update(['is_active' => true]);
    }

    /**
     * Désactiver l'appareil
     */
    public function deactivate()
    {
        $this->update(['is_active' => false]);
    }

    /**
     * Souscrire à un topic
     */
    public function subscribeToTopic($topic)
    {
        $topics = $this->topics ?? [];
        if (!in_array($topic, $topics)) {
            $topics[] = $topic;
            $this->update(['topics' => $topics]);
        }
    }

    /**
     * Se désabonner d'un topic
     */
    public function unsubscribeFromTopic($topic)
    {
        $topics = $this->topics ?? [];
        $topics = array_filter($topics, function($t) use ($topic) {
            return $t !== $topic;
        });
        $this->update(['topics' => array_values($topics)]);
    }

    /**
     * Vérifier si souscrit à un topic
     */
    public function isSubscribedToTopic($topic)
    {
        return in_array($topic, $this->topics ?? []);
    }

    /**
     * Mettre à jour les préférences
     */
    public function updatePreferences($preferences)
    {
        $current = $this->preferences ?? [];
        $updated = array_merge($current, $preferences);
        $this->update(['preferences' => $updated]);
    }

    /**
     * Obtenir une préférence
     */
    public function getPreference($key, $default = null)
    {
        return ($this->preferences ?? [])[$key] ?? $default;
    }

    /**
     * Vérifier si l'appareil est récent
     */
    public function isRecent($days = 7)
    {
        return $this->last_seen_at && $this->last_seen_at->gte(now()->subDays($days));
    }

    /**
     * Obtenir le statut de l'appareil
     */
    public function getStatusAttribute()
    {
        if (!$this->is_active) {
            return 'inactive';
        }

        if ($this->isRecent(1)) {
            return 'online';
        }

        if ($this->isRecent(7)) {
            return 'recent';
        }

        return 'offline';
    }

    /**
     * Obtenir l'icône du type d'appareil
     */
    public function getDeviceIconAttribute()
    {
        return match($this->device_type) {
            'android' => 'fab fa-android',
            'ios' => 'fab fa-apple',
            'web' => 'fas fa-globe',
            default => 'fas fa-mobile-alt'
        };
    }

    /**
     * Obtenir la description de l'appareil
     */
    public function getDescriptionAttribute()
    {
        $parts = [];

        if ($this->device_name) {
            $parts[] = $this->device_name;
        }

        if ($this->device_model) {
            $parts[] = $this->device_model;
        }

        if ($this->os_version) {
            $parts[] = $this->device_type . ' ' . $this->os_version;
        }

        return implode(' - ', $parts) ?: 'Appareil inconnu';
    }

    /**
     * Nettoyer les appareils inactifs
     */
    public static function cleanupInactiveDevices($days = 90)
    {
        return static::where('last_seen_at', '<', now()->subDays($days))
            ->orWhere('is_active', false)
            ->delete();
    }

    /**
     * Statistiques des appareils
     */
    public static function getStats()
    {
        return [
            'total' => static::count(),
            'active' => static::active()->count(),
            'recent' => static::recent(7)->count(),
            'by_type' => static::selectRaw('device_type, COUNT(*) as count')
                ->groupBy('device_type')
                ->pluck('count', 'device_type')
                ->toArray(),
            'by_status' => [
                'online' => static::active()->recent(1)->count(),
                'recent' => static::active()->recent(7)->count(),
                'offline' => static::active()->where('last_seen_at', '<', now()->subDays(7))->count(),
                'inactive' => static::where('is_active', false)->count(),
            ]
        ];
    }
}
