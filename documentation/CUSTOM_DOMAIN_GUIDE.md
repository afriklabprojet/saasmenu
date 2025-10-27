# 🌐 Système de Domaines Personnalisés

## Vue d'ensemble

Le système de domaines personnalisés permet à chaque restaurant/café d'utiliser son propre nom de domaine (ex: `monrestaurant.com`) au lieu de l'URL générique de la plateforme.

**Disponibilité** : À partir du plan **Starter**

---

## ✨ Fonctionnalités

### Pour les Restaurants

1. **Configuration Simple**
   - Interface intuitive pour ajouter un domaine
   - Instructions DNS pas à pas
   - Vérification automatique

2. **Vérification Automatique**
   - Détection CNAME et A records
   - Validation en un clic
   - Notification de succès/échec

3. **Gestion Complète**
   - Modification du domaine
   - Suppression si nécessaire
   - Statut en temps réel

### Pour les Clients

- Accès direct via le domaine personnalisé
- Redirection automatique vers la page du restaurant
- Expérience de marque cohérente

---

## 🚀 Installation et Configuration

### 1. Migration de la Base de Données

```bash
php artisan migrate
```

Cela ajoutera les colonnes suivantes à la table `users` :
- `custom_domain` (nullable)
- `domain_verified` (boolean)
- `domain_verified_at` (timestamp nullable)

### 2. Configuration du Middleware

Le middleware `CustomDomainMiddleware` est déjà créé. Pour l'activer globalement, ajoutez-le dans `app/Http/Kernel.php` :

```php
protected $middleware = [
    // ... autres middlewares
    \App\Http\Middleware\CustomDomainMiddleware::class,
];
```

### 3. Configuration DNS (Serveur)

#### Option A : Wildcard DNS (Recommandé)

Configurez un enregistrement DNS wildcard pour tous les sous-domaines :

```
*.votredomaine.com  →  IP_DU_SERVEUR
```

#### Option B : CNAME Wildcard

```
*  CNAME  votredomaine.com
```

### 4. Configuration Apache/Nginx

#### Apache (.htaccess ou VirtualHost)

```apache
<VirtualHost *:80>
    ServerName votredomaine.com
    ServerAlias *.votredomaine.com
    
    DocumentRoot /var/www/html/public
    
    <Directory /var/www/html/public>
        AllowOverride All
        Require all granted
    </Directory>
</VirtualHost>
```

#### Nginx

```nginx
server {
    listen 80;
    server_name votredomaine.com *.votredomaine.com;
    
    root /var/www/html/public;
    index index.php index.html;
    
    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }
    
    location ~ \.php$ {
        fastcgi_pass unix:/var/run/php/php8.2-fpm.sock;
        fastcgi_index index.php;
        include fastcgi_params;
    }
}
```

---

## 📖 Guide d'Utilisation (Restaurant)

### Étape 1 : Accéder à la Configuration

1. Connectez-vous à votre tableau de bord
2. Cliquez sur **"🌐 Mon Domaine"** dans le menu latéral

### Étape 2 : Ajouter votre Domaine

1. Entrez votre nom de domaine (ex: `monrestaurant.com`)
2. Cliquez sur **"Enregistrer le Domaine"**

### Étape 3 : Configurer le DNS

Rendez-vous chez votre registrar de domaine (GoDaddy, Namecheap, OVH, etc.) et ajoutez :

**Enregistrement CNAME**
```
Type: CNAME
Nom: www
Valeur: votredomaine.com
TTL: 3600
```

**Enregistrement A** (optionnel)
```
Type: A
Nom: @
Valeur: IP_DU_SERVEUR
TTL: 3600
```

### Étape 4 : Vérifier le Domaine

1. Attendez 1 à 48h pour la propagation DNS
2. Retournez sur **"🌐 Mon Domaine"**
3. Cliquez sur **"Vérifier le Domaine"**
4. Si succès : ✅ Votre restaurant est maintenant accessible via votre domaine !

---

## 🔧 API et Endpoints

### Routes Disponibles

```php
// Interface de gestion
GET  /admin/custom-domain

// Enregistrer un domaine
POST /admin/custom-domain

// Vérifier un domaine
POST /admin/custom-domain/verify

// Supprimer un domaine
DELETE /admin/custom-domain

// Obtenir les instructions DNS (JSON)
GET /admin/custom-domain/dns-instructions
```

### Exemple de Réponse API (DNS Instructions)

```json
{
    "domain": "monrestaurant.com",
    "app_domain": "votreplateforme.com",
    "app_ip": "123.456.789.0",
    "cname_record": {
        "type": "CNAME",
        "name": "@",
        "value": "votreplateforme.com"
    },
    "a_record": {
        "type": "A",
        "name": "@",
        "value": "123.456.789.0"
    }
}
```

---

## 🛡️ Sécurité

### Validation du Domaine

- **Format** : Regex `/^[a-z0-9]+([\-\.]{1}[a-z0-9]+)*\.[a-z]{2,6}$/`
- **Unicité** : Un domaine ne peut être utilisé que par un seul restaurant
- **Domaine principal** : Impossible d'utiliser le domaine de la plateforme

### Vérification DNS

1. **Vérification CNAME** : `dns_get_record()` pour valider le CNAME
2. **Vérification IP** : `gethostbyname()` pour valider l'enregistrement A
3. **Double vérification** : Les deux méthodes sont utilisées pour plus de fiabilité

### Protection

- Le middleware vérifie que le domaine est :
  - ✅ Enregistré dans la base de données
  - ✅ Vérifié (`domain_verified = true`)
  - ✅ Appartient à un vendor actif
  - ✅ Le restaurant n'est pas supprimé

---

## 🎨 Interface Utilisateur

### Dashboard Principal

- Badge de statut :
  - 🟢 **Vérifié** : Domaine actif
  - 🟡 **En attente** : Configuration DNS en cours
  - ⚫ **Non configuré** : Aucun domaine

### Menu Latéral

- Icône 🌐 avec badge visuel
- Badge vert (✓) si vérifié
- Badge jaune (!) si en attente

### Page de Configuration

1. **Formulaire d'ajout** (si aucun domaine)
2. **Carte de domaine actuel** (si domaine configuré)
3. **Instructions DNS détaillées**
4. **Avantages du domaine personnalisé**

---

## 📊 Base de Données

### Colonnes Ajoutées (table `users`)

| Colonne | Type | Description |
|---------|------|-------------|
| `custom_domain` | varchar(255) | Nom de domaine personnalisé |
| `domain_verified` | boolean | Statut de vérification |
| `domain_verified_at` | timestamp | Date de vérification |

### Requêtes Utiles

```sql
-- Trouver tous les domaines vérifiés
SELECT name, custom_domain, domain_verified_at 
FROM users 
WHERE domain_verified = 1 AND custom_domain IS NOT NULL;

-- Domaines en attente de vérification
SELECT name, custom_domain 
FROM users 
WHERE custom_domain IS NOT NULL AND domain_verified = 0;

-- Statistiques
SELECT 
    COUNT(*) as total_domains,
    SUM(CASE WHEN domain_verified = 1 THEN 1 ELSE 0 END) as verified,
    SUM(CASE WHEN domain_verified = 0 THEN 1 ELSE 0 END) as pending
FROM users 
WHERE custom_domain IS NOT NULL;
```

---

## 🐛 Dépannage

### Problème : "Domaine ne pointe pas vers notre serveur"

**Solutions** :
1. Vérifiez la configuration DNS chez votre registrar
2. Attendez la propagation DNS (jusqu'à 48h)
3. Utilisez `nslookup` ou `dig` pour tester :
   ```bash
   nslookup monrestaurant.com
   dig monrestaurant.com
   ```

### Problème : "404 Not Found" après vérification

**Solutions** :
1. Videz le cache Laravel : `php artisan cache:clear`
2. Redémarrez le serveur web
3. Vérifiez la configuration VirtualHost/server block

### Problème : "Ce domaine est déjà utilisé"

**Solution** : Contactez le support pour vérifier l'utilisation du domaine

### Problème : SSL/HTTPS

**Solution** : Configurez Let's Encrypt avec Certbot :
```bash
certbot --apache -d monrestaurant.com -d www.monrestaurant.com
```

---

## ✅ Checklist de Déploiement

### Côté Serveur
- [ ] Migration exécutée
- [ ] Middleware activé
- [ ] DNS wildcard configuré
- [ ] VirtualHost/ServerBlock configuré
- [ ] SSL configuré (Let's Encrypt)
- [ ] Routes enregistrées

### Côté Application
- [ ] Interface de gestion accessible
- [ ] Validation des domaines fonctionnelle
- [ ] Menu latéral mis à jour
- [ ] Tests de vérification DNS

### Côté Restaurant
- [ ] Accès à "Mon Domaine"
- [ ] Instructions DNS claires
- [ ] Vérification en un clic
- [ ] Feedback utilisateur approprié

---

## 🚀 Améliorations Futures

1. **Auto-SSL** : Configuration automatique des certificats SSL
2. **Multi-domaines** : Permettre plusieurs domaines par restaurant
3. **Sous-domaines** : Support de sous-domaines personnalisés
4. **Analytics** : Statistiques par domaine
5. **Email** : Configuration SMTP par domaine
6. **CDN** : Intégration Cloudflare automatique

---

## 📞 Support

Pour toute question ou problème :
- **Documentation** : Consultez ce guide
- **Support** : Contactez l'équipe technique
- **Communauté** : Forum de la plateforme

---

## 📝 Changelog

### Version 1.0.0 (23 octobre 2025)
- ✅ Système de domaines personnalisés
- ✅ Interface de gestion complète
- ✅ Vérification DNS automatique
- ✅ Instructions pas à pas
- ✅ Badge de statut en temps réel
- ✅ Support CNAME et A records
- ✅ Sécurité et validation
