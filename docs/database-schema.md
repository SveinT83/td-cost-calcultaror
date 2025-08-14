# Database Schema Documentation

This document describes the complete database schema for the TD Cost Calculator module, including tables, relationships, and constraints.

## Overview

The TD Cost Calculator uses 4 main tables to store cost calculation data:
- `cost_items` - Individual cost elements
- `products` - Service/product definitions  
- `cost_allocations` - Relationships between products and cost items
- `cost_item_logs` - Audit trail for changes

## Table Definitions

### cost_items

Primary table for storing individual cost elements like salary, rent, equipment costs.

```sql
CREATE TABLE `cost_items` (
  `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `description` text DEFAULT NULL,
  `price` decimal(10,2) NOT NULL,
  `period` enum('minute','hour','day','week','month','year') NOT NULL DEFAULT 'month',
  `category_id` bigint(20) UNSIGNED DEFAULT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `lifetime_months` int(11) DEFAULT NULL,
  `capacity` decimal(10,2) DEFAULT NULL,
  `notes` text DEFAULT NULL,
  `created_by` bigint(20) UNSIGNED DEFAULT NULL,
  `updated_by` bigint(20) UNSIGNED DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `cost_items_category_id_foreign` (`category_id`),
  KEY `cost_items_created_by_foreign` (`created_by`),
  KEY `cost_items_updated_by_foreign` (`updated_by`),
  KEY `cost_items_is_active_index` (`is_active`),
  KEY `cost_items_period_index` (`period`)
);
```

#### Field Descriptions

| Field | Type | Description |
|-------|------|-------------|
| `id` | bigint | Primary key |
| `name` | varchar(255) | Display name of the cost item |
| `description` | text | Optional detailed description |
| `price` | decimal(10,2) | Cost amount in the specified period |
| `period` | enum | Time period for the price (minute, hour, day, week, month, year) |
| `category_id` | bigint | Foreign key to categories table (optional) |
| `is_active` | tinyint(1) | Whether this cost item is currently active |
| `lifetime_months` | int | Expected lifetime in months (for depreciation) |
| `capacity` | decimal(10,2) | Maximum capacity or usage limit |
| `notes` | text | Additional notes or documentation |
| `created_by` | bigint | User who created this record |
| `updated_by` | bigint | User who last updated this record |
| `created_at` | timestamp | Creation timestamp |
| `updated_at` | timestamp | Last update timestamp |

#### Sample Data
```sql
INSERT INTO `cost_items` VALUES 
(1, 'Hourly Salary', 'Consultant hourly rate including taxes', 531.59, 'hour', NULL, 1, NULL, NULL, 'Includes employer taxes and benefits', 1, 1, NOW(), NOW()),
(2, 'Office Rent', 'Monthly office rental cost', 13385.78, 'month', 1, 1, NULL, NULL, 'Prime location downtown office space', 1, 1, NOW(), NOW()),
(3, 'Laptop Equipment', 'Developer laptop with depreciation', 25000.00, 'year', 2, 1, 36, 1.0, 'MacBook Pro M3 with 3-year expected lifetime', 1, 1, NOW(), NOW());
```

### products

Stores product or service definitions that combine multiple cost items.

```sql
CREATE TABLE `products` (
  `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `description` text DEFAULT NULL,
  `calculation_model` enum('per_user','per_resource','fixed_price') NOT NULL DEFAULT 'per_user',
  `expected_users` int(11) DEFAULT NULL,
  `expected_resource_units` int(11) DEFAULT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `notes` text DEFAULT NULL,
  `created_by` bigint(20) UNSIGNED DEFAULT NULL,
  `updated_by` bigint(20) UNSIGNED DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `products_created_by_foreign` (`created_by`),
  KEY `products_updated_by_foreign` (`updated_by`),
  KEY `products_is_active_index` (`is_active`),
  KEY `products_calculation_model_index` (`calculation_model`)
);
```

#### Field Descriptions

| Field | Type | Description |
|-------|------|-------------|
| `id` | bigint | Primary key |
| `name` | varchar(255) | Product or service name |
| `description` | text | Detailed product description |
| `calculation_model` | enum | How costs are calculated (per_user, per_resource, fixed_price) |
| `expected_users` | int | Expected number of users (for per_user model) |
| `expected_resource_units` | int | Expected resource units (for per_resource model) |
| `is_active` | tinyint(1) | Whether this product is currently active |
| `notes` | text | Additional notes about the product |
| `created_by` | bigint | User who created this record |
| `updated_by` | bigint | User who last updated this record |
| `created_at` | timestamp | Creation timestamp |
| `updated_at` | timestamp | Last update timestamp |

#### Sample Data
```sql
INSERT INTO `products` VALUES 
(1, 'Lei en nerd', 'Technical consulting service with hourly billing', 'per_resource', NULL, 1, 1, 'Hourly consultant service including office overhead', 1, 1, NOW(), NOW()),
(2, 'Team Office Suite', 'Complete office solution for development teams', 'per_user', 10, NULL, 1, 'Includes workspace, equipment, and utilities per team member', 1, 1, NOW(), NOW());
```

### cost_allocations

Junction table that defines how cost items are allocated to products.

```sql
CREATE TABLE `cost_allocations` (
  `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
  `product_id` bigint(20) UNSIGNED NOT NULL,
  `cost_item_id` bigint(20) UNSIGNED NOT NULL,
  `allocation_type` enum('fixed','per_user','per_resource_unit','percentage') NOT NULL DEFAULT 'fixed',
  `allocation_value` decimal(10,2) NOT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `notes` text DEFAULT NULL,
  `created_by` bigint(20) UNSIGNED DEFAULT NULL,
  `updated_by` bigint(20) UNSIGNED DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `cost_allocations_product_id_foreign` (`product_id`),
  KEY `cost_allocations_cost_item_id_foreign` (`cost_item_id`),
  KEY `cost_allocations_created_by_foreign` (`created_by`),
  KEY `cost_allocations_updated_by_foreign` (`updated_by`),
  KEY `cost_allocations_is_active_index` (`is_active`),
  CONSTRAINT `cost_allocations_product_id_foreign` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE,
  CONSTRAINT `cost_allocations_cost_item_id_foreign` FOREIGN KEY (`cost_item_id`) REFERENCES `cost_items` (`id`) ON DELETE CASCADE
);
```

#### Field Descriptions

| Field | Type | Description |
|-------|------|-------------|
| `id` | bigint | Primary key |
| `product_id` | bigint | Foreign key to products table |
| `cost_item_id` | bigint | Foreign key to cost_items table |
| `allocation_type` | enum | How this cost is allocated (fixed, per_user, per_resource_unit, percentage) |
| `allocation_value` | decimal(10,2) | Amount or percentage to allocate |
| `is_active` | tinyint(1) | Whether this allocation is currently active |
| `notes` | text | Additional notes about this allocation |
| `created_by` | bigint | User who created this record |
| `updated_by` | bigint | User who last updated this record |
| `created_at` | timestamp | Creation timestamp |
| `updated_at` | timestamp | Last update timestamp |

#### Allocation Types Explained

| Type | Description | Example Use Case |
|------|-------------|------------------|
| `fixed` | Same amount regardless of scale | Base license costs, fixed equipment |
| `per_user` | Amount multiplied by user count | Per-seat software licenses |
| `per_resource_unit` | Amount per resource/hour | Consultant time, server hours |
| `percentage` | Percentage of base cost | Overhead allocation, profit margin |

#### Sample Data
```sql
INSERT INTO `cost_allocations` VALUES 
(1, 1, 1, 'fixed', 531.59, 1, 'Direct consultant hourly rate', 1, 1, NOW(), NOW()),
(2, 1, 2, 'fixed', 63.74, 1, 'Office overhead calculated from monthly rent', 1, 1, NOW(), NOW()),
(3, 2, 2, 'per_user', 1338.58, 1, 'Office space allocation per team member', 1, 1, NOW(), NOW()),
(4, 2, 3, 'per_user', 694.44, 1, 'Laptop depreciation per team member', 1, 1, NOW(), NOW());
```

### cost_item_logs

Audit trail table for tracking changes to cost items and allocations.

```sql
CREATE TABLE `cost_item_logs` (
  `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
  `cost_item_id` bigint(20) UNSIGNED DEFAULT NULL,
  `product_id` bigint(20) UNSIGNED DEFAULT NULL,
  `allocation_id` bigint(20) UNSIGNED DEFAULT NULL,
  `action` enum('created','updated','deleted','allocated','deallocated') NOT NULL,
  `old_values` json DEFAULT NULL,
  `new_values` json DEFAULT NULL,
  `user_id` bigint(20) UNSIGNED DEFAULT NULL,
  `ip_address` varchar(45) DEFAULT NULL,
  `user_agent` text DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `cost_item_logs_cost_item_id_foreign` (`cost_item_id`),
  KEY `cost_item_logs_product_id_foreign` (`product_id`),
  KEY `cost_item_logs_allocation_id_foreign` (`allocation_id`),
  KEY `cost_item_logs_user_id_foreign` (`user_id`),
  KEY `cost_item_logs_action_index` (`action`),
  KEY `cost_item_logs_created_at_index` (`created_at`)
);
```

#### Field Descriptions

| Field | Type | Description |
|-------|------|-------------|
| `id` | bigint | Primary key |
| `cost_item_id` | bigint | Foreign key to cost_items table (nullable) |
| `product_id` | bigint | Foreign key to products table (nullable) |
| `allocation_id` | bigint | Foreign key to cost_allocations table (nullable) |
| `action` | enum | Type of action performed |
| `old_values` | json | Previous values before change |
| `new_values` | json | New values after change |
| `user_id` | bigint | User who performed the action |
| `ip_address` | varchar(45) | IP address of the user |
| `user_agent` | text | Browser user agent string |
| `created_at` | timestamp | When the action occurred |

## Relationships

### Entity Relationship Diagram

```
┌─────────────┐         ┌─────────────────┐         ┌─────────────┐
│ cost_items  │         │ cost_allocations │         │  products   │
│             │         │                 │         │             │
│ id (PK)     │◄────────┤ cost_item_id    │────────►│ id (PK)     │
│ name        │         │ product_id      │         │ name        │
│ price       │         │ allocation_type │         │ calc_model  │
│ period      │         │ allocation_value│         │ exp_users   │
│ category_id │         │ is_active       │         │ is_active   │
│ is_active   │         │ notes           │         │ notes       │
│ notes       │         │ ...             │         │ ...         │
│ ...         │         └─────────────────┘         └─────────────┘
└─────────────┘                   │
                                  │
                                  ▼
                        ┌─────────────────┐
                        │ cost_item_logs  │
                        │                 │
                        │ id (PK)         │
                        │ cost_item_id    │
                        │ product_id      │
                        │ allocation_id   │
                        │ action          │
                        │ old_values      │
                        │ new_values      │
                        │ ...             │
                        └─────────────────┘
```

### Eloquent Relationships

#### CostItem Model
```php
class CostItem extends Model {
    // One cost item can be allocated to many products
    public function allocations() {
        return $this->hasMany(CostAllocation::class);
    }
    
    // Many-to-many relationship with products through allocations
    public function products() {
        return $this->belongsToMany(Product::class, 'cost_allocations')
                    ->withPivot('allocation_type', 'allocation_value', 'is_active')
                    ->withTimestamps();
    }
    
    // Audit trail
    public function logs() {
        return $this->hasMany(CostItemLog::class);
    }
    
    // Category relationship (if Categories module is available)
    public function category() {
        return $this->belongsTo(Category::class);
    }
}
```

#### Product Model
```php
class Product extends Model {
    // One product can have many cost allocations
    public function allocations() {
        return $this->hasMany(CostAllocation::class);
    }
    
    // Alias for easier access
    public function costAllocations() {
        return $this->allocations();
    }
    
    // Many-to-many relationship with cost items
    public function costItems() {
        return $this->belongsToMany(CostItem::class, 'cost_allocations')
                    ->withPivot('allocation_type', 'allocation_value', 'is_active')
                    ->withTimestamps();
    }
    
    // Audit trail
    public function logs() {
        return $this->hasMany(CostItemLog::class);
    }
}
```

#### CostAllocation Model
```php
class CostAllocation extends Model {
    // Belongs to a product
    public function product() {
        return $this->belongsTo(Product::class);
    }
    
    // Belongs to a cost item
    public function costItem() {
        return $this->belongsTo(CostItem::class);
    }
    
    // Audit trail
    public function logs() {
        return $this->hasMany(CostItemLog::class, 'allocation_id');
    }
}
```

## Indexes and Performance

### Primary Indexes
- All tables have auto-incrementing primary keys
- Foreign key constraints automatically create indexes

### Custom Indexes
```sql
-- Performance indexes for common queries
CREATE INDEX idx_cost_items_active_period ON cost_items (is_active, period);
CREATE INDEX idx_products_active_model ON products (is_active, calculation_model);
CREATE INDEX idx_allocations_product_active ON cost_allocations (product_id, is_active);
CREATE INDEX idx_logs_item_date ON cost_item_logs (cost_item_id, created_at);

-- Composite indexes for complex queries
CREATE INDEX idx_allocations_lookup ON cost_allocations (product_id, cost_item_id, is_active);
```

### Query Optimization Tips

#### Good: Use eager loading
```php
$products = Product::with(['allocations.costItem'])
    ->where('is_active', true)
    ->get();
```

#### Bad: N+1 queries
```php
$products = Product::where('is_active', true)->get();
foreach ($products as $product) {
    foreach ($product->allocations as $allocation) {
        echo $allocation->costItem->name; // Additional query per allocation
    }
}
```

## Data Integrity

### Foreign Key Constraints
```sql
-- Ensure referential integrity
ALTER TABLE cost_allocations 
ADD CONSTRAINT fk_allocation_product 
FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE;

ALTER TABLE cost_allocations 
ADD CONSTRAINT fk_allocation_cost_item 
FOREIGN KEY (cost_item_id) REFERENCES cost_items(id) ON DELETE CASCADE;
```

### Business Rules Validation

#### Model Validation
```php
class CostItem extends Model {
    protected $rules = [
        'name' => 'required|string|max:255',
        'price' => 'required|numeric|min:0',
        'period' => 'required|in:minute,hour,day,week,month,year',
        'lifetime_months' => 'nullable|integer|min:1|max:600',
    ];
}
```

#### Database Constraints
```sql
-- Ensure positive values
ALTER TABLE cost_items ADD CONSTRAINT chk_price_positive CHECK (price >= 0);
ALTER TABLE cost_allocations ADD CONSTRAINT chk_allocation_positive CHECK (allocation_value >= 0);

-- Ensure logical relationships
ALTER TABLE products ADD CONSTRAINT chk_users_when_per_user 
CHECK (calculation_model != 'per_user' OR expected_users IS NOT NULL);
```

## Migration Strategy

### Version Control
- Each migration file includes timestamp prefix
- Rollback methods for all schema changes
- Seed data separated from schema migrations

### Example Migration
```php
class CreateCostItemsTable extends Migration {
    public function up() {
        Schema::create('cost_items', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->text('description')->nullable();
            $table->decimal('price', 10, 2);
            $table->enum('period', ['minute', 'hour', 'day', 'week', 'month', 'year'])
                  ->default('month');
            $table->foreignId('category_id')->nullable()->constrained()->onDelete('set null');
            $table->boolean('is_active')->default(true);
            $table->integer('lifetime_months')->nullable();
            $table->decimal('capacity', 10, 2)->nullable();
            $table->text('notes')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users');
            $table->foreignId('updated_by')->nullable()->constrained('users');
            $table->timestamps();
            
            // Indexes
            $table->index(['is_active', 'period']);
        });
    }
    
    public function down() {
        Schema::dropIfExists('cost_items');
    }
}
```

## Backup and Recovery

### Regular Backups
```bash
# Daily backup with timestamp
mysqldump --single-transaction taskhub_db \
  cost_items cost_allocations products cost_item_logs \
  > backup_cost_calculator_$(date +%Y%m%d).sql
```

### Point-in-Time Recovery
```bash
# Restore from backup
mysql taskhub_db < backup_cost_calculator_20250814.sql

# Verify data integrity
mysql -e "SELECT COUNT(*) FROM cost_items; SELECT COUNT(*) FROM products;" taskhub_db
```

---

*This schema is designed for scalability, performance, and data integrity while maintaining flexibility for future enhancements.*
