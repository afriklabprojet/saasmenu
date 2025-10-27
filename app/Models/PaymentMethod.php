<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PaymentMethod extends Model
{
    use HasFactory;

    protected $fillable = [
        'type',
        'name',
        'image',
        'credentials',
        'status',
        'position'
    ];

    protected $casts = [
        'credentials' => 'array'
    ];

    // Constants pour les types de paiement
    const COD = 1;
    const STRIPE = 2;
    const RAZORPAY = 3;
    const PAYPAL = 4;
    const MOLLIE = 5;
    const FLUTTERWAVE = 6;
    const PAYSTACK = 7;
    const MERCADOPAGO = 8;
    const PAYTAB = 9;
    const MYFATOORAH = 10;
    const TOYYIBPAY = 11;
    const PHONEPE = 12;
    const KHALTI = 13;
    const XENDIT = 14;
    const SADADPAY = 15;
    const CINETPAY = 16;

    /**
     * Scope pour les méthodes de paiement actives
     */
    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    /**
     * Scope pour trier par position
     */
    public function scopeOrdered($query)
    {
        return $query->orderBy('position');
    }

    /**
     * Obtenir la méthode de paiement par type
     */
    public static function getByType($type)
    {
        return self::where('type', $type)->first();
    }

    /**
     * Vérifier si une méthode de paiement est active
     */
    public static function isActive($type)
    {
        $method = self::getByType($type);
        return $method && $method->status === 'active';
    }

    /**
     * Obtenir les credentials de CinetPay
     */
    public static function getCinetPayCredentials()
    {
        $cinetpay = self::getByType(self::CINETPAY);
        return $cinetpay ? $cinetpay->credentials : null;
    }
}
