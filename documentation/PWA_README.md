# 📱 PWA E-menu - Progressive Web App

## 🌟 Vue d'ensemble

Le système PWA (Progressive Web App) d'E-menu transforme votre plateforme de commande en ligne en une application mobile native installable. Vos clients peuvent installer l'application directement depuis leur navigateur et profiter d'une expérience mobile optimale avec des notifications push, un mode hors ligne, et des performances améliorées.

## ✨ Fonctionnalités

### 🚀 Installation Native
- **Installation en un clic** depuis n'importe quel navigateur
- **Interface d'installation personnalisée** avec instructions spécifiques par plateforme
- **Détection automatique** des possibilités d'installation
- **Mode standalone** pour une expérience app native

### 🔔 Notifications Push
- **Notifications temps réel** pour les commandes
- **Notifications de statut** (confirmée, en préparation, prête, livrée)
- **Promotions et offres spéciales**
- **Gestion des souscriptions** utilisateur
- **Système de retry** automatique

### 📴 Mode Hors Ligne
- **Cache intelligent** des ressources essentielles
- **Page hors ligne personnalisée** avec retry automatique
- **Synchronisation en arrière-plan** quand la connexion revient
- **Stratégie de cache optimisée**

### 🎨 Interface Optimisée
- **Design responsive** adapté à tous les écrans
- **Animations fluides** et micro-interactions
- **Thème adaptatif** selon les couleurs de la marque
- **Interface native** en mode installé

## 📋 Prérequis

### Serveur
- **HTTPS obligatoire** (certificat SSL)
- **PHP 8.1+** avec Laravel 9+
- **Base de données MySQL** pour les souscriptions
- **Serveur web moderne** (Apache/Nginx)

### Client
- **Navigateur compatible PWA** (Chrome, Firefox, Safari, Edge)
- **JavaScript activé**
- **Notifications autorisées** (optionnel)

## 🛠️ Installation

### 1. Base de données

```bash
# Exécuter les migrations
php artisan migrate
```

La migration `create_push_subscriptions_table` créera la table nécessaire pour stocker les souscriptions push.

### 2. Configuration

Ajoutez ces variables à votre fichier `.env` :

```env
# VAPID Keys pour les notifications push
VAPID_SUBJECT=mailto:admin@votre-domaine.com
VAPID_PUBLIC_KEY=votre_clé_publique_vapid
VAPID_PRIVATE_KEY=votre_clé_privée_vapid

# Configuration PWA
PWA_DEFAULT_ICON=/images/logo.png
PWA_DEFAULT_BADGE=/images/badge.png
PWA_CLEANUP_ENABLED=true
PWA_CLEANUP_DAYS=30
```

### 3. Génération des clés VAPID

```bash
# Via npm (recommandé)
npx web-push generate-vapid-keys

# Ou en ligne sur https://vapidkeys.com/
```

### 4. Routes

Les routes PWA sont automatiquement enregistrées via `routes/pwa.php` :

```php
// Manifest PWA
Route::get('/pwa/manifest.json', [PWAController::class, 'manifest'])->name('pwa.manifest');

// Service Worker
Route::get('/pwa/sw.js', [PWAController::class, 'serviceWorker'])->name('pwa.sw');

// Pages PWA
Route::get('/pwa/offline', [PWAController::class, 'offline'])->name('pwa.offline');
Route::get('/pwa/install', [PWAController::class, 'install'])->name('pwa.install');

// API Notifications
Route::post('/pwa/subscribe', [PWAController::class, 'subscribe'])->name('pwa.subscribe');
Route::post('/pwa/unsubscribe', [PWAController::class, 'unsubscribe'])->name('pwa.unsubscribe');
```

## 🎯 Utilisation

### Installation par l'utilisateur

1. **Navigateur Desktop** :
   - Icône d'installation dans la barre d'adresse
   - Bannière d'installation automatique
   - Menu navigateur > "Installer E-menu"

2. **Mobile Android** :
   - Bannière "Ajouter à l'écran d'accueil"
   - Menu Chrome > "Installer l'application"

3. **Mobile iOS** :
   - Safari > Partager > "Sur l'écran d'accueil"
   - Instructions automatiques affichées

### Notifications Push

```php
use App\Services\PushNotificationService;

$notificationService = new PushNotificationService();

// Notification de nouvelle commande
$notificationService->notifyNewOrder($userId, [
    'id' => $order->id,
    'order_number' => $order->number,
    'total' => $order->total
]);

// Notification de changement de statut
$notificationService->notifyOrderStatusChange($userId, [
    'id' => $order->id,
    'status' => 'ready'
]);

// Promotion à plusieurs utilisateurs
$notificationService->sendToUsers($userIds, [
    'title' => 'Offre spéciale !',
    'message' => '20% de réduction sur votre prochaine commande',
    'url' => '/promotions/special-offer'
]);
```

## 🔧 Personnalisation

### Couleurs et Thème

Le PWA s'adapte automatiquement aux couleurs de votre marque définies dans les paramètres E-menu :

```php
// Dans le manifest, les couleurs sont automatiquement récupérées
'theme_color' => $appData->primary_color ?? '#007bff'
'background_color' => '#ffffff'
```

### Icônes

Placez vos icônes dans `public/images/` :
- `logo.png` - Icône principale (512x512px minimum)
- `badge.png` - Badge de notification (96x96px)
- `favicon.ico` - Favicon du site

### Service Worker

Modifiez `resources/views/pwa/service-worker.blade.php` pour personnaliser :
- **Stratégie de cache**
- **Ressources mises en cache**
- **Comportement hors ligne**

## 📊 Monitoring

### Statistiques des notifications

```php
$notificationService = new PushNotificationService();
$stats = $notificationService->getStats();

// Retourne :
// - total_subscriptions
// - active_subscriptions  
// - users_with_notifications
// - recent_subscriptions
```

### Nettoyage automatique

```bash
# Supprimer les anciennes souscriptions (30 jours par défaut)
$notificationService->cleanupOldSubscriptions();
```

## 🐛 Dépannage

### PWA non détectée

1. **Vérifiez HTTPS** - PWA nécessite une connexion sécurisée
2. **Validez le manifest** - Utilisez Chrome DevTools > Application > Manifest
3. **Service Worker** - Vérifiez l'enregistrement dans DevTools > Application > Service Workers

### Notifications non reçues

1. **Permissions** - Vérifiez que les notifications sont autorisées
2. **Clés VAPID** - Vérifiez la configuration des clés
3. **Souscription** - Vérifiez que l'utilisateur est bien abonné

### Mode hors ligne

1. **Cache Service Worker** - Vérifiez les ressources en cache dans DevTools
2. **Stratégie de cache** - Ajustez selon vos besoins
3. **Page offline** - Personnalisez `/pwa/offline`

## 🚀 Optimisations

### Performance

- **Cache agressif** pour les ressources statiques
- **Lazy loading** des images
- **Compression Gzip/Brotli**
- **CDN** pour les assets

### SEO et Accessibilité

- **Meta tags** optimisés
- **Schema markup** pour les restaurants
- **Alt text** pour toutes les images
- **Navigation clavier**

## 📱 Tests

### Navigateurs supportés

- ✅ **Chrome 67+** (Android/Desktop)
- ✅ **Firefox 65+** (Android/Desktop)  
- ✅ **Safari 14+** (iOS 14+)
- ✅ **Edge 79+** (Windows/Android)

### Tests recommandés

1. **Installation** sur différents appareils
2. **Notifications** push avec payload réel
3. **Mode hors ligne** avec connexion coupée
4. **Performance** avec Lighthouse

## 📚 Ressources

- [Documentation PWA](https://web.dev/progressive-web-apps/)
- [Web Push Protocol](https://tools.ietf.org/html/rfc8030)
- [Service Workers](https://developer.mozilla.org/docs/Web/API/Service_Worker_API)
- [Web App Manifest](https://developer.mozilla.org/docs/Web/Manifest)

## 🤝 Support

Pour toute question ou problème :
- 📧 **Email** : support@e-menu.com
- 📖 **Documentation** : Consultez ce README
- 🐛 **Issues** : Signalez les bugs via votre plateforme de support

---

**E-menu PWA** - Transformez votre restaurant en application mobile native ! 🚀📱
