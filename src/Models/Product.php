<?php

namespace TronderData\TdCostCalcultaror\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;
use App\Models\User;
use TronderData\TdCostCalcultaror\Traits\HasMetaData;
use TronderData\TdCostCalcultaror\Services\CacheService;

class Product extends Model
{
    use HasMetaData;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'calculation_model',
        'created_by',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Get the user that created the product.
     */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Get the cost allocations for this product.
     */
    public function costAllocations(): HasMany
    {
        return $this->hasMany(CostAllocation::class);
    }

    /**
     * Get all cost items associated with this product through allocations.
     * 
     * @return \Illuminate\Database\Eloquent\Relations\HasManyThrough
     */
    public function costItems()
    {
        return $this->hasManyThrough(
            CostItem::class,
            CostAllocation::class,
            'product_id', // Foreign key on cost_allocations table
            'id', // Foreign key on cost_items table
            'id', // Local key on products table
            'cost_item_id' // Local key on cost_allocations table
        );
    }

    /**
     * Calculate the total cost for this product based on the calculation model and allocations.
     * This method uses caching to improve performance for repeated calculations.
     */
    public function calculateTotalCost(array $parameters = []): float
    {
        // Check if we have a cached result for this calculation
        $cachedResult = CacheService::getCachedProductCalculation($this->id, $parameters);
        
        if ($cachedResult !== null) {
            return $cachedResult;
        }
        
        $total = 0;

        // Get all allocations with their associated cost items
        $allocations = $this->costAllocations()->with('costItem')->get();
        
        foreach ($allocations as $allocation) {
            // Skip if no cost item is associated
            if (!$allocation->costItem) {
                continue;
            }
            
            $itemCost = $allocation->costItem->price;
            
            // Apply allocation based on its type
            switch ($allocation->allocation_type) {
                case 'fixed':
                    $total += $itemCost * $allocation->allocation_value;
                    break;
                    
                case 'per_user':
                    $userCount = $parameters['user_count'] ?? 1;
                    $total += $itemCost * $allocation->allocation_value * $userCount;
                    break;
                    
                case 'per_resource_unit':
                    $resourceUnits = $parameters['resource_units'] ?? 1;
                    $total += $itemCost * $allocation->allocation_value * $resourceUnits;
                    break;
                    
                default:
                    $total += $itemCost;
            }
        }
        
        // Cache the calculation result
        CacheService::cacheProductCalculation($this->id, $total, $parameters);
        
        return $total;
    }
    
    /**
     * Calculate the monthly total cost for this product.
     * This converts all costs to a monthly basis regardless of the original period.
     */
    public function calculateMonthlyTotalCost(array $parameters = []): float
    {
        $total = 0;
        $allocations = $this->costAllocations()->with('costItem')->get();
        
        foreach ($allocations as $allocation) {
            if (!$allocation->costItem) {
                continue;
            }
            
            $itemCost = $allocation->costItem->price;
            $period = $allocation->costItem->period ?? 'month';
            
            // Convert to monthly cost
            switch ($period) {
                case 'minute':
                    $itemCost *= 43200; // 60 minutes * 24 hours * 30 days
                    break;
                case 'hour':
                    $itemCost *= 720; // 24 hours * 30 days
                    break;
                case 'day':
                    $itemCost *= 30; // 30 days
                    break;
                case 'year':
                    $itemCost /= 12; // divide by 12 months
                    break;
            }
            
            // Apply allocation based on its type
            switch ($allocation->allocation_type) {
                case 'fixed':
                    $total += $itemCost * $allocation->allocation_value;
                    break;
                case 'per_user':
                    $userCount = $parameters['user_count'] ?? 1;
                    $total += $itemCost * $allocation->allocation_value * $userCount;
                    break;
                case 'per_resource_unit':
                    $resourceUnits = $parameters['resource_units'] ?? 1;
                    $total += $itemCost * $allocation->allocation_value * $resourceUnits;
                    break;
                default:
                    $total += $itemCost;
            }
        }
        
        return $total;
    }
    
    /**
     * Calculate the yearly total cost for this product.
     * This converts all costs to a yearly basis regardless of the original period.
     */
    public function calculateYearlyTotalCost(array $parameters = []): float
    {
        // Simply multiply the monthly cost by 12
        return $this->calculateMonthlyTotalCost($parameters) * 12;
    }
    
    /**
     * Calculate the cost for a specific cost item allocation.
     * 
     * @param CostAllocation $allocation The allocation to calculate cost for
     * @param array $parameters Optional parameters like user_count, resource_units
     * @return float The calculated cost
     */
    public function calculateCostItemAllocation(CostAllocation $allocation, array $parameters = []): float
    {
        // If we don't have expected users set in metadata, use a default value of 1
        $expectedUsers = $this->getMetaField('expected_users') ?? 1;
        
        // Build parameters if not provided
        if (empty($parameters)) {
            $parameters = [
                'user_count' => intval($expectedUsers),
                'resource_units' => 1
            ];
        }
        
        // Use the allocation's own calculation method
        return $allocation->calculateCost($parameters);
    }
    
    /**
     * Boot the model.
     * 
     * @return void
     */
    protected static function boot()
    {
        parent::boot();
        
        // Clear cache when a product is updated
        static::updated(function ($product) {
            CacheService::clearProductCaches($product->id);
        });
        
        // Clear cache when a product is deleted
        static::deleted(function ($product) {
            CacheService::clearProductCaches($product->id);
        });
        
        // Clear cache when a product is created
        static::created(function ($product) {
            CacheService::clearProductsListCache();
            CacheService::clearDashboardStatsCache();
        });
    }
}
