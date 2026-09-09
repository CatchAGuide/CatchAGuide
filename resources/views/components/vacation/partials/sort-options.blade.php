<option value="" @selected(($currentSort ?? '') === '' || ($currentSort ?? '') === 'newest')>{{ __('message.newest') }}</option>
<option value="price-asc" @selected(($currentSort ?? '') === 'price-asc')>@lang('message.lowprice')</option>
<option value="price-desc" @selected(($currentSort ?? '') === 'price-desc')>{{ __('trips.catalog_sort_price_desc') }}</option>
