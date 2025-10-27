<?php

namespace App\Addons\RestaurantQrMenu\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use App\Models\User;

class RestaurantQrMenu extends Model
{
    protected $fillable = [
        'vendor_id',
        'name',
        'slug',
        'description',
        'qr_code_path',
        'menu_url',
        'table_numbers',
        'settings',
        'is_active',
        'scan_count',
        'last_scanned_at'
    ];

    protected $casts = [
        'table_numbers' => 'array',
        'settings' => 'array',
        'is_active' => 'boolean',
        'last_scanned_at' => 'datetime',
    ];

    /**
     * Relation avec le vendor/restaurant
     */
    public function vendor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'vendor_id');
    }

    /**
     * Relation avec les scans
     */
    public function scans(): HasMany
    {
        return $this->hasMany(QrMenuScan::class);
    }

    /**
     * Incrémenter le compteur de scans
     */
    public function incrementScanCount(): void
    {
        $this->increment('scan_count');
        $this->update(['last_scanned_at' => now()]);
    }

    /**
     * Obtenir l'URL complète du QR code
     */
    public function getQrCodeUrlAttribute(): string
    {
        return asset('storage/' . $this->qr_code_path);
    }

    /**
     * Obtenir les statistiques de scans
     */
    public function getScanStats(int $days = 30): array
    {
        $scans = $this->scans()
            ->where('scanned_at', '>=', now()->subDays($days))
            ->selectRaw('DATE(scanned_at) as date, COUNT(*) as count')
            ->groupBy('date')
            ->orderBy('date')
            ->get();

        return [
            'total_scans' => $this->scan_count,
            'recent_scans' => $scans->sum('count'),
            'daily_stats' => $scans->toArray(),
            'last_scan' => $this->last_scanned_at,
        ];
    }

    /**
     * Scope pour les QR codes actifs
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope pour un vendor spécifique
     */
    public function scopeForVendor($query, int $vendorId)
    {
        return $query->where('vendor_id', $vendorId);
    }
}
