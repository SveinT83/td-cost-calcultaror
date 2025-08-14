{{-- Dashboard Forecast Component --}}
<div class="dashboard-forecast mb-4">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5>{{ __('td-cost-calcultaror::messages.cost_forecasting') }}</h5>
                    <div class="forecast-controls">
                        <select id="forecastMonths" class="form-control form-control-sm d-inline-block" style="width: auto;">
                            <option value="3">3 {{ __('td-cost-calcultaror::messages.months') }}</option>
                            <option value="6" selected>6 {{ __('td-cost-calcultaror::messages.months') }}</option>
                            <option value="12">12 {{ __('td-cost-calcultaror::messages.months') }}</option>
                        </select>
                    </div>
                </div>
                <div class="card-body">
                    <div class="forecast-chart-container">
                        <canvas id="forecastChart" height="300"></canvas>
                    </div>
                    <div class="forecast-metadata mt-3">
                        <div class="row">
                            <div class="col-md-3 text-center">
                                <div class="forecast-stat">
                                    <span class="forecast-stat-label">{{ __('td-cost-calcultaror::messages.trend') }}</span>
                                    <span id="trendDirection" class="forecast-stat-value">
                                        <span class="badge badge-info">{{ __('td-cost-calcultaror::messages.loading') }}</span>
                                    </span>
                                </div>
                            </div>
                            <div class="col-md-3 text-center">
                                <div class="forecast-stat">
                                    <span class="forecast-stat-label">{{ __('td-cost-calcultaror::messages.confidence') }}</span>
                                    <span id="confidenceLevel" class="forecast-stat-value">
                                        <div class="progress">
                                            <div class="progress-bar" role="progressbar" style="width: 0%">0%</div>
                                        </div>
                                    </span>
                                </div>
                            </div>
                            <div class="col-md-3 text-center">
                                <div class="forecast-stat">
                                    <span class="forecast-stat-label">{{ __('td-cost-calcultaror::messages.projected_change') }}</span>
                                    <span id="projectedChange" class="forecast-stat-value">
                                        <span class="badge badge-info">{{ __('td-cost-calcultaror::messages.loading') }}</span>
                                    </span>
                                </div>
                            </div>
                            <div class="col-md-3 text-center">
                                <div class="forecast-stat">
                                    <span class="forecast-stat-label">{{ __('td-cost-calcultaror::messages.forecast_updated') }}</span>
                                    <span id="forecastUpdated" class="forecast-stat-value">
                                        {{ now()->format('Y-m-d H:i') }}
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    let forecastChart = null;
    const forecastMonthsSelect = document.getElementById('forecastMonths');
    
    // Initial load
    loadForecastData();
    
    // Handle months change
    forecastMonthsSelect.addEventListener('change', function() {
        loadForecastData();
    });
    
    function loadForecastData() {
        const months = forecastMonthsSelect.value;
        const period = document.getElementById('period') ? document.getElementById('period').value : '';
        const categoryId = document.getElementById('category_id') ? document.getElementById('category_id').value : '';
        
        // Show loading state
        document.getElementById('trendDirection').innerHTML = '<span class="badge badge-info">{{ __("td-cost-calcultaror::messages.loading") }}</span>';
        document.getElementById('projectedChange').innerHTML = '<span class="badge badge-info">{{ __("td-cost-calcultaror::messages.loading") }}</span>';
        
        // Fetch forecast data from API
        fetch(`/api/cost-calculator/forecast?months=${months}&period=${period}&category_id=${categoryId}`, {
            headers: {
                'Accept': 'application/json',
                'X-Requested-With': 'XMLHttpRequest'
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                renderForecastChart(data.data);
                updateForecastMetadata(data.data);
            }
        })
        .catch(error => {
            console.error('Error fetching forecast data:', error);
        });
    }
    
    function renderForecastChart(forecastData) {
        // Prepare chart data
        const labels = [];
        const historicalValues = [];
        const forecastValues = [];
        
        // Add historical data
        Object.values(forecastData.historical).forEach(item => {
            labels.push(item.label);
            historicalValues.push(item.total);
            forecastValues.push(null); // No forecast for historical periods
        });
        
        // Add forecast data
        Object.values(forecastData.forecast).forEach(item => {
            labels.push(item.label);
            historicalValues.push(null); // No historical data for forecast periods
            forecastValues.push(item.forecasted_total);
        });
        
        // Create or update chart
        const ctx = document.getElementById('forecastChart').getContext('2d');
        
        if (forecastChart) {
            forecastChart.destroy();
        }
        
        forecastChart = new Chart(ctx, {
            type: 'line',
            data: {
                labels: labels,
                datasets: [
                    {
                        label: '{{ __("td-cost-calcultaror::messages.historical_costs") }}',
                        data: historicalValues,
                        borderColor: 'rgba(54, 162, 235, 1)',
                        backgroundColor: 'rgba(54, 162, 235, 0.2)',
                        pointRadius: 4,
                        tension: 0.1
                    },
                    {
                        label: '{{ __("td-cost-calcultaror::messages.forecasted_costs") }}',
                        data: forecastValues,
                        borderColor: 'rgba(255, 159, 64, 1)',
                        backgroundColor: 'rgba(255, 159, 64, 0.2)',
                        borderDash: [5, 5],
                        pointRadius: 4,
                        tension: 0.1
                    }
                ]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                interaction: {
                    mode: 'index',
                    intersect: false
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: {
                            callback: function(value) {
                                return new Intl.NumberFormat('{{ app()->getLocale() }}', {
                                    style: 'currency',
                                    currency: '{{ config('td-cost-calcultaror.currency', 'USD') }}',
                                    maximumSignificantDigits: 3
                                }).format(value);
                            }
                        }
                    }
                },
                plugins: {
                    tooltip: {
                        callbacks: {
                            label: function(context) {
                                let label = context.dataset.label || '';
                                if (label) {
                                    label += ': ';
                                }
                                if (context.parsed.y !== null) {
                                    label += new Intl.NumberFormat('{{ app()->getLocale() }}', {
                                        style: 'currency',
                                        currency: '{{ config('td-cost-calcultaror.currency', 'USD') }}'
                                    }).format(context.parsed.y);
                                }
                                return label;
                            }
                        }
                    }
                }
            }
        });
    }
    
    function updateForecastMetadata(forecastData) {
        // Update confidence level
        const confidenceEl = document.getElementById('confidenceLevel');
        const confidence = forecastData.metadata.confidence_level;
        confidenceEl.innerHTML = `
            <div class="progress">
                <div class="progress-bar ${getConfidenceColorClass(confidence)}" 
                     role="progressbar" 
                     style="width: ${confidence}%" 
                     aria-valuenow="${confidence}" 
                     aria-valuemin="0" 
                     aria-valuemax="100">
                    ${confidence}%
                </div>
            </div>
        `;
        
        // Update trend direction
        const trendEl = document.getElementById('trendDirection');
        const trend = forecastData.metadata.trend_direction;
        let trendClass = 'badge-info';
        let trendIcon = '';
        
        if (trend === 'increasing') {
            trendClass = 'badge-danger';
            trendIcon = '<i class="fas fa-arrow-up"></i> ';
        } else if (trend === 'decreasing') {
            trendClass = 'badge-success';
            trendIcon = '<i class="fas fa-arrow-down"></i> ';
        } else {
            trendClass = 'badge-info';
            trendIcon = '<i class="fas fa-arrows-alt-h"></i> ';
        }
        
        trendEl.innerHTML = `<span class="badge ${trendClass}">${trendIcon}${getTrendLabel(trend)}</span>`;
        
        // Calculate projected change
        const firstForecast = Object.values(forecastData.forecast)[0];
        const lastForecast = Object.values(forecastData.forecast)[Object.keys(forecastData.forecast).length - 1];
        const changePercent = firstForecast && lastForecast ? 
            ((lastForecast.forecasted_total - firstForecast.forecasted_total) / firstForecast.forecasted_total * 100) : 0;
        
        const changeEl = document.getElementById('projectedChange');
        let changeClass = 'badge-info';
        let changeIcon = '';
        
        if (changePercent > 0) {
            changeClass = 'badge-danger';
            changeIcon = '<i class="fas fa-arrow-up"></i> ';
        } else if (changePercent < 0) {
            changeClass = 'badge-success';
            changeIcon = '<i class="fas fa-arrow-down"></i> ';
        } else {
            changeClass = 'badge-info';
            changeIcon = '<i class="fas fa-arrows-alt-h"></i> ';
        }
        
        changeEl.innerHTML = `<span class="badge ${changeClass}">${changeIcon}${Math.abs(changePercent).toFixed(1)}%</span>`;
        
        // Update forecast date
        document.getElementById('forecastUpdated').innerText = new Date().toLocaleString('{{ app()->getLocale() }}');
    }
    
    function getConfidenceColorClass(confidence) {
        if (confidence >= 80) return 'bg-success';
        if (confidence >= 60) return 'bg-info';
        if (confidence >= 40) return 'bg-warning';
        return 'bg-danger';
    }
    
    function getTrendLabel(trend) {
        switch(trend) {
            case 'increasing':
                return '{{ __("td-cost-calcultaror::messages.trend_increasing") }}';
            case 'decreasing':
                return '{{ __("td-cost-calcultaror::messages.trend_decreasing") }}';
            default:
                return '{{ __("td-cost-calcultaror::messages.trend_stable") }}';
        }
    }
});
</script>
@endpush
