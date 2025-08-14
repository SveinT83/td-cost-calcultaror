<?php

namespace TronderData\TdCostCalcultaror\Http\Controllers\Api;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use TronderData\TdCostCalcultaror\Models\CostItem;
use TronderData\TdCostCalcultaror\Models\Product;
use TronderData\TdCostCalcultaror\Models\CostAllocation;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class ExportApiController extends Controller
{
    /**
     * Create a new controller instance.
     * Note: Middleware is already defined in the routes file.
     */
    public function __construct()
    {
        // Middleware is defined in routes/api.php
        // $this->middleware('auth:sanctum');
        // $this->middleware('permission:view_cost_calculator');
    }

    /**
     * Export cost items data.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function exportCostItems(Request $request): JsonResponse
    {
        $request->validate([
            'format' => 'nullable|in:json,array',
            'period' => 'nullable|string',
            'category_id' => 'nullable|integer',
            'sort_by' => 'nullable|string|in:name,price,period,created_at',
            'sort_direction' => 'nullable|in:asc,desc',
        ]);

        $query = CostItem::with(['category', 'createdBy']);
        
        // Apply filters
        if ($request->has('period') && !empty($request->period)) {
            $query->where('period', $request->period);
        }
        
        if ($request->has('category_id') && !empty($request->category_id)) {
            $query->where('category_id', $request->category_id);
        }
        
        // Apply sorting
        $sortBy = $request->input('sort_by', 'created_at');
        $sortDirection = $request->input('sort_direction', 'desc');
        $query->orderBy($sortBy, $sortDirection);
        
        $costItems = $query->get();
        
        $data = $costItems->map(function ($item) {
            return [
                'id' => $item->id,
                'name' => $item->name,
                'price' => $item->price,
                'period' => $item->period,
                'category' => $item->category ? [
                    'id' => $item->category->id,
                    'name' => $item->category->name,
                ] : null,
                'created_by' => $item->createdBy ? [
                    'id' => $item->createdBy->id,
                    'name' => $item->createdBy->name,
                ] : null,
                'created_at' => $item->created_at->toIso8601String(),
                'updated_at' => $item->updated_at->toIso8601String(),
            ];
        });

        $format = $request->input('format', 'json');
        
        if ($format === 'array') {
            return response()->json($data->toArray());
        }
        
        return response()->json([
            'success' => true,
            'data' => $data,
            'meta' => [
                'total' => $costItems->count(),
                'exported_at' => Carbon::now()->toIso8601String(),
                'exported_by' => Auth::user()->name,
            ]
        ]);
    }
    
    /**
     * Export products data.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function exportProducts(Request $request): JsonResponse
    {
        $request->validate([
            'format' => 'nullable|in:json,array',
            'calculation_model' => 'nullable|string',
            'include_allocations' => 'nullable|boolean',
            'sort_by' => 'nullable|string|in:name,calculation_model,created_at',
            'sort_direction' => 'nullable|in:asc,desc',
        ]);
        
        $query = Product::with(['createdBy']);
        
        // Include allocations if requested
        if ($request->input('include_allocations', false)) {
            $query->with(['costAllocations.costItem.category']);
        }
        
        // Apply filters
        if ($request->has('calculation_model') && !empty($request->calculation_model)) {
            $query->where('calculation_model', $request->calculation_model);
        }
        
        // Apply sorting
        $sortBy = $request->input('sort_by', 'created_at');
        $sortDirection = $request->input('sort_direction', 'desc');
        $query->orderBy($sortBy, $sortDirection);
        
        $products = $query->get();
        
        $data = $products->map(function ($product) use ($request) {
            $result = [
                'id' => $product->id,
                'name' => $product->name,
                'calculation_model' => $product->calculation_model,
                'total_cost' => $product->calculateTotalCost(),
                'created_by' => $product->createdBy ? [
                    'id' => $product->createdBy->id,
                    'name' => $product->createdBy->name,
                ] : null,
                'created_at' => $product->created_at->toIso8601String(),
                'updated_at' => $product->updated_at->toIso8601String(),
            ];
            
            // Include allocations if requested
            if ($request->input('include_allocations', false)) {
                $result['allocations'] = $product->costAllocations->map(function ($allocation) {
                    return [
                        'id' => $allocation->id,
                        'percentage' => $allocation->percentage,
                        'quantity' => $allocation->quantity,
                        'cost_item' => [
                            'id' => $allocation->costItem->id,
                            'name' => $allocation->costItem->name,
                            'price' => $allocation->costItem->price,
                            'period' => $allocation->costItem->period,
                            'category' => $allocation->costItem->category ? $allocation->costItem->category->name : null,
                        ],
                        'allocated_cost' => $allocation->calculateAllocation(),
                    ];
                });
            }
            
            return $result;
        });

        $format = $request->input('format', 'json');
        
        if ($format === 'array') {
            return response()->json($data->toArray());
        }
        
        return response()->json([
            'success' => true,
            'data' => $data,
            'meta' => [
                'total' => $products->count(),
                'exported_at' => Carbon::now()->toIso8601String(),
                'exported_by' => Auth::user()->name,
            ]
        ]);
    }
    
    /**
     * Export statistics and summary data.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function exportStats(Request $request): JsonResponse
    {
        $request->validate([
            'format' => 'nullable|in:json,array',
            'period' => 'nullable|string',
        ]);
        
        // Get statistics
        $costItemsCount = CostItem::count();
        $productsCount = Product::count();
        $allocationsCount = CostAllocation::count();
        $totalCost = CostItem::sum('price');
        
        // Get costs by period
        $costsByPeriod = CostItem::selectRaw('period, SUM(price) as total')
            ->groupBy('period')
            ->get()
            ->map(function ($item) {
                return [
                    'period' => $item->period,
                    'total' => (float) $item->total,
                ];
            });
        
        // Get top cost items
        $topCostItems = CostItem::orderBy('price', 'desc')
            ->take(5)
            ->get()
            ->map(function ($item) {
                return [
                    'id' => $item->id,
                    'name' => $item->name,
                    'price' => $item->price,
                    'period' => $item->period,
                ];
            });
        
        // Get top products
        $topProducts = Product::withCount('costAllocations')
            ->orderByDesc('cost_allocations_count')
            ->take(5)
            ->get()
            ->map(function ($product) {
                return [
                    'id' => $product->id,
                    'name' => $product->name,
                    'calculation_model' => $product->calculation_model,
                    'total_cost' => $product->calculateTotalCost(),
                    'cost_items_count' => $product->cost_allocations_count,
                ];
            });
        
        $data = [
            'summary' => [
                'total_cost_items' => $costItemsCount,
                'total_products' => $productsCount,
                'total_allocations' => $allocationsCount,
                'total_cost' => $totalCost,
            ],
            'costs_by_period' => $costsByPeriod,
            'top_cost_items' => $topCostItems,
            'top_products' => $topProducts,
        ];
        
        $format = $request->input('format', 'json');
        
        if ($format === 'array') {
            return response()->json($data);
        }
        
        return response()->json([
            'success' => true,
            'data' => $data,
            'meta' => [
                'exported_at' => Carbon::now()->toIso8601String(),
                'exported_by' => Auth::user()->name,
            ]
        ]);
    }
}
