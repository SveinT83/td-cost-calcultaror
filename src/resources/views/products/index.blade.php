@extends('layouts.app')

@section('pageHeader')
    <x-page-header pageHeaderTitle="{{ __('td-cost-calcultaror::messages.products') }}" />
@endsection

@section('content')
    <div class="card shadow mb-4">
        <div class="card-header py-3 d-flex justify-content-between align-items-center">
            <h6 class="m-0 font-weight-bold text-primary">{{ __('td-cost-calcultaror::messages.products') }}</h6>
            <div>
                <button type="button" class="btn btn-sm btn-secondary mr-2" id="batch-actions-btn" style="display: none;" data-toggle="modal" data-target="#batchActionsModal">
                    <i class="fas fa-tasks"></i> {{ __('td-cost-calcultaror::messages.batch_actions') }}
                </button>
                <a href="{{ route('td-cost-calcultaror.products.create') }}" class="btn btn-primary btn-sm">
                    <i class="fas fa-plus"></i> {{ __('td-cost-calcultaror::messages.add_product') }}
                </a>
            </div>
        </div>
        <div class="card-body">
            @if(session('success'))
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    {{ session('success') }}
                    <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
            @endif
            
            @if(count($products) > 0)
                <div class="table-responsive">
                    <table class="table table-bordered" id="dataTable" width="100%" cellspacing="0">
                        <thead>
                            <tr>
                                <th width="5%">
                                    <div class="custom-control custom-checkbox">
                                        <input type="checkbox" class="custom-control-input" id="select-all">
                                        <label class="custom-control-label" for="select-all"></label>
                                    </div>
                                </th>
                                <th>{{ __('td-cost-calcultaror::messages.name') }}</th>
                                <th>{{ __('td-cost-calcultaror::messages.description') }}</th>
                                <th>{{ __('td-cost-calcultaror::messages.cost_items_count') }}</th>
                                <th>{{ __('td-cost-calcultaror::messages.total_cost') }}</th>
                                <th>{{ __('td-cost-calcultaror::messages.created_at') }}</th>
                                <th>{{ __('td-cost-calcultaror::messages.actions') }}</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($products as $product)
                                <tr>
                                    <td>
                                        <div class="custom-control custom-checkbox">
                                            <input type="checkbox" class="custom-control-input item-checkbox" id="item-{{ $product->id }}" value="{{ $product->id }}">
                                            <label class="custom-control-label" for="item-{{ $product->id }}"></label>
                                        </div>
                                    </td>
                                    <td>{{ $product->name }}</td>
                                    <td>{{ Str::limit($product->description ?? '', 50) }}</td>
                                    <td>{{ $product->costItems->count() }}</td>
                                    <td>{{ number_format($product->calculateTotalCost(), 2) }}</td>
                                    <td>{{ $product->created_at->format('Y-m-d') }}</td>
                                    <td>
                                        <a href="{{ route('td-cost-calcultaror.products.show', $product->id) }}" class="btn btn-info btn-sm">
                                            <i class="fas fa-eye"></i> {{ __('td-cost-calcultaror::messages.view') }}
                                        </a>
                                        <a href="{{ route('td-cost-calcultaror.products.edit', $product->id) }}" class="btn btn-primary btn-sm">
                                            <i class="fas fa-edit"></i> {{ __('td-cost-calcultaror::messages.edit') }}
                                        </a>
                                        <form action="{{ route('td-cost-calcultaror.products.destroy', $product->id) }}" method="POST" class="d-inline">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-danger btn-sm" onclick="return confirm('{{ __('td-cost-calcultaror::messages.confirm_delete_product') }}')">
                                                <i class="fas fa-trash"></i> {{ __('td-cost-calcultaror::messages.delete') }}
                                            </button>
                                        </form>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                
                <div class="mt-3">
                    {{ $products->links() }}
                </div>
            @else
                <div class="alert alert-info">
                    {{ __('td-cost-calcultaror::messages.no_products') }}
                </div>
            @endif
        </div>
    </div>
    
    <!-- Batch Actions Modal -->
    <div class="modal fade" id="batchActionsModal" tabindex="-1" role="dialog" aria-labelledby="batchActionsModalLabel" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <form action="{{ route('td-cost-calcultaror.products.batch') }}" method="POST" id="batch-form">
                    @csrf
                    <div class="modal-header">
                        <h5 class="modal-title" id="batchActionsModalLabel">{{ __('td-cost-calcultaror::messages.batch_actions') }}</h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body">
                        <p id="selected-count">{{ __('td-cost-calcultaror::messages.selected_items', ['count' => 0]) }}</p>
                        <div id="batch-item-list" class="alert alert-info mb-3" style="max-height: 200px; overflow-y: auto; display: none;">
                            <ul></ul>
                        </div>
                        
                        <div class="form-group">
                            <label for="batch-operation">{{ __('td-cost-calcultaror::messages.select_operation') }}</label>
                            <select name="operation" id="batch-operation" class="form-control">
                                <option value="">{{ __('td-cost-calcultaror::messages.select_operation') }}</option>
                                <option value="delete">{{ __('td-cost-calcultaror::messages.batch_delete') }}</option>
                                <option value="update_calculation_model">{{ __('td-cost-calcultaror::messages.batch_update_calculation_model') }}</option>
                            </select>
                        </div>
                        
                        <!-- Calculation model selection (hidden by default) -->
                        <div class="form-group" id="calculation-model-group" style="display: none;">
                            <label for="calculation_model">{{ __('td-cost-calcultaror::messages.calculation_model') }}</label>
                            <select name="calculation_model" id="calculation_model" class="form-control">
                                @foreach($calculation_models as $value => $label)
                                    <option value="{{ $value }}">{{ $label }}</option>
                                @endforeach
                            </select>
                        </div>
                        
                        <div class="alert alert-warning" id="delete-warning" style="display: none;">
                            {{ __('td-cost-calcultaror::messages.batch_delete_warning') }}
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">{{ __('td-cost-calcultaror::messages.cancel') }}</button>
                        <button type="submit" class="btn btn-primary" id="batch-submit" disabled>{{ __('td-cost-calcultaror::messages.apply') }}</button>
                    </div>
                    
                    <!-- Hidden inputs for selected items -->
                    <div id="selected-items-container"></div>
                </form>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
<script>
    $(document).ready(function() {
        // Initialize DataTable
        $('#dataTable').DataTable({
            paging: false,
            searching: true,
            ordering: true,
            info: false
        });
        
        // Handle select all checkbox
        $('#select-all').on('change', function() {
            $('.item-checkbox').prop('checked', $(this).is(':checked'));
            updateBatchButton();
        });
        
        // Handle individual checkboxes
        $('.item-checkbox').on('change', function() {
            updateBatchButton();
            
            // If any checkbox is unchecked, uncheck the "select all" checkbox
            if (!$(this).is(':checked')) {
                $('#select-all').prop('checked', false);
            }
            // If all checkboxes are checked, check the "select all" checkbox
            else if ($('.item-checkbox:checked').length === $('.item-checkbox').length) {
                $('#select-all').prop('checked', true);
            }
        });
        
        // Show/hide operation specific fields
        $('#batch-operation').on('change', function() {
            const operation = $(this).val();
            
            // Hide all operation-specific elements
            $('#calculation-model-group, #delete-warning').hide();
            
            // Show elements based on operation
            switch (operation) {
                case 'update_calculation_model':
                    $('#calculation-model-group').show();
                    break;
                case 'delete':
                    $('#delete-warning').show();
                    break;
            }
            
            // Enable/disable submit button
            $('#batch-submit').prop('disabled', operation === '');
        });
        
        // Handle batch form submission
        $('#batch-form').on('submit', function() {
            // Clear previous inputs
            $('#selected-items-container').empty();
            
            // Add selected items
            $('.item-checkbox:checked').each(function() {
                const itemId = $(this).val();
                $('#selected-items-container').append(
                    `<input type="hidden" name="items[]" value="${itemId}">`
                );
            });
            
            // Confirm deletions
            if ($('#batch-operation').val() === 'delete') {
                return confirm("{{ __('td-cost-calcultaror::messages.batch_delete_confirm') }}");
            }
            
            return true;
        });
        
        // Update batch button visibility based on selections
        function updateBatchButton() {
            const selectedCount = $('.item-checkbox:checked').length;
            
            // Show/hide batch action button
            $('#batch-actions-btn').toggle(selectedCount > 0);
            
            // Update count in modal
            $('#selected-count').text("{{ __('td-cost-calcultaror::messages.selected_items') }}".replace(':count', selectedCount));
            
            // Show list of selected items
            const $list = $('#batch-item-list ul').empty();
            if (selectedCount > 0 && selectedCount <= 10) {
                $('#batch-item-list').show();
                $('.item-checkbox:checked').each(function() {
                    const itemName = $(this).closest('tr').find('td:nth-child(2)').text();
                    $list.append(`<li>${itemName}</li>`);
                });
            } else if (selectedCount > 10) {
                $('#batch-item-list').show();
                $list.append(`<li>{{ __('td-cost-calcultaror::messages.many_items_selected') }}</li>`);
            } else {
                $('#batch-item-list').hide();
            }
        }
    });
</script>
@endpush
