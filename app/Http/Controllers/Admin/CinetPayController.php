<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\PaymentMethod;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class CinetPayController extends Controller
{
    /**
     * Afficher les paramètres CinetPay
     */
    public function index()
    {
        $cinetpay = PaymentMethod::where('type', PaymentMethod::CINETPAY)->first();
        $credentials = $cinetpay ? $cinetpay->credentials : [];

        return view('admin.cinetpay.index', compact('cinetpay', 'credentials'));
    }

    /**
     * Mettre à jour les paramètres CinetPay
     */
    public function update(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'status' => 'required|in:active,inactive',
            'environment' => 'required|in:sandbox,production',
            'site_id' => 'required|string',
            'api_key' => 'required|string',
            'secret_key' => 'required|string'
        ]);

        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }

        try {
            $cinetpay = PaymentMethod::where('type', PaymentMethod::CINETPAY)->first();

            if (!$cinetpay) {
                // Créer CinetPay s'il n'existe pas
                $cinetpay = new PaymentMethod();
                $cinetpay->type = PaymentMethod::CINETPAY;
                $cinetpay->name = 'CinetPay';
                $cinetpay->position = 1;
            }

            $cinetpay->status = $request->status;
            $cinetpay->credentials = [
                'environment' => $request->environment,
                'site_id' => $request->site_id,
                'api_key' => $request->api_key,
                'secret_key' => $request->secret_key
            ];

            $cinetpay->save();

            return back()->with('success', __('CinetPay settings updated successfully'));
        } catch (\Exception $e) {
            return back()->with('error', __('Error updating CinetPay settings: ') . $e->getMessage());
        }
    }

    /**
     * Tester la connexion CinetPay
     */
    public function test(Request $request)
    {
        try {
            $credentials = PaymentMethod::getCinetPayCredentials();

            if (!$credentials) {
                return response()->json(['success' => false, 'message' => __('CinetPay not configured')]);
            }

            // Test basique de l'API CinetPay
            $url = $credentials['environment'] === 'sandbox'
                ? 'https://api-checkout.cinetpay.com/v2/payment'
                : 'https://api-checkout.cinetpay.com/v2/payment';

            $testData = [
                'apikey' => $credentials['api_key'],
                'site_id' => $credentials['site_id'],
                'transaction_id' => 'TEST_' . time(),
                'amount' => 100,
                'currency' => 'XOF',
                'description' => 'Test de connexion',
                'return_url' => route('home'),
                'notify_url' => route('cinetpay.notify')
            ];

            $curl = curl_init();
            curl_setopt_array($curl, [
                CURLOPT_URL => $url,
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_POST => true,
                CURLOPT_POSTFIELDS => json_encode($testData),
                CURLOPT_HTTPHEADER => [
                    'Content-Type: application/json',
                    'Accept: application/json'
                ],
                CURLOPT_TIMEOUT => 30
            ]);

            $response = curl_exec($curl);
            $httpCode = curl_getinfo($curl, CURLINFO_HTTP_CODE);
            curl_close($curl);

            if ($response === false || $httpCode !== 200) {
                return response()->json(['success' => false, 'message' => __('Connection failed')]);
            }

            return response()->json(['success' => true, 'message' => __('Connection successful')]);

        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()]);
        }
    }
}
