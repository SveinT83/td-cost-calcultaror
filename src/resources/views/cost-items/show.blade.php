@extends('layouts.app')

@section('pageHeader')
    <x-page-header pageHeaderTitle="{{ __('td-cost-calcultaror::messages.cost_item_details') }}" />
@endsection

@section('content')
    <div class="card shadow mb-4">
        <div class="card-header py-3 d-flex justify-content-between align-items-center">
            <h6 class="m-0 font-weight-bold text-primary">{{ __('td-cost-calcultaror::messages.cost_item_details') }}</h6>
            <div>
                <a href="{{ route('td-cost-calcultaror.cost-items.edit', $costItem->id) }}" class="btn btn-sm btn-primary">
                    <i class="fas fa-edit"></i> {{ __('td-cost-calcultaror::messages.edit') }}
                </a>
                <form action="{{ route('td-cost-calcultaror.cost-items.destroy', $costItem->id) }}" method="POST" class="d-inline">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn btn-sm btn-danger" onclick="return confirm('{{ __('td-cost-calcultaror::messages.confirm_delete') }}')">
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
                            <td>{{ $costItem->name }}</td>
                        </tr>
                        <tr>
                            <th>{{ __('td-cost-calcultaror::messages.price') }}</th>
                            <td>{{ number_format($costItem->price, 2) }}</td>
                        </tr>
                        <tr>
                            <th>{{ __('td-cost-calcultaror::messages.period') }}</th>
                            <td>
                                @if($costItem->period == 'month')
                                    {{ __('td-cost-calcultaror::messages.period_month') }}
                                @elseif($costItem->period == 'year')
                                    {{ __('td-cost-calcultaror::messages.period_year') }}
                                @elseif($costItem->period == 'hour')
                                    {{ __('td-cost-calcultaror::messages.period_hour') }}
                                @elseif($costItem->period == 'minute')
                                    {{ __('td-cost-calcultaror::messages.period_minute') }}
                                @endif
                            </td>
                        </tr>
                        @if($categoryModuleAvailable && $costItem->category)
                            <tr>
                                <th>{{ __('td-cost-calcultaror::messages.category') }}</th>
                                <td>{{ $costItem->category->name }}</td>
                            </tr>
                        @endif
                        <tr>
                            <th>{{ __('td-cost-calcultaror::messages.created_at') }}</th>
                            <td>{{ $costItem->created_at->format('Y-m-d H:i:s') }}</td>
                        </tr>
                        <tr>
                            <th>{{ __('td-cost-calcultaror::messages.updated_at') }}</th>
                            <td>{{ $costItem->updated_at->format('Y-m-d H:i:s') }}</td>
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
                                    <th width="200">{{ __('td-cost-calcultaror::messages.lifetime_months') }}</th>
                                    <td>
                                        {{ $costItem->getMetaField('lifetime') ?: __('td-cost-calcultaror::messages.not_specified') }}
                                        @if($costItem->getMetaField('lifetime'))
                                            {{ __('td-cost-calcultaror::messages.months') }}
                                        @endif
                                    </td>
                                </tr>
                                <tr>
                                    <th>{{ __('td-cost-calcultaror::messages.capacity') }}</th>
                                    <td>{{ $costItem->getMetaField('capacity') ?: __('td-cost-calcultaror::messages.not_specified') }}</td>
                                </tr>
                                <tr>
                                    <th>{{ __('td-cost-calcultaror::messages.notes') }}</th>
                                    <td>
                                        @if($costItem->getMetaField('notes'))
                                            {!! nl2br(e($costItem->getMetaField('notes'))) !!}
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

            <!-- Products using this cost item -->
            <div class="card mb-4">
                <div class="card-header">
                    <h6 class="m-0 font-weight-bold text-primary">{{ __('td-cost-calcultaror::messages.used_in_products') }}</h6>
                </div>
                <div class="card-body">
                    @if($costItem->products->count() > 0)
                        <div class="table-responsive">
                            <table class="table table-bordered" width="100%" cellspacing="0">
                                <thead>
                                    <tr>
                                        <th>{{ __('td-cost-calcultaror::messages.product') }}</th>
                                        <th>{{ __('td-cost-calcultaror::messages.allocation_amount') }}</th>
                                        <th>{{ __('td-cost-calcultaror::messages.actions') }}</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($costItem->costAllocations as $allocation)
                                        <tr>
                                            <td>
                                                <a href="{{ route('td-cost-calcultaror.products.show', $allocation->product->id) }}">
                                                    {{ $allocation->product->name }}
                                                </a>
                                            </td>
                                            <td>{{ $allocation->amount }}</td>
                                            <td>
                                                <a href="{{ route('td-cost-calcultaror.products.show', $allocation->product->id) }}" class="btn btn-sm btn-info">
                                                    <i class="fas fa-eye"></i> {{ __('td-cost-calcultaror::messages.view') }}
                                                </a>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @else
                        <div class="alert alert-info">
                            {{ __('td-cost-calcultaror::messages.no_products_using_cost_item') }}
                        </div>
                    @endif
                </div>
            </div>

            <!-- Change history -->
            <div class="card mb-4">
                <div class="card-header">
                    <h6 class="m-0 font-weight-bold text-primary">{{ __('td-cost-calcultaror::messages.change_history') }}</h6>
                </div>
                <div class="card-body">
                    @if($costItem->logs->count() > 0)
                        <div class="table-responsive">
                            <table class="table table-bordered" width="100%" cellspacing="0">
                                <thead>
                                    <tr>
                                        <th>{{ __('td-cost-calcultaror::messages.date') }}</th>
                                        <th>{{ __('td-cost-calcultaror::messages.user') }}</th>
                                        <th>{{ __('td-cost-calcultaror::messages.field') }}</th>
                                        <th>{{ __('td-cost-calcultaror::messages.old_value') }}</th>
                                        <th>{{ __('td-cost-calcultaror::messages.new_value') }}</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($costItem->logs()->orderBy('created_at', 'desc')->get() as $log)
                                        <tr>
                                            <td>{{ $log->created_at->format('Y-m-d H:i:s') }}</td>
                                            <td>{{ $log->user ? $log->user->name : __('td-cost-calcultaror::messages.system') }}</td>
                                            <td>{{ $log->field_name }}</td>
                                            <td>{{ $log->formatted_old_value }}</td>
                                            <td>{{ $log->formatted_new_value }}</td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @else
                        <div class="alert alert-info">
                            {{ __('td-cost-calcultaror::messages.no_change_history') }}
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
@endsection
