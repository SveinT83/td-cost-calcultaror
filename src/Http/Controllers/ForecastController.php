<?php

namespace TronderData\TdCostCalcultaror\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use TronderData\TdCostCalcultaror\Services\ForecastService;

class ForecastController extends Controller
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
     * Display the forecast analysis page
     */
    public function index(Request $request)
    {
        // Get filters from the request
        $filters = [
            'period' => $request->input('period'),
            'category_id' => $request->input('category_id'),
            'months' => $request->input('months', 6),
        ];
        
        // Generate initial forecast data for the view
        $forecastData = $this->forecastService->generateCostForecast(
            $filters['months'],
            $filters['period'],
            $filters['category_id']
        );
        
        return view('td-cost-calcultaror::forecast', [
            'filters' => $filters,
            'forecastData' => $forecastData,
            'languages' => config('td-cost-calcultaror.languages.available', ['en' => 'English']),
            'currentLocale' => app()->getLocale(),
        ]);
    }
}
