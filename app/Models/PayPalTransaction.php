<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PayPalTransaction extends Model
{
    use HasFactory;

    protected $table = 'paypal_transactions';

    protected $fillable = [
        'order_id',
        'paypal_payment_id',
        'paypal_order_id',
        'payer_id',
        'type',
        'status',
        'amount',
        'currency',
        'fee_amount',
        'net_amount',
        'refund_id',
        'refund_amount',
        'refund_status',
        'transaction_details',
        'webhook_data',
        'processed_at',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'fee_amount' => 'decimal:2',
        'net_amount' => 'decimal:2',
        'refund_amount' => 'decimal:2',
        'transaction_details' => 'array',
        'webhook_data' => 'array',
        'processed_at' => 'datetime',
    ];

    /**
     * Relation avec la commande
     */
    public function order()
    {
        return $this->belongsTo(Order::class);
    }

    /**
     * Obtenir le statut formaté
     */
    public function getStatusLabelAttribute()
    {
        $statuses = [
            'created' => 'Créé',
            'approved' => 'Approuvé',
            'completed' => 'Complété',
            'failed' => 'Échoué',
            'cancelled' => 'Annulé',
            'denied' => 'Refusé',
            'pending' => 'En attente',
            'refunded' => 'Remboursé',
        ];

        return $statuses[$this->status] ?? 'Inconnu';
    }

    /**
     * Obtenir le type formaté
     */
    public function getTypeLabelAttribute()
    {
        $types = [
            'express_checkout' => 'PayPal Express',
            'direct_credit_card' => 'Carte de crédit directe',
            'subscription' => 'Abonnement',
            'billing_agreement' => 'Accord de facturation',
            'refund' => 'Remboursement',
        ];

        return $types[$this->type] ?? 'Inconnu';
    }

    /**
     * Vérifier si la transaction est complétée
     */
    public function isCompleted()
    {
        return $this->status === 'completed';
    }

    /**
     * Vérifier si la transaction a échoué
     */
    public function isFailed()
    {
        return in_array($this->status, ['failed', 'denied', 'cancelled']);
    }

    /**
     * Vérifier si la transaction peut être remboursée
     */
    public function canBeRefunded()
    {
        return $this->isCompleted() && empty($this->refund_id);
    }

    /**
     * Obtenir le montant net (après frais PayPal)
     */
    public function getNetAmountAttribute()
    {
        return $this->amount - ($this->fee_amount ?? 0);
    }

    /**
     * Scope pour les transactions complétées
     */
    public function scopeCompleted($query)
    {
        return $query->where('status', 'completed');
    }

    /**
     * Scope pour les transactions échouées
     */
    public function scopeFailed($query)
    {
        return $query->whereIn('status', ['failed', 'denied', 'cancelled']);
    }

    /**
     * Scope pour une commande spécifique
     */
    public function scopeForOrder($query, $orderId)
    {
        return $query->where('order_id', $orderId);
    }

    /**
     * Scope pour une période donnée
     */
    public function scopeForPeriod($query, $startDate, $endDate)
    {
        return $query->whereBetween('created_at', [$startDate, $endDate]);
    }
}
