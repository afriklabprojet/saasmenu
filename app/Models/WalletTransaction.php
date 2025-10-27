<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class WalletTransaction extends Model
{
    use HasFactory;

    protected $fillable = [
        'vendor_id',
        'transaction_id',
        'type',
        'amount',
        'source',
        'reference_id',
        'description',
        'status'
    ];

    protected $casts = [
        'amount' => 'decimal:2'
    ];

    // Relations
    public function vendor()
    {
        return $this->belongsTo(User::class, 'vendor_id');
    }

    public function wallet()
    {
        return $this->belongsTo(RestaurantWallet::class, 'vendor_id', 'vendor_id');
    }

    // Scopes
    public function scopeCredits($query)
    {
        return $query->where('type', 'credit');
    }

    public function scopeDebits($query)
    {
        return $query->where('type', 'debit');
    }

    public function scopeCompleted($query)
    {
        return $query->where('status', 'completed');
    }

    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    // Accessors
    public function getFormattedAmountAttribute()
    {
        return number_format($this->amount, 0, ',', ' ') . ' FCFA';
    }

    public function getStatusColorAttribute()
    {
        return match($this->status) {
            'completed' => 'success',
            'pending' => 'warning',
            'failed' => 'danger',
            default => 'secondary'
        };
    }

    public function getTypeIconAttribute()
    {
        return match($this->type) {
            'credit' => 'fa-plus-circle text-success',
            'debit' => 'fa-minus-circle text-danger',
            default => 'fa-circle'
        };
    }
}
