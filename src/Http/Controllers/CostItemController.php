<?php

namespace TronderData\TdCostCalcultaror\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\Controller;
use TronderData\TdCostCalcultaror\Models\CostItem;
use TronderData\Categories\Models\Category;

class CostItemController extends Controller
{
    /**
     * Create a new controller instance.
     */
    public function __construct()
    {
        // Middleware er allerede definert i route-filen
    }

    /**
     * Display a listing of the cost items.
     */
    public function index()
    {
        $costItems = CostItem::with('category')->orderBy('name')->paginate(15);
        
        return view('td-cost-calcultaror::cost-items.index', [
            'costItems' => $costItems,
            'categoryModuleAvailable' => CostItem::isCategoryModuleAvailable(),
            'periods' => [
                'month' => __('td-cost-calcultaror::messages.period_month'),
                'year' => __('td-cost-calcultaror::messages.period_year'),
                'hour' => __('td-cost-calcultaror::messages.period_hour'),
                'minute' => __('td-cost-calcultaror::messages.period_minute'),
            ]
        ]);
    }
    
    /**
     * Process batch operations on cost items.
     */
    public function batchOperation(Request $request)
    {
        // Check permission
        if (!auth()->user()->can('edit_cost_calculator')) {
            return redirect()->route('td-cost-calcultaror.cost-items.index')
                ->with('error', __('td-cost-calcultaror::messages.no_permission'));
        }
        
        // Validate input
        $validated = $request->validate([
            'items' => 'required|array',
            'items.*' => 'exists:cost_items,id',
            'operation' => 'required|string|in:delete,update_period,update_category',
            'period' => 'nullable|required_if:operation,update_period|string|in:month,year,hour,minute',
            'category_id' => 'nullable|required_if:operation,update_category|exists:categories,id',
        ]);
        
        // Get items
        $items = CostItem::whereIn('id', $validated['items'])->get();
        
        if ($items->isEmpty()) {
            return redirect()->route('td-cost-calcultaror.cost-items.index')
                ->with('warning', __('td-cost-calcultaror::messages.no_items_selected'));
        }
        
        $count = $items->count();
        
        // Process based on operation
        switch ($validated['operation']) {
            case 'delete':
                foreach ($items as $item) {
                    $item->delete(); // This will trigger any model events/logs
                }
                return redirect()->route('td-cost-calcultaror.cost-items.index')
                    ->with('success', __('td-cost-calcultaror::messages.batch_deleted', ['count' => $count]));
                    
            case 'update_period':
                foreach ($items as $item) {
                    $item->period = $validated['period'];
                    $item->save(); // This will trigger any model events/logs
                }
                return redirect()->route('td-cost-calcultaror.cost-items.index')
                    ->with('success', __('td-cost-calcultaror::messages.batch_updated_period', [
                        'count' => $count,
                        'period' => __('td-cost-calcultaror::messages.period_' . $validated['period'])
                    ]));
                    
            case 'update_category':
                // Only process if category module is available
                if (!CostItem::isCategoryModuleAvailable()) {
                    return redirect()->route('td-cost-calcultaror.cost-items.index')
                        ->with('error', __('td-cost-calcultaror::messages.category_not_available'));
                }
                
                foreach ($items as $item) {
                    $item->category_id = $validated['category_id'];
                    $item->save(); // This will trigger any model events/logs
                }
                
                $category = Category::find($validated['category_id']);
                $categoryName = $category ? $category->name : __('td-cost-calcultaror::messages.no_category');
                
                return redirect()->route('td-cost-calcultaror.cost-items.index')
                    ->with('success', __('td-cost-calcultaror::messages.batch_updated_category', [
                        'count' => $count,
                        'category' => $categoryName
                    ]));
        }
        
        return redirect()->route('td-cost-calcultaror.cost-items.index')
            ->with('warning', __('td-cost-calcultaror::messages.invalid_operation'));
    }

    /**
     * Show the form for creating a new cost item.
     */
    public function create()
    {
        $categories = [];
        
        if (CostItem::isCategoryModuleAvailable()) {
            $categories = Category::orderBy('name')->pluck('name', 'id');
        }
        
        return view('td-cost-calcultaror::cost-items.create', [
            'categories' => $categories,
            'categoryModuleAvailable' => CostItem::isCategoryModuleAvailable()
        ]);
    }

    /**
     * Store a newly created cost item in storage.
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
        $costItem->created_by = Auth::id();
        $costItem->save();
        
        // Handle metadata fields
        if ($request->has('metadata')) {
            foreach ($request->input('metadata') as $key => $value) {
                $costItem->setMetaField($key, $value);
            }
        }
        
        // Log the creation
        $costItem->logChange('create', null, $costItem->toArray());
        
        return redirect()->route('td-cost-calcultaror.cost-items.index')
            ->with('success', __('td-cost-calcultaror::messages.successfully_created', ['item' => $costItem->name]));
    }

    /**
     * Display the specified cost item.
     */
    public function show(CostItem $costItem)
    {
        // Load 5 most recent logs
        $logs = $costItem->logs()->latest('created_at')->take(5)->get();
        $allocations = $costItem->allocations()->with('product')->get();
        
        return view('td-cost-calcultaror::cost-items.show', [
            'costItem' => $costItem,
            'logs' => $logs,
            'allocations' => $allocations,
            'categoryModuleAvailable' => CostItem::isCategoryModuleAvailable()
        ]);
    }

    /**
     * Show the form for editing the specified cost item.
     */
    public function edit(CostItem $costItem)
    {
        $categories = [];
        
        if (CostItem::isCategoryModuleAvailable()) {
            $categories = Category::orderBy('name')->pluck('name', 'id');
        }
        
        return view('td-cost-calcultaror::cost-items.edit', [
            'costItem' => $costItem,
            'categories' => $categories,
            'categoryModuleAvailable' => CostItem::isCategoryModuleAvailable()
        ]);
    }

    /**
     * Update the specified cost item in storage.
     */
    public function update(Request $request, CostItem $costItem)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'price' => 'required|numeric|min:0',
            'period' => 'required|in:month,year,hour,minute',
            'category_id' => 'nullable|exists:categories,id',
        ]);
        
        // Store old values for logging
        $oldValues = $costItem->toArray();
        
        $costItem->fill($validated);
        $costItem->save();
        
        // Handle metadata fields
        if ($request->has('metadata')) {
            foreach ($request->input('metadata') as $key => $value) {
                $costItem->setMetaField($key, $value);
            }
        }
        
        // Log the change
        $costItem->logChange('update', $oldValues, $costItem->toArray());
        
        return redirect()->route('td-cost-calcultaror.cost-items.index')
            ->with('success', __('td-cost-calcultaror::messages.successfully_updated', ['item' => $costItem->name]));
    }

    /**
     * Remove the specified cost item from storage.
     */
    public function destroy(CostItem $costItem)
    {
        // Store values for logging before deletion
        $oldValues = $costItem->toArray();
        $name = $costItem->name;
        
        // Check if this cost item is being used in any allocations
        $hasAllocations = $costItem->allocations()->exists();
        if ($hasAllocations) {
            return redirect()->route('td-cost-calcultaror.cost-items.index')
                ->with('error', __('td-cost-calcultaror::messages.cannot_delete_in_use', ['item' => $name]));
        }
        
        // Log the deletion before actually deleting
        $costItem->logChange('delete', $oldValues, null);
        
        $costItem->delete();
        
        return redirect()->route('td-cost-calcultaror.cost-items.index')
            ->with('success', __('td-cost-calcultaror::messages.successfully_deleted', ['item' => $name]));
    }
}
