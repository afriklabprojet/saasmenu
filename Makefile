# Tests Makefile for RestroSaaS

.PHONY: test test-unit test-feature test-performance test-coverage help

# Default target
help:
	@echo "Available commands:"
	@echo "  test           - Run all tests"
	@echo "  test-unit      - Run unit tests only"
	@echo "  test-feature   - Run feature tests only"
	@echo "  test-performance - Run performance tests only"
	@echo "  test-coverage  - Run tests with coverage report"
	@echo "  test-parallel  - Run tests in parallel"
	@echo "  setup-test     - Setup test environment"

# Setup test environment
setup-test:
	@echo "Setting up test environment..."
	php artisan config:clear --env=testing
	php artisan cache:clear --env=testing
	php artisan migrate:fresh --env=testing --seed

# Run all tests
test:
	@echo "Running all tests..."
	php artisan test

# Run unit tests only
test-unit:
	@echo "Running unit tests..."
	php artisan test --testsuite=Unit

# Run feature tests only
test-feature:
	@echo "Running feature tests..."
	php artisan test --testsuite=Feature

# Run performance tests only
test-performance:
	@echo "Running performance tests..."
	php artisan test tests/Feature/Performance

# Run tests with coverage
test-coverage:
	@echo "Running tests with coverage..."
	php artisan test --coverage --min=70

# Run tests in parallel
test-parallel:
	@echo "Running tests in parallel..."
	php artisan test --parallel

# Quick test run (fast tests only)
test-quick:
	@echo "Running quick tests..."
	php artisan test --exclude-group=slow

# Test specific models
test-models:
	@echo "Testing models..."
	php artisan test tests/Unit/Models

# Test API endpoints
test-api:
	@echo "Testing API..."
	php artisan test tests/Feature/Api

# Clean test environment
clean-test:
	@echo "Cleaning test environment..."
	php artisan config:clear --env=testing
	php artisan cache:clear --env=testing
	php artisan migrate:fresh --env=testing
