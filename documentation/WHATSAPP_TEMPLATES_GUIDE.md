# 📱 Guide des Templates WhatsApp - Optimisés

**Date**: 23 octobre 2025  
**Version**: 2.0 - Templates Optimisés  
**Statut**: ✅ Production Ready

---

## 🎯 VUE D'ENSEMBLE

Ce système de templates WhatsApp offre des messages **professionnels**, **personnalisés** et **optimisés** pour chaque étape du cycle de vie d'une commande.

### ✨ Nouveautés

- ✅ **7 templates prédéfinis** (nouvelle commande, confirmation, préparation, etc.)
- ✅ **Messages structurés** avec emojis et mise en forme
- ✅ **Variables dynamiques** (25+ variables disponibles)
- ✅ **Notifications automatiques** par statut
- ✅ **Configuration flexible** via `.env` et `config/`
- ✅ **Multilingue** (FR, EN, AR)

---

## 📋 TEMPLATES DISPONIBLES

### 1. 🎉 Nouvelle Commande (`new_order`)

**Quand**: Immédiatement après la création de la commande  
**Destinataire**: Client  
**Contenu**:
- Informations client et commande
- Liste détaillée des articles avec prix
- Résumé financier complet
- Adresse de livraison
- Liens de suivi

**Exemple**:
```
🎉 Nouvelle Commande 🎉
══════════════════════════════

👤 Client : Jean Dupont
📱 Téléphone : +237 690 123 456
🏪 Restaurant : La Belle Époque
📦 Commande : #ORD-2025-001
🎯 Type : 🚗 Livraison
📅 Date : 23/10/2025 à 19:30

🛒 Articles Commandés :
──────────────────────────────

1. Poulet Braisé (Grande portion)
   Quantité: 2 x 3500 FCFA = 7000 FCFA
   ➕ Sauce piquante: 200 FCFA
   ➕ Plantain frit: 500 FCFA

2. Riz Sauté aux Crevettes
   Quantité: 1 x 4000 FCFA = 4000 FCFA

──────────────────────────────
💰 Résumé Financier :

• Sous-total : 11700 FCFA
• TVA (10%) : 1170 FCFA
• Frais de livraison : 500 FCFA
• 🎁 Réduction : -500 FCFA

══════════════════════════════
🎯 TOTAL : 12870 FCFA
══════════════════════════════

📍 Adresse de Livraison :
Avenue de l'Indépendance, Yaoundé
🏢 Bâtiment: Immeuble SOPECAM
🗺️ Repère: En face du Ministère

💳 Mode de Paiement : Mobile Money

──────────────────────────────
📱 Suivi en Temps Réel :
https://restaurant.com/track-order/ORD-2025-001

🏪 Voir le Menu :
https://restaurant.com/la-belle-epoque

Merci de votre confiance ! 🙏
```

---

### 2. ✅ Commande Confirmée (`order_confirmed`)

**Quand**: Restaurant accepte la commande  
**Destinataire**: Client  
**Format**: Simple et concis

**Exemple**:
```
✅ Commande Confirmée ✅

Bonjour Jean Dupont !

Votre commande #ORD-2025-001 a été confirmée.

📦 Détails :
• Restaurant: La Belle Époque
• Total: 12870 FCFA
• Livraison prévue: 23/10/2025 à 19:30

📍 Suivez votre commande:
https://restaurant.com/track-order/ORD-2025-001

Merci de votre confiance ! 🙏
```

---

### 3. 👨‍🍳 Préparation en Cours (`order_preparing`)

**Quand**: Chef commence la préparation  
**Destinataire**: Client

**Exemple**:
```
👨‍🍳 Préparation en Cours 👨‍🍳

Bonjour Jean Dupont !

Bonne nouvelle ! Notre chef prépare votre commande #ORD-2025-001 avec soin.

⏱️ Temps estimé : 20-30 minutes

Vous serez notifié dès que votre commande sera prête.

📍 Suivi en temps réel:
https://restaurant.com/track-order/ORD-2025-001
```

---

### 4. ✨ Commande Prête (`order_ready`)

**Quand**: Commande terminée  
**Destinataire**: Client  
**Variations**: Livraison VS Retrait

**Exemple (Livraison)**:
```
✨ Commande Prête ✨

Bonjour Jean Dupont !

Votre commande #ORD-2025-001 est prête ! 🎉

🚗 Livraison en cours
Notre livreur est en route vers :
📍 Avenue de l'Indépendance, Yaoundé
🏢 Immeuble SOPECAM
🗺️ Repère: En face du Ministère

Merci de rester disponible au +237 690 123 456

Bon appétit ! 🍽️
```

**Exemple (Retrait)**:
```
✨ Commande Prête ✨

Bonjour Jean Dupont !

Votre commande #ORD-2025-001 est prête ! 🎉

🏪 Retrait au restaurant
Vous pouvez venir récupérer votre commande chez :
📍 La Belle Époque
☎️ Contact: +237 699 000 000

Bon appétit ! 🍽️
```

---

### 5. 💳 Rappel de Paiement (`payment_reminder`)

**Quand**: 15 min après commande si paiement en attente  
**Destinataire**: Client  
**Contient**: Lien de paiement CinetPay

**Exemple**:
```
💳 Rappel de Paiement 💳
══════════════════════════════

Bonjour Jean Dupont !

Votre commande #ORD-2025-001 est en attente de paiement.

💰 Montant à payer : 12870 FCFA

💳 Payer maintenant :
https://cinetpay.com/payment/xyz123

📞 Besoin d'aide ? Contactez-nous :
+237 699 000 000
```

---

### 6. 🚗 En Route (`order_on_way`)

**Quand**: Livreur démarre  
**Destinataire**: Client

**Exemple**:
```
🚗 Livraison en Route 🚗

Bonjour Jean Dupont !

Votre commande #ORD-2025-001 est en route !

📍 Adresse : Avenue de l'Indépendance
📱 Restez disponible au : +237 690 123 456

⏱️ Livraison estimée : 10-15 minutes

À tout de suite ! 😊
```

---

### 7. 🎊 Livraison Effectuée (`order_delivered`)

**Quand**: Commande livrée  
**Destinataire**: Client

**Exemple**:
```
🎊 Livraison Effectuée 🎊

Bonjour Jean Dupont !

Votre commande #ORD-2025-001 a été livrée.

Merci d'avoir choisi La Belle Époque !

⭐ Votre avis nous intéresse :
https://restaurant.com/la-belle-epoque

À très bientôt ! 🙏
```

---

## 🔧 CONFIGURATION

### Variables .env

Ajoutez dans `.env` :

```bash
# Notifications automatiques WhatsApp
WHATSAPP_AUTO_NOTIFY_ORDER_CREATED=true
WHATSAPP_AUTO_NOTIFY_ORDER_ACCEPTED=true
WHATSAPP_AUTO_NOTIFY_ORDER_PREPARING=true
WHATSAPP_AUTO_NOTIFY_ORDER_READY=true
WHATSAPP_AUTO_NOTIFY_ORDER_ON_WAY=true
WHATSAPP_AUTO_NOTIFY_ORDER_DELIVERED=true
WHATSAPP_AUTO_NOTIFY_ORDER_CANCELLED=true
WHATSAPP_AUTO_NOTIFY_PAYMENT_PENDING=false

# Langue par défaut
WHATSAPP_DEFAULT_LANGUAGE=fr
```

---

## 💻 UTILISATION DANS LE CODE

### Méthode 1 : Service (Recommandé)

```php
use App\Services\WhatsAppTemplateService;

// Nouvelle commande
$message = WhatsAppTemplateService::generateNewOrderMessage(
    $order_number,
    $vendor_id,
    $vendordata
);

// Confirmation
$message = WhatsAppTemplateService::generateConfirmationMessage(
    $order_number,
    $vendor_id,
    $vendordata
);

// Préparation
$message = WhatsAppTemplateService::generatePreparingMessage(
    $order_number,
    $vendor_id,
    $vendordata
);

// Prête
$message = WhatsAppTemplateService::generateReadyMessage(
    $order_number,
    $vendor_id,
    $vendordata
);

// Rappel paiement
$message = WhatsAppTemplateService::generatePaymentReminderMessage(
    $order_number,
    $vendor_id,
    $vendordata,
    $payment_link
);
```

### Méthode 2 : Helper (Existant)

```php
// Utilise le template personnalisé du restaurant
$message = helper::whatsappmessage($order_number, $vdata, $storeinfo);
```

---

## 🎨 PERSONNALISATION

### Modifier les Templates

Éditez `config/whatsapp-templates.php` :

```php
'templates' => [
    'order_confirmed' => [
        'enabled' => true,
        'template' => "Votre message personnalisé avec {order_number}",
    ],
],
```

### Variables Disponibles

| Variable | Description | Exemple |
|----------|-------------|---------|
| `{customer_name}` | Nom du client | Jean Dupont |
| `{order_number}` | Numéro commande | #ORD-2025-001 |
| `{store_name}` | Nom restaurant | La Belle Époque |
| `{grand_total}` | Total | 12870 FCFA |
| `{delivery_type}` | Type | 🚗 Livraison |
| `{date}` | Date livraison | 23/10/2025 |
| `{time}` | Heure | 19:30 |
| `{track_order_url}` | Lien suivi | https://... |
| `{payment_link}` | Lien paiement | https://cinetpay... |

**Liste complète** : 25+ variables (voir `config/whatsapp-templates.php`)

---

## 🌍 MULTILINGUE

### Ajouter une Langue

1. **Créer les traductions** dans `config/whatsapp-templates.php`

2. **Détecter la langue du client** :
```php
$language = $customer->preferred_language ?? config('whatsapp-templates.default_language');
```

3. **Utiliser le bon template** :
```php
$template = config("whatsapp-templates.templates.{$event}.{$language}");
```

---

## 📊 BONNES PRATIQUES

### ✅ DO

- ✅ **Être concis** : Maximum 4096 caractères WhatsApp
- ✅ **Utiliser des emojis** : Rend le message plus visuel
- ✅ **Structurer** : Sections claires avec séparateurs
- ✅ **Inclure les liens** : Suivi, paiement, menu
- ✅ **Personnaliser** : Toujours utiliser le nom du client
- ✅ **Tester** : Vérifier l'affichage sur mobile

### ❌ DON'T

- ❌ **Messages trop longs** : Limite 4096 caractères
- ❌ **Trop d'emojis** : Maximum 3-4 par section
- ❌ **Informations manquantes** : Vérifier les variables
- ❌ **Liens cassés** : Valider les URLs
- ❌ **Spam** : Respecter les délais entre messages

---

## 🧪 TESTS

### Tester un Template

```bash
# Dans tinker
php artisan tinker

# Générer un message
$message = \App\Services\WhatsAppTemplateService::generateNewOrderMessage('ORD-001', 1, $vendor);
echo urldecode($message);
```

### Vérifier les Variables

```php
// Lister toutes les variables
$variables = config('whatsapp-templates.variables');
dd($variables);
```

---

## 🔄 MIGRATION DEPUIS L'ANCIEN SYSTÈME

### Ancien Code (helper.php)

```php
$whmessage = helper::whatsappmessage($order_number, $vdata, $storeinfo);
```

### Nouveau Code (Service)

```php
$whmessage = WhatsAppTemplateService::generateNewOrderMessage($order_number, $vdata, $storeinfo);
```

**Compatibilité** : Les deux méthodes fonctionnent en parallèle.

---

## 📈 ANALYTICS

### Mesures Recommandées

- **Taux de livraison** : Messages envoyés VS délivrés
- **Taux d'ouverture** : Messages lus
- **Taux de clic** : Clics sur liens (suivi, paiement)
- **Temps de réponse** : Client → Restaurant
- **Conversions** : Paiement après rappel

### Implémentation

```php
// Logger l'envoi
\Log::info('WhatsApp sent', [
    'order' => $order_number,
    'template' => 'new_order',
    'customer' => $customer_phone
]);

// Tracker les clics (URL courtes)
$track_url = URL::to('/t/' . base64_encode($order_number));
```

---

## 🚀 PROCHAINES ÉTAPES

1. ✅ **Intégrer au workflow** : Déclencher automatiquement
2. ✅ **Tester en production** : 10-20 commandes test
3. ✅ **Collecter feedback** : Clients + Restaurants
4. ✅ **Optimiser** : Ajuster selon les retours
5. ✅ **A/B Testing** : Tester différentes versions

---

## 📞 SUPPORT

**Documentation** :
- Guide principal : `WHATSAPP_FIRST_STRATEGY.md`
- Configuration : `config/whatsapp-templates.php`
- Service : `app/Services/WhatsAppTemplateService.php`

**Exemples** :
- Voir les 7 templates complets dans ce document
- Tester avec `php artisan tinker`

---

**Version** : 2.0  
**Dernière mise à jour** : 23 octobre 2025  
**Auteur** : GitHub Copilot
