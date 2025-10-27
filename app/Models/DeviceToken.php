<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class DeviceToken extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'device_token',
        'device_type',
        'device_info',
        'app_version',
        'is_active',
        'last_used_at',
    ];

    protected $casts = [
        'device_info' => 'array',
        'is_active' => 'boolean',
        'last_used_at' => 'datetime',
    ];

    /**
     * Relation avec l'utilisateur
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Scope pour les tokens actifs
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope pour les tokens expirés
     */
    public function scopeExpired($query, $days = 30)
    {
        $cutoffDate = Carbon::now()->subDays($days);
        return $query->where('last_used_at', '<', $cutoffDate)
            ->orWhere('is_active', false);
    }

    /**
     * Scope pour un type d'appareil spécifique
     */
    public function scopeDeviceType($query, $type)
    {
        return $query->where('device_type', $type);
    }

    /**
     * Marquer le token comme utilisé
     */
    public function markAsUsed()
    {
        $this->update([
            'last_used_at' => now(),
            'is_active' => true
        ]);
    }

    /**
     * Désactiver le token
     */
    public function deactivate()
    {
        $this->update(['is_active' => false]);
    }

    /**
     * Vérifier si le token est expiré
     */
    public function getIsExpiredAttribute()
    {
        if (!$this->last_used_at) {
            return false;
        }

        return $this->last_used_at->diffInDays(now()) > 30;
    }

    /**
     * Obtenir les informations formatées de l'appareil
     */
    public function getFormattedDeviceInfoAttribute()
    {
        $info = $this->device_info ?: [];
        
        return [
            'platform' => $info['platform'] ?? 'Unknown',
            'version' => $info['version'] ?? 'Unknown',
            'model' => $info['model'] ?? 'Unknown',
            'brand' => $info['brand'] ?? 'Unknown'
        ];
    }
}