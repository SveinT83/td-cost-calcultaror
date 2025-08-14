<?php

namespace Tests\Unit;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use TronderData\TdCostCalcultaror\Http\Controllers\Api\ExportApiController;
use TronderData\TdCostCalcultaror\Models\CostItem;
use TronderData\TdCostCalcultaror\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\User;

class ExportApiTest extends TestCase
{
    use RefreshDatabase;
    
    protected $user;
    protected $controller;
    
    protected function setUp(): void
    {
        parent::setUp();
        
        // Create a test user with permissions
        $this->user = User::factory()->create();
        $this->user->givePermissionTo('api_access_cost_calculator');
        
        // Create the controller
        $this->controller = new ExportApiController();
        
        // Authenticate the user
        Auth::login($this->user);
        
        // Create test data
        $this->createTestData();
    }
    
    protected function createTestData(): void
    {
        // Create 5 cost items
        CostItem::factory()->count(5)->create([
            'created_by' => $this->user->id
        ]);
        
        // Create 3 products
        Product::factory()->count(3)->create([
            'created_by' => $this->user->id
        ]);
    }
    
    /** @test */
    public function it_exports_cost_items_as_json()
    {
        // Create a request with JSON format
        $request = new Request([
            'format' => 'json'
        ]);
        
        // Get the response
        $response = $this->controller->exportCostItems($request);
        
        // Verify response
        $this->assertEquals(200, $response->getStatusCode());
        $this->assertEquals('application/json', $response->headers->get('Content-Type'));
        
        $content = json_decode($response->getContent(), true);
        $this->assertCount(5, $content);
        $this->assertArrayHasKey('name', $content[0]);
        $this->assertArrayHasKey('price', $content[0]);
    }
    
    /** @test */
    public function it_exports_products_as_json()
    {
        // Create a request with JSON format
        $request = new Request([
            'format' => 'json'
        ]);
        
        // Get the response
        $response = $this->controller->exportProducts($request);
        
        // Verify response
        $this->assertEquals(200, $response->getStatusCode());
        $this->assertEquals('application/json', $response->headers->get('Content-Type'));
        
        $content = json_decode($response->getContent(), true);
        $this->assertCount(3, $content);
        $this->assertArrayHasKey('name', $content[0]);
    }
    
    /** @test */
    public function it_exports_stats_as_json()
    {
        // Create a request with JSON format
        $request = new Request([
            'format' => 'json'
        ]);
        
        // Get the response
        $response = $this->controller->exportStats($request);
        
        // Verify response
        $this->assertEquals(200, $response->getStatusCode());
        $this->assertEquals('application/json', $response->headers->get('Content-Type'));
        
        $content = json_decode($response->getContent(), true);
        $this->assertArrayHasKey('costItemCount', $content);
        $this->assertArrayHasKey('productCount', $content);
        $this->assertArrayHasKey('totalCosts', $content);
    }
    
    /** @test */
    public function it_filters_cost_items_by_date_range()
    {
        // Create items with specific dates
        $olderItem = CostItem::factory()->create([
            'created_by' => $this->user->id,
            'created_at' => now()->subDays(30)
        ]);
        
        $newerItem = CostItem::factory()->create([
            'created_by' => $this->user->id,
            'created_at' => now()->subDays(5)
        ]);
        
        // Create a request with date filter
        $request = new Request([
            'format' => 'json',
            'date_from' => now()->subDays(10)->toDateString(),
            'date_to' => now()->toDateString()
        ]);
        
        // Get the response
        $response = $this->controller->exportCostItems($request);
        
        // Verify response
        $content = json_decode($response->getContent(), true);
        
        // Should only include the newer item
        $this->assertCount(1, $content);
        $this->assertEquals($newerItem->id, $content[0]['id']);
    }
}
