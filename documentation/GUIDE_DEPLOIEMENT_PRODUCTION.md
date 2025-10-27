# 🚀 GUIDE DE DÉPLOIEMENT PRODUCTION - RestroSaaS

**Date:** 24 octobre 2025  
**Version:** Production 1.0  
**Application:** RestroSaaS Beta

---

## ✅ CHANGEMENTS APPLIQUÉS POUR LA PRODUCTION

### 1. Configuration Environnement (.env)

```bash
APP_ENV=production          # ✅ Mode production activé
APP_DEBUG=false             # ✅ Debug désactivé (sécurité)
APP_URL=https://votre-domaine.com  # ⚠️ À CONFIGURER avec votre domaine

# Sécurité
FORCE_HTTPS=true           # ✅ Force HTTPS
SESSION_SECURE_COOKIE=true # ✅ Cookies sécurisés
LOG_LEVEL=warning          # ✅ Logs optimisés

# Performance
CACHE_DRIVER=redis         # ✅ Redis pour cache
SESSION_DRIVER=redis       # ✅ Redis pour sessions
QUEUE_CONNECTION=redis     # ✅ Redis pour queues
```

### 2. Service Worker PWA

```bash
✅ Service Worker réactivé (public/sw.js)
✅ Content-Security-Policy active (HTTPS forcé)
✅ PWA fonctionnel pour mode offline
```

### 3. Optimisations Laravel

```bash
✅ php artisan config:cache   # Configuration en cache
✅ php artisan route:cache    # Routes en cache
✅ php artisan view:cache     # Vues compilées en cache
```

---

## 🔧 ÉTAPES DE DÉPLOIEMENT

### ÉTAPE 1: Configuration du Serveur

#### Prérequis Serveur

```bash
# Système
- Ubuntu 20.04+ / Debian 11+ / CentOS 8+
- PHP 8.1+ (testé avec 8.4.8)
- MySQL 8.0+ ou MariaDB 10.5+
- Redis 6.0+
- Nginx ou Apache
- Composer 2.x
- Node.js 18+ & NPM

# Extensions PHP requises
sudo apt-get install php8.1-fpm php8.1-mysql php8.1-mbstring \
  php8.1-xml php8.1-bcmath php8.1-curl php8.1-zip php8.1-redis \
  php8.1-gd php8.1-intl php8.1-soap
```

#### Redis Installation

```bash
# Ubuntu/Debian
sudo apt-get install redis-server
sudo systemctl enable redis-server
sudo systemctl start redis-server

# Vérifier
redis-cli ping  # Doit retourner "PONG"
```

### ÉTAPE 2: Upload des Fichiers

```bash
# Option 1: Upload via FTP/SFTP
# Uploadez l'archive restro-saas-beta.zip sur votre serveur

# Option 2: Git Clone
git clone https://github.com/votre-repo/restro-saas.git
cd restro-saas

# Extraction (si archive)
unzip restro-saas-beta.zip -d /var/www/restro-saas
cd /var/www/restro-saas
```

### ÉTAPE 3: Installation des Dépendances

```bash
# Dépendances PHP (SANS dev)
composer install --optimize-autoloader --no-dev

# Dépendances Node.js
npm install

# Compiler les assets pour production
npm run build
```

### ÉTAPE 4: Configuration .env

```bash
# Copier le template
cp .env.example .env

# Éditer .env avec vos valeurs
nano .env
```

#### Variables CRITIQUES à Configurer:

```bash
# APPLICATION
APP_NAME='Votre Nom SaaS'
APP_ENV=production
APP_DEBUG=false
APP_URL=https://votre-domaine.com

# BASE DE DONNÉES
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=restro_saas_prod
DB_USERNAME=restro_user
DB_PASSWORD=VOTRE_MOT_DE_PASSE_FORT

# REDIS (vérifier les credentials)
REDIS_HOST=127.0.0.1
REDIS_PASSWORD=null
REDIS_PORT=6379

# MAIL (configurer votre service mail)
MAIL_MAILER=smtp
MAIL_HOST=smtp.mailtrap.io
MAIL_PORT=2525
MAIL_USERNAME=votre_username
MAIL_PASSWORD=votre_password
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS=noreply@votre-domaine.com
MAIL_FROM_NAME="${APP_NAME}"

# WHATSAPP BUSINESS (si activé)
WHATSAPP_ENABLED=true
WHATSAPP_DEMO_MODE=false
WHATSAPP_API_TOKEN=votre_token_meta
WHATSAPP_PHONE_NUMBER_ID=votre_phone_id
WHATSAPP_BUSINESS_ACCOUNT_ID=votre_account_id
```

### ÉTAPE 5: Génération de Clés & Sécurité

```bash
# Générer une nouvelle clé d'application (IMPORTANT!)
php artisan key:generate

# Créer le lien symbolique storage
php artisan storage:link

# Permissions
sudo chown -R www-data:www-data storage bootstrap/cache
sudo chmod -R 775 storage bootstrap/cache
```

### ÉTAPE 6: Base de Données

```bash
# Créer la base de données
mysql -u root -p
CREATE DATABASE restro_saas_prod CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
CREATE USER 'restro_user'@'localhost' IDENTIFIED BY 'VOTRE_MOT_DE_PASSE_FORT';
GRANT ALL PRIVILEGES ON restro_saas_prod.* TO 'restro_user'@'localhost';
FLUSH PRIVILEGES;
EXIT;

# Exécuter les migrations
php artisan migrate --force

# (Optionnel) Seed des données de base
php artisan db:seed
```

### ÉTAPE 7: Configuration Nginx

```nginx
# /etc/nginx/sites-available/restro-saas

server {
    listen 80;
    server_name votre-domaine.com www.votre-domaine.com;
    return 301 https://$server_name$request_uri;
}

server {
    listen 443 ssl http2;
    server_name votre-domaine.com www.votre-domaine.com;
    root /var/www/restro-saas/public;

    index index.php index.html index.htm;

    # SSL Configuration (Let's Encrypt)
    ssl_certificate /etc/letsencrypt/live/votre-domaine.com/fullchain.pem;
    ssl_certificate_key /etc/letsencrypt/live/votre-domaine.com/privkey.pem;
    ssl_protocols TLSv1.2 TLSv1.3;
    ssl_ciphers HIGH:!aNULL:!MD5;

    # Security Headers
    add_header X-Frame-Options "SAMEORIGIN" always;
    add_header X-Content-Type-Options "nosniff" always;
    add_header X-XSS-Protection "1; mode=block" always;
    add_header Referrer-Policy "no-referrer-when-downgrade" always;
    add_header Content-Security-Policy "upgrade-insecure-requests" always;

    # Logging
    access_log /var/log/nginx/restro-saas-access.log;
    error_log /var/log/nginx/restro-saas-error.log;

    # Gzip Compression
    gzip on;
    gzip_vary on;
    gzip_min_length 1000;
    gzip_types text/plain text/css text/xml text/javascript application/json application/javascript application/xml+rss;

    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }

    location ~ \.php$ {
        include snippets/fastcgi-php.conf;
        fastcgi_pass unix:/var/run/php/php8.1-fpm.sock;
        fastcgi_param SCRIPT_FILENAME $realpath_root$fastcgi_script_name;
        include fastcgi_params;
    }

    location ~ /\.(?!well-known).* {
        deny all;
    }

    # Cache static assets
    location ~* \.(jpg|jpeg|png|gif|ico|css|js|svg|woff|woff2|ttf|eot)$ {
        expires 1y;
        add_header Cache-Control "public, immutable";
    }
}
```

```bash
# Activer le site
sudo ln -s /etc/nginx/sites-available/restro-saas /etc/nginx/sites-enabled/
sudo nginx -t
sudo systemctl reload nginx
```

### ÉTAPE 8: SSL avec Let's Encrypt

```bash
# Installer Certbot
sudo apt-get install certbot python3-certbot-nginx

# Obtenir un certificat SSL
sudo certbot --nginx -d votre-domaine.com -d www.votre-domaine.com

# Auto-renouvellement (vérifie)
sudo certbot renew --dry-run
```

### ÉTAPE 9: Optimisations Production

```bash
# Cache des configurations
php artisan config:cache
php artisan route:cache
php artisan view:cache

# Optimiser l'autoloader
composer dump-autoload --optimize

# (Optionnel) OPcache pour PHP
# Déjà configuré si extension installée
```

### ÉTAPE 10: Configuration Queue Worker (Redis)

```bash
# Créer un service systemd
sudo nano /etc/systemd/system/restro-queue.service
```

```ini
[Unit]
Description=RestroSaaS Queue Worker
After=network.target redis-server.service

[Service]
Type=simple
User=www-data
Group=www-data
Restart=always
RestartSec=3
ExecStart=/usr/bin/php /var/www/restro-saas/artisan queue:work redis --sleep=3 --tries=3 --max-time=3600

[Install]
WantedBy=multi-user.target
```

```bash
# Activer le service
sudo systemctl daemon-reload
sudo systemctl enable restro-queue
sudo systemctl start restro-queue
sudo systemctl status restro-queue
```

### ÉTAPE 11: Configuration Cron (Scheduler)

```bash
# Éditer crontab
sudo crontab -e -u www-data

# Ajouter cette ligne
* * * * * cd /var/www/restro-saas && php artisan schedule:run >> /dev/null 2>&1
```

### ÉTAPE 12: Monitoring & Logs

```bash
# Voir les logs Laravel
tail -f storage/logs/laravel.log

# Voir les logs Nginx
tail -f /var/log/nginx/restro-saas-error.log

# Voir le statut des queues
php artisan queue:monitor

# Vider les logs (si trop gros)
php artisan log:clear
```

---

## 🔐 CHECKLIST SÉCURITÉ PRODUCTION

### Avant Mise en Ligne

- [ ] `APP_ENV=production` dans .env
- [ ] `APP_DEBUG=false` dans .env
- [ ] `APP_KEY` généré et unique
- [ ] Base de données avec mot de passe fort
- [ ] Fichier `.env` avec permissions 600
- [ ] Dossier `.git/` supprimé ou inaccessible
- [ ] SSL/HTTPS configuré et actif
- [ ] Firewall configuré (UFW/iptables)
- [ ] Permissions fichiers correctes (775 storage, 644 fichiers)
- [ ] Redis protégé (mot de passe ou localhost only)
- [ ] Backup automatique configuré
- [ ] Logs rotatifs configurés
- [ ] Monitoring activé (Uptime Robot, etc.)

### Configuration Firewall (UFW)

```bash
sudo ufw allow 22/tcp    # SSH
sudo ufw allow 80/tcp    # HTTP
sudo ufw allow 443/tcp   # HTTPS
sudo ufw enable
sudo ufw status
```

---

## 📊 TESTS POST-DÉPLOIEMENT

### Test 1: Vérifier l'accès HTTPS

```bash
curl -I https://votre-domaine.com
# Doit retourner: HTTP/2 200
```

### Test 2: Vérifier les assets

```bash
# CSS
curl -I https://votre-domaine.com/storage/landing/css/bootstrap.min.css
# Doit retourner: 200

# JS
curl -I https://votre-domaine.com/storage/landing/js/jquery.min.js
# Doit retourner: 200
```

### Test 3: Vérifier la base de données

```bash
php artisan migrate:status
# Toutes les migrations doivent être "Ran"
```

### Test 4: Vérifier Redis

```bash
php artisan tinker
>>> Cache::put('test', 'production', 60);
>>> Cache::get('test');
# Doit retourner: "production"
```

### Test 5: Vérifier les queues

```bash
php artisan queue:work --once
# Doit exécuter sans erreur
```

### Test 6: Test complet automatisé

```bash
./scripts/test-instance-complete.sh
# Doit retourner: 100% de réussite
```

---

## 🔄 MISES À JOUR FUTURES

### Procédure de Mise à Jour

```bash
# 1. Backup
php artisan backup:run

# 2. Mode maintenance
php artisan down --message="Mise à jour en cours..." --retry=60

# 3. Pull des nouveaux fichiers
git pull origin main
# Ou upload nouvelle archive

# 4. Dépendances
composer install --no-dev --optimize-autoloader
npm install
npm run build

# 5. Migrations
php artisan migrate --force

# 6. Clear & recache
php artisan config:clear
php artisan cache:clear
php artisan view:clear
php artisan config:cache
php artisan route:cache
php artisan view:cache

# 7. Restart services
sudo systemctl restart restro-queue
sudo systemctl reload php8.1-fpm
sudo systemctl reload nginx

# 8. Mode production
php artisan up
```

---

## 💾 BACKUP AUTOMATIQUE

### Script Backup Quotidien

```bash
# /var/scripts/backup-restro.sh
#!/bin/bash

DATE=$(date +%Y%m%d_%H%M%S)
BACKUP_DIR="/var/backups/restro-saas"
mkdir -p $BACKUP_DIR

# Backup base de données
mysqldump -u restro_user -p'VOTRE_PASSWORD' restro_saas_prod | gzip > $BACKUP_DIR/db_$DATE.sql.gz

# Backup storage
tar -czf $BACKUP_DIR/storage_$DATE.tar.gz /var/www/restro-saas/storage

# Backup .env
cp /var/www/restro-saas/.env $BACKUP_DIR/env_$DATE

# Nettoyer les backups > 7 jours
find $BACKUP_DIR -name "*.gz" -mtime +7 -delete
find $BACKUP_DIR -name "env_*" -mtime +7 -delete

echo "Backup terminé: $DATE"
```

```bash
# Rendre exécutable
chmod +x /var/scripts/backup-restro.sh

# Cron quotidien à 2h du matin
sudo crontab -e
0 2 * * * /var/scripts/backup-restro.sh >> /var/log/restro-backup.log 2>&1
```

---

## 📈 MONITORING RECOMMANDÉ

### Services Recommandés

1. **Uptime Monitoring**
   - UptimeRobot (gratuit)
   - Pingdom
   - StatusCake

2. **Error Tracking**
   - Sentry (Laravel integration)
   - Bugsnag
   - Rollbar

3. **Performance**
   - New Relic
   - Scout APM
   - Blackfire.io

4. **Logs Centralisés**
   - Papertrail
   - Loggly
   - ELK Stack

---

## ✅ RÉSUMÉ DES CHANGEMENTS

### Fichiers Modifiés

1. `.env`
   - APP_ENV: local → production
   - APP_DEBUG: true → false
   - CACHE_DRIVER: file → redis
   - SESSION_DRIVER: file → redis
   - QUEUE_CONNECTION: sync → redis

2. `public/sw.js`
   - Réactivé (sw.js.disabled → sw.js)

3. Caches Laravel
   - config:cache
   - route:cache
   - view:cache

### Sécurité Activée

- ✅ HTTPS forcé
- ✅ Session cookies sécurisés
- ✅ Content-Security-Policy active
- ✅ Debug mode désactivé
- ✅ Logs optimisés (warning level)

---

## 🆘 SUPPORT

En cas de problème:

1. Vérifier les logs: `tail -f storage/logs/laravel.log`
2. Consulter: `documentation/GUIDE_DEPANNAGE.md`
3. Tester: `./scripts/test-instance-complete.sh`
4. Vérifier services: `sudo systemctl status nginx php8.1-fpm redis-server`

---

**🎉 Votre application RestroSaaS est maintenant en PRODUCTION!**

Dernière mise à jour: 24 octobre 2025
