{{-- Dashboard Charts Component --}}
<div class="dashboard-charts">
    <div class="row">
        <div class="col-md-6">
            <div class="card mb-4">
                <div class="card-header">
                    <h5>{{ __('td-cost-calcultaror::messages.cost_by_category') }}</h5>
                </div>
                <div class="card-body">
                    <canvas id="costByCategoryChart" width="400" height="300"></canvas>
                </div>
            </div>
        </div>
        <div class="col-md-6">
            <div class="card mb-4">
                <div class="card-header">
                    <h5>{{ __('td-cost-calcultaror::messages.cost_by_period') }}</h5>
                </div>
                <div class="card-body">
                    <canvas id="costByPeriodChart" width="400" height="300"></canvas>
                </div>
            </div>
        </div>
    </div>
    
    <div class="row">
        <div class="col-md-8">
            <div class="card mb-4">
                <div class="card-header">
                    <h5>{{ __('td-cost-calcultaror::messages.cost_trend_analysis') }}</h5>
                </div>
                <div class="card-body">
                    <canvas id="costTrendChart" width="600" height="300"></canvas>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card mb-4">
                <div class="card-header">
                    <h5>{{ __('td-cost-calcultaror::messages.top_products_by_cost') }}</h5>
                </div>
                <div class="card-body">
                    <canvas id="topProductsChart" width="300" height="300"></canvas>
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js@3.7.1/dist/chart.min.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Cost by Category Chart
    const categoryChartCtx = document.getElementById('costByCategoryChart').getContext('2d');
    new Chart(categoryChartCtx, {
        type: 'doughnut',
        data: {
            labels: {!! json_encode($categoryLabels) !!},
            datasets: [{
                label: '{{ __("td-cost-calcultaror::messages.cost_amount") }}',
                data: {!! json_encode($categoryData) !!},
                backgroundColor: {!! json_encode($categoryColors) !!},
                borderWidth: 1
            }]
        },
        options: {
            responsive: true,
            plugins: {
                legend: {
                    position: 'right',
                },
                tooltip: {
                    callbacks: {
                        label: function(context) {
                            let label = context.label || '';
                            if (label) {
                                label += ': ';
                            }
                            if (context.parsed !== null) {
                                label += new Intl.NumberFormat('{{ app()->getLocale() }}', {
                                    style: 'currency',
                                    currency: '{{ config('td-cost-calcultaror.currency', 'USD') }}'
                                }).format(context.parsed);
                            }
                            return label;
                        }
                    }
                }
            }
        }
    });

    // Cost by Period Chart
    const periodChartCtx = document.getElementById('costByPeriodChart').getContext('2d');
    new Chart(periodChartCtx, {
        type: 'bar',
        data: {
            labels: {!! json_encode($periodLabels) !!},
            datasets: [{
                label: '{{ __("td-cost-calcultaror::messages.cost_amount") }}',
                data: {!! json_encode($periodData) !!},
                backgroundColor: 'rgba(54, 162, 235, 0.6)',
                borderColor: 'rgba(54, 162, 235, 1)',
                borderWidth: 1
            }]
        },
        options: {
            responsive: true,
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
            }
        }
    });

    // Cost Trend Chart
    const trendChartCtx = document.getElementById('costTrendChart').getContext('2d');
    new Chart(trendChartCtx, {
        type: 'line',
        data: {
            labels: {!! json_encode($trendLabels) !!},
            datasets: {!! json_encode($trendDatasets) !!}
        },
        options: {
            responsive: true,
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
                legend: {
                    position: 'top',
                },
                tooltip: {
                    mode: 'index',
                    intersect: false,
                }
            },
            interaction: {
                mode: 'nearest',
                axis: 'x',
                intersect: false
            }
        }
    });

    // Top Products Chart
    const productsChartCtx = document.getElementById('topProductsChart').getContext('2d');
    new Chart(productsChartCtx, {
        type: 'pie',
        data: {
            labels: {!! json_encode($topProductLabels) !!},
            datasets: [{
                label: '{{ __("td-cost-calcultaror::messages.cost_amount") }}',
                data: {!! json_encode($topProductData) !!},
                backgroundColor: [
                    'rgba(255, 99, 132, 0.7)',
                    'rgba(54, 162, 235, 0.7)',
                    'rgba(255, 206, 86, 0.7)',
                    'rgba(75, 192, 192, 0.7)',
                    'rgba(153, 102, 255, 0.7)'
                ],
                borderColor: [
                    'rgba(255, 99, 132, 1)',
                    'rgba(54, 162, 235, 1)',
                    'rgba(255, 206, 86, 1)',
                    'rgba(75, 192, 192, 1)',
                    'rgba(153, 102, 255, 1)'
                ],
                borderWidth: 1
            }]
        },
        options: {
            responsive: true,
            plugins: {
                legend: {
                    position: 'right',
                },
                tooltip: {
                    callbacks: {
                        label: function(context) {
                            let label = context.label || '';
                            if (label) {
                                label += ': ';
                            }
                            if (context.parsed !== null) {
                                label += new Intl.NumberFormat('{{ app()->getLocale() }}', {
                                    style: 'currency',
                                    currency: '{{ config('td-cost-calcultaror.currency', 'USD') }}'
                                }).format(context.parsed);
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
