# Guide de Configuration Firebase pour RestroSaaS

## Prérequis

1. Un compte Google/Firebase
2. Accès à la console Firebase : https://console.firebase.google.com/
3. PHP 8.1+ avec extensions JSON et cURL

## Étapes de Configuration

### 1. Créer un Projet Firebase

1. Allez sur https://console.firebase.google.com/
2. Cliquez sur "Ajouter un projet"
3. Entrez le nom de votre projet (ex: "RestroSaaS-Production")
4. Activez Google Analytics (recommandé)
5. Sélectionnez ou créez un compte Analytics
6. Cliquez sur "Créer le projet"

### 2. Configurer Firebase Cloud Messaging

1. Dans la console Firebase, allez dans "Cloud Messaging"
2. Cliquez sur "Commencer"
3. Activez les APIs requises si demandé

### 3. Ajouter une Application Web

1. Dans "Vue d'ensemble du projet", cliquez sur l'icône Web (</>)
2. Enregistrez votre application avec un nom (ex: "RestroSaaS Web")
3. Cochez "Configurer également Firebase Hosting" si souhaité
4. Cliquez sur "Enregistrer l'application"
5. **Copiez les valeurs de configuration** affichées

### 4. Générer les Clés d'Authentification

#### Clé Serveur (Server Key)
1. Allez dans "Paramètres du projet" > "Cloud Messaging"
2. Sous "API Cloud Messaging (V1)", notez l'ID du projet
3. Ou sous "API Cloud Messaging (hérité)", copiez la "Clé du serveur"

#### Fichier de Credentials JSON
1. Allez dans "Paramètres du projet" > "Comptes de service"
2. Cliquez sur "Générer une nouvelle clé privée"
3. Téléchargez le fichier JSON
4. **Placez ce fichier dans un endroit sécurisé** (pas dans le dossier public)
5. Notez le chemin vers ce fichier pour `FIREBASE_CREDENTIALS`

#### Clés VAPID (Web Push)
1. Allez dans "Paramètres du projet" > "Cloud Messaging"
2. Sous "Configuration Web", cliquez sur "Générer une paire de clés"
3. Copiez la clé publique et privée générées

### 5. Configuration de l'Application RestroSaaS

1. Copiez le fichier `.env.firebase.example` vers `.env` (ou ajoutez à votre .env existant)
2. Remplissez toutes les valeurs Firebase obtenues précédemment :

```env
FIREBASE_ENABLED=true
FIREBASE_PROJECT_ID=votre-project-id
FIREBASE_SERVER_KEY=votre-server-key
FIREBASE_CREDENTIALS=storage/app/private/firebase-credentials.json
FIREBASE_WEB_API_KEY=votre-web-api-key
FIREBASE_MESSAGING_SENDER_ID=votre-sender-id
FIREBASE_APP_ID=votre-app-id
FIREBASE_VAPID_PUBLIC_KEY=votre-vapid-public-key
FIREBASE_VAPID_PRIVATE_KEY=votre-vapid-private-key
```

### 6. Test de Configuration

1. Exécutez la commande de test :
```bash
php artisan firebase:test-connection
```

2. Envoyez une notification de test :
```bash
php artisan firebase:send-test-notification
```

### 7. Configuration Avancée

#### Webhooks (Optionnel)
Pour recevoir des confirmations de livraison et statistiques :
1. Configurez des endpoints dans votre application
2. Ajoutez les URLs dans votre .env :
```env
FIREBASE_WEBHOOK_DELIVERY=https://votre-domaine.com/webhooks/firebase/delivery
FIREBASE_WEBHOOK_OPEN=https://votre-domaine.com/webhooks/firebase/open
```

#### Topics et Segments
Les topics par défaut sont configurés automatiquement :
- `all_users` : Tous les utilisateurs
- `restaurants` : Tous les restaurants
- `customers` : Tous les clients
- `orders` : Notifications de commandes
- `promotions` : Offres et promotions

## Sécurité et Bonnes Pratiques

### 1. Sécurité des Clés
- **Jamais** commiter le fichier `firebase-credentials.json`
- Utilisez des variables d'environnement sécurisées en production
- Stockez le fichier credentials dans `storage/app/private/`

### 2. Restrictions API
1. Dans la console Firebase > APIs & Services > Credentials
2. Cliquez sur votre clé API
3. Sous "Restrictions d'application", sélectionnez "Références HTTP"
4. Ajoutez vos domaines autorisés

### 3. Surveillance
1. Activez les alertes de quota dans la console Firebase
2. Surveillez les logs d'erreurs dans "Cloud Messaging" > "Rapports"
3. Configurez des limites appropriées dans `config/firebase.php`

### 4. Performance
- Activez le cache Redis/Memcached pour les tokens
- Utilisez les queues pour les envois en masse
- Configurez des workers dédiés pour Firebase

## Dépannage

### Erreurs Communes

#### "Authentication failed"
- Vérifiez le chemin vers `firebase-credentials.json`
- Assurez-vous que le fichier a les bonnes permissions (644)
- Vérifiez que le service account a les rôles requis

#### "Invalid token"
- Les tokens d'appareils expirent, implémentez un nettoyage automatique
- Vérifiez que l'application cliente utilise la bonne configuration

#### "Quota exceeded"
- Vérifiez vos quotas dans la console Firebase
- Implémentez une limitation du taux d'envoi
- Considérez l'upgrade vers un plan payant

### Commandes Utiles

```bash
# Tester la connexion Firebase
php artisan firebase:test-connection

# Nettoyer les tokens expirés
php artisan firebase:cleanup-devices

# Envoyer une notification test
php artisan firebase:send-test-notification

# Traiter les notifications programmées
php artisan firebase:process-scheduled

# Voir les statistiques
php artisan firebase:stats
```

### Support et Documentation

- Documentation officielle : https://firebase.google.com/docs/cloud-messaging
- Console Firebase : https://console.firebase.google.com/
- Status Firebase : https://status.firebase.google.com/

## Configuration de Production

### Variables d'Environnement Critiques
```env
FIREBASE_ENABLED=true
FIREBASE_QUEUE_ENABLED=true
FIREBASE_ANALYTICS_ENABLED=true
FIREBASE_LOGGING_ENABLED=true
FIREBASE_LOG_LEVEL=warning
```

### Optimisations de Performance
1. Utilisez Redis pour le cache des tokens
2. Configurez des workers queue dédiés
3. Activez la compression des payloads
4. Utilisez des CDN pour les assets statiques

### Surveillance Production
1. Configurez des alertes sur les taux d'erreur
2. Surveillez les quotas et limites
3. Implémentez des métriques custom avec Analytics
4. Configurez des sauvegardes des configurations critiques
