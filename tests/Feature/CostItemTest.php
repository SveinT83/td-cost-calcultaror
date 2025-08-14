<?php

namespace TronderData\TdCostCalcultaror\Tests\Feature;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use TronderData\TdCostCalcultaror\Models\CostItem;
use App\Models\User;

class CostItemTest extends TestCase
{
    use RefreshDatabase;
    
    /** @test */
    public function it_can_create_a_cost_item()
    {
        // Create a test user
        $user = User::factory()->create();
        $this->actingAs($user);
        
        // Give permission to user if using a permission system
        // $user->givePermissionTo('edit_cost_calculator');
        
        $data = [
            'name' => 'Test Cost Item',
            'price' => 100,
            'period' => 'month',
        ];
        
        $response = $this->post(route('td-cost-calcultaror.cost-items.store'), $data);
        
        $response->assertRedirect(route('td-cost-calcultaror.cost-items.index'));
        
        $this->assertDatabaseHas('cost_items', [
            'name' => 'Test Cost Item',
            'price' => 100,
            'period' => 'month',
        ]);
    }
    
    /** @test */
    public function it_can_update_a_cost_item()
    {
        // Create a test user
        $user = User::factory()->create();
        $this->actingAs($user);
        
        // Create a cost item to update
        $costItem = CostItem::create([
            'name' => 'Original Cost Item',
            'price' => 100,
            'period' => 'month',
            'created_by' => $user->id,
        ]);
        
        $data = [
            'name' => 'Updated Cost Item',
            'price' => 200,
            'period' => 'year',
        ];
        
        $response = $this->put(route('td-cost-calcultaror.cost-items.update', $costItem->id), $data);
        
        $response->assertRedirect(route('td-cost-calcultaror.cost-items.index'));
        
        $this->assertDatabaseHas('cost_items', [
            'id' => $costItem->id,
            'name' => 'Updated Cost Item',
            'price' => 200,
            'period' => 'year',
        ]);
    }
    
    /** @test */
    public function it_can_delete_a_cost_item()
    {
        // Create a test user
        $user = User::factory()->create();
        $this->actingAs($user);
        
        // Create a cost item to delete
        $costItem = CostItem::create([
            'name' => 'Cost Item to Delete',
            'price' => 100,
            'period' => 'month',
            'created_by' => $user->id,
        ]);
        
        $response = $this->delete(route('td-cost-calcultaror.cost-items.destroy', $costItem->id));
        
        $response->assertRedirect(route('td-cost-calcultaror.cost-items.index'));
        
        $this->assertDatabaseMissing('cost_items', [
            'id' => $costItem->id,
        ]);
    }
}
