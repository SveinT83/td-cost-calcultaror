<?php

namespace TronderData\TdCostCalcultaror\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;
use TronderData\Categories\Models\Category;
use App\Models\User;
use TronderData\TdCostCalcultaror\Traits\HasMetaData;
use TronderData\TdCostCalcultaror\Services\CacheService;

class CostItem extends Model
{
    use HasMetaData;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'price',
        'period',
        'category_id',
        'created_by',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'price' => 'decimal:2',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Get the user that created the cost item.
     */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Get the category that the cost item belongs to.
     * This relationship may be null if the category doesn't exist or td-category is not installed.
     */
    public function category(): BelongsTo
    {
        if (class_exists(Category::class)) {
            return $this->belongsTo(Category::class);
        }

        return $this->belongsTo(Model::class, 'category_id'); // Fallback relationship
    }

    /**
     * Get the logs for the cost item.
     */
    public function logs(): HasMany
    {
        return $this->hasMany(CostItemLog::class);
    }

    /**
     * Get the allocations for this cost item.
     */
    public function allocations(): HasMany
    {
        return $this->hasMany(CostAllocation::class);
    }
    
    /**
     * Get the cost allocations for this cost item (alias for allocations).
     */
    public function costAllocations(): HasMany
    {
        return $this->hasMany(CostAllocation::class);
    }
    
    /**
     * Get the products that use this cost item through allocations.
     * 
     * @return \Illuminate\Database\Eloquent\Relations\HasManyThrough
     */
    public function products()
    {
        return $this->hasManyThrough(
            Product::class,
            CostAllocation::class,
            'cost_item_id', // Foreign key on cost_allocations table
            'id', // Foreign key on products table
            'id', // Local key on cost_items table
            'product_id' // Local key on cost_allocations table
        );
    }

    /**
     * Check if td-category module is available.
     */
    public static function isCategoryModuleAvailable(): bool
    {
        return class_exists(Category::class);
    }

    /**
     * Log a change to this cost item.
     */
    public function logChange(string $action, array $oldValue = null, array $newValue = null): void
    {
        $log = new CostItemLog();
        $log->cost_item_id = $this->id;
        $log->user_id = auth()->id() ?? 0;
        $log->action = $action;
        $log->old_value = $oldValue;
        $log->new_value = $newValue;
        $log->save();
        
        // Clear cache when a cost item is changed
        CacheService::clearCostItemCaches($this->id);
    }
    
    /**
     * Boot the model.
     * 
     * @return void
     */
    protected static function boot()
    {
        parent::boot();
        
        // Clear cache when a cost item is updated
        static::updated(function ($costItem) {
            CacheService::clearCostItemCaches($costItem->id);
        });
        
        // Clear cache when a cost item is deleted
        static::deleted(function ($costItem) {
            CacheService::clearCostItemCaches($costItem->id);
        });
        
        // Clear cache when a cost item is created
        static::created(function ($costItem) {
            CacheService::clearCostItemsListCache();
            CacheService::clearDashboardStatsCache();
        });
    }
}
