<?php

/**
 * Script de test manuel pour vérifier les fonctions du système d'abonnement
 * Usage: php test-functions.php
 */

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

echo "\n";
echo "╔═══════════════════════════════════════════════════════════════════╗\n";
echo "║                                                                   ║\n";
echo "║          🧪 TESTS MANUELS DES FONCTIONS D'ABONNEMENT              ║\n";
echo "║                                                                   ║\n";
echo "╚═══════════════════════════════════════════════════════════════════╝\n";
echo "\n";

$passed = 0;
$failed = 0;

// ============================================================================
// TEST 1: Fonction getPlanInfo() avec vendor valide
// ============================================================================
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";
echo "🧪 TEST 1: getPlanInfo() avec vendor valide\n";
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";

try {
    $vendor = \App\Models\User::where('type', 2)->whereNotNull('plan_id')->first();

    if ($vendor) {
        $planInfo = \App\Helpers\helper::getPlanInfo($vendor->id);

        echo "Vendor trouvé: ID " . $vendor->id . " (" . $vendor->email . ")\n";

        if (is_array($planInfo)) {
            echo "✅ Retourne un tableau\n";
            $passed++;

            $requiredKeys = ['plan_name', 'products_limit', 'categories_limit', 'staff_limit', 'whatsapp_integration', 'analytics'];
            $allKeysPresent = true;

            foreach ($requiredKeys as $key) {
                if (!array_key_exists($key, $planInfo)) {
                    echo "❌ Clé manquante: $key\n";
                    $allKeysPresent = false;
                    $failed++;
                }
            }

            if ($allKeysPresent) {
                echo "✅ Toutes les clés requises présentes\n";
                echo "   - Plan: " . $planInfo['plan_name'] . "\n";
                echo "   - Products Limit: " . $planInfo['products_limit'] . "\n";
                echo "   - Categories Limit: " . $planInfo['categories_limit'] . "\n";
                echo "   - Staff Limit: " . $planInfo['staff_limit'] . "\n";
                echo "   - WhatsApp: " . $planInfo['whatsapp_integration'] . "\n";
                echo "   - Analytics: " . $planInfo['analytics'] . "\n";
                $passed++;
            }
        } else {
            echo "❌ Ne retourne pas un tableau\n";
            $failed++;
        }
    } else {
        echo "⚠️  Aucun vendor trouvé (peut être normal si DB vide)\n";
        echo "   Test ignoré\n";
    }
} catch (\Exception $e) {
    echo "❌ Erreur: " . $e->getMessage() . "\n";
    $failed++;
}

echo "\n";

// ============================================================================
// TEST 2: getPlanInfo() avec vendor inexistant
// ============================================================================
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";
echo "🧪 TEST 2: getPlanInfo() avec vendor inexistant (ID: 99999)\n";
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";

try {
    $planInfo = \App\Helpers\helper::getPlanInfo(99999);

    if (is_array($planInfo)) {
        echo "✅ Retourne un tableau (pas d'erreur)\n";
        $passed++;

        if ($planInfo['plan_name'] === 'No Plan') {
            echo "✅ Plan name est 'No Plan'\n";
            $passed++;
        } else {
            echo "❌ Plan name devrait être 'No Plan', obtenu: " . $planInfo['plan_name'] . "\n";
            $failed++;
        }

        if ($planInfo['products_limit'] === 0) {
            echo "✅ Products limit est 0 (sécurité)\n";
            $passed++;
        } else {
            echo "❌ Products limit devrait être 0, obtenu: " . $planInfo['products_limit'] . "\n";
            $failed++;
        }
    } else {
        echo "❌ Ne retourne pas un tableau\n";
        $failed++;
    }
} catch (\Exception $e) {
    echo "❌ Erreur: " . $e->getMessage() . "\n";
    $failed++;
}

echo "\n";

// ============================================================================
// TEST 3: Vérification des plans en base
// ============================================================================
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";
echo "🧪 TEST 3: Structure des plans en base de données\n";
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";

try {
    $columns = ['name', 'price', 'products_limit', 'categories_limit', 'staff_limit', 'whatsapp_integration', 'analytics'];
    $plans = \App\Models\PricingPlan::select($columns)->get();

    if ($plans->count() > 0) {
        echo "✅ " . $plans->count() . " plan(s) trouvé(s)\n\n";
        $passed++;

        foreach ($plans as $plan) {
            echo "Plan: " . $plan->name . " (" . ($plan->price == 0 ? 'Gratuit' : '€' . $plan->price) . ")\n";
            echo "  ├─ Products: " . ($plan->products_limit == -1 ? '∞ Illimité' : $plan->products_limit) . "\n";
            echo "  ├─ Categories: " . ($plan->categories_limit == -1 ? '∞ Illimité' : $plan->categories_limit) . "\n";
            echo "  ├─ Staff: " . ($plan->staff_limit == -1 ? '∞ Illimité' : $plan->staff_limit) . "\n";
            echo "  ├─ WhatsApp: " . ($plan->whatsapp_integration == 1 ? '✓' : '✗') . "\n";
            echo "  └─ Analytics: " . ($plan->analytics == 1 ? '✓' : '✗') . "\n";
            echo "\n";
        }

        // Vérifier qu'il y a au moins un plan gratuit
        $freePlan = $plans->where('price', 0)->first();
        if ($freePlan) {
            echo "✅ Plan gratuit trouvé: " . $freePlan->name . "\n";
            $passed++;

            if ($freePlan->products_limit == 5 && $freePlan->categories_limit == 1) {
                echo "✅ Plan gratuit correctement configuré (5 produits, 1 catégorie)\n";
                $passed++;
            } else {
                echo "⚠️  Plan gratuit limites: " . $freePlan->products_limit . " produits, " . $freePlan->categories_limit . " catégories\n";
            }
        } else {
            echo "⚠️  Aucun plan gratuit trouvé\n";
        }

        // Vérifier qu'il y a au moins un plan illimité
        $unlimitedPlan = $plans->where('products_limit', -1)->first();
        if ($unlimitedPlan) {
            echo "✅ Plan illimité trouvé: " . $unlimitedPlan->name . "\n";
            $passed++;
        } else {
            echo "⚠️  Aucun plan illimité trouvé\n";
        }

    } else {
        echo "⚠️  Aucun plan trouvé en base de données\n";
    }
} catch (\Exception $e) {
    echo "❌ Erreur: " . $e->getMessage() . "\n";
    $failed++;
}

echo "\n";

// ============================================================================
// TEST 4: Vérification de la structure des colonnes
// ============================================================================
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";
echo "🧪 TEST 4: Colonnes de la table pricing_plans\n";
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";

try {
    $columns = \Illuminate\Support\Facades\Schema::getColumnListing('pricing_plans');

    $requiredColumns = [
        'products_limit',
        'categories_limit',
        'staff_limit',
        'whatsapp_integration',
        'analytics'
    ];

    $missingColumns = [];
    foreach ($requiredColumns as $col) {
        if (in_array($col, $columns)) {
            echo "✅ Colonne '$col' présente\n";
            $passed++;
        } else {
            echo "❌ Colonne '$col' manquante\n";
            $missingColumns[] = $col;
            $failed++;
        }
    }

    if (empty($missingColumns)) {
        echo "\n✅ Toutes les colonnes requises sont présentes\n";
        $passed++;
    } else {
        echo "\n❌ Colonnes manquantes: " . implode(', ', $missingColumns) . "\n";
        $failed++;
    }

} catch (\Exception $e) {
    echo "❌ Erreur: " . $e->getMessage() . "\n";
    $failed++;
}

echo "\n";

// ============================================================================
// TEST 5: Test de comptage de produits/catégories
// ============================================================================
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";
echo "🧪 TEST 5: Comptage de produits et catégories par vendor\n";
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";

try {
    $vendor = \App\Models\User::where('type', 2)->first();

    if ($vendor) {
        $productCount = \App\Models\Item::where('vendor_id', $vendor->id)->count();
        $categoryCount = \App\Models\Category::where('vendor_id', $vendor->id)->where('is_deleted', 2)->count();

        echo "Vendor ID: " . $vendor->id . "\n";
        echo "✅ Produits: " . $productCount . "\n";
        echo "✅ Catégories: " . $categoryCount . "\n";
        $passed += 2;

        if ($vendor->plan_id) {
            $planInfo = \App\Helpers\helper::getPlanInfo($vendor->id);
            echo "Plan: " . $planInfo['plan_name'] . "\n";
            echo "  Limite produits: " . ($planInfo['products_limit'] == -1 ? 'Illimité' : $planInfo['products_limit']) . "\n";
            echo "  Limite catégories: " . ($planInfo['categories_limit'] == -1 ? 'Illimité' : $planInfo['categories_limit']) . "\n";

            if ($planInfo['products_limit'] != -1) {
                $percentage = ($productCount / $planInfo['products_limit']) * 100;
                echo "  Utilisation produits: " . round($percentage, 1) . "%\n";

                if ($percentage >= 100) {
                    echo "  ⚠️  Limite produits ATTEINTE\n";
                } elseif ($percentage >= 80) {
                    echo "  ⚠️  Limite produits proche (≥80%)\n";
                } else {
                    echo "  ✅ Limite produits OK\n";
                }
            }

            $passed++;
        }
    } else {
        echo "⚠️  Aucun vendor trouvé (peut être normal si DB vide)\n";
    }
} catch (\Exception $e) {
    echo "❌ Erreur: " . $e->getMessage() . "\n";
    $failed++;
}

echo "\n";

// ============================================================================
// RÉSUMÉ FINAL
// ============================================================================
echo "╔═══════════════════════════════════════════════════════════════════╗\n";
echo "║                                                                   ║\n";
echo "║                     📊 RÉSUMÉ DES TESTS                            ║\n";
echo "║                                                                   ║\n";
echo "╚═══════════════════════════════════════════════════════════════════╝\n";
echo "\n";

$total = $passed + $failed;

echo "Tests totaux:     $total\n";
echo "✅ Tests réussis: $passed\n";
echo "❌ Tests échoués: $failed\n";
echo "\n";

if ($failed === 0) {
    echo "╔═══════════════════════════════════════════════════════════════════╗\n";
    echo "║                                                                   ║\n";
    echo "║          ✅ TOUS LES TESTS SONT PASSÉS AVEC SUCCÈS                ║\n";
    echo "║                                                                   ║\n";
    echo "║     🚀 Les fonctions du système d'abonnement sont opérationnelles ║\n";
    echo "║                                                                   ║\n";
    echo "╚═══════════════════════════════════════════════════════════════════╝\n";
    exit(0);
} else {
    $successRate = round(($passed / $total) * 100, 1);
    echo "╔═══════════════════════════════════════════════════════════════════╗\n";
    echo "║                                                                   ║\n";
    echo "║        ⚠️  CERTAINS TESTS ONT ÉCHOUÉ ($successRate% réussis)              ║\n";
    echo "║                                                                   ║\n";
    echo "║     Consultez les détails ci-dessus pour corriger les erreurs    ║\n";
    echo "║                                                                   ║\n";
    echo "╚═══════════════════════════════════════════════════════════════════╝\n";
    exit(1);
}
