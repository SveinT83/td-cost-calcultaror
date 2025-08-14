<?php

namespace TronderData\TdCostCalcultaror\Services;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Carbon;
use TronderData\TdCostCalcultaror\Models\CostItem;

class ForecastService
{
    /**
     * Generate cost forecast for the next N months
     * 
     * @param int $months Number of months to forecast
     * @param string|null $period Filter by period (monthly, yearly, etc)
     * @param int|null $categoryId Filter by category ID
     * @return array
     */
    public function generateCostForecast($months = 6, $period = null, $categoryId = null)
    {
        // Get historical data for the past 12 months to build the prediction model
        $historicalData = $this->getHistoricalCostData(12, $period, $categoryId);
        
        // Calculate trend factors based on historical data
        $trendFactors = $this->calculateTrendFactors($historicalData);
        
        // Generate forecast data
        $forecastData = $this->predictFutureCosts($historicalData, $trendFactors, $months);
        
        return [
            'historical' => $historicalData,
            'forecast' => $forecastData,
            'metadata' => [
                'confidence_level' => $this->calculateConfidenceLevel($historicalData),
                'trend_direction' => $trendFactors['direction'],
                'trend_strength' => $trendFactors['strength'],
                'forecast_months' => $months
            ]
        ];
    }
    
    /**
     * Retrieve historical cost data for the past N months
     */
    protected function getHistoricalCostData($months = 12, $period = null, $categoryId = null)
    {
        $startDate = now()->subMonths($months)->startOfMonth();
        $endDate = now()->endOfMonth();
        
        $result = [];
        
        // Loop through each month
        for ($date = clone $startDate; $date->lte($endDate); $date->addMonth()) {
            $monthKey = $date->format('Y-m');
            $monthLabel = $date->format('M Y');
            
            $query = CostItem::whereBetween('created_at', [
                $date->copy()->startOfMonth()->toDateString(),
                $date->copy()->endOfMonth()->toDateString()
            ])
            ->when($period, function($query, $period) {
                return $query->where('period', $period);
            })
            ->when($categoryId, function($query, $categoryId) {
                return $query->where('category_id', $categoryId);
            });
            
            $result[$monthKey] = [
                'label' => $monthLabel,
                'total' => $query->sum('price'),
                'count' => $query->count(),
                'timestamp' => $date->timestamp
            ];
        }
        
        return $result;
    }
    
    /**
     * Calculate trend factors based on historical data
     */
    protected function calculateTrendFactors($historicalData)
    {
        $values = array_column($historicalData, 'total');
        $timestamps = array_column($historicalData, 'timestamp');
        
        // Need at least 2 data points for trend calculation
        if (count($values) < 2) {
            return [
                'direction' => 'stable',
                'strength' => 0,
                'coefficient' => 0
            ];
        }
        
        // Simple linear regression to calculate trend
        $n = count($values);
        $sum_x = array_sum($timestamps);
        $sum_y = array_sum($values);
        $sum_xy = 0;
        $sum_xx = 0;
        
        for ($i = 0; $i < $n; $i++) {
            $sum_xy += $timestamps[$i] * $values[$i];
            $sum_xx += $timestamps[$i] * $timestamps[$i];
        }
        
        // Avoid division by zero
        if ($n * $sum_xx - $sum_x * $sum_x == 0) {
            $coefficient = 0;
        } else {
            $coefficient = ($n * $sum_xy - $sum_x * $sum_y) / ($n * $sum_xx - $sum_x * $sum_x);
        }
        
        // Determine trend direction and strength
        $direction = $coefficient > 0 ? 'increasing' : ($coefficient < 0 ? 'decreasing' : 'stable');
        
        // Calculate R-squared for trend strength (simple version)
        // Normalize coefficient to a 0-1 scale for strength
        $strength = min(1, abs($coefficient) / max(1, max($values)));
        
        return [
            'direction' => $direction,
            'strength' => $strength,
            'coefficient' => $coefficient
        ];
    }
    
    /**
     * Predict future costs based on historical data and trend factors
     */
    protected function predictFutureCosts($historicalData, $trendFactors, $months)
    {
        // Get the last historical data point
        $lastMonth = end($historicalData);
        $lastMonthDate = Carbon::createFromTimestamp($lastMonth['timestamp']);
        
        // Calculate average costs from the last 3 months (if available)
        $recentValues = array_slice($historicalData, -3);
        $baseValue = array_sum(array_column($recentValues, 'total')) / count($recentValues);
        
        $result = [];
        
        // Loop through future months
        for ($i = 1; $i <= $months; $i++) {
            $forecastDate = $lastMonthDate->copy()->addMonths($i);
            $monthKey = $forecastDate->format('Y-m');
            $monthLabel = $forecastDate->format('M Y');
            
            // Apply trend factor and some seasonal variation
            $trendEffect = $baseValue * $trendFactors['coefficient'] * $i;
            
            // Add some seasonal variation (higher costs in Q4, lower in Q1, etc.)
            $seasonalFactor = 1;
            $month = $forecastDate->month;
            
            if ($month >= 10) { // Q4: slight increase
                $seasonalFactor = 1.05;
            } elseif ($month <= 3) { // Q1: slight decrease
                $seasonalFactor = 0.95;
            }
            
            // Calculate forecasted cost with some random variation (±5%)
            $randomVariation = rand(95, 105) / 100;
            $forecastedCost = max(0, ($baseValue + $trendEffect) * $seasonalFactor * $randomVariation);
            
            $result[$monthKey] = [
                'label' => $monthLabel,
                'forecasted_total' => round($forecastedCost, 2),
                'confidence' => $this->getConfidenceForMonth($i),
                'timestamp' => $forecastDate->timestamp
            ];
        }
        
        return $result;
    }
    
    /**
     * Calculate confidence level for the forecast
     * Confidence decreases the further we predict into the future
     */
    protected function getConfidenceForMonth($monthsAhead)
    {
        // Simple linear decrease in confidence as we predict further into the future
        return max(0.5, 1 - ($monthsAhead / 20));
    }
    
    /**
     * Calculate overall confidence level for the forecast
     */
    protected function calculateConfidenceLevel($historicalData)
    {
        // More data points = higher confidence
        $dataPointsFactor = min(1, count($historicalData) / 12);
        
        // Check consistency of data (standard deviation relative to mean)
        $values = array_column($historicalData, 'total');
        $mean = array_sum($values) / max(1, count($values));
        
        $variance = 0;
        foreach ($values as $value) {
            $variance += pow($value - $mean, 2);
        }
        $stdDev = sqrt($variance / max(1, count($values)));
        $variationFactor = $mean > 0 ? min(1, 1 - ($stdDev / $mean) / 2) : 0.5;
        
        // Overall confidence is a combination of these factors
        $confidence = ($dataPointsFactor * 0.7) + ($variationFactor * 0.3);
        
        // Scale to a percentage
        return min(100, round($confidence * 100));
    }
}
