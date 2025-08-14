// Enhanced Interactive Dashboard Scripts
document.addEventListener('DOMContentLoaded', function() {
    // Store chart references
    let charts = {
        costByCategoryChart: null,
        costByPeriodChart: null,
        costTrendChart: null,
        topProductsChart: null
    };
    
    // Store chart data for reuse - these will be populated from the window.chartData object
    let chartData = window.chartData || {
        categoryLabels: [],
        categoryData: [],
        categoryColors: [],
        periodLabels: [],
        periodData: [],
        trendLabels: [],
        trendDatasets: [],
        topProductLabels: [],
        topProductData: []
    };
    
    // Initialize charts
    initializeCharts();
    
    // Add event listeners for interactive features
    document.querySelectorAll('.chart-type-selector').forEach(button => {
        button.addEventListener('click', function() {
            const chartType = this.dataset.chartType;
            const targetChart = this.dataset.target;
            updateChartType(targetChart, chartType);
        });
    });
    
    document.getElementById('downloadChartsBtn').addEventListener('click', function() {
        downloadCharts();
    });
    
    document.getElementById('fullscreenChartsBtn').addEventListener('click', function() {
        toggleFullscreenCharts();
    });
    
    document.getElementById('drilldownCategory').addEventListener('change', function() {
        applyDrilldownFilter('category', this.value);
    });
    
    document.getElementById('timeRangeFilter').addEventListener('change', function() {
        applyTimeRangeFilter(this.value);
    });
    
    document.getElementById('dataGrouping').addEventListener('change', function() {
        applyDataGrouping(this.value);
    });
    
    // Function to initialize all charts
    function initializeCharts() {
        // Re-initialize the charts using the stored chart references
        initCategoryChart();
        initPeriodChart();
        initTrendChart();
        initTopProductsChart();
        
        // Add click handlers for drill-down
        addChartDrilldownHandlers();
    }
    
    function initCategoryChart() {
        const ctx = document.getElementById('costByCategoryChart').getContext('2d');
        
        if (charts.costByCategoryChart) {
            charts.costByCategoryChart.destroy();
        }
        
        charts.costByCategoryChart = new Chart(ctx, {
            type: 'doughnut',
            data: {
                labels: chartData.categoryLabels,
                datasets: [{
                    label: 'Cost Amount',
                    data: chartData.categoryData,
                    backgroundColor: chartData.categoryColors,
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
                                    label += new Intl.NumberFormat(document.documentElement.lang || 'en', {
                                        style: 'currency',
                                        currency: window.appCurrency || 'USD'
                                    }).format(context.parsed);
                                }
                                return label;
                            }
                        }
                    }
                }
            }
        });
    }
    
    function initPeriodChart() {
        const ctx = document.getElementById('costByPeriodChart').getContext('2d');
        
        if (charts.costByPeriodChart) {
            charts.costByPeriodChart.destroy();
        }
        
        charts.costByPeriodChart = new Chart(ctx, {
            type: 'bar',
            data: {
                labels: chartData.periodLabels,
                datasets: [{
                    label: 'Cost Amount',
                    data: chartData.periodData,
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
                                return new Intl.NumberFormat(document.documentElement.lang || 'en', {
                                    style: 'currency',
                                    currency: window.appCurrency || 'USD',
                                    maximumSignificantDigits: 3
                                }).format(value);
                            }
                        }
                    }
                }
            }
        });
    }
    
    function initTrendChart() {
        const ctx = document.getElementById('costTrendChart').getContext('2d');
        
        if (charts.costTrendChart) {
            charts.costTrendChart.destroy();
        }
        
        charts.costTrendChart = new Chart(ctx, {
            type: 'line',
            data: {
                labels: chartData.trendLabels,
                datasets: chartData.trendDatasets
            },
            options: {
                responsive: true,
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: {
                            callback: function(value) {
                                return new Intl.NumberFormat(document.documentElement.lang || 'en', {
                                    style: 'currency',
                                    currency: window.appCurrency || 'USD',
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
    }
    
    function initTopProductsChart() {
        const ctx = document.getElementById('topProductsChart').getContext('2d');
        
        if (charts.topProductsChart) {
            charts.topProductsChart.destroy();
        }
        
        charts.topProductsChart = new Chart(ctx, {
            type: 'pie',
            data: {
                labels: chartData.topProductLabels,
                datasets: [{
                    label: 'Cost Amount',
                    data: chartData.topProductData,
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
                                    label += new Intl.NumberFormat(document.documentElement.lang || 'en', {
                                        style: 'currency',
                                        currency: window.appCurrency || 'USD'
                                    }).format(context.parsed);
                                }
                                return label;
                            }
                        }
                    }
                }
            }
        });
    }
    
    // Function to update chart type (bar, line, pie, doughnut)
    function updateChartType(chartId, newType) {
        if (!charts[chartId]) {
            console.error('Chart not found:', chartId);
            return;
        }
        
        // Store the current data and options
        const data = charts[chartId].data;
        const options = charts[chartId].options;
        
        // Destroy the current chart
        charts[chartId].destroy();
        
        // Create a new chart with the new type but same data
        const ctx = document.getElementById(chartId).getContext('2d');
        charts[chartId] = new Chart(ctx, {
            type: newType,
            data: data,
            options: options
        });
        
        // Update the chart's options based on new type
        if (newType === 'line' || newType === 'bar') {
            charts[chartId].options.scales.y.beginAtZero = true;
        }
        
        // Refresh the chart
        charts[chartId].update();
    }
    
    // Download charts as images
    function downloadCharts() {
        Object.keys(charts).forEach(chartId => {
            if (!charts[chartId]) return;
            
            const canvas = document.getElementById(chartId);
            const image = canvas.toDataURL("image/png");
            
            // Create an anchor and trigger download
            const downloadLink = document.createElement('a');
            downloadLink.href = image;
            downloadLink.download = chartId + '.png';
            document.body.appendChild(downloadLink);
            downloadLink.click();
            document.body.removeChild(downloadLink);
        });
    }
    
    // Toggle fullscreen view of charts
    function toggleFullscreenCharts() {
        const chartsContainer = document.getElementById('chartsContainer');
        
        if (!document.fullscreenElement) {
            if (chartsContainer.requestFullscreen) {
                chartsContainer.requestFullscreen();
            } else if (chartsContainer.mozRequestFullScreen) { /* Firefox */
                chartsContainer.mozRequestFullScreen();
            } else if (chartsContainer.webkitRequestFullscreen) { /* Chrome, Safari & Opera */
                chartsContainer.webkitRequestFullscreen();
            } else if (chartsContainer.msRequestFullscreen) { /* IE/Edge */
                chartsContainer.msRequestFullscreen();
            }
        } else {
            if (document.exitFullscreen) {
                document.exitFullscreen();
            } else if (document.mozCancelFullScreen) {
                document.mozCancelFullScreen();
            } else if (document.webkitExitFullscreen) {
                document.webkitExitFullscreen();
            } else if (document.msExitFullscreen) {
                document.msExitFullscreen();
            }
        }
    }
    
    // Add click handlers for drill-down functionality
    function addChartDrilldownHandlers() {
        // Category chart drill-down
        document.getElementById('costByCategoryChart').addEventListener('click', function(evt) {
            const activePoints = charts.costByCategoryChart.getElementsAtEventForMode(
                evt, 'nearest', { intersect: true }, true
            );
            
            if (activePoints.length > 0) {
                const clickedIndex = activePoints[0].index;
                const categoryName = chartData.categoryLabels[clickedIndex];
                
                // Show drill-down panel with details
                showDrilldown('category', categoryName);
            }
        });
        
        // Period chart drill-down
        document.getElementById('costByPeriodChart').addEventListener('click', function(evt) {
            const activePoints = charts.costByPeriodChart.getElementsAtEventForMode(
                evt, 'nearest', { intersect: true }, true
            );
            
            if (activePoints.length > 0) {
                const clickedIndex = activePoints[0].index;
                const periodName = chartData.periodLabels[clickedIndex];
                
                // Show drill-down panel with details
                showDrilldown('period', periodName);
            }
        });
        
        // Product chart drill-down
        document.getElementById('topProductsChart').addEventListener('click', function(evt) {
            const activePoints = charts.topProductsChart.getElementsAtEventForMode(
                evt, 'nearest', { intersect: true }, true
            );
            
            if (activePoints.length > 0) {
                const clickedIndex = activePoints[0].index;
                const productName = chartData.topProductLabels[clickedIndex];
                
                // Show drill-down panel with details
                showDrilldown('product', productName);
            }
        });
    }
    
    // Show drill-down panel with details
    function showDrilldown(type, name) {
        const drilldownPanel = document.getElementById('drilldownPanel');
        const drilldownTitle = document.getElementById('drilldownTitle');
        const drilldownContent = document.getElementById('drilldownContent');
        
        // Update the title
        switch (type) {
            case 'category':
                drilldownTitle.textContent = `Category Details: ${name}`;
                break;
            case 'period':
                drilldownTitle.textContent = `Period Details: ${name}`;
                break;
            case 'product':
                drilldownTitle.textContent = `Product Details: ${name}`;
                break;
        }
        
        // Fetch detailed data via AJAX
        fetchDrilldownData(type, name)
            .then(data => {
                // Render the data
                renderDrilldownContent(type, name, data);
                
                // Show the panel if hidden
                if (!drilldownPanel.classList.contains('show')) {
                    $(drilldownPanel).collapse('show');
                }
            })
            .catch(error => {
                console.error('Error fetching drill-down data:', error);
                drilldownContent.innerHTML = `<div class="alert alert-danger">
                    Error loading details
                </div>`;
                
                // Show the panel if hidden
                if (!drilldownPanel.classList.contains('show')) {
                    $(drilldownPanel).collapse('show');
                }
            });
    }
    
    // Fetch drill-down data (simulated for now)
    function fetchDrilldownData(type, name) {
        // This would typically be an AJAX call to fetch detailed data
        // For now, we'll simulate it with a Promise that resolves with mock data
        return new Promise((resolve) => {
            setTimeout(() => {
                let data = {};
                
                switch (type) {
                    case 'category':
                        data = {
                            name: name,
                            totalCost: Math.round(Math.random() * 10000) / 100,
                            itemCount: Math.floor(Math.random() * 20) + 1,
                            items: Array(5).fill().map((_, i) => ({
                                id: i + 1,
                                name: `Item ${i + 1} in ${name}`,
                                cost: Math.round(Math.random() * 1000) / 100
                            }))
                        };
                        break;
                    case 'period':
                        data = {
                            name: name,
                            totalCost: Math.round(Math.random() * 10000) / 100,
                            categoryBreakdown: [
                                { name: 'Salary', percentage: 40 },
                                { name: 'Hardware', percentage: 25 },
                                { name: 'Software', percentage: 20 },
                                { name: 'Services', percentage: 15 }
                            ]
                        };
                        break;
                    case 'product':
                        data = {
                            name: name,
                            totalCost: Math.round(Math.random() * 10000) / 100,
                            costItems: Array(3).fill().map((_, i) => ({
                                id: i + 1,
                                name: `Cost Component ${i + 1}`,
                                cost: Math.round(Math.random() * 500) / 100,
                                percentage: Math.round(Math.random() * 40) + 10
                            }))
                        };
                        break;
                }
                
                resolve(data);
            }, 300); // Simulate network delay
        });
    }
    
    // Render drill-down content based on data type
    function renderDrilldownContent(type, name, data) {
        const drilldownContent = document.getElementById('drilldownContent');
        let html = '';
        
        switch (type) {
            case 'category':
                html = `
                    <div class="row mb-3">
                        <div class="col-md-4">
                            <div class="card border-left-primary">
                                <div class="card-body py-2">
                                    <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">
                                        Total Cost
                                    </div>
                                    <div class="h5 mb-0 font-weight-bold text-gray-800">
                                        ${new Intl.NumberFormat(document.documentElement.lang || 'en', {
                                            style: 'currency',
                                            currency: window.appCurrency || 'USD'
                                        }).format(data.totalCost)}
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="card border-left-info">
                                <div class="card-body py-2">
                                    <div class="text-xs font-weight-bold text-info text-uppercase mb-1">
                                        Items
                                    </div>
                                    <div class="h5 mb-0 font-weight-bold text-gray-800">
                                        ${data.itemCount}
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="table-responsive">
                        <table class="table table-bordered table-sm">
                            <thead>
                                <tr>
                                    <th>Name</th>
                                    <th>Cost</th>
                                </tr>
                            </thead>
                            <tbody>
                                ${data.items.map(item => `
                                    <tr>
                                        <td>${item.name}</td>
                                        <td>${new Intl.NumberFormat('{{ app()->getLocale() }}', {
                                            style: 'currency',
                                            currency: '{{ config("td-cost-calcultaror.currency", "USD") }}'
                                        }).format(item.cost)}</td>
                                    </tr>
                                `).join('')}
                            </tbody>
                        </table>
                    </div>
                    <div class="mt-3">
                        <a href="#" class="btn btn-sm btn-primary">View All Items</a>
                    </div>
                `;
                break;
            
            case 'period':
                html = `
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <div class="card border-left-primary">
                                <div class="card-body py-2">
                                    <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">
                                        {{ __("td-cost-calcultaror::messages.total_cost") }}
                                    </div>
                                    <div class="h5 mb-0 font-weight-bold text-gray-800">
                                        ${new Intl.NumberFormat('{{ app()->getLocale() }}', {
                                            style: 'currency',
                                            currency: '{{ config("td-cost-calcultaror.currency", "USD") }}'
                                        }).format(data.totalCost)}
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <h6>Category Breakdown</h6>
                    <div class="progress-breakdown mb-3">
                        ${data.categoryBreakdown.map(category => `
                            <div class="mb-2">
                                <div class="d-flex justify-content-between">
                                    <small>${category.name}</small>
                                    <small>${category.percentage}%</small>
                                </div>
                                <div class="progress" style="height: 10px;">
                                    <div class="progress-bar" role="progressbar" style="width: ${category.percentage}%" 
                                        aria-valuenow="${category.percentage}" aria-valuemin="0" aria-valuemax="100"></div>
                                </div>
                            </div>
                        `).join('')}
                    </div>
                    <div class="mt-3">
                        <a href="#" class="btn btn-sm btn-primary">View Period Details</a>
                    </div>
                `;
                break;
            
            case 'product':
                html = `
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <div class="card border-left-primary">
                                <div class="card-body py-2">
                                    <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">
                                        {{ __("td-cost-calcultaror::messages.total_cost") }}
                                    </div>
                                    <div class="h5 mb-0 font-weight-bold text-gray-800">
                                        ${new Intl.NumberFormat('{{ app()->getLocale() }}', {
                                            style: 'currency',
                                            currency: '{{ config("td-cost-calcultaror.currency", "USD") }}'
                                        }).format(data.totalCost)}
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <h6>Cost Breakdown</h6>
                    <div class="table-responsive mb-3">
                        <table class="table table-bordered table-sm">
                            <thead>
                                <tr>
                                    <th>Component</th>
                                    <th>Cost</th>
                                    <th>Percentage</th>
                                </tr>
                            </thead>
                            <tbody>
                                ${data.costItems.map(item => `
                                    <tr>
                                        <td>${item.name}</td>
                                        <td>${new Intl.NumberFormat('{{ app()->getLocale() }}', {
                                            style: 'currency',
                                            currency: '{{ config("td-cost-calcultaror.currency", "USD") }}'
                                        }).format(item.cost)}</td>
                                        <td>
                                            <div class="progress" style="height: 8px;">
                                                <div class="progress-bar bg-info" role="progressbar" style="width: ${item.percentage}%" 
                                                    aria-valuenow="${item.percentage}" aria-valuemin="0" aria-valuemax="100"></div>
                                            </div>
                                            <small>${item.percentage}%</small>
                                        </td>
                                    </tr>
                                `).join('')}
                            </tbody>
                        </table>
                    </div>
                    <div class="mt-3">
                        <a href="#" class="btn btn-sm btn-primary">View Product Details</a>
                    </div>
                `;
                break;
        }
        
        drilldownContent.innerHTML = html;
    }
    
    // Apply category drilldown filter
    function applyDrilldownFilter(type, value) {
        // For now, we'll just simulate this with a simple alert
        if (value) {
            alert(`Filter applied: ${type} = ${value}`);
            
            // This would typically make an AJAX call to fetch new data
            // and update the charts. For now, we'll just simulate a change
            if (type === 'category' && charts.costByCategoryChart) {
                // Update highlight for the selected category
                const dataset = charts.costByCategoryChart.data.datasets[0];
                const backgroundColors = [...dataset.backgroundColor]; // Clone the array
                
                for (let i = 0; i < backgroundColors.length; i++) {
                    // Reset all colors to their default opacity
                    backgroundColors[i] = backgroundColors[i].replace(', 0.9)', ', 0.7)');
                }
                
                // If a value is selected, highlight that category
                if (value !== '') {
                    // Find the index of the category in the chart data
                    const index = chartData.categoryLabels.findIndex(
                        (catName, idx) => chartData.categoryData[idx] === parseInt(value)
                    );
                    
                    if (index !== -1) {
                        // Increase opacity of the selected category
                        backgroundColors[index] = backgroundColors[index].replace(', 0.7)', ', 0.9)');
                    }
                }
                
                dataset.backgroundColor = backgroundColors;
                charts.costByCategoryChart.update();
            }
        }
    }
    
    // Apply time range filter
    function applyTimeRangeFilter(months) {
        // This would make an AJAX call to fetch new data
        // For demonstration, we'll just show a message
        alert(`Loading data for months: ${months}`);
        
        // In a real implementation, this would update charts with new data
    }
    
    // Apply data grouping filter
    function applyDataGrouping(groupBy) {
        // This would make an AJAX call to fetch new grouped data
        alert(`Regrouping data by: ${groupBy}`);
        
        // In a real implementation, this would update charts with regrouped data
    }
});
