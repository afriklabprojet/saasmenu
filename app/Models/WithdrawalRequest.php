<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class WithdrawalRequest extends Model
{
    use HasFactory;

    protected $fillable = [
        'vendor_id',
        'withdrawal_method_id',
        'amount',
        'fee_amount',
        'net_amount',
        'reference',
        'cinetpay_transaction_id',
        'status',
        'processed_at',
        'failed_reason',
        'notes'
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'fee_amount' => 'decimal:2',
        'net_amount' => 'decimal:2',
        'processed_at' => 'datetime'
    ];

    // Relations
    public function vendor()
    {
        return $this->belongsTo(User::class, 'vendor_id');
    }

    public function withdrawalMethod()
    {
        return $this->belongsTo(WithdrawalMethod::class);
    }

    public function wallet()
    {
        return $this->belongsTo(RestaurantWallet::class, 'vendor_id', 'vendor_id');
    }

    // Scopes
    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    public function scopeProcessing($query)
    {
        return $query->where('status', 'processing');
    }

    public function scopeCompleted($query)
    {
        return $query->where('status', 'completed');
    }

    public function scopeFailed($query)
    {
        return $query->where('status', 'failed');
    }

    public function scopeToday($query)
    {
        return $query->whereDate('created_at', Carbon::today());
    }

    // Accessors
    public function getStatusTextAttribute()
    {
        return match($this->status) {
            'pending' => 'En attente',
            'processing' => 'En cours',
            'completed' => 'Terminé',
            'failed' => 'Échoué',
            'cancelled' => 'Annulé',
            default => 'Inconnu'
        };
    }

    public function getStatusColorAttribute()
    {
        return match($this->status) {
            'pending' => 'warning',
            'processing' => 'info',
            'completed' => 'success',
            'failed' => 'danger',
            'cancelled' => 'secondary',
            default => 'light'
        };
    }

    public function getFormattedAmountAttribute()
    {
        return number_format($this->amount, 0, ',', ' ') . ' FCFA';
    }

    public function getFormattedFeeAmountAttribute()
    {
        return number_format($this->fee_amount, 0, ',', ' ') . ' FCFA';
    }

    public function getFormattedNetAmountAttribute()
    {
        return number_format($this->net_amount, 0, ',', ' ') . ' FCFA';
    }

    // Methods
    public function canBeCancelled()
    {
        return in_array($this->status, ['pending']);
    }

    public function canBeRetried()
    {
        return in_array($this->status, ['failed']);
    }

    public function markAsProcessing($cinetpay_id = null)
    {
        $this->update([
            'status' => 'processing',
            'cinetpay_transaction_id' => $cinetpay_id,
            'processed_at' => now()
        ]);
    }

    public function markAsCompleted()
    {
        $this->update([
            'status' => 'completed',
            'processed_at' => now()
        ]);

        // Créer la transaction wallet
        WalletTransaction::create([
            'vendor_id' => $this->vendor_id,
            'transaction_id' => 'WTH-' . $this->id . '-' . time(),
            'type' => 'debit',
            'amount' => $this->amount,
            'source' => 'withdrawal',
            'reference_id' => $this->id,
            'description' => 'Retrait vers ' . $this->withdrawalMethod->type_name,
            'status' => 'completed'
        ]);
    }

    public function markAsFailed($reason = null)
    {
        $this->update([
            'status' => 'failed',
            'failed_reason' => $reason,
            'processed_at' => now()
        ]);
    }

    public static function calculateFee($amount)
    {
        // 2% avec un minimum de 100 FCFA
        return max(100, $amount * 0.02);
    }

    public static function generateReference($vendor_id)
    {
        return 'WTH-' . $vendor_id . '-' . time() . '-' . rand(1000, 9999);
    }
}
