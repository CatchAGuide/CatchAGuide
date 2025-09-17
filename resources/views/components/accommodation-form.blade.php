@php
    $accommodation = $accommodation ?? new \App\Models\Accommodation();
    $isEdit = $accommodation->exists;
    $formAction = $isEdit ? route('admin.accommodations.update', $accommodation) : route('admin.accommodations.store');
    $formMethod = $isEdit ? 'PUT' : 'POST';
@endphp

@include('components.accommodation-form-styles')
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

                <div class="form-group">
                    <label for="accommodation_info" class="form-label fw-bold fs-5">
                        {{ __('accommodations.fields.accommodation_details') }}
                        <i class="fas fa-info-circle ms-2 fs-6" data-bs-toggle="tooltip" data-bs-placement="top" 
                           title="Provide detailed information about your accommodation"></i>
                    </label>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group mb-3">
                                <label for="living_area_sqm" class="form-label">{{ __('accommodations.fields.living_area_sqm') }}</label>
                                <input type="number" class="form-control" id="living_area_sqm" name="living_area_sqm" value="{{ old('living_area_sqm', $accommodation->living_area_sqm) }}" placeholder="{{ __('accommodations.placeholders.living_area_sqm') }}" min="0">
                            </div>
                            <div class="form-group mb-3">
                                <label for="max_occupancy" class="form-label">{{ __('accommodations.fields.max_occupancy') }}</label>
                                <input type="number" class="form-control" id="max_occupancy" name="max_occupancy" value="{{ old('max_occupancy', $accommodation->max_occupancy) }}" placeholder="{{ __('accommodations.placeholders.max_occupancy') }}" min="1">
                            </div>
                            <div class="form-group mb-3">
                                <label for="number_of_bedrooms" class="form-label">{{ __('accommodations.fields.number_of_bedrooms') }}</label>
                                <input type="number" class="form-control" id="number_of_bedrooms" name="number_of_bedrooms" value="{{ old('number_of_bedrooms', $accommodation->number_of_bedrooms) }}" placeholder="{{ __('accommodations.placeholders.number_of_bedrooms') }}" min="0">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group mb-3">
                                <label for="number_of_beds" class="form-label">{{ __('accommodations.fields.number_of_beds') }}</label>
                                <input type="number" class="form-control" id="number_of_beds" name="number_of_beds" value="{{ old('number_of_beds', $accommodation->number_of_beds) }}" placeholder="{{ __('accommodations.placeholders.number_of_beds') }}" min="0">
                            </div>
                            <div class="form-group mb-3">
                                <label for="bathroom" class="form-label">{{ __('accommodations.fields.bathroom') }}</label>
                                <input type="number" class="form-control" id="bathroom" name="bathroom" value="{{ old('bathroom', $accommodation->bathroom) }}" placeholder="{{ __('accommodations.placeholders.bathroom') }}" min="0">
                            </div>
                            <div class="form-group mb-3">
                                <label for="condition_or_style" class="form-label">{{ __('accommodations.fields.condition_or_style') }}</label>
                                <input type="text" class="form-control" id="condition_or_style" name="condition_or_style" value="{{ old('condition_or_style', $accommodation->condition_or_style) }}" placeholder="{{ __('accommodations.placeholders.condition_or_style') }}">
                            </div>
                        </div>
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
                        
                        <label class="checkbox-label">
                            <input type="checkbox" name="wifi_or_internet" value="1" {{ old('wifi_or_internet', $accommodation->wifi_or_internet) ? 'checked' : '' }}>
                            <span class="checkbox-text">{{ __('accommodations.fields.wifi_or_internet') }}</span>
                        </label>
                    </div>
                </div>

                <hr>

                <div class="form-group">
                    <label class="form-label fw-bold fs-5">
                        {{ __('accommodations.sections.kitchen_equipment') }}
                        <i class="fas fa-info-circle ms-2 fs-6" data-bs-toggle="tooltip" data-bs-placement="top" 
                           title="Select kitchen equipment available"></i>
                    </label>
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
                    <label class="form-label fw-bold fs-5">
                        {{ __('accommodations.sections.pricing') }}
                        <i class="fas fa-info-circle ms-2 fs-6" data-bs-toggle="tooltip" data-bs-placement="top" 
                           title="Set your pricing structure"></i>
                    </label>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group mb-3">
                                <label for="price_per_night" class="form-label">{{ __('accommodations.fields.price_per_night') }}</label>
                                <div class="input-group">
                                    <span class="input-group-text">€</span>
                                    <input type="number" class="form-control" id="price_per_night" name="price_per_night" value="{{ old('price_per_night', $accommodation->price_per_night) }}" placeholder="{{ __('accommodations.placeholders.price_per_night') }}" min="0" step="0.01">
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group mb-3">
                                <label for="price_per_week" class="form-label">{{ __('accommodations.fields.price_per_week') }}</label>
                                <div class="input-group">
                                    <span class="input-group-text">€</span>
                                    <input type="number" class="form-control" id="price_per_week" name="price_per_week" value="{{ old('price_per_week', $accommodation->price_per_week) }}" placeholder="{{ __('accommodations.placeholders.price_per_week') }}" min="0" step="0.01">
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <hr>

                <div class="form-group">
                    <label class="form-label fw-bold fs-5">
                        {{ __('accommodations.sections.policies') }}
                        <i class="fas fa-info-circle ms-2 fs-6" data-bs-toggle="tooltip" data-bs-placement="top" 
                           title="Set your rental policies"></i>
                    </label>
                    <div class="checkbox-grid">
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
