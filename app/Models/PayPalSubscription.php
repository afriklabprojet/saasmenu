<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PayPalSubscription extends Model
{
    use HasFactory;

    protected $table = 'paypal_subscriptions';

    protected $fillable = [
        'user_id',
        'restaurant_id',
        'paypal_subscription_id',
        'plan_id',
        'status',
        'name',
        'description',
        'amount',
        'currency',
        'billing_cycle',
        'billing_frequency',
        'start_date',
        'next_billing_date',
        'cancelled_at',
        'failure_count',
        'subscription_details',
        'webhook_data',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'start_date' => 'datetime',
        'next_billing_date' => 'datetime',
        'cancelled_at' => 'datetime',
        'failure_count' => 'integer',
        'subscription_details' => 'array',
        'webhook_data' => 'array',
    ];

    /**
     * Relation avec l'utilisateur
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Relation avec le restaurant
     */
    public function restaurant()
    {
        return $this->belongsTo(Restaurant::class);
    }

    /**
     * Transactions liées à cet abonnement
     */
    public function transactions()
    {
        return $this->hasMany(PayPalTransaction::class, 'subscription_id');
    }

    /**
     * Obtenir le statut formaté
     */
    public function getStatusLabelAttribute()
    {
        $statuses = [
            'active' => 'Actif',
            'cancelled' => 'Annulé',
            'suspended' => 'Suspendu',
            'expired' => 'Expiré',
            'pending' => 'En attente',
            'approval_pending' => 'Approbation en attente',
        ];

        return $statuses[$this->status] ?? 'Inconnu';
    }

    /**
     * Obtenir le cycle de facturation formaté
     */
    public function getBillingCycleLabelAttribute()
    {
        $cycles = [
            'monthly' => 'Mensuel',
            'yearly' => 'Annuel',
            'weekly' => 'Hebdomadaire',
            'daily' => 'Quotidien',
        ];

        return $cycles[$this->billing_cycle] ?? 'Inconnu';
    }

    /**
     * Vérifier si l'abonnement est actif
     */
    public function isActive()
    {
        return $this->status === 'active';
    }

    /**
     * Vérifier si l'abonnement est annulé
     */
    public function isCancelled()
    {
        return $this->status === 'cancelled';
    }

    /**
     * Vérifier si l'abonnement peut être annulé
     */
    public function canBeCancelled()
    {
        return in_array($this->status, ['active', 'suspended']);
    }

    /**
     * Obtenir le prochain montant de facturation
     */
    public function getNextBillingAmount()
    {
        return $this->amount;
    }

    /**
     * Calculer le nombre de jours restants avant la prochaine facturation
     */
    public function getDaysUntilNextBilling()
    {
        if (!$this->next_billing_date) {
            return null;
        }

        return now()->diffInDays($this->next_billing_date, false);
    }

    /**
     * Marquer comme annulé
     */
    public function markAsCancelled()
    {
        $this->update([
            'status' => 'cancelled',
            'cancelled_at' => now(),
        ]);
    }

    /**
     * Incrementer le compteur d'échecs
     */
    public function incrementFailureCount()
    {
        $this->increment('failure_count');

        // Suspendre après 3 échecs
        if ($this->failure_count >= 3) {
            $this->update(['status' => 'suspended']);
        }
    }

    /**
     * Réinitialiser le compteur d'échecs
     */
    public function resetFailureCount()
    {
        $this->update(['failure_count' => 0]);
    }

    /**
     * Scope pour les abonnements actifs
     */
    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    /**
     * Scope pour les abonnements annulés
     */
    public function scopeCancelled($query)
    {
        return $query->where('status', 'cancelled');
    }

    /**
     * Scope pour un utilisateur spécifique
     */
    public function scopeForUser($query, $userId)
    {
        return $query->where('user_id', $userId);
    }

    /**
     * Scope pour un restaurant spécifique
     */
    public function scopeForRestaurant($query, $restaurantId)
    {
        return $query->where('restaurant_id', $restaurantId);
    }

    /**
     * Scope pour la facturation due
     */
    public function scopeDueForBilling($query)
    {
        return $query->where('status', 'active')
                    ->where('next_billing_date', '<=', now());
    }
}
