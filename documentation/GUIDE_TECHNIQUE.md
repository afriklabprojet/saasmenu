# ⚙️ Guide Technique RestroSaaS
### Installation, Configuration et Maintenance

---

## 🚀 **Installation Production**

### **Prérequis Système**
```
🖥️ SERVEUR
├── OS: Ubuntu 20.04+ LTS ou CentOS 8+
├── CPU: 4 cores minimum (8 cores recommandé)
├── RAM: 8GB minimum (16GB recommandé)
├── Stockage: 100GB SSD minimum
└── Bande passante: 100Mbps minimum

🗄️ BASE DE DONNÉES
├── MySQL 8.0+ ou MariaDB 10.6+
├── Storage: 50GB minimum
├── Backup: Quotidien automatique
└── Réplication: Master-Slave recommandé

🌐 WEB SERVER
├── Nginx 1.18+ (recommandé)
├── Apache 2.4+ (alternatif)
├── SSL: Let's Encrypt ou certificat commercial
└── CDN: CloudFlare recommandé
```

### **Installation PHP et Extensions**
```bash
# Ubuntu/Debian
sudo apt update && sudo apt upgrade -y
sudo apt install software-properties-common
sudo add-apt-repository ppa:ondrej/php
sudo apt update

# Installation PHP 8.1
sudo apt install php8.1-fpm php8.1-cli php8.1-mysql php8.1-redis \
    php8.1-json php8.1-mbstring php8.1-xml php8.1-bcmath \
    php8.1-curl php8.1-gd php8.1-zip php8.1-intl php8.1-soap

# Vérification
php -v
php -m | grep -E "(mysql|redis|gd|curl)"
```

### **Configuration Nginx**
```nginx
# /etc/nginx/sites-available/restro-saas.conf
server {
    listen 80;
    server_name votre-domaine.com www.votre-domaine.com;
    return 301 https://$server_name$request_uri;
}

server {
    listen 443 ssl http2;
    server_name votre-domaine.com www.votre-domaine.com;
    root /var/www/restro-saas/public;
    index index.php index.html;

    # SSL Configuration
    ssl_certificate /etc/letsencrypt/live/votre-domaine.com/fullchain.pem;
    ssl_certificate_key /etc/letsencrypt/live/votre-domaine.com/privkey.pem;
    ssl_protocols TLSv1.2 TLSv1.3;
    ssl_ciphers ECDHE-RSA-AES256-GCM-SHA512:DHE-RSA-AES256-GCM-SHA512;

    # Security Headers
    add_header X-Frame-Options "SAMEORIGIN" always;
    add_header X-XSS-Protection "1; mode=block" always;
    add_header X-Content-Type-Options "nosniff" always;
    add_header Strict-Transport-Security "max-age=31536000; includeSubDomains" always;

    # Gzip Compression
    gzip on;
    gzip_vary on;
    gzip_types text/plain text/css application/json application/javascript;

    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }

    location ~ \.php$ {
        fastcgi_pass unix:/var/run/php/php8.1-fpm.sock;
        fastcgi_index index.php;
        fastcgi_param SCRIPT_FILENAME $realpath_root$fastcgi_script_name;
        include fastcgi_params;
    }

    location ~ /\.(?!well-known).* {
        deny all;
    }
}
```

---

## 🔧 **Configuration Laravel**

### **Variables d'Environnement Production**
```env
# Application
APP_NAME="RestroSaaS"
APP_ENV=production
APP_KEY=base64:VOTRE_CLE_32_CARACTERES
APP_DEBUG=false
APP_URL=https://votre-domaine.com

# Base de données
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=restro_saas_prod
DB_USERNAME=restro_user
DB_PASSWORD=MOT_DE_PASSE_SECURISE

# Cache et Session
CACHE_DRIVER=redis
SESSION_DRIVER=redis
QUEUE_CONNECTION=redis

# Redis
REDIS_HOST=127.0.0.1
REDIS_PASSWORD=MOT_DE_PASSE_REDIS
REDIS_PORT=6379

# Mail
MAIL_MAILER=smtp
MAIL_HOST=smtp.gmail.com
MAIL_PORT=587
MAIL_USERNAME=noreply@votre-domaine.com
MAIL_PASSWORD=MOT_DE_PASSE_EMAIL
MAIL_ENCRYPTION=tls

# Paiements
STRIPE_KEY=pk_live_VOTRE_CLE_PUBLIQUE
STRIPE_SECRET=sk_live_VOTRE_CLE_SECRETE
PAYPAL_CLIENT_ID=VOTRE_PAYPAL_CLIENT_ID
PAYPAL_CLIENT_SECRET=VOTRE_PAYPAL_SECRET

# Stockage
FILESYSTEM_DRIVER=public
AWS_ACCESS_KEY_ID=VOTRE_AWS_KEY
AWS_SECRET_ACCESS_KEY=VOTRE_AWS_SECRET
AWS_DEFAULT_REGION=eu-west-1
AWS_BUCKET=restro-saas-assets

# Monitoring
LOG_CHANNEL=stack
LOG_LEVEL=error
SENTRY_LARAVEL_DSN=https://votre-sentry-dsn
```

### **Optimisations Performance**
```bash
# Cache des configurations
php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan event:cache

# Optimisation Composer
composer install --optimize-autoloader --no-dev

# Permissions
chown -R www-data:www-data /var/www/restro-saas
chmod -R 755 /var/www/restro-saas
chmod -R 775 /var/www/restro-saas/storage
chmod -R 775 /var/www/restro-saas/bootstrap/cache
```

---

## 🔒 **Sécurité Production**

### **Firewall et Accès**
```bash
# UFW Configuration
sudo ufw default deny incoming
sudo ufw default allow outgoing
sudo ufw allow ssh
sudo ufw allow 'Nginx Full'
sudo ufw allow 3306  # MySQL (si externe)
sudo ufw enable

# Fail2Ban pour protection SSH
sudo apt install fail2ban
sudo systemctl enable fail2ban
sudo systemctl start fail2ban
```

### **SSL et Certificats**
```bash
# Installation Certbot
sudo apt install certbot python3-certbot-nginx

# Génération certificat Let's Encrypt
sudo certbot --nginx -d votre-domaine.com -d www.votre-domaine.com

# Renouvellement automatique
sudo crontab -e
# Ajouter: 0 12 * * * /usr/bin/certbot renew --quiet
```

### **Sécurisation MySQL**
```bash
# Script de sécurisation
sudo mysql_secure_installation

# Configuration utilisateur dédié
mysql -u root -p
CREATE DATABASE restro_saas_prod CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
CREATE USER 'restro_user'@'localhost' IDENTIFIED BY 'MOT_DE_PASSE_SECURISE';
GRANT ALL PRIVILEGES ON restro_saas_prod.* TO 'restro_user'@'localhost';
FLUSH PRIVILEGES;
```

---

## 📊 **Monitoring et Logs**

### **Configuration Logging**
```php
// config/logging.php
'channels' => [
    'stack' => [
        'driver' => 'stack',
        'channels' => ['single', 'slack'],
        'ignore_exceptions' => false,
    ],
    
    'performance' => [
        'driver' => 'daily',
        'path' => storage_path('logs/performance.log'),
        'level' => 'info',
        'days' => 30,
    ],
    
    'security' => [
        'driver' => 'daily',
        'path' => storage_path('logs/security.log'),
        'level' => 'warning',
        'days' => 90,
    ],
    
    'payments' => [
        'driver' => 'daily',
        'path' => storage_path('logs/payments.log'),
        'level' => 'info',
        'days' => 365,
    ],
]
```

### **Monitoring Système**
```bash
# Installation Netdata
bash <(curl -Ss https://my-netdata.io/kickstart.sh)

# Configuration
sudo systemctl enable netdata
sudo systemctl start netdata

# Accès: http://votre-ip:19999
```

### **Alertes Automatiques**
```bash
# Script de monitoring personnalisé
#!/bin/bash
# /usr/local/bin/restro-monitor.sh

# Vérification espace disque
DISK_USAGE=$(df / | tail -1 | awk '{print $5}' | sed 's/%//')
if [ $DISK_USAGE -gt 80 ]; then
    echo "ALERTE: Espace disque > 80%" | mail -s "RestroSaaS Alert" admin@votre-domaine.com
fi

# Vérification MySQL
mysqladmin ping -h localhost -u restro_user -p$DB_PASSWORD > /dev/null 2>&1
if [ $? -ne 0 ]; then
    echo "ALERTE: MySQL inaccessible" | mail -s "RestroSaaS Alert" admin@votre-domaine.com
fi

# Vérification Redis
redis-cli ping > /dev/null 2>&1
if [ $? -ne 0 ]; then
    echo "ALERTE: Redis inaccessible" | mail -s "RestroSaaS Alert" admin@votre-domaine.com
fi

# Crontab: */5 * * * * /usr/local/bin/restro-monitor.sh
```

---

## 💾 **Backup et Restauration**

### **Script Backup Automatique**
```bash
#!/bin/bash
# /usr/local/bin/backup-restro.sh

DATE=$(date +"%Y-%m-%d_%H-%M-%S")
BACKUP_DIR="/var/backups/restro-saas"
DB_NAME="restro_saas_prod"
DB_USER="restro_user"
DB_PASS="MOT_DE_PASSE"

# Création répertoire
mkdir -p $BACKUP_DIR/$DATE

# Backup base de données
mysqldump -u $DB_USER -p$DB_PASS $DB_NAME | gzip > $BACKUP_DIR/$DATE/database.sql.gz

# Backup fichiers
tar -czf $BACKUP_DIR/$DATE/files.tar.gz /var/www/restro-saas \
    --exclude="/var/www/restro-saas/storage/logs" \
    --exclude="/var/www/restro-saas/node_modules"

# Backup .env
cp /var/www/restro-saas/.env $BACKUP_DIR/$DATE/

# Vérification intégrité
md5sum $BACKUP_DIR/$DATE/* > $BACKUP_DIR/$DATE/checksums.md5

# Nettoyage anciens backups (garde 30 jours)
find $BACKUP_DIR -type d -mtime +30 -exec rm -rf {} \;

# Notification succès
echo "Backup terminé: $BACKUP_DIR/$DATE" | mail -s "Backup RestroSaaS OK" admin@votre-domaine.com
```

### **Script Restauration**
```bash
#!/bin/bash
# /usr/local/bin/restore-restro.sh

BACKUP_DATE=$1
BACKUP_DIR="/var/backups/restro-saas/$BACKUP_DATE"

if [ ! -d "$BACKUP_DIR" ]; then
    echo "Backup introuvable: $BACKUP_DIR"
    exit 1
fi

# Vérification intégrité
cd $BACKUP_DIR
md5sum -c checksums.md5
if [ $? -ne 0 ]; then
    echo "Erreur intégrité backup"
    exit 1
fi

# Restauration base de données
echo "Restauration base de données..."
gunzip < database.sql.gz | mysql -u $DB_USER -p$DB_PASS $DB_NAME

# Restauration fichiers
echo "Restauration fichiers..."
tar -xzf files.tar.gz -C /

# Restauration .env
cp .env /var/www/restro-saas/

# Permissions
chown -R www-data:www-data /var/www/restro-saas
chmod -R 755 /var/www/restro-saas
chmod -R 775 /var/www/restro-saas/storage

echo "Restauration terminée avec succès"
```

---

## ⚡ **Optimisation Performance**

### **Configuration PHP-FPM**
```ini
; /etc/php/8.1/fpm/pool.d/restro-saas.conf
[restro-saas]
user = www-data
group = www-data
listen = /var/run/php/php8.1-fpm-restro.sock
listen.owner = www-data
listen.group = www-data

pm = dynamic
pm.max_children = 50
pm.start_servers = 5
pm.min_spare_servers = 5
pm.max_spare_servers = 35
pm.max_requests = 1000

php_admin_value[memory_limit] = 256M
php_admin_value[upload_max_filesize] = 10M
php_admin_value[post_max_size] = 10M
php_admin_value[max_execution_time] = 60
```

### **Configuration MySQL**
```ini
# /etc/mysql/mysql.conf.d/restro-optimized.cnf
[mysqld]
innodb_buffer_pool_size = 2G
innodb_log_file_size = 256M
innodb_flush_log_at_trx_commit = 2
innodb_file_per_table = 1

query_cache_type = 1
query_cache_size = 256M
query_cache_limit = 2M

max_connections = 200
thread_cache_size = 50
table_open_cache = 2000

slow_query_log = 1
slow_query_log_file = /var/log/mysql/slow.log
long_query_time = 2
```

### **Configuration Redis**
```ini
# /etc/redis/redis.conf
maxmemory 1gb
maxmemory-policy allkeys-lru
save 900 1
save 300 10
save 60 10000

tcp-keepalive 300
timeout 0
tcp-backlog 511
```

---

## 🚀 **Déploiement et CI/CD**

### **Script Déploiement**
```bash
#!/bin/bash
# deploy.sh

set -e

echo "🚀 Déploiement RestroSaaS"

# Variables
REPO_URL="https://github.com/votre-repo/restro-saas.git"
DEPLOY_PATH="/var/www/restro-saas"
BACKUP_PATH="/var/backups/deploy-$(date +%Y%m%d-%H%M%S)"

# Backup avant déploiement
echo "📦 Création backup..."
cp -r $DEPLOY_PATH $BACKUP_PATH

# Git pull
echo "📥 Récupération code..."
cd $DEPLOY_PATH
git pull origin main

# Dépendances
echo "📚 Installation dépendances..."
composer install --no-dev --optimize-autoloader

# Migration base de données
echo "🗄️ Migration base..."
php artisan migrate --force

# Cache refresh
echo "⚡ Refresh cache..."
php artisan config:cache
php artisan route:cache
php artisan view:cache

# Assets
echo "🎨 Compilation assets..."
npm ci
npm run production

# Permissions
echo "🔐 Permissions..."
chown -R www-data:www-data $DEPLOY_PATH
chmod -R 755 $DEPLOY_PATH
chmod -R 775 $DEPLOY_PATH/storage

# Redémarrage services
echo "🔄 Redémarrage services..."
sudo systemctl reload nginx
sudo systemctl reload php8.1-fpm

# Test santé
echo "🏥 Test santé..."
curl -f http://localhost/health-check || exit 1

echo "✅ Déploiement terminé avec succès"
```

### **GitHub Actions CI/CD**
```yaml
# .github/workflows/deploy.yml
name: Deploy RestroSaaS

on:
  push:
    branches: [ main ]

jobs:
  deploy:
    runs-on: ubuntu-latest
    
    steps:
    - uses: actions/checkout@v3
    
    - name: Setup PHP
      uses: shivammathur/setup-php@v2
      with:
        php-version: '8.1'
        
    - name: Install dependencies
      run: composer install --no-dev --optimize-autoloader
      
    - name: Run tests
      run: php artisan test
      
    - name: Deploy to production
      uses: appleboy/ssh-action@v0.1.5
      with:
        host: ${{ secrets.HOST }}
        username: ${{ secrets.USERNAME }}
        key: ${{ secrets.SSH_KEY }}
        script: |
          cd /var/www/restro-saas
          ./deploy.sh
```

---

## 🔧 **Maintenance et Troubleshooting**

### **Commandes Maintenance Quotidienne**
```bash
# Script maintenance quotidienne
#!/bin/bash
# /usr/local/bin/daily-maintenance.sh

# Nettoyage logs
find /var/www/restro-saas/storage/logs -name "*.log" -mtime +30 -delete

# Nettoyage cache Laravel
php artisan cache:clear
php artisan config:cache
php artisan route:cache

# Optimisation MySQL
mysql -u root -p -e "OPTIMIZE TABLE restro_saas_prod.*;"

# Backup quotidien
/usr/local/bin/backup-restro.sh

# Monitoring santé
php artisan system:monitor
php artisan performance:test --type=basic

echo "Maintenance quotidienne terminée"
```

### **Diagnostics Problèmes Fréquents**
```bash
# Check status services
sudo systemctl status nginx php8.1-fpm mysql redis

# Check disk space
df -h
du -sh /var/www/restro-saas/storage/logs/*

# Check MySQL slow queries
mysql -u root -p -e "SELECT * FROM mysql.slow_log ORDER BY start_time DESC LIMIT 10;"

# Check PHP errors
tail -f /var/log/php8.1-fpm.log

# Check application logs
tail -f /var/www/restro-saas/storage/logs/laravel.log

# Performance test
php artisan performance:test --type=basic
```

### **Contacts Support Technique**
```
🆘 URGENCE PRODUCTION: +33 X XX XX XX XX
💬 Support technique: tech-support@restro-saas.com
📖 Documentation: https://docs.restro-saas.com
🐛 Bug reports: https://github.com/restro-saas/issues
💬 Slack communauté: restro-saas.slack.com
```

---

*⚙️ Configuration technique optimisée pour RestroSaaS ! 🚀*

**Version:** 2.0 | **Dernière mise à jour:** Octobre 2025
