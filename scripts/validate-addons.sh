#!/bin/bash

# RestroSaaS Addons Validation Script
# This script validates that all 8 addons are properly configured and functional

set -e

echo "🔍 RestroSaaS Addons Validation Started"
echo "======================================"

# Colors for output
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
NC='\033[0m' # No Color

VALIDATION_LOG="storage/logs/addon-validation-$(date +%Y%m%d_%H%M%S).log"

# Function to print colored output
print_status() {
    echo -e "${BLUE}[INFO]${NC} $1" | tee -a "$VALIDATION_LOG"
}

print_success() {
    echo -e "${GREEN}[✓]${NC} $1" | tee -a "$VALIDATION_LOG"
}

print_warning() {
    echo -e "${YELLOW}[⚠]${NC} $1" | tee -a "$VALIDATION_LOG"
}

print_error() {
    echo -e "${RED}[✗]${NC} $1" | tee -a "$VALIDATION_LOG"
}

ERRORS=0

# Test counter
TESTS_PASSED=0
TESTS_FAILED=0

test_passed() {
    ((TESTS_PASSED++))
    print_success "$1"
}

test_failed() {
    ((TESTS_FAILED++))
    ((ERRORS++))
    print_error "$1"
}

print_status "Starting validation at $(date)"
print_status "Validation log: $VALIDATION_LOG"

# 1. Environment Validation
print_status "1. Validating Environment Configuration..."

if [ -f .env ]; then
    test_passed "Environment file exists"
else
    test_failed "Environment file (.env) not found"
fi

# Check required environment variables
ENV_VARS=("APP_KEY" "DB_CONNECTION" "DB_DATABASE" "REDIS_HOST" "QUEUE_CONNECTION")
for var in "${ENV_VARS[@]}"; do
    if grep -q "^${var}=" .env 2>/dev/null && [ -n "$(grep "^${var}=" .env | cut -d'=' -f2)" ]; then
        test_passed "Environment variable $var is set"
    else
        test_failed "Environment variable $var is missing or empty"
    fi
done

# 2. Database Validation
print_status "2. Validating Database Connection..."

if php artisan tinker --execute="try { DB::connection()->getPdo(); echo 'SUCCESS'; } catch(Exception \$e) { echo 'FAILED: ' . \$e->getMessage(); }" 2>/dev/null | grep -q "SUCCESS"; then
    test_passed "Database connection successful"
else
    test_failed "Database connection failed"
fi

# Check required tables
TABLES=("users" "restaurants" "pos_terminals" "pos_sessions" "loyalty_programs" "loyalty_members" "table_qr_codes" "device_tokens" "import_jobs" "export_jobs")
for table in "${TABLES[@]}"; do
    if php artisan tinker --execute="try { DB::table('$table')->count(); echo 'EXISTS'; } catch(Exception \$e) { echo 'MISSING'; }" 2>/dev/null | grep -q "EXISTS"; then
        test_passed "Table '$table' exists"
    else
        test_failed "Table '$table' is missing"
    fi
done

# 3. Redis Validation
print_status "3. Validating Redis Connection..."

if php artisan tinker --execute="try { Redis::ping(); echo 'SUCCESS'; } catch(Exception \$e) { echo 'FAILED: ' . \$e->getMessage(); }" 2>/dev/null | grep -q "SUCCESS"; then
    test_passed "Redis connection successful"
else
    test_failed "Redis connection failed"
fi

# 4. File Permissions Validation
print_status "4. Validating File Permissions..."

DIRECTORIES=("storage/logs" "storage/app" "storage/framework" "bootstrap/cache")
for dir in "${DIRECTORIES[@]}"; do
    if [ -w "$dir" ]; then
        test_passed "Directory '$dir' is writable"
    else
        test_failed "Directory '$dir' is not writable"
    fi
done

# Check addon-specific directories
ADDON_DIRS=("storage/app/imports" "storage/app/exports" "storage/app/firebase" "storage/app/qr-codes")
for dir in "${ADDON_DIRS[@]}"; do
    if [ -d "$dir" ] && [ -w "$dir" ]; then
        test_passed "Addon directory '$dir' exists and is writable"
    else
        test_failed "Addon directory '$dir' is missing or not writable"
    fi
done

# 5. Queue System Validation
print_status "5. Validating Queue System..."

# Check if supervisor is running (if available)
if command -v supervisorctl >/dev/null 2>&1; then
    if supervisorctl status 2>/dev/null | grep -q "restro-saas"; then
        test_passed "Supervisor queue workers are configured"
    else
        test_warning "Supervisor queue workers not found (may be normal in development)"
    fi
else
    test_warning "Supervisor not available (may be normal in development)"
fi

# Test queue job dispatch
if php artisan tinker --execute="try { dispatch(function() { logger('Queue validation test'); }); echo 'SUCCESS'; } catch(Exception \$e) { echo 'FAILED: ' . \$e->getMessage(); }" 2>/dev/null | grep -q "SUCCESS"; then
    test_passed "Queue job dispatch successful"
else
    test_failed "Queue job dispatch failed"
fi

# 6. API Endpoints Validation
print_status "6. Validating API Endpoints..."

BASE_URL=$(php artisan tinker --execute="echo config('app.url');" 2>/dev/null)

# Test health endpoint
if curl -s "${BASE_URL}/api/health" | grep -q "healthy"; then
    test_passed "API health endpoint responsive"
else
    test_failed "API health endpoint not responsive"
fi

# 7. Addon Models Validation
print_status "7. Validating Addon Models..."

MODELS=("POSTerminal" "POSSession" "LoyaltyProgram" "LoyaltyMember" "TableQrCode" "DeviceToken" "ImportJob" "ExportJob")
for model in "${MODELS[@]}"; do
    if php artisan tinker --execute="try { App\\Models\\$model::query(); echo 'EXISTS'; } catch(Exception \$e) { echo 'MISSING'; }" 2>/dev/null | grep -q "EXISTS"; then
        test_passed "Model '$model' is accessible"
    else
        test_failed "Model '$model' is not accessible"
    fi
done

# 8. Service Classes Validation
print_status "8. Validating Service Classes..."

SERVICES=("FirebaseService" "ImportExportService")
for service in "${SERVICES[@]}"; do
    if php artisan tinker --execute="try { app('App\\Services\\$service'); echo 'EXISTS'; } catch(Exception \$e) { echo 'MISSING'; }" 2>/dev/null | grep -q "EXISTS"; then
        test_passed "Service '$service' is accessible"
    else
        test_failed "Service '$service' is not accessible"
    fi
done

# 9. Middleware Validation
print_status "9. Validating Middleware..."

MIDDLEWARES=("ValidateAddonPermission" "AddonRateLimit" "ValidateApiKey")
for middleware in "${MIDDLEWARES[@]}"; do
    if grep -r "class $middleware" app/Http/Middleware/ >/dev/null 2>&1; then
        test_passed "Middleware '$middleware' exists"
    else
        test_failed "Middleware '$middleware' is missing"
    fi
done

# 10. Console Commands Validation
print_status "10. Validating Console Commands..."

COMMANDS=("addons:process-imports" "addons:process-exports" "addons:cleanup-files" "addons:send-notification")
for command in "${COMMANDS[@]}"; do
    if php artisan list | grep -q "$command"; then
        test_passed "Console command '$command' is registered"
    else
        test_failed "Console command '$command' is not registered"
    fi
done

# 11. Configuration Files Validation
print_status "11. Validating Configuration Files..."

CONFIG_FILES=("l5-swagger.php" "addon-queue.php")
for config in "${CONFIG_FILES[@]}"; do
    if [ -f "config/$config" ]; then
        test_passed "Configuration file '$config' exists"
    else
        test_failed "Configuration file '$config' is missing"
    fi
done

# 12. Frontend Assets Validation
print_status "12. Validating Frontend Assets..."

if [ -d "public/build" ] || [ -f "public/js/app.js" ]; then
    test_passed "Frontend assets are built"
else
    test_warning "Frontend assets may need to be built (run 'npm run build')"
fi

# 13. API Documentation Validation
print_status "13. Validating API Documentation..."

if [ -f "storage/api-docs/api-docs.json" ]; then
    test_passed "API documentation is generated"
else
    test_warning "API documentation may need to be generated (run 'php artisan l5-swagger:generate')"
fi

# 14. Test Suite Validation
print_status "14. Validating Test Suite..."

if [ -d "tests/Feature" ] && [ -d "tests/Unit" ]; then
    test_passed "Test directories exist"

    # Count test files
    FEATURE_TESTS=$(find tests/Feature -name "*.php" | wc -l)
    UNIT_TESTS=$(find tests/Unit -name "*.php" | wc -l)

    if [ "$FEATURE_TESTS" -gt 0 ]; then
        test_passed "Feature tests available ($FEATURE_TESTS files)"
    else
        test_warning "No feature tests found"
    fi

    if [ "$UNIT_TESTS" -gt 0 ]; then
        test_passed "Unit tests available ($UNIT_TESTS files)"
    else
        test_warning "No unit tests found"
    fi
else
    test_failed "Test directories are missing"
fi

# 15. Factory and Seeder Validation
print_status "15. Validating Factories and Seeders..."

FACTORIES=("POSTerminalFactory" "LoyaltyProgramFactory" "TableQrCodeFactory" "DeviceTokenFactory")
for factory in "${FACTORIES[@]}"; do
    if [ -f "database/factories/$factory.php" ]; then
        test_passed "Factory '$factory' exists"
    else
        test_failed "Factory '$factory' is missing"
    fi
done

if [ -f "database/seeders/AddonDemoSeeder.php" ]; then
    test_passed "Demo seeder exists"
else
    test_failed "Demo seeder is missing"
fi

# Summary
print_status "Validation Summary"
echo "=================" | tee -a "$VALIDATION_LOG"
echo "" | tee -a "$VALIDATION_LOG"

if [ $ERRORS -eq 0 ]; then
    print_success "🎉 All validations passed! ($TESTS_PASSED tests)"
    print_success "✅ RestroSaaS Addons are properly configured and ready to use"
    echo "" | tee -a "$VALIDATION_LOG"
    print_status "🚀 Next Steps:"
    echo "  • Access API documentation: ${BASE_URL}/api/documentation" | tee -a "$VALIDATION_LOG"
    echo "  • Run tests: php artisan test" | tee -a "$VALIDATION_LOG"
    echo "  • Seed demo data: php artisan db:seed --class=AddonDemoSeeder" | tee -a "$VALIDATION_LOG"
    echo "  • Monitor logs: tail -f storage/logs/laravel.log" | tee -a "$VALIDATION_LOG"

    EXIT_CODE=0
else
    print_error "❌ Validation completed with $ERRORS error(s) and $TESTS_FAILED failed test(s)"
    print_error "📋 Please review the errors above and fix them before proceeding"
    print_error "📄 Full validation log: $VALIDATION_LOG"

    EXIT_CODE=1
fi

echo "" | tee -a "$VALIDATION_LOG"
print_status "Validation completed at $(date)"
echo "Tests passed: $TESTS_PASSED" | tee -a "$VALIDATION_LOG"
echo "Tests failed: $TESTS_FAILED" | tee -a "$VALIDATION_LOG"
echo "Total errors: $ERRORS" | tee -a "$VALIDATION_LOG"

exit $EXIT_CODE
