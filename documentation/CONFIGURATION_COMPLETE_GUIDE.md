# 🔧 GUIDE DE CONFIGURATION COMPLÈTE - RESTRO-SAAS

## 📋 **Guide pour configurer tous les 15 addons**

### 🎯 **Vue d'ensemble**

Ce guide vous aide à configurer correctement le fichier `.env` pour que tous les 15 addons de RestroSaaS fonctionnent parfaitement.

---

## 🏗️ **1. CONFIGURATION DE BASE**

### 📝 **Application principale**
```bash
# Copiez .env.example vers .env
cp .env.example .env

# Configurez les bases
APP_NAME="Votre Restaurant SaaS"
APP_URL=https://votre-domaine.com
APP_ENV=production  # ou 'local' pour développement
APP_DEBUG=false     # ou 'true' pour développement
```

### 🗄️ **Base de données**
```bash
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=restro_saas
DB_USERNAME=votre_utilisateur
DB_PASSWORD=votre_mot_de_passe
```

---

## 👤 **2. ADDON SOCIAL LOGIN (2/15)**

### 🔑 **Google OAuth**
1. Allez sur [Google Cloud Console](https://console.cloud.google.com/)
2. Créez un projet ou sélectionnez-en un
3. Activez l'API Google+ 
4. Créez des credentials OAuth 2.0
```bash
GOOGLE_CLIENT_ID=votre_client_id.apps.googleusercontent.com
GOOGLE_CLIENT_SECRET=votre_client_secret
```

### 📘 **Facebook OAuth**
1. Allez sur [Facebook Developers](https://developers.facebook.com/)
2. Créez une app Facebook
3. Ajoutez le produit "Facebook Login"
```bash
FACEBOOK_CLIENT_ID=votre_app_id
FACEBOOK_CLIENT_SECRET=votre_app_secret
```

### 🍎 **Apple OAuth** (optionnel)
```bash
APPLE_CLIENT_ID=votre_service_id
APPLE_CLIENT_SECRET=votre_private_key
```

---

## 📊 **3. ADDON ANALYTICS (8/15)**

### 📈 **Google Analytics**
1. Créez un compte [Google Analytics](https://analytics.google.com/)
2. Créez une propriété pour votre site
3. Obtenez votre ID de suivi
```bash
GOOGLE_ANALYTICS_ID=G-XXXXXXXXXX
GOOGLE_ANALYTICS_TRACKING_ID=UA-XXXXXXXX-X
```

---

## 💳 **4. SYSTÈMES DE PAIEMENT**

### 💳 **Stripe** (recommandé)
1. Créez un compte [Stripe](https://stripe.com/)
2. Obtenez vos clés API
```bash
STRIPE_KEY=pk_live_XXXXXXXXXX  # ou pk_test_ pour test
STRIPE_SECRET=sk_live_XXXXXXXXXX  # ou sk_test_ pour test
STRIPE_WEBHOOK_SECRET=whsec_XXXXXXXXXX
```

### 🅿️ **PayPal**
1. Créez un compte [PayPal Developer](https://developer.paypal.com/)
2. Créez une app
```bash
PAYPAL_CLIENT_ID=votre_client_id
PAYPAL_CLIENT_SECRET=votre_client_secret
PAYPAL_MODE=live  # ou 'sandbox' pour test
```

### 🇪🇺 **Mollie** (Europe)
```bash
MOLLIE_KEY=live_XXXXXXXXXX  # ou test_ pour test
```

### 🌍 **CinetPay** (Mobile Money Afrique)
1. Créez un compte [CinetPay](https://cinetpay.com/)
2. Obtenez vos credentials
```bash
CINETPAY_API_KEY=votre_api_key
CINETPAY_SITE_ID=votre_site_id
CINETPAY_SECRET_KEY=votre_secret_key
CINETPAY_MODE=live  # ou 'sandbox' pour test
```

---

## 💬 **5. ADDON WHATSAPP (7/15)**

### 📱 **WhatsApp Business API**
1. Créez un compte [Meta Business](https://business.facebook.com/)
2. Configurez WhatsApp Business API
3. Obtenez vos credentials

```bash
# Étapes détaillées :
# 1. Allez sur https://business.facebook.com/
# 2. Créez un Business Manager
# 3. Ajoutez WhatsApp comme produit
# 4. Configurez un numéro de téléphone
# 5. Obtenez le token d'accès permanent

WHATSAPP_API_TOKEN=votre_token_permanent
WHATSAPP_PHONE_NUMBER_ID=123456789012345
WHATSAPP_BUSINESS_ACCOUNT_ID=votre_business_id
WHATSAPP_APP_SECRET=votre_app_secret
WHATSAPP_WEBHOOK_VERIFY_TOKEN=votre_token_unique
```

---

## 📧 **6. CONFIGURATION EMAIL**

### 📮 **Gmail SMTP** (recommandé)
```bash
MAIL_MAILER=smtp
MAIL_HOST=smtp.gmail.com
MAIL_PORT=587
MAIL_USERNAME=votre-email@gmail.com
MAIL_PASSWORD=votre_mot_de_passe_app  # Pas votre mot de passe Gmail !
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS="noreply@votre-domaine.com"
```

**⚠️ Important :** Utilisez un "Mot de passe d'application" Gmail, pas votre mot de passe principal.

---

## 🔍 **7. ADDON SEO (1/15)**

```bash
SEO_DEFAULT_TITLE="Votre Restaurant - Commande en ligne"
SEO_DEFAULT_DESCRIPTION="Commandez en ligne facilement. Menu QR, livraison rapide, paiement sécurisé."
SEO_DEFAULT_KEYWORDS="restaurant,commande,livraison,menu"
SEO_GOOGLE_SITE_VERIFICATION=votre_code_verification
```

---

## 🌍 **8. ADDON MULTI-LANGUAGE (3/15)**

```bash
DEFAULT_LANGUAGE=fr
SUPPORTED_LANGUAGES=fr,en,ar
FALLBACK_LANGUAGE=fr
AUTO_DETECT_LANGUAGE=true
```

---

## 📱 **9. ADDON QR MENU (4/15)**

```bash
QR_MENU_ENABLED=true
QR_CODE_SIZE=200
QR_CODE_MARGIN=2
QR_CODE_FORMAT=png
```

---

## ⭐ **10. ADDON REVIEWS (5/15)**

```bash
REVIEWS_ENABLED=true
REVIEWS_MODERATION=true  # Les avis nécessitent une modération
REVIEWS_MIN_RATING=1
REVIEWS_MAX_RATING=5
REVIEWS_REQUIRE_LOGIN=false  # Avis anonymes autorisés
```

---

## 📅 **11. ADDON BOOKING (6/15)**

```bash
BOOKING_ENABLED=true
BOOKING_ADVANCE_DAYS=30      # Réservations jusqu'à 30 jours à l'avance
BOOKING_TIME_SLOTS=30        # Créneaux de 30 minutes
BOOKING_MIN_GUESTS=1
BOOKING_MAX_GUESTS=20
BOOKING_CONFIRMATION_REQUIRED=true
```

---

## 🎁 **12. ADDON LOYALTY (9/15)**

```bash
LOYALTY_ENABLED=true
LOYALTY_POINTS_PER_EURO=10        # 10 points par euro dépensé
LOYALTY_MIN_REDEMPTION_POINTS=100  # Minimum 100 points pour utiliser
LOYALTY_EXPIRY_MONTHS=12          # Points expirent après 12 mois
```

---

## 🚚 **13. ADDON DELIVERY (10/15)**

```bash
DELIVERY_ENABLED=true
DELIVERY_RADIUS=10              # Rayon de livraison en km
DELIVERY_BASE_FEE=5.00         # Frais de base
DELIVERY_PER_KM_FEE=1.50       # Prix par km
DELIVERY_FREE_ABOVE=50.00      # Livraison gratuite au-dessus de 50€
DELIVERY_TIME_ESTIMATE=30       # Estimation en minutes
```

---

## 💳 **14. ADDON POS (11/15)**

```bash
POS_ENABLED=true
POS_PRINTER_IP=192.168.1.100   # IP de votre imprimante de reçus
POS_RECEIPT_WIDTH=58           # Largeur en mm
POS_AUTO_PRINT=true            # Impression automatique
```

---

## 📋 **15. ADDON MENU (12/15)**

```bash
MENU_CACHE_ENABLED=true
MENU_CACHE_DURATION=3600       # Cache pendant 1 heure
MENU_IMAGES_REQUIRED=false
MENU_MAX_CATEGORIES=50
MENU_MAX_ITEMS_PER_CATEGORY=100
```

---

## 📢 **16. ADDON MARKETING (13/15)**

```bash
MARKETING_EMAIL_ENABLED=true
MARKETING_SMS_ENABLED=false
MARKETING_PUSH_NOTIFICATIONS=true
MARKETING_NEWSLETTER_ENABLED=true
```

---

## 💰 **17. ADDON FINANCE (14/15)**

```bash
FINANCE_CURRENCY=EUR           # ou USD, XOF, etc.
FINANCE_TAX_RATE=20.0         # Taux de TVA en %
FINANCE_REPORTING_ENABLED=true
FINANCE_AUTO_RECONCILIATION=false
```

---

## 👥 **18. ADDON STAFF (15/15)**

```bash
STAFF_ROLES_ENABLED=true
STAFF_PERMISSIONS_ENABLED=true
STAFF_TIME_TRACKING=false
STAFF_PAYROLL_ENABLED=false
```

---

## 🔐 **19. SÉCURITÉ & CAPTCHA**

### 🛡️ **reCAPTCHA**
1. Allez sur [Google reCAPTCHA](https://www.google.com/recaptcha/)
2. Créez un site reCAPTCHA v3
```bash
RECAPTCHA_SITE_KEY=votre_site_key
RECAPTCHA_SECRET_KEY=votre_secret_key
RECAPTCHA_SCORE_THRESHOLD=0.5
```

---

## 📱 **20. PWA (Application Web)**

```bash
PWA_NAME="Votre Restaurant App"
PWA_SHORT_NAME="Restaurant"
PWA_DESCRIPTION="Application de commande en ligne"
PWA_THEME_COLOR="#1f2937"
PWA_BACKGROUND_COLOR="#ffffff"
```

---

## ✅ **ÉTAPES FINALES**

### 🔧 **Après configuration**
```bash
# 1. Générer la clé d'application
php artisan key:generate

# 2. Créer les tables
php artisan migrate

# 3. Optimiser pour production
php artisan config:cache
php artisan route:cache
php artisan view:cache

# 4. Tester la configuration
./scripts/test-all-15-addons.sh
```

### 🎯 **Configuration minimale pour démarrer**
Pour un démarrage rapide, configurez au minimum :
1. **Base de données** (obligatoire)
2. **Email** (recommandé)
3. **WhatsApp** (pour les commandes)
4. **Stripe** (pour les paiements)

Les autres addons peuvent être configurés progressivement.

---

## 🚨 **IMPORTANT - SÉCURITÉ**

### 🔒 **Conseils de sécurité**
1. **Gardez le `.env` secret** - Ne le commitez jamais dans git
2. **Utilisez HTTPS** en production
3. **Changez tous les tokens par défaut**
4. **Activez le mode production** (`APP_ENV=production`)
5. **Désactivez le debug** (`APP_DEBUG=false`)

### 🔐 **Tokens à changer absolument**
- `WHATSAPP_WEBHOOK_VERIFY_TOKEN`
- `APP_KEY` (généré par `php artisan key:generate`)
- Tous les tokens et secrets API

---

## 📞 **SUPPORT**

Si vous avez des questions :
1. Consultez la documentation : `documentation/`
2. Utilisez les scripts de test : `scripts/`
3. Vérifiez les logs : `storage/logs/`

**🎯 Avec cette configuration complète, tous les 15 addons seront opérationnels !**

---

*Guide de configuration RestroSaaS - Version complète pour 15 addons* 🔧
