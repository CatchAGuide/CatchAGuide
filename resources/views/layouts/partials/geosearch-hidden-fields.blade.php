{{-- Shared geospatial search hidden fields (GET forms). Populated by Google Places in scripts.blade.php --}}
<input type="hidden" name="bounds_ne_lat" value="{{ request('bounds_ne_lat') }}" data-geosearch="bounds_ne_lat">
<input type="hidden" name="bounds_ne_lng" value="{{ request('bounds_ne_lng') }}" data-geosearch="bounds_ne_lng">
<input type="hidden" name="bounds_sw_lat" value="{{ request('bounds_sw_lat') }}" data-geosearch="bounds_sw_lat">
<input type="hidden" name="bounds_sw_lng" value="{{ request('bounds_sw_lng') }}" data-geosearch="bounds_sw_lng">
<input type="hidden" name="country_short" value="{{ request('country_short') }}" data-geosearch="country_short">
<input type="hidden" name="place_types" value="{{ is_array(request('place_types')) ? json_encode(request('place_types')) : request('place_types') }}" data-geosearch="place_types">
