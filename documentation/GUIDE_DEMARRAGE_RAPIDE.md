# 🚀 GUIDE DE DÉMARRAGE RAPIDE - E-MENU WHATSAPP SAAS
## Configuration et Mise en Production

---

## 📋 Vue d'ensemble

**E-menu WhatsApp SaaS** est une plateforme complète de menu numérique pour restaurants avec:
- 📱 Notifications WhatsApp automatiques
- 💳 Paiements CinetPay (Orange/MTN/Moov Money)
- 🍽️ Gestion de commandes en temps réel
- 🌍 Interface multilingue (FR/EN)
- 📊 Tableaux de bord analytics

---

## ⚡ Installation Rapide (5 minutes)

### Prérequis
```bash
✅ PHP 8.1+
✅ MySQL 5.7+ / MariaDB 10.3+
✅ Composer
✅ Node.js 16+
✅ Serveur Web (Apache/Nginx)
```

### Installation
```bash
# 1. Cloner le projet
cd /var/www/
git clone [votre-repo] emenu
cd emenu

# 2. Installer les dépendances
composer install --optimize-autoloader --no-dev
npm install && npm run build

# 3. Configuration
cp .env.example .env
php artisan key:generate

# 4. Base de données
nano .env  # Configurer DB_*
php artisan migrate --seed

# 5. Permissions
chmod -R 775 storage bootstrap/cache
chown -R www-data:www-data storage bootstrap/cache

# 6. Optimisations
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

---

## 👤 Compte Administrateur

### Identifiants par défaut
```
📧 Email: admin@emenu.com
🔑 Mot de passe: admin123
🎨 Branding: WhatsApp (Vert #25D366)
💰 Devise: XOF (CFA)
🌍 Langue: Français
```

### Créer/Modifier l'admin
```bash
# Commande interactive
php artisan admin:setup

# Avec options
php artisan admin:setup \
  --email=votre@email.com \
  --password=votreMotDePasse \
  --name="Votre Nom"
```

### Ou via seeder
```bash
php artisan db:seed --class=AdminSeeder --force
```

---

## 🔧 Configuration Essentielle

### 1. Variables .env principales

```env
# Application
APP_NAME="E-menu WhatsApp SaaS"
APP_ENV=production
APP_DEBUG=false
APP_URL=https://votre-domaine.com

# Base de données
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=emenu_db
DB_USERNAME=emenu_user
DB_PASSWORD=votre_mot_de_passe_securise

# Mail (pour notifications)
MAIL_MAILER=smtp
MAIL_HOST=smtp.gmail.com
MAIL_PORT=587
MAIL_USERNAME=votre@email.com
MAIL_PASSWORD=votre_mot_de_passe_app
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS=noreply@emenu.com
MAIL_FROM_NAME="E-menu"
```

### 2. WhatsApp Business API

**📱 Guide complet:** `WHATSAPP_CONFIGURATION.md`

```env
WHATSAPP_ENABLED=true
WHATSAPP_API_URL=https://graph.facebook.com/v18.0
WHATSAPP_PHONE_NUMBER_ID=your_phone_number_id
WHATSAPP_ACCESS_TOKEN=your_access_token
WHATSAPP_FROM_NUMBER=22500000000

# Meta App
META_APP_ID=your_app_id
META_APP_SECRET=your_app_secret
```

**Obtenir les clés:**
1. https://developers.facebook.com/
2. Créer App > Ajouter WhatsApp
3. Copier Phone Number ID et Access Token

### 3. CinetPay Paiements

**💳 Guide complet:** `CINETPAY_CONFIGURATION.md`

```env
CINETPAY_ENABLED=true
CINETPAY_API_KEY=your_api_key
CINETPAY_SITE_ID=your_site_id
CINETPAY_SECRET_KEY=your_secret_key
CINETPAY_MODE=PRODUCTION  # ou TEST
CINETPAY_CURRENCY=XOF
CINETPAY_CHANNELS=ORANGE_MONEY_CI,MTN_CI,MOOV_CI,CARD
```

**Obtenir les clés:**
1. https://merchant.cinetpay.com
2. S'inscrire et vérifier KYC
3. Paramètres > API > Copier les clés

---

## 🌐 Configuration Serveur

### Nginx (Recommandé)

```nginx
server {
    listen 80;
    listen [::]:80;
    server_name votre-domaine.com;
    
    # Redirection HTTPS
    return 301 https://$server_name$request_uri;
}

server {
    listen 443 ssl http2;
    listen [::]:443 ssl http2;
    server_name votre-domaine.com;
    root /var/www/emenu/public;

    # SSL
    ssl_certificate /etc/letsencrypt/live/votre-domaine.com/fullchain.pem;
    ssl_certificate_key /etc/letsencrypt/live/votre-domaine.com/privkey.pem;
    
    # Sécurité
    add_header X-Frame-Options "SAMEORIGIN" always;
    add_header X-XSS-Protection "1; mode=block" always;
    add_header X-Content-Type-Options "nosniff" always;

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

### Apache

```apache
<VirtualHost *:80>
    ServerName votre-domaine.com
    Redirect permanent / https://votre-domaine.com/
</VirtualHost>

<VirtualHost *:443>
    ServerName votre-domaine.com
    DocumentRoot /var/www/emenu/public

    SSLEngine on
    SSLCertificateFile /etc/letsencrypt/live/votre-domaine.com/fullchain.pem
    SSLCertificateKeyFile /etc/letsencrypt/live/votre-domaine.com/privkey.pem

    <Directory /var/www/emenu/public>
        AllowOverride All
        Require all granted
    </Directory>

    ErrorLog ${APACHE_LOG_DIR}/emenu-error.log
    CustomLog ${APACHE_LOG_DIR}/emenu-access.log combined
</VirtualHost>
```

---

## ✅ Checklist de Mise en Production

### Sécurité
- [ ] `APP_DEBUG=false` dans .env
- [ ] `APP_ENV=production` dans .env
- [ ] Certificat SSL installé (Let's Encrypt)
- [ ] Firewall configuré (UFW/iptables)
- [ ] Mots de passe forts partout
- [ ] `.env` non accessible publiquement
- [ ] Sauvegardes automatiques configurées

### Performance
- [ ] `php artisan config:cache`
- [ ] `php artisan route:cache`
- [ ] `php artisan view:cache`
- [ ] `composer install --optimize-autoloader --no-dev`
- [ ] Redis/Memcached configuré (optionnel)
- [ ] CDN pour assets statiques (optionnel)

### Fonctionnalités
- [ ] WhatsApp Business API configuré et testé
- [ ] CinetPay configuré et testé (mode TEST puis PRODUCTION)
- [ ] Email SMTP configuré et testé
- [ ] Compte admin créé
- [ ] Langue par défaut: Français
- [ ] Devise: XOF
- [ ] Timezone: Africa/Abidjan

### Monitoring
- [ ] Logs configurés (`storage/logs/`)
- [ ] Cron jobs configurés
- [ ] Queue workers actifs
- [ ] Monitoring serveur (Uptime, CPU, RAM)
- [ ] Alertes configurées

---

## ⏰ Cron Jobs

Ajouter au crontab:
```bash
crontab -e
```

```cron
# E-menu Scheduler
* * * * * cd /var/www/emenu && php artisan schedule:run >> /dev/null 2>&1

# Nettoyage des logs (hebdomadaire)
0 2 * * 0 cd /var/www/emenu && php artisan log:clean >> /dev/null 2>&1

# Backup database (quotidien à 3h)
0 3 * * * cd /var/www/emenu && php artisan backup:run >> /dev/null 2>&1
```

---

## 🔄 Queue Workers

Pour traiter les jobs en arrière-plan (emails, notifications):

```bash
# Supervisor configuration
nano /etc/supervisor/conf.d/emenu-worker.conf
```

```ini
[program:emenu-worker]
process_name=%(program_name)s_%(process_num)02d
command=php /var/www/emenu/artisan queue:work --sleep=3 --tries=3 --max-time=3600
autostart=true
autorestart=true
stopasgroup=true
killasgroup=true
user=www-data
numprocs=2
redirect_stderr=true
stdout_logfile=/var/www/emenu/storage/logs/worker.log
stopwaitsecs=3600
```

```bash
# Démarrer
sudo supervisorctl reread
sudo supervisorctl update
sudo supervisorctl start emenu-worker:*
```

---

## 🧪 Tests Post-Installation

### Test 1: Application accessible
```bash
curl -I https://votre-domaine.com
# Attendu: HTTP/2 200
```

### Test 2: Admin login
```
URL: https://votre-domaine.com/admin/login
Email: admin@emenu.com
Password: admin123
```

### Test 3: WhatsApp
```bash
php artisan tinker
>>> app(\App\Services\WhatsAppService::class)->sendMessage('+225XXXXXXXX', 'Test!');
```

### Test 4: CinetPay
```bash
php artisan cinetpay:test-payment --amount=100
```

### Test 5: Email
```bash
php artisan tinker
>>> Mail::raw('Test email E-menu', function($msg) { $msg->to('test@example.com'); });
```

---

## 📊 Monitoring

### Logs importants
```bash
# Application
tail -f storage/logs/laravel.log

# WhatsApp
tail -f storage/logs/whatsapp.log

# CinetPay
tail -f storage/logs/cinetpay.log

# Nginx
tail -f /var/log/nginx/emenu-access.log
tail -f /var/log/nginx/emenu-error.log
```

### Commandes de diagnostic
```bash
# Statut système
php artisan about

# Vérifier configuration
php artisan config:show

# Test base de données
php artisan db:show

# Vérifier queue
php artisan queue:monitor

# Statistiques
php artisan emenu:stats
```

---

## 🚨 Dépannage

### Problème: Page blanche
```bash
# Activer le debug temporairement
APP_DEBUG=true php artisan serve
# Vérifier storage/logs/laravel.log
```

### Problème: Permissions
```bash
sudo chown -R www-data:www-data /var/www/emenu
sudo chmod -R 775 /var/www/emenu/storage
sudo chmod -R 775 /var/www/emenu/bootstrap/cache
```

### Problème: Cache
```bash
php artisan cache:clear
php artisan config:clear
php artisan route:clear
php artisan view:clear
```

### Problème: Migrations
```bash
# Voir statut
php artisan migrate:status

# Réexécuter si nécessaire
php artisan migrate:fresh --seed  # ⚠️ EFFACE DONNÉES
```

---

## 📞 Support

### Documentation
- **README.md** - Présentation générale
- **WHATSAPP_CONFIGURATION.md** - Configuration WhatsApp détaillée
- **CINETPAY_CONFIGURATION.md** - Configuration CinetPay détaillée
- **INSTALLATION.md** - Guide d'installation complet

### Ressources
- **GitHub**: [URL du repo]
- **Documentation en ligne**: [URL]
- **Support Email**: support@emenu.com

---

## 🎯 Prochaines Étapes

1. **Personnalisation**
   - Logo et branding
   - Couleurs de thème
   - Templates emails

2. **Contenu**
   - Ajouter restaurants
   - Créer menus
   - Configurer catégories

3. **Marketing**
   - QR codes restaurants
   - Landing page
   - Réseaux sociaux

4. **Optimisation**
   - SEO
   - Performance
   - Analytics

---

## ✨ Félicitations !

Votre plateforme **E-menu WhatsApp SaaS** est maintenant opérationnelle ! 🎉

**Identifiants admin:**
- 📧 Email: `admin@emenu.com`
- 🔑 Mot de passe: `admin123`

**⚠️ IMPORTANT:** Changez le mot de passe admin immédiatement après la première connexion !

---

*Guide créé le 22 octobre 2025*
*Version: 1.0 - E-menu WhatsApp SaaS*
*Auteur: Équipe E-menu*
