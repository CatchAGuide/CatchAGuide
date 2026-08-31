@php
    $currentSort = $currentSort ?? ($filter->sortBy ?? '');
    $effectiveSort = $currentSort === '' || $currentSort === null
        ? 'recommended'
        : $currentSort;
@endphp
<option value="recommended" @selected($effectiveSort === 'recommended')>{{ __('offers.sort_recommended') }}</option>
<option value="newest" @selected($effectiveSort === 'newest')>{{ __('offers.sort_newest') }}</option>
<option value="nearest" @selected($effectiveSort === 'nearest')>{{ __('offers.sort_nearest') }}</option>
<option value="price-asc" @selected($effectiveSort === 'price-asc')>{{ __('offers.sort_price_asc') }}</option>
<option value="price-desc" @selected($effectiveSort === 'price-desc')>{{ __('offers.sort_price_desc') }}</option>
