@extends('layouts.app')

@section('pageHeader')
    <x-page-header pageHeaderTitle="{{ __('td-cost-calcultaror::messages.create_product') }}" />
@endsection

@section('content')
    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">{{ __('td-cost-calcultaror::messages.create_product') }}</h6>
        </div>
        <div class="card-body">
            <form action="{{ route('td-cost-calcultaror.products.store') }}" method="POST">
                @csrf
                
                <div class="form-group">
                    <label for="name">{{ __('td-cost-calcultaror::messages.name') }} <span class="text-danger">*</span></label>
                    <input type="text" class="form-control @error('name') is-invalid @enderror" id="name" name="name" 
                           value="{{ old('name') }}" required>
                    @error('name')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
                
                <div class="form-group">
                    <label for="description">{{ __('td-cost-calcultaror::messages.description') }}</label>
                    <textarea class="form-control @error('description') is-invalid @enderror" id="description" 
                              name="description" rows="3">{{ old('description') }}</textarea>
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
                                <option value="{{ $id }}" @if(old('category_id') == $id) selected @endif>{{ $name }}</option>
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
                        <div class="form-group">
                            <label for="calculation_model">{{ __('td-cost-calcultaror::messages.calculation_model') }}</label>
                            <select class="form-control @error('calculation_model') is-invalid @enderror" id="calculation_model" name="calculation_model">
                                <option value="">{{ __('td-cost-calcultaror::messages.select_model') }}</option>
                                @foreach($calculation_models as $value => $label)
                                    <option value="{{ $value }}" @if(old('calculation_model') == $value) selected @endif>
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
                                   value="{{ old('metadata.expected_users') }}" min="0">
                            <small class="form-text text-muted">
                                {{ __('td-cost-calcultaror::messages.expected_users_help') }}
                            </small>
                        </div>
                        
                        <div class="form-group">
                            <label for="metadata_notes">{{ __('td-cost-calcultaror::messages.notes') }}</label>
                            <textarea class="form-control" id="metadata_notes" name="metadata[notes]" rows="3">{{ old('metadata.notes') }}</textarea>
                        </div>
                    </div>
                </div>
                
                <div class="form-group text-right">
                    <a href="{{ route('td-cost-calcultaror.products.index') }}" class="btn btn-secondary">
                        {{ __('td-cost-calcultaror::messages.cancel') }}
                    </a>
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save"></i> {{ __('td-cost-calcultaror::messages.save_and_add_cost_items') }}
                    </button>
                </div>
            </form>
        </div>
    </div>
@endsection
