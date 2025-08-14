@extends('layouts.app')

@section('pageHeader')
    <x-page-header pageHeaderTitle="{{ __('td-cost-calcultaror::messages.dashboard') }}" />
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
                    <a href="{{ route('td-cost-calcultaror.dashboard', ['locale' => $code] + request()->except('locale')) }}" 
                       class="btn {{ $currentLocale == $code ? 'btn-primary' : 'btn-secondary' }}">
                        {{ $name }}
                    </a>
                    @endforeach
                </div>
            </div>
        </div>
    </div>
    
    <!-- Filter and Cache Controls -->
    <div class="mb-4">
        <div class="card shadow">
            <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
                <h6 class="m-0 font-weight-bold text-primary">{{ __('td-cost-calcultaror::messages.filters') }}</h6>
                @can('edit_cost_calculator')
                <a href="{{ route('td-cost-calcultaror.clear-cache') }}" class="btn btn-sm btn-primary">
                    <i class="fas fa-sync-alt mr-1"></i> {{ __('td-cost-calcultaror::messages.clear_cache') }}
                </a>
                @endcan
            </div>
            <div class="card-body">
                <form action="{{ route('td-cost-calcultaror.dashboard') }}" method="GET" class="row">
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
                    @if($categoryModuleAvailable)
                    <div class="col-md-3 mb-2">
                        <label for="category_id">{{ __('td-cost-calcultaror::messages.category_filter') }}</label>
                        <select name="category_id" id="category_id" class="form-control form-control-sm">
                            <option value="">{{ __('td-cost-calcultaror::messages.all_categories') }}</option>
                            @foreach($costsByCategory as $name => $data)
                            <option value="{{ $data['id'] }}" {{ isset($filters['category_id']) && $filters['category_id'] == $data['id'] ? 'selected' : '' }}>{{ $name }}</option>
                            @endforeach
                        </select>
                    </div>
                    @endif
                    <div class="col-md-3 mb-2">
                        <label for="date_range">{{ __('td-cost-calcultaror::messages.date_range') }}</label>
                        <input type="text" name="date_range" id="date_range" class="form-control form-control-sm datepicker" value="{{ $filters['date_range'] ?? '' }}" placeholder="YYYY-MM-DD - YYYY-MM-DD">
                    </div>
                    <div class="col-md-3 mb-2 d-flex align-items-end">
                        <button type="submit" class="btn btn-sm btn-primary">
                            <i class="fas fa-filter mr-1"></i> {{ __('td-cost-calcultaror::messages.apply_filters') }}
                        </button>
                        <a href="{{ route('td-cost-calcultaror.dashboard') }}" class="btn btn-sm btn-secondary ml-2">
                            <i class="fas fa-times mr-1"></i> {{ __('td-cost-calcultaror::messages.clear') }}
                        </a>
                    </div>
                </form>
            </div>
        </div>
    </div>
    
    <div class="row">
        <!-- Overview Stats -->
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-primary shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">
                                {{ __('td-cost-calcultaror::messages.cost_items') }}</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $costItemCount }}</div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-coins fa-2x text-gray-300"></i>
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
                                {{ __('td-cost-calcultaror::messages.products') }}</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $productCount }}</div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-box fa-2x text-gray-300"></i>
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
                                {{ __('td-cost-calcultaror::messages.total_costs') }}</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">{{ number_format($totalCosts, 2) }}</div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-calculator fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Cost Trend Over Time -->
    <div class="row">
        <div class="col-xl-12">
            <div class="card shadow mb-4">
                <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
                    <h6 class="m-0 font-weight-bold text-primary">{{ __('td-cost-calcultaror::messages.cost_trend') }}</h6>
                    <div class="dropdown no-arrow">
                        <a class="dropdown-toggle" href="#" role="button" id="dropdownMenuLink"
                            data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                            <i class="fas fa-ellipsis-v fa-sm fa-fw text-gray-400"></i>
                        </a>
                        <div class="dropdown-menu dropdown-menu-right shadow animated--fade-in"
                            aria-labelledby="dropdownMenuLink">
                            <div class="dropdown-header">{{ __('td-cost-calcultaror::messages.export_options') }}:</div>
                            <a class="dropdown-item" href="#" id="exportTrendPNG">
                                <i class="fas fa-file-image fa-sm fa-fw mr-2 text-gray-400"></i>
                                {{ __('td-cost-calcultaror::messages.export_as_image') }}
                            </a>
                            <a class="dropdown-item" href="#" id="exportTrendCSV">
                                <i class="fas fa-file-csv fa-sm fa-fw mr-2 text-gray-400"></i>
                                {{ __('td-cost-calcultaror::messages.export_as_csv') }}
                            </a>
                        </div>
                    </div>
                </div>
                <div class="card-body">
                    @if(count($costTrend) > 0)
                        <div class="chart-area">
                            <canvas id="costTrendChart"></canvas>
                        </div>
                    @else
                        <div class="text-center text-gray-500 py-5">
                            <i class="fas fa-chart-line fa-3x mb-3"></i>
                            <p>{{ __('td-cost-calcultaror::messages.no_data_available') }}</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <!-- Costs by Period -->
        <div class="col-xl-6 col-lg-7">
            <div class="card shadow mb-4">
                <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
                    <h6 class="m-0 font-weight-bold text-primary">{{ __('td-cost-calcultaror::messages.costs_by_period') }}</h6>
                    <div class="dropdown no-arrow">
                        <a class="dropdown-toggle" href="#" role="button" id="periodDropdownMenuLink"
                            data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                            <i class="fas fa-ellipsis-v fa-sm fa-fw text-gray-400"></i>
                        </a>
                        <div class="dropdown-menu dropdown-menu-right shadow animated--fade-in"
                            aria-labelledby="periodDropdownMenuLink">
                            <div class="dropdown-header">{{ __('td-cost-calcultaror::messages.export_options') }}:</div>
                            <a class="dropdown-item" href="#" id="exportPeriodPNG">
                                <i class="fas fa-file-image fa-sm fa-fw mr-2 text-gray-400"></i>
                                {{ __('td-cost-calcultaror::messages.export_as_image') }}
                            </a>
                            <a class="dropdown-item" href="#" id="exportPeriodCSV">
                                <i class="fas fa-file-csv fa-sm fa-fw mr-2 text-gray-400"></i>
                                {{ __('td-cost-calcultaror::messages.export_as_csv') }}
                            </a>
                        </div>
                    </div>
                </div>
                <div class="card-body">
                    @if(count($costsByPeriod) > 0)
                        <div class="chart-bar">
                            <canvas id="costsByPeriodChart"></canvas>
                        </div>
                        <hr>
                        <div class="table-responsive mt-3">
                            <table class="table table-sm table-bordered">
                                <thead>
                                    <tr>
                                        <th>{{ __('td-cost-calcultaror::messages.period') }}</th>
                                        <th>{{ __('td-cost-calcultaror::messages.total_cost') }}</th>
                                        <th>{{ __('td-cost-calcultaror::messages.item_count') }}</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($costsByPeriod as $period)
                                        <tr>
                                            <td>{{ __('td-cost-calcultaror::messages.period_' . $period->period) }}</td>
                                            <td>{{ number_format($period->total, 2) }}</td>
                                            <td>{{ $period->count }}</td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @else
                        <div class="text-center text-gray-500 py-5">
                            <i class="fas fa-chart-bar fa-3x mb-3"></i>
                            <p>{{ __('td-cost-calcultaror::messages.no_data_available') }}</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>

        <!-- Costs by Category -->
        <div class="col-xl-6 col-lg-5">
            <div class="card shadow mb-4">
                <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
                    <h6 class="m-0 font-weight-bold text-primary">{{ __('td-cost-calcultaror::messages.costs_by_category') }}</h6>
                    <div class="dropdown no-arrow">
                        <a class="dropdown-toggle" href="#" role="button" id="categoryDropdownMenuLink"
                            data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                            <i class="fas fa-ellipsis-v fa-sm fa-fw text-gray-400"></i>
                        </a>
                        <div class="dropdown-menu dropdown-menu-right shadow animated--fade-in"
                            aria-labelledby="categoryDropdownMenuLink">
                            <div class="dropdown-header">{{ __('td-cost-calcultaror::messages.export_options') }}:</div>
                            <a class="dropdown-item" href="#" id="exportCategoryPNG">
                                <i class="fas fa-file-image fa-sm fa-fw mr-2 text-gray-400"></i>
                                {{ __('td-cost-calcultaror::messages.export_as_image') }}
                            </a>
                            <a class="dropdown-item" href="#" id="exportCategoryCSV">
                                <i class="fas fa-file-csv fa-sm fa-fw mr-2 text-gray-400"></i>
                                {{ __('td-cost-calcultaror::messages.export_as_csv') }}
                            </a>
                        </div>
                    </div>
                </div>
                <div class="card-body">
                    @if($categoryModuleAvailable && count($costsByCategory) > 0)
                        <div class="chart-pie">
                            <canvas id="costsByCategoryChart"></canvas>
                        </div>
                        <hr>
                        <div class="table-responsive mt-3">
                            <table class="table table-sm table-bordered">
                                <thead>
                                    <tr>
                                        <th>{{ __('td-cost-calcultaror::messages.category') }}</th>
                                        <th>{{ __('td-cost-calcultaror::messages.total_cost') }}</th>
                                        <th>{{ __('td-cost-calcultaror::messages.item_count') }}</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($costsByCategory as $category => $data)
                                        <tr>
                                            <td>{{ $category ?: __('td-cost-calcultaror::messages.uncategorized') }}</td>
                                            <td>{{ number_format($data['total'], 2) }}</td>
                                            <td>{{ $data['count'] }}</td>
                                        </tr>
                                    @endforeach
                                    @if($costsWithoutCategory > 0)
                                        <tr>
                                            <td>{{ __('td-cost-calcultaror::messages.uncategorized') }}</td>
                                            <td>{{ number_format($costsWithoutCategory, 2) }}</td>
                                            <td>-</td>
                                        </tr>
                                    @endif
                                </tbody>
                            </table>
                        </div>
                    @else
                        <div class="text-center text-gray-500 py-5">
                            @if(!$categoryModuleAvailable)
                                <i class="fas fa-puzzle-piece fa-3x mb-3"></i>
                                <p>{{ __('td-cost-calcultaror::messages.category_not_available') }}</p>
                            @else
                                <i class="fas fa-chart-pie fa-3x mb-3"></i>
                                <p>{{ __('td-cost-calcultaror::messages.no_data_available') }}</p>
                            @endif
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <!-- Most Expensive Products -->
        <div class="col-xl-6 mb-4">
            <div class="card shadow">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">{{ __('td-cost-calcultaror::messages.expensive_products') }}</h6>
                </div>
                <div class="card-body">
                    @if($expensiveProducts->count() > 0)
                        <div class="table-responsive">
                            <table class="table table-bordered" width="100%" cellspacing="0">
                                <thead>
                                    <tr>
                                        <th>{{ __('td-cost-calcultaror::messages.name') }}</th>
                                        <th>{{ __('td-cost-calcultaror::messages.calculation_model') }}</th>
                                        <th>{{ __('td-cost-calcultaror::messages.total_cost') }}</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($expensiveProducts as $product)
                                        <tr>
                                            <td>
                                                <a href="{{ route('td-cost-calcultaror.products.show', $product->id) }}">
                                                    {{ $product->name }}
                                                </a>
                                            </td>
                                            <td>{{ __('td-cost-calcultaror::messages.calc_'.$product->calculation_model) }}</td>
                                            <td>{{ number_format($product->total_cost, 2) }}</td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @else
                        <div class="text-center text-gray-500 py-5">
                            <i class="fas fa-box fa-3x mb-3"></i>
                            <p>{{ __('td-cost-calcultaror::messages.no_products_found') }}</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>

        <!-- Recent Cost Items -->
        <div class="col-xl-6 mb-4">
            <div class="card shadow">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">{{ __('td-cost-calcultaror::messages.recent_cost_items') }}</h6>
                </div>
                <div class="card-body">
                    @if($recentCostItems->count() > 0)
                        <div class="table-responsive">
                            <table class="table table-bordered" width="100%" cellspacing="0">
                                <thead>
                                    <tr>
                                        <th>{{ __('td-cost-calcultaror::messages.name') }}</th>
                                        <th>{{ __('td-cost-calcultaror::messages.price') }}</th>
                                        <th>{{ __('td-cost-calcultaror::messages.period') }}</th>
                                        <th>{{ __('td-cost-calcultaror::messages.created_at') }}</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($recentCostItems as $costItem)
                                        <tr>
                                            <td>
                                                <a href="{{ route('td-cost-calcultaror.cost-items.show', $costItem->id) }}">
                                                    {{ $costItem->name }}
                                                </a>
                                                @if($costItem->usage_count > 0)
                                                    <span class="badge badge-success">{{ __('td-cost-calcultaror::messages.used_in_products_count', ['count' => $costItem->usage_count]) }}</span>
                                                @endif
                                            </td>
                                            <td>{{ number_format($costItem->price, 2) }}</td>
                                            <td>{{ __('td-cost-calcultaror::messages.period_'.$costItem->period) }}</td>
                                            <td>{{ $costItem->created_at->format('Y-m-d') }}</td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @else
                        <div class="text-center text-gray-500 py-5">
                            <i class="fas fa-coins fa-3x mb-3"></i>
                            <p>{{ __('td-cost-calcultaror::messages.no_items_found') }}</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
<!-- Include extra dashboard components -->
@include('td-cost-calcultaror::dashboard-extra')

<!-- Visual Analytics Dashboard -->
<div class="mb-4">
    <div class="card shadow">
        <div class="card-header py-3 d-flex justify-content-between align-items-center">
            <h6 class="m-0 font-weight-bold text-primary">{{ __('td-cost-calcultaror::messages.visual_analytics') }}</h6>
            
            <div class="dropdown">
                <button class="btn btn-sm btn-primary dropdown-toggle" type="button" id="chartOptionsDropdown" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                    <i class="fas fa-cog mr-1"></i> {{ __('td-cost-calcultaror::messages.chart_options') }}
                </button>
                <div class="dropdown-menu dropdown-menu-right" aria-labelledby="chartOptionsDropdown">
                    <h6 class="dropdown-header">{{ __('td-cost-calcultaror::messages.chart_types') }}</h6>
                    <button class="dropdown-item chart-type-selector" data-chart-type="doughnut" data-target="costByCategoryChart">{{ __('td-cost-calcultaror::messages.doughnut_chart') }}</button>
                    <button class="dropdown-item chart-type-selector" data-chart-type="pie" data-target="costByCategoryChart">{{ __('td-cost-calcultaror::messages.pie_chart') }}</button>
                    <button class="dropdown-item chart-type-selector" data-chart-type="bar" data-target="costByPeriodChart">{{ __('td-cost-calcultaror::messages.bar_chart') }}</button>
                    <button class="dropdown-item chart-type-selector" data-chart-type="line" data-target="costByPeriodChart">{{ __('td-cost-calcultaror::messages.line_chart') }}</button>
                    <div class="dropdown-divider"></div>
                    <h6 class="dropdown-header">{{ __('td-cost-calcultaror::messages.chart_actions') }}</h6>
                    <button class="dropdown-item" id="downloadChartsBtn">{{ __('td-cost-calcultaror::messages.download_charts') }}</button>
                    <button class="dropdown-item" id="fullscreenChartsBtn">{{ __('td-cost-calcultaror::messages.fullscreen_view') }}</button>
                </div>
            </div>
        </div>
        <div class="card-body">
            <div id="chartFilters" class="mb-4">
                <div class="row">
                    <div class="col-md-4">
                        <label for="drilldownCategory">{{ __('td-cost-calcultaror::messages.drilldown_category') }}</label>
                        <select id="drilldownCategory" class="form-control form-control-sm">
                            <option value="">{{ __('td-cost-calcultaror::messages.all_categories') }}</option>
                            @foreach($costsByCategory as $category => $data)
                                <option value="{{ $data['id'] }}">{{ $category }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-4">
                        <label for="timeRangeFilter">{{ __('td-cost-calcultaror::messages.time_range') }}</label>
                        <select id="timeRangeFilter" class="form-control form-control-sm">
                            <option value="3">{{ __('td-cost-calcultaror::messages.last_3_months') }}</option>
                            <option value="6" selected>{{ __('td-cost-calcultaror::messages.last_6_months') }}</option>
                            <option value="12">{{ __('td-cost-calcultaror::messages.last_12_months') }}</option>
                        </select>
                    </div>
                    <div class="col-md-4">
                        <label for="dataGrouping">{{ __('td-cost-calcultaror::messages.data_grouping') }}</label>
                        <select id="dataGrouping" class="form-control form-control-sm">
                            <option value="category">{{ __('td-cost-calcultaror::messages.by_category') }}</option>
                            <option value="period">{{ __('td-cost-calcultaror::messages.by_period') }}</option>
                            <option value="product">{{ __('td-cost-calcultaror::messages.by_product') }}</option>
                        </select>
                    </div>
                </div>
            </div>
            
            <div id="chartsContainer">
                @include('td-cost-calcultaror::dashboard.charts')
            </div>
            
            <!-- Drill-down Details Panel -->
            <div class="mt-4 collapse" id="drilldownPanel">
                <div class="card border-left-primary">
                    <div class="card-header bg-light">
                        <h6 class="m-0 font-weight-bold text-primary" id="drilldownTitle">{{ __('td-cost-calcultaror::messages.detailed_view') }}</h6>
                    </div>
                    <div class="card-body">
                        <div id="drilldownContent">
                            <div class="text-center text-muted">
                                <i class="fas fa-chart-bar fa-3x mb-3"></i>
                                <p>{{ __('td-cost-calcultaror::messages.click_chart_for_details') }}</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Cost Forecasting -->
@include('td-cost-calcultaror::dashboard.forecast')
@endsection
