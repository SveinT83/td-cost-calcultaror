{{-- Product Stats Widget - This file should be included, not extended --}}
<div class="row mt-4">
    <div class="col-xl-12">
        <div class="card shadow mb-4">
            <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
                <h6 class="m-0 font-weight-bold text-primary">{{ __('td-cost-calcultaror::messages.product_statistics') }}</h6>
                <div class="dropdown no-arrow">
                    <a class="dropdown-toggle" href="#" role="button" id="exportStatsLink"
                        data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                        <i class="fas fa-download fa-sm fa-fw text-gray-400"></i>
                    </a>
                    <div class="dropdown-menu dropdown-menu-right shadow animated--fade-in"
                        aria-labelledby="exportStatsLink">
                        <a class="dropdown-item" href="{{ route('td-cost-calcultaror.export-stats') }}">
                            <i class="fas fa-file-excel fa-sm fa-fw mr-2 text-gray-400"></i>
                            {{ __('td-cost-calcultaror::messages.export_all_data') }}
                        </a>
                    </div>
                </div>
            </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-xl-3 col-md-6 mb-4">
                            <div class="card h-100 py-2 bg-light">
                                <div class="card-body">
                                    <div class="row no-gutters align-items-center">
                                        <div class="col mr-2">
                                            <div class="text-xs font-weight-bold text-uppercase mb-1">
                                                {{ __('td-cost-calcultaror::messages.average_product_cost') }}
                                            </div>
                                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                                {{ number_format($productStats['avg_cost'], 2) }}
                                            </div>
                                        </div>
                                        <div class="col-auto">
                                            <i class="fas fa-balance-scale fa-2x text-gray-300"></i>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <div class="col-xl-3 col-md-6 mb-4">
                            <div class="card h-100 py-2 bg-light">
                                <div class="card-body">
                                    <div class="row no-gutters align-items-center">
                                        <div class="col mr-2">
                                            <div class="text-xs font-weight-bold text-uppercase mb-1">
                                                {{ __('td-cost-calcultaror::messages.highest_product_cost') }}
                                            </div>
                                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                                {{ number_format($productStats['max_cost'], 2) }}
                                            </div>
                                        </div>
                                        <div class="col-auto">
                                            <i class="fas fa-arrow-up fa-2x text-danger"></i>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <div class="col-xl-3 col-md-6 mb-4">
                            <div class="card h-100 py-2 bg-light">
                                <div class="card-body">
                                    <div class="row no-gutters align-items-center">
                                        <div class="col mr-2">
                                            <div class="text-xs font-weight-bold text-uppercase mb-1">
                                                {{ __('td-cost-calcultaror::messages.lowest_product_cost') }}
                                            </div>
                                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                                {{ number_format($productStats['min_cost'], 2) }}
                                            </div>
                                        </div>
                                        <div class="col-auto">
                                            <i class="fas fa-arrow-down fa-2x text-success"></i>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <div class="col-xl-3 col-md-6 mb-4">
                            <div class="card h-100 py-2 bg-light">
                                <div class="card-body">
                                    <div class="row no-gutters align-items-center">
                                        <div class="col mr-2">
                                            <div class="text-xs font-weight-bold text-uppercase mb-1">
                                                {{ __('td-cost-calcultaror::messages.avg_items_per_product') }}
                                            </div>
                                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                                @if($productCount > 0)
                                                    {{ number_format($costItemCount / $productCount, 1) }}
                                                @else
                                                    0
                                                @endif
                                            </div>
                                        </div>
                                        <div class="col-auto">
                                            <i class="fas fa-list fa-2x text-gray-300"></i>
                                        </div>
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
<script src="https://cdn.jsdelivr.net/npm/chart.js@2.9.4"></script>
<script src="https://cdn.jsdelivr.net/npm/chartjs-plugin-datalabels@0.7.0"></script>

<script>
    // Setup global chart defaults
    Chart.defaults.global.defaultFontFamily = 'Nunito';
    Chart.defaults.global.defaultFontColor = '#858796';
    
    $(document).ready(function() {
        // Period chart
        @if(count($costsByPeriod) > 0)
            var ctxPeriod = document.getElementById('costsByPeriodChart').getContext('2d');
            
            var labels = [];
            var data = [];
            var backgroundColors = [
                '#4e73df', '#1cc88a', '#36b9cc', '#f6c23e', '#e74a3b'
            ];
            
            @foreach($costsByPeriod as $period)
                labels.push('{{ __("td-cost-calcultaror::messages.period_" . $period->period) }}');
                data.push({{ $period->total }});
            @endforeach
            
            var periodChart = new Chart(ctxPeriod, {
                type: 'bar',
                data: {
                    labels: labels,
                    datasets: [{
                        label: '{{ __("td-cost-calcultaror::messages.total_cost") }}',
                        data: data,
                        backgroundColor: backgroundColors,
                        borderColor: backgroundColors,
                        borderWidth: 1
                    }]
                },
                options: {
                    maintainAspectRatio: false,
                    layout: {
                        padding: {
                            left: 10,
                            right: 25,
                            top: 25,
                            bottom: 0
                        }
                    },
                    scales: {
                        xAxes: [{
                            gridLines: {
                                display: false,
                                drawBorder: false
                            }
                        }],
                        yAxes: [{
                            ticks: {
                                beginAtZero: true,
                                callback: function(value) {
                                    return '$ ' + value.toLocaleString();
                                }
                            },
                            gridLines: {
                                color: "rgb(234, 236, 244)",
                                zeroLineColor: "rgb(234, 236, 244)",
                                drawBorder: false,
                                borderDash: [2],
                                zeroLineBorderDash: [2]
                            }
                        }],
                    },
                    tooltips: {
                        callbacks: {
                            label: function(tooltipItem, chart) {
                                var datasetLabel = chart.datasets[tooltipItem.datasetIndex].label || '';
                                return datasetLabel + ': $ ' + tooltipItem.yLabel.toLocaleString();
                            }
                        }
                    },
                    plugins: {
                        datalabels: {
                            anchor: 'end',
                            align: 'top',
                            formatter: function(value) {
                                return '$ ' + value.toLocaleString();
                            },
                            color: '#4e73df',
                            font: {
                                weight: 'bold',
                            }
                        }
                    }
                }
            });
            
            // Export functionality for Period chart
            $('#exportPeriodPNG').on('click', function() {
                var link = document.createElement('a');
                link.href = periodChart.toBase64Image();
                link.download = 'costs-by-period.png';
                link.click();
            });
            
            $('#exportPeriodCSV').on('click', function() {
                let csvContent = "data:text/csv;charset=utf-8,Period,Total Cost,Count\n";
                @foreach($costsByPeriod as $period)
                    csvContent += "{{ __('td-cost-calcultaror::messages.period_' . $period->period) }},{{ $period->total }},{{ $period->count }}\n";
                @endforeach
                
                var encodedUri = encodeURI(csvContent);
                var link = document.createElement('a');
                link.setAttribute('href', encodedUri);
                link.setAttribute('download', 'costs-by-period.csv');
                document.body.appendChild(link);
                link.click();
                document.body.removeChild(link);
            });
        @endif
        
        // Category chart
        @if($categoryModuleAvailable && count($costsByCategory) > 0)
            var ctxCategory = document.getElementById('costsByCategoryChart').getContext('2d');
            
            var categoryLabels = [];
            var categoryData = [];
            var categoryColors = generateColorArray({{ count($costsByCategory) }});
            
            @foreach($costsByCategory as $category => $data)
                categoryLabels.push('{{ $category ?: __("td-cost-calcultaror::messages.uncategorized") }}');
                categoryData.push({{ $data['total'] }});
            @endforeach
            
            @if($costsWithoutCategory > 0)
                categoryLabels.push('{{ __("td-cost-calcultaror::messages.uncategorized") }}');
                categoryData.push({{ $costsWithoutCategory }});
            @endif
            
            var categoryChart = new Chart(ctxCategory, {
                type: 'doughnut',
                data: {
                    labels: categoryLabels,
                    datasets: [{
                        data: categoryData,
                        backgroundColor: categoryColors,
                        hoverBackgroundColor: categoryColors,
                        hoverBorderColor: "rgba(234, 236, 244, 1)",
                    }],
                },
                options: {
                    maintainAspectRatio: false,
                    tooltips: {
                        callbacks: {
                            label: function(tooltipItem, data) {
                                var dataset = data.datasets[tooltipItem.datasetIndex];
                                var total = dataset.data.reduce(function(previousValue, currentValue) {
                                    return previousValue + currentValue;
                                });
                                var currentValue = dataset.data[tooltipItem.index];
                                var percentage = Math.round((currentValue/total) * 100);
                                
                                return data.labels[tooltipItem.index] + ': $ ' + currentValue.toLocaleString() + ' (' + percentage + '%)';
                            }
                        }
                    },
                    plugins: {
                        datalabels: {
                            formatter: function(value, ctx) {
                                let sum = 0;
                                let dataArr = ctx.chart.data.datasets[0].data;
                                dataArr.map(data => {
                                    sum += data;
                                });
                                let percentage = ((value * 100) / sum).toFixed(0) + "%";
                                return percentage;
                            },
                            color: '#fff',
                            font: {
                                weight: 'bold',
                                size: 12
                            }
                        }
                    },
                    legend: {
                        position: 'right',
                        labels: {
                            boxWidth: 12
                        }
                    },
                    cutoutPercentage: 60,
                }
            });
            
            // Export functionality for Category chart
            $('#exportCategoryPNG').on('click', function() {
                var link = document.createElement('a');
                link.href = categoryChart.toBase64Image();
                link.download = 'costs-by-category.png';
                link.click();
            });
            
            $('#exportCategoryCSV').on('click', function() {
                let csvContent = "data:text/csv;charset=utf-8,Category,Total Cost,Count\n";
                @foreach($costsByCategory as $category => $data)
                    csvContent += "{{ $category ?: __('td-cost-calcultaror::messages.uncategorized') }},{{ $data['total'] }},{{ $data['count'] }}\n";
                @endforeach
                @if($costsWithoutCategory > 0)
                    csvContent += "{{ __('td-cost-calcultaror::messages.uncategorized') }},{{ $costsWithoutCategory }},-\n";
                @endif
                
                var encodedUri = encodeURI(csvContent);
                var link = document.createElement('a');
                link.setAttribute('href', encodedUri);
                link.setAttribute('download', 'costs-by-category.csv');
                document.body.appendChild(link);
                link.click();
                document.body.removeChild(link);
            });
        @endif
        
        // Cost trend chart
        @if(count($costTrend) > 0)
            var ctxTrend = document.getElementById('costTrendChart').getContext('2d');
            
            var trendLabels = [];
            var trendData = [];
            
            @foreach($costTrend as $month => $cost)
                trendLabels.push('{{ $month }}');
                trendData.push({{ $cost }});
            @endforeach
            
            var trendChart = new Chart(ctxTrend, {
                type: 'line',
                data: {
                    labels: trendLabels,
                    datasets: [{
                        label: '{{ __("td-cost-calcultaror::messages.monthly_cost") }}',
                        data: trendData,
                        lineTension: 0.3,
                        backgroundColor: "rgba(78, 115, 223, 0.05)",
                        borderColor: "rgba(78, 115, 223, 1)",
                        pointRadius: 3,
                        pointBackgroundColor: "rgba(78, 115, 223, 1)",
                        pointBorderColor: "rgba(78, 115, 223, 1)",
                        pointHoverRadius: 5,
                        pointHoverBackgroundColor: "rgba(78, 115, 223, 1)",
                        pointHoverBorderColor: "rgba(78, 115, 223, 1)",
                        pointHitRadius: 10,
                        pointBorderWidth: 2,
                    }]
                },
                options: {
                    maintainAspectRatio: false,
                    layout: {
                        padding: {
                            left: 10,
                            right: 25,
                            top: 25,
                            bottom: 0
                        }
                    },
                    scales: {
                        xAxes: [{
                            time: {
                                unit: 'month'
                            },
                            gridLines: {
                                display: false,
                                drawBorder: false
                            },
                            ticks: {
                                maxTicksLimit: 12
                            }
                        }],
                        yAxes: [{
                            ticks: {
                                beginAtZero: true,
                                callback: function(value) {
                                    return '$ ' + value.toLocaleString();
                                }
                            },
                            gridLines: {
                                color: "rgb(234, 236, 244)",
                                zeroLineColor: "rgb(234, 236, 244)",
                                drawBorder: false,
                                borderDash: [2],
                                zeroLineBorderDash: [2]
                            }
                        }],
                    },
                    tooltips: {
                        callbacks: {
                            label: function(tooltipItem, chart) {
                                var datasetLabel = chart.datasets[tooltipItem.datasetIndex].label || '';
                                return datasetLabel + ': $ ' + tooltipItem.yLabel.toLocaleString();
                            }
                        }
                    }
                }
            });
            
            // Export functionality for Trend chart
            $('#exportTrendPNG').on('click', function() {
                var link = document.createElement('a');
                link.href = trendChart.toBase64Image();
                link.download = 'cost-trend.png';
                link.click();
            });
            
            $('#exportTrendCSV').on('click', function() {
                let csvContent = "data:text/csv;charset=utf-8,Month,Total Cost\n";
                @foreach($costTrend as $month => $cost)
                    csvContent += "{{ $month }},{{ $cost }}\n";
                @endforeach
                
                var encodedUri = encodeURI(csvContent);
                var link = document.createElement('a');
                link.setAttribute('href', encodedUri);
                link.setAttribute('download', 'cost-trend.csv');
                document.body.appendChild(link);
                link.click();
                document.body.removeChild(link);
            });
        @endif
    });
    
    // Helper function to generate random colors
    function generateColorArray(count) {
        var colors = [
            '#4e73df', '#1cc88a', '#36b9cc', '#f6c23e', '#e74a3b',
            '#5a5c69', '#2e59d9', '#17a673', '#2c9faf', '#f6c23e'
        ];
        
        if (count <= colors.length) {
            return colors.slice(0, count);
        }
        
        // If we need more colors than we have predefined, generate random ones
        while (colors.length < count) {
            var r = Math.floor(Math.random() * 200);
            var g = Math.floor(Math.random() * 200);
            var b = Math.floor(Math.random() * 200);
            colors.push('rgb(' + r + ',' + g + ',' + b + ')');
        }
        
        return colors;
    }
</script>
@endpush
