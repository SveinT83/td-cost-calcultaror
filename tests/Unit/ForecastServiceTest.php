<?php

namespace Tests\Unit;

use Tests\TestCase;
use TronderData\TdCostCalcultaror\Services\ForecastService;
use TronderData\TdCostCalcultaror\Models\CostItem;
use Mockery;
use Illuminate\Support\Carbon;

class ForecastServiceTest extends TestCase
{
    protected $forecastService;
    
    protected function setUp(): void
    {
        parent::setUp();
        $this->forecastService = new ForecastService();
    }
    
    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }
    
    /** @test */
    public function it_can_generate_forecast_data()
    {
        // Mock the historical data method to return test data
        $forecastService = Mockery::mock(ForecastService::class)->makePartial();
        $forecastService->shouldReceive('getHistoricalCostData')
            ->andReturn([
                '2025-01' => [
                    'label' => 'Jan 2025',
                    'total' => 1000,
                    'count' => 5,
                    'timestamp' => Carbon::create(2025, 1, 15)->timestamp
                ],
                '2025-02' => [
                    'label' => 'Feb 2025',
                    'total' => 1100,
                    'count' => 6,
                    'timestamp' => Carbon::create(2025, 2, 15)->timestamp
                ],
                '2025-03' => [
                    'label' => 'Mar 2025',
                    'total' => 1200,
                    'count' => 6,
                    'timestamp' => Carbon::create(2025, 3, 15)->timestamp
                ],
            ]);
        
        // Generate forecast data
        $result = $forecastService->generateCostForecast(3);
        
        // Check forecast structure
        $this->assertArrayHasKey('historical', $result);
        $this->assertArrayHasKey('forecast', $result);
        $this->assertArrayHasKey('metadata', $result);
        
        // Check metadata structure
        $this->assertArrayHasKey('confidence_level', $result['metadata']);
        $this->assertArrayHasKey('trend_direction', $result['metadata']);
        $this->assertArrayHasKey('trend_strength', $result['metadata']);
        
        // Check forecast data
        $this->assertCount(3, $result['forecast']);
    }
    
    /** @test */
    public function it_calculates_trend_factors_correctly()
    {
        $historicalData = [
            '2025-01' => ['total' => 100, 'timestamp' => Carbon::create(2025, 1, 15)->timestamp],
            '2025-02' => ['total' => 110, 'timestamp' => Carbon::create(2025, 2, 15)->timestamp],
            '2025-03' => ['total' => 120, 'timestamp' => Carbon::create(2025, 3, 15)->timestamp],
        ];
        
        $method = new \ReflectionMethod(ForecastService::class, 'calculateTrendFactors');
        $method->setAccessible(true);
        
        $result = $method->invoke($this->forecastService, $historicalData);
        
        $this->assertEquals('increasing', $result['direction']);
        $this->assertIsNumeric($result['strength']);
        $this->assertGreaterThan(0, $result['coefficient']);
    }
    
    /** @test */
    public function it_predicts_future_costs()
    {
        $historicalData = [
            '2025-01' => [
                'label' => 'Jan 2025',
                'total' => 1000,
                'count' => 5,
                'timestamp' => Carbon::create(2025, 1, 15)->timestamp
            ],
            '2025-02' => [
                'label' => 'Feb 2025',
                'total' => 1100,
                'count' => 6,
                'timestamp' => Carbon::create(2025, 2, 15)->timestamp
            ],
            '2025-03' => [
                'label' => 'Mar 2025',
                'total' => 1200,
                'count' => 6,
                'timestamp' => Carbon::create(2025, 3, 15)->timestamp
            ],
        ];
        
        $trendFactors = [
            'direction' => 'increasing',
            'strength' => 0.8,
            'coefficient' => 0.05
        ];
        
        $method = new \ReflectionMethod(ForecastService::class, 'predictFutureCosts');
        $method->setAccessible(true);
        
        $result = $method->invoke($this->forecastService, $historicalData, $trendFactors, 3);
        
        $this->assertCount(3, $result);
        
        // Check that first forecast month is after last historical month
        $forecastKeys = array_keys($result);
        $this->assertEquals('2025-04', $forecastKeys[0]);
    }
}
