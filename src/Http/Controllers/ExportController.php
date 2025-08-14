<?php

namespace TronderData\TdCostCalcultaror\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Response;
use App\Http\Controllers\Controller;
use TronderData\TdCostCalcultaror\Models\CostItem;
use TronderData\TdCostCalcultaror\Models\Product;
use Maatwebsite\Excel\Facades\Excel;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Illuminate\Support\Collection;

class ExportController extends Controller
{
    /**
     * Create a new controller instance.
     * Note: Middleware is already defined in the routes file.
     */
    public function __construct()
    {
        // Middleware is defined in routes/web.php
        // $this->middleware('auth');
        // $this->middleware('permission:view_cost_calculator');
    }
    
    /**
     * Export statistics to Excel.
     */
    public function exportStats()
    {
        return Excel::download(new StatsExport(), 'cost-calculator-statistics-' . date('Y-m-d') . '.xlsx');
    }
    
    /**
     * Export products to CSV.
     */
    public function exportProducts()
    {
        return Excel::download(new ProductsExport(), 'products-' . date('Y-m-d') . '.csv');
    }
    
    /**
     * Export cost items to CSV.
     */
    public function exportCostItems()
    {
        return Excel::download(new CostItemsExport(), 'cost-items-' . date('Y-m-d') . '.csv');
    }
}

class StatsExport implements FromCollection, WithHeadings, ShouldAutoSize
{
    public function collection()
    {
        // Get Products with cost statistics
        $products = Product::withCount(['costAllocations as total_cost' => function($query) {
                $query->select(\DB::raw('SUM(cost_items.price * cost_allocations.amount)'))
                    ->join('cost_items', 'cost_allocations.cost_item_id', '=', 'cost_items.id');
            }])
            ->get();
            
        // Get Cost Items with allocation counts
        $costItems = CostItem::select('cost_items.*', 
                \DB::raw('(SELECT COUNT(*) FROM cost_allocations WHERE cost_allocations.cost_item_id = cost_items.id) as usage_count'))
                ->with('category')
                ->get();
                
        // Prepare data for export
        $data = new Collection();
        
        // Add product data
        $data->push(['PRODUCT STATISTICS', '', '', '', '']);
        $data->push(['Name', 'Description', 'Cost Items', 'Total Cost', 'Calculation Model']);
        
        foreach ($products as $product) {
            $data->push([
                $product->name,
                $product->description,
                $product->costAllocations->count(),
                $product->total_cost,
                $product->getMetaField('calculation_model') ?: 'fixed',
            ]);
        }
        
        // Add empty row as separator
        $data->push(['', '', '', '', '']);
        $data->push(['COST ITEM STATISTICS', '', '', '', '']);
        $data->push(['Name', 'Price', 'Period', 'Category', 'Usage Count']);
        
        foreach ($costItems as $item) {
            $data->push([
                $item->name,
                $item->price,
                $item->period,
                $item->category ? $item->category->name : 'None',
                $item->usage_count,
            ]);
        }
        
        return $data;
    }
    
    public function headings(): array
    {
        return [];  // Headings are included in the collection
    }
}

class ProductsExport implements FromCollection, WithHeadings, ShouldAutoSize
{
    public function collection()
    {
        $products = Product::withCount(['costAllocations as total_cost' => function($query) {
                $query->select(\DB::raw('SUM(cost_items.price * cost_allocations.amount)'))
                    ->join('cost_items', 'cost_allocations.cost_item_id', '=', 'cost_items.id');
            }])
            ->get();
            
        $data = new Collection();
        
        foreach ($products as $product) {
            $data->push([
                $product->id,
                $product->name,
                $product->description,
                $product->total_cost,
                $product->getMetaField('calculation_model') ?: 'fixed',
                $product->getMetaField('expected_users'),
                $product->getMetaField('notes'),
                $product->created_at,
                $product->updated_at,
            ]);
        }
        
        return $data;
    }
    
    public function headings(): array
    {
        return [
            'ID',
            'Name',
            'Description',
            'Total Cost',
            'Calculation Model',
            'Expected Users',
            'Notes',
            'Created At',
            'Updated At'
        ];
    }
}

class CostItemsExport implements FromCollection, WithHeadings, ShouldAutoSize
{
    public function collection()
    {
        $costItems = CostItem::select('cost_items.*', 
            \DB::raw('(SELECT COUNT(*) FROM cost_allocations WHERE cost_allocations.cost_item_id = cost_items.id) as usage_count'))
            ->with('category')
            ->get();
            
        $data = new Collection();
        
        foreach ($costItems as $item) {
            $data->push([
                $item->id,
                $item->name,
                $item->price,
                $item->period,
                $item->category ? $item->category->name : 'None',
                $item->usage_count,
                $item->created_at,
                $item->updated_at,
            ]);
        }
        
        return $data;
    }
    
    public function headings(): array
    {
        return [
            'ID',
            'Name',
            'Price',
            'Period',
            'Category',
            'Usage Count',
            'Created At',
            'Updated At'
        ];
    }
}
