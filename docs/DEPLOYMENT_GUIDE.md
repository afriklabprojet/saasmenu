# 🚀 Guide de Déploiement - APIs RESTful

## ✅ Pré-requis de déploiement

### Checklist de validation
- [x] 133 tests passés (449 assertions)
- [x] 0 bug critique
- [x] Authorization complète (vendor-based)
- [x] Validation des données (FormRequests)
- [x] Gestion des erreurs
- [x] Code review effectué
- [x] Documentation complète

### Environnement requis
- **PHP** : 8.1+
- **Laravel** : 10.x
- **MySQL** : 8.0+
- **Composer** : 2.x
- **Extension PHP** : mbstring, openssl, pdo, tokenizer, xml, ctype, json

---

## 📋 Étapes de déploiement

### 1. Préparation de l'environnement

```bash
# Cloner le dépôt
git clone <repository-url>
cd restro-saas

# Installer les dépendances
composer install --optimize-autoloader --no-dev

# Copier le fichier d'environnement
cp .env.example .env

# Générer la clé d'application
php artisan key:generate
```

### 2. Configuration de la base de données

```bash
# Éditer .env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=restrosaas
DB_USERNAME=root
DB_PASSWORD=your_password
```

```bash
# Exécuter les migrations
php artisan migrate --force

# Seed les données de base (optionnel)
php artisan db:seed --class=BaseDataSeeder
```

### 3. Configuration de l'authentification

```bash
# Configuration Sanctum dans .env
SANCTUM_STATEFUL_DOMAINS=localhost,127.0.0.1,votredomaine.com
SESSION_DOMAIN=.votredomaine.com
```

### 4. Optimisation pour la production

```bash
# Cache de configuration
php artisan config:cache

# Cache des routes
php artisan route:cache

# Cache des vues
php artisan view:cache

# Optimisation Composer
composer dump-autoload --optimize --classmap-authoritative
```

### 5. Configuration du serveur web

#### Apache (.htaccess)
```apache
<IfModule mod_rewrite.c>
    RewriteEngine On
    RewriteRule ^(.*)$ public/$1 [L]
</IfModule>
```

#### Nginx
```nginx
server {
    listen 80;
    server_name votredomaine.com;
    root /var/www/restro-saas/public;

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

### 6. Permissions des dossiers

```bash
# Permissions storage et cache
chmod -R 775 storage bootstrap/cache
chown -R www-data:www-data storage bootstrap/cache
```

### 7. Configuration du queue worker (optionnel)

```bash
# Supervisor configuration
sudo nano /etc/supervisor/conf.d/restro-saas-worker.conf
```

```ini
[program:restro-saas-worker]
process_name=%(program_name)s_%(process_num)02d
command=php /var/www/restro-saas/artisan queue:work --sleep=3 --tries=3 --max-time=3600
autostart=true
autorestart=true
stopasgroup=true
killasgroup=true
user=www-data
numprocs=2
redirect_stderr=true
stdout_logfile=/var/www/restro-saas/storage/logs/worker.log
stopwaitsecs=3600
```

```bash
# Démarrer le worker
sudo supervisorctl reread
sudo supervisorctl update
sudo supervisorctl start restro-saas-worker:*
```

### 8. Configuration du scheduler

```bash
# Ajouter au crontab
crontab -e

# Ajouter cette ligne
* * * * * cd /var/www/restro-saas && php artisan schedule:run >> /dev/null 2>&1
```

---

## 🔐 Sécurité en production

### Variables d'environnement critiques

```bash
APP_ENV=production
APP_DEBUG=false
APP_URL=https://votredomaine.com

# Générer une clé forte
APP_KEY=base64:VOTRE_CLE_GENEREE

# Désactiver les routes de debug
DEBUGBAR_ENABLED=false
TELESCOPE_ENABLED=false
```

### HTTPS obligatoire

```nginx
# Redirection HTTP vers HTTPS
server {
    listen 80;
    server_name votredomaine.com;
    return 301 https://$server_name$request_uri;
}

server {
    listen 443 ssl http2;
    server_name votredomaine.com;
    
    ssl_certificate /path/to/cert.pem;
    ssl_certificate_key /path/to/key.pem;
    
    # ... reste de la config
}
```

### Headers de sécurité

```php
// app/Http/Middleware/SecurityHeaders.php
public function handle($request, Closure $next)
{
    $response = $next($request);
    
    $response->headers->set('X-Frame-Options', 'SAMEORIGIN');
    $response->headers->set('X-Content-Type-Options', 'nosniff');
    $response->headers->set('X-XSS-Protection', '1; mode=block');
    $response->headers->set('Referrer-Policy', 'strict-origin-when-cross-origin');
    
    return $response;
}
```

### Rate Limiting

```php
// app/Providers/RouteServiceProvider.php
RateLimiter::for('api', function (Request $request) {
    return Limit::perMinute(60)->by($request->user()?->id ?: $request->ip());
});

// routes/api.php
Route::middleware(['auth:sanctum', 'throttle:api'])->group(function() {
    // Vos routes API
});
```

---

## 📊 Monitoring et logs

### Configuration des logs

```php
// config/logging.php - Production
'channels' => [
    'stack' => [
        'driver' => 'stack',
        'channels' => ['daily', 'slack'],
        'ignore_exceptions' => false,
    ],
    
    'daily' => [
        'driver' => 'daily',
        'path' => storage_path('logs/laravel.log'),
        'level' => 'error',
        'days' => 14,
    ],
    
    'slack' => [
        'driver' => 'slack',
        'url' => env('LOG_SLACK_WEBHOOK_URL'),
        'username' => 'RestroSaaS Bot',
        'emoji' => ':boom:',
        'level' => 'critical',
    ],
],
```

### Monitoring des erreurs

```bash
# Installation Sentry (optionnel)
composer require sentry/sentry-laravel

# Configuration .env
SENTRY_LARAVEL_DSN=https://your-dsn@sentry.io/project-id
SENTRY_TRACES_SAMPLE_RATE=0.2
```

### Health Check endpoint

```php
// routes/api.php
Route::get('/health', function () {
    return response()->json([
        'status' => 'ok',
        'timestamp' => now()->toISOString(),
        'database' => DB::connection()->getDatabaseName(),
        'cache' => Cache::has('test_key') ? 'ok' : 'error',
    ]);
});
```

---

## 🧪 Tests en production

### Smoke tests après déploiement

```bash
# Vérifier que l'API répond
curl -X GET https://votredomaine.com/api/health

# Tester l'authentification
curl -X POST https://votredomaine.com/api/login \
  -H "Content-Type: application/json" \
  -d '{"email":"test@example.com","password":"password"}'

# Tester un endpoint admin (avec token)
curl -X GET https://votredomaine.com/api/admin/orders \
  -H "Authorization: Bearer YOUR_TOKEN" \
  -H "Accept: application/json"
```

### Tests automatisés

```bash
# Exécuter les tests sur le serveur de staging
php artisan test --testsuite=Feature --stop-on-failure

# Tests spécifiques aux APIs
php artisan test --filter="Api"
```

---

## 🔄 Mise à jour et rollback

### Déploiement avec zéro downtime

```bash
#!/bin/bash
# deploy.sh

# Activer le mode maintenance
php artisan down --message="Mise à jour en cours" --retry=60

# Pull des changements
git pull origin main

# Installer dépendances
composer install --optimize-autoloader --no-dev

# Migrations
php artisan migrate --force

# Vider les caches
php artisan cache:clear
php artisan config:cache
php artisan route:cache
php artisan view:cache

# Redémarrer le queue worker
sudo supervisorctl restart restro-saas-worker:*

# Désactiver le mode maintenance
php artisan up

echo "✅ Déploiement terminé avec succès"
```

### Rollback rapide

```bash
#!/bin/bash
# rollback.sh

# Activer maintenance
php artisan down

# Retour à la version précédente
git reset --hard HEAD~1

# Rollback migrations (si nécessaire)
php artisan migrate:rollback --step=1

# Réinstaller dépendances
composer install --no-dev

# Caches
php artisan config:cache
php artisan route:cache

# Redémarrage workers
sudo supervisorctl restart restro-saas-worker:*

# Maintenance off
php artisan up

echo "✅ Rollback effectué"
```

---

## 📈 Performance

### Optimisations recommandées

1. **OPcache activé**
```ini
; php.ini
opcache.enable=1
opcache.memory_consumption=256
opcache.max_accelerated_files=20000
opcache.validate_timestamps=0
```

2. **Redis pour cache et sessions**
```bash
# .env
CACHE_DRIVER=redis
SESSION_DRIVER=redis
QUEUE_CONNECTION=redis

REDIS_HOST=127.0.0.1
REDIS_PASSWORD=null
REDIS_PORT=6379
```

3. **CDN pour assets statiques**
```bash
# Configuration CloudFlare, AWS CloudFront, etc.
ASSET_URL=https://cdn.votredomaine.com
```

4. **Database indexing**
```bash
# Vérifier les index critiques
php artisan db:show
php artisan db:table orders --show-indexes
```

---

## 📞 Support et maintenance

### Commandes utiles

```bash
# Logs en temps réel
tail -f storage/logs/laravel.log

# Vider tous les caches
php artisan optimize:clear

# Lister les routes
php artisan route:list --path=admin

# Statistiques queue
php artisan queue:monitor

# Backup base de données
mysqldump -u root -p restrosaas > backup_$(date +%Y%m%d).sql
```

### Checklist maintenance mensuelle

- [ ] Vérifier les logs d'erreur
- [ ] Analyser les performances (slow queries)
- [ ] Mettre à jour les dépendances (`composer update`)
- [ ] Backup de la base de données
- [ ] Vérifier l'espace disque
- [ ] Tester les endpoints critiques
- [ ] Vérifier les certificats SSL

---

## 🎯 Métriques de succès

### KPIs à surveiller

- **Uptime** : > 99.9%
- **Response time** : < 200ms (95th percentile)
- **Error rate** : < 0.1%
- **Test coverage** : 100% (133 tests)
- **Database queries** : < 50ms en moyenne

### Monitoring recommandé

- **New Relic** ou **Datadog** pour APM
- **Pingdom** ou **UptimeRobot** pour uptime
- **Sentry** pour error tracking
- **Laravel Telescope** (staging uniquement)

---

## ✅ Checklist finale de déploiement

- [ ] Variables d'environnement configurées
- [ ] Base de données migrée
- [ ] Caches optimisés
- [ ] HTTPS configuré
- [ ] Permissions correctes
- [ ] Queue worker démarré
- [ ] Cron scheduler actif
- [ ] Logs configurés
- [ ] Monitoring actif
- [ ] Backup automatique configuré
- [ ] Tests smoke réussis
- [ ] Documentation à jour

---

**Le projet RestroSaaS est prêt pour la production !** 🚀

*Guide mis à jour le 15 novembre 2025*
