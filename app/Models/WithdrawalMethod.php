<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class WithdrawalMethod extends Model
{
    use HasFactory;

    protected $fillable = [
        'vendor_id',
        'type',
        'account_number',
        'account_name',
        'additional_info',
        'is_active',
        'is_verified'
    ];

    protected $casts = [
        'additional_info' => 'array',
        'is_active' => 'boolean',
        'is_verified' => 'boolean'
    ];

    // Relations
    public function vendor()
    {
        return $this->belongsTo(User::class, 'vendor_id');
    }

    public function withdrawalRequests()
    {
        return $this->hasMany(WithdrawalRequest::class, 'withdrawal_method_id');
    }

    // Scopes
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeVerified($query)
    {
        return $query->where('is_verified', true);
    }

    // Accessors
    public function getTypeNameAttribute()
    {
        return match($this->type) {
            'orange_money' => 'Orange Money',
            'mtn_money' => 'MTN Money',
            'moov_money' => 'Moov Money',
            'bank_transfer' => 'Virement Bancaire',
            'cinetpay' => 'CinetPay',
            default => ucfirst(str_replace('_', ' ', $this->type))
        };
    }

    public function getFormattedAccountNumberAttribute()
    {
        if (in_array($this->type, ['orange_money', 'mtn_money', 'moov_money'])) {
            // Format phone number: +225 XX XX XX XX XX
            $number = preg_replace('/[^0-9]/', '', $this->account_number);
            if (strlen($number) >= 10) {
                return '+225 ' . substr($number, -10, 2) . ' ' . substr($number, -8, 2) . ' ' . substr($number, -6, 2) . ' ' . substr($number, -4, 2) . ' ' . substr($number, -2);
            }
        }

        return $this->account_number;
    }

    public function getIconAttribute()
    {
        return match($this->type) {
            'orange_money' => 'fab fa-cc-mastercard text-orange',
            'mtn_money' => 'fab fa-cc-mastercard text-warning',
            'moov_money' => 'fab fa-cc-mastercard text-primary',
            'bank_transfer' => 'fas fa-university',
            'cinetpay' => 'fas fa-credit-card',
            default => 'fas fa-wallet'
        };
    }

    // Methods
    public function canBeUsedForWithdrawal()
    {
        return $this->is_active && $this->is_verified;
    }

    public function validateAccountNumber()
    {
        switch ($this->type) {
            case 'orange_money':
            case 'mtn_money':
            case 'moov_money':
                // Valider format téléphone mobile money
                return preg_match('/^(\+225|0)[0-9]{10}$/', preg_replace('/[^0-9+]/', '', $this->account_number));

            case 'bank_transfer':
                // Validation basique pour compte bancaire
                return strlen(preg_replace('/[^0-9A-Z]/', '', strtoupper($this->account_number))) >= 10;

            default:
                return true;
        }
    }
}
