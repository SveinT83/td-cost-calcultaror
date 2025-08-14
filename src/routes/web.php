<?php

use Illuminate\Support\Facades\Route;
use TronderData\TdCostCalcultaror\Http\Controllers\CostItemController;
use TronderData\TdCostCalcultaror\Http\Controllers\ProductController;
use TronderData\TdCostCalcultaror\Http\Controllers\DashboardController;
use TronderData\TdCostCalcultaror\Http\Controllers\ForecastController;
use TronderData\TdCostCalcultaror\Http\Controllers\ExportController;

Route::middleware(['web','auth', 'td-cost-calculator-locale'])
    ->prefix('admin/cost-calculator')
    ->name('td-cost-calcultaror.')
    ->group(function() {
        // Dashboard
        Route::get('/', [DashboardController::class, 'index'])->name('dashboard');
        Route::get('/clear-cache', [DashboardController::class, 'clearCache'])->name('clear-cache');
        
        // Forecast Analysis
        Route::get('/forecast', [ForecastController::class, 'index'])->name('forecast');
        
        // Cost Items
        Route::resource('cost-items', CostItemController::class);
        Route::post('cost-items-batch', [CostItemController::class, 'batchOperation'])->name('cost-items.batch');
        
        // Products
        Route::resource('products', ProductController::class);
        Route::post('products-batch', [ProductController::class, 'batchOperation'])->name('products.batch');
        
        // Allocations
        Route::get('products/{product}/allocations', [ProductController::class, 'manageAllocations'])
            ->name('products.allocations');
        
        Route::post('products/{product}/allocations', [ProductController::class, 'addAllocation'])
            ->name('products.allocations.add');
        
        Route::delete('products/{product}/allocations/{allocation}', [ProductController::class, 'removeAllocation'])
            ->name('products.allocations.remove');
        
        Route::put('products/{product}/allocations/{allocation}', [ProductController::class, 'updateAllocation'])
            ->name('products.update-allocation');
            
        // Attach cost item to product
        Route::post('products/{product}/attach-cost-item', [ProductController::class, 'attachCostItem'])
            ->name('products.attach-cost-item');
            
        // Detach cost item from product
        Route::delete('products/{product}/detach-cost-item/{allocation}', [ProductController::class, 'detachCostItem'])
            ->name('products.detach-cost-item');
        
        // Calculate
        Route::get('products/{product}/calculate', [ProductController::class, 'calculate'])
            ->name('products.calculate');
        
        Route::post('products/{product}/calculate', [ProductController::class, 'calculate'])
            ->name('products.calculate.post');
            
        // Export
        Route::get('/export/stats', [ExportController::class, 'exportStats'])
            ->name('export-stats');
            
        Route::get('/export/products', [ExportController::class, 'exportProducts'])
            ->name('export-products');
            
        Route::get('/export/cost-items', [ExportController::class, 'exportCostItems'])
            ->name('export-cost-items');
    });
