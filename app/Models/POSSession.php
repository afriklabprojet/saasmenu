<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class POSSession extends Model
{
    use HasFactory;

    protected $table = 'pos_sessions';

    protected $fillable = [
        'restaurant_id',
        'terminal_id',
        'user_id',
        'opening_cash',
        'closing_cash',
        'expected_cash',
        'cash_difference',
        'total_sales',
        'total_transactions',
        'opened_at',
        'closed_at',
        'status',
        'notes',
    ];

    protected $casts = [
        'opening_cash' => 'decimal:2',
        'closing_cash' => 'decimal:2',
        'expected_cash' => 'decimal:2',
        'cash_difference' => 'decimal:2',
        'total_sales' => 'decimal:2',
        'total_transactions' => 'integer',
        'opened_at' => 'datetime',
        'closed_at' => 'datetime',
    ];

    /**
     * Status possibles
     */
    const STATUS_ACTIVE = 'active';
    const STATUS_CLOSED = 'closed';
    const STATUS_RECONCILED = 'reconciled';

    /**
     * Restaurant de cette session
     */
    public function restaurant()
    {
        return $this->belongsTo(Restaurant::class);
    }

    /**
     * Terminal utilisé
     */
    public function terminal()
    {
        return $this->belongsTo(POSTerminal::class, 'terminal_id');
    }

    /**
     * Utilisateur de la session
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Commandes de cette session
     */
    public function orders()
    {
        return $this->hasMany(Order::class, 'pos_session_id');
    }

    /**
     * Paiements de cette session
     */
    public function payments()
    {
        return $this->hasMany(Payment::class, 'pos_session_id');
    }

    /**
     * Scope pour les sessions actives
     */
    public function scopeActive($query)
    {
        return $query->where('status', self::STATUS_ACTIVE);
    }

    /**
     * Scope pour les sessions fermées
     */
    public function scopeClosed($query)
    {
        return $query->where('status', self::STATUS_CLOSED);
    }

    /**
     * Calculer la durée de la session
     */
    public function getDurationAttribute()
    {
        if (!$this->opened_at) {
            return 0;
        }

        $endTime = $this->closed_at ?? now();
        return $this->opened_at->diffInMinutes($endTime);
    }

    /**
     * Vérifier si la session est active
     */
    public function isActive()
    {
        return $this->status === self::STATUS_ACTIVE;
    }

    /**
     * Fermer la session
     */
    public function close($closingCash, $notes = null)
    {
        $this->update([
            'status' => self::STATUS_CLOSED,
            'closing_cash' => $closingCash,
            'closed_at' => now(),
            'notes' => $notes,
            'cash_difference' => $closingCash - $this->expected_cash,
        ]);
    }

    /**
     * Calculer les ventes totales
     */
    public function calculateTotalSales()
    {
        return $this->orders()
            ->where('payment_status', 'completed')
            ->sum('total_amount');
    }

    /**
     * Obtenir le rapport de session
     */
    public function getReport()
    {
        $orders = $this->orders()->with('payments')->get();

        $paymentMethods = $orders->flatMap->payments
            ->groupBy('payment_method')
            ->map(function($payments) {
                return [
                    'count' => $payments->count(),
                    'total' => $payments->sum('amount'),
                ];
            });

        return [
            'session_id' => $this->id,
            'terminal' => $this->terminal->name,
            'user' => $this->user->name,
            'duration' => $this->duration,
            'opened_at' => $this->opened_at,
            'closed_at' => $this->closed_at,
            'opening_cash' => $this->opening_cash,
            'closing_cash' => $this->closing_cash,
            'expected_cash' => $this->expected_cash,
            'cash_difference' => $this->cash_difference,
            'total_orders' => $orders->count(),
            'total_sales' => $orders->sum('total_amount'),
            'payment_methods' => $paymentMethods,
            'status' => $this->status,
        ];
    }
}
