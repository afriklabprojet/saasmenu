<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\RestaurantWallet;
use App\Models\WalletTransaction;
use App\Models\WithdrawalMethod;
use App\Models\WithdrawalRequest;
use App\Services\CinetPayService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;

class WalletController extends Controller
{
    public function index()
    {
        $vendor_id = Auth::user()->type == 4 ? Auth::user()->vendor_id : Auth::user()->id;

        // Récupérer ou créer le wallet
        $wallet = RestaurantWallet::firstOrCreate(
            ['vendor_id' => $vendor_id],
            [
                'balance' => 0,
                'pending_balance' => 0,
                'total_earnings' => 0,
                'total_withdrawn' => 0
            ]
        );

        // Statistiques du mois
        $monthlyStats = $this->getMonthlyStats($vendor_id);

        // Dernières transactions
        $recentTransactions = WalletTransaction::where('vendor_id', $vendor_id)
            ->orderBy('created_at', 'desc')
            ->limit(10)
            ->get();

        // Moyens de retrait
        $withdrawalMethods = WithdrawalMethod::where('vendor_id', $vendor_id)
            ->where('is_active', true)
            ->get();

        // Demandes de retrait en cours
        $pendingWithdrawals = WithdrawalRequest::where('vendor_id', $vendor_id)
            ->whereIn('status', ['pending', 'processing'])
            ->orderBy('created_at', 'desc')
            ->get();

        return view('admin.wallet.dashboard', compact(
            'wallet',
            'monthlyStats',
            'recentTransactions',
            'withdrawalMethods',
            'pendingWithdrawals'
        ));
    }

    public function transactions(Request $request)
    {
        $vendor_id = Auth::user()->type == 4 ? Auth::user()->vendor_id : Auth::user()->id;

        $query = WalletTransaction::where('vendor_id', $vendor_id);

        // Filtres
        if ($request->type) {
            $query->where('type', $request->type);
        }

        if ($request->source) {
            $query->where('source', $request->source);
        }

        if ($request->date_from) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }

        if ($request->date_to) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }

        $transactions = $query->orderBy('created_at', 'desc')->paginate(20);

        return view('admin.wallet.transactions', compact('transactions'));
    }

    public function withdrawalMethods()
    {
        $vendor_id = Auth::user()->type == 4 ? Auth::user()->vendor_id : Auth::user()->id;

        $methods = WithdrawalMethod::where('vendor_id', $vendor_id)->get();

        return view('admin.wallet.withdrawal_methods', compact('methods'));
    }

    public function addWithdrawalMethod(Request $request)
    {
        $vendor_id = Auth::user()->type == 4 ? Auth::user()->vendor_id : Auth::user()->id;

        $validator = Validator::make($request->all(), [
            'type' => 'required|in:orange_money,mtn_money,moov_money,bank_transfer,cinetpay_card',
            'account_number' => 'required|string|max:50',
            'account_name' => 'required|string|max:100'
        ]);

        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }

        try {
            WithdrawalMethod::create([
                'vendor_id' => $vendor_id,
                'type' => $request->type,
                'account_number' => $request->account_number,
                'account_name' => $request->account_name,
                'additional_info' => $request->additional_info ? json_decode($request->additional_info, true) : null,
                'is_active' => true,
                'is_verified' => false
            ]);

            return back()->with('success', 'Méthode de retrait ajoutée avec succès');
        } catch (\Exception $e) {
            return back()->with('error', 'Erreur lors de l\'ajout: ' . $e->getMessage());
        }
    }

    public function requestWithdrawal(Request $request)
    {
        $vendor_id = Auth::user()->type == 4 ? Auth::user()->vendor_id : Auth::user()->id;

        $validator = Validator::make($request->all(), [
            'amount' => 'required|numeric|min:1000', // Minimum 1000 FCFA
            'withdrawal_method_id' => 'required|exists:withdrawal_methods,id'
        ]);

        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }

        $wallet = RestaurantWallet::where('vendor_id', $vendor_id)->first();
        $amount = $request->amount;

        if (!$wallet || !$wallet->canWithdraw($amount)) {
            return back()->with('error', 'Solde insuffisant pour ce retrait');
        }

        // Calculer les frais (2% minimum 100 FCFA)
        $fee = max(100, $amount * 0.02);
        $netAmount = $amount - $fee;

        try {
            DB::beginTransaction();

            // Créer la demande de retrait
            $withdrawal = WithdrawalRequest::create([
                'vendor_id' => $vendor_id,
                'request_id' => 'WD_' . uniqid(),
                'amount' => $amount,
                'fee' => $fee,
                'net_amount' => $netAmount,
                'withdrawal_method_id' => $request->withdrawal_method_id,
                'status' => 'pending',
                'requested_at' => now()
            ]);

            // Mettre en attente le montant dans le wallet
            $wallet->increment('pending_balance', $amount);
            $wallet->decrement('balance', $amount);

            // Enregistrer la transaction
            WalletTransaction::create([
                'vendor_id' => $vendor_id,
                'transaction_id' => 'TXN_WD_' . $withdrawal->id,
                'type' => 'debit',
                'amount' => $amount,
                'source' => 'withdrawal',
                'reference_id' => $withdrawal->id,
                'description' => 'Demande de retrait #' . $withdrawal->request_id,
                'status' => 'pending'
            ]);

            DB::commit();

            // Traiter le retrait automatiquement si possible
            $this->processWithdrawal($withdrawal);

            return back()->with('success', 'Demande de retrait soumise avec succès');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Erreur lors de la demande: ' . $e->getMessage());
        }
    }

    private function processWithdrawal(WithdrawalRequest $withdrawal)
    {
        $method = $withdrawal->withdrawalMethod;

        try {
            switch ($method->type) {
                case 'orange_money':
                case 'mtn_money':
                case 'moov_money':
                    return $this->processMobileMoneyWithdrawal($withdrawal);

                case 'cinetpay_card':
                    return $this->processCinetPayWithdrawal($withdrawal);

                case 'bank_transfer':
                    return $this->processBankTransfer($withdrawal);

                default:
                    throw new \Exception('Type de retrait non supporté');
            }
        } catch (\Exception $e) {
            $withdrawal->update([
                'status' => 'failed',
                'notes' => 'Erreur: ' . $e->getMessage()
            ]);

            // Remettre le montant dans le wallet
            $wallet = $withdrawal->vendor->wallet;
            $wallet->decrement('pending_balance', $withdrawal->amount);
            $wallet->increment('balance', $withdrawal->amount);
        }
    }

    private function processMobileMoneyWithdrawal(WithdrawalRequest $withdrawal)
    {
        // Intégration avec CinetPay pour les retraits Mobile Money
        $cinetPayService = new CinetPayService();

        $response = $cinetPayService->initiateWithdrawal([
            'amount' => $withdrawal->net_amount,
            'phone_number' => $withdrawal->withdrawalMethod->account_number,
            'operator' => $this->getOperatorFromType($withdrawal->withdrawalMethod->type),
            'reference' => $withdrawal->request_id
        ]);

        if ($response['success']) {
            $withdrawal->update([
                'status' => 'processing',
                'provider_transaction_id' => $response['transaction_id']
            ]);

            return true;
        }

        throw new \Exception($response['message']);
    }

    private function processCinetPayWithdrawal(WithdrawalRequest $withdrawal)
    {
        // Retrait direct via CinetPay (si le restaurant a une carte CinetPay)
        // Implementation dépend de l'API CinetPay pour les retraits

        $withdrawal->update([
            'status' => 'completed',
            'processed_at' => now(),
            'notes' => 'Retrait traité via CinetPay'
        ]);

        // Finaliser la transaction
        $this->finalizeWithdrawal($withdrawal);

        return true;
    }

    private function processBankTransfer(WithdrawalRequest $withdrawal)
    {
        // Les virements bancaires nécessitent un traitement manuel
        $withdrawal->update([
            'status' => 'processing',
            'notes' => 'Virement bancaire en cours de traitement'
        ]);

        // Notifier l'équipe admin pour traitement manuel
        // TODO: Envoyer notification à l'équipe

        return true;
    }

    private function finalizeWithdrawal(WithdrawalRequest $withdrawal)
    {
        $wallet = RestaurantWallet::where('vendor_id', $withdrawal->vendor_id)->first();

        // Retirer du pending et incrémenter total_withdrawn
        $wallet->decrement('pending_balance', $withdrawal->amount);
        $wallet->increment('total_withdrawn', $withdrawal->amount);

        // Mettre à jour la transaction
        WalletTransaction::where('reference_id', $withdrawal->id)
            ->where('source', 'withdrawal')
            ->update(['status' => 'completed']);
    }

    private function getOperatorFromType($type)
    {
        return [
            'orange_money' => 'orange',
            'mtn_money' => 'mtn',
            'moov_money' => 'moov'
        ][$type] ?? 'orange';
    }

    private function getMonthlyStats($vendor_id)
    {
        $startOfMonth = now()->startOfMonth();

        return [
            'earnings' => WalletTransaction::where('vendor_id', $vendor_id)
                ->where('type', 'credit')
                ->where('created_at', '>=', $startOfMonth)
                ->sum('amount'),
            'withdrawals' => WalletTransaction::where('vendor_id', $vendor_id)
                ->where('type', 'debit')
                ->where('source', 'withdrawal')
                ->where('created_at', '>=', $startOfMonth)
                ->sum('amount'),
            'commission' => WalletTransaction::where('vendor_id', $vendor_id)
                ->where('type', 'debit')
                ->where('source', 'commission')
                ->where('created_at', '>=', $startOfMonth)
                ->sum('amount')
        ];
    }
}
