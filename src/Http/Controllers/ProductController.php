<?php

namespace TronderData\TdCostCalcultaror\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\Controller;
use TronderData\TdCostCalcultaror\Models\Product;
use TronderData\TdCostCalcultaror\Models\CostItem;
use TronderData\TdCostCalcultaror\Models\CostAllocation;
use TronderData\TdCostCalcultaror\Services\CacheService;
use TronderData\Categories\Models\Category;

class ProductController extends Controller
{
    /**
     * Create a new controller instance.
     */
    public function __construct()
    {
        // Middleware er allerede definert i route-filen
    }

    /**
     * Display a listing of the products.
     */
    public function index()
    {
        $products = Product::orderBy('name')->paginate(15);
        
        return view('td-cost-calcultaror::products.index', [
            'products' => $products,
            'categoryModuleAvailable' => CostItem::isCategoryModuleAvailable(),
            'calculation_models' => [
                'per_user' => __('td-cost-calcultaror::messages.calc_per_user'),
                'per_resource' => __('td-cost-calcultaror::messages.calc_per_resource'),
                'fixed_price' => __('td-cost-calcultaror::messages.calc_fixed_price'),
            ]
        ]);
    }
    
    /**
     * Process batch operations on products.
     */
    public function batchOperation(Request $request)
    {
        // Check permission
        if (!auth()->user()->can('edit_cost_calculator')) {
            return redirect()->route('td-cost-calcultaror.products.index')
                ->with('error', __('td-cost-calcultaror::messages.no_permission'));
        }
        
        // Validate input
        $validated = $request->validate([
            'items' => 'required|array',
            'items.*' => 'exists:products,id',
            'operation' => 'required|string|in:delete,update_calculation_model',
            'calculation_model' => 'nullable|required_if:operation,update_calculation_model|string|in:per_user,per_resource,fixed_price',
        ]);
        
        // Get items
        $items = Product::whereIn('id', $validated['items'])->get();
        
        if ($items->isEmpty()) {
            return redirect()->route('td-cost-calcultaror.products.index')
                ->with('warning', __('td-cost-calcultaror::messages.no_items_selected'));
        }
        
        $count = $items->count();
        
        // Process based on operation
        switch ($validated['operation']) {
            case 'delete':
                foreach ($items as $item) {
                    $item->delete(); // This will trigger any model events
                }
                return redirect()->route('td-cost-calcultaror.products.index')
                    ->with('success', __('td-cost-calcultaror::messages.batch_deleted', ['count' => $count]));
                    
            case 'update_calculation_model':
                foreach ($items as $item) {
                    $item->calculation_model = $validated['calculation_model'];
                    $item->save(); // This will trigger any model events
                }
                return redirect()->route('td-cost-calcultaror.products.index')
                    ->with('success', __('td-cost-calcultaror::messages.batch_updated_calculation_model', [
                        'count' => $count,
                        'model' => __('td-cost-calcultaror::messages.calc_' . $validated['calculation_model'])
                    ]));
        }
        
        return redirect()->route('td-cost-calcultaror.products.index')
            ->with('warning', __('td-cost-calcultaror::messages.invalid_operation'));
    }

    /**
     * Show the form for creating a new product.
     */
    public function create()
    {
        $categories = [];
        
        if (CostItem::isCategoryModuleAvailable()) {
            $categories = Category::orderBy('name')->pluck('name', 'id');
        }
        
        return view('td-cost-calcultaror::products.create', [
            'categoryModuleAvailable' => CostItem::isCategoryModuleAvailable(),
            'categories' => $categories,
            'calculation_models' => [
                'per_user' => __('td-cost-calcultaror::messages.calc_per_user'),
                'per_resource' => __('td-cost-calcultaror::messages.calc_per_resource'),
                'fixed_price' => __('td-cost-calcultaror::messages.calc_fixed_price'),
            ]
        ]);
    }

    /**
     * Store a newly created product in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'calculation_model' => 'required|in:per_user,per_resource,fixed_price',
        ]);

        // Create product with validated data
        $product = new Product($validated);
        $product->created_by = Auth::id();
        $product->save();
        
        // Handle additional metadata fields
        if ($request->has('metadata')) {
            foreach ($request->input('metadata') as $key => $value) {
                $product->setMetaField($key, $value);
            }
        }
        
        return redirect()->route('td-cost-calcultaror.products.index')
            ->with('success', __('td-cost-calcultaror::messages.successfully_created', ['item' => $product->name]));
    }

    /**
     * Display the specified product.
     */
    public function show(Product $product)
    {
        $allocations = $product->costAllocations()->with('costItem')->get();
        
        // Calculate total cost based on calculation model
        $totalCost = $product->calculateTotalCost();
        
        return view('td-cost-calcultaror::products.show', [
            'product' => $product,
            'allocations' => $allocations,
            'totalCost' => $totalCost,
            'categoryModuleAvailable' => CostItem::isCategoryModuleAvailable()
        ]);
    }

    /**
     * Show the form for editing the specified product.
     */
    public function edit(Product $product)
    {
        $categories = [];
        
        if (CostItem::isCategoryModuleAvailable()) {
            $categories = Category::orderBy('name')->pluck('name', 'id');
        }
        
        // Get all available cost items
        $availableCostItems = CostItem::orderBy('name')->get();
        
        // Get existing allocations
        $allocations = $product->costAllocations()->with('costItem')->get();
        
        return view('td-cost-calcultaror::products.edit', [
            'product' => $product,
            'categoryModuleAvailable' => CostItem::isCategoryModuleAvailable(),
            'categories' => $categories,
            'availableCostItems' => $availableCostItems,
            'allocations' => $allocations,
            'calculation_models' => [
                'per_user' => __('td-cost-calcultaror::messages.calc_per_user'),
                'per_resource' => __('td-cost-calcultaror::messages.calc_per_resource'),
                'fixed_price' => __('td-cost-calcultaror::messages.calc_fixed_price'),
            ]
        ]);
    }

    /**
     * Update the specified product in storage.
     */
    public function update(Request $request, Product $product)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'calculation_model' => 'required|in:per_user,per_resource,fixed_price',
        ]);
        
        $product->fill($validated);
        $product->save();
        
        // Handle metadata fields
        if ($request->has('metadata')) {
            foreach ($request->input('metadata') as $key => $value) {
                $product->setMetaField($key, $value);
            }
        }
        
        return redirect()->route('td-cost-calcultaror.products.index')
            ->with('success', __('td-cost-calcultaror::messages.successfully_updated', ['item' => $product->name]));
    }

    /**
     * Remove the specified product from storage.
     */
    public function destroy(Product $product)
    {
        $name = $product->name;
        
        // Delete all allocations associated with this product
        $product->costAllocations()->delete();
        
        $product->delete();
        
        return redirect()->route('td-cost-calcultaror.products.index')
            ->with('success', __('td-cost-calcultaror::messages.successfully_deleted', ['item' => $name]));
    }
    
    /**
     * Show the form for managing cost allocations for a product.
     */
    public function manageAllocations(Product $product)
    {
        $allocations = $product->costAllocations()->with('costItem')->get();
        $costItems = CostItem::orderBy('name')->get();
        
        return view('td-cost-calcultaror::products.allocations', [
            'product' => $product,
            'allocations' => $allocations,
            'costItems' => $costItems,
        ]);
    }
    
    /**
     * Add a cost allocation to a product.
     */
    public function addAllocation(Request $request, Product $product)
    {
        $validated = $request->validate([
            'cost_item_id' => 'required|exists:cost_items,id',
            'allocation_type' => 'required|in:per_user,fixed,per_resource_unit',
            'allocation_value' => 'required|numeric|min:0',
        ]);

        $allocation = new CostAllocation($validated);
        $allocation->product_id = $product->id;
        $allocation->save();
        
        return redirect()->route('td-cost-calcultaror.products.allocations', $product->id)
            ->with('success', __('td-cost-calcultaror::messages.allocation_added'));
    }
    
    /**
     * Remove a cost allocation from a product.
     */
    public function removeAllocation(Product $product, CostAllocation $allocation)
    {
        if ($allocation->product_id !== $product->id) {
            abort(403);
        }
        
        $allocation->delete();
        
        return redirect()->route('td-cost-calcultaror.products.allocations', $product->id)
            ->with('success', __('td-cost-calcultaror::messages.allocation_removed'));
    }
    
    /**
     * Attach a cost item to a product (create allocation from edit page).
     */
    public function attachCostItem(Request $request, Product $product)
    {
        $validated = $request->validate([
            'cost_item_id' => 'required|exists:cost_items,id',
            'allocation_type' => 'required|in:per_user,fixed,per_resource_unit',
            'allocation_value' => 'required|numeric|min:0',
        ]);

        $allocation = new CostAllocation($validated);
        $allocation->product_id = $product->id;
        $allocation->save();
        
        return redirect()->route('td-cost-calcultaror.products.edit', $product->id)
            ->with('success', __('td-cost-calcultaror::messages.cost_item_attached'));
    }
    
    /**
     * Detach a cost item from a product (delete allocation).
     *
     * @param Product $product
     * @param int $allocation
     * @return \Illuminate\Http\RedirectResponse
     */
    public function detachCostItem(Product $product, $allocation)
    {
        $allocation = CostAllocation::findOrFail($allocation);
        
        // Check that this allocation belongs to the given product
        if ($allocation->product_id !== $product->id) {
            return redirect()->route('td-cost-calcultaror.products.edit', $product->id)
                ->with('error', __('td-cost-calcultaror::messages.unauthorized'));
        }
        
        $allocation->delete();
        
        // Clear any cached values
        CacheService::clearProductCaches($product->id);
        
        return redirect()->route('td-cost-calcultaror.products.edit', $product->id)
            ->with('success', __('td-cost-calcultaror::messages.cost_item_detached'));
    }
    
    /**
     * Calculate costs for a product with specific parameters.
     */
    /**
     * Update a specific allocation for a product
     * 
     * @param Request $request
     * @param Product $product
     * @param int $allocation
     * @return \Illuminate\Http\Response
     */
    public function updateAllocation(Request $request, Product $product, $allocation)
    {
        $validated = $request->validate([
            'allocation_type' => 'required|string|in:fixed,per_user,per_resource_unit,percentage',
            'allocation_value' => 'required|numeric|min:0',
        ]);
        
        $alloc = CostAllocation::findOrFail($allocation);
        
        // Ensure the allocation belongs to the product
        if ($alloc->product_id != $product->id) {
            return redirect()->route('td-cost-calcultaror.products.edit', $product)
                ->with('error', __('td-cost-calcultaror::messages.allocation_not_found'));
        }
        
        $alloc->update([
            'allocation_type' => $validated['allocation_type'],
            'allocation_value' => $validated['allocation_value'],
        ]);
        
        // Clear any cached values
        CacheService::clearProductCaches($product->id);
        
        return redirect()->route('td-cost-calcultaror.products.edit', $product)
            ->with('success', __('td-cost-calcultaror::messages.allocation_updated_successfully'));
    }
    
    public function calculate(Request $request, Product $product)
    {
        $validated = $request->validate([
            'user_count' => 'nullable|integer|min:1',
            'resource_units' => 'nullable|integer|min:1',
        ]);
        
        $parameters = [];
        
        if (isset($validated['user_count'])) {
            $parameters['user_count'] = $validated['user_count'];
        }
        
        if (isset($validated['resource_units'])) {
            $parameters['resource_units'] = $validated['resource_units'];
        }
        
        $totalCost = $product->calculateTotalCost($parameters);
        $allocations = $product->costAllocations()->with('costItem')->get();
        
        $calculatedAllocations = [];
        foreach ($allocations as $allocation) {
            $calculatedAllocations[] = [
                'name' => $allocation->costItem->name,
                'cost' => $allocation->calculateCost($parameters),
                'type' => $allocation->allocation_type,
                'value' => $allocation->allocation_value,
            ];
        }
        
        return view('td-cost-calcultaror::products.calculate', [
            'product' => $product,
            'totalCost' => $totalCost,
            'allocations' => $calculatedAllocations,
            'parameters' => $parameters,
        ]);
    }
}
