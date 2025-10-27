<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RestaurantWallet extends Model
{
    use HasFactory;

    protected $fillable = [
        'vendor_id',
        'balance',
        'pending_balance', 
        'total_earnings',
        'total_withdrawn'
    ];

    protected $casts = [
        'balance' => 'decimal:2',
        'pending_balance' => 'decimal:2',
        'total_earnings' => 'decimal:2',
        'total_withdrawn' => 'decimal:2'
    ];

    // Relations
    public function vendor()
    {
        return $this->belongsTo(User::class, 'vendor_id');
    }

    public function transactions()
    {
        return $this->hasMany(WalletTransaction::class, 'vendor_id', 'vendor_id');
    }

    public function withdrawalMethods()
    {
        return $this->hasMany(WithdrawalMethod::class, 'vendor_id', 'vendor_id');
    }

    // Methods
    public function addFunds($amount, $source, $description, $reference_id = null)
    {
        $this->increment('balance', $amount);
        $this->increment('total_earnings', $amount);

        return WalletTransaction::create([
            'vendor_id' => $this->vendor_id,
            'transaction_id' => 'TXN_' . uniqid(),
            'type' => 'credit',
            'amount' => $amount,
            'source' => $source,
            'reference_id' => $reference_id,
            'description' => $description,
            'status' => 'completed'
        ]);
    }

    public function deductFunds($amount, $source, $description, $reference_id = null)
    {
        if ($this->balance >= $amount) {
            $this->decrement('balance', $amount);
            $this->increment('total_withdrawn', $amount);

            return WalletTransaction::create([
                'vendor_id' => $this->vendor_id,
                'transaction_id' => 'TXN_' . uniqid(),
                'type' => 'debit',
                'amount' => $amount,
                'source' => $source,
                'reference_id' => $reference_id,
                'description' => $description,
                'status' => 'completed'
            ]);
        }
        
        return false;
    }

    public function getAvailableBalance()
    {
        return $this->balance - $this->pending_balance;
    }

    public function canWithdraw($amount)
    {
        return $this->getAvailableBalance() >= $amount;
    }
}