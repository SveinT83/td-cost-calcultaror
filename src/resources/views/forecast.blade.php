@extends('layouts.app')

@section('pageHeader')
    <x-page-header pageHeaderTitle="{{ __('td-cost-calcultaror::messages.forecast') }}" />
@endsection

@section('content')
    <!-- Language Selector -->
    <div class="mb-4">
        <div class="card shadow">
            <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
                <h6 class="m-0 font-weight-bold text-primary">{{ __('td-cost-calcultaror::messages.language') }}</h6>
            </div>
            <div class="card-body">
                <div class="btn-group" role="group">
                    @foreach($languages as $code => $name)
                    <a href="{{ route('td-cost-calcultaror.forecast', ['locale' => $code] + request()->except('locale')) }}" 
                       class="btn {{ $currentLocale == $code ? 'btn-primary' : 'btn-secondary' }}">
                        {{ $name }}
                    </a>
                    @endforeach
                </div>
            </div>
        </div>
    </div>
    
    <!-- Filter Controls -->
    <div class="mb-4">
        <div class="card shadow">
            <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
                <h6 class="m-0 font-weight-bold text-primary">{{ __('td-cost-calcultaror::messages.forecast_filters') }}</h6>
            </div>
            <div class="card-body">
                <form action="{{ route('td-cost-calcultaror.forecast') }}" method="GET" class="row">
                    <div class="col-md-3 mb-2">
                        <label for="period">{{ __('td-cost-calcultaror::messages.period_filter') }}</label>
                        <select name="period" id="period" class="form-control form-control-sm">
                            <option value="">{{ __('td-cost-calcultaror::messages.all_periods') }}</option>
                            <option value="monthly" {{ isset($filters['period']) && $filters['period'] == 'monthly' ? 'selected' : '' }}>{{ __('td-cost-calcultaror::messages.monthly') }}</option>
                            <option value="yearly" {{ isset($filters['period']) && $filters['period'] == 'yearly' ? 'selected' : '' }}>{{ __('td-cost-calcultaror::messages.yearly') }}</option>
                            <option value="hourly" {{ isset($filters['period']) && $filters['period'] == 'hourly' ? 'selected' : '' }}>{{ __('td-cost-calcultaror::messages.hourly') }}</option>
                            <option value="minute" {{ isset($filters['period']) && $filters['period'] == 'minute' ? 'selected' : '' }}>{{ __('td-cost-calcultaror::messages.minute') }}</option>
                        </select>
                    </div>
                    <div class="col-md-3 mb-2">
                        <label for="months">{{ __('td-cost-calcultaror::messages.forecast_period') }}</label>
                        <select name="months" id="months" class="form-control form-control-sm">
                            <option value="3" {{ isset($filters['months']) && $filters['months'] == 3 ? 'selected' : '' }}>3 {{ __('td-cost-calcultaror::messages.months') }}</option>
                            <option value="6" {{ isset($filters['months']) && $filters['months'] == 6 ? 'selected' : '' }}>6 {{ __('td-cost-calcultaror::messages.months') }}</option>
                            <option value="12" {{ isset($filters['months']) && $filters['months'] == 12 ? 'selected' : '' }}>12 {{ __('td-cost-calcultaror::messages.months') }}</option>
                            <option value="24" {{ isset($filters['months']) && $filters['months'] == 24 ? 'selected' : '' }}>24 {{ __('td-cost-calcultaror::messages.months') }}</option>
                        </select>
                    </div>
                    <div class="col-md-3 mb-2 d-flex align-items-end">
                        <button type="submit" class="btn btn-sm btn-primary">
                            <i class="fas fa-filter mr-1"></i> {{ __('td-cost-calcultaror::messages.apply_filters') }}
                        </button>
                        <a href="{{ route('td-cost-calcultaror.forecast') }}" class="btn btn-sm btn-secondary ml-2">
                            <i class="fas fa-times mr-1"></i> {{ __('td-cost-calcultaror::messages.clear') }}
                        </a>
                    </div>
                </form>
            </div>
        </div>
    </div>
    
    <!-- Forecast Summary Cards -->
    <div class="row mb-4">
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-primary shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">
                                {{ __('td-cost-calcultaror::messages.trend_direction') }}</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                @if($forecastData['metadata']['trend_direction'] == 'increasing')
                                    <span class="text-danger">
                                        <i class="fas fa-arrow-up"></i> {{ __('td-cost-calcultaror::messages.trend_increasing') }}
                                    </span>
                                @elseif($forecastData['metadata']['trend_direction'] == 'decreasing')
                                    <span class="text-success">
                                        <i class="fas fa-arrow-down"></i> {{ __('td-cost-calcultaror::messages.trend_decreasing') }}
                                    </span>
                                @else
                                    <span class="text-info">
                                        <i class="fas fa-arrows-alt-h"></i> {{ __('td-cost-calcultaror::messages.trend_stable') }}
                                    </span>
                                @endif
                            </div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-chart-line fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-success shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-success text-uppercase mb-1">
                                {{ __('td-cost-calcultaror::messages.confidence_level') }}</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                <div class="progress">
                                    <div class="progress-bar {{ $forecastData['metadata']['confidence_level'] >= 80 ? 'bg-success' : ($forecastData['metadata']['confidence_level'] >= 60 ? 'bg-info' : ($forecastData['metadata']['confidence_level'] >= 40 ? 'bg-warning' : 'bg-danger')) }}" 
                                         role="progressbar" 
                                         style="width: {{ $forecastData['metadata']['confidence_level'] }}%" 
                                         aria-valuenow="{{ $forecastData['metadata']['confidence_level'] }}" 
                                         aria-valuemin="0" 
                                         aria-valuemax="100">
                                        {{ $forecastData['metadata']['confidence_level'] }}%
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-check-circle fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-info shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-info text-uppercase mb-1">
                                {{ __('td-cost-calcultaror::messages.forecast_period') }}</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                {{ $filters['months'] ?? 6 }} {{ __('td-cost-calcultaror::messages.months') }}
                            </div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-calendar fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-warning shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">
                                {{ __('td-cost-calcultaror::messages.forecast_range') }}</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                @php
                                    $firstMonth = array_key_first($forecastData['forecast']);
                                    $lastMonth = array_key_last($forecastData['forecast']);
                                    $start = isset($forecastData['forecast'][$firstMonth]) ? $forecastData['forecast'][$firstMonth]['label'] : '';
                                    $end = isset($forecastData['forecast'][$lastMonth]) ? $forecastData['forecast'][$lastMonth]['label'] : '';
                                @endphp
                                {{ $start }} - {{ $end }}
                            </div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-calendar-alt fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Main Forecast Chart -->
    <div class="row">
        <div class="col-12">
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">{{ __('td-cost-calcultaror::messages.cost_forecast_chart') }}</h6>
                </div>
                <div class="card-body">
                    <div class="chart-container" style="position: relative; height:400px;">
                        <canvas id="forecastChart"></canvas>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Forecast Data Tables -->
    <div class="row">
        <div class="col-md-6">
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">{{ __('td-cost-calcultaror::messages.historical_data') }}</h6>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-bordered" width="100%" cellspacing="0">
                            <thead>
                                <tr>
                                    <th>{{ __('td-cost-calcultaror::messages.period') }}</th>
                                    <th>{{ __('td-cost-calcultaror::messages.cost_amount') }}</th>
                                    <th>{{ __('td-cost-calcultaror::messages.items') }}</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($forecastData['historical'] as $period => $data)
                                <tr>
                                    <td>{{ $data['label'] }}</td>
                                    <td>{{ number_format($data['total'], 2) }}</td>
                                    <td>{{ $data['count'] }}</td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-md-6">
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">{{ __('td-cost-calcultaror::messages.forecast_data') }}</h6>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-bordered" width="100%" cellspacing="0">
                            <thead>
                                <tr>
                                    <th>{{ __('td-cost-calcultaror::messages.period') }}</th>
                                    <th>{{ __('td-cost-calcultaror::messages.forecasted_amount') }}</th>
                                    <th>{{ __('td-cost-calcultaror::messages.confidence') }}</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($forecastData['forecast'] as $period => $data)
                                <tr>
                                    <td>{{ $data['label'] }}</td>
                                    <td>{{ number_format($data['forecasted_total'], 2) }}</td>
                                    <td>
                                        <div class="progress">
                                            <div class="progress-bar {{ $data['confidence'] >= 0.8 ? 'bg-success' : ($data['confidence'] >= 0.6 ? 'bg-info' : ($data['confidence'] >= 0.4 ? 'bg-warning' : 'bg-danger')) }}" 
                                                 role="progressbar" 
                                                 style="width: {{ $data['confidence'] * 100 }}%" 
                                                 aria-valuenow="{{ $data['confidence'] * 100 }}" 
                                                 aria-valuemin="0" 
                                                 aria-valuemax="100">
                                                {{ number_format($data['confidence'] * 100) }}%
                                            </div>
                                        </div>
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Help Information -->
    <div class="row">
        <div class="col-12">
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">{{ __('td-cost-calcultaror::messages.forecast_help') }}</h6>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-4">
                            <div class="card mb-2">
                                <div class="card-body">
                                    <h5 class="card-title">{{ __('td-cost-calcultaror::messages.how_forecast_works') }}</h5>
                                    <p class="card-text">{{ __('td-cost-calcultaror::messages.forecast_explanation') }}</p>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="card mb-2">
                                <div class="card-body">
                                    <h5 class="card-title">{{ __('td-cost-calcultaror::messages.confidence_explained') }}</h5>
                                    <p class="card-text">{{ __('td-cost-calcultaror::messages.confidence_explanation') }}</p>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="card mb-2">
                                <div class="card-body">
                                    <h5 class="card-title">{{ __('td-cost-calcultaror::messages.how_to_use') }}</h5>
                                    <p class="card-text">{{ __('td-cost-calcultaror::messages.usage_explanation') }}</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js@3.7.1/dist/chart.min.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Prepare chart data
    const labels = [];
    const historicalValues = [];
    const forecastValues = [];
    const confidenceIntervals = [];
    
    // Add historical data
    @foreach($forecastData['historical'] as $period => $data)
        labels.push('{{ $data['label'] }}');
        historicalValues.push({{ $data['total'] }});
        forecastValues.push(null);
        confidenceIntervals.push(null);
    @endforeach
    
    // Add forecast data
    @foreach($forecastData['forecast'] as $period => $data)
        labels.push('{{ $data['label'] }}');
        historicalValues.push(null);
        forecastValues.push({{ $data['forecasted_total'] }});
        
        // Calculate confidence interval (±10% per confidence point)
        const confidenceMargin = {{ $data['forecasted_total'] }} * (1 - {{ $data['confidence'] }}) * 0.5;
        confidenceIntervals.push([
            {{ $data['forecasted_total'] }} - confidenceMargin,
            {{ $data['forecasted_total'] }} + confidenceMargin
        ]);
    @endforeach
    
    // Create chart
    const ctx = document.getElementById('forecastChart').getContext('2d');
    const forecastChart = new Chart(ctx, {
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
                                
                                // Add confidence interval for forecast data
                                if (context.datasetIndex === 1 && confidenceIntervals[context.dataIndex]) {
                                    const interval = confidenceIntervals[context.dataIndex];
                                    const lowerBound = new Intl.NumberFormat('{{ app()->getLocale() }}', {
                                        style: 'currency',
                                        currency: '{{ config('td-cost-calcultaror.currency', 'USD') }}'
                                    }).format(interval[0]);
                                    const upperBound = new Intl.NumberFormat('{{ app()->getLocale() }}', {
                                        style: 'currency',
                                        currency: '{{ config('td-cost-calcultaror.currency', 'USD') }}'
                                    }).format(interval[1]);
                                    
                                    label += ` (${lowerBound} - ${upperBound})`;
                                }
                            }
                            return label;
                        }
                    }
                }
            }
        }
    });
});
</script>
@endpush
