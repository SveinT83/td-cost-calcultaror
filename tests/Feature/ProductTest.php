<?php

namespace TronderData\TdCostCalcultaror\Tests\Feature;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use TronderData\TdCostCalcultaror\Models\Product;
use TronderData\TdCostCalcultaror\Models\CostItem;
use TronderData\TdCostCalcultaror\Models\CostAllocation;
use App\Models\User;

class ProductTest extends TestCase
{
    use RefreshDatabase;
    
    /** @test */
    public function it_can_calculate_product_total_cost()
    {
        // Create a test user
        $user = User::factory()->create();
        
        // Create cost items
        $costItem1 = CostItem::create([
            'name' => 'Server Cost',
            'price' => 100,
            'period' => 'month',
            'created_by' => $user->id,
        ]);
        
        $costItem2 = CostItem::create([
            'name' => 'License Cost',
            'price' => 50,
            'period' => 'month',
            'created_by' => $user->id,
        ]);
        
        // Create a product
        $product = Product::create([
            'name' => 'Test Product',
            'description' => 'A test product',
            'created_by' => $user->id,
        ]);
        
        // Create allocations
        CostAllocation::create([
            'product_id' => $product->id,
            'cost_item_id' => $costItem1->id,
            'amount' => 2, // 2 servers
        ]);
        
        CostAllocation::create([
            'product_id' => $product->id,
            'cost_item_id' => $costItem2->id,
            'amount' => 5, // 5 licenses
        ]);
        
        // Calculate total cost (should be 2*100 + 5*50 = 200 + 250 = 450)
        $totalCost = $product->calculateTotalCost();
        
        $this->assertEquals(450, $totalCost);
    }
    
    /** @test */
    public function it_can_add_cost_item_to_product()
    {
        // Create a test user
        $user = User::factory()->create();
        $this->actingAs($user);
        
        // Create a cost item
        $costItem = CostItem::create([
            'name' => 'Test Cost Item',
            'price' => 100,
            'period' => 'month',
            'created_by' => $user->id,
        ]);
        
        // Create a product
        $product = Product::create([
            'name' => 'Test Product',
            'description' => 'A test product',
            'created_by' => $user->id,
        ]);
        
        // Add cost item to product
        $data = [
            'cost_item_id' => $costItem->id,
            'amount' => 3,
        ];
        
        $response = $this->post(route('td-cost-calcultaror.products.attach-cost-item', $product->id), $data);
        
        $response->assertRedirect(route('td-cost-calcultaror.products.edit', $product->id));
        
        $this->assertDatabaseHas('cost_allocations', [
            'product_id' => $product->id,
            'cost_item_id' => $costItem->id,
            'amount' => 3,
        ]);
    }
}
