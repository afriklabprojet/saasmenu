# 🧪 GUIDE COMPLET DES TESTS FONCTIONNELS
## Système d'Abonnement RestroSaaS

---

## 📋 TABLE DES MATIÈRES

1. [Préparation de l'environnement de test](#1-préparation)
2. [Tests Base de Données](#2-tests-base-de-données)
3. [Tests Administration des Plans](#3-tests-administration-des-plans)
4. [Tests Limites Produits](#4-tests-limites-produits)
5. [Tests Limites Catégories](#5-tests-limites-catégories)
6. [Tests Middleware](#6-tests-middleware)
7. [Tests Affichage Visual](#7-tests-affichage-visual)
8. [Tests Sécurité & Bypass](#8-tests-sécurité--bypass)
9. [Tests Performance](#9-tests-performance)
10. [Checklist Validation Finale](#10-checklist-validation-finale)

---

## 1. PRÉPARATION

### 1.1 Environnement de Test

```bash
# Vérifier l'environnement
php artisan --version        # Laravel 10.49.1
php --version                # PHP 8.4.8

# Vider tous les caches
php artisan config:clear
php artisan cache:clear
php artisan route:clear
php artisan view:clear

# Vérifier la base de données
php artisan migrate:status

# Créer une sauvegarde
php artisan backup:run       # ou
mysqldump -u root -p restro_saas > backup_before_tests.sql
```

### 1.2 Comptes de Test Nécessaires

| Type | Email | Mot de passe | Rôle |
|------|-------|--------------|------|
| Admin | admin@test.com | admin123 | type=1 |
| Vendor Plan Gratuit | vendor1@test.com | vendor123 | type=2, plan_id=1 |
| Vendor Plan Starter | vendor2@test.com | vendor123 | type=2, plan_id=2 |
| Vendor Plan Enterprise | vendor3@test.com | vendor123 | type=2, plan_id=5 |

### 1.3 État Initial de la Base de Données

```sql
-- Vérifier les plans existants
SELECT id, name, products_limit, categories_limit, staff_limit 
FROM pricing_plans 
ORDER BY price ASC;

-- Résultat attendu:
-- 1 | Plan Gratuit    | 5  | 1  | 1
-- 2 | Starter         | 50 | 15 | 3
-- 3 | Basic           | 100| 25 | 5
-- 4 | Professional    | 500| 50 | 15
-- 5 | Enterprise      | -1 | -1 | -1
```

---

## 2. TESTS BASE DE DONNÉES

### Test 2.1 : Vérification des Colonnes

**Objectif** : S'assurer que toutes les colonnes ont été ajoutées correctement.

```sql
-- Vérifier la structure de pricing_plans
DESCRIBE pricing_plans;

-- Colonnes attendues:
-- products_limit (int, nullable)
-- categories_limit (int, nullable)
-- staff_limit (int, nullable)
-- whatsapp_integration (tinyint, default 2)
-- analytics (tinyint, default 2)
-- custom_domain (tinyint, default 2)
```

**✅ Critères de réussite** :
- Toutes les colonnes existent
- Types de données corrects
- Valeurs par défaut définies

### Test 2.2 : Données des Plans

**Objectif** : Vérifier que les plans ont des limites cohérentes.

```sql
-- Vérifier Plan Gratuit
SELECT * FROM pricing_plans WHERE id = 1;

-- Vérifier que:
-- products_limit = 5
-- categories_limit = 1
-- staff_limit = 1
-- whatsapp_integration = 1 ou 2
-- analytics = 0 ou 2
```

**✅ Critères de réussite** :
- Plan Gratuit : limites strictes (5, 1, 1)
- Plans payants : limites progressives
- Plan Enterprise : -1 (illimité)

---

## 3. TESTS ADMINISTRATION DES PLANS

### Test 3.1 : Création d'un Nouveau Plan

**Étapes** :
1. Connexion en tant qu'Admin
2. Aller à `/admin/plan`
3. Cliquer "Ajouter nouveau plan"
4. Remplir le formulaire :
   - Nom : "Plan Test"
   - Prix : 25.00
   - Type : Mensuel
   - Durée : 1 mois
   - Products Limit : Sélectionner "Limité" → Entrer 20
   - Categories Limit : Sélectionner "Limité" → Entrer 10
   - Staff Limit : Sélectionner "Limité" → Entrer 5
   - Cocher "WhatsApp Integration"
   - Cocher "Analytics"
5. Cliquer "Sauvegarder"

**✅ Critères de réussite** :
```sql
-- Vérifier en base de données
SELECT name, products_limit, categories_limit, staff_limit, 
       whatsapp_integration, analytics 
FROM pricing_plans 
WHERE name = 'Plan Test';

-- Résultat attendu:
-- Plan Test | 20 | 10 | 5 | 1 | 1
```

**📸 Captures d'écran** :
- [ ] Formulaire rempli
- [ ] Message de succès
- [ ] Plan affiché dans la liste

### Test 3.2 : Modification d'un Plan Existant

**Étapes** :
1. Admin → Plans → Modifier "Plan Gratuit"
2. Changer Products Limit de 5 à 3
3. Changer Categories Limit de 1 à 2
4. Sauvegarder

**✅ Critères de réussite** :
```sql
SELECT products_limit, categories_limit 
FROM pricing_plans 
WHERE id = 1;

-- Résultat attendu:
-- 3 | 2
```

**⚠️ Attention** : Remettre les valeurs d'origine après le test !

### Test 3.3 : Plan Illimité

**Étapes** :
1. Admin → Plans → Modifier "Plan Enterprise"
2. Vérifier que tous les champs affichent "Illimité"
3. Sélectionner "Illimité" pour Products Limit
4. Sauvegarder

**✅ Critères de réussite** :
```sql
SELECT products_limit FROM pricing_plans WHERE id = 5;
-- Résultat: -1
```

---

## 4. TESTS LIMITES PRODUITS

### Test 4.1 : Ajout Produit Dans les Limites

**Contexte** : Vendor avec Plan Gratuit (5 produits max)

**Étapes** :
1. Connexion : vendor1@test.com
2. Aller à `/admin/products`
3. Vérifier l'indicateur : "Produits: 0/5 (0%)" - Badge VERT
4. Cliquer "Ajouter Produit"
5. Remplir et sauvegarder (produit 1)
6. Répéter jusqu'à 4 produits

**✅ Critères de réussite** :
- Produit 1 : Badge "1/5 (20%)" - VERT ✅
- Produit 2 : Badge "2/5 (40%)" - VERT ✅
- Produit 3 : Badge "3/5 (60%)" - VERT ✅
- Produit 4 : Badge "4/5 (80%)" - ORANGE ⚠️
  - Bouton "Upgrader le Plan" visible
  - Message d'avertissement sur page d'ajout

### Test 4.2 : Avertissement à 80%

**Contexte** : 4 produits sur 5 (80%)

**Étapes** :
1. Cliquer "Ajouter Produit"
2. Observer la page d'ajout

**✅ Critères de réussite** :
- Alerte orange visible en haut du formulaire
- Message : "⚠️ Attention ! Vous utilisez 4/5 produits (80%). Upgrader maintenant pour ajouter plus de produits."
- Lien "Upgrader maintenant" → `/admin/plan`

**📸 Capture d'écran** :
- [ ] Message d'avertissement affiché

### Test 4.3 : Limite Atteinte (100%)

**Contexte** : 5 produits sur 5

**Étapes** :
1. Ajouter le 5ème produit
2. Retourner à `/admin/products`
3. Observer l'indicateur

**✅ Critères de réussite** :
- Badge "5/5 (100%)" - ROUGE ❌
- Bouton "Ajouter Produit" DÉSACTIVÉ (grisé)
- Tooltip au survol : "Limite atteinte. Upgrader votre plan pour ajouter plus."
- Clic sur le bouton ne fait rien

**📸 Captures d'écran** :
- [ ] Badge rouge affiché
- [ ] Bouton désactivé
- [ ] Tooltip visible

### Test 4.4 : Blocage Formulaire d'Ajout

**Contexte** : 5/5 produits (limite atteinte)

**Étapes** :
1. Essayer d'accéder à `/admin/products/add`

**✅ Critères de réussite** :
- Redirection automatique vers `/admin/products`
- Message d'erreur : "Limite de produits atteinte. Upgrader pour ajouter plus."
- Impossible d'afficher le formulaire

### Test 4.5 : Blocage Sauvegarde

**Contexte** : 5/5 produits

**Étapes** :
1. Utiliser un outil (Postman, curl) pour envoyer une requête POST à `/admin/products/save`

```bash
curl -X POST http://localhost:8000/admin/products/save \
  -H "Cookie: laravel_session=..." \
  -d "product_name=Test&category=1&price=10"
```

**✅ Critères de réussite** :
- Requête bloquée
- Redirection avec message d'erreur
- Produit NON créé en base de données

### Test 4.6 : Plan Illimité

**Contexte** : Vendor avec Plan Enterprise (illimité)

**Étapes** :
1. Connexion : vendor3@test.com
2. Aller à `/admin/products`
3. Ajouter 50+ produits

**✅ Critères de réussite** :
- Badge affiche "50 / Illimité" - VERT
- Aucune barre de progression
- Bouton "Ajouter" toujours actif
- Aucun message d'avertissement

---

## 5. TESTS LIMITES CATÉGORIES

### Test 5.1 : Ajout Catégorie (Plan Gratuit)

**Contexte** : Vendor avec Plan Gratuit (1 catégorie max)

**Étapes** :
1. Connexion : vendor1@test.com
2. Aller à `/admin/categories`
3. Vérifier l'indicateur : "Catégories: 0/1 (0%)" - Badge VERT
4. Cliquer "Ajouter Catégorie"
5. Remplir et sauvegarder

**✅ Critères de réussite** :
- Avant : Badge "0/1 (0%)" - VERT
- Après : Badge "1/1 (100%)" - ROUGE
- Bouton "Ajouter" immédiatement DÉSACTIVÉ

### Test 5.2 : Limite Catégorie Atteinte

**Contexte** : 1/1 catégorie

**Étapes** :
1. Essayer de cliquer sur "Ajouter Catégorie"
2. Essayer d'accéder à `/admin/categories/add`

**✅ Critères de réussite** :
- Bouton désactivé, clic ne fait rien
- Accès direct bloqué
- Message : "Limite de catégories atteinte. Upgrader pour ajouter plus."
- Redirection vers `/admin/categories`

### Test 5.3 : Catégories avec Plan Starter

**Contexte** : Plan Starter (15 catégories max)

**Étapes** :
1. Connexion : vendor2@test.com
2. Ajouter 12 catégories (80%)
3. Observer l'indicateur

**✅ Critères de réussite** :
- Badge "12/15 (80%)" - ORANGE
- Message d'avertissement sur page d'ajout
- Bouton "Upgrader" visible

---

## 6. TESTS MIDDLEWARE

### Test 6.1 : Protection Route Products

**Étapes** :
1. Vendor avec limite atteinte (5/5 produits)
2. Essayer d'accéder à `/admin/products/add`

**✅ Critères de réussite** :
- Middleware `SubscriptionLimitMiddleware` déclenché
- Vérification dans `ProductController::add()` aussi
- Double protection active

### Test 6.2 : Protection Route Categories

**Étapes** :
1. Vendor avec limite atteinte (1/1 catégorie)
2. Essayer d'accéder à `/admin/categories/add`

**✅ Critères de réussite** :
- Blocage par le contrôleur
- Message d'erreur affiché
- Redirection correcte

### Test 6.3 : Admin Exempt

**Étapes** :
1. Connexion en tant qu'Admin (type=1)
2. Accéder à `/admin/products`

**✅ Critères de réussite** :
- Aucun indicateur de limite affiché
- Pas de vérification pour l'admin
- Accès complet à toutes les fonctionnalités

---

## 7. TESTS AFFICHAGE VISUAL

### Test 7.1 : Couleurs des Badges

**Vérifier sur** : `/admin/products` et `/admin/categories`

| Usage | Couleur | Classe Bootstrap | Visual |
|-------|---------|------------------|--------|
| 0-79% | Vert | alert-success | ✅ |
| 80-99% | Orange | alert-warning | ⚠️ |
| 100% | Rouge | alert-danger | ❌ |

**✅ Critères de réussite** :
- Transitions de couleur automatiques
- Barre de progression correspond à la couleur

### Test 7.2 : Barres de Progression

**Étapes** :
1. Vendor avec 3/5 produits
2. Observer la barre de progression

**✅ Critères de réussite** :
- Largeur : 60% de la barre
- Couleur : verte
- Animation smooth

### Test 7.3 : Tooltips

**Étapes** :
1. Limite atteinte (bouton désactivé)
2. Survoler le bouton "Ajouter"

**✅ Critères de réussite** :
- Tooltip apparaît au survol
- Message : "Limite atteinte. Upgrader votre plan pour ajouter plus."
- Tooltip disparaît en quittant

### Test 7.4 : Responsive Design

**Tester sur** :
- Desktop (1920x1080)
- Tablette (768x1024)
- Mobile (375x667)

**✅ Critères de réussite** :
- Indicateurs visibles sur tous les écrans
- Pas de débordement
- Boutons cliquables

---

## 8. TESTS SÉCURITÉ & BYPASS

### Test 8.1 : Tentative Bypass API

**Étapes** :
```bash
# Essayer de créer un produit via API quand limite atteinte
curl -X POST http://localhost:8000/admin/products/save \
  -H "Content-Type: application/json" \
  -H "Cookie: laravel_session=xxx" \
  -d '{"product_name":"Bypass Test","category":1,"price":10}'
```

**✅ Critères de réussite** :
- Requête bloquée
- Validation côté serveur active
- Aucune insertion en base

### Test 8.2 : Manipulation Session

**Étapes** :
1. Limite atteinte (5/5)
2. Ouvrir DevTools → Application → Cookies
3. Essayer de modifier les valeurs de session

**✅ Critères de réussite** :
- Vérification côté serveur à chaque requête
- Session manipulation inefficace
- Limites toujours appliquées

### Test 8.3 : SQL Injection

**Étapes** :
```bash
# Tenter injection dans le nom du produit
curl -X POST http://localhost:8000/admin/products/save \
  -d "product_name=' OR '1'='1&category=1&price=10"
```

**✅ Critères de réussite** :
- Protection Eloquent ORM active
- Pas d'erreur SQL exposée
- Requête bloquée ou échappée

### Test 8.4 : Import en Masse

**Étapes** :
1. Vendor avec limite (5 produits)
2. Essayer d'importer 10 produits via CSV (si fonction existe)

**✅ Critères de réussite** :
- Import bloqué à la limite
- Message d'erreur approprié
- Seulement 5 produits importés max

---

## 9. TESTS PERFORMANCE

### Test 9.1 : Temps de Chargement

**Mesurer avec Chrome DevTools** :

| Page | Temps attendu | Requêtes SQL |
|------|---------------|--------------|
| /admin/products | < 500ms | 3-5 |
| /admin/categories | < 300ms | 2-3 |
| /admin/plan | < 400ms | 2-4 |

**✅ Critères de réussite** :
- Pages chargent en moins de 500ms
- Pas de requête N+1
- Cache utilisé efficacement

### Test 9.2 : getPlanInfo() Performance

**Test de charge** :
```php
// Dans tinker
php artisan tinker

$start = microtime(true);
for ($i = 0; $i < 1000; $i++) {
    helper::getPlanInfo(2);
}
$end = microtime(true);
echo "Temps: " . ($end - $start) . "s\n";
```

**✅ Critères de réussite** :
- 1000 appels < 1 seconde
- Possibilité d'ajouter cache si trop lent

### Test 9.3 : Charge Utilisateurs Concurrent

**Simulation** :
- 10 vendors ajoutant des produits simultanément

**✅ Critères de réussite** :
- Pas de race condition
- Limites respectées pour chaque vendor
- Pas de deadlock en base

---

## 10. CHECKLIST VALIDATION FINALE

### 10.1 Base de Données ✅

- [ ] Toutes les migrations exécutées sans erreur
- [ ] Colonnes `products_limit`, `categories_limit`, `staff_limit` présentes
- [ ] Colonnes `whatsapp_integration`, `analytics` présentes
- [ ] Plan Gratuit : limites correctes (5, 1, 1)
- [ ] Plan Enterprise : valeurs -1 (illimité)

### 10.2 Backend ✅

- [ ] `PlanPricingController::save_plan()` sauvegarde toutes les colonnes
- [ ] `PlanPricingController::update_plan()` met à jour toutes les colonnes
- [ ] `ProductController::add()` vérifie la limite avant affichage
- [ ] `ProductController::save()` vérifie la limite avant insertion
- [ ] `CategoryController::add_category()` vérifie la limite
- [ ] `CategoryController::save_category()` vérifie la limite
- [ ] `helper::getPlanInfo()` retourne un tableau structuré
- [ ] Gestion des cas null sans erreur

### 10.3 Frontend ✅

- [ ] Indicateur de limite affiché sur `/admin/products`
- [ ] Indicateur de limite affiché sur `/admin/categories`
- [ ] Couleurs dynamiques (vert, orange, rouge)
- [ ] Barre de progression affichée correctement
- [ ] Bouton "Ajouter" désactivé quand limite atteinte
- [ ] Tooltip explicatif au survol du bouton désactivé
- [ ] Message d'avertissement à 80%
- [ ] Bouton "Upgrader" visible à partir de 80%
- [ ] Plans illimités affichent "X / Illimité"

### 10.4 Traductions ✅

- [ ] `labels.product_limit_reached` existe en FR
- [ ] `labels.category_limit_reached` existe en FR
- [ ] `labels.upgrade_to_add_more` existe en FR
- [ ] `labels.upgrade_plan` existe en FR
- [ ] `labels.you_are_using` existe en FR
- [ ] `labels.upgrade_now` existe en FR
- [ ] Messages d'erreur affichés en français

### 10.5 Sécurité ✅

- [ ] Validation côté serveur active
- [ ] Impossible de bypass via URL directe
- [ ] Impossible de bypass via API
- [ ] Middleware protection active
- [ ] Admin exempt des limitations
- [ ] Pas de fuite d'informations sensibles

### 10.6 UX ✅

- [ ] Indicateurs visuels clairs et intuitifs
- [ ] Messages d'erreur explicites
- [ ] Workflow upgrade fluide
- [ ] Responsive sur mobile/tablette/desktop
- [ ] Pas de confusion pour l'utilisateur
- [ ] Performance acceptable (<500ms)

---

## 📊 RAPPORT DE TEST

### Format du Rapport

```markdown
# Rapport de Test - [Date]

## Testeur
- Nom: [Votre nom]
- Date: [Date du test]
- Version: Laravel 10.49.1 / PHP 8.4.8

## Résultats Globaux
- Tests réussis: X/Y (Z%)
- Tests échoués: X
- Tests bloqués: X

## Détails par Catégorie

### Base de Données
- ✅ Test 2.1: Colonnes présentes
- ✅ Test 2.2: Données cohérentes

### Administration Plans
- ✅ Test 3.1: Création plan
- ⚠️ Test 3.2: Modification (bug mineur détecté)
- ✅ Test 3.3: Plan illimité

[... etc ...]

## Bugs Détectés

### Bug #1: [Titre]
- **Sévérité**: Critique / Majeure / Mineure
- **Description**: [Description]
- **Steps to reproduce**: [Étapes]
- **Expected**: [Résultat attendu]
- **Actual**: [Résultat obtenu]
- **Screenshot**: [Lien]

## Recommandations

1. [Recommandation 1]
2. [Recommandation 2]

## Conclusion

[Système prêt pour production ? Oui/Non]
[Commentaires additionnels]
```

---

## 🚀 COMMANDES UTILES

```bash
# Lancer le serveur de test
php artisan serve

# Ouvrir tinker pour tests rapides
php artisan tinker

# Vérifier les logs
tail -f storage/logs/laravel.log

# Tester une route spécifique
php artisan route:list | grep product

# Vider les caches entre les tests
php artisan optimize:clear

# Recharger la base de données
php artisan migrate:fresh --seed

# Créer un backup avant tests
php artisan backup:run

# Restaurer après tests
mysql -u root -p restro_saas < backup_before_tests.sql
```

---

## 📞 SUPPORT

En cas de problème pendant les tests :

1. Vérifier les logs : `storage/logs/laravel.log`
2. Vérifier la console du navigateur (F12)
3. Tester en mode debug : `APP_DEBUG=true` dans `.env`
4. Vider tous les caches
5. Vérifier que les migrations sont à jour

---

**Date de création** : 23 octobre 2025  
**Version du document** : 1.0  
**Statut** : ✅ Prêt pour exécution
