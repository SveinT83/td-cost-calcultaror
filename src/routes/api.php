<?php

use Illuminate\Support\Facades\Route;
use TronderData\TdCostCalcultaror\Http\Controllers\Api\CostItemApiController;
use TronderData\TdCostCalcultaror\Http\Controllers\Api\ProductApiController;
use TronderData\TdCostCalcultaror\Http\Controllers\Api\ExportApiController;
use TronderData\TdCostCalcultaror\Http\Controllers\Api\ForecastApiController;

Route::middleware(['api', 'auth:sanctum', 'td-cost-calculator-locale'])
    ->prefix('api/cost-calculator')
    ->name('api.td-cost-calcultaror.')
    ->group(function() {
        // Cost Items
        Route::get('/cost-items', [CostItemApiController::class, 'index'])->name('cost-items.index');
        Route::get('/cost-items/{id}', [CostItemApiController::class, 'show'])->name('cost-items.show');
        Route::post('/cost-items', [CostItemApiController::class, 'store'])->name('cost-items.store');
        Route::put('/cost-items/{id}', [CostItemApiController::class, 'update'])->name('cost-items.update');
        Route::delete('/cost-items/{id}', [CostItemApiController::class, 'destroy'])->name('cost-items.destroy');
        Route::get('/cost-items/{id}/logs', [CostItemApiController::class, 'logs'])->name('cost-items.logs');
        
        // Products
        Route::get('/products', [ProductApiController::class, 'index'])->name('products.index');
        Route::get('/products/{id}', [ProductApiController::class, 'show'])->name('products.show');
        Route::post('/products', [ProductApiController::class, 'store'])->name('products.store');
        Route::put('/products/{id}', [ProductApiController::class, 'update'])->name('products.update');
        Route::delete('/products/{id}', [ProductApiController::class, 'destroy'])->name('products.destroy');
        
        // Allocations
        Route::get('/products/{id}/allocations', [ProductApiController::class, 'allocations'])->name('products.allocations');
        Route::post('/products/{id}/allocations', [ProductApiController::class, 'addAllocation'])->name('products.allocations.add');
        Route::delete('/products/{productId}/allocations/{allocationId}', [ProductApiController::class, 'removeAllocation'])->name('products.allocations.remove');
        
        // Calculate
        Route::post('/products/{id}/calculate', [ProductApiController::class, 'calculate'])->name('products.calculate');
        
        // Export API endpoints
        Route::get('/export/cost-items', [ExportApiController::class, 'exportCostItems'])->name('export.cost-items');
        Route::get('/export/products', [ExportApiController::class, 'exportProducts'])->name('export.products');
        Route::get('/export/stats', [ExportApiController::class, 'exportStats'])->name('export.statistics');
        
        // Forecast API endpoints
        Route::get('/forecast', [ForecastApiController::class, 'forecast'])->name('forecast');
    });
