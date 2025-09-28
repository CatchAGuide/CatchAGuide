@php
    $accommodation = $accommodation ?? new \App\Models\Accommodation();
    $isEdit = $accommodation->exists;
    $formAction = $isEdit ? route('admin.accommodations.update', $accommodation) : route('admin.accommodations.store');
    $formMethod = $isEdit ? 'PUT' : 'POST';
@endphp

<div id="accommodation-form" class="card">
    <div class="card-body">
        
        <div class="step-wrapper">
            <div class="step-buttons">
                <div class="step-button active" data-step="1">
                    <i class="fas fa-images"></i>
                </div>
                <div class="step-button" data-step="2">
                    <i class="fas fa-home"></i>
                </div>
                <div class="step-button" data-step="3">
                    <i class="fas fa-info-circle"></i>
                </div>
                <div class="step-button" data-step="4">
                    <i class="fas fa-map-marker-alt"></i>
                </div>
                <div class="step-button" data-step="5">
                    <i class="fas fa-list-alt"></i>
                </div>
                <div class="step-button" data-step="6">
                    <i class="fas fa-dollar-sign"></i>
                </div>
            </div>

            <div class="step-line"></div>
        </div>
        <div id="error-container" class="alert alert-danger" style="display: none;"></div>

        <form action="{{ $formAction }}" method="POST" id="accommodationForm" enctype="multipart/form-data">
            @csrf
            @method($formMethod)
            <meta name="csrf-token" content="{{ csrf_token() }}">

            @if ($errors->any())
                <div class="alert alert-danger">
                    <ul>
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <input type="hidden" name="target_redirect" id="target_redirect" value="{{ $targetRedirect ?? route('admin.accommodations.index') }}">
            <input type="hidden" name="is_update" id="is_update" value="{{ $isEdit ? 1 : 0 }}">
            <input type="hidden" name="is_draft" id="is_draft" value="{{ isset($accommodation->status) && $accommodation->status == 'draft' ? 1 : 0 }}">
            <input type="hidden" name="accommodation_id" id="accommodation_id" value="{{ $accommodation->id ?? 0 }}">
            <input type="hidden" name="thumbnail_path" id="thumbnail_path" value="{{ $accommodation->thumbnail_path ?? '' }}">
            <input type="hidden" name="existing_images" id="existing_images" value="{{ $accommodation->gallery_images ?? '' }}">
            <input type="hidden" name="user_id" id="user_id" value="{{ $accommodation->user_id ?? auth()->id() }}">
            <input type="hidden" id="image_list" name="image_list">

            <!-- Step 1: Images and Basic Info -->
            <div class="step active" id="step1">
                <h5>{{ __('accommodations.sections.basic_info') }}</h5>

                <label for="title_image" class="form-label fw-bold fs-5">
                    {{ __('accommodations.fields.thumbnail_path') }}
                    <i class="fas fa-info-circle ms-2 fs-6" data-bs-toggle="tooltip" data-bs-placement="top" 
                       title="Upload a main image for your accommodation"></i>
                </label>
                <div class="card mb-4">
                    <div class="card-body">
                        <div class="file-upload-wrapper">
                            <input id="title_image" name="title_image[]" type="file" multiple />
                            <input id="cropped_image" name="cropped_image[]" type="file" multiple hidden/>
                            <label for="title_image" class="file-upload-btn">Choose Images</label>
                        </div>
                        <div id="croppedImagesContainer"></div>
                    </div>

                    <div class="image-area" id="imagePreviewContainer"></div>
                    <input type="hidden" name="primaryImage" id="primaryImageInput">
                </div>

                <hr>

                <div class="form-group">
                    <label for="location" class="form-label fw-bold fs-5">
                        {{ __('accommodations.fields.location') }}
                        <i class="fas fa-info-circle ms-2 fs-6" data-bs-toggle="tooltip" data-bs-placement="top" 
                           title="Enter the location of your accommodation"></i>
                    </label>
                    <input type="search" class="form-control" id="location" name="location" value="{{ old('location', $accommodation->location) }}" placeholder="{{ __('accommodations.placeholders.location') }}">
                    <input type="hidden" name="latitude" id="latitude" value="{{ old('lat', $accommodation->lat) }}">
                    <input type="hidden" name="longitude" id="longitude" value="{{ old('lng', $accommodation->lng) }}">
                    <input type="hidden" name="country" id="country" value="{{ old('country', $accommodation->country) }}">
                    <input type="hidden" name="city" id="city" value="{{ old('city', $accommodation->city) }}">
                    <input type="hidden" name="region" id="region" value="{{ old('region', $accommodation->region) }}">
                </div>

                <hr>

                <div class="form-group">
                    <label for="title" class="form-label fw-bold fs-5">
                        {{ __('accommodations.fields.title') }}
                        <i class="fas fa-info-circle ms-2 fs-6" data-bs-toggle="tooltip" data-bs-placement="top" title="Enter a catchy title for your accommodation"></i>
                    </label>
                    <input type="text" class="form-control" id="title" name="title" value="{{ old('title', $accommodation->title) }}" placeholder="{{ __('accommodations.placeholders.title') }}">
                </div>

                <div class="button-group">
                    <div class="left-buttons">
                        {{-- <button type="button" class="btn btn-secondary" id="saveDraftBtn1">
                            {{ __('accommodations.leave_save_draft') }}
                        </button> --}}
                    </div>
                    <div class="right-buttons">
                        <div class="row-button">
                            <div></div>
                            <button type="button" class="btn btn-primary" id="nextBtn1">
                                Next
                            </button>
                        </div>
                        <button type="submit" class="btn btn-primary" id="submitBtn1" style="display: none;">
                            {{ $isEdit ? __('accommodations.edit') : __('accommodations.create') }}
                        </button>
                    </div>
                </div>
            </div>

            <!-- Step 2: Accommodation Type and Description -->
            <div class="step" id="step2">
                <h5>{{ __('accommodations.sections.accommodation_details') }}</h5>

                <div class="form-group">
                    <label for="accommodation_type" class="form-label fw-bold fs-5">
                        {{ __('accommodations.fields.accommodation_type') }}
                        <i class="fas fa-info-circle ms-2 fs-6" data-bs-toggle="tooltip" data-bs-placement="top" 
                           title="Select the type of accommodation"></i>
                    </label>
                    <select class="form-control" id="accommodation_type" name="accommodation_type">
                        <option value="">{{ __('accommodations.placeholders.accommodation_type') }}</option>
                        @foreach(__('accommodations.options.accommodation_types') as $key => $value)
                            <option value="{{ $key }}" {{ old('accommodation_type', $accommodation->accommodation_type) == $key ? 'selected' : '' }}>
                                {{ $value }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <hr>

                <div class="form-group">
                    <label for="description" class="form-label fw-bold fs-5">
                        {{ __('accommodations.fields.description') }}
                        <i class="fas fa-info-circle ms-2 fs-6" data-bs-toggle="tooltip" data-bs-placement="top" 
                           title="Describe your accommodation in detail"></i>
                    </label>
                    <textarea class="form-control" id="description" name="description" rows="5" placeholder="{{ __('accommodations.placeholders.description') }}">{{ old('description', $accommodation->description) }}</textarea>
                </div>

                <div class="button-group">
                    <div class="left-buttons">
                        <button type="button" class="btn btn-secondary" id="saveDraftBtn2">
                            Save Draft
                        </button>
                    </div>
                    <div class="right-buttons">
                        <div class="row-button">
                            <button type="button" class="btn btn-info" id="prevBtn2">
                                Previous
                            </button>
                            <button type="button" class="btn btn-primary" id="nextBtn2">
                                Next
                            </button>
                        </div>
                        <button type="submit" class="btn btn-primary" id="submitBtn2" style="display: none;">
                            {{ $isEdit ? __('accommodations.edit') : __('accommodations.create') }}
                        </button>
                    </div>
                </div>
            </div>

            <!-- Step 3: Accommodation Information and Details -->
            <div class="step" id="step3">
                <h5>{{ __('accommodations.sections.accommodation_details') }}</h5>

                <!-- Basic Accommodation Details -->
                <div class="form-group">
                    <label for="accommodation_info" class="form-label fw-bold fs-5">
                        {{ __('accommodations.fields.accommodation_details') }}
                        <i class="fas fa-info-circle ms-2 fs-6" data-bs-toggle="tooltip" data-bs-placement="top" 
                           title="Provide detailed information about your accommodation"></i>
                    </label>
                    
                    <div class="row">
                        <div class="col-md-4">
                            <div class="form-group mb-3">
                                <label for="living_area_sqm" class="form-label">{{ __('accommodations.fields.living_area_sqm') }}</label>
                                <input type="number" class="form-control" id="living_area_sqm" name="living_area_sqm" value="{{ old('living_area_sqm', $accommodation->living_area_sqm) }}" placeholder="{{ __('accommodations.placeholders.living_area_sqm') }}" min="0">
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group mb-3">
                                <label for="max_occupancy" class="form-label">{{ __('accommodations.fields.max_occupancy') }}</label>
                                <input type="number" class="form-control" id="max_occupancy" name="max_occupancy" value="{{ old('max_occupancy', $accommodation->max_occupancy) }}" placeholder="{{ __('accommodations.placeholders.max_occupancy') }}" min="1">
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group mb-3">
                                <label for="number_of_bedrooms" class="form-label">{{ __('accommodations.fields.number_of_bedrooms') }}</label>
                                <input type="number" class="form-control" id="number_of_bedrooms" name="number_of_bedrooms" value="{{ old('number_of_bedrooms', $accommodation->number_of_bedrooms) }}" placeholder="{{ __('accommodations.placeholders.number_of_bedrooms') }}" min="0">
                            </div>
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group mb-3">
                                <label for="bathroom" class="form-label">{{ __('accommodations.fields.bathroom') }}</label>
                                <input type="number" class="form-control" id="bathroom" name="bathroom" value="{{ old('bathroom', $accommodation->bathroom) }}" placeholder="{{ __('accommodations.placeholders.bathroom') }}" min="0">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group mb-3">
                                <label for="condition_or_style" class="form-label">{{ __('accommodations.fields.condition_or_style') }}</label>
                                <input type="text" class="form-control" id="condition_or_style" name="condition_or_style" value="{{ old('condition_or_style', $accommodation->condition_or_style) }}" placeholder="{{ __('accommodations.placeholders.condition_or_style') }}">
                            </div>
                        </div>
                    </div>
                </div>

                <hr>

                <!-- Bed Configuration -->
                <div class="form-group">
                    <label class="form-label fw-bold fs-5">
                        {{ __('accommodations.fields.bed_configuration') }}
                        <i class="fas fa-info-circle ms-2 fs-6" data-bs-toggle="tooltip" data-bs-placement="top" 
                           title="Configure the bed types and quantities in your accommodation"></i>
                    </label>
                    
                    <div class="bed-configuration-grid">
                        @foreach(__('accommodations.options.bed_types') as $key => $value)
                            <div class="btn-checkbox-container">
                                <input type="checkbox" name="bed_type_checkboxes[]" value="{{ $key }}" id="bed_type_{{ $key }}">
                                <label for="bed_type_{{ $key }}" class="btn btn-outline-primary btn-checkbox">
                                    {{ $value }}
                                </label>
                                <input type="number" class="form-control extra-input" name="bed_type_{{ $key }}" placeholder="Quantity of {{ $value }}..." min="0" max="20">
                            </div>
                        @endforeach
                    </div>
                </div>

                <hr>

                <div class="form-group">
                    <label for="floor_layout" class="form-label fw-bold fs-5">
                        {{ __('accommodations.fields.floor_layout') }}
                        <i class="fas fa-info-circle ms-2 fs-6" data-bs-toggle="tooltip" data-bs-placement="top" 
                           title="Describe the floor layout of your accommodation"></i>
                    </label>
                    <input type="text" class="form-control" id="floor_layout" name="floor_layout" value="{{ old('floor_layout', $accommodation->floor_layout) }}" placeholder="{{ __('accommodations.placeholders.floor_layout') }}">
                </div>

                <div class="button-group">
                    <div class="left-buttons">
                        <button type="button" class="btn btn-secondary" id="saveDraftBtn3">
                            Save Draft
                        </button>
                    </div>
                    <div class="right-buttons">
                        <div class="row-button">
                            <button type="button" class="btn btn-info" id="prevBtn3">
                                Previous
                            </button>
                            <button type="button" class="btn btn-primary" id="nextBtn3">
                                Next
                            </button>
                        </div>
                        <button type="submit" class="btn btn-primary" id="submitBtn3" style="display: none;">
                            {{ $isEdit ? __('accommodations.edit') : __('accommodations.create') }}
                        </button>
                    </div>
                </div>
            </div>

            <!-- Step 4: Location Information -->
            <div class="step" id="step4">
                <h5>{{ __('accommodations.sections.location_info') }}</h5>

                <div class="form-group">
                    <label for="location_description" class="form-label fw-bold fs-5">
                        {{ __('accommodations.fields.location_description') }}
                        <i class="fas fa-info-circle ms-2 fs-6" data-bs-toggle="tooltip" data-bs-placement="top" 
                           title="Describe the location and surroundings"></i>
                    </label>
                    <textarea class="form-control" id="location_description" name="location_description" rows="4" placeholder="{{ __('accommodations.placeholders.location_description') }}">{{ old('location_description', $accommodation->location_description) }}</textarea>
                </div>

                <hr>

                <div class="form-group">
                    <label class="form-label fw-bold fs-5">
                        {{ __('accommodations.sections.location_distances') }}
                        <i class="fas fa-info-circle ms-2 fs-6" data-bs-toggle="tooltip" data-bs-placement="top" 
                           title="Provide distances to important locations"></i>
                    </label>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group mb-3">
                                <label for="distance_to_water_m" class="form-label">{{ __('accommodations.fields.distance_to_water_m') }}</label>
                                <input type="number" class="form-control" id="distance_to_water_m" name="distance_to_water_m" value="{{ old('distance_to_water_m', $accommodation->distance_to_water_m) }}" placeholder="{{ __('accommodations.placeholders.distance_to_water_m') }}" min="0">
                            </div>
                            <div class="form-group mb-3">
                                <label for="distance_to_boat_berth_m" class="form-label">{{ __('accommodations.fields.distance_to_boat_berth_m') }}</label>
                                <input type="number" class="form-control" id="distance_to_boat_berth_m" name="distance_to_boat_berth_m" value="{{ old('distance_to_boat_berth_m', $accommodation->distance_to_boat_berth_m) }}" placeholder="{{ __('accommodations.placeholders.distance_to_boat_berth_m') }}" min="0">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group mb-3">
                                <label for="distance_to_shop_km" class="form-label">{{ __('accommodations.fields.distance_to_shop_km') }}</label>
                                <input type="number" class="form-control" id="distance_to_shop_km" name="distance_to_shop_km" value="{{ old('distance_to_shop_km', $accommodation->distance_to_shop_km) }}" placeholder="{{ __('accommodations.placeholders.distance_to_shop_km') }}" min="0" step="0.01">
                            </div>
                            <div class="form-group mb-3">
                                <label for="distance_to_parking_m" class="form-label">{{ __('accommodations.fields.distance_to_parking_m') }}</label>
                                <input type="number" class="form-control" id="distance_to_parking_m" name="distance_to_parking_m" value="{{ old('distance_to_parking_m', $accommodation->distance_to_parking_m) }}" placeholder="{{ __('accommodations.placeholders.distance_to_parking_m') }}" min="0">
                            </div>
                        </div>
                    </div>
                </div>

                <div class="button-group">
                    <div class="left-buttons">
                        <button type="button" class="btn btn-secondary" id="saveDraftBtn4">
                            Save Draft
                        </button>
                    </div>
                    <div class="right-buttons">
                        <div class="row-button">
                            <button type="button" class="btn btn-info" id="prevBtn4">
                                Previous
                            </button>
                            <button type="button" class="btn btn-primary" id="nextBtn4">
                                Next
                            </button>
                        </div>
                        <button type="submit" class="btn btn-primary" id="submitBtn4" style="display: none;">
                            {{ $isEdit ? __('accommodations.edit') : __('accommodations.create') }}
                        </button>
                    </div>
                </div>
            </div>

            <!-- Step 5: Amenities and Features -->
            <div class="step" id="step5">
                <h5>{{ __('accommodations.sections.amenities') }}</h5>

                <div class="form-group">
                    <label class="form-label fw-bold fs-5">
                        {{ __('accommodations.sections.amenities') }}
                        <i class="fas fa-info-circle ms-2 fs-6" data-bs-toggle="tooltip" data-bs-placement="top" 
                           title="Select all amenities available in your accommodation"></i>
                    </label>
                    <div class="d-flex flex-wrap btn-group-toggle">
                        <input type="checkbox" name="terrace" value="1" id="terrace" {{ old('terrace', $accommodation->terrace) ? 'checked' : '' }}>
                        <label for="terrace" class="btn btn-outline-primary m-2 flex-fill btn-checkbox" style="flex-basis: calc(25% - 20px);">{{ __('accommodations.fields.terrace') }}</label>
                        
                        <input type="checkbox" name="garden" value="1" id="garden" {{ old('garden', $accommodation->garden) ? 'checked' : '' }}>
                        <label for="garden" class="btn btn-outline-primary m-2 flex-fill btn-checkbox" style="flex-basis: calc(25% - 20px);">{{ __('accommodations.fields.garden') }}</label>
                        
                        <input type="checkbox" name="swimming_pool" value="1" id="swimming_pool" {{ old('swimming_pool', $accommodation->swimming_pool) ? 'checked' : '' }}>
                        <label for="swimming_pool" class="btn btn-outline-primary m-2 flex-fill btn-checkbox" style="flex-basis: calc(25% - 20px);">{{ __('accommodations.fields.swimming_pool') }}</label>
                        
                        <input type="checkbox" name="private_jetty_boat_dock" value="1" id="private_jetty_boat_dock" {{ old('private_jetty_boat_dock', $accommodation->private_jetty_boat_dock) ? 'checked' : '' }}>
                        <label for="private_jetty_boat_dock" class="btn btn-outline-primary m-2 flex-fill btn-checkbox" style="flex-basis: calc(25% - 20px);">Private jetty / boat dock</label>
                        
                        <input type="checkbox" name="fish_cleaning_station" value="1" id="fish_cleaning_station" {{ old('fish_cleaning_station', $accommodation->fish_cleaning_station) ? 'checked' : '' }}>
                        <label for="fish_cleaning_station" class="btn btn-outline-primary m-2 flex-fill btn-checkbox" style="flex-basis: calc(25% - 20px);">Fish cleaning / filleting station</label>
                        
                        <input type="checkbox" name="smoker" value="1" id="smoker" {{ old('smoker', $accommodation->smoker) ? 'checked' : '' }}>
                        <label for="smoker" class="btn btn-outline-primary m-2 flex-fill btn-checkbox" style="flex-basis: calc(25% - 20px);">Smoker</label>
                        
                        <input type="checkbox" name="barbecue_area" value="1" id="barbecue_area" {{ old('barbecue_area', $accommodation->barbecue_area) ? 'checked' : '' }}>
                        <label for="barbecue_area" class="btn btn-outline-primary m-2 flex-fill btn-checkbox" style="flex-basis: calc(25% - 20px);">Barbecue area</label>
                        
                        <input type="checkbox" name="lockable_storage_fishing" value="1" id="lockable_storage_fishing" {{ old('lockable_storage_fishing', $accommodation->lockable_storage_fishing) ? 'checked' : '' }}>
                        <label for="lockable_storage_fishing" class="btn btn-outline-primary m-2 flex-fill btn-checkbox" style="flex-basis: calc(25% - 20px);">Lockable storage for fishing equipment</label>
                        
                        <input type="checkbox" name="wifi" value="1" id="wifi" {{ old('wifi', $accommodation->wifi) ? 'checked' : '' }}>
                        <label for="wifi" class="btn btn-outline-primary m-2 flex-fill btn-checkbox" style="flex-basis: calc(25% - 20px);">WiFi</label>
                        
                        <input type="checkbox" name="fireplace_stove" value="1" id="fireplace_stove" {{ old('fireplace_stove', $accommodation->fireplace_stove) ? 'checked' : '' }}>
                        <label for="fireplace_stove" class="btn btn-outline-primary m-2 flex-fill btn-checkbox" style="flex-basis: calc(25% - 20px);">Fireplace / stove</label>
                        
                        <input type="checkbox" name="sauna" value="1" id="sauna" {{ old('sauna', $accommodation->sauna) ? 'checked' : '' }}>
                        <label for="sauna" class="btn btn-outline-primary m-2 flex-fill btn-checkbox" style="flex-basis: calc(25% - 20px);">Sauna</label>
                        
                        <input type="checkbox" name="pool_hot_tub" value="1" id="pool_hot_tub" {{ old('pool_hot_tub', $accommodation->pool_hot_tub) ? 'checked' : '' }}>
                        <label for="pool_hot_tub" class="btn btn-outline-primary m-2 flex-fill btn-checkbox" style="flex-basis: calc(25% - 20px);">Pool / hot tub</label>
                        
                        <input type="checkbox" name="games_corner" value="1" id="games_corner" {{ old('games_corner', $accommodation->games_corner) ? 'checked' : '' }}>
                        <label for="games_corner" class="btn btn-outline-primary m-2 flex-fill btn-checkbox" style="flex-basis: calc(25% - 20px);">Billiards / table tennis / darts / games corner</label>
                        
                        <input type="checkbox" name="balcony" value="1" id="balcony" {{ old('balcony', $accommodation->balcony) ? 'checked' : '' }}>
                        <label for="balcony" class="btn btn-outline-primary m-2 flex-fill btn-checkbox" style="flex-basis: calc(25% - 20px);">Balcony</label>
                        
                        <input type="checkbox" name="garden_furniture_sun_loungers" value="1" id="garden_furniture_sun_loungers" {{ old('garden_furniture_sun_loungers', $accommodation->garden_furniture_sun_loungers) ? 'checked' : '' }}>
                        <label for="garden_furniture_sun_loungers" class="btn btn-outline-primary m-2 flex-fill btn-checkbox" style="flex-basis: calc(25% - 20px);">Garden furniture / sun loungers</label>
                        
                        <input type="checkbox" name="parking_spaces" value="1" id="parking_spaces" {{ old('parking_spaces', $accommodation->parking_spaces) ? 'checked' : '' }}>
                        <label for="parking_spaces" class="btn btn-outline-primary m-2 flex-fill btn-checkbox" style="flex-basis: calc(25% - 20px);">Parking spaces</label>
                        
                        <input type="checkbox" name="charging_station_electric_cars" value="1" id="charging_station_electric_cars" {{ old('charging_station_electric_cars', $accommodation->charging_station_electric_cars) ? 'checked' : '' }}>
                        <label for="charging_station_electric_cars" class="btn btn-outline-primary m-2 flex-fill btn-checkbox" style="flex-basis: calc(25% - 20px);">Charging station for electric cars</label>
                        
                        <input type="checkbox" name="boat_ramp_nearby" value="1" id="boat_ramp_nearby" {{ old('boat_ramp_nearby', $accommodation->boat_ramp_nearby) ? 'checked' : '' }}>
                        <label for="boat_ramp_nearby" class="btn btn-outline-primary m-2 flex-fill btn-checkbox" style="flex-basis: calc(25% - 20px);">Boat ramp nearby</label>
                        
                        <input type="checkbox" name="tv" value="1" id="tv" {{ old('tv', $accommodation->tv) ? 'checked' : '' }}>
                        <label for="tv" class="btn btn-outline-primary m-2 flex-fill btn-checkbox" style="flex-basis: calc(25% - 20px);">TV</label>
                        
                        <input type="checkbox" name="sound_system" value="1" id="sound_system" {{ old('sound_system', $accommodation->sound_system) ? 'checked' : '' }}>
                        <label for="sound_system" class="btn btn-outline-primary m-2 flex-fill btn-checkbox" style="flex-basis: calc(25% - 20px);">Sound system</label>
                        
                        <input type="checkbox" name="reception" value="1" id="reception" {{ old('reception', $accommodation->reception) ? 'checked' : '' }}>
                        <label for="reception" class="btn btn-outline-primary m-2 flex-fill btn-checkbox" style="flex-basis: calc(25% - 20px);">Reception</label>
                        
                        <input type="checkbox" name="keybox" value="1" id="keybox" {{ old('keybox', $accommodation->keybox) ? 'checked' : '' }}>
                        <label for="keybox" class="btn btn-outline-primary m-2 flex-fill btn-checkbox" style="flex-basis: calc(25% - 20px);">Keybox</label>
                        
                        <input type="checkbox" name="heating" value="1" id="heating" {{ old('heating', $accommodation->heating) ? 'checked' : '' }}>
                        <label for="heating" class="btn btn-outline-primary m-2 flex-fill btn-checkbox" style="flex-basis: calc(25% - 20px);">Heating</label>
                        
                        <input type="checkbox" name="air_conditioning" value="1" id="air_conditioning" {{ old('air_conditioning', $accommodation->air_conditioning) ? 'checked' : '' }}>
                        <label for="air_conditioning" class="btn btn-outline-primary m-2 flex-fill btn-checkbox" style="flex-basis: calc(25% - 20px);">Air Conditioning</label>
                    </div>
                </div>

                <hr>

                <div class="form-group">
                    <label class="form-label fw-bold fs-5">
                        {{ __('accommodations.sections.kitchen_equipment') }}
                        <i class="fas fa-info-circle ms-2 fs-6" data-bs-toggle="tooltip" data-bs-placement="top" 
                           title="Select kitchen equipment available"></i>
                    </label>
                    <div class="d-flex flex-wrap btn-group-toggle">
                        <input type="checkbox" name="refrigerator" value="1" id="refrigerator" {{ old('refrigerator', $accommodation->refrigerator) ? 'checked' : '' }}>
                        <label for="refrigerator" class="btn btn-outline-primary m-2 flex-fill btn-checkbox" style="flex-basis: calc(25% - 20px);">Refrigerator</label>
                        
                        <input type="checkbox" name="freezer" value="1" id="freezer" {{ old('freezer', $accommodation->freezer) ? 'checked' : '' }}>
                        <label for="freezer" class="btn btn-outline-primary m-2 flex-fill btn-checkbox" style="flex-basis: calc(25% - 20px);">Freezer or freezer compartment</label>
                        
                        <input type="checkbox" name="oven" value="1" id="oven" {{ old('oven', $accommodation->oven) ? 'checked' : '' }}>
                        <label for="oven" class="btn btn-outline-primary m-2 flex-fill btn-checkbox" style="flex-basis: calc(25% - 20px);">{{ __('accommodations.fields.oven') }}</label>
                        
                        <input type="checkbox" name="stove_or_ceramic_hob" value="1" id="stove_or_ceramic_hob" {{ old('stove_or_ceramic_hob', $accommodation->stove_or_ceramic_hob) ? 'checked' : '' }}>
                        <label for="stove_or_ceramic_hob" class="btn btn-outline-primary m-2 flex-fill btn-checkbox" style="flex-basis: calc(25% - 20px);">{{ __('accommodations.fields.stove_or_ceramic_hob') }}</label>
                        
                        <input type="checkbox" name="microwave" value="1" id="microwave" {{ old('microwave', $accommodation->microwave) ? 'checked' : '' }}>
                        <label for="microwave" class="btn btn-outline-primary m-2 flex-fill btn-checkbox" style="flex-basis: calc(25% - 20px);">{{ __('accommodations.fields.microwave') }}</label>
                        
                        <input type="checkbox" name="dishwasher" value="1" id="dishwasher" {{ old('dishwasher', $accommodation->dishwasher) ? 'checked' : '' }}>
                        <label for="dishwasher" class="btn btn-outline-primary m-2 flex-fill btn-checkbox" style="flex-basis: calc(25% - 20px);">{{ __('accommodations.fields.dishwasher') }}</label>
                        
                        <input type="checkbox" name="coffee_machine" value="1" id="coffee_machine" {{ old('coffee_machine', $accommodation->coffee_machine) ? 'checked' : '' }}>
                        <label for="coffee_machine" class="btn btn-outline-primary m-2 flex-fill btn-checkbox" style="flex-basis: calc(25% - 20px);">{{ __('accommodations.fields.coffee_machine') }}</label>
                        
                        <input type="checkbox" name="kettle" value="1" id="kettle" {{ old('kettle', $accommodation->kettle) ? 'checked' : '' }}>
                        <label for="kettle" class="btn btn-outline-primary m-2 flex-fill btn-checkbox" style="flex-basis: calc(25% - 20px);">Kettle</label>
                        
                        <input type="checkbox" name="toaster" value="1" id="toaster" {{ old('toaster', $accommodation->toaster) ? 'checked' : '' }}>
                        <label for="toaster" class="btn btn-outline-primary m-2 flex-fill btn-checkbox" style="flex-basis: calc(25% - 20px);">Toaster</label>
                        
                        <input type="checkbox" name="blender_hand_mixer" value="1" id="blender_hand_mixer" {{ old('blender_hand_mixer', $accommodation->blender_hand_mixer) ? 'checked' : '' }}>
                        <label for="blender_hand_mixer" class="btn btn-outline-primary m-2 flex-fill btn-checkbox" style="flex-basis: calc(25% - 20px);">Blender or hand mixer</label>
                        
                        <input type="checkbox" name="basic_cooking_supplies" value="1" id="basic_cooking_supplies" {{ old('basic_cooking_supplies', $accommodation->basic_cooking_supplies) ? 'checked' : '' }}>
                        <label for="basic_cooking_supplies" class="btn btn-outline-primary m-2 flex-fill btn-checkbox" style="flex-basis: calc(25% - 20px);">Basic cooking supplies</label>
                        
                        <input type="checkbox" name="kitchen_utensils" value="1" id="kitchen_utensils" {{ old('kitchen_utensils', $accommodation->kitchen_utensils) ? 'checked' : '' }}>
                        <label for="kitchen_utensils" class="btn btn-outline-primary m-2 flex-fill btn-checkbox" style="flex-basis: calc(25% - 20px);">Kitchen utensils</label>
                        
                        <input type="checkbox" name="baking_equipment" value="1" id="baking_equipment" {{ old('baking_equipment', $accommodation->baking_equipment) ? 'checked' : '' }}>
                        <label for="baking_equipment" class="btn btn-outline-primary m-2 flex-fill btn-checkbox" style="flex-basis: calc(25% - 20px);">Baking equipment</label>
                        
                        <input type="checkbox" name="dishwashing_items" value="1" id="dishwashing_items" {{ old('dishwashing_items', $accommodation->dishwashing_items) ? 'checked' : '' }}>
                        <label for="dishwashing_items" class="btn btn-outline-primary m-2 flex-fill btn-checkbox" style="flex-basis: calc(25% - 20px);">Dishwashing items</label>
                        
                        <input type="checkbox" name="wine_glasses" value="1" id="wine_glasses" {{ old('wine_glasses', $accommodation->wine_glasses) ? 'checked' : '' }}>
                        <label for="wine_glasses" class="btn btn-outline-primary m-2 flex-fill btn-checkbox" style="flex-basis: calc(25% - 20px);">Wine Glasses</label>
                    </div>
                </div>

                <hr>

                <div class="form-group">
                    <label class="form-label fw-bold fs-5">
                        Bathroom Amenities
                        <i class="fas fa-info-circle ms-2 fs-6" data-bs-toggle="tooltip" data-bs-placement="top" 
                           title="Select bathroom amenities available"></i>
                        </label>
                    <div class="d-flex flex-wrap btn-group-toggle">
                        <input type="checkbox" name="iron_and_ironing_board" value="1" id="iron_and_ironing_board" {{ old('iron_and_ironing_board', $accommodation->iron_and_ironing_board) ? 'checked' : '' }}>
                        <label for="iron_and_ironing_board" class="btn btn-outline-primary m-2 flex-fill btn-checkbox" style="flex-basis: calc(25% - 20px);">Iron and ironing board</label>
                        
                        <input type="checkbox" name="clothes_drying_rack" value="1" id="clothes_drying_rack" {{ old('clothes_drying_rack', $accommodation->clothes_drying_rack) ? 'checked' : '' }}>
                        <label for="clothes_drying_rack" class="btn btn-outline-primary m-2 flex-fill btn-checkbox" style="flex-basis: calc(25% - 20px);">Clothes drying rack / clothesline</label>
                        
                        <input type="checkbox" name="toilet" value="1" id="toilet" {{ old('toilet', $accommodation->toilet) ? 'checked' : '' }}>
                        <label for="toilet" class="btn btn-outline-primary m-2 flex-fill btn-checkbox" style="flex-basis: calc(25% - 20px);">Toilet</label>
                        
                        <input type="checkbox" name="own_shower" value="1" id="own_shower" {{ old('own_shower', $accommodation->own_shower) ? 'checked' : '' }}>
                        <label for="own_shower" class="btn btn-outline-primary m-2 flex-fill btn-checkbox" style="flex-basis: calc(25% - 20px);">Own shower</label>
                    </div>
                </div>

                <div class="button-group">
                    <div class="left-buttons">
                        <button type="button" class="btn btn-secondary" id="saveDraftBtn5">
                            Save Draft
                        </button>
                    </div>
                    <div class="right-buttons">
                        <div class="row-button">
                            <button type="button" class="btn btn-info" id="prevBtn5">
                                Previous
                            </button>
                            <button type="button" class="btn btn-primary" id="nextBtn5">
                                Next
                            </button>
                        </div>
                        <button type="submit" class="btn btn-primary" id="submitBtn5" style="display: none;">
                            {{ $isEdit ? __('accommodations.edit') : __('accommodations.create') }}
                        </button>
                    </div>
                </div>
            </div>

            <!-- Step 6: Pricing and Policies -->
            <div class="step" id="step6">
                <h5>{{ __('accommodations.sections.pricing') }}</h5>

                <div class="form-group">
                    <label for="pricing" class="form-label fw-bold fs-5">
                        {{ __('accommodations.sections.pricing') }}
                        <i class="fas fa-info-circle ms-2 fs-6" data-bs-toggle="tooltip" data-bs-placement="top" 
                           title="Set your pricing structure"></i>
                    </label>
                    
                    <div class="alert alert-secondary mb-3 d-flex">
                        <i class="fas fa-info-circle me-2"></i>
                        <div>Choose how you want to price your accommodation. You can charge per accommodation (fixed price regardless of number of guests) or per person (price varies based on number of guests).</div>
                    </div>
                    
                    <div class="d-flex flex-wrap btn-group-toggle">
                        <input type="radio" name="price_type" value="per_accommodation" id="per_accommodation_checkbox" {{ old('price_type', $accommodation->price_type) == 'per_accommodation' ? 'checked' : '' }}>
                        <label for="per_accommodation_checkbox" class="btn btn-outline-primary m-2 flex-fill btn-checkbox" style="flex-basis: calc(50% - 20px);">
                            Price per accommodation
                        </label>
                        
                        <input type="radio" name="price_type" value="per_person" id="per_person_checkbox" {{ old('price_type', $accommodation->price_type) == 'per_person' ? 'checked' : '' }}>
                        <label for="per_person_checkbox" class="btn btn-outline-primary m-2 flex-fill btn-checkbox" style="flex-basis: calc(50% - 20px);">
                            Price per person
                        </label>
                    </div>
                    
                    <!-- Per accommodation pricing -->
                    <div id="per_accommodation_pricing" style="display: none; margin-top: 15px; padding: 15px; border: 1px solid #ddd; border-radius: 5px; background-color: #f9f9f9;">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group mb-3">
                                    <label for="price_per_night" class="form-label">Price per night</label>
                                <div class="input-group">
                                    <span class="input-group-text">€</span>
                                        <input type="number" class="form-control" id="price_per_night" name="price_per_night" value="{{ old('price_per_night', $accommodation->price_per_night) }}" placeholder="Enter price per night" min="0" step="0.01">
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group mb-3">
                                    <label for="price_per_week" class="form-label">Price per week</label>
                                <div class="input-group">
                                    <span class="input-group-text">€</span>
                                        <input type="number" class="form-control" id="price_per_week" name="price_per_week" value="{{ old('price_per_week', $accommodation->price_per_week) }}" placeholder="Enter price per week" min="0" step="0.01">
                                </div>
                            </div>
                        </div>
                        </div>
                    </div>
                    
                    <!-- Per person pricing -->
                    <div id="per_person_pricing" style="display: none; margin-top: 15px; padding: 15px; border: 1px solid #ddd; border-radius: 5px; background-color: #f9f9f9;">
                        <div class="alert alert-info">
                            <i class="fas fa-info-circle me-2"></i>
                            <strong>Per person pricing:</strong> You can set different prices based on the number of guests. The system will use the maximum occupancy you set earlier to create pricing tiers.
                        </div>
                        <div class="alert alert-info">
                            <i class="fas fa-info-circle me-2"></i>
                            <strong>Pricing per person:</strong> Set the price per person for each guest count. For example, if you set €50 for 1 guest and €45 for 2 guests, a single guest will pay €50 per night, while two guests will pay €45 each (€90 total).
                        </div>
                        <div class="form-group" id="dynamic-person-pricing-container"></div>
                    </div>
                    
                    <!-- Currency selection -->
                    <div class="mt-3">
                        <label for="currency" class="form-label">Currency</label>
                        <select class="form-control" id="currency" name="currency">
                            <option value="EUR" {{ old('currency', $accommodation->currency ?? 'EUR') == 'EUR' ? 'selected' : '' }}>Euro (EUR)</option>
                            <option value="USD" {{ old('currency', $accommodation->currency) == 'USD' ? 'selected' : '' }}>US Dollar (USD)</option>
                            <option value="GBP" {{ old('currency', $accommodation->currency) == 'GBP' ? 'selected' : '' }}>British Pound (GBP)</option>
                        </select>
                    </div>
                </div>

                <hr>

                <div class="form-group">
                    <label class="form-label fw-bold fs-5">
                        {{ __('accommodations.sections.policies') }}
                        <i class="fas fa-info-circle ms-2 fs-6" data-bs-toggle="tooltip" data-bs-placement="top" 
                           title="Set your rental policies"></i>
                    </label>
                    
                    <div class="policies-grid">
                        <div class="btn-checkbox-container">
                            <input type="checkbox" name="policy_checkboxes[]" value="bed_linen_included" id="policy_bed_linen_included">
                            <label for="policy_bed_linen_included" class="btn btn-outline-primary btn-checkbox">
                                {{ __('accommodations.fields.bed_linen_included') }}
                            </label>
                            <textarea class="form-control extra-input" name="policy_bed_linen_included" placeholder="{{ __('accommodations.fields.bed_linen_included') }} details..."></textarea>
                        </div>

                        <div class="btn-checkbox-container">
                            <input type="checkbox" name="policy_checkboxes[]" value="pets_allowed" id="policy_pets_allowed">
                            <label for="policy_pets_allowed" class="btn btn-outline-primary btn-checkbox">
                                {{ __('accommodations.fields.pets_allowed') }}
                            </label>
                            <textarea class="form-control extra-input" name="policy_pets_allowed" placeholder="{{ __('accommodations.fields.pets_allowed') }} details..."></textarea>
                        </div>

                        <div class="btn-checkbox-container">
                            <input type="checkbox" name="policy_checkboxes[]" value="smoking_allowed" id="policy_smoking_allowed">
                            <label for="policy_smoking_allowed" class="btn btn-outline-primary btn-checkbox">
                                {{ __('accommodations.fields.smoking_allowed') }}
                            </label>
                            <textarea class="form-control extra-input" name="policy_smoking_allowed" placeholder="{{ __('accommodations.fields.smoking_allowed') }} details..."></textarea>
                        </div>

                        <div class="btn-checkbox-container">
                            <input type="checkbox" name="policy_checkboxes[]" value="towels_included" id="policy_towels_included">
                            <label for="policy_towels_included" class="btn btn-outline-primary btn-checkbox">
                                Towels included
                            </label>
                            <textarea class="form-control extra-input" name="policy_towels_included" placeholder="Towels included details..."></textarea>
                        </div>

                        <div class="btn-checkbox-container">
                            <input type="checkbox" name="policy_checkboxes[]" value="quiet_hours_no_parties" id="policy_quiet_hours_no_parties">
                            <label for="policy_quiet_hours_no_parties" class="btn btn-outline-primary btn-checkbox">
                                Quiet hours / no parties
                            </label>
                            <textarea class="form-control extra-input" name="policy_quiet_hours_no_parties" placeholder="Quiet hours / no parties details..."></textarea>
                        </div>

                        <div class="btn-checkbox-container">
                            <input type="checkbox" name="policy_checkboxes[]" value="checkin_checkout_times" id="policy_checkin_checkout_times">
                            <label for="policy_checkin_checkout_times" class="btn btn-outline-primary btn-checkbox">
                                Check-in / Check-out times
                            </label>
                            <textarea class="form-control extra-input" name="policy_checkin_checkout_times" placeholder="Check-in / Check-out times details..."></textarea>
                        </div>

                        <div class="btn-checkbox-container">
                            <input type="checkbox" name="policy_checkboxes[]" value="children_allowed_child_friendly" id="policy_children_allowed_child_friendly">
                            <label for="policy_children_allowed_child_friendly" class="btn btn-outline-primary btn-checkbox">
                                Children allowed / child-friendly
                            </label>
                            <textarea class="form-control extra-input" name="policy_children_allowed_child_friendly" placeholder="Children allowed / child-friendly details..."></textarea>
                        </div>

                        <div class="btn-checkbox-container">
                            <input type="checkbox" name="policy_checkboxes[]" value="accessible_barrier_free" id="policy_accessible_barrier_free">
                            <label for="policy_accessible_barrier_free" class="btn btn-outline-primary btn-checkbox">
                                Accessible / barrier-free
                            </label>
                            <textarea class="form-control extra-input" name="policy_accessible_barrier_free" placeholder="Accessible / barrier-free details..."></textarea>
                        </div>

                        <div class="btn-checkbox-container">
                            <input type="checkbox" name="policy_checkboxes[]" value="energy_usage_included" id="policy_energy_usage_included">
                            <label for="policy_energy_usage_included" class="btn btn-outline-primary btn-checkbox">
                                Energy usage included
                        </label>
                            <textarea class="form-control extra-input" name="policy_energy_usage_included" placeholder="Energy usage included details..."></textarea>
                        </div>

                        <div class="btn-checkbox-container">
                            <input type="checkbox" name="policy_checkboxes[]" value="water_usage_included" id="policy_water_usage_included">
                            <label for="policy_water_usage_included" class="btn btn-outline-primary btn-checkbox">
                                Water usage included
                        </label>
                            <textarea class="form-control extra-input" name="policy_water_usage_included" placeholder="Water usage included details..."></textarea>
                        </div>

                        <div class="btn-checkbox-container">
                            <input type="checkbox" name="policy_checkboxes[]" value="parking_availability" id="policy_parking_availability">
                            <label for="policy_parking_availability" class="btn btn-outline-primary btn-checkbox">
                                Parking availability
                        </label>
                            <textarea class="form-control extra-input" name="policy_parking_availability" placeholder="Parking availability details..."></textarea>
                        </div>
                    </div>
                </div>

                <hr>

                <div class="form-group">
                    <label class="form-label fw-bold fs-5">
                        Rental Conditions
                        <i class="fas fa-info-circle ms-2 fs-6" data-bs-toggle="tooltip" data-bs-placement="top" 
                           title="Set specific rental conditions and rules"></i>
                        </label>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group mb-3">
                                <label for="checkin_checkout_time" class="form-label">{{ __('accommodations.fields.checkin_checkout_time') }}</label>
                                <input type="text" class="form-control" id="checkin_checkout_time" name="checkin_checkout_time" 
                                       value="{{ old('checkin_checkout_time', $accommodation->checkin_checkout_time) }}" 
                                       placeholder="e.g., Check-in: 15:00, Check-out: 10:00">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group mb-3">
                                <label class="form-label">{{ __('accommodations.fields.self_checkin') }}</label>
                                <div class="d-flex flex-wrap btn-group-toggle">
                                    <input type="checkbox" name="self_checkin" value="1" id="self_checkin" {{ old('self_checkin', $accommodation->self_checkin) ? 'checked' : '' }}>
                                    <label for="self_checkin" class="btn btn-outline-primary m-2 flex-fill btn-checkbox" style="flex-basis: calc(100% - 20px);">{{ __('accommodations.fields.self_checkin') }}</label>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group mb-3">
                                <label for="quiet_times" class="form-label">{{ __('accommodations.fields.quiet_times') }}</label>
                                <input type="text" class="form-control" id="quiet_times" name="quiet_times" 
                                       value="{{ old('quiet_times', $accommodation->quiet_times) }}" 
                                       placeholder="e.g., 22:00 - 7:00">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group mb-3">
                                <label for="waste_disposal_recycling_rules" class="form-label">{{ __('accommodations.fields.waste_disposal_recycling_rules') }}</label>
                                <input type="text" class="form-control" id="waste_disposal_recycling_rules" name="waste_disposal_recycling_rules" 
                                       value="{{ old('waste_disposal_recycling_rules', $accommodation->waste_disposal_recycling_rules) }}" 
                                       placeholder="e.g., Separate recycling bins provided">
                            </div>
                        </div>
                    </div>
                </div>

                <div class="button-group">
                    <div class="left-buttons">
                        <button type="button" class="btn btn-secondary" id="saveDraftBtn6">
                            Save Draft
                        </button>
                    </div>
                    <div class="right-buttons">
                        <div class="row-button">
                            <button type="button" class="btn btn-info" id="prevBtn6">
                                Previous
                            </button>
                            <div></div>
                        </div>
                        <button type="submit" class="btn btn-primary" id="submitBtn6" onclick="document.getElementById('is_draft').value = '0';">
                            {{ $isEdit ? __('accommodations.edit') : __('accommodations.create') }}
                        </button>
                    </div>
                </div>
            </div>
        </form>
    </div>
</div>
@include('components.accommodation-form-scripts')

                            <div></div>

                        </div>

                        <button type="submit" class="btn btn-primary" id="submitBtn6" onclick="document.getElementById('is_draft').value = '0';">

                            {{ $isEdit ? __('accommodations.edit') : __('accommodations.create') }}

                        </button>

                    </div>

                </div>

            </div>

        </form>

    </div>

</div>

@include('components.accommodation-form-scripts')
