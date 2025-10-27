# 📱 Guide d'Intégration WhatsApp Business API - E-menu SaaS

## 📋 Table des Matières
1. [Vue d'ensemble](#vue-densemble)
2. [Prérequis](#prérequis)
3. [Configuration Meta Business](#configuration-meta-business)
4. [Installation et Configuration](#installation-et-configuration)
5. [Utilisation](#utilisation)
6. [Templates de Messages](#templates-de-messages)
7. [Webhooks](#webhooks)
8. [Tests](#tests)
9. [Dépannage](#dépannage)
10. [Production](#production)

---

## 🎯 Vue d'ensemble

L'addon **WhatsApp Message** permet d'envoyer automatiquement des notifications WhatsApp aux restaurants et clients pour:
- ✅ Nouvelles commandes (notification restaurant)
- ✅ Confirmation de paiement (notification client)
- ✅ Mises à jour de livraison (notification client)
- ✅ Messages personnalisés via l'interface admin

### Architecture

```
┌─────────────────┐
│   Commande      │
│   Créée         │
└────────┬────────┘
         │
         ▼
┌─────────────────┐      ┌──────────────────┐      ┌─────────────────┐
│ OrderCreated    │─────▶│ WhatsAppService  │─────▶│ WhatsApp API    │
│ Event           │      │                  │      │ (Meta)          │
└─────────────────┘      └──────────────────┘      └─────────────────┘
         │                        │
         │                        ▼
         │               ┌──────────────────┐
         │               │ whatsapp_        │
         │               │ messages_log     │
         │               └──────────────────┘
         ▼
┌─────────────────┐
│ SendWhatsApp    │
│ Notification    │
│ Listener        │
└─────────────────┘
```

---

## ✅ Prérequis

### 1. Compte Meta Business
- Compte Facebook Business Manager actif
- Application Meta créée (Business App)
- Numéro de téléphone WhatsApp Business vérifié

### 2. Serveur
- PHP 8.1+
- Laravel 10+
- HTTPS actif (obligatoire pour les webhooks Meta)
- URL publique accessible (pas de localhost)

### 3. Extensions PHP
```bash
php -m | grep -E "curl|json|mbstring|openssl"
```

---

## 🚀 Configuration Meta Business

### Étape 1: Créer une Application Meta

1. Allez sur https://developers.facebook.com/apps
2. Cliquez sur **Créer une app**
3. Choisissez le type: **Business**
4. Remplissez les informations:
   - Nom de l'app: `E-menu WhatsApp`
   - Email de contact
   - Compte Business associé

### Étape 2: Ajouter WhatsApp Business API

1. Dans le tableau de bord de votre app
2. Cliquez sur **Ajouter un produit**
3. Trouvez **WhatsApp** et cliquez **Configurer**
4. Suivez l'assistant de configuration

### Étape 3: Obtenir les Credentials

#### A. Token d'accès (WHATSAPP_API_TOKEN)

1. Allez dans **WhatsApp > Démarrage**
2. Section **Token d'accès temporaire**
3. Copiez le token (valide 24h)
4. Pour un token permanent:
   - Allez dans **Paramètres > Basique**
   - Générez un **Token d'accès système** (ne expire jamais)

#### B. Phone Number ID (WHATSAPP_PHONE_NUMBER_ID)

1. Dans **WhatsApp > Démarrage**
2. Section **Numéro de téléphone de test** ou votre numéro vérifié
3. Copiez l'ID du numéro (format numérique)

Exemple: `123456789012345`

#### C. Business Account ID (WHATSAPP_BUSINESS_ACCOUNT_ID)

1. Dans **WhatsApp > Démarrage**
2. En haut à gauche, votre compte WhatsApp Business
3. Copiez l'ID

Exemple: `123456789012345`

#### D. App Secret (WHATSAPP_APP_SECRET)

1. Allez dans **Paramètres > Basique**
2. Trouvez **Clé secrète de l'app**
3. Cliquez sur **Afficher** et copiez

### Étape 4: Configurer le Webhook

1. Dans **WhatsApp > Configuration**
2. Section **Webhook**
3. Cliquez **Modifier**
4. Remplissez:
   - **URL de rappel**: `https://votre-domaine.com/api/whatsapp/webhook`
   - **Token de vérification**: `emenu_whatsapp_2024_secure_token` (celui dans votre .env)
5. Cliquez **Vérifier et enregistrer**
6. Abonnez-vous aux champs:
   - ✅ `messages` - Messages entrants
   - ✅ `message_status` - Statuts des messages

---

## ⚙️ Installation et Configuration

### 1. Variables d'Environnement

Éditez votre fichier `.env`:

```env
# WhatsApp Business API Configuration
WHATSAPP_ENABLED=true
WHATSAPP_DEMO_MODE=false
WHATSAPP_API_URL=https://graph.facebook.com/v18.0
WHATSAPP_API_TOKEN=votre_token_acces_meta
WHATSAPP_PHONE_NUMBER_ID=123456789012345
WHATSAPP_BUSINESS_ACCOUNT_ID=123456789012345
WHATSAPP_APP_SECRET=votre_app_secret

# Webhook Configuration
WHATSAPP_WEBHOOK_VERIFY_TOKEN=emenu_whatsapp_2024_secure_token
WHATSAPP_WEBHOOK_URL=https://votre-domaine.com/api/whatsapp/webhook

# Phone Configuration (Côte d'Ivoire)
WHATSAPP_DEFAULT_COUNTRY_CODE=225
WHATSAPP_TEST_PHONE=22507123456

# Notifications
WHATSAPP_NOTIFY_RESTAURANT_ON_ORDER=true
WHATSAPP_NOTIFY_CUSTOMER_ON_PAYMENT=true
WHATSAPP_NOTIFY_CUSTOMER_ON_DELIVERY_UPDATE=true
```

### 2. Migration de la Base de Données

La table `whatsapp_messages_log` est déjà créée. Vérifiez:

```bash
php artisan migrate:status
```

Si besoin:

```bash
php artisan migrate
```

### 3. Cache Clear

```bash
php artisan config:clear
php artisan cache:clear
php artisan route:clear
```

### 4. Queue Worker (Recommandé en Production)

Pour des notifications asynchrones:

```bash
# Modifier .env
QUEUE_CONNECTION=database

# Créer la table jobs
php artisan queue:table
php artisan migrate

# Lancer le worker
php artisan queue:work --tries=3
```

---

## 📖 Utilisation

### 1. Envoi Automatique

Les notifications sont envoyées automatiquement via les événements:

#### Nouvelle Commande

```php
use App\Events\OrderCreatedEvent;

// Dans votre OrderController
event(new OrderCreatedEvent($order, $restaurant));
```

#### Paiement Confirmé

```php
use App\Events\PaymentConfirmedEvent;

// Après confirmation de paiement
event(new PaymentConfirmedEvent($order, $customer));
```

#### Mise à Jour Livraison

```php
use App\Events\DeliveryStatusUpdatedEvent;

// Lors du changement de statut
event(new DeliveryStatusUpdatedEvent($order, $customer, 'on_the_way'));
```

Statuts disponibles:
- `accepted` - Commande acceptée
- `prepared` - Commande prête
- `on_the_way` - En livraison
- `delivered` - Livrée
- `cancelled` - Annulée

### 2. Envoi Manuel

```php
use App\Services\WhatsAppService;

$whatsapp = app(WhatsAppService::class);

// Message simple
$result = $whatsapp->sendMessage(
    '22507123456', 
    'Bonjour! Votre commande #123 est prête.'
);

// Notification de commande
$result = $whatsapp->sendOrderNotification($order, $restaurant);

// Confirmation de paiement
$result = $whatsapp->sendPaymentConfirmation($order, $customer);

// Mise à jour de livraison
$result = $whatsapp->sendDeliveryUpdate($order, $customer, 'delivered');
```

### 3. API Admin

#### Tester la Connexion

```bash
POST /api/whatsapp/test-connection
Authorization: Bearer {token}

{
  "phone": "22507123456"
}
```

#### Envoyer un Message Test

```bash
POST /api/whatsapp/test-message
Authorization: Bearer {token}

{
  "phone": "22507123456",
  "message": "Test de message WhatsApp"
}
```

#### Statistiques

```bash
GET /api/whatsapp/statistics?days=30&restaurant_id=1
Authorization: Bearer {token}
```

#### Historique des Messages

```bash
GET /api/whatsapp/messages/history?status=failed&message_type=order_notification
Authorization: Bearer {token}
```

#### Renvoyer un Message Échoué

```bash
POST /api/whatsapp/messages/{messageId}/retry
Authorization: Bearer {token}
```

---

## 📝 Templates de Messages

### Message de Nouvelle Commande (Restaurant)

```
🔔 **NOUVELLE COMMANDE #123**

👤 Client: Jean Kouadio
📱 Tél: 0707123456
📍 Adresse: Cocody, Angré 7ème Tranche

📦 **Articles:**
• Poulet Braisé x2 - 5 000 XOF
• Attiéké x2 - 1 000 XOF
• Jus d'Orange x1 - 500 XOF

💰 **Total: 6 500 XOF**
💳 Paiement: Orange Money
⏰ Commande passée le: 23/10/2025 à 14:30

📝 Note: Sans piment
```

### Confirmation de Paiement (Client)

```
✅ **PAIEMENT CONFIRMÉ**

Bonjour Jean,

Votre paiement de **6 500 XOF** pour la commande #123 a été confirmé avec succès.

🍽️ Votre commande est en préparation !
⏰ Livraison estimée: 30-45 minutes

Merci pour votre confiance ! 🙏
```

### Mise à Jour de Livraison (Client)

```
**MISE À JOUR DE LIVRAISON**

Bonjour Jean,

🚗 Votre commande #123 est en route vers vous !

🚗 Livreur: Konan Yao
📱 Contact: 0708654321
```

---

## 🔗 Webhooks

### Vérification du Webhook (GET)

Meta envoie une requête GET pour vérifier votre webhook:

```
GET /api/whatsapp/webhook?hub.mode=subscribe&hub.verify_token=emenu_whatsapp_2024_secure_token&hub.challenge=123456
```

Votre serveur doit retourner le `hub.challenge` avec un code 200.

### Réception des Notifications (POST)

Meta envoie des POST pour:
- Changements de statut des messages
- Messages entrants
- Erreurs

Exemple de payload:

```json
{
  "entry": [{
    "changes": [{
      "value": {
        "statuses": [{
          "id": "wamid.xxx",
          "status": "delivered",
          "timestamp": "1234567890"
        }]
      }
    }]
  }]
}
```

Statuts possibles:
- `sent` - Envoyé à WhatsApp
- `delivered` - Livré au téléphone
- `read` - Lu par le destinataire
- `failed` - Échec d'envoi

---

## 🧪 Tests

### 1. Test de Configuration

```bash
php artisan tinker

# Vérifier la config
config('whatsapp.enabled')
config('whatsapp.api_token')
config('whatsapp.phone_number_id')
```

### 2. Test d'Envoi

```bash
php artisan tinker

$whatsapp = app(\App\Services\WhatsAppService::class);
$result = $whatsapp->testConnection('22507123456');
dd($result);
```

Résultat attendu:

```php
[
  "success" => true,
  "message_id" => "wamid.HBgNMjI1MDcxMjM0NTY=",
  "phone" => "22507123456"
]
```

### 3. Test d'Événement

```bash
php artisan tinker

$order = \App\Models\Order::first();
$restaurant = $order->restorant;
event(new \App\Events\OrderCreatedEvent($order, $restaurant));
```

### 4. Vérifier les Logs

```bash
# Logs WhatsApp
tail -f storage/logs/laravel.log | grep WhatsApp

# Table des messages
php artisan tinker
DB::table('whatsapp_messages_log')->latest()->take(5)->get();
```

---

## 🔧 Dépannage

### Erreur: "Invalid access token"

**Cause:** Token expiré ou invalide

**Solution:**
1. Régénérez un token permanent dans Meta Business
2. Mettez à jour `WHATSAPP_API_TOKEN` dans `.env`
3. `php artisan config:clear`

### Erreur: "Phone number not registered"

**Cause:** Numéro non enregistré dans WhatsApp Business

**Solution:**
1. Utilisez un numéro de test autorisé dans Meta
2. Ou enregistrez votre numéro dans Meta Business Manager

### Erreur: "Webhook verification failed"

**Cause:** Token de vérification incorrect ou URL non accessible

**Solution:**
1. Vérifiez `WHATSAPP_WEBHOOK_VERIFY_TOKEN` dans `.env`
2. Testez votre URL: `curl https://votre-domaine.com/api/whatsapp/webhook?hub.mode=subscribe&hub.verify_token=votre_token&hub.challenge=test`
3. Assurez-vous que le serveur est accessible (pas de firewall)

### Messages non envoyés

**Diagnostic:**

```bash
# Vérifier les messages échoués
php artisan tinker
DB::table('whatsapp_messages_log')->where('status', 'failed')->get();

# Vérifier les queues
php artisan queue:failed
```

**Solutions:**
1. Vérifiez le numéro de téléphone (format international)
2. Vérifiez les credentials Meta
3. Vérifiez les quotas WhatsApp (limite 1000 messages/jour en test)

### Numéros mal formatés

**Problème:** Numéros locaux ivoiriens (ex: 07 12 34 56 78)

**Solution:** Le service formatte automatiquement:
- `07 12 34 56 78` → `22507123456`
- `0712345678` → `22507123456`
- `+225 07 12 34 56 78` → `22507123456`

Si problème persiste:

```php
$whatsapp = app(\App\Services\WhatsAppService::class);
$formatted = $whatsapp->validatePhoneNumber('0712345678');
// true ou false
```

---

## 🚀 Production

### 1. Sécurité

```env
# .env Production
WHATSAPP_ENABLED=true
WHATSAPP_DEMO_MODE=false

# Token permanent (ne partez jamais avec un token temporaire)
WHATSAPP_API_TOKEN=EAAx...permanent_token...xyz

# Token de vérification complexe
WHATSAPP_WEBHOOK_VERIFY_TOKEN=votre_token_tres_securise_aleatoire_123xyz
```

### 2. HTTPS Obligatoire

Meta n'accepte que les webhooks HTTPS. Configurez SSL:

```bash
# Exemple avec Let's Encrypt
sudo certbot --nginx -d votre-domaine.com
```

### 3. Queue Worker Supervisord

Créez `/etc/supervisor/conf.d/whatsapp-worker.conf`:

```ini
[program:whatsapp-worker]
process_name=%(program_name)s_%(process_num)02d
command=php /path/to/artisan queue:work --queue=default --tries=3 --timeout=90
autostart=true
autorestart=true
stopasgroup=true
killasgroup=true
user=www-data
numprocs=2
redirect_stderr=true
stdout_logfile=/var/log/whatsapp-worker.log
```

Puis:

```bash
sudo supervisorctl reread
sudo supervisorctl update
sudo supervisorctl start whatsapp-worker:*
```

### 4. Monitoring

#### A. Logs Centralisés

Ajoutez dans `config/logging.php`:

```php
'whatsapp' => [
    'driver' => 'daily',
    'path' => storage_path('logs/whatsapp.log'),
    'level' => 'info',
    'days' => 30,
],
```

Utilisez:

```php
\Log::channel('whatsapp')->info('Message envoyé', $data);
```

#### B. Alertes

Créez un job pour alerter en cas de trop d'échecs:

```php
$failedCount = DB::table('whatsapp_messages_log')
    ->where('created_at', '>=', now()->subHour())
    ->where('status', 'failed')
    ->count();

if ($failedCount > 10) {
    // Envoyer alerte admin
    \Log::critical("WhatsApp: {$failedCount} échecs dans la dernière heure");
}
```

### 5. Limites WhatsApp

- **Mode Test:** 1000 messages/jour
- **Mode Production:** Limites progressives selon votre usage
- **Rate Limit:** 60 messages/minute par défaut

Gérez les limites:

```php
// config/whatsapp.php
'limits' => [
    'max_messages_per_minute' => 60,
],
```

### 6. Nettoyage des Logs

Créez un job planifié pour nettoyer les anciens logs:

```bash
php artisan make:command CleanWhatsAppLogs
```

```php
// app/Console/Commands/CleanWhatsAppLogs.php
DB::table('whatsapp_messages_log')
    ->where('created_at', '<', now()->subDays(90))
    ->delete();
```

Ajoutez dans `app/Console/Kernel.php`:

```php
protected function schedule(Schedule $schedule)
{
    $schedule->command('whatsapp:clean-logs')->daily();
}
```

---

## 📊 Tableau de Bord (Interface Admin - À Venir)

L'interface admin permettra:
- ✅ Configuration graphique des credentials
- ✅ Test d'envoi de messages
- ✅ Historique des messages avec filtres
- ✅ Statistiques (envoyés, livrés, lus, échoués)
- ✅ Templates personnalisés
- ✅ Retry des messages échoués

---

## 🎓 Ressources

- [Documentation WhatsApp Business API](https://developers.facebook.com/docs/whatsapp/business-platform)
- [Meta Business Manager](https://business.facebook.com/)
- [WhatsApp API Explorer](https://developers.facebook.com/tools/explorer/)
- [Codes d'erreur WhatsApp](https://developers.facebook.com/docs/whatsapp/cloud-api/support/error-codes)

---

## 📞 Support

En cas de problème:
1. Vérifiez ce guide
2. Consultez les logs: `storage/logs/laravel.log`
3. Vérifiez la table: `whatsapp_messages_log`
4. Testez avec l'API Meta directement: https://developers.facebook.com/tools/explorer/

---

**Version:** 1.0.0  
**Dernière mise à jour:** 23 octobre 2025  
**Statut:** ✅ Production Ready
