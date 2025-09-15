@php
    $accommodation = $accommodation ?? new \App\Models\Accommodation();
    $isEdit = $accommodation->exists;
    $formAction = $isEdit ? route('admin.accommodations.update', $accommodation) : route('admin.accommodations.store');
    $formMethod = $isEdit ? 'PUT' : 'POST';
@endphp

<form action="{{ $formAction }}" method="POST" enctype="multipart/form-data" class="accommodation-form">
    @csrf
    @method($formMethod)
    
    <div class="form-sections">
        <!-- Basic Information Section -->
        <div class="form-section" data-section="basic_info">
            <h3 class="section-title">{{ __('accommodations.sections.basic_info') }}</h3>
            
            <div class="form-row">
                <div class="form-group">
                    <label for="title" class="form-label required">{{ __('accommodations.fields.title') }}</label>
                    <input type="text" id="title" name="title" class="form-control" 
                           value="{{ old('title', $accommodation->title) }}" 
                           placeholder="{{ __('accommodations.placeholders.title') }}" required>
                    @error('title')
                        <span class="error-message">{{ $message }}</span>
                    @enderror
                </div>
                
                <div class="form-group">
                    <label for="accommodation_type" class="form-label required">{{ __('accommodations.fields.accommodation_type') }}</label>
                    <select id="accommodation_type" name="accommodation_type" class="form-control" required>
                        <option value="">{{ __('accommodations.placeholders.accommodation_type') }}</option>
                        @foreach(__('accommodations.options.accommodation_types') as $key => $value)
                            <option value="{{ $key }}" {{ old('accommodation_type', $accommodation->accommodation_type) == $key ? 'selected' : '' }}>
                                {{ $value }}
                            </option>
                        @endforeach
                    </select>
                    @error('accommodation_type')
                        <span class="error-message">{{ $message }}</span>
                    @enderror
                </div>
            </div>
            
            <div class="form-group">
                <label for="description" class="form-label">{{ __('accommodations.fields.description') }}</label>
                <textarea id="description" name="description" class="form-control" rows="4" 
                          placeholder="{{ __('accommodations.placeholders.description') }}">{{ old('description', $accommodation->description) }}</textarea>
                @error('description')
                    <span class="error-message">{{ $message }}</span>
                @enderror
            </div>
            
            <div class="form-row">
                <div class="form-group">
                    <label for="condition_or_style" class="form-label">{{ __('accommodations.fields.condition_or_style') }}</label>
                    <input type="text" id="condition_or_style" name="condition_or_style" class="form-control" 
                           value="{{ old('condition_or_style', $accommodation->condition_or_style) }}" 
                           placeholder="{{ __('accommodations.placeholders.condition_or_style') }}">
                    @error('condition_or_style')
                        <span class="error-message">{{ $message }}</span>
                    @enderror
                </div>
                
                <div class="form-group">
                    <label for="status" class="form-label">{{ __('accommodations.fields.status') }}</label>
                    <select id="status" name="status" class="form-control">
                        @foreach(__('accommodations.options.statuses') as $key => $value)
                            <option value="{{ $key }}" {{ old('status', $accommodation->status) == $key ? 'selected' : '' }}>
                                {{ $value }}
                            </option>
                        @endforeach
                    </select>
                    @error('status')
                        <span class="error-message">{{ $message }}</span>
                    @enderror
                </div>
            </div>
        </div>

        <!-- Location Information Section -->
        <div class="form-section" data-section="location_info">
            <h3 class="section-title">{{ __('accommodations.sections.location_info') }}</h3>
            
            <div class="form-row">
                <div class="form-group">
                    <label for="location" class="form-label required">{{ __('accommodations.fields.location') }}</label>
                    <input type="text" id="location" name="location" class="form-control" 
                           value="{{ old('location', $accommodation->location) }}" 
                           placeholder="{{ __('accommodations.placeholders.location') }}" required>
                    @error('location')
                        <span class="error-message">{{ $message }}</span>
                    @enderror
                </div>
                
                <div class="form-group">
                    <label for="city" class="form-label required">{{ __('accommodations.fields.city') }}</label>
                    <input type="text" id="city" name="city" class="form-control" 
                           value="{{ old('city', $accommodation->city) }}" 
                           placeholder="{{ __('accommodations.placeholders.city') }}" required>
                    @error('city')
                        <span class="error-message">{{ $message }}</span>
                    @enderror
                </div>
            </div>
            
            <div class="form-row">
                <div class="form-group">
                    <label for="country" class="form-label required">{{ __('accommodations.fields.country') }}</label>
                    <input type="text" id="country" name="country" class="form-control" 
                           value="{{ old('country', $accommodation->country) }}" 
                           placeholder="{{ __('accommodations.placeholders.country') }}" required>
                    @error('country')
                        <span class="error-message">{{ $message }}</span>
                    @enderror
                </div>
                
                <div class="form-group">
                    <label for="region" class="form-label required">{{ __('accommodations.fields.region') }}</label>
                    <input type="text" id="region" name="region" class="form-control" 
                           value="{{ old('region', $accommodation->region) }}" 
                           placeholder="{{ __('accommodations.placeholders.region') }}" required>
                    @error('region')
                        <span class="error-message">{{ $message }}</span>
                    @enderror
                </div>
            </div>
            
            <div class="form-row">
                <div class="form-group">
                    <label for="lat" class="form-label">{{ __('accommodations.fields.lat') }}</label>
                    <input type="number" id="lat" name="lat" class="form-control" step="any" 
                           value="{{ old('lat', $accommodation->lat) }}" 
                           placeholder="e.g. 55.6761">
                    @error('lat')
                        <span class="error-message">{{ $message }}</span>
                    @enderror
                </div>
                
                <div class="form-group">
                    <label for="lng" class="form-label">{{ __('accommodations.fields.lng') }}</label>
                    <input type="number" id="lng" name="lng" class="form-control" step="any" 
                           value="{{ old('lng', $accommodation->lng) }}" 
                           placeholder="e.g. 12.5683">
                    @error('lng')
                        <span class="error-message">{{ $message }}</span>
                    @enderror
                </div>
            </div>
            
            <div class="form-group">
                <label for="location_description" class="form-label">{{ __('accommodations.fields.location_description') }}</label>
                <textarea id="location_description" name="location_description" class="form-control" rows="3" 
                          placeholder="{{ __('accommodations.placeholders.location_description') }}">{{ old('location_description', $accommodation->location_description) }}</textarea>
                @error('location_description')
                    <span class="error-message">{{ $message }}</span>
                @enderror
            </div>
        </div>

        <!-- Accommodation Details Section -->
        <div class="form-section" data-section="accommodation_details">
            <h3 class="section-title">{{ __('accommodations.sections.accommodation_details') }}</h3>
            
            <div class="form-row">
                <div class="form-group">
                    <label for="living_area_sqm" class="form-label">{{ __('accommodations.fields.living_area_sqm') }}</label>
                    <input type="number" id="living_area_sqm" name="living_area_sqm" class="form-control" min="0" 
                           value="{{ old('living_area_sqm', $accommodation->living_area_sqm) }}" 
                           placeholder="{{ __('accommodations.placeholders.living_area_sqm') }}">
                    @error('living_area_sqm')
                        <span class="error-message">{{ $message }}</span>
                    @enderror
                </div>
                
                <div class="form-group">
                    <label for="max_occupancy" class="form-label">{{ __('accommodations.fields.max_occupancy') }}</label>
                    <input type="number" id="max_occupancy" name="max_occupancy" class="form-control" min="1" 
                           value="{{ old('max_occupancy', $accommodation->max_occupancy) }}" 
                           placeholder="{{ __('accommodations.placeholders.max_occupancy') }}">
                    @error('max_occupancy')
                        <span class="error-message">{{ $message }}</span>
                    @enderror
                </div>
            </div>
            
            <div class="form-row">
                <div class="form-group">
                    <label for="number_of_bedrooms" class="form-label">{{ __('accommodations.fields.number_of_bedrooms') }}</label>
                    <input type="number" id="number_of_bedrooms" name="number_of_bedrooms" class="form-control" min="0" 
                           value="{{ old('number_of_bedrooms', $accommodation->number_of_bedrooms) }}" 
                           placeholder="{{ __('accommodations.placeholders.number_of_bedrooms') }}">
                    @error('number_of_bedrooms')
                        <span class="error-message">{{ $message }}</span>
                    @enderror
                </div>
                
                <div class="form-group">
                    <label for="number_of_beds" class="form-label">{{ __('accommodations.fields.number_of_beds') }}</label>
                    <input type="number" id="number_of_beds" name="number_of_beds" class="form-control" min="0" 
                           value="{{ old('number_of_beds', $accommodation->number_of_beds) }}" 
                           placeholder="{{ __('accommodations.placeholders.number_of_beds') }}">
                    @error('number_of_beds')
                        <span class="error-message">{{ $message }}</span>
                    @enderror
                </div>
            </div>
            
            <div class="form-group">
                <label for="floor_layout" class="form-label">{{ __('accommodations.fields.floor_layout') }}</label>
                <input type="text" id="floor_layout" name="floor_layout" class="form-control" 
                       value="{{ old('floor_layout', $accommodation->floor_layout) }}" 
                       placeholder="{{ __('accommodations.placeholders.floor_layout') }}">
                @error('floor_layout')
                    <span class="error-message">{{ $message }}</span>
                @enderror
            </div>
            
            <div class="form-group">
                <label class="form-label">{{ __('accommodations.fields.bed_types') }}</label>
                <div class="checkbox-group">
                    @foreach(__('accommodations.options.bed_types') as $key => $value)
                        <label class="checkbox-label">
                            <input type="checkbox" name="bed_types[]" value="{{ $key }}" 
                                   {{ in_array($key, old('bed_types', $accommodation->bed_types ?? [])) ? 'checked' : '' }}>
                            <span class="checkbox-text">{{ $value }}</span>
                        </label>
                    @endforeach
                </div>
                @error('bed_types')
                    <span class="error-message">{{ $message }}</span>
                @enderror
            </div>
        </div>

        <!-- Amenities Section -->
        <div class="form-section" data-section="amenities">
            <h3 class="section-title">{{ __('accommodations.sections.amenities') }}</h3>
            
            <div class="checkbox-grid">
                <label class="checkbox-label">
                    <input type="checkbox" name="living_room" value="1" {{ old('living_room', $accommodation->living_room) ? 'checked' : '' }}>
                    <span class="checkbox-text">{{ __('accommodations.fields.living_room') }}</span>
                </label>
                
                <label class="checkbox-label">
                    <input type="checkbox" name="dining_room_or_area" value="1" {{ old('dining_room_or_area', $accommodation->dining_room_or_area) ? 'checked' : '' }}>
                    <span class="checkbox-text">{{ __('accommodations.fields.dining_room_or_area') }}</span>
                </label>
                
                <label class="checkbox-label">
                    <input type="checkbox" name="terrace" value="1" {{ old('terrace', $accommodation->terrace) ? 'checked' : '' }}>
                    <span class="checkbox-text">{{ __('accommodations.fields.terrace') }}</span>
                </label>
                
                <label class="checkbox-label">
                    <input type="checkbox" name="garden" value="1" {{ old('garden', $accommodation->garden) ? 'checked' : '' }}>
                    <span class="checkbox-text">{{ __('accommodations.fields.garden') }}</span>
                </label>
                
                <label class="checkbox-label">
                    <input type="checkbox" name="swimming_pool" value="1" {{ old('swimming_pool', $accommodation->swimming_pool) ? 'checked' : '' }}>
                    <span class="checkbox-text">{{ __('accommodations.fields.swimming_pool') }}</span>
                </label>
            </div>
        </div>

        <!-- Kitchen Equipment Section -->
        <div class="form-section" data-section="kitchen_equipment">
            <h3 class="section-title">{{ __('accommodations.sections.kitchen_equipment') }}</h3>
            
            <div class="form-group">
                <label for="kitchen_type" class="form-label">{{ __('accommodations.fields.kitchen_type') }}</label>
                <select id="kitchen_type" name="kitchen_type" class="form-control">
                    <option value="">{{ __('accommodations.placeholders.kitchen_type') }}</option>
                    @foreach(__('accommodations.options.kitchen_types') as $key => $value)
                        <option value="{{ $key }}" {{ old('kitchen_type', $accommodation->kitchen_type) == $key ? 'selected' : '' }}>
                            {{ $value }}
                        </option>
                    @endforeach
                </select>
                @error('kitchen_type')
                    <span class="error-message">{{ $message }}</span>
                @enderror
            </div>
            
            <div class="checkbox-grid">
                <label class="checkbox-label">
                    <input type="checkbox" name="refrigerator_freezer" value="1" {{ old('refrigerator_freezer', $accommodation->refrigerator_freezer) ? 'checked' : '' }}>
                    <span class="checkbox-text">{{ __('accommodations.fields.refrigerator_freezer') }}</span>
                </label>
                
                <label class="checkbox-label">
                    <input type="checkbox" name="oven" value="1" {{ old('oven', $accommodation->oven) ? 'checked' : '' }}>
                    <span class="checkbox-text">{{ __('accommodations.fields.oven') }}</span>
                </label>
                
                <label class="checkbox-label">
                    <input type="checkbox" name="stove_or_ceramic_hob" value="1" {{ old('stove_or_ceramic_hob', $accommodation->stove_or_ceramic_hob) ? 'checked' : '' }}>
                    <span class="checkbox-text">{{ __('accommodations.fields.stove_or_ceramic_hob') }}</span>
                </label>
                
                <label class="checkbox-label">
                    <input type="checkbox" name="microwave" value="1" {{ old('microwave', $accommodation->microwave) ? 'checked' : '' }}>
                    <span class="checkbox-text">{{ __('accommodations.fields.microwave') }}</span>
                </label>
                
                <label class="checkbox-label">
                    <input type="checkbox" name="dishwasher" value="1" {{ old('dishwasher', $accommodation->dishwasher) ? 'checked' : '' }}>
                    <span class="checkbox-text">{{ __('accommodations.fields.dishwasher') }}</span>
                </label>
                
                <label class="checkbox-label">
                    <input type="checkbox" name="coffee_machine" value="1" {{ old('coffee_machine', $accommodation->coffee_machine) ? 'checked' : '' }}>
                    <span class="checkbox-text">{{ __('accommodations.fields.coffee_machine') }}</span>
                </label>
                
                <label class="checkbox-label">
                    <input type="checkbox" name="cookware_and_dishes" value="1" {{ old('cookware_and_dishes', $accommodation->cookware_and_dishes) ? 'checked' : '' }}>
                    <span class="checkbox-text">{{ __('accommodations.fields.cookware_and_dishes') }}</span>
                </label>
            </div>
        </div>

        <!-- Laundry Facilities Section -->
        <div class="form-section" data-section="laundry_facilities">
            <h3 class="section-title">{{ __('accommodations.sections.laundry_facilities') }}</h3>
            
            <div class="form-row">
                <div class="form-group">
                    <label for="bathroom" class="form-label">{{ __('accommodations.fields.bathroom') }}</label>
                    <input type="number" id="bathroom" name="bathroom" class="form-control" min="0" 
                           value="{{ old('bathroom', $accommodation->bathroom) }}" 
                           placeholder="{{ __('accommodations.placeholders.bathroom') }}">
                    @error('bathroom')
                        <span class="error-message">{{ $message }}</span>
                    @enderror
                </div>
            </div>
            
            <div class="checkbox-grid">
                <label class="checkbox-label">
                    <input type="checkbox" name="washing_machine" value="1" {{ old('washing_machine', $accommodation->washing_machine) ? 'checked' : '' }}>
                    <span class="checkbox-text">{{ __('accommodations.fields.washing_machine') }}</span>
                </label>
                
                <label class="checkbox-label">
                    <input type="checkbox" name="dryer" value="1" {{ old('dryer', $accommodation->dryer) ? 'checked' : '' }}>
                    <span class="checkbox-text">{{ __('accommodations.fields.dryer') }}</span>
                </label>
                
                <label class="checkbox-label">
                    <input type="checkbox" name="separate_laundry_room" value="1" {{ old('separate_laundry_room', $accommodation->separate_laundry_room) ? 'checked' : '' }}>
                    <span class="checkbox-text">{{ __('accommodations.fields.separate_laundry_room') }}</span>
                </label>
                
                <label class="checkbox-label">
                    <input type="checkbox" name="freezer_room" value="1" {{ old('freezer_room', $accommodation->freezer_room) ? 'checked' : '' }}>
                    <span class="checkbox-text">{{ __('accommodations.fields.freezer_room') }}</span>
                </label>
                
                <label class="checkbox-label">
                    <input type="checkbox" name="filleting_house" value="1" {{ old('filleting_house', $accommodation->filleting_house) ? 'checked' : '' }}>
                    <span class="checkbox-text">{{ __('accommodations.fields.filleting_house') }}</span>
                </label>
            </div>
        </div>

        <!-- Policies Section -->
        <div class="form-section" data-section="policies">
            <h3 class="section-title">{{ __('accommodations.sections.policies') }}</h3>
            
            <div class="checkbox-grid">
                <label class="checkbox-label">
                    <input type="checkbox" name="wifi_or_internet" value="1" {{ old('wifi_or_internet', $accommodation->wifi_or_internet) ? 'checked' : '' }}>
                    <span class="checkbox-text">{{ __('accommodations.fields.wifi_or_internet') }}</span>
                </label>
                
                <label class="checkbox-label">
                    <input type="checkbox" name="bed_linen_included" value="1" {{ old('bed_linen_included', $accommodation->bed_linen_included) ? 'checked' : '' }}>
                    <span class="checkbox-text">{{ __('accommodations.fields.bed_linen_included') }}</span>
                </label>
                
                <label class="checkbox-label">
                    <input type="checkbox" name="utilities_included" value="1" {{ old('utilities_included', $accommodation->utilities_included) ? 'checked' : '' }}>
                    <span class="checkbox-text">{{ __('accommodations.fields.utilities_included') }}</span>
                </label>
                
                <label class="checkbox-label">
                    <input type="checkbox" name="pets_allowed" value="1" {{ old('pets_allowed', $accommodation->pets_allowed) ? 'checked' : '' }}>
                    <span class="checkbox-text">{{ __('accommodations.fields.pets_allowed') }}</span>
                </label>
                
                <label class="checkbox-label">
                    <input type="checkbox" name="smoking_allowed" value="1" {{ old('smoking_allowed', $accommodation->smoking_allowed) ? 'checked' : '' }}>
                    <span class="checkbox-text">{{ __('accommodations.fields.smoking_allowed') }}</span>
                </label>
                
                <label class="checkbox-label">
                    <input type="checkbox" name="reception_available" value="1" {{ old('reception_available', $accommodation->reception_available) ? 'checked' : '' }}>
                    <span class="checkbox-text">{{ __('accommodations.fields.reception_available') }}</span>
                </label>
            </div>
        </div>

        <!-- Location Distances Section -->
        <div class="form-section" data-section="location_distances">
            <h3 class="section-title">{{ __('accommodations.sections.location_distances') }}</h3>
            
            <div class="form-row">
                <div class="form-group">
                    <label for="distance_to_water_m" class="form-label">{{ __('accommodations.fields.distance_to_water_m') }}</label>
                    <input type="number" id="distance_to_water_m" name="distance_to_water_m" class="form-control" min="0" 
                           value="{{ old('distance_to_water_m', $accommodation->distance_to_water_m) }}" 
                           placeholder="{{ __('accommodations.placeholders.distance_to_water_m') }}">
                    @error('distance_to_water_m')
                        <span class="error-message">{{ $message }}</span>
                    @enderror
                </div>
                
                <div class="form-group">
                    <label for="distance_to_boat_berth_m" class="form-label">{{ __('accommodations.fields.distance_to_boat_berth_m') }}</label>
                    <input type="number" id="distance_to_boat_berth_m" name="distance_to_boat_berth_m" class="form-control" min="0" 
                           value="{{ old('distance_to_boat_berth_m', $accommodation->distance_to_boat_berth_m) }}" 
                           placeholder="{{ __('accommodations.placeholders.distance_to_boat_berth_m') }}">
                    @error('distance_to_boat_berth_m')
                        <span class="error-message">{{ $message }}</span>
                    @enderror
                </div>
            </div>
            
            <div class="form-row">
                <div class="form-group">
                    <label for="distance_to_shop_km" class="form-label">{{ __('accommodations.fields.distance_to_shop_km') }}</label>
                    <input type="number" id="distance_to_shop_km" name="distance_to_shop_km" class="form-control" min="0" step="0.01" 
                           value="{{ old('distance_to_shop_km', $accommodation->distance_to_shop_km) }}" 
                           placeholder="{{ __('accommodations.placeholders.distance_to_shop_km') }}">
                    @error('distance_to_shop_km')
                        <span class="error-message">{{ $message }}</span>
                    @enderror
                </div>
                
                <div class="form-group">
                    <label for="distance_to_parking_m" class="form-label">{{ __('accommodations.fields.distance_to_parking_m') }}</label>
                    <input type="number" id="distance_to_parking_m" name="distance_to_parking_m" class="form-control" min="0" 
                           value="{{ old('distance_to_parking_m', $accommodation->distance_to_parking_m) }}" 
                           placeholder="{{ __('accommodations.placeholders.distance_to_parking_m') }}">
                    @error('distance_to_parking_m')
                        <span class="error-message">{{ $message }}</span>
                    @enderror
                </div>
            </div>
            
            <div class="form-row">
                <div class="form-group">
                    <label for="distance_to_nearest_town_km" class="form-label">{{ __('accommodations.fields.distance_to_nearest_town_km') }}</label>
                    <input type="number" id="distance_to_nearest_town_km" name="distance_to_nearest_town_km" class="form-control" min="0" step="0.01" 
                           value="{{ old('distance_to_nearest_town_km', $accommodation->distance_to_nearest_town_km) }}" 
                           placeholder="{{ __('accommodations.placeholders.distance_to_nearest_town_km') }}">
                    @error('distance_to_nearest_town_km')
                        <span class="error-message">{{ $message }}</span>
                    @enderror
                </div>
                
                <div class="form-group">
                    <label for="distance_to_airport_km" class="form-label">{{ __('accommodations.fields.distance_to_airport_km') }}</label>
                    <input type="number" id="distance_to_airport_km" name="distance_to_airport_km" class="form-control" min="0" step="0.01" 
                           value="{{ old('distance_to_airport_km', $accommodation->distance_to_airport_km) }}" 
                           placeholder="{{ __('accommodations.placeholders.distance_to_airport_km') }}">
                    @error('distance_to_airport_km')
                        <span class="error-message">{{ $message }}</span>
                    @enderror
                </div>
            </div>
            
            <div class="form-group">
                <label for="distance_to_ferry_port_km" class="form-label">{{ __('accommodations.fields.distance_to_ferry_port_km') }}</label>
                <input type="number" id="distance_to_ferry_port_km" name="distance_to_ferry_port_km" class="form-control" min="0" step="0.01" 
                       value="{{ old('distance_to_ferry_port_km', $accommodation->distance_to_ferry_port_km) }}" 
                       placeholder="{{ __('accommodations.placeholders.distance_to_ferry_port_km') }}">
                @error('distance_to_ferry_port_km')
                    <span class="error-message">{{ $message }}</span>
                @enderror
            </div>
        </div>

        <!-- Pricing Section -->
        <div class="form-section" data-section="pricing">
            <h3 class="section-title">{{ __('accommodations.sections.pricing') }}</h3>
            
            <div class="form-row">
                <div class="form-group">
                    <label for="price_per_night" class="form-label">{{ __('accommodations.fields.price_per_night') }}</label>
                    <input type="number" id="price_per_night" name="price_per_night" class="form-control" min="0" step="0.01" 
                           value="{{ old('price_per_night', $accommodation->price_per_night) }}" 
                           placeholder="{{ __('accommodations.placeholders.price_per_night') }}">
                    @error('price_per_night')
                        <span class="error-message">{{ $message }}</span>
                    @enderror
                </div>
                
                <div class="form-group">
                    <label for="price_per_week" class="form-label">{{ __('accommodations.fields.price_per_week') }}</label>
                    <input type="number" id="price_per_week" name="price_per_week" class="form-control" min="0" step="0.01" 
                           value="{{ old('price_per_week', $accommodation->price_per_week) }}" 
                           placeholder="{{ __('accommodations.placeholders.price_per_week') }}">
                    @error('price_per_week')
                        <span class="error-message">{{ $message }}</span>
                    @enderror
                </div>
            </div>
            
            <div class="form-group">
                <label for="currency" class="form-label">{{ __('accommodations.fields.currency') }}</label>
                <select id="currency" name="currency" class="form-control">
                    @foreach(__('accommodations.options.currencies') as $key => $value)
                        <option value="{{ $key }}" {{ old('currency', $accommodation->currency) == $key ? 'selected' : '' }}>
                            {{ $value }}
                        </option>
                    @endforeach
                </select>
                @error('currency')
                    <span class="error-message">{{ $message }}</span>
                @enderror
            </div>
        </div>

        <!-- Rental Terms Section -->
        <div class="form-section" data-section="rental_terms">
            <h3 class="section-title">{{ __('accommodations.sections.rental_terms') }}</h3>
            
            <div class="form-row">
                <div class="form-group">
                    <label for="changeover_day" class="form-label">{{ __('accommodations.fields.changeover_day') }}</label>
                    <select id="changeover_day" name="changeover_day" class="form-control">
                        <option value="">{{ __('accommodations.placeholders.changeover_day') }}</option>
                        @foreach(__('accommodations.options.changeover_days') as $key => $value)
                            <option value="{{ $key }}" {{ old('changeover_day', $accommodation->changeover_day) == $key ? 'selected' : '' }}>
                                {{ $value }}
                            </option>
                        @endforeach
                    </select>
                    @error('changeover_day')
                        <span class="error-message">{{ $message }}</span>
                    @enderror
                </div>
                
                <div class="form-group">
                    <label for="minimum_stay_nights" class="form-label">{{ __('accommodations.fields.minimum_stay_nights') }}</label>
                    <input type="number" id="minimum_stay_nights" name="minimum_stay_nights" class="form-control" min="1" 
                           value="{{ old('minimum_stay_nights', $accommodation->minimum_stay_nights) }}" 
                           placeholder="{{ __('accommodations.placeholders.minimum_stay_nights') }}">
                    @error('minimum_stay_nights')
                        <span class="error-message">{{ $message }}</span>
                    @enderror
                </div>
            </div>
            
            <div class="form-group">
                <label class="form-label">{{ __('accommodations.fields.rental_includes') }}</label>
                <div class="checkbox-group">
                    <label class="checkbox-label">
                        <input type="checkbox" name="rental_includes[]" value="electricity">
                        <span class="checkbox-text">Electricity</span>
                    </label>
                    <label class="checkbox-label">
                        <input type="checkbox" name="rental_includes[]" value="water">
                        <span class="checkbox-text">Water</span>
                    </label>
                    <label class="checkbox-label">
                        <input type="checkbox" name="rental_includes[]" value="heating">
                        <span class="checkbox-text">Heating</span>
                    </label>
                    <label class="checkbox-label">
                        <input type="checkbox" name="rental_includes[]" value="wifi">
                        <span class="checkbox-text">WiFi</span>
                    </label>
                    <label class="checkbox-label">
                        <input type="checkbox" name="rental_includes[]" value="parking">
                        <span class="checkbox-text">Parking</span>
                    </label>
                    <label class="checkbox-label">
                        <input type="checkbox" name="rental_includes[]" value="linen">
                        <span class="checkbox-text">Bed Linen</span>
                    </label>
                </div>
                @error('rental_includes')
                    <span class="error-message">{{ $message }}</span>
                @enderror
            </div>
        </div>

        <!-- File Uploads Section -->
        <div class="form-section" data-section="file_uploads">
            <h3 class="section-title">File Uploads</h3>
            
            <div class="form-group">
                <label for="thumbnail_path" class="form-label">{{ __('accommodations.fields.thumbnail_path') }}</label>
                <input type="file" id="thumbnail_path" name="thumbnail_path" class="form-control" accept="image/*">
                @if($accommodation->thumbnail_path)
                    <div class="current-file">
                        <small>Current: {{ basename($accommodation->thumbnail_path) }}</small>
                    </div>
                @endif
                @error('thumbnail_path')
                    <span class="error-message">{{ $message }}</span>
                @enderror
            </div>
            
            <div class="form-group">
                <label for="gallery_images" class="form-label">{{ __('accommodations.fields.gallery_images') }}</label>
                <input type="file" id="gallery_images" name="gallery_images[]" class="form-control" accept="image/*" multiple>
                @if($accommodation->gallery_images)
                    <div class="current-files">
                        <small>Current: {{ count($accommodation->gallery_images) }} images</small>
                    </div>
                @endif
                @error('gallery_images')
                    <span class="error-message">{{ $message }}</span>
                @enderror
            </div>
        </div>
    </div>

    <div class="form-actions">
        <button type="submit" class="btn btn-primary">
            {{ $isEdit ? __('accommodations.edit') : __('accommodations.create') }}
        </button>
        <a href="{{ route('admin.accommodations.index') }}" class="btn btn-secondary">
            Cancel
        </a>
    </div>
</form>
