# Architecture Overview

This document describes the overall architecture and design patterns used in the TD Cost Calculator module.

## System Architecture

### High-Level Architecture
```
┌─────────────────┐    ┌─────────────────┐    ┌─────────────────┐
│   Presentation  │    │    Business     │    │   Data Access   │
│     Layer       │◄──►│     Logic       │◄──►│     Layer       │
│                 │    │     Layer       │    │                 │
│ • Controllers   │    │ • Services      │    │ • Models        │
│ • Views         │    │ • Calculations  │    │ • Migrations    │
│ • JavaScript    │    │ • Validation    │    │ • Database      │
└─────────────────┘    └─────────────────┘    └─────────────────┘
```

### Design Patterns

#### 1. Repository Pattern (Implicit via Eloquent)
```php
// Models act as repositories
$costItems = CostItem::where('active', true)->get();
$product = Product::with('allocations.costItem')->find($id);
```

#### 2. Service Layer Pattern
```php
// Business logic in services
class ForecastService {
    public function calculateYearlyProjection($product, $parameters) {
        // Complex business logic here
    }
}
```

#### 3. Observer Pattern (Laravel Events)
```php
// Model events for cache invalidation
class Product extends Model {
    protected static function booted() {
        static::saved(function ($product) {
            CacheService::clearProductCaches($product->id);
        });
    }
}
```

## Module Structure

### Core Components

#### 1. Models (`src/Models/`)
- **CostItem**: Individual cost elements
- **Product**: Service/product definitions
- **CostAllocation**: Relationship between products and cost items
- **CostItemLog**: Audit trail for changes

#### 2. Controllers (`src/Http/Controllers/`)
- **Web Controllers**: Handle browser requests
- **API Controllers**: Handle AJAX and external API requests
- **RESTful design** with standard CRUD operations

#### 3. Services (`src/Services/`)
- **CacheService**: Centralized cache management
- **ForecastService**: Cost projection calculations
- **CalculationEngine**: Core cost calculation logic

#### 4. Views (`src/resources/views/`)
- **Blade templates** with component-based design
- **Bootstrap 4/5** for consistent styling
- **Responsive design** for mobile compatibility

## Data Flow

### Cost Calculation Flow
```
User Input
    ↓
Controller receives request
    ↓
Service validates parameters
    ↓
Model fetches data
    ↓
Calculation engine processes
    ↓
Cache stores result
    ↓
Response returned to user
```

### Example: Product Cost Calculation
```php
// 1. Controller receives request
public function calculate(Request $request, Product $product) {
    // 2. Service validates and processes
    $result = app(CalculationService::class)
        ->calculateTotalCost($product, $request->all());
    
    // 3. Return formatted response
    return response()->json($result);
}

// 4. Service orchestrates the calculation
class CalculationService {
    public function calculateTotalCost($product, $parameters) {
        // Get allocations with cost items
        $allocations = $product->allocations()
            ->with('costItem')
            ->get();
        
        $totalCost = 0;
        foreach ($allocations as $allocation) {
            $totalCost += $this->calculateAllocationCost(
                $allocation, 
                $parameters
            );
        }
        
        return [
            'total_cost' => $totalCost,
            'breakdown' => $this->getBreakdown($allocations, $parameters),
            'currency' => 'NOK'
        ];
    }
}
```

## Database Design

### Entity Relationship Diagram
```
CostItem (1) ──────┐
                   │
                   │ (1:N)
                   │
                   ▼
             CostAllocation (N) ──────► Product (1)
                   │
                   │ (1:N)
                   │
                   ▼
             CostItemLog (N)
```

### Key Relationships
- **CostItem** → **CostAllocation** (One-to-Many)
- **Product** → **CostAllocation** (One-to-Many)
- **CostAllocation** → **CostItemLog** (One-to-Many)

## Calculation Engine

### Allocation Types
```php
abstract class AllocationCalculator {
    abstract public function calculate($allocation, $parameters);
}

class FixedAllocationCalculator extends AllocationCalculator {
    public function calculate($allocation, $parameters) {
        return $allocation->allocation_value;
    }
}

class PerUserAllocationCalculator extends AllocationCalculator {
    public function calculate($allocation, $parameters) {
        $userCount = $parameters['user_count'] ?? 1;
        return $allocation->allocation_value * $userCount;
    }
}

class PerResourceUnitAllocationCalculator extends AllocationCalculator {
    public function calculate($allocation, $parameters) {
        $resourceUnits = $parameters['resource_units'] ?? 1;
        return $allocation->allocation_value * $resourceUnits;
    }
}
```

### Period Normalization
```php
class PeriodCalculator {
    const PERIODS = [
        'minute' => 1,
        'hour' => 60,
        'day' => 1440,
        'week' => 10080,
        'month' => 43200,  // 30 days average
        'year' => 525600   // 365 days
    ];
    
    public function normalizeToMinutes($price, $period) {
        return $price / self::PERIODS[$period];
    }
    
    public function convertTo($price, $fromPeriod, $toPeriod) {
        $pricePerMinute = $this->normalizeToMinutes($price, $fromPeriod);
        return $pricePerMinute * self::PERIODS[$toPeriod];
    }
}
```

## Caching Strategy

### Cache Layers
1. **Query Cache**: Database query results
2. **Calculation Cache**: Expensive calculations
3. **View Cache**: Rendered HTML fragments
4. **Session Cache**: User-specific data

### Cache Implementation
```php
class CacheService {
    const TTL = 3600; // 1 hour
    
    public static function getCachedCalculation($productId, $parameters) {
        $key = "product_calculation_{$productId}_" . md5(serialize($parameters));
        
        return Cache::remember($key, self::TTL, function() use ($productId, $parameters) {
            return app(CalculationService::class)
                ->calculateTotalCost($productId, $parameters);
        });
    }
    
    public static function clearProductCaches($productId) {
        Cache::tags(["product_{$productId}"])->flush();
    }
}
```

## Security Considerations

### Input Validation
```php
class ProductController extends Controller {
    public function store(Request $request) {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'calculation_model' => 'required|in:per_user,per_resource,fixed_price',
            'expected_users' => 'nullable|integer|min:1|max:10000'
        ]);
        
        // Additional business logic validation
        if ($validated['calculation_model'] === 'per_user' && !isset($validated['expected_users'])) {
            throw new ValidationException('Expected users required for per-user calculation');
        }
    }
}
```

### Authorization
```php
class ProductController extends Controller {
    public function __construct() {
        $this->middleware('can:view_cost_calculator');
        $this->middleware('can:edit_cost_calculator')->except(['index', 'show']);
        $this->middleware('can:delete_cost_calculator')->only(['destroy']);
    }
}
```

## Performance Optimization

### Database Optimization
- **Eager Loading**: Prevent N+1 queries
- **Indexing**: Optimize frequent queries
- **Query Scoping**: Efficient data filtering

```php
// Good: Eager loading
$products = Product::with(['allocations.costItem', 'category'])->get();

// Bad: N+1 queries
$products = Product::all();
foreach ($products as $product) {
    $product->allocations; // Additional query per product
}
```

### Memory Management
```php
// Use chunk() for large datasets
CostItem::chunk(100, function ($costItems) {
    foreach ($costItems as $costItem) {
        // Process each item
    }
});
```

## Error Handling

### Exception Hierarchy
```
Exception
├── BusinessLogicException
│   ├── InvalidCalculationException
│   └── InsufficientDataException
├── ValidationException (Laravel)
└── DatabaseException
```

### Error Response Format
```php
{
    "success": false,
    "error": {
        "type": "InvalidCalculationException",
        "message": "Cannot calculate cost without allocation data",
        "code": "CALC_001"
    },
    "debug": {
        "file": "CalculationService.php",
        "line": 42,
        "trace": "..."
    }
}
```

## Testing Strategy

### Test Pyramid
```
                   ▲
                  /E2E\     ← Few, slow, high confidence
                 /─────\
                /Feature\   ← Some, medium speed
               /─────────\
              /Unit Tests\ ← Many, fast, focused
             /───────────\
```

### Test Categories
1. **Unit Tests**: Model methods, services, calculations
2. **Feature Tests**: Controller endpoints, user workflows
3. **Integration Tests**: Database interactions, external APIs
4. **Browser Tests**: Full user scenarios (optional)

## Extension Points

### Adding New Allocation Types
1. Create calculator class implementing `AllocationCalculatorInterface`
2. Register in `AllocationCalculatorFactory`
3. Add database enum value
4. Update frontend dropdown options

### Adding New Calculation Models
1. Extend `CalculationService` with new method
2. Add to product model enum
3. Update validation rules
4. Create corresponding frontend logic

### Adding New Export Formats
1. Create exporter class implementing `ExporterInterface`
2. Register in `ExportService`
3. Add route and controller method
4. Update frontend export options

---

*This architecture supports scalability, maintainability, and extensibility while following Laravel best practices.*
