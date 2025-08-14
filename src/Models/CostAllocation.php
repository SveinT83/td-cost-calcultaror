<?php

namespace TronderData\TdCostCalcultaror\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use TronderData\TdCostCalcultaror\Traits\HasMetaData;
use TronderData\TdCostCalcultaror\Services\CacheService;

class CostAllocation extends Model
{
    use HasMetaData;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'product_id',
        'cost_item_id',
        'allocation_type',
        'allocation_value',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'allocation_value' => 'decimal:4',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Get the product that this allocation belongs to.
     */
    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    /**
     * Get the cost item that this allocation is for.
     */
    public function costItem(): BelongsTo
    {
        return $this->belongsTo(CostItem::class);
    }

    /**
     * Calculate the cost for this allocation based on its type and parameters.
     */
    public function calculateCost(array $parameters = []): float
    {
        if (!$this->costItem) {
            return 0;
        }
        
        $itemCost = $this->costItem->price;
        
        switch ($this->allocation_type) {
            case 'fixed':
                return $itemCost * $this->allocation_value;
                
            case 'per_user':
                $userCount = $parameters['user_count'] ?? 1;
                return $itemCost * $this->allocation_value * $userCount;
                
            case 'per_resource_unit':
                $resourceUnits = $parameters['resource_units'] ?? 1;
                return $itemCost * $this->allocation_value * $resourceUnits;
                
            default:
                return $itemCost;
        }
    }
    
    /**
     * Boot the model.
     * 
     * @return void
     */
    protected static function boot()
    {
        parent::boot();
        
        // When an allocation is created/updated/deleted, we need to clear caches for related models
        static::saved(function ($allocation) {
            // Clear product caches
            if ($allocation->product_id) {
                CacheService::clearProductCaches($allocation->product_id);
            }
            
            // Clear cost item caches
            if ($allocation->cost_item_id) {
                CacheService::clearCostItemCaches($allocation->cost_item_id);
            }
            
            // Clear dashboard stats
            CacheService::clearDashboardStatsCache();
        });
        
        static::deleted(function ($allocation) {
            // Clear product caches
            if ($allocation->product_id) {
                CacheService::clearProductCaches($allocation->product_id);
            }
            
            // Clear cost item caches
            if ($allocation->cost_item_id) {
                CacheService::clearCostItemCaches($allocation->cost_item_id);
            }
            
            // Clear dashboard stats
            CacheService::clearDashboardStatsCache();
        });
    }
}
