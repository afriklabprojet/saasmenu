# 🚀 GUIDE DE DÉPLOIEMENT EN PRODUCTION - RESTRO SAAS

## 📋 Vue d'ensemble

Ce guide vous permet de déployer l'application Restro SaaS en production avec le dump KOFFI.

**Date de création**: 24 octobre 2025  
**Base de données**: c2687072c_restooo225  
**Dump**: database/backups/koffi.sql (186 KB, 115 tables)

---

## ✅ ÉTAPE 1: PRÉPARATION DU SERVEUR

### 1.1 Connexion SSH

```bash
ssh votre-utilisateur@votre-serveur.com
cd /chemin/vers/votre/projet
```

### 1.2 Vérification des prérequis

```bash
# Vérifier PHP (>= 8.1)
php -v

# Vérifier Composer
composer --version

# Vérifier MySQL
mysql --version

# Vérifier les extensions PHP requises
php -m | grep -E "pdo|mysql|mbstring|xml|curl|openssl|json|tokenizer"
```

---

## ✅ ÉTAPE 2: UPLOAD DES FICHIERS

### 2.1 Transférer les fichiers

```bash
# Option A: Via SCP (depuis votre machine locale)
scp -r /Users/teya2023/Documents/codecayon\ SaaS/restrosaas-37/saas-whatsapp/restro-saas/* \
  utilisateur@serveur:/chemin/vers/projet/

# Option B: Via Git (si configuré)
git pull origin main

# Option C: Via FTP/SFTP
# Utilisez FileZilla ou Cyberduck
```

### 2.2 Transférer le dump KOFFI

```bash
# Depuis votre machine locale
scp database/backups/koffi.sql utilisateur@serveur:/chemin/vers/projet/database/backups/
```

---

## ✅ ÉTAPE 3: CONFIGURATION

### 3.1 Fichier .env

```bash
# Copier le fichier .env
cp .env.example .env

# Éditer avec vos paramètres de production
nano .env
```

**Configuration requise dans .env:**

```env
APP_NAME='Restro SaaS'
APP_ENV=production
APP_DEBUG=false
APP_URL=https://votre-domaine.com

FORCE_HTTPS=true
SESSION_SECURE_COOKIE=true

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=c2687072c_restooo225
DB_USERNAME=c2687072c_paulin225
DB_PASSWORD='7)2GRB~eZ#IiBr.Q'

CACHE_DRIVER=file
SESSION_DRIVER=file
QUEUE_CONNECTION=database
```

### 3.2 Générer la clé d'application

```bash
php artisan key:generate
```

---

## ✅ ÉTAPE 4: INSTALLATION DES DÉPENDANCES

### 4.1 Installer les packages Composer

```bash
composer install --optimize-autoloader --no-dev --quiet
```

### 4.2 Créer les dossiers nécessaires

```bash
mkdir -p storage/framework/cache/data
mkdir -p storage/framework/sessions
mkdir -p storage/framework/views
mkdir -p storage/logs
mkdir -p bootstrap/cache
```

### 4.3 Définir les permissions

```bash
chmod -R 775 storage bootstrap/cache
chown -R www-data:www-data storage bootstrap/cache
```

---

## ✅ ÉTAPE 5: DÉPLOIEMENT DE LA BASE DE DONNÉES

### Option A: Restaurer le dump KOFFI (Recommandé si vous voulez les données existantes)

```bash
# 1. Créer la base de données si elle n'existe pas
mysql -u c2687072c_paulin225 -p'7)2GRB~eZ#IiBr.Q' -e "CREATE DATABASE IF NOT EXISTS c2687072c_restooo225;"

# 2. Restaurer le dump KOFFI
mysql -u c2687072c_paulin225 -p'7)2GRB~eZ#IiBr.Q' c2687072c_restooo225 < database/backups/koffi.sql

# 3. Vérifier
mysql -u c2687072c_paulin225 -p'7)2GRB~eZ#IiBr.Q' c2687072c_restooo225 -e "SHOW TABLES;"
```

### Option B: Migrations fraîches (Pour une installation vierge)

```bash
# Exécuter les migrations
php artisan migrate:fresh --force

# Exécuter les seeders
php artisan db:seed --force
```

---

## ✅ ÉTAPE 6: OPTIMISATIONS LARAVEL

### 6.1 Créer le lien symbolique storage

```bash
php artisan storage:link
```

### 6.2 Cache des configurations

```bash
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

### 6.3 Optimisation de l'autoloader

```bash
composer dump-autoload --optimize --classmap-authoritative
```

---

## ✅ ÉTAPE 7: CONFIGURATION DU SERVEUR WEB

### 7.1 Configuration Nginx

```nginx
server {
    listen 80;
    server_name votre-domaine.com;
    root /chemin/vers/projet/public;

    add_header X-Frame-Options "SAMEORIGIN";
    add_header X-Content-Type-Options "nosniff";

    index index.php;

    charset utf-8;

    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }

    location = /favicon.ico { access_log off; log_not_found off; }
    location = /robots.txt  { access_log off; log_not_found off; }

    error_page 404 /index.php;

    location ~ \.php$ {
        fastcgi_pass unix:/var/run/php/php8.1-fpm.sock;
        fastcgi_param SCRIPT_FILENAME $realpath_root$fastcgi_script_name;
        include fastcgi_params;
    }

    location ~ /\.(?!well-known).* {
        deny all;
    }
}
```

### 7.2 Redémarrer Nginx

```bash
sudo nginx -t
sudo systemctl restart nginx
```

---

## ✅ ÉTAPE 8: SSL/HTTPS (Certbot)

### 8.1 Installer Certbot

```bash
sudo apt-get update
sudo apt-get install certbot python3-certbot-nginx
```

### 8.2 Générer le certificat SSL

```bash
sudo certbot --nginx -d votre-domaine.com -d www.votre-domaine.com
```

---

## ✅ ÉTAPE 9: VÉRIFICATIONS FINALES

### 9.1 Vérifier les logs

```bash
# Logs Laravel
tail -f storage/logs/laravel.log

# Logs Nginx
tail -f /var/log/nginx/error.log
```

### 9.2 Tester l'application

```bash
# Tester la page d'accueil
curl -I https://votre-domaine.com

# Vérifier la connexion base de données
php artisan tinker
>>> DB::connection()->getPdo();
```

### 9.3 Checklist de vérification

- [ ] Le site s'affiche correctement (https://votre-domaine.com)
- [ ] Les CSS/JS se chargent
- [ ] La connexion à la base de données fonctionne
- [ ] Le panel admin est accessible (/admin)
- [ ] Les logs ne montrent pas d'erreurs critiques
- [ ] Le certificat SSL est actif (cadenas vert)
- [ ] Les permissions des dossiers sont correctes

---

## 🔧 DÉPANNAGE

### Erreur "Please provide a valid cache path"

```bash
mkdir -p storage/framework/cache/data storage/framework/sessions storage/framework/views
chmod -R 775 storage bootstrap/cache
```

### Erreur "The stream or file could not be opened"

```bash
chmod -R 775 storage
chown -R www-data:www-data storage
```

### Erreur 500 - Internal Server Error

```bash
# Vérifier les logs
tail -f storage/logs/laravel.log

# Recréer les caches
php artisan cache:clear
php artisan config:clear
php artisan view:clear
```

### CSS/JS ne se chargent pas

```bash
# Recréer le lien symbolique
php artisan storage:link

# Vérifier les permissions
ls -la public/storage
```

### Base de données inaccessible

```bash
# Tester la connexion
mysql -u c2687072c_paulin225 -p'7)2GRB~eZ#IiBr.Q' -h 127.0.0.1 c2687072c_restooo225

# Vérifier .env
cat .env | grep DB_
```

---

## 📊 MAINTENANCE

### Sauvegardes automatiques

```bash
# Créer un script de sauvegarde quotidien
cat > /root/backup-db.sh << 'SCRIPT'
#!/bin/bash
DATE=$(date +%Y%m%d_%H%M%S)
mysqldump -u c2687072c_paulin225 -p'7)2GRB~eZ#IiBr.Q' c2687072c_restooo225 | gzip > /backups/koffi_$DATE.sql.gz
find /backups -name "koffi_*.sql.gz" -mtime +7 -delete
SCRIPT

chmod +x /root/backup-db.sh

# Ajouter au crontab (tous les jours à 2h du matin)
echo "0 2 * * * /root/backup-db.sh" | crontab -
```

### Mise à jour de l'application

```bash
# 1. Sauvegarder la base
mysqldump -u c2687072c_paulin225 -p'7)2GRB~eZ#IiBr.Q' c2687072c_restooo225 > backup_avant_maj.sql

# 2. Mettre à jour le code
git pull origin main

# 3. Mettre à jour les dépendances
composer install --optimize-autoloader --no-dev

# 4. Exécuter les migrations
php artisan migrate --force

# 5. Optimiser
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

---

## 🎯 RÉSUMÉ DES COMMANDES

```bash
# DÉPLOIEMENT COMPLET EN UNE FOIS
cd /chemin/vers/projet
composer install --optimize-autoloader --no-dev --quiet
mkdir -p storage/framework/{cache/data,sessions,views} storage/logs bootstrap/cache
chmod -R 775 storage bootstrap/cache
php artisan storage:link
mysql -u c2687072c_paulin225 -p'7)2GRB~eZ#IiBr.Q' c2687072c_restooo225 < database/backups/koffi.sql
php artisan config:cache
php artisan route:cache
php artisan view:cache
composer dump-autoload --optimize
```

---

## 📞 SUPPORT

- **Documentation**: Consultez PROJECT_STRUCTURE.md
- **Logs**: storage/logs/laravel.log
- **Base de données**: koffi.sql (backup de référence)

---

## ✅ STATUT DU DÉPLOIEMENT

- [x] Migrations corrigées (0 erreurs)
- [x] Dump KOFFI créé (186 KB, 115 tables)
- [x] Guide de déploiement complet
- [ ] Déploiement sur serveur de production
- [ ] Tests fonctionnels en production

**Tout est prêt pour le déploiement en production!** 🚀
