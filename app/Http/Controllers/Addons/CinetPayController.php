<?php

namespace App\Http\Controllers\Addons;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Order;
use App\Models\Settings;
use App\Models\Payment;
use App\Helpers\helper;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Config;
use App\Helpers\CinetPayHelper;

class CinetPayController extends Controller
{
    private function getCinetPayConfig($vendor_id)
    {
        return CinetPayHelper::getConfig($vendor_id);
    }

    public function initPayment(Request $request)
    {
        try {
            $vendor_id = $request->vendor_id;
            $order_number = $request->order_number;
            $amount = $request->amount;
            $customer_email = $request->customer_email;
            $customer_name = $request->customer_name;
            $customer_phone = $request->customer_phone;

            // Get CinetPay configuration
            $cinetpay_config = $this->getCinetPayConfig($vendor_id);

            if (!$cinetpay_config || $cinetpay_config->is_available != 1) {
                return response()->json(['status' => 0, 'message' => 'CinetPay not available'], 400);
            }

            // Get store info for return URLs
            $storeinfo = helper::storeinfo($vendor_id);

            // Prepare CinetPay payment data
            $data = [
                'apikey' => $cinetpay_config->public_key,
                'site_id' => $cinetpay_config->secret_key,
                'transaction_id' => $order_number . '_' . time(),
                'amount' => (int)($amount),
                'currency' => $cinetpay_config->currency ?? 'XOF',
                'alternative_currency' => '',
                'description' => 'Order #' . $order_number . ' from ' . $storeinfo->name,
                'customer_id' => $customer_email,
                'customer_name' => $customer_name,
                'customer_surname' => '',
                'customer_email' => $customer_email,
                'customer_phone_number' => $customer_phone,
                'customer_address' => '',
                'customer_city' => '',
                'customer_country' => 'CI',
                'customer_state' => '',
                'customer_zip_code' => '',
                'return_url' => URL::to($storeinfo->slug . '/cinetpay/return'),
                'notify_url' => URL::to('/cinetpay/notify'),
                'channels' => 'ALL',
                'metadata' => json_encode([
                    'vendor_id' => $vendor_id,
                    'order_number' => $order_number
                ])
            ];

            // CinetPay API endpoint
            $api_url = $cinetpay_config->environment == 'sandbox'
                ? 'https://api-checkout.cinetpay.com/v2/payment'
                : 'https://api-checkout.cinetpay.com/v2/payment';

            // Make API call to CinetPay
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $api_url);
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_HTTPHEADER, [
                'Content-Type: application/json',
                'Accept: application/json'
            ]);

            $response = curl_exec($ch);
            $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            curl_close($ch);

            if ($http_code != 200) {
                return response()->json(['status' => 0, 'message' => 'Payment gateway error'], 400);
            }

            $result = json_decode($response, true);

            if ($result['code'] == '201') {
                // Payment link created successfully
                return response()->json([
                    'status' => 1,
                    'payment_url' => $result['data']['payment_url'],
                    'transaction_id' => $data['transaction_id']
                ]);
            } else {
                return response()->json(['status' => 0, 'message' => $result['message'] ?? 'Payment initialization failed'], 400);
            }

        } catch (\Exception $e) {
            Log::error('CinetPay Init Error: ' . $e->getMessage());
            return response()->json(['status' => 0, 'message' => 'Payment initialization failed'], 500);
        }
    }

    public function return(Request $request)
    {
        try {
            $transaction_id = $request->transaction_id;
            $token = $request->token;

            if (!$transaction_id || !$token) {
                return redirect()->back()->with('error', 'Invalid payment response');
            }

            // Extract order number and vendor ID from transaction ID
            $order_parts = explode('_', $transaction_id);
            $order_number = $order_parts[0];

            $order = Order::where('order_number', $order_number)->first();

            if (!$order) {
                return redirect()->back()->with('error', 'Order not found');
            }

            // Get CinetPay configuration
            $cinetpay_config = $this->getCinetPayConfig($order->vendor_id);

            // Verify payment status
            $verify_data = [
                'apikey' => $cinetpay_config->public_key,
                'site_id' => $cinetpay_config->secret_key,
                'transaction_id' => $transaction_id
            ];

            $verify_url = $cinetpay_config->environment == 'sandbox'
                ? 'https://api-checkout.cinetpay.com/v2/payment/check'
                : 'https://api-checkout.cinetpay.com/v2/payment/check';

            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $verify_url);
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($verify_data));
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_HTTPHEADER, [
                'Content-Type: application/json',
                'Accept: application/json'
            ]);

            $verify_response = curl_exec($ch);
            curl_close($ch);

            $verify_result = json_decode($verify_response, true);

            $storeinfo = helper::storeinfo($order->vendor_id);

            if ($verify_result['code'] == '00' && $verify_result['data']['status'] == 'ACCEPTED') {
                // Payment successful
                $order->payment_status = 2; // Paid
                $order->payment_id = $transaction_id;
                $order->save();

                return redirect($storeinfo->slug . '/success/' . $order_number)
                    ->with('success', trans('messages.order_placed'));
            } else {
                // Payment failed
                return redirect($storeinfo->slug . '/cart')
                    ->with('error', 'Payment failed or cancelled');
            }

        } catch (\Exception $e) {
            Log::error('CinetPay Return Error: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Payment verification failed');
        }
    }

    public function notify(Request $request)
    {
        try {
            $transaction_id = $request->cpm_trans_id;
            $status = $request->cpm_result;

            if (!$transaction_id) {
                return response('Invalid notification', 400);
            }

            // Extract order number from transaction ID
            $order_parts = explode('_', $transaction_id);
            $order_number = $order_parts[0];

            $order = Order::where('order_number', $order_number)->first();

            if (!$order) {
                return response('Order not found', 404);
            }

            // Get CinetPay configuration
            $cinetpay_config = $this->getCinetPayConfig($order->vendor_id);

            // Verify the notification
            $verify_data = [
                'apikey' => $cinetpay_config->public_key,
                'site_id' => $cinetpay_config->secret_key,
                'transaction_id' => $transaction_id
            ];

            $verify_url = $cinetpay_config->environment == 'sandbox'
                ? 'https://api-checkout.cinetpay.com/v2/payment/check'
                : 'https://api-checkout.cinetpay.com/v2/payment/check';

            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $verify_url);
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($verify_data));
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_HTTPHEADER, [
                'Content-Type: application/json',
                'Accept: application/json'
            ]);

            $verify_response = curl_exec($ch);
            curl_close($ch);

            $verify_result = json_decode($verify_response, true);

            if ($verify_result['code'] == '00' && $verify_result['data']['status'] == 'ACCEPTED') {
                // Payment successful
                $order->payment_status = 2; // Paid
                $order->payment_id = $transaction_id;
                $order->save();

                // Send confirmation emails, notifications etc.
                $emaildata = helper::emailconfigration($order->vendor_id);
                Config::set('mail', $emaildata);
                helper::order_status_email($order->customer_email, $order->customer_name, 'Payment Confirmed', 'Your payment has been confirmed for order #' . $order_number, $order->vendor_id);

                return response('OK', 200);
            }

            return response('Payment verification failed', 400);

        } catch (\Exception $e) {
            Log::error('CinetPay Notify Error: ' . $e->getMessage());
            return response('Error', 500);
        }
    }
}
