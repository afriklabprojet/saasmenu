# 💳 GUIDE DE CONFIGURATION CINETPAY
## E-menu WhatsApp SaaS - Paiements Mobiles Africains

---

## 🎯 Vue d'ensemble

CinetPay permet d'accepter les paiements via:
- 🟠 **Orange Money** (Côte d'Ivoire, Sénégal, Mali, etc.)
- 🟡 **MTN Mobile Money** (Côte d'Ivoire, Bénin, etc.)
- 🔵 **Moov Money** (Côte d'Ivoire, Togo, Bénin)
- 💳 **Cartes bancaires** Visa/Mastercard
- 🏦 **Transferts bancaires**

---

## 📋 Prérequis

### 1. Compte CinetPay Merchant
- ✅ Inscription sur https://cinetpay.com
- ✅ Vérification KYC complétée
- ✅ Compte merchant activé

### 2. Documents requis
- ✅ Pièce d'identité
- ✅ Justificatif de domicile
- ✅ Registre de commerce (pour entreprises)

---

## 🚀 Configuration Rapide

### Étape 1: Créer un Compte CinetPay

1. **S'inscrire**
   ```
   URL: https://merchant.cinetpay.com/register
   
   Informations requises:
   - Nom complet
   - Email professionnel
   - Numéro de téléphone
   - Pays
   - Type d'activité: E-commerce/Restaurant
   ```

2. **Vérification KYC**
   ```
   Tableau de bord > Mon Compte > KYC
   
   Uploader:
   - Copie pièce d'identité
   - Justificatif domicile
   - RCS (si entreprise)
   
   ⏱️ Délai de vérification: 24-48h
   ```

3. **Obtenir les clés API**
   ```
   Tableau de bord > Paramètres > API
   
   Copier:
   ✅ API Key
   ✅ Site ID
   ✅ Secret Key
   ```

### Étape 2: Configuration dans E-menu

1. **Éditer le fichier `.env`**
   ```bash
   nano /path/to/emenu/.env
   ```

2. **Ajouter les variables CinetPay**
   ```env
   # CinetPay Configuration
   CINETPAY_ENABLED=true
   CINETPAY_API_KEY=your_api_key_here
   CINETPAY_SITE_ID=your_site_id_here
   CINETPAY_SECRET_KEY=your_secret_key_here
   
   # Mode: TEST ou PRODUCTION
   CINETPAY_MODE=TEST
   
   # URL de retour après paiement
   CINETPAY_RETURN_URL=https://votre-domaine.com/payment/return
   CINETPAY_NOTIFY_URL=https://votre-domaine.com/payment/notify
   CINETPAY_CANCEL_URL=https://votre-domaine.com/payment/cancel
   
   # Devise par défaut
   CINETPAY_CURRENCY=XOF
   
   # Canaux de paiement actifs (séparés par virgule)
   # Options: ORANGE_MONEY_CI, MTN_CI, MOOV_CI, CARD, BANK_TRANSFER
   CINETPAY_CHANNELS=ORANGE_MONEY_CI,MTN_CI,MOOV_CI,CARD
   
   # Montant minimum de transaction (en XOF)
   CINETPAY_MIN_AMOUNT=100
   
   # Commission merchant (%)
   CINETPAY_COMMISSION=2.5
   ```

3. **Nettoyer le cache**
   ```bash
   php artisan config:clear
   php artisan cache:clear
   php artisan config:cache
   ```

### Étape 3: Configuration des Webhooks

1. **Dans le tableau de bord CinetPay**
   ```
   Paramètres > Notifications > URL de notification
   
   URL IPN: https://votre-domaine.com/api/payment/cinetpay/webhook
   
   ⚠️ L'URL doit être accessible publiquement (HTTPS obligatoire)
   ```

2. **Événements à recevoir**
   ```
   ☑️ Paiement réussi (ACCEPTED)
   ☑️ Paiement échoué (REFUSED)
   ☑️ Paiement en attente (PENDING)
   ☑️ Paiement annulé (CANCELLED)
   ```

---

## 🧪 Tests en Mode Sandbox

### Test des paiements

CinetPay fournit des numéros de test pour chaque opérateur:

#### 🟠 Orange Money - TEST
```
Numéro: +225 07 00 00 00 01
Code OTP: 123456
Montant test: 100 - 50,000 XOF
```

#### 🟡 MTN Mobile Money - TEST
```
Numéro: +225 05 00 00 00 01
Code PIN: 0000
Montant test: 100 - 50,000 XOF
```

#### 🔵 Moov Money - TEST
```
Numéro: +225 01 00 00 00 01
Code secret: 1234
Montant test: 100 - 50,000 XOF
```

#### 💳 Carte bancaire - TEST
```
Numéro: 4111 1111 1111 1111
CVV: 123
Date expiration: 12/25
Nom: TEST CARD
```

### Commandes de test

```bash
# Test 1: Initialiser un paiement
php artisan tinker

# Dans tinker:
$cinetpay = new \App\Services\CinetPayService();
$payment = $cinetpay->initPayment([
    'amount' => 5000,
    'currency' => 'XOF',
    'transaction_id' => 'TEST-' . time(),
    'customer_name' => 'John Doe',
    'customer_email' => 'test@example.com',
    'customer_phone' => '+22507000001',
    'description' => 'Test paiement E-menu'
]);

echo $payment['payment_url'];  // URL à ouvrir dans le navigateur
```

```bash
# Test 2: Vérifier le statut d'un paiement
$status = $cinetpay->checkPaymentStatus('TRANSACTION_ID');
print_r($status);
```

```bash
# Test 3: Simulation webhook
curl -X POST https://votre-domaine.com/api/payment/cinetpay/webhook \
  -H "Content-Type: application/json" \
  -d '{
    "cpm_trans_id": "123456",
    "cpm_trans_status": "ACCEPTED",
    "cpm_amount": "5000",
    "cpm_currency": "XOF",
    "signature": "test_signature"
  }'
```

---

## 🔧 Configuration Avancée

### Personnalisation de l'interface de paiement

```php
// Dans config/cinetpay.php
return [
    'api_key' => env('CINETPAY_API_KEY'),
    'site_id' => env('CINETPAY_SITE_ID'),
    'secret_key' => env('CINETPAY_SECRET_KEY'),
    'mode' => env('CINETPAY_MODE', 'TEST'),
    
    // Personnalisation
    'branding' => [
        'logo_url' => 'https://votre-domaine.com/logo.png',
        'color' => '#25D366',  // Couleur principale
        'background_color' => '#ffffff',
    ],
    
    // Timeout (en secondes)
    'timeout' => 300,  // 5 minutes
    
    // Langue de l'interface
    'lang' => 'fr',  // fr, en
];
```

### Gestion des frais

```env
# Qui paie les frais?
# Options: merchant, customer, shared
CINETPAY_FEE_MODE=customer

# Frais par canal (en %)
CINETPAY_ORANGE_MONEY_FEE=1.5
CINETPAY_MTN_FEE=1.5
CINETPAY_MOOV_FEE=1.5
CINETPAY_CARD_FEE=2.5
```

### Limites de transaction

```env
# Montants min/max par canal (XOF)
CINETPAY_ORANGE_MIN=100
CINETPAY_ORANGE_MAX=500000

CINETPAY_MTN_MIN=100
CINETPAY_MTN_MAX=500000

CINETPAY_MOOV_MIN=100
CINETPAY_MOOV_MAX=500000

CINETPAY_CARD_MIN=500
CINETPAY_CARD_MAX=1000000
```

---

## 🔐 Sécurité

### Validation des signatures

```php
// Le système vérifie automatiquement les signatures
// Configuration dans app/Services/CinetPayService.php

public function verifySignature($data, $signature)
{
    $secretKey = config('cinetpay.secret_key');
    $expectedSignature = hash_hmac('sha256', json_encode($data), $secretKey);
    
    return hash_equals($expectedSignature, $signature);
}
```

### Protection CSRF

```php
// Routes protégées dans routes/web.php
Route::post('/payment/return', [PaymentController::class, 'return'])
    ->middleware('verify.cinetpay.signature');
```

### Logs de sécurité

```env
# Activer les logs détaillés
CINETPAY_LOG_LEVEL=debug
CINETPAY_LOG_WEBHOOKS=true
```

---

## 📊 Monitoring

### Tableau de bord CinetPay
```
merchant.cinetpay.com/dashboard

Métriques disponibles:
- 💰 Transactions du jour/mois
- 📊 Taux de réussite
- 🔄 Paiements en attente
- ❌ Paiements échoués
- 💸 Revenus nets
```

### Logs E-menu

```bash
# Voir les logs de paiement
tail -f storage/logs/cinetpay.log

# Filtrer par statut
grep "ACCEPTED" storage/logs/cinetpay.log
grep "REFUSED" storage/logs/cinetpay.log

# Statistiques
php artisan cinetpay:stats

# Résultat:
# Transactions (24h): 45
# Montant total: 1,250,000 XOF
# Taux de succès: 94.5%
# Canal le plus utilisé: Orange Money (60%)
```

---

## 🚨 Dépannage

### Problème 1: Paiement bloqué en "PENDING"

**Causes possibles**:
- Client n'a pas validé le paiement
- Solde insuffisant
- Timeout dépassé

**Solutions**:
```bash
# Vérifier le statut
php artisan cinetpay:check-status TRANSACTION_ID

# Annuler si nécessaire
php artisan cinetpay:cancel TRANSACTION_ID
```

### Problème 2: Webhook non reçu

**Vérifications**:
```bash
# 1. URL accessible?
curl https://votre-domaine.com/api/payment/cinetpay/webhook

# 2. HTTPS actif?
curl -I https://votre-domaine.com

# 3. Logs serveur
tail -50 /var/log/nginx/error.log

# 4. Tester manuellement
php artisan cinetpay:test-webhook
```

### Problème 3: Signature invalide

**Solutions**:
```bash
# Vérifier la clé secrète
php artisan tinker
>>> config('cinetpay.secret_key')

# Nettoyer le cache config
php artisan config:clear
php artisan config:cache

# Tester la signature
php artisan cinetpay:verify-signature
```

### Problème 4: Erreur "Invalid API Key"

**Solutions**:
```env
# Vérifier les clés dans .env
CINETPAY_API_KEY=your_correct_api_key
CINETPAY_SITE_ID=your_correct_site_id
CINETPAY_SECRET_KEY=your_correct_secret_key

# Mode doit correspondre aux clés
CINETPAY_MODE=TEST  # ou PRODUCTION
```

---

## 💰 Tarification CinetPay

### Frais par canal (Côte d'Ivoire)

| Canal | Commission | Montant Min | Montant Max |
|-------|-----------|-------------|-------------|
| Orange Money | 1.5% + 10 XOF | 100 XOF | 500,000 XOF |
| MTN Money | 1.5% + 10 XOF | 100 XOF | 500,000 XOF |
| Moov Money | 1.5% + 10 XOF | 100 XOF | 500,000 XOF |
| Carte bancaire | 2.5% + 50 XOF | 500 XOF | 1,000,000 XOF |
| Virement bancaire | 1% + 100 XOF | 10,000 XOF | 10,000,000 XOF |

**Exemple de calcul:**
```
Commande: 10,000 XOF via Orange Money
Commission CinetPay: 10,000 × 1.5% + 10 = 160 XOF
Montant reçu: 10,000 - 160 = 9,840 XOF
```

---

## 🔄 Passage en Production

### Checklist

- [ ] Compte merchant vérifié (KYC)
- [ ] Clés API de production obtenues
- [ ] `.env` mis à jour avec clés PRODUCTION
- [ ] `CINETPAY_MODE=PRODUCTION`
- [ ] URLs de retour configurées (HTTPS)
- [ ] Webhooks testés en production
- [ ] Certificat SSL actif
- [ ] Logs monitoring configurés
- [ ] Backup database configuré
- [ ] Support CinetPay contacté

### Migration vers production

```bash
# 1. Sauvegarder la base de données
php artisan backup:run

# 2. Mettre à jour .env
sed -i 's/CINETPAY_MODE=TEST/CINETPAY_MODE=PRODUCTION/' .env
sed -i 's/CINETPAY_API_KEY=.*/CINETPAY_API_KEY=prod_key/' .env
sed -i 's/CINETPAY_SITE_ID=.*/CINETPAY_SITE_ID=prod_site/' .env
sed -i 's/CINETPAY_SECRET_KEY=.*/CINETPAY_SECRET_KEY=prod_secret/' .env

# 3. Nettoyer les caches
php artisan config:clear
php artisan cache:clear
php artisan config:cache

# 4. Test de paiement réel (petit montant)
php artisan cinetpay:test-payment --amount=100

# 5. Monitoring
php artisan queue:work --queue=payments &
tail -f storage/logs/cinetpay.log
```

---

## 📞 Support

### CinetPay
- **Email**: support@cinetpay.com
- **Téléphone**: +225 27 20 66 99 66
- **WhatsApp**: +225 07 00 00 00 00
- **Documentation**: https://docs.cinetpay.com
- **Statut API**: https://status.cinetpay.com

### E-menu
- **Email**: support@emenu.com
- **Documentation**: Voir README.md

---

## ✅ Checklist de Configuration

- [ ] Compte CinetPay créé et vérifié
- [ ] Clés API obtenues (TEST et PRODUCTION)
- [ ] Variables `.env` configurées
- [ ] Webhooks configurés
- [ ] Tests en mode sandbox réussis
- [ ] Orange Money testé
- [ ] MTN Money testé
- [ ] Moov Money testé
- [ ] Carte bancaire testée
- [ ] Signature webhook validée
- [ ] Logs monitoring actifs
- [ ] HTTPS configuré
- [ ] Ready pour production

---

*Dernière mise à jour: 22 octobre 2025*
*Version: 1.0 - E-menu WhatsApp SaaS*
