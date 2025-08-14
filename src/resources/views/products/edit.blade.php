@extends('layouts.app')

@section('pageHeader')
    <x-page-header pageHeaderTitle="{{ __('td-cost-calcultaror::messages.edit_product') }}" />
@endsection

@section('content')
    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">{{ __('td-cost-calcultaror::messages.edit_product') }}</h6>
        </div>
        <div class="card-body">
            <form action="{{ route('td-cost-calcultaror.products.update', $product->id) }}" method="POST">
                @csrf
                @method('PUT')
                
                <div class="form-group">
                    <label for="name">{{ __('td-cost-calcultaror::messages.name') }} <span class="text-danger">*</span></label>
                    <input type="text" class="form-control @error('name') is-invalid @enderror" id="name" name="name" 
                           value="{{ old('name', $product->name) }}" required>
                    @error('name')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
                
                <div class="form-group">
                    <label for="description">{{ __('td-cost-calcultaror::messages.description') }}</label>
                    <textarea class="form-control @error('description') is-invalid @enderror" id="description" 
                              name="description" rows="3">{{ old('description', $product->description) }}</textarea>
                    @error('description')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
                
                @if($categoryModuleAvailable)
                    <div class="form-group">
                        <label for="category_id">{{ __('td-cost-calcultaror::messages.category') }}</label>
                        <select class="form-control @error('category_id') is-invalid @enderror" id="category_id" name="category_id">
                            <option value="">{{ __('td-cost-calcultaror::messages.select_category') }}</option>
                            @foreach($categories as $id => $name)
                                <option value="{{ $id }}" @if(old('category_id', $product->category_id) == $id) selected @endif>{{ $name }}</option>
                            @endforeach
                        </select>
                        @error('category_id')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                @endif
                
                <!-- Metadata fields -->
                <div class="card mb-4">
                    <div class="card-header">
                        <h6 class="m-0 font-weight-bold text-primary">{{ __('td-cost-calcultaror::messages.additional_properties') }}</h6>
                    </div>
                    <div class="card-body">
                        <!-- Calculation Model - Direct field, not metadata -->
                        <div class="form-group">
                            <label for="calculation_model">{{ __('td-cost-calcultaror::messages.calculation_model') }}</label>
                            <select class="form-control @error('calculation_model') is-invalid @enderror" 
                                    id="calculation_model" name="calculation_model">
                                <option value="" disabled selected>{{ __('td-cost-calcultaror::messages.select_calculation_model') }}</option>
                                @foreach($calculation_models as $value => $label)
                                    <option value="{{ $value }}" @if(old('calculation_model', $product->calculation_model) == $value) selected @endif>
                                        {{ $label }}
                                    </option>
                                @endforeach
                            </select>
                            @error('calculation_model')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <small class="form-text text-muted">
                                {{ __('td-cost-calcultaror::messages.calculation_model_help') }}
                            </small>
                        </div>
                        
                        <div class="form-group">
                            <label for="metadata_expected_users">{{ __('td-cost-calcultaror::messages.expected_users') }}</label>
                            <input type="number" class="form-control" id="metadata_expected_users" name="metadata[expected_users]" 
                                   value="{{ old('metadata.expected_users', $product->getMetaField('expected_users')) }}" min="0">
                            <small class="form-text text-muted">
                                {{ __('td-cost-calcultaror::messages.expected_users_help') }}
                            </small>
                        </div>
                        
                        <div class="form-group">
                            <label for="metadata_notes">{{ __('td-cost-calcultaror::messages.notes') }}</label>
                            <textarea class="form-control" id="metadata_notes" name="metadata[notes]" rows="3">{{ old('metadata.notes', $product->getMetaField('notes')) }}</textarea>
                        </div>
                    </div>
                </div>
                
                <div class="form-group text-right">
                    <a href="{{ route('td-cost-calcultaror.products.index') }}" class="btn btn-secondary">
                        {{ __('td-cost-calcultaror::messages.cancel') }}
                    </a>
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save"></i> {{ __('td-cost-calcultaror::messages.save') }}
                    </button>
                </div>
            </form>
        </div>
    </div>
    
    <!-- Cost Items Section -->
    <div class="card shadow mb-4">
        <div class="card-header py-3 d-flex justify-content-between align-items-center">
            <h6 class="m-0 font-weight-bold text-primary">{{ __('td-cost-calcultaror::messages.cost_items') }}</h6>
            <div>
                <button type="button" class="btn btn-primary btn-sm" id="addCostItemButton" data-bs-toggle="modal" data-bs-target="#addCostItemModal">
                    <i class="fas fa-plus"></i> {{ __('td-cost-calcultaror::messages.add_cost_item') }}
                </button>
                <a href="javascript:void(0)" onclick="showAddCostItemModal()" class="btn btn-outline-primary btn-sm ml-2">
                    <i class="fas fa-plus-circle"></i> {{ __('td-cost-calcultaror::messages.add_cost_item') }} (Alt)
                </a>
            </div>
        </div>
        <div class="card-body">
            @if($product->costItems->count() > 0)
                <div class="table-responsive">
                    <table class="table table-bordered" width="100%" cellspacing="0">
                        <thead>
                            <tr>
                                <th>{{ __('td-cost-calcultaror::messages.name') }}</th>
                                <th>{{ __('td-cost-calcultaror::messages.price') }}</th>
                                <th>{{ __('td-cost-calcultaror::messages.period') }}</th>
                                <th>{{ __('td-cost-calcultaror::messages.allocation_amount') }}</th>
                                <th>{{ __('td-cost-calcultaror::messages.calculated_cost') }}</th>
                                <th>{{ __('td-cost-calcultaror::messages.actions') }}</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($product->costAllocations as $allocation)
                                <tr>
                                    <td>
                                        <a href="{{ route('td-cost-calcultaror.cost-items.show', $allocation->costItem->id) }}">
                                            {{ $allocation->costItem->name }}
                                        </a>
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
                                    <td>{{ $allocation->allocation_value }}</td>
                                    <td>{{ number_format($product->calculateCostItemAllocation($allocation), 2) }}</td>
                                    <td>
                                        <button type="button" class="btn btn-primary btn-sm" 
                                                onclick="editAllocation({{ $allocation->id }}, '{{ addslashes($allocation->costItem->name) }}', '{{ $allocation->allocation_type }}', {{ $allocation->allocation_value }})">
                                            <i class="fas fa-edit"></i> {{ __('td-cost-calcultaror::messages.edit') }}
                                        </button>
                                        <form action="{{ route('td-cost-calcultaror.products.detach-cost-item', [$product->id, $allocation->id]) }}" method="POST" class="d-inline">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-danger btn-sm" onclick="return confirm('{{ __('td-cost-calcultaror::messages.confirm_remove_cost_item') }}')">
                                                <i class="fas fa-trash"></i> {{ __('td-cost-calcultaror::messages.remove') }}
                                            </button>
                                        </form>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                        <tfoot>
                            <tr>
                                <th colspan="4" class="text-right">{{ __('td-cost-calcultaror::messages.total_cost') }}:</th>
                                <th>{{ number_format($product->calculateTotalCost(), 2) }}</th>
                                <th></th>
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
    
    <!-- Add Cost Item Modal -->
    <div class="modal fade" id="addCostItemModal" tabindex="-1" aria-labelledby="addCostItemModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <form action="{{ route('td-cost-calcultaror.products.attach-cost-item', $product->id) }}" method="POST">
                    @csrf
                    <div class="modal-header">
                        <h5 class="modal-title" id="addCostItemModalLabel">{{ __('td-cost-calcultaror::messages.add_cost_item') }}</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <div class="form-group">
                            <label for="cost_item_id">{{ __('td-cost-calcultaror::messages.cost_item') }} <span class="text-danger">*</span></label>
                            <select class="form-control" id="cost_item_id" name="cost_item_id" required>
                                <option value="">{{ __('td-cost-calcultaror::messages.select_cost_item') }}</option>
                                @foreach($availableCostItems as $costItem)
                                    <option value="{{ $costItem->id }}">{{ $costItem->name }} ({{ number_format($costItem->price, 2) }} / {{ $costItem->period }})</option>
                                @endforeach
                            </select>
                        </div>
                        
                        <div class="form-group">
                            <label for="allocation_type">{{ __('td-cost-calcultaror::messages.allocation_type') }} <span class="text-danger">*</span></label>
                            <select class="form-control" id="allocation_type" name="allocation_type" required>
                                <option value="fixed">{{ __('td-cost-calcultaror::messages.fixed_allocation') }}</option>
                                <option value="per_user">{{ __('td-cost-calcultaror::messages.per_user_allocation') }}</option>
                                <option value="per_resource_unit">{{ __('td-cost-calcultaror::messages.per_resource_unit_allocation') }}</option>
                            </select>
                        </div>
                        
                        <div class="form-group">
                            <label for="allocation_value">{{ __('td-cost-calcultaror::messages.allocation_value') }} <span class="text-danger">*</span></label>
                            <input type="number" class="form-control" id="allocation_value" name="allocation_value" min="0.01" step="0.01" value="1" required>
                            <small class="form-text text-muted">
                                {{ __('td-cost-calcultaror::messages.allocation_amount_help') }}
                            </small>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">{{ __('td-cost-calcultaror::messages.cancel') }}</button>
                        <button type="submit" class="btn btn-primary">{{ __('td-cost-calcultaror::messages.add') }}</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    
    <!-- Edit Allocation Modal -->
    <div class="modal fade" id="editAllocationModal" tabindex="-1" aria-labelledby="editAllocationModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <form id="editAllocationForm" action="" method="POST">
                    @csrf
                    @method('PUT')
                    <div class="modal-header">
                        <h5 class="modal-title" id="editAllocationModalLabel">{{ __('td-cost-calcultaror::messages.edit_allocation') }}</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <div class="form-group">
                            <label for="edit_cost_item_name">{{ __('td-cost-calcultaror::messages.cost_item') }}</label>
                            <input type="text" class="form-control" id="edit_cost_item_name" readonly>
                        </div>
                        
                        <div class="form-group">
                            <label for="edit_allocation_type">{{ __('td-cost-calcultaror::messages.allocation_type') }} <span class="text-danger">*</span></label>
                            <select class="form-control" id="edit_allocation_type" name="allocation_type" required>
                                <option value="fixed">{{ __('td-cost-calcultaror::messages.fixed_allocation') }}</option>
                                <option value="per_user">{{ __('td-cost-calcultaror::messages.allocation_per_user') }}</option>
                                <option value="per_resource_unit">{{ __('td-cost-calcultaror::messages.allocation_per_resource_unit') }}</option>
                            </select>
                        </div>
                        
                        <div class="form-group">
                            <label for="edit_amount">{{ __('td-cost-calcultaror::messages.allocation_value') }} <span class="text-danger">*</span></label>
                            <input type="number" class="form-control" id="edit_amount" name="allocation_value" min="0.01" step="0.01" required>
                            <small class="form-text text-muted">
                                {{ __('td-cost-calcultaror::messages.allocation_amount_help') }}
                            </small>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">{{ __('td-cost-calcultaror::messages.cancel') }}</button>
                        <button type="submit" class="btn btn-primary">{{ __('td-cost-calcultaror::messages.save') }}</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection

<script>
    // Global function to edit allocation with modal
    function editAllocation(allocationId, costItemName, currentType, currentAmount) {
        console.log('=== editAllocation called ===');
        console.log('allocationId:', allocationId);
        console.log('costItemName:', costItemName);
        console.log('currentType:', currentType);
        console.log('currentAmount:', currentAmount);
        
        try {
            // Populate the edit modal with current values
            document.getElementById('edit_cost_item_name').value = costItemName;
            document.getElementById('edit_allocation_type').value = currentType;
            document.getElementById('edit_amount').value = currentAmount;
            
            // Set up the form action for the specific allocation
            const editForm = document.getElementById('editAllocationForm');
            editForm.action = '{{ route('td-cost-calcultaror.products.update-allocation', [$product->id, '']) }}/' + allocationId;
            
            console.log('Form action set to:', editForm.action);
            console.log('Cost item name set to:', costItemName);
            
            // Show the modal
            if (typeof bootstrap !== 'undefined') {
                var editModal = new bootstrap.Modal(document.getElementById('editAllocationModal'));
                editModal.show();
            } else if (typeof $ !== 'undefined') {
                $('#editAllocationModal').modal('show');
            } else {
                console.error('Neither Bootstrap nor jQuery is available');
            }
        } catch (error) {
            console.error('Error in editAllocation:', error);
            alert('{{ __('td-cost-calcultaror::messages.error_occurred') }}: ' + error.message);
        }
    }
    
    // Global function to show the add cost item modal
    function showAddCostItemModal() {
        console.log('Showing add cost item modal via global function');
        try {
            if (typeof bootstrap !== 'undefined') {
                var addCostItemModal = new bootstrap.Modal(document.getElementById('addCostItemModal'));
                addCostItemModal.show();
            } else if (typeof $ !== 'undefined') {
                $('#addCostItemModal').modal('show');
            } else {
                console.error('Neither Bootstrap nor jQuery is available');
            }
        } catch (error) {
            console.error('Error showing modal:', error);
        }
    }
    
    document.addEventListener('DOMContentLoaded', function() {
        console.log('Product edit script loaded');
        console.log('editAllocation function available:', typeof editAllocation);
    });
</script>