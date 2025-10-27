<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class BasicPlanSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run()
    {
        // Créer un plan de base gratuit
        $plan_id = DB::table('pricing_plans')->insertGetId([
            'name' => 'Plan Gratuit',
            'description' => 'Plan de base gratuit pour commencer',
            'features' => 'Accès illimité, Produits illimités, Commandes illimitées',
            'price' => 0,
            'duration' => 365,
            'service_limit' => -1,
            'appoinment_limit' => -1,
            'type' => 'yearly',
            'is_available' => 1,
            'created_at' => now(),
            'updated_at' => now()
        ]);

        // Mettre à jour l'utilisateur avec le plan
        DB::table('users')->where('id', 1)->update([
            'plan_id' => $plan_id,
            'allow_without_subscription' => 1
        ]);

        // Créer une transaction pour activer le plan
        DB::table('transactions')->insert([
            'vendor_id' => 1,
            'plan_id' => $plan_id,
            'amount' => 0,
            'payment_type' => '2',
            'status' => '2',
            'expire_date' => now()->addYear(),
            'service_limit' => -1,
            'appoinment_limit' => -1,
            'created_at' => now(),
            'updated_at' => now()
        ]);

        echo "Plan de base créé et assigné à l'utilisateur 1\n";
    }
}
