#!/bin/bash

# RestroSaaS Test Runner Script
# Usage: ./run-tests.sh [option]

set -e

PROJECT_DIR="/Users/teya2023/Documents/codecayon SaaS/restrosaas-37/saas-whatsapp/restro-saas"
cd "$PROJECT_DIR"

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

# Function to setup test environment
setup_test_env() {
    print_status "Setting up test environment..."
    
    # Clear caches
    php artisan config:clear --env=testing
    php artisan cache:clear --env=testing
    
    # Run migrations
    print_status "Running test migrations..."
    php artisan migrate:fresh --env=testing --force
    
    print_success "Test environment setup complete!"
}

# Function to run specific test suite
run_test_suite() {
    local suite=$1
    local description=$2
    
    print_status "Running $description..."
    
    if php artisan test --testsuite="$suite" --stop-on-failure; then
        print_success "$description completed successfully!"
        return 0
    else
        print_error "$description failed!"
        return 1
    fi
}

# Function to run performance tests
run_performance_tests() {
    print_status "Running performance tests..."
    
    if php artisan test tests/Feature/Performance --stop-on-failure; then
        print_success "Performance tests completed successfully!"
        return 0
    else
        print_error "Performance tests failed!"
        return 1
    fi
}

# Function to run all tests with coverage
run_tests_with_coverage() {
    print_status "Running all tests with coverage..."
    
    if php artisan test --coverage --min=70; then
        print_success "All tests with coverage completed successfully!"
        return 0
    else
        print_error "Tests failed or coverage is below 70%!"
        return 1
    fi
}

# Main execution
case "${1:-all}" in
    "setup")
        setup_test_env
        ;;
    "unit")
        setup_test_env
        run_test_suite "Unit" "unit tests"
        ;;
    "feature")
        setup_test_env
        run_test_suite "Feature" "feature tests"
        ;;
    "performance")
        setup_test_env
        run_performance_tests
        ;;
    "coverage")
        setup_test_env
        run_tests_with_coverage
        ;;
    "all"|"")
        print_status "Running complete test suite..."
        setup_test_env
        
        # Run unit tests
        if ! run_test_suite "Unit" "unit tests"; then
            exit 1
        fi
        
        # Run feature tests
        if ! run_test_suite "Feature" "feature tests"; then
            exit 1
        fi
        
        # Run performance tests
        if ! run_performance_tests; then
            print_warning "Performance tests failed, but continuing..."
        fi
        
        print_success "All test suites completed!"
        ;;
    "help"|"-h"|"--help")
        echo "RestroSaaS Test Runner"
        echo ""
        echo "Usage: $0 [option]"
        echo ""
        echo "Options:"
        echo "  setup       Setup test environment only"
        echo "  unit        Run unit tests only"
        echo "  feature     Run feature tests only"
        echo "  performance Run performance tests only"
        echo "  coverage    Run all tests with coverage"
        echo "  all         Run all test suites (default)"
        echo "  help        Show this help message"
        ;;
    *)
        print_error "Unknown option: $1"
        echo "Use '$0 help' for available options"
        exit 1
        ;;
esac