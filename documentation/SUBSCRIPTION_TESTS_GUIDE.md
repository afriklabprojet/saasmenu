# 🧪 GUIDE DE TEST - SYSTÈME D'ABONNEMENT

## 🎯 Objectif
Vérifier que le système d'abonnement fonctionne correctement avec les limites par plan.

---

## 🚀 PRÉPARATION

### 1. Vider les caches
```bash
cd /Users/teya2023/Documents/codecayon\ SaaS/restrosaas-37/saas-whatsapp/restro-saas
php artisan config:clear
php artisan route:clear
php artisan cache:clear
php artisan view:clear
```

### 2. Vérifier la configuration des plans
```bash
php artisan tinker
```

```php
// Afficher tous les plans avec leurs limites
PricingPlan::all(['id', 'name', 'price', 'products_limit', 'categories_limit', 'custom_domain', 'analytics'])->toArray();

// Résultat attendu:
// 5 plans avec limites configurées
```

### 3. Vérifier les vendors
```php
// Dans tinker
DB::table('users')->where('type', 2)->get(['id', 'name', 'plan_id', 'allow_without_subscription'])->toArray();

// Tous les vendors doivent avoir plan_id = 1 (Plan Gratuit)
// allow_without_subscription = 2 (désactivé)
```

---

## 📝 TESTS FONCTIONNELS

### TEST 1: Inscription nouveau vendor ✅

**Objectif:** Vérifier auto-assignation du Plan Gratuit

**Étapes:**
1. Aller sur `/register`
2. Remplir formulaire inscription vendor
3. Soumettre

**Résultat attendu:**
- ✅ Compte créé avec succès
- ✅ `plan_id` = 1 (Plan Gratuit)
- ✅ `purchase_date` = Date du jour
- ✅ `purchase_amount` = 0
- ✅ `allow_without_subscription` = 2

**Vérification en base:**
```sql
SELECT id, name, plan_id, purchase_date, purchase_amount, allow_without_subscription 
FROM users 
WHERE email = 'email_du_nouveau_vendor@example.com';
```

---

### TEST 2: Limite produits (Plan Gratuit) ✅

**Objectif:** Bloquer ajout du 11ème produit

**Prérequis:**
- Vendor avec Plan Gratuit (10 produits max)
- Avoir déjà 10 produits créés

**Étapes:**
1. Se connecter comme vendor
2. Aller sur `/admin/products`
3. Vérifier le nombre actuel de produits (devrait être 10/10)
4. Cliquer sur "Ajouter produit" ou aller sur `/admin/products/add`

**Résultat attendu:**
- ❌ Redirection vers page précédente
- ❌ Message flash d'erreur: "Limite de produits atteinte pour votre plan. Veuillez mettre à niveau."
- ❌ Formulaire d'ajout non accessible

**Vérification code:**
```php
// Route protégée dans routes/web.php
Route::get('products/add', [ItemController::class, 'add_product'])
    ->middleware('subscription.limit:products');
```

**Test sans limite:**
- Mettre à jour le vendor avec plan_id = 5 (Enterprise - illimité)
- Réessayer l'ajout → ✅ Devrait fonctionner

---

### TEST 3: Limite catégories (Plan Gratuit) ✅

**Objectif:** Bloquer ajout de la 6ème catégorie

**Prérequis:**
- Vendor avec Plan Gratuit (5 catégories max)
- Avoir déjà 5 catégories créées

**Étapes:**
1. Se connecter comme vendor
2. Aller sur `/admin/categories`
3. Vérifier le nombre de catégories (devrait être 5/5)
4. Cliquer sur "Ajouter catégorie" ou aller sur `/admin/categories/add`

**Résultat attendu:**
- ❌ Redirection vers page précédente
- ❌ Message flash: "Limite de catégories atteinte pour votre plan. Veuillez mettre à niveau."

**Vérification:**
```php
// Route protégée
Route::get('add', [CategoryController::class, 'add_category'])
    ->middleware('subscription.limit:categories');
```

---

### TEST 4: Domaine personnalisé (Plan Gratuit) ❌

**Objectif:** Bloquer accès domaine personnalisé

**Prérequis:**
- Vendor avec Plan Gratuit (custom_domain = 0)

**Étapes:**
1. Se connecter comme vendor
2. Aller sur `/admin/custom-domain`

**Résultat attendu:**
- ❌ Redirection immédiate
- ❌ Message: "Le domaine personnalisé n'est pas disponible dans votre plan actuel."
- ❌ Page de configuration non accessible

**Test avec Starter:**
1. Mettre à jour vendor: `plan_id = 2` (Starter)
2. Retourner sur `/admin/custom-domain`
3. ✅ Page devrait être accessible

**Vérification:**
```php
// Groupe de routes protégé
Route::group([
    'prefix' => 'custom-domain',
    'middleware' => 'subscription.limit:custom_domain'
], ...);
```

---

### TEST 5: Analytics (Plan Gratuit) ❌

**Objectif:** Bloquer accès aux analytics

**Prérequis:**
- Vendor avec Plan Gratuit (analytics = 0)

**Étapes:**
1. Se connecter comme vendor
2. Essayer d'accéder à `/admin/analytics/dashboard`

**Résultat attendu:**
- ❌ Redirection
- ❌ Message: "Les analyses ne sont pas disponibles dans votre plan actuel."

**Routes à tester:**
- `/admin/analytics/dashboard` ❌
- `/admin/analytics/revenue` ❌
- `/admin/analytics/top-selling` ❌
- `/admin/analytics/peak-hours` ❌
- `/admin/analytics/customers` ❌
- `/admin/analytics/categories` ❌
- `/admin/analytics/compare` ❌
- `/admin/analytics/export` ❌

**Test avec Starter:**
1. Mettre à jour vendor: `plan_id = 2`
2. ✅ Toutes les pages analytics doivent être accessibles

---

### TEST 6: Upgrade de plan ✅

**Objectif:** Vérifier déblocage fonctionnalités après upgrade

**Scénario:**
1. Vendor Plan Gratuit → Upgrade vers Starter

**Avant (Plan Gratuit):**
- ❌ Custom Domain bloqué
- ❌ Analytics bloqué
- ✅ 10 produits max
- ✅ 5 catégories max

**Simulation upgrade:**
```sql
UPDATE users 
SET plan_id = 2, 
    purchase_date = NOW(), 
    purchase_amount = 4.99 
WHERE id = [vendor_id];
```

**Après (Starter):**
- ✅ Custom Domain accessible
- ✅ Analytics accessible
- ✅ 50 produits max
- ✅ 15 catégories max

**Vérification:**
1. Accéder `/admin/custom-domain` → ✅ OK
2. Accéder `/admin/analytics/dashboard` → ✅ OK
3. Ajouter 11ème produit → ✅ OK (limite maintenant 50)
4. Ajouter 6ème catégorie → ✅ OK (limite maintenant 15)

---

## 🔧 TESTS TECHNIQUES

### TEST 7: Middleware registration

**Commande:**
```bash
php artisan route:list --name=subscription
```

**Résultat attendu:**
Aucune route nommée "subscription" car c'est un middleware, pas une route.

**Vérification dans Kernel.php:**
```bash
grep -n "subscription.limit" app/Http/Kernel.php
```

**Résultat attendu:**
```
[ligne]: 'subscription.limit' => \App\Http\Middleware\SubscriptionLimitMiddleware::class,
```

---

### TEST 8: Helper functions

**Commande:**
```bash
php artisan tinker
```

```php
// Test checkPlanLimit (via la classe Helper)
$vendor = User::where('type', 2)->first();

// Test avec vendor Plan Gratuit
$canAddProduct = Helper::checkPlanLimit($vendor->id, 'products');
// Retour attendu: true si < 10 produits, false si >= 10

$canUseDomain = Helper::checkPlanLimit($vendor->id, 'custom_domain');
// Retour attendu: false (Plan Gratuit)

$canUseAnalytics = Helper::checkPlanLimit($vendor->id, 'analytics');
// Retour attendu: false (Plan Gratuit)

// Test getPlanInfo
$planInfo = Helper::getPlanInfo($vendor->id);
echo $planInfo->name; // "Plan Gratuit"
echo $planInfo->products_limit; // 10
echo $planInfo->custom_domain; // 0
```

---

### TEST 9: Comptage actuel vs limite

**Commande:**
```bash
php artisan tinker
```

```php
$vendor = User::where('type', 2)->first();
$plan = PricingPlan::find($vendor->plan_id);

echo "📊 VENDOR: {$vendor->name}\n";
echo "📋 PLAN: {$plan->name}\n\n";

// Produits
$products_count = Item::where('vendor_id', $vendor->id)->count();
echo "Produits: {$products_count} / {$plan->products_limit}\n";

// Catégories
$categories_count = \App\Models\Category::where('vendor_id', $vendor->id)->count();
echo "Catégories: {$categories_count} / {$plan->categories_limit}\n";

// Statut
if ($products_count >= $plan->products_limit) {
    echo "⚠️ LIMITE PRODUITS ATTEINTE!\n";
}

if ($categories_count >= $plan->categories_limit) {
    echo "⚠️ LIMITE CATÉGORIES ATTEINTE!\n";
}
```

---

## 🎭 SCÉNARIOS DE TEST COMPLETS

### SCÉNARIO A: Nouveau restaurant (Plan Gratuit)

**Étape 1 - Inscription:**
1. Créer compte vendor
2. Vérifier plan_id = 1

**Étape 2 - Ajout produits:**
1. Ajouter 10 produits → ✅ OK
2. Essayer d'ajouter 11ème → ❌ Bloqué

**Étape 3 - Ajout catégories:**
1. Ajouter 5 catégories → ✅ OK
2. Essayer d'ajouter 6ème → ❌ Bloqué

**Étape 4 - Tentative analytics:**
1. Accéder analytics → ❌ Bloqué

**Étape 5 - Tentative custom domain:**
1. Accéder custom-domain → ❌ Bloqué

**Conclusion:** Plan Gratuit fonctionne avec restrictions

---

### SCÉNARIO B: Upgrade vers Starter

**Étape 1 - Upgrade:**
```sql
UPDATE users SET plan_id = 2, purchase_amount = 4.99 WHERE id = [vendor_id];
```

**Étape 2 - Vérifications:**
1. Analytics accessible → ✅ OK
2. Custom domain accessible → ✅ OK
3. Ajouter 11ème produit → ✅ OK (limite 50)
4. Ajouter 6ème catégorie → ✅ OK (limite 15)

**Conclusion:** Upgrade débloque fonctionnalités premium

---

### SCÉNARIO C: Grande entreprise (Enterprise)

**Setup:**
```sql
UPDATE users SET plan_id = 5, purchase_amount = 99.99 WHERE id = [vendor_id];
```

**Tests:**
1. Ajouter 100+ produits → ✅ OK (illimité)
2. Ajouter 50+ catégories → ✅ OK (illimité)
3. Analytics → ✅ OK
4. Custom domain → ✅ OK

**Conclusion:** Enterprise = accès total sans limites

---

## 📊 TABLEAU RÉCAPITULATIF DES TESTS

| Test | Fonctionnalité | Plan Gratuit | Starter | Enterprise | Statut |
|------|----------------|--------------|---------|------------|--------|
| 1 | Auto-assignation inscription | ✅ Plan Gratuit | N/A | N/A | ✅ |
| 2 | Limite 10 produits | ❌ Bloqué à 10 | ✅ 50 max | ✅ Illimité | ✅ |
| 3 | Limite 5 catégories | ❌ Bloqué à 5 | ✅ 15 max | ✅ Illimité | ✅ |
| 4 | Custom Domain | ❌ Bloqué | ✅ Autorisé | ✅ Autorisé | ✅ |
| 5 | Analytics | ❌ Bloqué | ✅ Autorisé | ✅ Autorisé | ✅ |
| 6 | Upgrade déblocage | N/A | ✅ Fonctionnalités débloquées | ✅ | ✅ |
| 7 | Middleware registration | ✅ | ✅ | ✅ | ✅ |
| 8 | Helper functions | ✅ | ✅ | ✅ | ✅ |
| 9 | Comptage vs limite | ✅ | ✅ | ✅ | ✅ |

---

## 🐛 DÉPANNAGE

### Problème 1: Middleware ne bloque pas
**Cause:** Cache non vidé  
**Solution:**
```bash
php artisan config:clear
php artisan route:clear
php artisan cache:clear
```

---

### Problème 2: Vendor peut ajouter produits malgré limite
**Vérifications:**
1. Vérifier plan du vendor: `SELECT plan_id FROM users WHERE id = X;`
2. Vérifier limite du plan: `SELECT products_limit FROM pricing_plans WHERE id = Y;`
3. Compter produits: `SELECT COUNT(*) FROM products WHERE vendor_id = X;`
4. Vérifier route: `grep "products/add" routes/web.php` → doit avoir `->middleware('subscription.limit:products')`

---

### Problème 3: Analytics accessible avec Plan Gratuit
**Vérifications:**
1. Vérifier plan: `SELECT analytics FROM pricing_plans WHERE id = 1;` → doit être 0
2. Vérifier route group: ligne ~210 de routes/web.php → doit avoir `'middleware' => 'subscription.limit:analytics'`
3. Vider cache routes

---

### Problème 4: Helper functions non trouvées
**Cause:** Helpers pas dans namespace global  
**Solution:** Utiliser via classe Helper:
```php
Helper::checkPlanLimit($vendor_id, 'products');
Helper::getPlanInfo($vendor_id);
```

---

## ✅ VALIDATION FINALE

**Checklist avant mise en production:**

- [ ] Migration exécutée (7 colonnes ajoutées)
- [ ] 5 plans configurés avec limites
- [ ] Middleware créé et enregistré
- [ ] Routes protégées (17 routes)
- [ ] Caches vidés
- [ ] Test Plan Gratuit: produits bloqués à 10 ✅
- [ ] Test Plan Gratuit: catégories bloquées à 5 ✅
- [ ] Test Plan Gratuit: custom domain bloqué ✅
- [ ] Test Plan Gratuit: analytics bloqué ✅
- [ ] Test Starter: custom domain accessible ✅
- [ ] Test Starter: analytics accessible ✅
- [ ] Test Enterprise: tout illimité ✅
- [ ] Helper functions fonctionnelles ✅
- [ ] Messages d'erreur affichés ✅

---

## 📞 COMMANDES UTILES

### Vider tous les caches
```bash
php artisan optimize:clear
```

### Afficher routes avec middleware
```bash
php artisan route:list | grep "subscription.limit"
```

### Tester en tinker
```bash
php artisan tinker
```

```php
// Quick test
$v = User::where('type', 2)->first();
$p = PricingPlan::find($v->plan_id);
echo "{$v->name} - {$p->name} - Produits: {$p->products_limit}\n";
```

### Réinitialiser un vendor en Plan Gratuit
```php
// Dans tinker
$vendor = User::find([id]);
$vendor->plan_id = 1;
$vendor->purchase_amount = 0;
$vendor->allow_without_subscription = 2;
$vendor->save();
```

### Simuler upgrade Starter
```php
$vendor = User::find([id]);
$vendor->plan_id = 2;
$vendor->purchase_amount = 4.99;
$vendor->purchase_date = now();
$vendor->save();
```

---

## 🎯 RÉSULTAT ATTENDU

À la fin de tous les tests, vous devez avoir:

✅ **Plan Gratuit** limite correctement à 10 produits et 5 catégories  
✅ **Custom Domain** bloqué pour Plan Gratuit, accessible dès Starter  
✅ **Analytics** bloqué pour Plan Gratuit, accessible dès Starter  
✅ **Messages d'erreur** clairs avec invitation à upgrader  
✅ **Nouveaux vendors** reçoivent automatiquement Plan Gratuit  
✅ **Upgrade** débloque instantanément les fonctionnalités  

---

**Date:** 23 Octobre 2025  
**Version:** 1.0.0  
**Statut:** ✅ Prêt pour tests
