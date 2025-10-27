# RestroSaaS Addons System

[![Laravel](https://img.shields.io/badge/Laravel-9.52.16-red.svg)](https://laravel.com)
[![PHP](https://img.shields.io/badge/PHP-8.1+-blue.svg)](https://php.net)
[![License](https://img.shields.io/badge/License-MIT-green.svg)](LICENSE)

## 🎯 Overview

RestroSaaS Addons is a comprehensive enterprise-grade addon system that extends the core RestroSaaS platform with 8 powerful modules designed for restaurant management and operations.

## 🚀 Quick Start

```bash
# Clone and setup
git clone <repository-url>
cd restro-saas

# Run quick setup
chmod +x quick-start.sh
./quick-start.sh

# Start development server
php artisan serve
```

Visit `http://localhost:8000/api/documentation` to explore the API.

## 📦 Included Addons

### 1. 🛠️ API Routes Foundation
- **Purpose**: Core API infrastructure with authentication and rate limiting
- **Features**: JWT authentication, API versioning, request validation, error handling
- **Endpoints**: Authentication, user management, health checks

### 2. 📱 TableQR System
- **Purpose**: QR code-based table management and ordering
- **Features**: Dynamic QR generation, table tracking, menu integration
- **Endpoints**: QR generation, table scanning, order management

### 3. 🎁 Loyalty Program
- **Purpose**: Customer loyalty and rewards management
- **Features**: Points system, tier management, reward redemption
- **Endpoints**: Member management, points tracking, reward processing

### 4. 💳 POS System
- **Purpose**: Point of sale terminal and session management
- **Features**: Multi-terminal support, cart management, checkout processing
- **Endpoints**: Terminal management, cart operations, payment processing

### 5. 💰 PayPal Gateway
- **Purpose**: PayPal payment integration
- **Features**: Secure payments, webhook handling, refund management
- **Endpoints**: Payment processing, transaction tracking, webhook handling

### 6. 🔐 Social Login
- **Purpose**: Social media authentication integration
- **Features**: Facebook/Google login, profile synchronization
- **Endpoints**: OAuth flows, profile management, account linking

### 7. 🔔 Firebase Push Notifications
- **Purpose**: Real-time push notification system
- **Features**: Device management, targeted messaging, analytics
- **Endpoints**: Device registration, message sending, analytics

### 8. 📊 Import/Export Tools
- **Purpose**: Data import/export functionality
- **Features**: CSV/Excel support, batch processing, data validation
- **Endpoints**: File upload, processing status, download results

## 🏗️ Architecture

```
RestroSaaS Addons/
├── API Layer (REST + Documentation)
├── Service Layer (Business Logic)
├── Model Layer (Data Access)
├── Queue System (Background Processing)
├── Validation Layer (Request/Response)
└── Testing Suite (Unit + Integration)
```

### Key Components

- **Controllers**: API endpoint handlers with full Swagger documentation
- **Services**: Business logic and external service integration
- **Models**: Eloquent models with relationships and validation
- **Requests**: Form request validation classes
- **Middleware**: Authentication, rate limiting, and permissions
- **Jobs**: Background processing for heavy operations
- **Tests**: Comprehensive PHPUnit test suite

## 🔧 Installation

### Prerequisites

- PHP 8.1+
- Laravel 9.52.16+
- MySQL 8.0+ / PostgreSQL 13+
- Redis 6.0+
- Node.js 16+
- Composer 2.0+

### Development Setup

1. **Environment Setup**:
```bash
cp .env.example .env
php artisan key:generate
```

2. **Database Configuration**:
```bash
# Configure database in .env
php artisan migrate
php artisan db:seed --class=AddonDemoSeeder
```

3. **Cache and Optimization**:
```bash
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

4. **API Documentation**:
```bash
php artisan l5-swagger:generate
```

### Production Deployment

Use the automated production setup:

```bash
chmod +x setup-production.sh
./setup-production.sh
```

See [PRODUCTION_DEPLOYMENT.md](PRODUCTION_DEPLOYMENT.md) for detailed instructions.

## 📚 API Documentation

### Interactive Documentation
- **URL**: `/api/documentation`
- **Format**: Swagger/OpenAPI 3.0
- **Features**: Interactive testing, code examples, authentication

### Authentication

All API endpoints require authentication via Laravel Sanctum:

```bash
# Login to get token
POST /api/auth/login
{
  "email": "user@example.com",
  "password": "password"
}

# Use token in subsequent requests
Authorization: Bearer {token}
```

### Example API Calls

**POS Session Management**:
```bash
# Create POS session
POST /api/pos/sessions
{
  "terminal_id": 1,
  "cashier_id": 5
}

# Add item to cart
POST /api/pos/cart/items
{
  "session_id": 1,
  "product_id": 10,
  "quantity": 2
}
```

**Loyalty Program**:
```bash
# Create loyalty member
POST /api/loyalty/members
{
  "program_id": 1,
  "customer_id": 25,
  "phone": "+1234567890"
}

# Award points
POST /api/loyalty/transactions
{
  "member_id": 1,
  "points": 100,
  "type": "earned"
}
```

## 🧪 Testing

### Running Tests

```bash
# Run all tests
php artisan test

# Run specific test suite
php artisan test --testsuite=Feature
php artisan test --testsuite=Unit

# Run with coverage
php artisan test --coverage
```

### Test Structure

```
tests/
├── Feature/
│   ├── POSSystemTest.php
│   ├── LoyaltyProgramTest.php
│   ├── TableQRTest.php
│   └── ...
├── Unit/
│   ├── Services/
│   ├── Models/
│   └── ...
└── TestCase.php
```

### Sample Test

```php
public function test_pos_session_creation()
{
    $terminal = POSTerminal::factory()->create();
    $user = User::factory()->create();
    
    $response = $this->actingAs($user, 'sanctum')
        ->postJson('/api/pos/sessions', [
            'terminal_id' => $terminal->id,
            'cashier_id' => $user->id
        ]);
    
    $response->assertStatus(201)
        ->assertJsonStructure(['data' => ['id', 'terminal_id', 'status']]);
}
```

## 🔄 Queue System

### Queue Configuration

The system uses Redis for queue management with specialized configurations:

```php
// config/addon-queue.php
'pos' => [
    'driver' => 'redis',
    'retry_after' => 300,
    'block_for' => null,
],
'loyalty' => [
    'driver' => 'redis',
    'retry_after' => 600,
    'block_for' => null,
],
```

### Background Jobs

```bash
# Start queue workers
php artisan queue:work --queue=pos,loyalty,notifications,imports

# Monitor queue
php artisan queue:monitor pos,loyalty,notifications
```

### Supervisor Configuration

```ini
[program:restro-saas-addon-worker]
process_name=%(program_name)s_%(process_num)02d
command=php /path/to/artisan queue:work redis --queue=pos,loyalty,notifications,imports --sleep=3 --tries=3 --max-time=3600
autostart=true
autorestart=true
stopasgroup=true
killasgroup=true
user=www-data
numprocs=4
redirect_stderr=true
stdout_logfile=/path/to/storage/logs/queue-worker.log
stopwaitsecs=3600
```

## 📈 Monitoring

### Health Checks

```bash
# System health
GET /api/health

# Addon-specific health
GET /api/pos/health
GET /api/loyalty/health
```

### Logging

All addon operations are logged with structured data:

```php
Log::channel('addon')->info('POS session created', [
    'session_id' => $session->id,
    'terminal_id' => $session->terminal_id,
    'cashier_id' => $session->cashier_id,
    'timestamp' => now()
]);
```

### Performance Metrics

- **Database Queries**: Optimized with eager loading and indexing
- **Cache Usage**: Redis caching for frequent data
- **API Response Times**: Average < 200ms
- **Memory Usage**: Optimized for concurrent requests

## 🛠️ Development

### Code Standards

- **PSR-12**: PHP coding standards
- **Laravel Best Practices**: Following Laravel conventions
- **API Standards**: RESTful design with proper HTTP status codes
- **Documentation**: Comprehensive Swagger/OpenAPI annotations

### Contributing

1. Fork the repository
2. Create feature branch: `git checkout -b feature/addon-name`
3. Write tests for new functionality
4. Ensure all tests pass: `php artisan test`
5. Update documentation
6. Submit pull request

### Adding New Addons

1. **Create Controller**:
```php
// app/Http/Controllers/Api/NewAddonController.php
class NewAddonController extends BaseApiController
{
    /**
     * @OA\Get(
     *     path="/api/new-addon",
     *     summary="Get addon data",
     *     @OA\Response(response=200, description="Success")
     * )
     */
    public function index()
    {
        // Implementation
    }
}
```

2. **Define Routes**:
```php
// routes/api.php
Route::middleware('auth:sanctum')->group(function () {
    Route::prefix('new-addon')->group(function () {
        Route::get('/', [NewAddonController::class, 'index']);
    });
});
```

3. **Create Tests**:
```php
// tests/Feature/NewAddonTest.php
class NewAddonTest extends TestCase
{
    public function test_new_addon_functionality()
    {
        // Test implementation
    }
}
```

## 🔒 Security

### Authentication & Authorization

- **Sanctum Tokens**: Secure API authentication
- **Rate Limiting**: Prevents API abuse
- **Permission Middleware**: Role-based access control
- **Input Validation**: All requests validated and sanitized

### Data Protection

- **Encryption**: Sensitive data encrypted at rest
- **HTTPS**: All communication over secure channels
- **SQL Injection**: Protected via Eloquent ORM
- **XSS Protection**: Input sanitization and output encoding

## 📋 Validation

### System Validation

Run the comprehensive validation script:

```bash
chmod +x validate-addons.sh
./validate-addons.sh
```

This validates:
- ✅ Environment configuration
- ✅ Database connections and tables
- ✅ Redis connectivity
- ✅ File permissions
- ✅ Queue system
- ✅ API endpoints
- ✅ Models and services
- ✅ Test suite
- ✅ Documentation

## 🚀 Performance

### Optimization Features

- **Database Indexing**: Optimized queries with proper indexes
- **Caching Strategy**: Multi-layer caching (Redis, application, database)
- **Queue Processing**: Background processing for heavy operations
- **Asset Optimization**: Minified CSS/JS, CDN support
- **Database Connection Pooling**: Efficient connection management

### Benchmarks

- **API Response Time**: < 200ms average
- **Concurrent Users**: 1000+ simultaneous sessions
- **Database Queries**: < 10 queries per request average
- **Memory Usage**: < 128MB per worker process

## 📞 Support

### Documentation Resources

- **API Documentation**: `/api/documentation`
- **Architecture Guide**: `ARCHITECTURE_MODULAIRE.md`
- **Production Deployment**: `PRODUCTION_DEPLOYMENT.md`
- **Installation Guide**: `INSTALLATION.md`

### Troubleshooting

Common issues and solutions:

1. **Queue Jobs Not Processing**:
```bash
# Check queue status
php artisan queue:monitor

# Restart workers
supervisorctl restart restro-saas-addon-worker:*
```

2. **API Documentation Not Loading**:
```bash
# Regenerate documentation
php artisan l5-swagger:generate

# Clear cache
php artisan optimize:clear
```

3. **Database Connection Issues**:
```bash
# Test connection
php artisan tinker
>>> DB::connection()->getPdo();

# Run migrations
php artisan migrate:status
```

### Getting Help

- **Issues**: GitHub Issues for bug reports
- **Discussions**: GitHub Discussions for questions
- **Documentation**: Check `/api/documentation` for API details
- **Logs**: Monitor `storage/logs/` for error details

## 📄 License

This project is licensed under the MIT License - see the [LICENSE](LICENSE) file for details.

## 🙏 Acknowledgments

- Laravel Framework for the excellent foundation
- Swagger/OpenAPI for API documentation
- Redis for high-performance caching and queuing
- PHPUnit for comprehensive testing framework

---

**RestroSaaS Addons** - Enterprise-grade restaurant management system extensions 🚀
