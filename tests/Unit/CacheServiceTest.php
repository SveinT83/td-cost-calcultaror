<?php

namespace Tests\Unit;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use TronderData\TdCostCalcultaror\Services\CacheService;
use TronderData\TdCostCalcultaror\Models\CostItem;
use TronderData\TdCostCalcultaror\Models\Product;
use App\Models\User;

class CacheServiceTest extends TestCase
{
    use RefreshDatabase;
    
    protected $user;
    
    protected function setUp(): void
    {
        parent::setUp();
        
        // Create a test user
        $this->user = User::factory()->create();
        
        // Create test data
        $this->createTestData();
        
        // Clear all cache before testing
        Cache::flush();
    }
    
    protected function createTestData(): void
    {
        // Create cost items
        CostItem::factory()->count(3)->create([
            'created_by' => $this->user->id
        ]);
        
        // Create products
        Product::factory()->count(2)->create([
            'created_by' => $this->user->id
        ]);
    }
    
    /** @test */
    public function it_caches_and_retrieves_dashboard_stats()
    {
        // Prepare test data
        $filters = ['period' => 'monthly'];
        $testStats = [
            'costItemCount' => 3,
            'productCount' => 2,
            'totalCosts' => 1500
        ];
        
        // Cache the stats
        CacheService::cacheDashboardStats($testStats, $filters, 60);
        
        // Retrieve the stats
        $cachedStats = CacheService::getCachedDashboardStats($filters);
        
        // Verify the data was cached and retrieved correctly
        $this->assertNotNull($cachedStats);
        $this->assertEquals($testStats['costItemCount'], $cachedStats['costItemCount']);
        $this->assertEquals($testStats['productCount'], $cachedStats['productCount']);
        $this->assertEquals($testStats['totalCosts'], $cachedStats['totalCosts']);
    }
    
    /** @test */
    public function it_clears_dashboard_stats_cache()
    {
        // Prepare and cache test data
        $filters = ['period' => 'monthly'];
        $testStats = [
            'costItemCount' => 3,
            'productCount' => 2,
            'totalCosts' => 1500
        ];
        
        CacheService::cacheDashboardStats($testStats, $filters, 60);
        
        // Verify data is cached
        $this->assertNotNull(CacheService::getCachedDashboardStats($filters));
        
        // Clear the cache
        CacheService::clearDashboardStatsCache();
        
        // Verify cache was cleared
        $this->assertNull(CacheService::getCachedDashboardStats($filters));
    }
    
    /** @test */
    public function it_caches_product_with_different_keys_for_different_products()
    {
        // Get two products
        $product1 = Product::first();
        $product2 = Product::skip(1)->first();
        
        // Create test data for both products
        $testData1 = ['name' => $product1->name, 'totalCost' => 1000];
        $testData2 = ['name' => $product2->name, 'totalCost' => 2000];
        
        // Cache both products
        CacheService::cacheProductData($product1->id, $testData1);
        CacheService::cacheProductData($product2->id, $testData2);
        
        // Retrieve both cached data sets
        $cachedData1 = CacheService::getCachedProductData($product1->id);
        $cachedData2 = CacheService::getCachedProductData($product2->id);
        
        // Verify both data sets were cached correctly
        $this->assertEquals($testData1, $cachedData1);
        $this->assertEquals($testData2, $cachedData2);
    }
    
    /** @test */
    public function it_invalidates_cache_when_product_is_updated()
    {
        // Get a product
        $product = Product::first();
        
        // Cache product data
        CacheService::cacheProductData($product->id, ['name' => $product->name]);
        
        // Verify data is cached
        $this->assertNotNull(CacheService::getCachedProductData($product->id));
        
        // Update the product (which should trigger cache invalidation)
        $product->name = 'Updated Product Name';
        $product->save();
        
        // Verify cache was invalidated
        $this->assertNull(CacheService::getCachedProductData($product->id));
    }
    
    /** @test */
    public function it_uses_filters_in_cache_key_for_dashboard_stats()
    {
        // Prepare two different filter sets
        $filters1 = ['period' => 'monthly'];
        $filters2 = ['period' => 'yearly'];
        
        // Create test data for both filter sets
        $testStats1 = ['costItemCount' => 3, 'productCount' => 2, 'totalCosts' => 1500];
        $testStats2 = ['costItemCount' => 2, 'productCount' => 1, 'totalCosts' => 1000];
        
        // Cache both data sets
        CacheService::cacheDashboardStats($testStats1, $filters1, 60);
        CacheService::cacheDashboardStats($testStats2, $filters2, 60);
        
        // Retrieve both cached data sets
        $cachedStats1 = CacheService::getCachedDashboardStats($filters1);
        $cachedStats2 = CacheService::getCachedDashboardStats($filters2);
        
        // Verify both data sets were cached correctly with different keys
        $this->assertEquals($testStats1, $cachedStats1);
        $this->assertEquals($testStats2, $cachedStats2);
    }
}
