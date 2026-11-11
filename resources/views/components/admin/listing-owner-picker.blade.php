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
    $createUrl = route('admin.customers.index', ['create' => 1]);
@endphp

<div
    class="admin-listing-owner-picker"
    data-owner-picker
    data-search-url="{{ $searchUrl }}"
    data-sync-provider-name="{{ $syncProviderName ? '1' : '0' }}"
    data-provider-name-input="{{ $providerNameInputId }}"
>
    <h3 class="admin-listing-owner-picker__title">{{ __('admin.listing_owner.label') }}</h3>
    <p class="admin-listing-owner-picker__help">{{ __('admin.listing_owner.help') }}</p>

    <input type="hidden" name="{{ $inputName }}" id="{{ $inputId }}" value="{{ $selected['id'] ?? '' }}" data-owner-id>

    <div class="admin-listing-owner-picker__controls">
        <input
            type="search"
            class="form-control admin-listing-owner-picker__search"
            id="{{ $inputId }}_search"
            data-owner-search
            value="{{ $selected['label'] ?? '' }}"
            placeholder="{{ __('admin.listing_owner.search_placeholder') }}"
            autocomplete="off"
        >
        <button type="button" class="btn btn-outline-secondary admin-listing-owner-picker__btn" data-owner-clear title="{{ __('admin.listing_owner.clear') }}" aria-label="{{ __('admin.listing_owner.clear') }}">
            <i class="fa fa-times"></i>
        </button>
        <a href="{{ $createUrl }}" class="btn btn-outline-primary admin-listing-owner-picker__btn" target="_blank" rel="noopener" title="{{ __('admin.listing_owner.create') }}" aria-label="{{ __('admin.listing_owner.create') }}">
            <i class="fa fa-plus"></i>
        </a>
    </div>

    <div class="admin-listing-owner-picker__meta" data-owner-meta>
        @if($selected)
            <span class="badge bg-{{ $selected['role'] === 'guide' ? 'success' : ($selected['role'] === 'provider' ? 'primary' : 'secondary') }}-light">
                {{ __('admin.customers.role_'.$selected['role']) }}
            </span>
            @if($selected['city'] !== '')
                <span class="text-muted small">{{ $selected['city'] }}</span>
            @endif
        @else
            <span class="text-muted small">{{ __('admin.listing_owner.none') }}</span>
        @endif
    </div>

    <ul class="list-group admin-listing-owner-picker__results" data-owner-results hidden></ul>
</div>

@once
    @push('js_push')
        @include('components.admin.listing-owner-picker-script')
    @endpush
@endonce
