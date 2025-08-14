# Development Setup Guide

This guide will help you set up a local development environment for the TD Cost Calculator module.

## Prerequisites

### System Requirements
- **PHP** >= 8.1 with extensions:
  - BCMath (for precise decimal calculations)
  - PDO MySQL/PostgreSQL
  - JSON
  - OpenSSL
  - Tokenizer
  - XML
- **Composer** >= 2.0
- **Node.js** >= 16.x (for asset compilation)
- **Git** >= 2.0
- **TaskHub Core** installation

### Development Tools (Recommended)
- **PHPStorm** or **VS Code** with PHP extensions
- **Xdebug** for debugging
- **Laravel Debugbar** for profiling
- **MySQL Workbench** or **phpMyAdmin** for database management

## Installation Steps

### 1. Clone the Repository
```bash
# Navigate to your TaskHub modules directory
cd /path/to/taskhub/modules

# Clone the repository
git clone https://github.com/tronderdata/td-cost-calcultaror.git
cd td-cost-calcultaror
```

### 2. Install Dependencies
```bash
# Install PHP dependencies
composer install

# Install Node.js dependencies (if any)
npm install
```

### 3. Environment Configuration
```bash
# Copy environment configuration (if exists)
cp .env.example .env

# Edit configuration as needed
nano .env
```

### 4. Database Setup
```bash
# Run migrations from TaskHub root directory
cd /path/to/taskhub
php artisan migrate --path=modules/td-cost-calcultaror/database/migrations

# Seed test data (optional)
php artisan db:seed --class=TdCostCalcultarorSeeder
```

### 5. Asset Compilation
```bash
# Compile assets (if applicable)
npm run dev

# Or for production
npm run production
```

### 6. Permissions Setup
```bash
# Set proper permissions for storage and cache
chmod -R 775 storage/
chmod -R 775 bootstrap/cache/
```

## Development Environment Configuration

### Xdebug Setup
Add to your `php.ini`:
```ini
zend_extension=xdebug
xdebug.mode=debug,coverage
xdebug.start_with_request=yes
xdebug.client_host=localhost
xdebug.client_port=9003
```

### Laravel Debugbar
Add to your TaskHub `.env`:
```bash
APP_DEBUG=true
DEBUGBAR_ENABLED=true
```

### Database Configuration
Recommended development database settings:
```bash
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=taskhub_dev
DB_USERNAME=your_username
DB_PASSWORD=your_password
```

## Project Structure

```
td-cost-calcultaror/
├── database/
│   └── migrations/          # Database migrations
├── docs/                    # Technical documentation
├── src/
│   ├── Http/
│   │   ├── Controllers/     # Web and API controllers
│   │   └── Middleware/      # Custom middleware
│   ├── Models/              # Eloquent models
│   ├── Providers/           # Service providers
│   ├── Services/            # Business logic services
│   ├── Traits/              # Reusable traits
│   ├── resources/
│   │   ├── lang/            # Language files
│   │   └── views/           # Blade templates
│   └── routes/              # Route definitions
├── tests/                   # Test suites
└── composer.json            # Dependencies
```

## Running Tests

### Unit Tests
```bash
# Run all tests
php artisan test modules/td-cost-calcultaror/tests/

# Run specific test class
php artisan test modules/td-cost-calcultaror/tests/Unit/CostCalculationTest.php

# Run with coverage
php artisan test --coverage
```

### Feature Tests
```bash
# Run feature tests only
php artisan test modules/td-cost-calcultaror/tests/Feature/

# Run specific feature
php artisan test modules/td-cost-calcultaror/tests/Feature/ProductTest.php
```

## Development Workflow

### 1. Branch Strategy
```bash
# Create feature branch
git checkout -b feature/new-feature-name

# Make changes and commit
git add .
git commit -m "Add new feature description"

# Push branch
git push origin feature/new-feature-name
```

### 2. Code Standards
- Follow **PSR-12** coding standards
- Use **meaningful variable and method names**
- Add **PHPDoc** comments for all methods
- Write **tests** for new functionality

### 3. Testing Before Commit
```bash
# Run code standards check
./vendor/bin/phpcs src/ --standard=PSR12

# Run static analysis
./vendor/bin/phpstan analyse src/

# Run all tests
php artisan test modules/td-cost-calcultaror/tests/
```

## Debugging Tips

### Database Query Debugging
```php
// Enable query logging
\DB::enableQueryLog();

// Your code here

// Dump queries
dd(\DB::getQueryLog());
```

### Cache Debugging
```bash
# Clear all caches
php artisan cache:clear
php artisan config:clear
php artisan view:clear
php artisan route:clear

# Clear module-specific cache
CacheService::clearAllCaches();
```

### Performance Profiling
```php
// Add to your code for timing
$start = microtime(true);
// Your code
$time = microtime(true) - $start;
\Log::info("Operation took: " . $time . " seconds");
```

## Common Development Tasks

### Adding New Cost Calculation
1. Create model in `src/Models/`
2. Add migration in `database/migrations/`
3. Create controller in `src/Http/Controllers/`
4. Add routes in `src/routes/web.php`
5. Create views in `src/resources/views/`
6. Write tests in `tests/`

### Adding New Language Support
1. Create language directory: `src/resources/lang/xx/`
2. Copy `messages.php` from existing language
3. Translate all strings
4. Test in browser with `?lang=xx`

### Adding New API Endpoint
1. Create API controller in `src/Http/Controllers/Api/`
2. Add routes in `src/routes/api.php`
3. Add validation and responses
4. Document in `docs/api.md`
5. Write API tests

## Troubleshooting

### Common Issues

**Migration Errors:**
```bash
# Check migration status
php artisan migrate:status --path=modules/td-cost-calcultaror/database/migrations

# Rollback and retry
php artisan migrate:rollback --step=1
php artisan migrate --path=modules/td-cost-calcultaror/database/migrations
```

**Permission Errors:**
```bash
# Fix file permissions
chmod -R 775 storage/
chown -R www-data:www-data storage/
```

**Cache Issues:**
```bash
# Nuclear option - clear everything
php artisan optimize:clear
composer dump-autoload
```

## Getting Help

- Check [Troubleshooting Guide](troubleshooting.md)
- Review [Architecture Documentation](architecture.md)
- Search existing [GitHub Issues](https://github.com/tronderdata/td-cost-calcultaror/issues)
- Ask in [GitHub Discussions](https://github.com/tronderdata/td-cost-calcultaror/discussions)

---

*Happy coding! 🚀*
