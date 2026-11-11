@props([
    'selectedId' => null,
    'inputName' => 'user_id',
    'inputId' => 'user_id',
    'syncProviderName' => false,
    'providerNameInputId' => 'provider_name',
])

@php
    use App\Models\User;
    use App\Services\Admin\ListingOwnerSearch;

    $selectedId = is_numeric($selectedId) && (int) $selectedId > 0 ? (int) $selectedId : null;
    $selected = $selectedId
        ? app(ListingOwnerSearch::class)->presentOne(User::query()->with('information')->find($selectedId))
        : null;
    $searchUrl = route('admin.customers.search');
    $createUrl = route('admin.customers.create');
@endphp

<div
    class="admin-listing-owner-picker mb-4"
    data-owner-picker
    data-search-url="{{ $searchUrl }}"
    data-sync-provider-name="{{ $syncProviderName ? '1' : '0' }}"
    data-provider-name-input="{{ $providerNameInputId }}"
>
    <label class="form-label fw-bold" for="{{ $inputId }}_search">
        {{ __('admin.listing_owner.label') }}
    </label>
    <p class="text-muted small mb-2">{{ __('admin.listing_owner.help') }}</p>

    <input type="hidden" name="{{ $inputName }}" id="{{ $inputId }}" value="{{ $selected['id'] ?? '' }}" data-owner-id>

    <div class="input-group">
        <input
            type="search"
            class="form-control"
            id="{{ $inputId }}_search"
            data-owner-search
            value="{{ $selected['label'] ?? '' }}"
            placeholder="{{ __('admin.listing_owner.search_placeholder') }}"
            autocomplete="off"
        >
        <button type="button" class="btn btn-outline-secondary" data-owner-clear title="{{ __('admin.listing_owner.clear') }}">
            <i class="fa fa-times"></i>
        </button>
        <a href="{{ $createUrl }}" class="btn btn-outline-primary" target="_blank" rel="noopener" title="{{ __('admin.listing_owner.create') }}">
            <i class="fa fa-plus"></i>
        </a>
    </div>

    <div class="admin-listing-owner-picker__meta mt-1" data-owner-meta>
        @if($selected)
            <span class="badge bg-{{ $selected['role'] === 'guide' ? 'success' : ($selected['role'] === 'provider' ? 'primary' : 'secondary') }}-light">
                {{ __('admin.customers.role_'.$selected['role']) }}
            </span>
            @if($selected['city'] !== '')
                <span class="text-muted small ms-1">{{ $selected['city'] }}</span>
            @endif
        @else
            <span class="text-muted small">{{ __('admin.listing_owner.none') }}</span>
        @endif
    </div>

    <ul class="admin-listing-owner-picker__results list-group mt-1" data-owner-results hidden></ul>
</div>

@once
    @push('js_push')
        @include('components.admin.listing-owner-picker-script')
    @endpush
@endonce
