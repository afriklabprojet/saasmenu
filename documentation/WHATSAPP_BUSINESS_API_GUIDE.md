# 📱 GUIDE D'INTÉGRATION WHATSAPP BUSINESS API

**Date**: 23 octobre 2025  
**Système**: Envoi automatique de messages WhatsApp  
**Statut**: ✅ **PRÊT POUR PRODUCTION**

---

## 📋 TABLE DES MATIÈRES

1. [Vue d'ensemble](#vue-densemble)
2. [Prérequis](#prérequis)
3. [Configuration Meta Business](#configuration-meta-business)
4. [Installation](#installation)
5. [Configuration Environnement](#configuration-environnement)
6. [Tests](#tests)
7. [Utilisation](#utilisation)
8. [Monitoring](#monitoring)
9. [Dépannage](#dépannage)
10. [Limites et Quotas](#limites-et-quotas)

---

## 🎯 VUE D'ENSEMBLE

### Ce qui a été implémenté

✅ **WhatsAppBusinessService** - Service complet d'envoi de messages  
✅ **Intégration OrderController** - Envoi automatique lors des changements de statut  
✅ **WhatsAppLog Model** - Logging en base de données  
✅ **Configuration complète** - Fichier config/whatsapp.php  
✅ **Migration** - Table whatsapp_logs pour traçabilité  
✅ **Gestion d'erreurs** - Retry automatique et logging

### Architecture

```
Client commande → Restaurant accepte/annule
                        ↓
              OrderController@update()
                        ↓
          sendWhatsAppNotification()
                        ↓
          WhatsAppBusinessService
                        ↓
        Meta WhatsApp Business API
                        ↓
            Client reçoit le message
                        ↓
              WhatsAppLog (BDD)
```

---

## ✅ PRÉREQUIS

### 1. Compte Meta Business

Vous devez avoir :
- ✅ Un compte Facebook Business Manager
- ✅ Une application Meta (Facebook App)
- ✅ Accès à WhatsApp Business API
- ✅ Un numéro de téléphone vérifié

### 2. Serveur

- ✅ PHP 8.1+
- ✅ Laravel 10.x
- ✅ MySQL 5.7+
- ✅ HTTPS/SSL actif (obligatoire pour webhooks)
- ✅ Domaine public accessible

---

## 🔧 CONFIGURATION META BUSINESS

### Étape 1: Créer une App Meta

1. **Accéder à Meta Developers**
   ```
   https://developers.facebook.com/apps/
   ```

2. **Créer une nouvelle app**
   - Type: **Business**
   - Nom: `E-menu WhatsApp Notifications`
   - Email de contact professionnel

3. **Ajouter le produit WhatsApp**
   - Dashboard → Ajouter un produit
   - Sélectionner **WhatsApp** → Configuration

### Étape 2: Configurer WhatsApp Business

1. **Sélectionner un compte WhatsApp Business**
   - Créer un nouveau compte ou utiliser existant
   - Nom commercial: Nom de votre restaurant

2. **Ajouter un numéro de téléphone**
   - Cliquer sur "Ajouter un numéro de téléphone"
   - Vérifier le numéro via SMS
   - ⚠️ **Important**: Ce numéro sera utilisé pour ENVOYER les messages

3. **Obtenir le Phone Number ID**
   ```
   WhatsApp → API Setup → Phone Number ID
   ```
   Copier le numéro (ex: `123456789012345`)

### Étape 3: Générer un Token d'Accès

1. **Token temporaire (24h) - Pour tester**
   ```
   WhatsApp → API Setup → Temporary Access Token
   ```

2. **Token permanent - Pour production**
   
   a. Créer un Système Utilisateur
   ```
   Business Settings → Utilisateurs → Utilisateurs système
   → Ajouter → Nom: "WhatsApp API"
   ```

   b. Assigner les permissions
   ```
   Permissions requises:
   - whatsapp_business_messaging
   - whatsapp_business_management
   ```

   c. Générer le token
   ```
   Utilisateur système → Générer un nouveau jeton
   → Sélectionner l'application
   → Sélectionner les permissions
   → Générer le jeton
   ```

   ⚠️ **IMPORTANT**: Copiez et sauvegardez ce token immédiatement, il ne sera plus affiché !

### Étape 4: Obtenir les IDs

Récupérez les informations suivantes :

| Variable | Où trouver | Exemple |
|----------|------------|---------|
| **WHATSAPP_API_TOKEN** | Système Utilisateur → Tokens | `EAAxxxxxxxxxxxxx` |
| **WHATSAPP_PHONE_NUMBER_ID** | WhatsApp → API Setup | `123456789012345` |
| **WHATSAPP_BUSINESS_ACCOUNT_ID** | WhatsApp → Settings | `123456789012345` |
| **WHATSAPP_APP_SECRET** | Paramètres de l'app → Basique | `abc123...` |

---

## 💻 INSTALLATION

### 1. Migrer la base de données

```bash
cd /path/to/restro-saas
php artisan migrate
```

Cela créera la table `whatsapp_logs` :

```sql
CREATE TABLE whatsapp_logs (
    id BIGINT PRIMARY KEY AUTO_INCREMENT,
    to VARCHAR(20) INDEX,
    message TEXT,
    status VARCHAR(100),
    success BOOLEAN INDEX,
    message_id VARCHAR(100),
    response JSON,
    context JSON,
    sent_at TIMESTAMP,
    created_at TIMESTAMP,
    updated_at TIMESTAMP
);
```

### 2. Vérifier les fichiers

✅ Fichiers créés :
```
app/Services/WhatsAppBusinessService.php
app/Models/WhatsAppLog.php
database/migrations/2025_10_23_015418_create_whatsapp_logs_table.php
config/whatsapp.php
```

✅ Fichiers modifiés :
```
app/Http/Controllers/admin/OrderController.php (import + méthode)
.env.example (variables ajoutées)
```

---

## ⚙️ CONFIGURATION ENVIRONNEMENT

### 1. Copier les variables dans `.env`

```bash
# WhatsApp Business API Configuration
WHATSAPP_API_URL=https://graph.facebook.com/v18.0
WHATSAPP_API_TOKEN=EAAxxxxxxxxxxxxxxxxxxxxxxxxx
WHATSAPP_PHONE_NUMBER_ID=123456789012345
WHATSAPP_BUSINESS_ACCOUNT_ID=123456789012345
WHATSAPP_APP_SECRET=abc123def456
WHATSAPP_WEBHOOK_VERIFY_TOKEN=emenu_whatsapp_2024
WHATSAPP_WEBHOOK_URL=https://votre-domaine.com/api/whatsapp/webhook

# Configuration
WHATSAPP_DEFAULT_COUNTRY_CODE=225
WHATSAPP_TIMEOUT=30
WHATSAPP_ENABLED=true
WHATSAPP_DEMO_MODE=false

# Notifications automatiques
WHATSAPP_AUTO_NOTIFY_ORDER_ACCEPTED=true
WHATSAPP_AUTO_NOTIFY_ORDER_DELIVERED=true
WHATSAPP_AUTO_NOTIFY_ORDER_CANCELLED=true
```

### 2. Remplacer les valeurs

| Variable | Valeur | Source |
|----------|--------|--------|
| `WHATSAPP_API_TOKEN` | Votre token permanent | Meta Business Manager |
| `WHATSAPP_PHONE_NUMBER_ID` | ID de votre numéro | WhatsApp API Setup |
| `WHATSAPP_BUSINESS_ACCOUNT_ID` | ID du compte | WhatsApp Settings |
| `WHATSAPP_APP_SECRET` | App Secret | Paramètres de l'app |
| `WHATSAPP_WEBHOOK_URL` | https://votre-domaine.com/api/... | Votre domaine |

### 3. Mode Démo (Recommandé pour débuter)

Pour tester sans envoyer de vrais messages :

```bash
WHATSAPP_DEMO_MODE=true
```

En mode démo :
- ✅ Les messages sont générés
- ✅ Les logs sont créés
- ❌ Aucun message n'est envoyé
- ✅ Vous voyez exactement ce qui serait envoyé

### 4. Activer l'envoi réel

Une fois les tests validés :

```bash
WHATSAPP_DEMO_MODE=false
WHATSAPP_ENABLED=true
```

### 5. Clear cache

```bash
php artisan config:clear
php artisan cache:clear
```

---

## 🧪 TESTS

### Test 1: Vérifier la configuration

Créez un fichier `test-whatsapp-api.php` dans `public/` :

```php
<?php
require __DIR__ . '/../vendor/autoload.php';

$app = require_once __DIR__ . '/../bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Services\WhatsAppBusinessService;

$service = new WhatsAppBusinessService();

// Test de connexion
$result = $service->testConnection();

echo "=== TEST CONNEXION WHATSAPP API ===\n";
echo json_encode($result, JSON_PRETTY_PRINT);
echo "\n";

if ($result['success']) {
    echo "✅ Connexion réussie !\n";
    echo "Nom du numéro: " . $result['details']['display_phone_number'] . "\n";
} else {
    echo "❌ Échec de connexion\n";
    echo "Erreur: " . $result['message'] . "\n";
}
```

Exécuter :

```bash
php public/test-whatsapp-api.php
```

### Test 2: Envoyer un message test

```php
<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Services\WhatsAppBusinessService;

$service = new WhatsAppBusinessService();

// Remplacer par votre numéro de test
$testPhone = '2250709123456'; // Format: code pays + numéro

$message = "🎉 Test E-menu WhatsApp\n\n";
$message .= "Ceci est un message de test.\n";
$message .= "Si vous recevez ce message, l'intégration fonctionne ! ✅";

$result = $service->sendTextMessage($testPhone, $message, [
    'test' => true,
    'sent_by' => 'manual_test'
]);

echo "=== RÉSULTAT ENVOI TEST ===\n";
echo json_encode($result, JSON_PRETTY_PRINT);
echo "\n";

if ($result['success']) {
    echo "✅ Message envoyé !\n";
    echo "Message ID: " . ($result['context']['message_id'] ?? 'N/A') . "\n";
} else {
    echo "❌ Échec d'envoi\n";
    echo "Erreur: " . $result['status'] . "\n";
}
```

### Test 3: Simuler un changement de statut de commande

```bash
# En mode démo
WHATSAPP_DEMO_MODE=true

# 1. Créer une commande test dans l'admin
# 2. Accepter la commande
# 3. Vérifier les logs

tail -f storage/logs/laravel.log | grep WhatsApp
```

Vous devriez voir :

```
[2025-10-23 16:30:45] local.INFO: WhatsApp message sent {
    "success": true,
    "to": "2250709123456",
    "template": "order_confirmed",
    "message_id": "wamid.xxxxx"
}
```

---

## 🚀 UTILISATION

### Flux Automatique

Le système fonctionne automatiquement :

1. **Restaurant accepte une commande** (type 2)
   ```
   Admin → Orders → [Commande] → Accepter
   ```
   → Client reçoit "✅ Commande Confirmée"

2. **Restaurant marque prête** (type 3)
   ```
   Admin → Orders → [Commande] → Marquer livrée
   ```
   → Client reçoit "✨ Commande Prête"

3. **Restaurant annule** (type 4)
   ```
   Admin → Orders → [Commande] → Annuler
   ```
   → Client reçoit "❌ Commande Annulée"

### Envoi Manuel (depuis le code)

```php
use App\Services\WhatsAppBusinessService;

$whatsapp = new WhatsAppBusinessService();

// Message simple
$result = $whatsapp->sendTextMessage(
    '2250709123456',
    'Votre commande est prête !',
    ['order_id' => 123]
);

// Template approuvé Meta
$result = $whatsapp->sendTemplateMessage(
    '2250709123456',
    'order_confirmation',
    ['Jean Dupont', 'ORD-001', '12870 FCFA'],
    'fr',
    ['order_id' => 123]
);

// Générer lien WhatsApp (sans API)
$url = $whatsapp->generateWhatsAppUrl(
    '2250709123456',
    'Bonjour, ma commande est #ORD-001'
);
// Retourne: https://wa.me/2250709123456?text=...
```

---

## 📊 MONITORING

### Vérifier les logs applicatifs

```bash
# Voir tous les messages WhatsApp
tail -f storage/logs/laravel.log | grep "WhatsApp"

# Voir uniquement les réussites
tail -f storage/logs/laravel.log | grep "WhatsApp message sent successfully"

# Voir uniquement les échecs
tail -f storage/logs/laravel.log | grep "WhatsApp.*failed"
```

### Vérifier les logs en base de données

```sql
-- Derniers messages envoyés
SELECT * FROM whatsapp_logs 
ORDER BY created_at DESC 
LIMIT 10;

-- Statistiques des 7 derniers jours
SELECT 
    DATE(created_at) as date,
    COUNT(*) as total,
    SUM(success) as reussis,
    COUNT(*) - SUM(success) as echecs
FROM whatsapp_logs
WHERE created_at >= DATE_SUB(NOW(), INTERVAL 7 DAY)
GROUP BY DATE(created_at);

-- Messages échoués
SELECT to, message, status, response
FROM whatsapp_logs
WHERE success = 0
ORDER BY created_at DESC;
```

### Dashboard de statistiques

```php
use App\Services\WhatsAppBusinessService;

$service = new WhatsAppBusinessService();
$stats = $service->getStats(7); // 7 derniers jours

// Retourne:
// {
//   "period": "7 days",
//   "total_sent": 145,
//   "total_success": 142,
//   "total_failed": 3,
//   "success_rate": "97.93%"
// }
```

---

## 🔍 DÉPANNAGE

### Erreur: "WhatsApp API not configured"

**Cause**: Token ou Phone Number ID manquant

**Solution**:
```bash
# Vérifier .env
grep WHATSAPP .env

# Doit contenir:
WHATSAPP_API_TOKEN=EAAxxxxx
WHATSAPP_PHONE_NUMBER_ID=123456789

# Clear cache
php artisan config:clear
```

### Erreur: "Invalid phone number"

**Cause**: Numéro mal formaté

**Solution**: Le numéro doit être au format international sans `+`
```
✅ Correct: 2250709123456
❌ Incorrect: +225 07 09 12 34 56
❌ Incorrect: 0709123456
```

Le service formate automatiquement si numéro commence par 0.

### Erreur: "Recipient phone number not in allowed list"

**Cause**: En mode test, seuls certains numéros peuvent recevoir

**Solution**:
1. Aller dans Meta Business → WhatsApp → API Setup
2. Ajouter le numéro de test dans "To" field
3. Vérifier le numéro via code SMS

### Erreur: "(#131030) Recipient phone number not in allowed list"

**Cause**: Compte WhatsApp Business en mode sandbox

**Solution**:
1. Vérifier le compte business sur Meta
2. Passer en mode production
3. Ou ajouter les numéros dans la liste blanche

### Messages non envoyés (mode démo actif)

**Cause**: `WHATSAPP_DEMO_MODE=true`

**Solution**:
```bash
# Dans .env
WHATSAPP_DEMO_MODE=false
WHATSAPP_ENABLED=true

# Clear cache
php artisan config:clear
```

### Erreur: "API request failed" (status 401)

**Cause**: Token expiré ou invalide

**Solution**:
1. Générer un nouveau token permanent
2. Mettre à jour `.env`
3. Clear cache

---

## 📈 LIMITES ET QUOTAS

### Limites Meta WhatsApp Business API

| Type | Limite | Notes |
|------|--------|-------|
| **Messages/jour** | 1,000 (niveau 1) | Augmente avec l'utilisation |
| **Messages/seconde** | 80 | Rate limiting |
| **Taille message** | 4,096 caractères | Texte seul |
| **Taille média** | 16 MB (image), 64 MB (vidéo) | |

### Niveaux de messagerie

Meta augmente votre limite selon votre usage :

| Niveau | Limite/jour | Comment atteindre |
|--------|-------------|-------------------|
| **1** | 1,000 | Par défaut |
| **2** | 10,000 | Envoyer 1,000 msg en 7 jours |
| **3** | 100,000 | Envoyer 10,000 msg en 7 jours |
| **4** | Illimité | Demande manuelle |

### Configuration du système (config/whatsapp.php)

```php
'limits' => [
    'max_retry_attempts' => 3,      // Réessais en cas d'échec
    'retry_delay' => 60,             // Délai entre réessais (sec)
    'max_messages_per_minute' => 60, // Rate limiting local
]
```

---

## 🎯 CHECKLIST PRODUCTION

Avant de mettre en production :

### Configuration

- [ ] Token permanent généré (pas temporaire 24h)
- [ ] `WHATSAPP_ENABLED=true`
- [ ] `WHATSAPP_DEMO_MODE=false`
- [ ] Webhook URL configuré (HTTPS obligatoire)
- [ ] Numéro WhatsApp Business vérifié

### Tests

- [ ] Test de connexion réussi
- [ ] Message test reçu
- [ ] Acceptation de commande → Message reçu
- [ ] Annulation de commande → Message reçu
- [ ] Logs créés en BDD

### Monitoring

- [ ] Logs Laravel configurés
- [ ] Alertes sur échecs critiques
- [ ] Dashboard de statistiques accessible
- [ ] Rétention des logs définie (90 jours par défaut)

### Sécurité

- [ ] Token stocké de manière sécurisée (.env)
- [ ] `.env` dans `.gitignore`
- [ ] Webhook verify token aléatoire et complexe
- [ ] HTTPS actif sur tout le domaine

---

## 📚 RESSOURCES

### Documentation Officielle

- **Meta WhatsApp Business API**: https://developers.facebook.com/docs/whatsapp/cloud-api
- **Meta Business Manager**: https://business.facebook.com/
- **Graph API Explorer**: https://developers.facebook.com/tools/explorer/

### Support

- **Documentation E-menu**: `WHATSAPP_TEMPLATES_GUIDE.md`
- **Exemples d'intégration**: `app/Examples/WhatsAppIntegrationExample.php`
- **Tests**: `test-whatsapp-templates.sh`

### Fichiers du Système

```
app/
  ├── Services/
  │   ├── WhatsAppBusinessService.php       ← Service d'envoi
  │   └── WhatsAppTemplateService.php        ← Génération messages
  ├── Models/
  │   └── WhatsAppLog.php                    ← Logging BDD
  └── Http/Controllers/admin/
      └── OrderController.php                ← Intégration envoi auto

config/
  └── whatsapp.php                           ← Configuration

database/migrations/
  └── 2025_10_23_015418_create_whatsapp_logs_table.php
```

---

## 🎉 RÉSUMÉ

**Ce qui fonctionne maintenant** :

✅ Envoi automatique de messages lors des changements de statut  
✅ Logging complet en base de données  
✅ Gestion d'erreurs et retry automatique  
✅ Mode démo pour tester sans envoyer  
✅ Formatage automatique des numéros  
✅ Statistiques d'envoi  
✅ Test de connexion API  

**Prochaines étapes** :

1. Configurer votre compte Meta Business
2. Obtenir vos credentials API
3. Remplir les variables `.env`
4. Migrer la base de données
5. Tester en mode démo
6. Activer l'envoi réel

**Questions ?** Consultez la section [Dépannage](#dépannage) ou vérifiez les logs.

---

**Version**: 1.0  
**Date**: 23 octobre 2025  
**Statut**: ✅ Production Ready  
**Auteur**: E-menu Development Team

🚀 **L'intégration WhatsApp Business API est prête pour automatiser vos notifications !**
