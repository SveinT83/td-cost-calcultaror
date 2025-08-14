<?php

namespace TronderData\TdCostCalcultaror\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use TronderData\TdCostCalcultaror\Models\CostItem;
use TronderData\TdCostCalcultaror\Models\Product;
use TronderData\TdCostCalcultaror\Services\CacheService;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class DashboardController extends Controller
{
    /**
     * Create a new controller instance.
     */
    public function __construct()
    {
        // Middleware er allerede definert i route-filen
    }
    
    /**
     * Generate colors for chart segments
     */
    protected function generateChartColors($count)
    {
        $baseColors = [
            'rgba(255, 99, 132, 0.7)',   // Red
            'rgba(54, 162, 235, 0.7)',   // Blue
            'rgba(255, 206, 86, 0.7)',   // Yellow
            'rgba(75, 192, 192, 0.7)',   // Green
            'rgba(153, 102, 255, 0.7)',  // Purple
            'rgba(255, 159, 64, 0.7)',   // Orange
            'rgba(201, 203, 207, 0.7)',  // Grey
        ];
        
        $colors = [];
        
        // Loop through the base colors and add enough colors for the chart
        for ($i = 0; $i < $count; $i++) {
            $colors[] = $baseColors[$i % count($baseColors)];
        }
        
        return $colors;
    }
    
    /**
     * Display the dashboard with cost statistics.
     */
    public function index(Request $request)
    {
        // Get filters from the request
        $filters = [
            'period' => $request->input('period'),
            'category_id' => $request->input('category_id'),
            'date_range' => $request->input('date_range'),
        ];
        
        // Check if we have cached dashboard stats
        $cachedStats = CacheService::getCachedDashboardStats($filters);
        
        if ($cachedStats !== null) {
            return view('td-cost-calcultaror::dashboard', $cachedStats);
        }
        
        // Generate the dashboard stats from scratch
        
        // Total number of cost items and products
        $costItemCount = CostItem::count();
        $productCount = Product::count();
        
        // Sum of all cost items
        $totalCosts = CostItem::sum('price');
        
        // Cost items by period - apply period filter if provided
        $costsByPeriodQuery = CostItem::select('period', DB::raw('SUM(price) as total'), DB::raw('COUNT(*) as count'))
            ->when($filters['period'], function($query, $period) {
                return $query->where('period', $period);
            })
            ->when($filters['category_id'], function($query, $categoryId) {
                return $query->where('category_id', $categoryId);
            });
            
        // Apply date range filter if provided
        if (!empty($filters['date_range'])) {
            $dateRange = explode(' - ', $filters['date_range']);
            if (count($dateRange) === 2) {
                $startDate = Carbon::createFromFormat('Y-m-d', $dateRange[0])->startOfDay();
                $endDate = Carbon::createFromFormat('Y-m-d', $dateRange[1])->endOfDay();
                
                $costsByPeriodQuery->whereBetween('created_at', [$startDate, $endDate]);
            }
        }
        
        $costsByPeriod = $costsByPeriodQuery->groupBy('period')->get();
        
        // Cost items by category if td-category is available
        $costsByCategory = [];
        $costsWithoutCategory = 0;
        
        if (CostItem::isCategoryModuleAvailable()) {
            $costsByCategoryQuery = CostItem::select(
                    'categories.name as category', 
                    'categories.id as category_id',
                    DB::raw('SUM(price) as total'),
                    DB::raw('COUNT(*) as count')
                )
                ->leftJoin('categories', 'cost_items.category_id', '=', 'categories.id')
                ->when($filters['period'], function($query, $period) {
                    return $query->where('cost_items.period', $period);
                })
                ->when($filters['date_range'], function($query) use ($filters) {
                    $dateRange = explode(' - ', $filters['date_range']);
                    if (count($dateRange) === 2) {
                        $startDate = Carbon::createFromFormat('Y-m-d', $dateRange[0])->startOfDay();
                        $endDate = Carbon::createFromFormat('Y-m-d', $dateRange[1])->endOfDay();
                        return $query->whereBetween('cost_items.created_at', [$startDate, $endDate]);
                    }
                    return $query;
                })
                ->groupBy('categories.name', 'categories.id')
                ->get();
                
            foreach ($costsByCategoryQuery as $item) {
                if ($item->category) {
                    $costsByCategory[$item->category] = [
                        'total' => $item->total,
                        'count' => $item->count,
                        'id' => $item->category_id
                    ];
                } else {
                    $costsWithoutCategory = $item->total;
                }
            }
        }
        
        // Cost distribution by month (last 12 months)
        $costTrend = [];
        $startDate = now()->subMonths(11)->startOfMonth();
        $endDate = now()->endOfMonth();
        
        for ($date = clone $startDate; $date->lte($endDate); $date->addMonth()) {
            $monthKey = $date->format('Y-m');
            $monthName = $date->format('M Y');
            
            $monthlyCostQuery = CostItem::whereBetween('created_at', [
                $date->copy()->startOfMonth()->toDateString(),
                $date->copy()->endOfMonth()->toDateString()
            ])
            ->when($filters['period'], function($query, $period) {
                return $query->where('period', $period);
            })
            ->when($filters['category_id'], function($query, $categoryId) {
                return $query->where('category_id', $categoryId);
            });
            
            $costTrend[$monthName] = $monthlyCostQuery->sum('price');
        }
        
        // Most expensive products
        $expensiveProductsQuery = Product::withCount(['costAllocations as total_cost' => function($query) use ($filters) {
                $query->select(DB::raw('SUM(cost_items.price * cost_allocations.allocation_value)'))
                    ->join('cost_items', 'cost_allocations.cost_item_id', '=', 'cost_items.id')
                    ->when($filters['period'], function($q, $period) {
                        return $q->where('cost_items.period', $period);
                    })
                    ->when($filters['category_id'], function($q, $categoryId) {
                        return $q->where('cost_items.category_id', $categoryId);
                    });
            }]);
            
        $expensiveProducts = $expensiveProductsQuery
            ->orderBy('total_cost', 'desc')
            ->take(5)
            ->get();
            
        // Detailed product stats - get average cost per product
        $productStatsQuery = Product::join('cost_allocations', 'products.id', '=', 'cost_allocations.product_id')
            ->join('cost_items', 'cost_allocations.cost_item_id', '=', 'cost_items.id')
            ->when($filters['period'], function($query, $period) {
                return $query->where('cost_items.period', $period);
            })
            ->when($filters['category_id'], function($query, $categoryId) {
                return $query->where('cost_items.category_id', $categoryId);
            });
            
        $productStats = [
            'avg_cost' => ($result = clone $productStatsQuery->select(DB::raw('AVG(cost_items.price * cost_allocations.allocation_value) as avg_cost'))->first()) ? $result->avg_cost : 0,
            'max_cost' => ($result = clone $productStatsQuery->select(DB::raw('MAX(cost_items.price * cost_allocations.allocation_value) as max_cost'))->first()) ? $result->max_cost : 0, 
            'min_cost' => ($result = clone $productStatsQuery->select(DB::raw('MIN(cost_items.price * cost_allocations.allocation_value) as min_cost'))->first()) ? $result->min_cost : 0,
        ];
        
        // Recent cost items with more details
        $recentCostItemsQuery = CostItem::with(['category'])
            ->select('cost_items.*', 
                DB::raw('(SELECT COUNT(*) FROM cost_allocations WHERE cost_allocations.cost_item_id = cost_items.id) as usage_count'))
            ->when($filters['period'], function($query, $period) {
                return $query->where('period', $period);
            })
            ->when($filters['category_id'], function($query, $categoryId) {
                return $query->where('category_id', $categoryId);
            });
            
        $recentCostItems = $recentCostItemsQuery
            ->latest()
            ->take(5)
            ->get();
        
        // Prepare the view data
        $viewData = [
            'costItemCount' => $costItemCount,
            'productCount' => $productCount,
            'totalCosts' => $totalCosts,
            'costsByPeriod' => $costsByPeriod,
            'costsByCategory' => $costsByCategory,
            'costsWithoutCategory' => $costsWithoutCategory,
            'costTrend' => $costTrend,
            'expensiveProducts' => $expensiveProducts,
            'recentCostItems' => $recentCostItems,
            'productStats' => $productStats,
            'categoryModuleAvailable' => CostItem::isCategoryModuleAvailable(),
            'filters' => $filters,
            'languages' => config('td-cost-calcultaror.languages.available', ['en' => 'English']),
            'currentLocale' => app()->getLocale(),
            
            // Chart data for the new dashboard analytics
            'categoryLabels' => array_keys($costsByCategory),
            'categoryData' => array_map(function($item) { return $item['total']; }, $costsByCategory),
            'categoryColors' => $this->generateChartColors(count($costsByCategory)),
            'periodLabels' => $costsByPeriod->pluck('period')->toArray(),
            'periodData' => $costsByPeriod->pluck('total')->toArray(),
            'trendLabels' => array_keys($costTrend),
            'trendDatasets' => [
                [
                    'label' => __('td-cost-calcultaror::messages.monthly_costs'),
                    'data' => array_values($costTrend),
                    'borderColor' => 'rgba(75, 192, 192, 1)',
                    'backgroundColor' => 'rgba(75, 192, 192, 0.2)',
                    'tension' => 0.4
                ]
            ],
            'topProductLabels' => $expensiveProducts->pluck('name')->toArray(),
            'topProductData' => $expensiveProducts->pluck('total_cost')->toArray(),
        ];
        
        // Cache the dashboard stats for future requests
        // We use a shorter cache duration (15 minutes) for the dashboard
        // since it's a frequently accessed and dynamic page
        CacheService::cacheDashboardStats($viewData, $filters, 15);
        
        return view('td-cost-calcultaror::dashboard', $viewData);
    }
    
    /**
     * Clear the dashboard cache.
     * This can be useful for admins to force a refresh of the data.
     */
    public function clearCache()
    {
        // Check if user has permission to clear cache
        if (!auth()->user()->can('edit_cost_calculator')) {
            return redirect()->route('td-cost-calcultaror.dashboard')
                ->with('error', 'You do not have permission to clear the cache.');
        }
        
        CacheService::clearDashboardStatsCache();
        
        return redirect()->route('td-cost-calcultaror.dashboard')
            ->with('success', 'Dashboard cache cleared successfully.');
    }
}
