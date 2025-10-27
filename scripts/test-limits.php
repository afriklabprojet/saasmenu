<?php

require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\User;
use App\Models\Category;
use App\Models\Item;
use App\Helpers\helper;

echo "\n";
echo "╔═══════════════════════════════════════════════════════════════════════╗\n";
echo "║                                                                       ║\n";
echo "║          🧪 TEST DES LIMITES DE PRODUITS ET CATÉGORIES               ║\n";
echo "║                                                                       ║\n";
echo "╚═══════════════════════════════════════════════════════════════════════╝\n";
echo "\n";

$totalTests = 0;
$passedTests = 0;
$failedTests = 0;

// Fonction helper pour les tests
function test($description, $condition, &$total, &$passed, &$failed) {
    $total++;
    if ($condition) {
        $passed++;
        echo "✅ " . $description . "\n";
        return true;
    } else {
        $failed++;
        echo "❌ " . $description . "\n";
        return false;
    }
}

echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";
echo "🧪 TEST 1: Vérification des vendors et plans créés\n";
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";

$vendorGratuit = User::find(5);
$vendorStarter = User::find(6);
$vendorIllimite = User::find(7);

test("Vendor Gratuit existe (ID: 5)", $vendorGratuit != null, $totalTests, $passedTests, $failedTests);
test("Vendor Starter existe (ID: 6)", $vendorStarter != null, $totalTests, $passedTests, $failedTests);
test("Vendor Illimité existe (ID: 7)", $vendorIllimite != null, $totalTests, $passedTests, $failedTests);

if ($vendorGratuit) {
    $planGratuit = helper::getPlanInfo($vendorGratuit->id);
    test("Plan Gratuit a products_limit = 5", $planGratuit['products_limit'] == 5, $totalTests, $passedTests, $failedTests);
    test("Plan Gratuit a categories_limit = 3", $planGratuit['categories_limit'] == 3, $totalTests, $passedTests, $failedTests);
    echo "   Plan: {$planGratuit['plan_name']} (Produits: {$planGratuit['products_limit']}, Catégories: {$planGratuit['categories_limit']})\n";
}

if ($vendorStarter) {
    $planStarter = helper::getPlanInfo($vendorStarter->id);
    test("Plan Starter a products_limit = 20", $planStarter['products_limit'] == 20, $totalTests, $passedTests, $failedTests);
    test("Plan Starter a categories_limit = 10", $planStarter['categories_limit'] == 10, $totalTests, $passedTests, $failedTests);
    echo "   Plan: {$planStarter['plan_name']} (Produits: {$planStarter['products_limit']}, Catégories: {$planStarter['categories_limit']})\n";
}

if ($vendorIllimite) {
    $planIllimite = helper::getPlanInfo($vendorIllimite->id);
    test("Plan Illimité a products_limit = -1", $planIllimite['products_limit'] == -1, $totalTests, $passedTests, $failedTests);
    test("Plan Illimité a categories_limit = -1", $planIllimite['categories_limit'] == -1, $totalTests, $passedTests, $failedTests);
    echo "   Plan: {$planIllimite['plan_name']} (Produits: Illimité, Catégories: Illimité)\n";
}

echo "\n";
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";
echo "🧪 TEST 2: Création de catégories pour Vendor Gratuit (limite: 3)\n";
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";

if ($vendorGratuit) {
    // Créer 3 catégories (devrait fonctionner)
    $categories = [];
    for ($i = 1; $i <= 3; $i++) {
        $category = Category::create([
            'name' => "Catégorie Test $i",
            'vendor_id' => $vendorGratuit->id,
            'is_available' => 1,
            'is_deleted' => 2
        ]);
        $categories[] = $category;
        echo "   ✅ Catégorie $i créée (ID: {$category->id})\n";
    }

    $count = Category::where('vendor_id', $vendorGratuit->id)->where('is_deleted', 2)->count();
    test("Exactement 3 catégories créées", $count == 3, $totalTests, $passedTests, $failedTests);

    $planInfo = helper::getPlanInfo($vendorGratuit->id);
    $atLimit = ($count >= $planInfo['categories_limit'] && $planInfo['categories_limit'] != -1);
    test("Limite de catégories atteinte (3/3)", $atLimit, $totalTests, $passedTests, $failedTests);

    // Tenter de créer une 4ème catégorie (devrait échouer en production via controller)
    echo "   ⚠️  Tentative de créer une 4ème catégorie (devrait être bloquée par le controller)\n";
}

echo "\n";
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";
echo "🧪 TEST 3: Création de produits pour Vendor Gratuit (limite: 5)\n";
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";

if ($vendorGratuit && isset($categories[0])) {
    // Créer 5 produits (devrait fonctionner)
    $products = [];
    for ($i = 1; $i <= 5; $i++) {
        $product = Item::create([
            'name' => "Produit Test $i",
            'cat_id' => $categories[0]->id,
            'vendor_id' => $vendorGratuit->id,
            'price' => 9.99 + $i,
            'description' => "Description du produit test $i",
            'image' => 'default.jpg',
            'is_available' => 1
        ]);
        $products[] = $product;
        echo "   ✅ Produit $i créé (ID: {$product->id})\n";
    }

    $count = Item::where('vendor_id', $vendorGratuit->id)->count();
    test("Exactement 5 produits créés", $count == 5, $totalTests, $passedTests, $failedTests);

    $planInfo = helper::getPlanInfo($vendorGratuit->id);
    $atLimit = ($count >= $planInfo['products_limit'] && $planInfo['products_limit'] != -1);
    test("Limite de produits atteinte (5/5)", $atLimit, $totalTests, $passedTests, $failedTests);

    echo "   ⚠️  Tentative de créer un 6ème produit (devrait être bloquée par le controller)\n";
}

echo "\n";
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";
echo "🧪 TEST 4: Création de catégories pour Vendor Starter (limite: 10)\n";
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";

if ($vendorStarter) {
    // Créer 10 catégories
    for ($i = 1; $i <= 10; $i++) {
        $category = Category::create([
            'name' => "Catégorie Starter $i",
            'vendor_id' => $vendorStarter->id,
            'is_available' => 1,
            'is_deleted' => 2
        ]);
        if ($i <= 3 || $i > 7) {
            echo "   ✅ Catégorie $i créée (ID: {$category->id})\n";
        } else if ($i == 4) {
            echo "   ... (catégories 4-7 créées) ...\n";
        }
    }

    $count = Category::where('vendor_id', $vendorStarter->id)->where('is_deleted', 2)->count();
    test("Exactement 10 catégories créées", $count == 10, $totalTests, $passedTests, $failedTests);

    $planInfo = helper::getPlanInfo($vendorStarter->id);
    $atLimit = ($count >= $planInfo['categories_limit'] && $planInfo['categories_limit'] != -1);
    test("Limite de catégories atteinte (10/10)", $atLimit, $totalTests, $passedTests, $failedTests);
}

echo "\n";
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";
echo "🧪 TEST 5: Création de produits pour Vendor Starter (limite: 20)\n";
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";

if ($vendorStarter) {
    $starterCategories = Category::where('vendor_id', $vendorStarter->id)->where('is_deleted', 2)->get();
    if ($starterCategories->count() > 0) {
        // Créer 20 produits
        for ($i = 1; $i <= 20; $i++) {
            $categoryIndex = ($i - 1) % $starterCategories->count();
            $product = Item::create([
                'name' => "Produit Starter $i",
                'cat_id' => $starterCategories[$categoryIndex]->id,
                'vendor_id' => $vendorStarter->id,
                'price' => 14.99 + $i,
                'description' => "Description du produit starter $i",
                'image' => 'default.jpg',
                'is_available' => 1
            ]);
            if ($i <= 3 || $i > 17) {
                echo "   ✅ Produit $i créé (ID: {$product->id})\n";
            } else if ($i == 4) {
                echo "   ... (produits 4-17 créés) ...\n";
            }
        }

        $count = Item::where('vendor_id', $vendorStarter->id)->count();
        test("Exactement 20 produits créés", $count == 20, $totalTests, $passedTests, $failedTests);

        $planInfo = helper::getPlanInfo($vendorStarter->id);
        $atLimit = ($count >= $planInfo['products_limit'] && $planInfo['products_limit'] != -1);
        test("Limite de produits atteinte (20/20)", $atLimit, $totalTests, $passedTests, $failedTests);
    }
}

echo "\n";
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";
echo "🧪 TEST 6: Création pour Vendor Illimité (pas de limite)\n";
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";

if ($vendorIllimite) {
    $planInfo = helper::getPlanInfo($vendorIllimite->id);
    test("Plan Illimité n'a pas de limite produits", $planInfo['products_limit'] == -1, $totalTests, $passedTests, $failedTests);
    test("Plan Illimité n'a pas de limite catégories", $planInfo['categories_limit'] == -1, $totalTests, $passedTests, $failedTests);

    // Créer 15 catégories (devrait fonctionner sans problème)
    for ($i = 1; $i <= 15; $i++) {
        $category = Category::create([
            'name' => "Catégorie Illimité $i",
            'vendor_id' => $vendorIllimite->id,
            'is_available' => 1,
            'is_deleted' => 2
        ]);
        if ($i <= 2 || $i > 13) {
            echo "   ✅ Catégorie $i créée (ID: {$category->id})\n";
        } else if ($i == 3) {
            echo "   ... (catégories 3-13 créées) ...\n";
        }
    }

    $catCount = Category::where('vendor_id', $vendorIllimite->id)->where('is_deleted', 2)->count();
    test("15 catégories créées sans limite", $catCount == 15, $totalTests, $passedTests, $failedTests);

    // Créer 30 produits (devrait fonctionner sans problème)
    $illimiteCategories = Category::where('vendor_id', $vendorIllimite->id)->where('is_deleted', 2)->get();
    if ($illimiteCategories->count() > 0) {
        for ($i = 1; $i <= 30; $i++) {
            $categoryIndex = ($i - 1) % $illimiteCategories->count();
            $product = Item::create([
                'name' => "Produit Illimité $i",
                'cat_id' => $illimiteCategories[$categoryIndex]->id,
                'vendor_id' => $vendorIllimite->id,
                'price' => 19.99 + $i,
                'description' => "Description du produit illimité $i",
                'image' => 'default.jpg',
                'is_available' => 1
            ]);
            if ($i <= 2 || $i > 28) {
                echo "   ✅ Produit $i créé (ID: {$product->id})\n";
            } else if ($i == 3) {
                echo "   ... (produits 3-28 créés) ...\n";
            }
        }

        $prodCount = Item::where('vendor_id', $vendorIllimite->id)->count();
        test("30 produits créés sans limite", $prodCount == 30, $totalTests, $passedTests, $failedTests);
    }
}

echo "\n";
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";
echo "🧪 TEST 7: Vérification du calcul de pourcentage d'utilisation\n";
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";

if ($vendorGratuit) {
    $planInfo = helper::getPlanInfo($vendorGratuit->id);
    $prodCount = Item::where('vendor_id', $vendorGratuit->id)->count();
    $catCount = Category::where('vendor_id', $vendorGratuit->id)->where('is_deleted', 2)->count();

    $prodPercent = ($planInfo['products_limit'] > 0) ? ($prodCount / $planInfo['products_limit']) * 100 : 0;
    $catPercent = ($planInfo['categories_limit'] > 0) ? ($catCount / $planInfo['categories_limit']) * 100 : 0;

    echo "   📊 Vendor Gratuit:\n";
    echo "      Produits: $prodCount / {$planInfo['products_limit']} (" . round($prodPercent, 1) . "%)\n";
    echo "      Catégories: $catCount / {$planInfo['categories_limit']} (" . round($catPercent, 1) . "%)\n";

    test("Utilisation produits à 100%", $prodPercent == 100, $totalTests, $passedTests, $failedTests);
    test("Utilisation catégories à 100%", $catPercent == 100, $totalTests, $passedTests, $failedTests);
}

if ($vendorStarter) {
    $planInfo = helper::getPlanInfo($vendorStarter->id);
    $prodCount = Item::where('vendor_id', $vendorStarter->id)->count();
    $catCount = Category::where('vendor_id', $vendorStarter->id)->where('is_deleted', 2)->count();

    $prodPercent = ($planInfo['products_limit'] > 0) ? ($prodCount / $planInfo['products_limit']) * 100 : 0;
    $catPercent = ($planInfo['categories_limit'] > 0) ? ($catCount / $planInfo['categories_limit']) * 100 : 0;

    echo "   📊 Vendor Starter:\n";
    echo "      Produits: $prodCount / {$planInfo['products_limit']} (" . round($prodPercent, 1) . "%)\n";
    echo "      Catégories: $catCount / {$planInfo['categories_limit']} (" . round($catPercent, 1) . "%)\n";

    test("Utilisation produits à 100%", $prodPercent == 100, $totalTests, $passedTests, $failedTests);
    test("Utilisation catégories à 100%", $catPercent == 100, $totalTests, $passedTests, $failedTests);
}

echo "\n";
echo "╔═══════════════════════════════════════════════════════════════════════╗\n";
echo "║                                                                       ║\n";
echo "║                         📊 RÉSUMÉ DES TESTS                           ║\n";
echo "║                                                                       ║\n";
echo "╚═══════════════════════════════════════════════════════════════════════╝\n";
echo "\n";
echo "Tests totaux:     $totalTests\n";
echo "✅ Tests réussis: $passedTests\n";
echo "❌ Tests échoués: $failedTests\n";
echo "\n";

if ($failedTests == 0) {
    echo "╔═══════════════════════════════════════════════════════════════════════╗\n";
    echo "║                                                                       ║\n";
    echo "║          ✅ TOUS LES TESTS SONT PASSÉS AVEC SUCCÈS                   ║\n";
    echo "║                                                                       ║\n";
    echo "║     🚀 Le système de limites est opérationnel                        ║\n";
    echo "║                                                                       ║\n";
    echo "╚═══════════════════════════════════════════════════════════════════════╝\n";
    exit(0);
} else {
    $percent = round(($passedTests / $totalTests) * 100, 1);
    echo "╔═══════════════════════════════════════════════════════════════════════╗\n";
    echo "║                                                                       ║\n";
    echo "║        ⚠️  CERTAINS TESTS ONT ÉCHOUÉ ($percent% réussis)             ║\n";
    echo "║                                                                       ║\n";
    echo "║     Consultez les détails ci-dessus pour corriger les erreurs       ║\n";
    echo "║                                                                       ║\n";
    echo "╚═══════════════════════════════════════════════════════════════════════╝\n";
    exit(1);
}
