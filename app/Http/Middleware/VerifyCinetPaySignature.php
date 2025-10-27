<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class VerifyCinetPaySignature
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure(\Illuminate\Http\Request): (\Illuminate\Http\Response|\Illuminate\Http\RedirectResponse)  $next
     * @return \Illuminate\Http\Response|\Illuminate\Http\RedirectResponse
     */
    public function handle(Request $request, Closure $next)
    {
        // CinetPay webhook verification
        if ($request->isMethod('post') && $request->is('cinetpay/notify')) {
            
            // Log the webhook for debugging
            Log::info('CinetPay webhook received', [
                'headers' => $request->headers->all(),
                'body' => $request->all(),
                'ip' => $request->ip(),
            ]);

            // Basic validation - CinetPay sends these fields
            $requiredFields = ['cpm_trans_id', 'cpm_result'];
            
            foreach ($requiredFields as $field) {
                if (!$request->has($field)) {
                    Log::warning('CinetPay webhook missing required field: ' . $field);
                    return response('Missing required field: ' . $field, 400);
                }
            }

            // Optional: Verify IP whitelist (CinetPay IPs)
            $allowedIps = [
                '41.207.170.17',
                '41.207.170.18', 
                '41.207.170.19',
                '154.70.74.18',
                '154.70.74.19',
                '154.70.74.20',
                // Add more CinetPay IPs as needed
            ];

            // In production, you might want to verify the source IP
            // if (!in_array($request->ip(), $allowedIps)) {
            //     Log::warning('CinetPay webhook from unauthorized IP: ' . $request->ip());
            //     return response('Unauthorized', 403);
            // }
        }

        return $next($request);
    }
}