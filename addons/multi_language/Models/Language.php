<?php

namespace Addons\MultiLanguage\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Modèle pour les langues
 */
class Language extends Model
{
    protected $table = 'languages';

    protected $fillable = [
        'code',
        'name',
        'native_name',
        'flag',
        'direction',
        'is_active',
        'is_default'
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'is_default' => 'boolean'
    ];

    /**
     * Scope pour les langues actives
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Obtenir la langue par défaut
     */
    public static function getDefault()
    {
        return static::where('is_default', true)->first();
    }

    /**
     * Obtenir toutes les langues actives
     */
    public static function getActive()
    {
        return static::active()->orderBy('name')->get();
    }
}
