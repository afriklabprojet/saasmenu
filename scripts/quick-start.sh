#!/bin/bash

# RestroSaaS Addons Quick Start Script
# This script provides a complete setup for development environment

set -e

echo "🚀 RestroSaaS Addons Quick Start"
echo "==============================="

# Colors for output
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
NC='\033[0m' # No Color

print_step() {
    echo -e "${BLUE}[STEP]${NC} $1"
}

print_success() {
    echo -e "${GREEN}[✓]${NC} $1"
}

print_warning() {
    echo -e "${YELLOW}[⚠]${NC} $1"
}

print_error() {
    echo -e "${RED}[✗]${NC} $1"
}

# Check if we're in the right directory
if [ ! -f "artisan" ]; then
    print_error "This script must be run from the Laravel project root directory"
    exit 1
fi

print_step "1. Installing PHP Dependencies..."
if command -v composer >/dev/null 2>&1; then
    composer install --optimize-autoloader
    print_success "PHP dependencies installed"
else
    print_error "Composer not found. Please install Composer first."
    exit 1
fi

print_step "2. Installing Node.js Dependencies..."
if command -v npm >/dev/null 2>&1; then
    npm install
    print_success "Node.js dependencies installed"
else
    print_warning "npm not found. Frontend assets may not build correctly."
fi

print_step "3. Setting up Environment..."
if [ ! -f ".env" ]; then
    if [ -f ".env.example" ]; then
        cp .env.example .env
        print_success "Environment file created from example"
    else
        print_error ".env.example file not found"
        exit 1
    fi
else
    print_success "Environment file already exists"
fi

print_step "4. Generating Application Key..."
php artisan key:generate
print_success "Application key generated"

print_step "5. Setting up Database..."
print_warning "Please ensure your database is configured in .env file"
read -p "Have you configured your database settings in .env? (y/n): " -n 1 -r
echo
if [[ $REPLY =~ ^[Yy]$ ]]; then
    php artisan migrate
    print_success "Database migrations completed"
else
    print_warning "Skipping database setup. Run 'php artisan migrate' after configuring database."
fi

print_step "6. Creating Storage Directories..."
mkdir -p storage/app/imports
mkdir -p storage/app/exports
mkdir -p storage/app/firebase
mkdir -p storage/app/qr-codes
mkdir -p storage/logs/addons
print_success "Storage directories created"

print_step "7. Setting File Permissions..."
chmod -R 775 storage
chmod -R 775 bootstrap/cache
print_success "File permissions set"

print_step "8. Generating API Documentation..."
php artisan l5-swagger:generate
print_success "API documentation generated"

print_step "9. Seeding Demo Data..."
read -p "Would you like to seed demo data for addons? (y/n): " -n 1 -r
echo
if [[ $REPLY =~ ^[Yy]$ ]]; then
    php artisan db:seed --class=AddonDemoSeeder
    print_success "Demo data seeded"
else
    print_warning "Skipping demo data. Run 'php artisan db:seed --class=AddonDemoSeeder' later if needed."
fi

print_step "10. Building Frontend Assets..."
if command -v npm >/dev/null 2>&1; then
    npm run build
    print_success "Frontend assets built"
else
    print_warning "Skipping frontend build (npm not available)"
fi

print_step "11. Running Validation..."
if [ -f "validate-addons.sh" ]; then
    chmod +x validate-addons.sh
    ./validate-addons.sh
else
    print_warning "Validation script not found"
fi

echo ""
echo "🎉 Quick Start Complete!"
echo "======================="
echo ""
print_success "RestroSaaS Addons are now set up and ready to use!"
echo ""
echo "📋 Next Steps:"
echo "  • Start development server: php artisan serve"
echo "  • View API docs: http://localhost:8000/api/documentation"
echo "  • Run tests: php artisan test"
echo "  • Start queue worker: php artisan queue:work"
echo ""
echo "📁 Important Files:"
echo "  • Environment: .env"
echo "  • API Routes: routes/api.php"
echo "  • Controllers: app/Http/Controllers/Api/"
echo "  • Models: app/Models/"
echo "  • Services: app/Services/"
echo ""
echo "🔧 Development Commands:"
echo "  • Clear cache: php artisan optimize:clear"
echo "  • Generate docs: php artisan l5-swagger:generate"
echo "  • Run migrations: php artisan migrate"
echo "  • Seed data: php artisan db:seed --class=AddonDemoSeeder"
echo ""
echo "📖 Documentation:"
echo "  • Production deployment: PRODUCTION_DEPLOYMENT.md"
echo "  • Architecture: ARCHITECTURE_MODULAIRE.md"
echo "  • API documentation: http://localhost:8000/api/documentation"
echo ""
print_success "Happy coding! 🚀"
