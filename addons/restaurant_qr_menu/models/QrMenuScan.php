<?php

namespace App\Addons\RestaurantQrMenu\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class QrMenuScan extends Model
{
    protected $fillable = [
        'qr_menu_id',
        'ip_address',
        'user_agent',
        'device_type',
        'table_number',
        'location_data',
        'scanned_at'
    ];

    protected $casts = [
        'location_data' => 'array',
        'scanned_at' => 'datetime',
    ];

    /**
     * Relation avec le QR menu
     */
    public function qrMenu(): BelongsTo
    {
        return $this->belongsTo(RestaurantQrMenu::class);
    }

    /**
     * Obtenir le type d'appareil à partir du user agent
     */
    public function getDetectedDeviceTypeAttribute(): string
    {
        if (!$this->user_agent) {
            return 'unknown';
        }

        $userAgent = strtolower($this->user_agent);

        if (strpos($userAgent, 'mobile') !== false || strpos($userAgent, 'android') !== false) {
            return 'mobile';
        }

        if (strpos($userAgent, 'tablet') !== false || strpos($userAgent, 'ipad') !== false) {
            return 'tablet';
        }

        return 'desktop';
    }

    /**
     * Scope pour les scans récents
     */
    public function scopeRecent($query, int $days = 7)
    {
        return $query->where('scanned_at', '>=', now()->subDays($days));
    }

    /**
     * Scope pour un QR menu spécifique
     */
    public function scopeForQrMenu($query, int $qrMenuId)
    {
        return $query->where('qr_menu_id', $qrMenuId);
    }
}
