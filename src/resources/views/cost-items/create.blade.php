@extends('layouts.app')

@section('pageHeader')
    <x-page-header pageHeaderTitle="{{ __('td-cost-calcultaror::messages.create_cost_item') }}" />
@endsection

@section('content')
    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">{{ __('td-cost-calcultaror::messages.create_cost_item') }}</h6>
        </div>
        <div class="card-body">
            <form action="{{ route('td-cost-calcultaror.cost-items.store') }}" method="POST">
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
                    <label for="price">{{ __('td-cost-calcultaror::messages.price') }} <span class="text-danger">*</span></label>
                    <div class="input-group">
                        <div class="input-group-prepend">
                            <span class="input-group-text"><i class="fas fa-dollar-sign"></i></span>
                        </div>
                        <input type="number" class="form-control @error('price') is-invalid @enderror" id="price" 
                               name="price" value="{{ old('price') }}" step="0.01" min="0" required>
                        @error('price')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>
                
                <div class="form-group">
                    <label for="period">{{ __('td-cost-calcultaror::messages.period') }} <span class="text-danger">*</span></label>
                    <select class="form-control @error('period') is-invalid @enderror" id="period" name="period" required>
                        <option value="month" @if(old('period') == 'month') selected @endif>
                            {{ __('td-cost-calcultaror::messages.period_month') }}
                        </option>
                        <option value="year" @if(old('period') == 'year') selected @endif>
                            {{ __('td-cost-calcultaror::messages.period_year') }}
                        </option>
                        <option value="hour" @if(old('period') == 'hour') selected @endif>
                            {{ __('td-cost-calcultaror::messages.period_hour') }}
                        </option>
                        <option value="minute" @if(old('period') == 'minute') selected @endif>
                            {{ __('td-cost-calcultaror::messages.period_minute') }}
                        </option>
                    </select>
                    @error('period')
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
                            <label for="metadata_lifetime">{{ __('td-cost-calcultaror::messages.lifetime_months') }}</label>
                            <input type="number" class="form-control" id="metadata_lifetime" name="metadata[lifetime]" 
                                   value="{{ old('metadata.lifetime') }}" min="0">
                            <small class="form-text text-muted" id="lifetime-help">
                                {{ __('td-cost-calcultaror::messages.lifetime_help') }}
                            </small>
                        </div>
                        
                        <div class="form-group">
                            <label for="metadata_capacity">{{ __('td-cost-calcultaror::messages.capacity') }}</label>
                            <input type="number" class="form-control" id="metadata_capacity" name="metadata[capacity]" 
                                   value="{{ old('metadata.capacity') }}" min="0">
                            <small class="form-text text-muted">
                                {{ __('td-cost-calcultaror::messages.capacity_help') }}
                            </small>
                        </div>
                        
                        <div class="form-group">
                            <label for="metadata_notes">{{ __('td-cost-calcultaror::messages.notes') }}</label>
                            <textarea class="form-control" id="metadata_notes" name="metadata[notes]" rows="3">{{ old('metadata.notes') }}</textarea>
                        </div>
                    </div>
                </div>
                
                <div class="form-group text-right">
                    <a href="{{ route('td-cost-calcultaror.cost-items.index') }}" class="btn btn-secondary">
                        {{ __('td-cost-calcultaror::messages.cancel') }}
                    </a>
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save"></i> {{ __('td-cost-calcultaror::messages.save') }}
                    </button>
                </div>
            </form>
        </div>
    </div>
@endsection

@push('scripts')
<script>
$(document).ready(function() {
    // Update lifetime label and help text based on selected period
    function updateLifetimeLabels() {
        const period = $('#period').val();
        const $label = $('label[for="metadata_lifetime"]');
        const $help = $('#lifetime-help');
        
        let periodText, helpText;
        
        switch(period) {
            case 'hour':
                periodText = '{{ __("td-cost-calcultaror::messages.lifetime_months") }}'.replace('måneder', 'timer').replace('months', 'hours');
                helpText = 'Hvor mange timer dette kostnadselementet forventes å vare';
                break;
            case 'year':
                periodText = '{{ __("td-cost-calcultaror::messages.lifetime_months") }}'.replace('måneder', 'år').replace('months', 'years');
                helpText = 'Hvor mange år dette kostnadselementet forventes å vare';
                break;
            case 'minute':
                periodText = '{{ __("td-cost-calcultaror::messages.lifetime_months") }}'.replace('måneder', 'minutter').replace('months', 'minutes');
                helpText = 'Hvor mange minutter dette kostnadselementet forventes å vare';
                break;
            default: // month
                periodText = '{{ __("td-cost-calcultaror::messages.lifetime_months") }}';
                helpText = '{{ __("td-cost-calcultaror::messages.lifetime_help") }}';
                break;
        }
        
        $label.text(periodText);
        $help.text(helpText);
    }
    
    // Update on page load
    updateLifetimeLabels();
    
    // Update when period changes
    $('#period').on('change', updateLifetimeLabels);
});
</script>
@endpush
