<?php

namespace App\Addons\RestaurantQrMenu\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Models\User;

class QrMenuDesign extends Model
{
    protected $fillable = [
        'vendor_id',
        'name',
        'logo_path',
        'background_color',
        'foreground_color',
        'size',
        'format',
        'custom_settings',
        'is_default'
    ];

    protected $casts = [
        'custom_settings' => 'array',
        'is_default' => 'boolean',
    ];

    /**
     * Relation avec le vendor
     */
    public function vendor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'vendor_id');
    }

    /**
     * Obtenir l'URL complète du logo
     */
    public function getLogoUrlAttribute(): ?string
    {
        return $this->logo_path ? asset('storage/' . $this->logo_path) : null;
    }

    /**
     * Scope pour les designs par défaut
     */
    public function scopeDefault($query)
    {
        return $query->where('is_default', true);
    }

    /**
     * Scope pour un vendor spécifique
     */
    public function scopeForVendor($query, int $vendorId)
    {
        return $query->where('vendor_id', $vendorId);
    }

    /**
     * Définir comme design par défaut pour un vendor
     */
    public function setAsDefault(): void
    {
        // Désactiver tous les autres designs par défaut du vendor
        static::where('vendor_id', $this->vendor_id)
            ->where('id', '!=', $this->id)
            ->update(['is_default' => false]);

        // Activer celui-ci
        $this->update(['is_default' => true]);
    }
}