<?php

namespace TronderData\TdCostCalcultaror\Http\Controllers\Api;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use TronderData\TdCostCalcultaror\Models\Product;
use TronderData\TdCostCalcultaror\Models\CostAllocation;
use TronderData\TdCostCalcultaror\Services\CacheService;

class ProductApiController extends Controller
{
    /**
     * Create a new controller instance.
     */
    public function __construct()
    {
        // Middleware er allerede definert i route-filen
    }

    /**
     * Get a list of all products.
     */
    public function index(Request $request)
    {
        $filters = [
            'calculation_model' => $request->input('calculation_model'),
            'search' => $request->input('search'),
            'sort_by' => $request->input('sort_by', 'created_at'),
            'sort_direction' => $request->input('sort_direction', 'desc'),
        ];
        
        // Check if we have a cached result for these filters
        $cachedProducts = CacheService::getCachedProducts($filters);
        
        if ($cachedProducts !== null) {
            return response()->json([
                'success' => true,
                'data' => $cachedProducts,
                'cached' => true,
            ]);
        }
        
        $query = Product::query();
        
        // Apply filters
        if (!empty($filters['calculation_model'])) {
            $query->where('calculation_model', $filters['calculation_model']);
        }
        
        if (!empty($filters['search'])) {
            $query->where('name', 'like', '%' . $filters['search'] . '%');
        }
        
        // Apply sorting
        $query->orderBy($filters['sort_by'], $filters['sort_direction']);
        
        // Get the products
        $products = $query->get();
        
        // Cache the results for future requests
        CacheService::cacheProducts($products, $filters);
        
        return response()->json([
            'success' => true,
            'data' => $products,
        ]);
    }
    
    /**
     * Get a specific product by ID.
     */
    public function show($id)
    {
        // Check if we have a cached product
        $product = CacheService::getCachedProduct($id);
        
        if (!$product) {
            // If not cached, load it from the database
            $product = Product::with('costAllocations.costItem')->find($id);
            
            if (!$product) {
                return response()->json([
                    'success' => false,
                    'message' => 'Product not found',
                ], 404);
            }
            
            // Cache the product for future requests
            CacheService::cacheProduct($product);
        }
        
        // Get metadata fields
        $metadata = $product->getMetaFields();
        
        // Format the response
        $response = $product->toArray();
        $response['metadata'] = $metadata;
        
        return response()->json([
            'success' => true,
            'data' => $response,
        ]);
    }
    
    /**
     * Create a new product.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'calculation_model' => 'required|in:per_user,per_resource,fixed_price',
        ]);
        
        $product = new Product($validated);
        $product->created_by = auth()->id() ?? 1; // Default to ID 1 if no auth
        $product->save();
        
        // Handle metadata fields
        if ($request->has('metadata') && is_array($request->metadata)) {
            foreach ($request->metadata as $key => $value) {
                $product->setMetaField($key, $value);
            }
        }
        
        return response()->json([
            'success' => true,
            'message' => 'Product created successfully',
            'data' => $product,
        ], 201);
    }
    
    /**
     * Update an existing product.
     */
    public function update(Request $request, $id)
    {
        $product = Product::find($id);
        
        if (!$product) {
            return response()->json([
                'success' => false,
                'message' => 'Product not found',
            ], 404);
        }
        
        $validated = $request->validate([
            'name' => 'sometimes|required|string|max:255',
            'calculation_model' => 'sometimes|required|in:per_user,per_resource,fixed_price',
        ]);
        
        $product->fill($validated);
        $product->save();
        
        // Handle metadata fields
        if ($request->has('metadata') && is_array($request->metadata)) {
            foreach ($request->metadata as $key => $value) {
                $product->setMetaField($key, $value);
            }
        }
        
        return response()->json([
            'success' => true,
            'message' => 'Product updated successfully',
            'data' => $product,
        ]);
    }
    
    /**
     * Delete a product.
     */
    public function destroy($id)
    {
        $product = Product::find($id);
        
        if (!$product) {
            return response()->json([
                'success' => false,
                'message' => 'Product not found',
            ], 404);
        }
        
        // Delete all allocations associated with this product
        $product->costAllocations()->delete();
        
        $product->delete();
        
        return response()->json([
            'success' => true,
            'message' => 'Product deleted successfully',
        ]);
    }
    
    /**
     * Get all cost allocations for a product.
     */
    public function allocations($id)
    {
        $product = Product::find($id);
        
        if (!$product) {
            return response()->json([
                'success' => false,
                'message' => 'Product not found',
            ], 404);
        }
        
        $allocations = $product->costAllocations()->with('costItem')->get();
        
        return response()->json([
            'success' => true,
            'data' => $allocations,
        ]);
    }
    
    /**
     * Add a cost allocation to a product.
     */
    public function addAllocation(Request $request, $id)
    {
        $product = Product::find($id);
        
        if (!$product) {
            return response()->json([
                'success' => false,
                'message' => 'Product not found',
            ], 404);
        }
        
        $validated = $request->validate([
            'cost_item_id' => 'required|exists:cost_items,id',
            'allocation_type' => 'required|in:per_user,fixed,per_resource_unit',
            'allocation_value' => 'required|numeric|min:0',
        ]);
        
        $allocation = new CostAllocation($validated);
        $allocation->product_id = $product->id;
        $allocation->save();
        
        return response()->json([
            'success' => true,
            'message' => 'Allocation added successfully',
            'data' => $allocation,
        ], 201);
    }
    
    /**
     * Remove a cost allocation from a product.
     */
    public function removeAllocation($productId, $allocationId)
    {
        $product = Product::find($productId);
        
        if (!$product) {
            return response()->json([
                'success' => false,
                'message' => 'Product not found',
            ], 404);
        }
        
        $allocation = CostAllocation::where('id', $allocationId)
            ->where('product_id', $productId)
            ->first();
        
        if (!$allocation) {
            return response()->json([
                'success' => false,
                'message' => 'Allocation not found',
            ], 404);
        }
        
        $allocation->delete();
        
        return response()->json([
            'success' => true,
            'message' => 'Allocation removed successfully',
        ]);
    }
    
    /**
     * Calculate the total cost for a product based on parameters.
     */
    public function calculate(Request $request, $id)
    {
        $product = Product::with('costAllocations.costItem')->find($id);
        
        if (!$product) {
            return response()->json([
                'success' => false,
                'message' => 'Product not found',
            ], 404);
        }
        
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
        
        // Calculate individual allocation costs
        $allocations = [];
        foreach ($product->costAllocations as $allocation) {
            $allocations[] = [
                'id' => $allocation->id,
                'cost_item' => $allocation->costItem ? $allocation->costItem->name : 'Unknown',
                'allocation_type' => $allocation->allocation_type,
                'allocation_value' => $allocation->allocation_value,
                'cost' => $allocation->calculateCost($parameters),
            ];
        }
        
        return response()->json([
            'success' => true,
            'data' => [
                'product' => $product->name,
                'calculation_model' => $product->calculation_model,
                'parameters' => $parameters,
                'total_cost' => $totalCost,
                'allocations' => $allocations,
            ],
        ]);
    }
}
