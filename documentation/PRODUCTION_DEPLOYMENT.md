# RestroSaaS Addons Production Deployment Guide

## 🚀 Overview

This guide covers the complete deployment process for all 8 RestroSaaS priority addons in a production environment.

## 📋 Prerequisites

### System Requirements
- **PHP**: 8.1+ with required extensions
- **MySQL**: 8.0+ or MariaDB 10.4+
- **Redis**: 6.0+ for caching and queues
- **Node.js**: 16+ with NPM
- **Nginx**: 1.18+ or Apache 2.4+
- **Supervisor**: For queue worker management
- **SSL Certificate**: For HTTPS encryption

### PHP Extensions Required
```bash
php8.1-cli php8.1-fpm php8.1-mysql php8.1-redis php8.1-mbstring 
php8.1-xml php8.1-zip php8.1-curl php8.1-gd php8.1-intl 
php8.1-bcmath php8.1-soap php8.1-imagick
```

## 🔧 Deployment Steps

### 1. Initial Setup
```bash
# Clone repository
git clone https://github.com/your-repo/restro-saas.git
cd restro-saas

# Make setup script executable
chmod +x setup-production.sh

# Run production setup
./setup-production.sh
```

### 2. Environment Configuration

Update `.env.production` with your production values:

```env
# Application
APP_NAME="RestroSaaS"
APP_ENV=production
APP_KEY=base64:your-generated-key
APP_DEBUG=false
APP_URL=https://yourdomain.com

# Database
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=restro_saas_production
DB_USERNAME=your_db_user
DB_PASSWORD=your_secure_password

# Redis
REDIS_HOST=127.0.0.1
REDIS_PASSWORD=your_redis_password
REDIS_PORT=6379

# Cache & Sessions
CACHE_DRIVER=redis
SESSION_DRIVER=redis
QUEUE_CONNECTION=redis

# Mail Configuration
MAIL_MAILER=smtp
MAIL_HOST=your-smtp-server
MAIL_PORT=587
MAIL_USERNAME=your-email@domain.com
MAIL_PASSWORD=your-email-password
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS=noreply@yourdomain.com
MAIL_FROM_NAME="RestroSaaS"

# Firebase Configuration
FIREBASE_PROJECT_ID=your-production-project-id
FIREBASE_CREDENTIALS_PATH=storage/app/firebase/production-service-account.json
FIREBASE_DATABASE_URL=https://your-production-project.firebaseio.com

# PayPal Configuration
PAYPAL_MODE=live
PAYPAL_CLIENT_ID=your-production-paypal-client-id
PAYPAL_CLIENT_SECRET=your-production-paypal-client-secret
PAYPAL_WEBHOOK_ID=your-production-webhook-id

# Social Login
FACEBOOK_CLIENT_ID=your-production-facebook-client-id
FACEBOOK_CLIENT_SECRET=your-production-facebook-client-secret
FACEBOOK_REDIRECT_URL=https://yourdomain.com/auth/facebook/callback

GOOGLE_CLIENT_ID=your-production-google-client-id
GOOGLE_CLIENT_SECRET=your-production-google-client-secret
GOOGLE_REDIRECT_URL=https://yourdomain.com/auth/google/callback

# Queue Configuration
ADDON_QUEUE_WORKERS=4
IMPORT_EXPORT_QUEUE=import_export
NOTIFICATION_QUEUE=notifications
POS_QUEUE=pos_processing
LOYALTY_QUEUE=loyalty_processing

# Monitoring
LOG_CHANNEL=daily
LOG_DEPRECATIONS_CHANNEL=null
LOG_LEVEL=error
```

### 3. Database Setup

```bash
# Create database
mysql -u root -p
CREATE DATABASE restro_saas_production CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
CREATE USER 'restro_user'@'localhost' IDENTIFIED BY 'secure_password';
GRANT ALL PRIVILEGES ON restro_saas_production.* TO 'restro_user'@'localhost';
FLUSH PRIVILEGES;
EXIT;

# Run migrations
php artisan migrate --env=production --force

# Seed with demo data (optional for testing)
php artisan db:seed --class=AddonDemoSeeder --env=production
```

### 4. File Permissions

```bash
# Set correct ownership
sudo chown -R $USER:www-data storage bootstrap/cache
sudo chown -R $USER:www-data storage/app/imports
sudo chown -R $USER:www-data storage/app/exports
sudo chown -R $USER:www-data storage/app/firebase
sudo chown -R $USER:www-data storage/app/qr-codes

# Set permissions
chmod -R 755 storage bootstrap/cache
chmod -R 775 storage/logs storage/app/imports storage/app/exports
```

### 5. Web Server Configuration

#### Nginx Configuration
```bash
# Copy configuration
sudo cp nginx-restro-saas.conf /etc/nginx/sites-available/restro-saas
sudo ln -s /etc/nginx/sites-available/restro-saas /etc/nginx/sites-enabled/

# Update domain and SSL paths in the configuration
sudo nano /etc/nginx/sites-available/restro-saas

# Test and reload
sudo nginx -t
sudo systemctl reload nginx
```

#### Rate Limiting Zones
Add to `/etc/nginx/nginx.conf` in the `http` block:
```nginx
# Rate limiting zones for RestroSaaS addons
limit_req_zone $binary_remote_addr zone=api:10m rate=100r/m;
limit_req_zone $binary_remote_addr zone=pos_api:10m rate=60r/m;
limit_req_zone $binary_remote_addr zone=loyalty_api:10m rate=80r/m;
limit_req_zone $binary_remote_addr zone=tableqr_api:10m rate=200r/m;
```

### 6. Queue Workers Setup

```bash
# Update supervisor configuration
sudo supervisorctl reread
sudo supervisorctl update
sudo supervisorctl start restro-saas-addons:*

# Check status
sudo supervisorctl status restro-saas-addons:*
```

### 7. Cron Jobs

```bash
# Add Laravel scheduler to crontab
crontab -e

# Add this line:
* * * * * cd /path/to/your/project && php artisan schedule:run >> /dev/null 2>&1

# Add health check (every 5 minutes)
*/5 * * * * cd /path/to/your/project && ./addon-health-check.sh
```

### 8. SSL Certificate

#### Using Let's Encrypt (Certbot)
```bash
# Install certbot
sudo apt install certbot python3-certbot-nginx

# Obtain certificate
sudo certbot --nginx -d yourdomain.com -d www.yourdomain.com

# Test automatic renewal
sudo certbot renew --dry-run
```

## 🔍 Monitoring and Maintenance

### Health Checks

The `addon-health-check.sh` script monitors:
- API endpoint availability
- Database connectivity
- Queue worker status
- Redis connectivity
- Storage permissions

### Log Monitoring

Monitor these log files:
```bash
# Application logs
tail -f storage/logs/laravel.log

# Queue worker logs
tail -f storage/logs/worker-*.log

# Nginx logs
sudo tail -f /var/log/nginx/access.log
sudo tail -f /var/log/nginx/error.log

# Health check logs
tail -f storage/logs/health-check.log
```

### Performance Monitoring

Key metrics to monitor:
- API response times
- Queue job processing rates
- Memory usage
- Database query performance
- Redis memory usage
- SSL certificate expiry

## 🚀 Deployment Updates

Use the automated deployment script:
```bash
./deploy-addons.sh
```

This script:
1. Puts application in maintenance mode
2. Pulls latest code changes
3. Updates dependencies
4. Runs database migrations
5. Rebuilds caches
6. Restarts queue workers
7. Runs health checks
8. Brings application back online

## 🔐 Security Considerations

### API Security
- All endpoints require authentication
- Rate limiting is enforced per addon
- CORS policies are configured
- Input validation on all requests

### Database Security
- Use dedicated database user with minimal privileges
- Enable SSL for database connections
- Regular security updates
- Backup encryption

### File Security
- Restrict file upload types
- Scan uploaded files for malware
- Store sensitive files outside web root
- Implement proper access controls

## 📊 API Documentation

Access comprehensive API documentation at:
- **Interactive Docs**: `https://yourdomain.com/api/documentation`
- **OpenAPI Spec**: `https://yourdomain.com/api-docs.json`
- **Health Check**: `https://yourdomain.com/api/health`

## 🔧 Troubleshooting

### Common Issues

#### Queue Workers Not Processing
```bash
# Check supervisor status
sudo supervisorctl status restro-saas-addons:*

# Restart workers
sudo supervisorctl restart restro-saas-addons:*

# Check worker logs
tail -f storage/logs/worker-*.log
```

#### High Memory Usage
```bash
# Monitor memory usage
htop

# Optimize PHP-FPM settings
sudo nano /etc/php/8.1/fpm/pool.d/www.conf

# Restart PHP-FPM
sudo systemctl restart php8.1-fpm
```

#### Database Connection Issues
```bash
# Test database connection
php artisan tinker
DB::connection()->getPdo();

# Check MySQL process list
mysql -u root -p -e "SHOW PROCESSLIST;"
```

#### Redis Connection Issues
```bash
# Test Redis connection
redis-cli ping

# Check Redis memory usage
redis-cli info memory
```

### Emergency Procedures

#### Rollback Deployment
```bash
# Revert to previous version
git checkout previous-tag
./deploy-addons.sh
```

#### Emergency Maintenance Mode
```bash
# Enable maintenance mode
php artisan down --message="Emergency maintenance in progress" --retry=60

# Disable maintenance mode
php artisan up
```

## 📞 Support and Resources

### Documentation Links
- **Laravel Documentation**: https://laravel.com/docs
- **Swagger/OpenAPI**: https://swagger.io/docs/
- **Firebase Admin SDK**: https://firebase.google.com/docs/admin
- **PayPal Developer**: https://developer.paypal.com/

### Monitoring Tools
- **Laravel Telescope**: For application debugging
- **Laravel Horizon**: For queue monitoring
- **New Relic/DataDog**: For application monitoring
- **Uptime Robot**: For uptime monitoring

## 🎉 Conclusion

Your RestroSaaS addons are now fully deployed in production with:

✅ All 8 priority addons operational  
✅ Comprehensive API documentation  
✅ Queue workers for background processing  
✅ Health monitoring and logging  
✅ Automated deployment scripts  
✅ Security hardening  
✅ Performance optimization  

For support, please refer to the API documentation or contact the development team.
