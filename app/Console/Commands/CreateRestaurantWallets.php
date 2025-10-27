<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\User;
use App\Models\RestaurantWallet;
use App\Models\WalletTransaction;
use Illuminate\Support\Facades\DB;

class CreateRestaurantWallets extends Command
{
    protected $signature = 'wallet:create-restaurants';
    protected $description = 'Créer les wallets pour tous les restaurants existants';

    public function handle()
    {
        $this->info('Création des wallets pour tous les restaurants...');

        // Récupérer tous les utilisateurs de type restaurant (type 2)
        $restaurants = User::where('type', 2)->get();

        $created = 0;
        $existing = 0;

        foreach ($restaurants as $restaurant) {
            // Vérifier si le wallet existe déjà
            $wallet = RestaurantWallet::where('vendor_id', $restaurant->id)->first();

            if (!$wallet) {
                // Créer le wallet avec des données de démonstration
                $wallet = RestaurantWallet::create([
                    'vendor_id' => $restaurant->id,
                    'balance' => rand(50000, 500000), // Solde aléatoire entre 50k et 500k FCFA
                    'pending_balance' => rand(0, 50000), // Solde en attente
                    'total_earned' => rand(100000, 1000000), // Total gagné
                    'total_withdrawn' => rand(0, 200000), // Total retiré
                ]);

                // Ajouter quelques transactions de démonstration
                $this->createDemoTransactions($wallet);

                $created++;
                $this->info("✅ Wallet créé pour {$restaurant->name} (ID: {$restaurant->id})");
            } else {
                $existing++;
                $this->info("ℹ️  Wallet existe déjà pour {$restaurant->name} (ID: {$restaurant->id})");
            }
        }

        $this->info("\n📊 Résumé:");
        $this->info("   • Wallets créés: {$created}");
        $this->info("   • Wallets existants: {$existing}");
        $this->info("   • Total restaurants: " . ($created + $existing));

        if ($created > 0) {
            $this->info("\n🎉 Tous les wallets ont été créés avec succès!");
        }

        return 0;
    }

    private function createDemoTransactions(RestaurantWallet $wallet)
    {
        $transactions = [
            [
                'type' => 'credit',
                'amount' => 25000,
                'description' => 'Paiement commande #CMD-001',
                'status' => 'completed',
                'metadata' => json_encode(['order_id' => 'CMD-001', 'payment_method' => 'CinetPay']),
                'created_at' => now()->subDays(5),
            ],
            [
                'type' => 'credit',
                'amount' => 15000,
                'description' => 'Paiement commande #CMD-002',
                'status' => 'completed',
                'metadata' => json_encode(['order_id' => 'CMD-002', 'payment_method' => 'Orange Money']),
                'created_at' => now()->subDays(3),
            ],
            [
                'type' => 'debit',
                'amount' => 10000,
                'description' => 'Retrait vers Orange Money',
                'status' => 'completed',
                'metadata' => json_encode(['withdrawal_id' => 'WTH-001', 'fee' => 200]),
                'created_at' => now()->subDays(1),
            ],
            [
                'type' => 'credit',
                'amount' => 30000,
                'description' => 'Paiement commande #CMD-003',
                'status' => 'pending',
                'metadata' => json_encode(['order_id' => 'CMD-003', 'payment_method' => 'MTN Money']),
                'created_at' => now(),
            ],
        ];

        $balance = 0;
        foreach ($transactions as $transactionData) {
            // Calculer le solde avant la transaction
            $balanceBefore = $balance;

            // Mettre à jour le solde
            if ($transactionData['type'] === 'credit') {
                $balance += $transactionData['amount'];
            } else {
                $balance -= $transactionData['amount'];
            }

            // Créer la transaction
            WalletTransaction::create([
                'wallet_id' => $wallet->id,
                'reference' => 'TXN-' . strtoupper(uniqid()),
                'type' => $transactionData['type'],
                'amount' => $transactionData['amount'],
                'description' => $transactionData['description'],
                'status' => $transactionData['status'],
                'balance_before' => $balanceBefore,
                'balance_after' => $balance,
                'metadata' => $transactionData['metadata'],
                'created_at' => $transactionData['created_at'],
                'updated_at' => $transactionData['created_at'],
            ]);
        }
    }
}
