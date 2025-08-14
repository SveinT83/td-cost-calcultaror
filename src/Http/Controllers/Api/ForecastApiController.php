<?php

namespace TronderData\TdCostCalcultaror\Http\Controllers\Api;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use TronderData\TdCostCalcultaror\Services\ForecastService;

class ForecastApiController extends Controller
{
    protected $forecastService;
    
    /**
     * Create a new controller instance.
     */
    public function __construct(ForecastService $forecastService)
    {
        // Middleware er allerede definert i route-filen
        $this->forecastService = $forecastService;
    }
    
    /**
     * Get cost forecast data
     */
    public function forecast(Request $request)
    {
        // Validate the request
        $request->validate([
            'months' => 'integer|min:1|max:24',
            'period' => 'nullable|in:monthly,yearly,hourly,minute',
            'category_id' => 'nullable|integer|exists:categories,id',
        ]);
        
        // Get forecast parameters
        $months = $request->input('months', 6);
        $period = $request->input('period');
        $categoryId = $request->input('category_id');
        
        // Generate forecast data
        $forecastData = $this->forecastService->generateCostForecast($months, $period, $categoryId);
        
        return response()->json([
            'success' => true,
            'data' => $forecastData
        ]);
    }
}
