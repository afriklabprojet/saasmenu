<?php

namespace App\Services;

use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class AuditService
{
    /**
     * Log a critical admin action for audit trail
     */
    public static function logAdminAction(string $action, string $entity, array $data = [], ?int $entityId = null): void
    {
        $auditData = [
            'timestamp' => Carbon::now()->toISOString(),
            'user_id' => Auth::id(),
            'user_email' => Auth::user()?->email ?? 'system',
            'user_type' => Auth::user()?->type ?? 'unknown',
            'action' => $action,
            'entity' => $entity,
            'entity_id' => $entityId,
            'data' => $data,
            'ip_address' => request()?->ip(),
            'user_agent' => request()?->userAgent(),
            'url' => request()?->fullUrl(),
        ];

        Log::channel('audit')->info('ADMIN_ACTION', $auditData);
    }

    /**
     * Log security events
     */
    public static function logSecurityEvent(string $event, array $data = []): void
    {
        $securityData = [
            'timestamp' => Carbon::now()->toISOString(),
            'event' => $event,
            'user_id' => Auth::id(),
            'ip_address' => request()?->ip(),
            'user_agent' => request()?->userAgent(),
            'data' => $data,
        ];

        Log::channel('security')->warning('SECURITY_EVENT', $securityData);
    }

    /**
     * Log payment transactions
     */
    public static function logPaymentTransaction(string $action, array $transactionData): void
    {
        $paymentLog = [
            'timestamp' => Carbon::now()->toISOString(),
            'action' => $action,
            'user_id' => Auth::id(),
            'vendor_id' => $transactionData['vendor_id'] ?? null,
            'order_id' => $transactionData['order_id'] ?? null,
            'amount' => $transactionData['amount'] ?? null,
            'payment_method' => $transactionData['payment_method'] ?? null,
            'status' => $transactionData['status'] ?? null,
            'ip_address' => request()?->ip(),
        ];

        Log::channel('payment')->info('PAYMENT_TRANSACTION', $paymentLog);
    }

    /**
     * Log GDPR compliance actions
     */
    public static function logGDPRAction(string $action, int $customerId, array $data = []): void
    {
        $gdprData = [
            'timestamp' => Carbon::now()->toISOString(),
            'action' => $action,
            'customer_id' => $customerId,
            'requested_by' => Auth::id(),
            'data' => $data,
            'ip_address' => request()?->ip(),
        ];

        Log::channel('gdpr')->info('GDPR_ACTION', $gdprData);
    }
}