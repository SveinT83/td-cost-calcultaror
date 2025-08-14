@extends('layouts.app')

@section('pageHeader')
    <x-page-header pageHeaderTitle="{{ __('td-cost-calcultaror::messages.product_details') }}" />
@endsection

@section('content')
    <div class="card shadow mb-4">
        <div class="card-header py-3 d-flex justify-content-between align-items-center">
            <h6 class="m-0 font-weight-bold text-primary">{{ __('td-cost-calcultaror::messages.product_details') }}</h6>
            <div>
                <a href="{{ route('td-cost-calcultaror.products.edit', $product->id) }}" class="btn btn-sm btn-primary">
                    <i class="fas fa-edit"></i> {{ __('td-cost-calcultaror::messages.edit') }}
                </a>
                <form action="{{ route('td-cost-calcultaror.products.destroy', $product->id) }}" method="POST" class="d-inline">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn btn-sm btn-danger" onclick="return confirm('{{ __('td-cost-calcultaror::messages.confirm_delete_product') }}')">
                        <i class="fas fa-trash"></i> {{ __('td-cost-calcultaror::messages.delete') }}
                    </button>
                </form>
            </div>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered">
                    <tbody>
                        <tr>
                            <th width="200">{{ __('td-cost-calcultaror::messages.name') }}</th>
                            <td>{{ $product->name }}</td>
                        </tr>
                        <tr>
                            <th>{{ __('td-cost-calcultaror::messages.description') }}</th>
                            <td>{{ $product->description ?: __('td-cost-calcultaror::messages.no_description') }}</td>
                        </tr>
                        @if($categoryModuleAvailable && $product->category)
                            <tr>
                                <th>{{ __('td-cost-calcultaror::messages.category') }}</th>
                                <td>{{ $product->category->name }}</td>
                            </tr>
                        @endif
                        <tr>
                            <th>{{ __('td-cost-calcultaror::messages.total_cost') }}</th>
                            <td>{{ number_format($product->calculateTotalCost(), 2) }}</td>
                        </tr>
                        <tr>
                            <th>{{ __('td-cost-calcultaror::messages.created_at') }}</th>
                            <td>{{ $product->created_at->format('Y-m-d H:i:s') }}</td>
                        </tr>
                        <tr>
                            <th>{{ __('td-cost-calcultaror::messages.updated_at') }}</th>
                            <td>{{ $product->updated_at->format('Y-m-d H:i:s') }}</td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <!-- Metadata section -->
            <div class="card mb-4 mt-4">
                <div class="card-header">
                    <h6 class="m-0 font-weight-bold text-primary">{{ __('td-cost-calcultaror::messages.additional_properties') }}</h6>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-bordered">
                            <tbody>
                                <tr>
                                    <th width="200">{{ __('td-cost-calcultaror::messages.calculation_model') }}</th>
                                    <td>
                                        @php
                                            $calculationModel = $product->getMetaField('calculation_model') ?: 'fixed';
                                        @endphp
                                        @if($calculationModel == 'fixed')
                                            {{ __('td-cost-calcultaror::messages.model_fixed') }}
                                        @elseif($calculationModel == 'per_user')
                                            {{ __('td-cost-calcultaror::messages.model_per_user') }}
                                        @elseif($calculationModel == 'per_hour')
                                            {{ __('td-cost-calcultaror::messages.model_per_hour') }}
                                        @elseif($calculationModel == 'per_unit')
                                            {{ __('td-cost-calcultaror::messages.model_per_unit') }}
                                        @endif
                                    </td>
                                </tr>
                                <tr>
                                    <th>{{ __('td-cost-calcultaror::messages.expected_users') }}</th>
                                    <td>{{ $product->getMetaField('expected_users') ?: __('td-cost-calcultaror::messages.not_specified') }}</td>
                                </tr>
                                <tr>
                                    <th>{{ __('td-cost-calcultaror::messages.notes') }}</th>
                                    <td>
                                        @if($product->getMetaField('notes'))
                                            {!! nl2br(e($product->getMetaField('notes'))) !!}
                                        @else
                                            {{ __('td-cost-calcultaror::messages.no_notes') }}
                                        @endif
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <!-- Cost Items Section -->
            <div class="card mb-4">
                <div class="card-header py-3 d-flex justify-content-between align-items-center">
                    <h6 class="m-0 font-weight-bold text-primary">{{ __('td-cost-calcultaror::messages.cost_breakdown') }}</h6>
                    <a href="{{ route('td-cost-calcultaror.products.edit', $product->id) }}" class="btn btn-sm btn-primary">
                        <i class="fas fa-plus"></i> {{ __('td-cost-calcultaror::messages.manage_cost_items') }}
                    </a>
                </div>
                <div class="card-body">
                    @if($product->costItems->count() > 0)
                        <div class="table-responsive">
                            <table class="table table-bordered" width="100%" cellspacing="0">
                                <thead>
                                    <tr>
                                        <th>{{ __('td-cost-calcultaror::messages.cost_item') }}</th>
                                        <th>{{ __('td-cost-calcultaror::messages.price') }}</th>
                                        <th>{{ __('td-cost-calcultaror::messages.period') }}</th>
                                        <th>{{ __('td-cost-calcultaror::messages.allocation_amount') }}</th>
                                        <th>{{ __('td-cost-calcultaror::messages.calculated_cost') }}</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($product->costAllocations as $allocation)
                                        <tr>
                                            <td>
                                                <a href="{{ route('td-cost-calcultaror.cost-items.show', $allocation->costItem->id) }}">
                                                    {{ $allocation->costItem->name }}
                                                </a>
                                                @if($categoryModuleAvailable && $allocation->costItem->category)
                                                    <span class="badge badge-info">{{ $allocation->costItem->category->name }}</span>
                                                @endif
                                            </td>
                                            <td>{{ number_format($allocation->costItem->price, 2) }}</td>
                                            <td>
                                                @if($allocation->costItem->period == 'month')
                                                    {{ __('td-cost-calcultaror::messages.period_month') }}
                                                @elseif($allocation->costItem->period == 'year')
                                                    {{ __('td-cost-calcultaror::messages.period_year') }}
                                                @elseif($allocation->costItem->period == 'hour')
                                                    {{ __('td-cost-calcultaror::messages.period_hour') }}
                                                @elseif($allocation->costItem->period == 'minute')
                                                    {{ __('td-cost-calcultaror::messages.period_minute') }}
                                                @endif
                                            </td>
                                            <td>{{ $allocation->amount }}</td>
                                            <td>{{ number_format($product->calculateCostItemAllocation($allocation), 2) }}</td>
                                        </tr>
                                    @endforeach
                                </tbody>
                                <tfoot>
                                    <tr>
                                        <th colspan="4" class="text-right">{{ __('td-cost-calcultaror::messages.total_cost') }}:</th>
                                        <th>{{ number_format($product->calculateTotalCost(), 2) }}</th>
                                    </tr>
                                </tfoot>
                            </table>
                        </div>
                    @else
                        <div class="alert alert-info">
                            {{ __('td-cost-calcultaror::messages.no_cost_items_added') }}
                        </div>
                    @endif
                </div>
            </div>

            <!-- Cost breakdown visualization -->
            <div class="card mb-4">
                <div class="card-header">
                    <h6 class="m-0 font-weight-bold text-primary">{{ __('td-cost-calcultaror::messages.cost_visualization') }}</h6>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <canvas id="costBreakdownChart" width="100%" height="50"></canvas>
                        </div>
                        <div class="col-md-6">
                            <div class="table-responsive">
                                <table class="table table-bordered" width="100%" cellspacing="0">
                                    <thead>
                                        <tr>
                                            <th>{{ __('td-cost-calcultaror::messages.calculation_scenario') }}</th>
                                            <th>{{ __('td-cost-calcultaror::messages.calculated_cost') }}</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @php
                                            $calculationModel = $product->getMetaField('calculation_model') ?: 'fixed';
                                            $expectedUsers = (int)$product->getMetaField('expected_users') ?: 1;
                                        @endphp
                                        
                                        @if($calculationModel == 'per_user')
                                            <tr>
                                                <td>{{ __('td-cost-calcultaror::messages.cost_per_user') }}</td>
                                                <td>{{ number_format($product->calculateTotalCost() / $expectedUsers, 2) }}</td>
                                            </tr>
                                            <tr>
                                                <td>{{ __('td-cost-calcultaror::messages.cost_for_users', ['count' => $expectedUsers]) }}</td>
                                                <td>{{ number_format($product->calculateTotalCost(), 2) }}</td>
                                            </tr>
                                            <tr>
                                                <td>{{ __('td-cost-calcultaror::messages.cost_for_users', ['count' => $expectedUsers * 2]) }}</td>
                                                <td>{{ number_format($product->calculateTotalCost() * 2, 2) }}</td>
                                            </tr>
                                        @elseif($calculationModel == 'per_hour')
                                            <tr>
                                                <td>{{ __('td-cost-calcultaror::messages.cost_per_hour') }}</td>
                                                <td>{{ number_format($product->calculateTotalCost(), 2) }}</td>
                                            </tr>
                                            <tr>
                                                <td>{{ __('td-cost-calcultaror::messages.cost_per_day', ['hours' => 8]) }}</td>
                                                <td>{{ number_format($product->calculateTotalCost() * 8, 2) }}</td>
                                            </tr>
                                            <tr>
                                                <td>{{ __('td-cost-calcultaror::messages.cost_per_month', ['hours' => 160]) }}</td>
                                                <td>{{ number_format($product->calculateTotalCost() * 160, 2) }}</td>
                                            </tr>
                                        @else
                                            <tr>
                                                <td>{{ __('td-cost-calcultaror::messages.total_cost') }}</td>
                                                <td>{{ number_format($product->calculateTotalCost(), 2) }}</td>
                                            </tr>
                                            <tr>
                                                <td>{{ __('td-cost-calcultaror::messages.monthly_cost') }}</td>
                                                <td>{{ number_format($product->calculateMonthlyTotalCost(), 2) }}</td>
                                            </tr>
                                            <tr>
                                                <td>{{ __('td-cost-calcultaror::messages.yearly_cost') }}</td>
                                                <td>{{ number_format($product->calculateYearlyTotalCost(), 2) }}</td>
                                            </tr>
                                        @endif
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    $(document).ready(function() {
        // Cost breakdown chart
        const costBreakdownCtx = document.getElementById('costBreakdownChart').getContext('2d');
        
        const costBreakdownLabels = [
            @foreach($product->costAllocations as $allocation)
                '{{ $allocation->costItem->name }}',
            @endforeach
        ];
        
        const costBreakdownData = [
            @foreach($product->costAllocations as $allocation)
                {{ $product->calculateCostItemAllocation($allocation) }},
            @endforeach
        ];
        
        // Generate random colors for the chart
        const colors = [];
        for (let i = 0; i < costBreakdownLabels.length; i++) {
            const r = Math.floor(Math.random() * 200);
            const g = Math.floor(Math.random() * 200);
            const b = Math.floor(Math.random() * 200);
            colors.push(`rgba(${r}, ${g}, ${b}, 0.7)`);
        }
        
        const costBreakdownChart = new Chart(costBreakdownCtx, {
            type: 'pie',
            data: {
                labels: costBreakdownLabels,
                datasets: [{
                    data: costBreakdownData,
                    backgroundColor: colors
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
                                const label = context.label || '';
                                const value = context.raw;
                                const total = context.dataset.data.reduce((acc, val) => acc + val, 0);
                                const percentage = Math.round((value / total) * 100);
                                return `${label}: ${value.toFixed(2)} (${percentage}%)`;
                            }
                        }
                    }
                }
            }
        });
    });
</script>
@endpush
