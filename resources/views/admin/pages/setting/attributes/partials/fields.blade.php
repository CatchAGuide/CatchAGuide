@php
    $hasActive = in_array('is_active', $fields, true);
    $hasSort = in_array('sort_order', $fields, true);
    $hasInputType = in_array('input_type', $fields, true);
    $hasPlaceholder = in_array('placeholder', $fields, true);
    $hasPlaceholderEn = in_array('placeholder_en', $fields, true);
    $isEdit = $row !== null;
@endphp

<div class="mb-3">
    <label class="form-label">{{ __('admin.listing_attributes.columns.name_en') }}</label>
    <input type="text"
           name="name_en"
           class="form-control"
           value="{{ $isEdit ? $row->getRawOriginal('name_en') : old('name_en') }}"
           required>
</div>

<div class="mb-3">
    <label class="form-label">{{ __('admin.listing_attributes.columns.name_de') }}</label>
    <input type="text"
           name="{{ $deField }}"
           class="form-control"
           value="{{ $isEdit ? $row->getRawOriginal($deField) : old($deField) }}">
</div>

@if($hasInputType)
    <div class="mb-3">
        <label class="form-label">{{ __('admin.listing_attributes.columns.input_type') }}</label>
        <select name="input_type" class="form-select">
            @foreach(['text', 'number', 'textarea', 'select', 'checkbox', 'radio'] as $inputType)
                <option value="{{ $inputType }}"
                    {{ ($isEdit ? $row->getRawOriginal('input_type') : old('input_type', 'text')) === $inputType ? 'selected' : '' }}>
                    {{ $inputType }}
                </option>
            @endforeach
        </select>
    </div>
@endif

@if($hasPlaceholder)
    <div class="mb-3">
        <label class="form-label">{{ __('admin.listing_attributes.columns.placeholder') }}</label>
        <input type="text"
               name="placeholder"
               class="form-control"
               value="{{ $isEdit ? $row->getRawOriginal('placeholder') : old('placeholder') }}">
    </div>
@endif

@if($hasPlaceholderEn)
    <div class="mb-3">
        <label class="form-label">{{ __('admin.listing_attributes.columns.placeholder_en') }}</label>
        <input type="text"
               name="placeholder_en"
               class="form-control"
               value="{{ $isEdit ? $row->getRawOriginal('placeholder_en') : old('placeholder_en') }}">
    </div>
@endif

@if($hasActive || $hasSort)
    <div class="row">
        @if($hasActive)
            <div class="col-md-6 mb-3">
                <label class="form-label">{{ __('admin.listing_attributes.columns.active') }}</label>
                <select name="is_active" class="form-select">
                    @php
                        $activeValue = $isEdit ? (int) $row->is_active : (int) old('is_active', 1);
                    @endphp
                    <option value="1" {{ $activeValue === 1 ? 'selected' : '' }}>{{ __('admin.listing_attributes.yes') }}</option>
                    <option value="0" {{ $activeValue === 0 ? 'selected' : '' }}>{{ __('admin.listing_attributes.no') }}</option>
                </select>
            </div>
        @endif
        @if($hasSort)
            <div class="col-md-6 mb-3">
                <label class="form-label">{{ __('admin.listing_attributes.columns.sort_order') }}</label>
                <input type="number"
                       name="sort_order"
                       class="form-control"
                       value="{{ $isEdit ? $row->sort_order : old('sort_order', 0) }}">
            </div>
        @endif
    </div>
@endif
