<?php

namespace TronderData\TdCostCalcultaror\Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\User;
use Spatie\Permission\Models\Permission;
use TronderData\TdCostCalcultaror\Models\CostItem;
use TronderData\TdCostCalcultaror\Models\Product;
use TronderData\TdCostCalcultaror\Models\CostAllocation;

class ExportApiTest extends TestCase
{
    use RefreshDatabase;

    protected $user;
    protected $costItems = [];
    protected $products = [];

    protected function setUp(): void
    {
        parent::setUp();

        // Create user with permissions
        $this->user = User::factory()->create();
        $permission = Permission::create(['name' => 'view_cost_calculator']);
        $this->user->givePermissionTo($permission);

        // Create sample cost items
        $this->costItems[] = CostItem::create([
            'name' => 'Developer Salary',
            'price' => 8000,
            'period' => 'monthly',
            'created_by' => $this->user->id
        ]);

        $this->costItems[] = CostItem::create([
            'name' => 'Server Costs',
            'price' => 500,
            'period' => 'monthly',
            'created_by' => $this->user->id
        ]);

        // Create sample product
        $this->products[] = Product::create([
            'name' => 'Web Application',
            'calculation_model' => 'percentage',
            'created_by' => $this->user->id
        ]);

        // Create cost allocation
        CostAllocation::create([
            'product_id' => $this->products[0]->id,
            'cost_item_id' => $this->costItems[0]->id,
            'percentage' => 50,
            'quantity' => null
        ]);

        CostAllocation::create([
            'product_id' => $this->products[0]->id,
            'cost_item_id' => $this->costItems[1]->id,
            'percentage' => 100,
            'quantity' => null
        ]);
    }

    /**
     * Test exporting cost items via API.
     *
     * @return void
     */
    public function testExportCostItems()
    {
        $response = $this->actingAs($this->user, 'sanctum')
            ->getJson('/api/cost-calculator/export/cost-items');

        $response->assertStatus(200);
        $response->assertJsonStructure([
            'success',
            'data' => [
                '*' => [
                    'id',
                    'name',
                    'price',
                    'period',
                    'created_by',
                    'created_at',
                    'updated_at'
                ]
            ],
            'meta' => [
                'total',
                'exported_at',
                'exported_by'
            ]
        ]);

        $response->assertJsonCount(2, 'data');
        $response->assertJsonFragment(['name' => 'Developer Salary']);
        $response->assertJsonFragment(['name' => 'Server Costs']);
    }

    /**
     * Test exporting products via API.
     *
     * @return void
     */
    public function testExportProducts()
    {
        $response = $this->actingAs($this->user, 'sanctum')
            ->getJson('/api/cost-calculator/export/products?include_allocations=true');

        $response->assertStatus(200);
        $response->assertJsonStructure([
            'success',
            'data' => [
                '*' => [
                    'id',
                    'name',
                    'calculation_model',
                    'total_cost',
                    'created_by',
                    'created_at',
                    'updated_at',
                    'allocations' => [
                        '*' => [
                            'id',
                            'percentage',
                            'quantity',
                            'cost_item',
                            'allocated_cost'
                        ]
                    ]
                ]
            ],
            'meta'
        ]);

        $response->assertJsonCount(1, 'data');
        $response->assertJsonFragment(['name' => 'Web Application']);
        $response->assertJsonCount(2, 'data.0.allocations');
    }

    /**
     * Test exporting statistics via API.
     *
     * @return void
     */
    public function testExportStats()
    {
        $response = $this->actingAs($this->user, 'sanctum')
            ->getJson('/api/cost-calculator/export/stats');

        $response->assertStatus(200);
        $response->assertJsonStructure([
            'success',
            'data' => [
                'summary' => [
                    'total_cost_items',
                    'total_products',
                    'total_allocations',
                    'total_cost',
                ],
                'costs_by_period',
                'top_cost_items',
                'top_products'
            ],
            'meta'
        ]);

        $response->assertJson([
            'data' => [
                'summary' => [
                    'total_cost_items' => 2,
                    'total_products' => 1,
                    'total_allocations' => 2,
                ]
            ]
        ]);
    }

    /**
     * Test filtering and sorting in cost items export.
     *
     * @return void
     */
    public function testExportCostItemsWithFilters()
    {
        $response = $this->actingAs($this->user, 'sanctum')
            ->getJson('/api/cost-calculator/export/cost-items?period=monthly&sort_by=price&sort_direction=desc');

        $response->assertStatus(200);
        $response->assertJsonCount(2, 'data');
        
        // First item should be the Developer Salary (highest price)
        $this->assertEquals('Developer Salary', $response['data'][0]['name']);
        $this->assertEquals(8000, $response['data'][0]['price']);
    }

    /**
     * Test export products with calculation model filter.
     *
     * @return void
     */
    public function testExportProductsWithFilters()
    {
        // Create another product with different calculation model
        Product::create([
            'name' => 'Mobile App',
            'calculation_model' => 'fixed',
            'created_by' => $this->user->id
        ]);

        $response = $this->actingAs($this->user, 'sanctum')
            ->getJson('/api/cost-calculator/export/products?calculation_model=percentage');

        $response->assertStatus(200);
        $response->assertJsonCount(1, 'data');
        $response->assertJsonFragment(['name' => 'Web Application']);
    }

    /**
     * Test unauthorized access to export endpoints.
     *
     * @return void
     */
    public function testUnauthorizedAccess()
    {
        // Create user without permissions
        $unauthorizedUser = User::factory()->create();

        $response = $this->actingAs($unauthorizedUser, 'sanctum')
            ->getJson('/api/cost-calculator/export/cost-items');

        $response->assertStatus(403);
    }
}
