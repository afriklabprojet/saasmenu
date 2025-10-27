<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SocialAccount extends Model
{
    use HasFactory;

    protected $table = 'social_accounts';

    protected $fillable = [
        'user_id',
        'provider',
        'provider_id',
        'provider_token',
        'provider_refresh_token',
        'provider_expires_at',
        'avatar',
        'profile_data',
        'last_login_at',
        'is_active',
    ];

    protected $casts = [
        'profile_data' => 'array',
        'provider_expires_at' => 'datetime',
        'last_login_at' => 'datetime',
        'is_active' => 'boolean',
    ];

    /**
     * Relation avec l'utilisateur
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Obtenir le nom du provider formaté
     */
    public function getProviderNameAttribute()
    {
        $providers = [
            'google' => 'Google',
            'facebook' => 'Facebook',
            'twitter' => 'Twitter',
            'github' => 'GitHub',
            'linkedin' => 'LinkedIn',
        ];

        return $providers[$this->provider] ?? ucfirst($this->provider);
    }

    /**
     * Vérifier si le token a expiré
     */
    public function isTokenExpired()
    {
        if (!$this->provider_expires_at) {
            return false;
        }

        return $this->provider_expires_at->isPast();
    }

    /**
     * Obtenir l'avatar avec fallback
     */
    public function getAvatarUrlAttribute()
    {
        if ($this->avatar) {
            return $this->avatar;
        }

        // Fallback basé sur le provider
        if ($this->provider === 'google' && isset($this->profile_data['picture'])) {
            return $this->profile_data['picture'];
        }

        if ($this->provider === 'facebook' && isset($this->profile_data['picture']['data']['url'])) {
            return $this->profile_data['picture']['data']['url'];
        }

        return null;
    }

    /**
     * Obtenir le nom d'affichage
     */
    public function getDisplayNameAttribute()
    {
        if (isset($this->profile_data['name'])) {
            return $this->profile_data['name'];
        }

        return $this->user->name ?? 'Utilisateur';
    }

    /**
     * Obtenir l'email du profil social
     */
    public function getProfileEmailAttribute()
    {
        return $this->profile_data['email'] ?? null;
    }

    /**
     * Marquer la dernière connexion
     */
    public function markAsLoggedIn()
    {
        $this->update([
            'last_login_at' => now(),
            'is_active' => true,
        ]);
    }

    /**
     * Désactiver le compte social
     */
    public function deactivate()
    {
        $this->update(['is_active' => false]);
    }

    /**
     * Scope pour un provider spécifique
     */
    public function scopeForProvider($query, $provider)
    {
        return $query->where('provider', $provider);
    }

    /**
     * Scope pour les comptes actifs
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope pour les tokens expirés
     */
    public function scopeExpiredTokens($query)
    {
        return $query->where('provider_expires_at', '<', now())
                    ->whereNotNull('provider_expires_at');
    }

    /**
     * Scope pour un utilisateur spécifique
     */
    public function scopeForUser($query, $userId)
    {
        return $query->where('user_id', $userId);
    }

    /**
     * Obtenir les informations formatées pour l'API
     */
    public function toApiArray()
    {
        return [
            'id' => $this->id,
            'provider' => $this->provider,
            'provider_name' => $this->provider_name,
            'provider_id' => $this->provider_id,
            'avatar' => $this->avatar_url,
            'display_name' => $this->display_name,
            'email' => $this->profile_email,
            'is_active' => $this->is_active,
            'last_login_at' => $this->last_login_at?->toISOString(),
            'token_expired' => $this->isTokenExpired(),
        ];
    }

    /**
     * Boot du modèle
     */
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($socialAccount) {
            $socialAccount->is_active = true;
            $socialAccount->last_login_at = now();
        });
    }
}
