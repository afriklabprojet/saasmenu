#!/bin/bash

# RestroSaaS Addons Production Setup Script
# This script configures the production environment for all 8 addons

set -e

echo "🚀 Starting RestroSaaS Addons Production Setup..."
echo "=================================================="

# Colors for output
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
NC='\033[0m' # No Color

# Function to print colored output
print_status() {
    echo -e "${BLUE}[INFO]${NC} $1"
}

print_success() {
    echo -e "${GREEN}[SUCCESS]${NC} $1"
}

print_warning() {
    echo -e "${YELLOW}[WARNING]${NC} $1"
}

print_error() {
    echo -e "${RED}[ERROR]${NC} $1"
}

# Check if running as root
if [[ $EUID -eq 0 ]]; then
   print_error "This script should not be run as root for security reasons."
   exit 1
fi

# Check required tools
print_status "Checking required tools..."

command -v php >/dev/null 2>&1 || { print_error "PHP is required but not installed. Aborting."; exit 1; }
command -v composer >/dev/null 2>&1 || { print_error "Composer is required but not installed. Aborting."; exit 1; }
command -v npm >/dev/null 2>&1 || { print_error "NPM is required but not installed. Aborting."; exit 1; }

print_success "All required tools are available"

# Check PHP version
PHP_VERSION=$(php -r "echo PHP_VERSION;")
print_status "PHP Version: $PHP_VERSION"

if php -r "exit(version_compare(PHP_VERSION, '8.1.0', '<') ? 1 : 0);"; then
    print_error "PHP 8.1.0 or higher is required. Current version: $PHP_VERSION"
    exit 1
fi

# Environment Configuration
print_status "Configuring production environment..."

# Copy .env.example to .env.production if it doesn't exist
if [ ! -f .env.production ]; then
    if [ -f .env.example ]; then
        cp .env.example .env.production
        print_success "Created .env.production from .env.example"
    else
        print_error ".env.example not found"
        exit 1
    fi
fi

# Update production environment variables
print_status "Updating production environment configuration..."

# Application settings
sed -i.bak 's/APP_ENV=.*/APP_ENV=production/' .env.production
sed -i.bak 's/APP_DEBUG=.*/APP_DEBUG=false/' .env.production
sed -i.bak 's/LOG_LEVEL=.*/LOG_LEVEL=error/' .env.production

# Session and cache configuration for production
if ! grep -q "SESSION_DRIVER=" .env.production; then
    echo "SESSION_DRIVER=redis" >> .env.production
fi

if ! grep -q "CACHE_DRIVER=" .env.production; then
    echo "CACHE_DRIVER=redis" >> .env.production
fi

if ! grep -q "QUEUE_CONNECTION=" .env.production; then
    echo "QUEUE_CONNECTION=redis" >> .env.production
fi

# Addon-specific configurations
print_status "Adding addon-specific production configurations..."

# Firebase configuration
if ! grep -q "FIREBASE_PROJECT_ID=" .env.production; then
    cat >> .env.production << EOF

# Firebase Configuration (Production)
FIREBASE_PROJECT_ID=your-production-project-id
FIREBASE_CREDENTIALS_PATH=storage/app/firebase/production-service-account.json
FIREBASE_DATABASE_URL=https://your-production-project.firebaseio.com

EOF
fi

# PayPal configuration
if ! grep -q "PAYPAL_MODE=" .env.production; then
    cat >> .env.production << EOF

# PayPal Configuration (Production)
PAYPAL_MODE=live
PAYPAL_CLIENT_ID=your-production-paypal-client-id
PAYPAL_CLIENT_SECRET=your-production-paypal-client-secret
PAYPAL_WEBHOOK_ID=your-production-webhook-id

EOF
fi

# Social login configuration
if ! grep -q "FACEBOOK_CLIENT_ID=" .env.production; then
    cat >> .env.production << EOF

# Social Login Configuration (Production)
FACEBOOK_CLIENT_ID=your-production-facebook-client-id
FACEBOOK_CLIENT_SECRET=your-production-facebook-client-secret
FACEBOOK_REDIRECT_URL=https://yourdomain.com/auth/facebook/callback

GOOGLE_CLIENT_ID=your-production-google-client-id
GOOGLE_CLIENT_SECRET=your-production-google-client-secret
GOOGLE_REDIRECT_URL=https://yourdomain.com/auth/google/callback

EOF
fi

# Queue and job configuration
if ! grep -q "ADDON_QUEUE_WORKERS=" .env.production; then
    cat >> .env.production << EOF

# Addon Queue Configuration
ADDON_QUEUE_WORKERS=4
IMPORT_EXPORT_QUEUE=import_export
NOTIFICATION_QUEUE=notifications
POS_QUEUE=pos_processing
LOYALTY_QUEUE=loyalty_processing

EOF
fi

print_success "Production environment configuration updated"

# Composer install for production
print_status "Installing Composer dependencies for production..."
composer install --no-dev --optimize-autoloader --no-interaction
print_success "Composer dependencies installed"

# NPM install and build for production
print_status "Building frontend assets for production..."
npm ci --production
npm run build
print_success "Frontend assets built"

# Laravel optimizations
print_status "Applying Laravel production optimizations..."

# Generate application key if not set
if ! grep -q "APP_KEY=base64:" .env.production; then
    php artisan key:generate --env=production --force
    print_success "Application key generated"
fi

# Cache configuration
php artisan config:cache --env=production
print_success "Configuration cached"

# Cache routes
php artisan route:cache
print_success "Routes cached"

# Cache views
php artisan view:cache
print_success "Views cached"

# Cache events
php artisan event:cache
print_success "Events cached"

# Database migration and optimization
print_status "Running database migrations..."
php artisan migrate --env=production --force
print_success "Database migrations completed"

# Generate API documentation
print_status "Generating API documentation..."
php artisan l5-swagger:generate
print_success "API documentation generated"

# Create storage links
print_status "Creating storage symbolic links..."
php artisan storage:link
print_success "Storage links created"

# Set proper file permissions
print_status "Setting file permissions..."
chmod -R 755 storage bootstrap/cache
chmod -R 775 storage/logs
print_success "File permissions set"

# Create addon-specific directories
print_status "Creating addon-specific directories..."
mkdir -p storage/app/imports
mkdir -p storage/app/exports
mkdir -p storage/app/firebase
mkdir -p storage/app/qr-codes
mkdir -p storage/app/pos-receipts
mkdir -p storage/logs/addons

chmod -R 775 storage/app/imports
chmod -R 775 storage/app/exports
chmod -R 775 storage/app/firebase
chmod -R 775 storage/app/qr-codes
chmod -R 775 storage/app/pos-receipts
chmod -R 775 storage/logs/addons

print_success "Addon directories created"

# Create supervisor configuration for queue workers
print_status "Creating supervisor configuration for queue workers..."

sudo tee /etc/supervisor/conf.d/restro-saas-addons.conf > /dev/null << EOF
[group:restro-saas-addons]
programs=restro-saas-default,restro-saas-import-export,restro-saas-notifications,restro-saas-pos,restro-saas-loyalty

[program:restro-saas-default]
process_name=%(program_name)s_%(process_num)02d
command=php $(pwd)/artisan queue:work redis --sleep=3 --tries=3 --max-time=3600
autostart=true
autorestart=true
stopasgroup=true
killasgroup=true
user=$(whoami)
numprocs=2
redirect_stderr=true
stdout_logfile=$(pwd)/storage/logs/worker-default.log
stopwaitsecs=3600

[program:restro-saas-import-export]
process_name=%(program_name)s_%(process_num)02d
command=php $(pwd)/artisan queue:work redis --queue=import_export --sleep=3 --tries=3 --max-time=3600
autostart=true
autorestart=true
stopasgroup=true
killasgroup=true
user=$(whoami)
numprocs=1
redirect_stderr=true
stdout_logfile=$(pwd)/storage/logs/worker-import-export.log
stopwaitsecs=3600

[program:restro-saas-notifications]
process_name=%(program_name)s_%(process_num)02d
command=php $(pwd)/artisan queue:work redis --queue=notifications --sleep=3 --tries=3 --max-time=3600
autostart=true
autorestart=true
stopasgroup=true
killasgroup=true
user=$(whoami)
numprocs=2
redirect_stderr=true
stdout_logfile=$(pwd)/storage/logs/worker-notifications.log
stopwaitsecs=3600

[program:restro-saas-pos]
process_name=%(program_name)s_%(process_num)02d
command=php $(pwd)/artisan queue:work redis --queue=pos_processing --sleep=3 --tries=1 --max-time=1800
autostart=true
autorestart=true
stopasgroup=true
killasgroup=true
user=$(whoami)
numprocs=1
redirect_stderr=true
stdout_logfile=$(pwd)/storage/logs/worker-pos.log
stopwaitsecs=1800

[program:restro-saas-loyalty]
process_name=%(program_name)s_%(process_num)02d
command=php $(pwd)/artisan queue:work redis --queue=loyalty_processing --sleep=3 --tries=3 --max-time=3600
autostart=true
autorestart=true
stopasgroup=true
killasgroup=true
user=$(whoami)
numprocs=1
redirect_stderr=true
stdout_logfile=$(pwd)/storage/logs/worker-loyalty.log
stopwaitsecs=3600
EOF

print_success "Supervisor configuration created"

# Create cron jobs for scheduled tasks
print_status "Setting up cron jobs for scheduled tasks..."

# Add Laravel scheduler to crontab if not already present
CRON_JOB="* * * * * cd $(pwd) && php artisan schedule:run >> /dev/null 2>&1"
(crontab -l 2>/dev/null; echo "$CRON_JOB") | sort -u | crontab -

print_success "Cron jobs configured"

# Create nginx configuration template
print_status "Creating nginx configuration template..."

cat > nginx-restro-saas.conf << EOF
server {
    listen 80;
    listen [::]:80;
    server_name yourdomain.com www.yourdomain.com;
    return 301 https://\$server_name\$request_uri;
}

server {
    listen 443 ssl http2;
    listen [::]:443 ssl http2;
    server_name yourdomain.com www.yourdomain.com;

    root $(pwd)/public;
    index index.php index.html;

    # SSL Configuration (update paths to your certificates)
    ssl_certificate /path/to/your/certificate.pem;
    ssl_certificate_key /path/to/your/private.key;
    ssl_protocols TLSv1.2 TLSv1.3;
    ssl_ciphers ECDHE-RSA-AES256-GCM-SHA512:DHE-RSA-AES256-GCM-SHA512:ECDHE-RSA-AES256-GCM-SHA384:DHE-RSA-AES256-GCM-SHA384;
    ssl_prefer_server_ciphers off;

    # Security headers
    add_header X-Frame-Options "SAMEORIGIN" always;
    add_header X-XSS-Protection "1; mode=block" always;
    add_header X-Content-Type-Options "nosniff" always;
    add_header Referrer-Policy "no-referrer-when-downgrade" always;
    add_header Content-Security-Policy "default-src 'self' http: https: data: blob: 'unsafe-inline'" always;

    # Rate limiting for API endpoints
    location /api {
        limit_req zone=api burst=20 nodelay;
        try_files \$uri \$uri/ /index.php?\$query_string;
    }

    # Special handling for addon API endpoints
    location /api/pos {
        limit_req zone=pos_api burst=10 nodelay;
        try_files \$uri \$uri/ /index.php?\$query_string;
    }

    location /api/loyalty {
        limit_req zone=loyalty_api burst=15 nodelay;
        try_files \$uri \$uri/ /index.php?\$query_string;
    }

    location /api/tableqr {
        limit_req zone=tableqr_api burst=30 nodelay;
        try_files \$uri \$uri/ /index.php?\$query_string;
    }

    location / {
        try_files \$uri \$uri/ /index.php?\$query_string;
    }

    location = /favicon.ico { access_log off; log_not_found off; }
    location = /robots.txt  { access_log off; log_not_found off; }

    error_page 404 /index.php;

    location ~ \.php\$ {
        fastcgi_pass unix:/var/run/php/php8.1-fpm.sock;
        fastcgi_param SCRIPT_FILENAME \$realpath_root\$fastcgi_script_name;
        include fastcgi_params;
        fastcgi_hide_header X-Powered-By;
    }

    location ~ /\.(?!well-known).* {
        deny all;
    }

    # Cache static assets
    location ~* \.(jpg|jpeg|png|gif|ico|css|js|woff|woff2)\$ {
        expires 1y;
        add_header Cache-Control "public, immutable";
    }
}

# Rate limiting zones (add to http block in main nginx.conf)
# limit_req_zone \$binary_remote_addr zone=api:10m rate=100r/m;
# limit_req_zone \$binary_remote_addr zone=pos_api:10m rate=60r/m;
# limit_req_zone \$binary_remote_addr zone=loyalty_api:10m rate=80r/m;
# limit_req_zone \$binary_remote_addr zone=tableqr_api:10m rate=200r/m;
EOF

print_success "Nginx configuration template created"

# Create health check script
print_status "Creating health check script..."

cat > addon-health-check.sh << 'EOF'
#!/bin/bash

# RestroSaaS Addons Health Check Script

DOMAIN="https://yourdomain.com"
LOG_FILE="storage/logs/health-check.log"

echo "$(date): Starting health check..." >> $LOG_FILE

# Check main API health
API_HEALTH=$(curl -s -o /dev/null -w "%{http_code}" "$DOMAIN/api/health")
if [ "$API_HEALTH" = "200" ]; then
    echo "$(date): API Health - OK" >> $LOG_FILE
else
    echo "$(date): API Health - FAILED (HTTP $API_HEALTH)" >> $LOG_FILE
fi

# Check database connection
DB_CHECK=$(php artisan tinker --execute="try { DB::connection()->getPdo(); echo 'OK'; } catch(Exception \$e) { echo 'FAILED'; }")
echo "$(date): Database - $DB_CHECK" >> $LOG_FILE

# Check queue workers
QUEUE_STATUS=$(supervisorctl status restro-saas-addons:* | grep -c "RUNNING")
echo "$(date): Queue Workers Running - $QUEUE_STATUS" >> $LOG_FILE

# Check storage permissions
STORAGE_WRITABLE=$([ -w storage/logs ] && echo "OK" || echo "FAILED")
echo "$(date): Storage Writable - $STORAGE_WRITABLE" >> $LOG_FILE

# Check Redis connection
REDIS_CHECK=$(php artisan tinker --execute="try { Redis::ping(); echo 'OK'; } catch(Exception \$e) { echo 'FAILED'; }")
echo "$(date): Redis - $REDIS_CHECK" >> $LOG_FILE

echo "$(date): Health check completed" >> $LOG_FILE
EOF

chmod +x addon-health-check.sh
print_success "Health check script created"

# Create deployment script
print_status "Creating deployment script..."

cat > deploy-addons.sh << 'EOF'
#!/bin/bash

# RestroSaaS Addons Deployment Script

set -e

echo "🚀 Starting RestroSaaS Addons Deployment..."

# Put application in maintenance mode
php artisan down --message="Updating addons..." --retry=60

# Pull latest changes
git pull origin main

# Install/update dependencies
composer install --no-dev --optimize-autoloader --no-interaction

# Build frontend assets
npm ci --production
npm run build

# Run database migrations
php artisan migrate --force

# Clear and rebuild caches
php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan event:cache

# Generate API documentation
php artisan l5-swagger:generate

# Restart queue workers
supervisorctl restart restro-saas-addons:*

# Restart PHP-FPM (adjust service name as needed)
sudo systemctl reload php8.1-fpm

# Run health check
./addon-health-check.sh

# Bring application back online
php artisan up

echo "✅ Deployment completed successfully!"
EOF

chmod +x deploy-addons.sh
print_success "Deployment script created"

# Final recommendations
print_status "Production setup completed successfully!"
echo ""
echo "📋 Next Steps:"
echo "=============="
print_warning "1. Update .env.production with your actual production credentials"
print_warning "2. Configure SSL certificates in nginx configuration"
print_warning "3. Update nginx rate limiting zones in main nginx.conf"
print_warning "4. Copy nginx-restro-saas.conf to /etc/nginx/sites-available/"
print_warning "5. Enable the site: sudo ln -s /etc/nginx/sites-available/nginx-restro-saas.conf /etc/nginx/sites-enabled/"
print_warning "6. Reload nginx: sudo systemctl reload nginx"
print_warning "7. Update and reload supervisor: sudo supervisorctl reread && sudo supervisorctl update"
print_warning "8. Add health check to cron: */5 * * * * cd $(pwd) && ./addon-health-check.sh"
print_warning "9. Test all addon endpoints using the API documentation"
print_warning "10. Set up monitoring and log aggregation"

echo ""
print_success "🎉 RestroSaaS Addons Production Setup Complete!"
print_success "📖 Access API documentation at: https://yourdomain.com/api/documentation"
print_success "🔍 Monitor logs in: storage/logs/"
print_success "⚡ Deploy updates using: ./deploy-addons.sh"
