<?php

namespace App\Helpers;

use App\Models\Payment;
use App\Models\Order;
use Illuminate\Support\Facades\Log;

class CinetPayHelper
{
    /**
     * Get CinetPay configuration for a vendor
     */
    public static function getConfig($vendor_id)
    {
        return Payment::where('payment_type', '16')
            ->where('vendor_id', $vendor_id)
            ->where('is_available', 1)
            ->first();
    }

    /**
     * Format amount for CinetPay (must be integer)
     */
    public static function formatAmount($amount)
    {
        return (int)round($amount);
    }

    /**
     * Generate unique transaction ID
     */
    public static function generateTransactionId($order_number)
    {
        return $order_number . '_' . time() . '_' . rand(1000, 9999);
    }

    /**
     * Validate CinetPay webhook signature
     */
    public static function validateWebhook($request)
    {
        // Basic validation for required fields
        $required = ['cpm_trans_id', 'cpm_result'];
        
        foreach ($required as $field) {
            if (!$request->has($field)) {
                return false;
            }
        }
        
        return true;
    }

    /**
     * Get payment status text
     */
    public static function getStatusText($status)
    {
        $statuses = [
            'ACCEPTED' => 'Payment successful',
            'REFUSED' => 'Payment refused',
            'CANCELLED' => 'Payment cancelled',
            'PENDING' => 'Payment pending',
        ];

        return $statuses[$status] ?? 'Unknown status';
    }

    /**
     * Log CinetPay transaction
     */
    public static function logTransaction($type, $data, $response = null)
    {
        Log::channel('cinetpay')->info("CinetPay {$type}", [
            'request_data' => $data,
            'response' => $response,
            'timestamp' => now(),
        ]);
    }

    /**
     * Get supported currencies
     */
    public static function getSupportedCurrencies()
    {
        return config('cinetpay.supported_currencies', ['XOF', 'XAF', 'USD', 'EUR']);
    }

    /**
     * Check if currency is supported
     */
    public static function isCurrencySupported($currency)
    {
        return in_array(strtoupper($currency), self::getSupportedCurrencies());
    }

    /**
     * Get API endpoint based on environment
     */
    public static function getApiUrl($environment, $endpoint = 'payment')
    {
        $urls = config('cinetpay.api_url');
        
        if ($endpoint === 'check') {
            $urls = config('cinetpay.check_url');
        }
        
        return $environment === 'sandbox' ? $urls['sandbox'] : $urls['live'];
    }

    /**
     * Format phone number for CinetPay
     */
    public static function formatPhoneNumber($phone, $country_code = '+225')
    {
        // Remove all non-numeric characters
        $phone = preg_replace('/[^0-9]/', '', $phone);
        
        // Add country code if not present
        if (!str_starts_with($phone, substr($country_code, 1))) {
            $phone = substr($country_code, 1) . $phone;
        }
        
        return $phone;
    }

    /**
     * Validate CinetPay credentials
     */
    public static function validateCredentials($api_key, $site_id, $environment = 'sandbox')
    {
        if (empty($api_key) || empty($site_id)) {
            return false;
        }

        // Basic format validation
        if (strlen($api_key) < 10 || strlen($site_id) < 3) {
            return false;
        }

        return true;
    }

    /**
     * Get error message in user language
     */
    public static function getErrorMessage($code)
    {
        $messages = [
            '201' => 'Payment link created successfully',
            '402' => 'Invalid credentials',
            '404' => 'Transaction not found', 
            '400' => 'Bad request parameters',
            '500' => 'Server error',
        ];

        return $messages[$code] ?? 'Unknown error occurred';
    }

    /**
     * Calculate CinetPay fees (estimation)
     */
    public static function calculateFees($amount, $currency = 'XOF', $method = 'mobile_money')
    {
        // Estimation des frais CinetPay
        $fees = [
            'mobile_money' => ['rate' => 0.025, 'fixed' => 0], // 2.5%
            'card' => ['rate' => 0.035, 'fixed' => 100], // 3.5% + 100 XOF
            'bank' => ['rate' => 0.02, 'fixed' => 500], // 2% + 500 XOF
        ];

        $fee_config = $fees[$method] ?? $fees['mobile_money'];
        
        return ($amount * $fee_config['rate']) + $fee_config['fixed'];
    }
}