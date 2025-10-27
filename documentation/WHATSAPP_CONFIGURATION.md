# 📱 GUIDE DE CONFIGURATION WHATSAPP BUSINESS API
## E-menu WhatsApp SaaS - Notifications Automatiques

---

## 🎯 Vue d'ensemble

Ce guide vous explique comment configurer WhatsApp Business API pour envoyer des notifications automatiques aux clients (confirmations de commande, statut, etc.).

---

## 📋 Prérequis

### 1. Compte WhatsApp Business
- ✅ Compte WhatsApp Business vérifié
- ✅ Numéro de téléphone dédié (non utilisé sur WhatsApp personnel)
- ✅ Accès à Facebook Business Manager

### 2. Meta (Facebook) Developer Account
- ✅ Compte développeur Meta
- ✅ Application créée sur Meta for Developers
- ✅ WhatsApp Business API activée

---

## 🚀 Étapes de Configuration

### Étape 1: Créer une Application Meta

1. **Accéder au portail développeur**
   - URL: https://developers.facebook.com/
   - Se connecter avec votre compte Facebook

2. **Créer une nouvelle application**
   ```
   Mes Apps > Créer une App
   Type: Business
   Nom: E-menu WhatsApp Notifications
   ```

3. **Ajouter WhatsApp à l'application**
   ```
   Tableau de bord > Ajouter des produits
   Sélectionner: WhatsApp > Configurer
   ```

### Étape 2: Configuration WhatsApp Business

1. **Obtenir les identifiants**
   - **Phone Number ID**: ID du numéro WhatsApp
   - **WhatsApp Business Account ID**: ID du compte
   - **App ID**: ID de l'application Meta
   - **App Secret**: Secret de l'application

2. **Générer un Access Token**
   ```
   WhatsApp > Démarrage rapide
   Copier le "Temporary access token"
   
   ⚠️ Pour la production, créez un System User Token permanent
   ```

### Étape 3: Configuration dans E-menu

1. **Ouvrir le fichier `.env`**
   ```bash
   nano /path/to/emenu/.env
   ```

2. **Ajouter les variables WhatsApp**
   ```env
   # WhatsApp Business API Configuration
   WHATSAPP_ENABLED=true
   WHATSAPP_API_URL=https://graph.facebook.com/v18.0
   WHATSAPP_PHONE_NUMBER_ID=your_phone_number_id
   WHATSAPP_BUSINESS_ACCOUNT_ID=your_business_account_id
   WHATSAPP_ACCESS_TOKEN=your_permanent_access_token
   WHATSAPP_VERIFY_TOKEN=your_custom_verify_token
   
   # Numéro d'envoi (format international sans +)
   WHATSAPP_FROM_NUMBER=22500000000
   
   # Meta App Configuration
   META_APP_ID=your_app_id
   META_APP_SECRET=your_app_secret
   ```

3. **Nettoyer le cache**
   ```bash
   php artisan config:clear
   php artisan cache:clear
   ```

### Étape 4: Configuration des Webhooks

1. **URL de webhook E-menu**
   ```
   https://votre-domaine.com/api/webhooks/whatsapp
   ```

2. **Dans Meta Developer Console**
   ```
   WhatsApp > Configuration > Webhooks
   URL de rappel: https://votre-domaine.com/api/webhooks/whatsapp
   Token de vérification: [Votre WHATSAPP_VERIFY_TOKEN]
   
   Événements à abonner:
   ☑️ messages
   ☑️ message_status
   ☑️ messaging_postbacks
   ```

3. **Tester le webhook**
   ```bash
   # Vérifier que le webhook est accessible
   curl -X GET "https://votre-domaine.com/api/webhooks/whatsapp?hub.mode=subscribe&hub.verify_token=your_verify_token&hub.challenge=test"
   ```

---

## 🧪 Tests

### Test 1: Envoi d'un message simple

```bash
# Via artisan tinker
php artisan tinker

# Dans tinker:
$whatsapp = new \App\Services\WhatsAppService();
$whatsapp->sendMessage(
    '22500000000',  // Numéro destinataire
    'Test depuis E-menu!'
);
```

### Test 2: Notification de commande

```bash
# Créer une commande de test et vérifier l'envoi
php artisan test:whatsapp-notification
```

### Test 3: Template de message

```php
// Utiliser un template approuvé
$whatsapp->sendTemplateMessage(
    '22500000000',
    'order_confirmation',  // Nom du template
    'fr',                   // Langue
    [
        'customer_name' => 'John Doe',
        'order_number' => '#12345',
        'total_amount' => '15,000 XOF'
    ]
);
```

---

## 📝 Templates de Messages WhatsApp

### Créer des Templates Approuvés

1. **Accéder aux templates**
   ```
   Meta Business Suite > WhatsApp Manager
   > Message Templates > Créer un Template
   ```

2. **Template: Confirmation de Commande**
   ```
   Nom: order_confirmation
   Catégorie: TRANSACTIONAL
   Langue: Français
   
   Contenu:
   Bonjour {{1}},
   
   Votre commande {{2}} a été confirmée! 
   Montant total: {{3}}
   
   Merci d'avoir choisi E-menu! 🍽️
   ```

3. **Template: Commande Prête**
   ```
   Nom: order_ready
   Catégorie: TRANSACTIONAL
   Langue: Français
   
   Contenu:
   Bonjour {{1}},
   
   Votre commande {{2}} est prête! 🎉
   Vous pouvez venir la récupérer.
   
   À bientôt! 🍽️
   ```

---

## 🔧 Configuration Avancée

### Limitations et Rate Limits

```env
# Quotas WhatsApp (dépendent de la qualité du numéro)
WHATSAPP_DAILY_LIMIT=1000          # Messages par jour
WHATSAPP_RATE_LIMIT_PER_SECOND=80  # Messages par seconde
```

### Files d'attente

```env
# Utiliser une queue pour les envois en masse
QUEUE_CONNECTION=redis
WHATSAPP_USE_QUEUE=true
```

### Retry Logic

```env
# Configuration des réessais
WHATSAPP_MAX_RETRIES=3
WHATSAPP_RETRY_DELAY=5  # secondes
```

---

## 🔐 Sécurité

### 1. Validation des Webhooks
```php
// Le système vérifie automatiquement la signature Meta
// Configuration dans config/services.php
'whatsapp' => [
    'verify_token' => env('WHATSAPP_VERIFY_TOKEN'),
    'app_secret' => env('META_APP_SECRET'),
],
```

### 2. Tokens sécurisés
```bash
# Ne JAMAIS commiter les tokens
# Utiliser .env et .env.example
echo "WHATSAPP_ACCESS_TOKEN=*****" >> .env
```

### 3. HTTPS Obligatoire
```nginx
# Nginx - Forcer HTTPS
server {
    listen 80;
    return 301 https://$server_name$request_uri;
}
```

---

## 📊 Monitoring

### Logs WhatsApp
```bash
# Vérifier les logs d'envoi
tail -f storage/logs/whatsapp.log

# Logs Laravel
tail -f storage/logs/laravel.log | grep WhatsApp
```

### Métriques
```bash
# Statistiques d'envoi
php artisan whatsapp:stats

# Résultat attendu:
# Messages envoyés (24h): 150
# Taux de succès: 98.5%
# Échecs: 2
```

---

## 🚨 Dépannage

### Problème 1: Messages non envoyés

**Symptôme**: `Error sending WhatsApp message`

**Solutions**:
```bash
# 1. Vérifier l'access token
php artisan tinker
>>> config('services.whatsapp.access_token')

# 2. Tester la connexion API
curl -X GET "https://graph.facebook.com/v18.0/me?access_token=YOUR_TOKEN"

# 3. Vérifier les logs
tail -50 storage/logs/laravel.log
```

### Problème 2: Webhooks non reçus

**Solutions**:
```bash
# 1. Vérifier que l'URL est accessible
curl https://votre-domaine.com/api/webhooks/whatsapp

# 2. Vérifier le verify_token
# Dans Meta Console > Webhooks > Modifier

# 3. Tester manuellement
curl -X POST https://votre-domaine.com/api/webhooks/whatsapp \
  -H "Content-Type: application/json" \
  -d '{"test": "data"}'
```

### Problème 3: Rate Limit dépassé

**Solutions**:
```env
# Activer la queue
WHATSAPP_USE_QUEUE=true

# Réduire la cadence
WHATSAPP_RATE_LIMIT_PER_SECOND=20
```

---

## 📞 Support

### Ressources Meta
- **Documentation**: https://developers.facebook.com/docs/whatsapp
- **Support**: https://business.facebook.com/business/help

### Ressources E-menu
- **GitHub**: Ouvrir une issue
- **Email**: support@emenu.com

---

## ✅ Checklist de Configuration

- [ ] Compte WhatsApp Business créé
- [ ] Application Meta configurée
- [ ] WhatsApp Business API activée
- [ ] Phone Number ID obtenu
- [ ] Access Token permanent généré
- [ ] Variables `.env` configurées
- [ ] Webhooks configurés et testés
- [ ] Templates de messages approuvés
- [ ] Test d'envoi réussi
- [ ] Logs monitoring configurés
- [ ] HTTPS activé
- [ ] Rate limits configurés

---

*Dernière mise à jour: 22 octobre 2025*
*Version: 1.0 - E-menu WhatsApp SaaS*
