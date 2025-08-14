<?php

namespace TronderData\TdCostCalcultaror\Services;

use Illuminate\Support\Facades\Cache;
use TronderData\TdCostCalcultaror\Models\CostItem;
use TronderData\TdCostCalcultaror\Models\Product;
use TronderData\TdCostCalcultaror\Models\CostAllocation;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection;

class CacheService
{
    /**
     * Cache key prefix for all keys used by this module
     */
    const CACHE_PREFIX = 'td_cost_calculator_';
    
    /**
     * Default cache duration in minutes
     */
    const DEFAULT_DURATION = 60; // 1 hour
    
    /**
     * Get the cache key for cost items list
     * 
     * @param array $filters Optional filters to include in the cache key
     * @return string
     */
    public static function getCostItemsKey(array $filters = []): string
    {
        $key = self::CACHE_PREFIX . 'cost_items';
        
        if (!empty($filters)) {
            $key .= '_' . md5(serialize($filters));
        }
        
        return $key;
    }
    
    /**
     * Get the cache key for a specific cost item
     * 
     * @param int $id Cost item ID
     * @return string
     */
    public static function getCostItemKey(int $id): string
    {
        return self::CACHE_PREFIX . 'cost_item_' . $id;
    }
    
    /**
     * Get the cache key for products list
     * 
     * @param array $filters Optional filters to include in the cache key
     * @return string
     */
    public static function getProductsKey(array $filters = []): string
    {
        $key = self::CACHE_PREFIX . 'products';
        
        if (!empty($filters)) {
            $key .= '_' . md5(serialize($filters));
        }
        
        return $key;
    }
    
    /**
     * Get the cache key for a specific product
     * 
     * @param int $id Product ID
     * @return string
     */
    public static function getProductKey(int $id): string
    {
        return self::CACHE_PREFIX . 'product_' . $id;
    }
    
    /**
     * Get the cache key for dashboard statistics
     * 
     * @param array $filters Optional filters to include in the cache key
     * @return string
     */
    public static function getDashboardStatsKey(array $filters = []): string
    {
        $key = self::CACHE_PREFIX . 'dashboard_stats';
        
        if (!empty($filters)) {
            $key .= '_' . md5(serialize($filters));
        }
        
        return $key;
    }
    
    /**
     * Get the cache key for product cost calculation
     * 
     * @param int $id Product ID
     * @param array $parameters Optional calculation parameters
     * @return string
     */
    public static function getProductCalculationKey(int $id, array $parameters = []): string
    {
        $key = self::CACHE_PREFIX . 'product_calculation_' . $id;
        
        if (!empty($parameters)) {
            $key .= '_' . md5(serialize($parameters));
        }
        
        return $key;
    }
    
    /**
     * Cache cost items
     * 
     * @param Collection $costItems Collection of cost items
     * @param array $filters Optional filters used to retrieve the items
     * @param int $minutes Duration to cache in minutes
     * @return void
     */
    public static function cacheCostItems(Collection $costItems, array $filters = [], int $minutes = self::DEFAULT_DURATION): void
    {
        Cache::put(self::getCostItemsKey($filters), $costItems, Carbon::now()->addMinutes($minutes));
    }
    
    /**
     * Get cached cost items
     * 
     * @param array $filters Optional filters to include in the cache key
     * @return Collection|null Collection of cost items or null if not cached
     */
    public static function getCachedCostItems(array $filters = []): ?Collection
    {
        return Cache::get(self::getCostItemsKey($filters));
    }
    
    /**
     * Cache a single cost item
     * 
     * @param CostItem $costItem Cost item to cache
     * @param int $minutes Duration to cache in minutes
     * @return void
     */
    public static function cacheCostItem(CostItem $costItem, int $minutes = self::DEFAULT_DURATION): void
    {
        Cache::put(self::getCostItemKey($costItem->id), $costItem, Carbon::now()->addMinutes($minutes));
    }
    
    /**
     * Get a cached cost item
     * 
     * @param int $id Cost item ID
     * @return CostItem|null Cost item or null if not cached
     */
    public static function getCachedCostItem(int $id): ?CostItem
    {
        return Cache::get(self::getCostItemKey($id));
    }
    
    /**
     * Cache products
     * 
     * @param Collection $products Collection of products
     * @param array $filters Optional filters used to retrieve the products
     * @param int $minutes Duration to cache in minutes
     * @return void
     */
    public static function cacheProducts(Collection $products, array $filters = [], int $minutes = self::DEFAULT_DURATION): void
    {
        Cache::put(self::getProductsKey($filters), $products, Carbon::now()->addMinutes($minutes));
    }
    
    /**
     * Get cached products
     * 
     * @param array $filters Optional filters to include in the cache key
     * @return Collection|null Collection of products or null if not cached
     */
    public static function getCachedProducts(array $filters = []): ?Collection
    {
        return Cache::get(self::getProductsKey($filters));
    }
    
    /**
     * Cache a single product
     * 
     * @param Product $product Product to cache
     * @param int $minutes Duration to cache in minutes
     * @return void
     */
    public static function cacheProduct(Product $product, int $minutes = self::DEFAULT_DURATION): void
    {
        Cache::put(self::getProductKey($product->id), $product, Carbon::now()->addMinutes($minutes));
    }
    
    /**
     * Get a cached product
     * 
     * @param int $id Product ID
     * @return Product|null Product or null if not cached
     */
    public static function getCachedProduct(int $id): ?Product
    {
        return Cache::get(self::getProductKey($id));
    }
    
    /**
     * Cache dashboard statistics
     * 
     * @param array $stats Statistics data
     * @param array $filters Optional filters used for the statistics
     * @param int $minutes Duration to cache in minutes
     * @return void
     */
    public static function cacheDashboardStats(array $stats, array $filters = [], int $minutes = self::DEFAULT_DURATION): void
    {
        Cache::put(self::getDashboardStatsKey($filters), $stats, Carbon::now()->addMinutes($minutes));
    }
    
    /**
     * Get cached dashboard statistics
     * 
     * @param array $filters Optional filters to include in the cache key
     * @return array|null Statistics data or null if not cached
     */
    public static function getCachedDashboardStats(array $filters = []): ?array
    {
        return Cache::get(self::getDashboardStatsKey($filters));
    }
    
    /**
     * Cache product cost calculation
     * 
     * @param int $productId Product ID
     * @param float $cost Calculated cost
     * @param array $parameters Optional calculation parameters
     * @param int $minutes Duration to cache in minutes
     * @return void
     */
    public static function cacheProductCalculation(int $productId, float $cost, array $parameters = [], int $minutes = self::DEFAULT_DURATION): void
    {
        Cache::put(self::getProductCalculationKey($productId, $parameters), $cost, Carbon::now()->addMinutes($minutes));
    }
    
    /**
     * Get cached product cost calculation
     * 
     * @param int $productId Product ID
     * @param array $parameters Optional calculation parameters
     * @return float|null Calculated cost or null if not cached
     */
    public static function getCachedProductCalculation(int $productId, array $parameters = []): ?float
    {
        return Cache::get(self::getProductCalculationKey($productId, $parameters));
    }
    
    /**
     * Clear all caches for a cost item
     * 
     * @param int $costItemId Cost item ID
     * @return void
     */
    public static function clearCostItemCaches(int $costItemId): void
    {
        Cache::forget(self::getCostItemKey($costItemId));
        
        // We also need to clear any list caches
        self::clearCostItemsListCache();
        
        // Clear dashboard stats as they include cost items
        self::clearDashboardStatsCache();
        
        // Clear any product calculations that might include this cost item
        self::clearProductCalculationCaches();
    }
    
    /**
     * Clear all caches for a product
     * 
     * @param int $productId Product ID
     * @return void
     */
    public static function clearProductCaches(int $productId): void
    {
        Cache::forget(self::getProductKey($productId));
        
        // Clear any cached calculations for this product
        Cache::forget(self::getProductCalculationKey($productId));
        
        // Clear product list caches
        self::clearProductsListCache();
        
        // Clear dashboard stats as they include products
        self::clearDashboardStatsCache();
    }
    
    /**
     * Clear all cost items list caches by using a pattern
     * 
     * @return void
     */
    public static function clearCostItemsListCache(): void
    {
        $pattern = self::CACHE_PREFIX . 'cost_items*';
        self::clearCacheByPattern($pattern);
    }
    
    /**
     * Clear all products list caches by using a pattern
     * 
     * @return void
     */
    public static function clearProductsListCache(): void
    {
        $pattern = self::CACHE_PREFIX . 'products*';
        self::clearCacheByPattern($pattern);
    }
    
    /**
     * Clear all dashboard stats caches by using a pattern
     * 
     * @return void
     */
    public static function clearDashboardStatsCache(): void
    {
        $pattern = self::CACHE_PREFIX . 'dashboard_stats*';
        self::clearCacheByPattern($pattern);
    }
    
    /**
     * Clear all product calculation caches by using a pattern
     * 
     * @return void
     */
    public static function clearProductCalculationCaches(): void
    {
        $pattern = self::CACHE_PREFIX . 'product_calculation*';
        self::clearCacheByPattern($pattern);
    }
    
    /**
     * Clear all caches for this module
     * 
     * @return void
     */
    public static function clearAllCaches(): void
    {
        $pattern = self::CACHE_PREFIX . '*';
        self::clearCacheByPattern($pattern);
    }
    
    /**
     * Clear caches by pattern
     * This is a helper method to handle different cache drivers
     * 
     * @param string $pattern Pattern to match keys against
     * @return void
     */
    protected static function clearCacheByPattern(string $pattern): void
    {
        // For cache drivers that support the "flush" method with a pattern
        if (method_exists(Cache::getStore(), 'flush')) {
            try {
                Cache::getStore()->flush($pattern);
                return;
            } catch (\Exception $e) {
                // Fallback to default behavior if flush with pattern not supported
            }
        }
        
        // For Redis, we could use a more specific approach
        // This is a simplified version that works for common cache drivers
        // For production use, we might need to implement specific logic for each driver
        Cache::flush();
    }
}
