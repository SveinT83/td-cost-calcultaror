<?php

namespace TronderData\TdCostCalcultaror\Http\Controllers\Api;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use TronderData\TdCostCalcultaror\Models\CostItem;
use TronderData\TdCostCalcultaror\Models\CostItemLog;

class CostItemApiController extends Controller
{
    /**
     * Create a new controller instance.
     */
    public function __construct()
    {
        // Middleware er allerede definert i route-filen
    }

    /**
     * Get a list of all cost items.
     */
    public function index()
    {
        $costItems = CostItem::with('category')->get();
        
        return response()->json([
            'success' => true,
            'data' => $costItems,
            'category_module_available' => CostItem::isCategoryModuleAvailable(),
        ]);
    }
    
    /**
     * Get a specific cost item by ID.
     */
    public function show($id)
    {
        $costItem = CostItem::with(['category', 'allocations.product'])->find($id);
        
        if (!$costItem) {
            return response()->json([
                'success' => false,
                'message' => 'Cost item not found',
            ], 404);
        }
        
        // Get metadata fields
        $metadata = $costItem->getMetaFields();
        
        // Format the response
        $response = $costItem->toArray();
        $response['metadata'] = $metadata;
        
        return response()->json([
            'success' => true,
            'data' => $response,
        ]);
    }
    
    /**
     * Create a new cost item.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'price' => 'required|numeric|min:0',
            'period' => 'required|in:month,year,hour,minute',
            'category_id' => 'nullable|exists:categories,id',
        ]);
        
        $costItem = new CostItem($validated);
        $costItem->created_by = auth()->id() ?? 1; // Default to ID 1 if no auth
        $costItem->save();
        
        // Handle metadata fields
        if ($request->has('metadata') && is_array($request->metadata)) {
            foreach ($request->metadata as $key => $value) {
                $costItem->setMetaField($key, $value);
            }
        }
        
        // Log the creation
        $costItem->logChange('create', null, $costItem->toArray());
        
        return response()->json([
            'success' => true,
            'message' => 'Cost item created successfully',
            'data' => $costItem,
        ], 201);
    }
    
    /**
     * Update an existing cost item.
     */
    public function update(Request $request, $id)
    {
        $costItem = CostItem::find($id);
        
        if (!$costItem) {
            return response()->json([
                'success' => false,
                'message' => 'Cost item not found',
            ], 404);
        }
        
        $validated = $request->validate([
            'name' => 'sometimes|required|string|max:255',
            'price' => 'sometimes|required|numeric|min:0',
            'period' => 'sometimes|required|in:month,year,hour,minute',
            'category_id' => 'nullable|exists:categories,id',
        ]);
        
        // Store old values for logging
        $oldValues = $costItem->toArray();
        
        $costItem->fill($validated);
        $costItem->save();
        
        // Handle metadata fields
        if ($request->has('metadata') && is_array($request->metadata)) {
            foreach ($request->metadata as $key => $value) {
                $costItem->setMetaField($key, $value);
            }
        }
        
        // Log the update
        $costItem->logChange('update', $oldValues, $costItem->toArray());
        
        return response()->json([
            'success' => true,
            'message' => 'Cost item updated successfully',
            'data' => $costItem,
        ]);
    }
    
    /**
     * Delete a cost item.
     */
    public function destroy($id)
    {
        $costItem = CostItem::find($id);
        
        if (!$costItem) {
            return response()->json([
                'success' => false,
                'message' => 'Cost item not found',
            ], 404);
        }
        
        // Check if this cost item is being used in any allocations
        $hasAllocations = $costItem->allocations()->exists();
        if ($hasAllocations) {
            return response()->json([
                'success' => false,
                'message' => 'Cannot delete cost item that is in use',
            ], 400);
        }
        
        // Store old values for logging
        $oldValues = $costItem->toArray();
        
        // Log the deletion before actually deleting
        $costItem->logChange('delete', $oldValues, null);
        
        $costItem->delete();
        
        return response()->json([
            'success' => true,
            'message' => 'Cost item deleted successfully',
        ]);
    }
    
    /**
     * Get logs for a specific cost item.
     */
    public function logs($id)
    {
        $costItem = CostItem::find($id);
        
        if (!$costItem) {
            return response()->json([
                'success' => false,
                'message' => 'Cost item not found',
            ], 404);
        }
        
        $logs = CostItemLog::where('cost_item_id', $id)
            ->with('user')
            ->orderBy('created_at', 'desc')
            ->get();
        
        return response()->json([
            'success' => true,
            'data' => $logs,
        ]);
    }
}
