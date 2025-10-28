@include('components.accommodation-form-styles')
<div id="accommodation-form" class="card">
    <div class="card-body">
        
        <div class="step-wrapper">
            <div class="step-buttons">
                <button type="button" class="step-button active" data-step="1">
                    <i class="fas fa-images"></i>
                </button>
                <button type="button" class="step-button" data-step="2">
                    <i class="fas fa-home"></i>
                </button>
                <button type="button" class="step-button" data-step="3">
                    <i class="fas fa-info-circle"></i>
                </button>
                <button type="button" class="step-button" data-step="4">
                    <i class="fas fa-map-marker-alt"></i>
                </button>
                <button type="button" class="step-button" data-step="5">
                    <i class="fas fa-euro-sign"></i>
                </button>
                <button type="button" class="step-button" data-step="6">
                    <i class="fas fa-list-alt"></i>
                </button>
            </div>

            <div class="step-line"></div>
        </div>
        <div id="error-container" class="alert alert-danger" style="display: none;"></div>

        <form action="{{ $formAction ?? (isset($formData['id']) && $formData['id'] ? route('admin.accommodations.update', $formData['id']) : route('admin.accommodations.store')) }}" method="POST" id="accommodationForm" enctype="multipart/form-data">
            @csrf
            @if(isset($formData['id']) && $formData['id'])
                @method('PUT')
            @endif
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
            <input type="hidden" name="is_update" id="is_update" value="{{ $formData['is_update'] ?? 0 }}">
            <input type="hidden" name="is_draft" id="is_draft" value="{{ isset($formData['status']) && $formData['status'] == 'draft' ? 1 : 0 }}">
            <input type="hidden" name="accommodation_id" id="accommodation_id" value="{{ $formData['id'] ?? 0 }}">
            <input type="hidden" name="thumbnail_path" id="thumbnail_path" value="{{ $formData['thumbnail_path'] ?? '' }}">
            <input type="hidden" name="existing_images" id="existing_images" value="{{ isset($formData['gallery_images']) && is_array($formData['gallery_images']) ? json_encode($formData['gallery_images']) : (isset($formData['gallery_images']) ? $formData['gallery_images'] : '') }}">
            <input type="hidden" name="user_id" id="user_id" value="{{ $formData['user_id'] ?? auth()->id() }}">
            <input type="hidden" name="status" id="status" value="{{ $formData['status'] ?? 'active' }}">
            <input type="hidden" id="image_list" name="image_list">


            <!-- Step 1: Gallery, Location and Title -->
            <div class="step active" id="step1">
                {{-- <h5>{{ __('accommodations.upload_images_title') }}</h5> --}}

                <label for="title_image" class="form-label fw-bold fs-5">
                    {{ __('accommodations.upload_image') }}
                    <i class="fas fa-info-circle ms-2 fs-6" data-bs-toggle="tooltip" data-bs-placement="top" 
                       title="{{ __('accommodations.tooltip_upload_image') }}"></i>
                </label>
                <div class="card mb-4">
                    <div class="card-body">
                        <div class="file-upload-wrapper">
                            <input id="title_image" name="title_image[]" type="file" multiple />
                            <input id="cropped_image" name="cropped_image[]" type="file" multiple hidden/>
                            <label for="title_image" class="file-upload-btn">{{ __('accommodations.choose_files') }}</label>
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
                           title="{{ __('accommodations.tooltip_location') }}"></i>
                    </label>
                    <input type="search" class="form-control" id="location" name="location" value="{{ $formData['location'] ?? '' }}" placeholder="{{ __('accommodations.location_placeholder') }}">
                    <input type="hidden" name="latitude" id="latitude" value="{{ $formData['lat'] ?? '' }}">
                    <input type="hidden" name="longitude" id="longitude" value="{{ $formData['lng'] ?? '' }}">
                    <input type="hidden" name="country" id="country" value="{{ $formData['country'] ?? '' }}">
                    <input type="hidden" name="city" id="city" value="{{ $formData['city'] ?? '' }}">
                    <input type="hidden" name="region" id="region" value="{{ $formData['region'] ?? '' }}">
                </div>

                <hr>

                <div class="form-group">
                    <label for="title" class="form-label fw-bold fs-5">
                        {{ __('accommodations.fields.title') }}
                        <i class="fas fa-info-circle ms-2 fs-6" data-bs-toggle="tooltip" data-bs-placement="top" title="{{ __('accommodations.tooltip_title') }}"></i>
                    </label>
                    <input type="text" class="form-control" id="title" name="title" value="{{ $formData['title'] ?? '' }}" placeholder="{{ __('accommodations.enter_catchy_title') }}">
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
                                {{ __('accommodations.next') }}
                            </button>
                        </div>
                        <button type="submit" class="btn btn-primary" id="submitBtn1" style="display: none;">
                            {{ __('accommodations.submit_publish') }}
                        </button>
                    </div>
                </div>
            </div>

            <!-- Step 2: Accommodation Types -->
            <div class="step" id="step2">
                <h5>{{ __('accommodations.accommodation_types_title') }}</h5>

                <div class="form-group">
                    <label for="accommodation_types" class="form-label fw-bold fs-5">
                        {{ __('accommodations.accommodation_types') }}
                        <i class="fas fa-info-circle ms-2 fs-6" data-bs-toggle="tooltip" data-bs-placement="top" 
                           title="{{ __('accommodations.tooltip_accommodation_types') }}"></i>
                    </label>
                    <div class="d-flex flex-wrap btn-group-toggle">
                        @foreach($accommodationTypes ?? [] as $accommodationType)
                            <input type="radio" name="accommodation_type" value="{{ $accommodationType->id }}" id="accommodation_type_{{ $accommodationType->id }}" 
                                   {{ (isset($formData['accommodation_type']) && $formData['accommodation_type'] == $accommodationType->id) ? 'checked' : '' }}>
                            <label for="accommodation_type_{{ $accommodationType->id }}" class="btn btn-outline-primary m-2 flex-fill btn-checkbox" style="flex-basis: calc(33.33% - 20px);">
                                {{ $accommodationType->name }}
                    </label>
                        @endforeach
                </div>
                </div>

                <div class="button-group">
                    <div class="left-buttons">
                        <button type="button" class="btn btn-secondary" id="saveDraftBtn2">
                            {{ __('accommodations.leave_save_draft') }}
                        </button>
                    </div>
                    <div class="right-buttons">
                        <div class="row-button">
                            <button type="button" class="btn btn-info" id="prevBtn2">
                                {{ __('accommodations.previous') }}
                            </button>
                            <button type="button" class="btn btn-primary" id="nextBtn2">
                                {{ __('accommodations.next') }}
                            </button>
                        </div>
                        <button type="submit" class="btn btn-primary" id="submitBtn2" style="display: none;">
                            {{ __('accommodations.submit_publish') }}
                        </button>
                    </div>
                </div>
            </div>

            <!-- Step 3: Accommodation Details -->
            <div class="step" id="step3">
                <h5>{{ __('accommodations.accommodation_details_title') }}</h5>

                <!-- Max Occupancy Field -->
                <div class="form-group mb-4">
                    <label for="max_occupancy" class="form-label fw-bold fs-5">
                        {{ __('accommodations.max_occupancy') }}
                        <i class="fas fa-info-circle ms-2 fs-6" data-bs-toggle="tooltip" data-bs-placement="top" 
                           title="Maximum number of guests this accommodation can host"></i>
                    </label>
                    <input type="number" class="form-control" id="max_occupancy" name="max_occupancy" 
                           value="{{ $formData['max_occupancy'] ?? '' }}" min="1" 
                           placeholder="Enter maximum occupancy">
                </div>

                <hr>

                <div class="form-group">
                    <label for="accommodation_details" class="form-label fw-bold fs-5">
                        {{ __('accommodations.accommodation_details') }}
                        <i class="fas fa-info-circle ms-2 fs-6" data-bs-toggle="tooltip" data-bs-placement="top" 
                           title="{{ __('accommodations.tooltip_accommodation_details') }}"></i>
                    </label>
                    
                    <div class="accommodation-details-grid">
                        @foreach($accommodationDetails ?? [] as $accommodationDetail)
                            <div class="btn-checkbox-container">
                                <input type="checkbox" name="accommodation_detail_checkboxes[]" value="{{ $accommodationDetail->id }}" id="accommodation_detail_{{ $accommodationDetail->id }}">
                                <label for="accommodation_detail_{{ $accommodationDetail->id }}" class="btn btn-outline-primary btn-checkbox">
                                    {{ $accommodationDetail->name }}
                                </label>
                                <input type="{{ $accommodationDetail->input_type }}" class="form-control extra-input" name="accommodation_detail_{{ $accommodationDetail->id }}" placeholder="{{ $accommodationDetail->placeholder }}" min="{{ $accommodationDetail->input_type === 'number' ? '0' : '' }}">
                            </div>
                        @endforeach
                    </div>
                </div>

                <hr>

                <div class="form-group">
                    <label for="room_configuration" class="form-label fw-bold fs-5">
                        {{ __('accommodations.room_configuration') }}
                        <i class="fas fa-info-circle ms-2 fs-6" data-bs-toggle="tooltip" data-bs-placement="top" 
                           title="{{ __('accommodations.tooltip_room_configuration') }}"></i>
                    </label>
                    
                    <div class="room-configuration-grid">
                        @foreach($roomConfigurations ?? [] as $roomConfiguration)
                            <div class="btn-checkbox-container">
                                <input type="checkbox" name="room_config_checkboxes[]" value="{{ $roomConfiguration->id }}" id="room_config_{{ $roomConfiguration->id }}">
                                <label for="room_config_{{ $roomConfiguration->id }}" class="btn btn-outline-primary btn-checkbox">
                                    {{ $roomConfiguration->name }}
                    </label>
                                <input type="text" class="form-control extra-input" name="room_config_{{ $roomConfiguration->id }}" placeholder="{{ __('accommodations.enter_value_for') . ' ' . $roomConfiguration->name }}" min="0">
                            </div>
                        @endforeach
                    </div>
                </div>

                <div class="button-group">
                    <div class="left-buttons">
                        <button type="button" class="btn btn-secondary" id="saveDraftBtn3">
                            {{ __('accommodations.leave_save_draft') }}
                        </button>
                    </div>
                    <div class="right-buttons">
                        <div class="row-button">
                            <button type="button" class="btn btn-info" id="prevBtn3">
                                {{ __('accommodations.previous') }}
                            </button>
                            <button type="button" class="btn btn-primary" id="nextBtn3">
                                {{ __('accommodations.next') }}
                            </button>
                        </div>
                        <button type="submit" class="btn btn-primary" id="submitBtn3" style="display: none;">
                            {{ __('accommodations.submit_publish') }}
                        </button>
                    </div>
                </div>
            </div>

            <!-- Step 4: Distances -->
            <div class="step" id="step4">
                <h5>{{ __('accommodations.distances_title') }}</h5>

                <div class="form-group">
                    <label class="form-label fw-bold fs-5">
                        {{ __('accommodations.distances') }}
                        <i class="fas fa-info-circle ms-2 fs-6" data-bs-toggle="tooltip" data-bs-placement="top" 
                           title="{{ __('accommodations.tooltip_distances') }}"></i>
                    </label>
                    <div class="row">
                        <div class="col-md-4">
                            <div class="form-group mb-3">
                                <label for="distance_to_water" class="form-label">{{ __('accommodations.distance_to_water') }}</label>
                                <input type="text" class="form-control" id="distance_to_water" name="distance_to_water" value="{{ $formData['distance_to_water'] ?? '' }}" placeholder="{{ __('accommodations.distance_to_water_placeholder') }}">
                            </div>
                            </div>
                        <div class="col-md-4">
                            <div class="form-group mb-3">
                                <label for="distance_to_boat_mooring" class="form-label">{{ __('accommodations.distance_to_boat_mooring') }}</label>
                                <input type="text" class="form-control" id="distance_to_boat_mooring" name="distance_to_boat_mooring" value="{{ $formData['distance_to_boat_mooring'] ?? '' }}" placeholder="{{ __('accommodations.distance_to_boat_mooring_placeholder') }}">
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group mb-3">
                                <label for="distance_to_parking_lot" class="form-label">{{ __('accommodations.distance_to_parking_lot') }}</label>
                                <input type="text" class="form-control" id="distance_to_parking_lot" name="distance_to_parking_lot" value="{{ $formData['distance_to_parking_lot'] ?? '' }}" placeholder="{{ __('accommodations.distance_to_parking_lot_placeholder') }}">
                            </div>
                        </div>
                    </div>
                </div>

                <div class="button-group">
                    <div class="left-buttons">
                        <button type="button" class="btn btn-secondary" id="saveDraftBtn4">
                            {{ __('accommodations.leave_save_draft') }}
                        </button>
                    </div>
                    <div class="right-buttons">
                        <div class="row-button">
                            <button type="button" class="btn btn-info" id="prevBtn4">
                                {{ __('accommodations.previous') }}
                            </button>
                            <button type="button" class="btn btn-primary" id="nextBtn4">
                                {{ __('accommodations.next') }}
                            </button>
                        </div>
                        <button type="submit" class="btn btn-primary" id="submitBtn4" style="display: none;">
                            {{ __('accommodations.submit_publish') }}
                        </button>
                    </div>
                </div>
            </div>

            <!-- Step 5: Pricing -->
            <div class="step" id="step5">
                <h5>{{ __('accommodations.pricing_title') }}</h5>

                <!-- Currency Selection (moved to top) -->
                <div class="form-group mb-4">
                    <label for="currency" class="form-label fw-bold fs-5">
                        {{ __('accommodations.currency') }}
                        <i class="fas fa-info-circle ms-2 fs-6" data-bs-toggle="tooltip" data-bs-placement="top" 
                           title="Set different prices based on number of guests. Each row represents the total price for that number of persons."></i>
                    </label>
                    
                    <div class="alert alert-info mb-3">
                        <i class="fas fa-info-circle me-2"></i>
                        <small>Add pricing tiers for different guest counts. Click "Add Pricing Tier" to add prices for 1 person, 2 persons, etc. Each row shows the <strong>total price</strong> for that number of guests.</small>
                    </div>

                    <div id="per-person-pricing-container">
                        <!-- Dynamic rows will be added here -->
                    </div>

                    <button type="button" class="btn btn-success btn-sm mt-2" id="add-person-pricing-btn">
                        <i class="fas fa-plus"></i> Add Pricing Tier
                    </button>
                </div>

                <hr>

                <!-- Per Person Pricing Section -->
                <div class="form-group">
                    <label class="form-label fw-bold fs-5">
                        Per Person Pricing (Optional)
                        <i class="fas fa-info-circle ms-2 fs-6" data-bs-toggle="tooltip" data-bs-placement="top" 
                           title="Set different prices based on number of guests. Each row represents the total price for that number of persons."></i>
                    </label>
                    
                    <div class="alert alert-info mb-3">
                        <i class="fas fa-info-circle me-2"></i>
                        <small>Add pricing tiers for different guest counts. Click "Add Pricing Tier" to add prices for 1 person, 2 persons, etc. Each row shows the <strong>total price</strong> for that number of guests.</small>
                    </div>

                    <div id="per-person-pricing-container">
                        <!-- Dynamic rows will be added here -->
                    </div>

                    <button type="button" class="btn btn-success btn-sm mt-2" id="add-person-pricing-btn">
                        <i class="fas fa-plus"></i> Add Pricing Tier
                    </button>
                </div>

                <div class="button-group">
                    <div class="left-buttons">
                        <button type="button" class="btn btn-secondary" id="saveDraftBtn5">
                            {{ __('accommodations.leave_save_draft') }}
                        </button>
                    </div>
                    <div class="right-buttons">
                        <div class="row-button">
                            <button type="button" class="btn btn-info" id="prevBtn5">
                                {{ __('accommodations.previous') }}
                            </button>
                            <button type="button" class="btn btn-primary" id="nextBtn5">
                                {{ __('accommodations.next') }}
                            </button>
                        </div>
                        <button type="submit" class="btn btn-primary" id="submitBtn5" style="display: none;">
                            {{ __('accommodations.submit_publish') }}
                        </button>
                    </div>
                </div>
            </div>

            <!-- Step 6: Facilities & Policies -->
            <div class="step" id="step6">
                <h5>{{ __('accommodations.sections.policies') }}</h5>

                <!-- Facilities Section (Moved here and made smaller) -->
                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="facilities" class="form-label fw-bold fs-6">
                                {{ __('accommodations.facilities') }}
                                <i class="fas fa-info-circle ms-2 fs-6" data-bs-toggle="tooltip" data-bs-placement="top" 
                                   title="{{ __('accommodations.tooltip_facilities') }}"></i>
                            </label>
                            <input type="text" class="form-control form-control-sm" name="facilities" id="facilities" data-role="tagsinput" placeholder="{{ __('accommodations.add_facilities') }}" data-bs-toggle="tooltip" title="{{ __('accommodations.tooltip_add_facilities') }}">
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="kitchen_equipment" class="form-label fw-bold fs-6">
                                {{ __('accommodations.kitchen_equipment') }}
                                <i class="fas fa-info-circle ms-2 fs-6" data-bs-toggle="tooltip" data-bs-placement="top" 
                                   title="{{ __('accommodations.tooltip_kitchen_equipment') }}"></i>
                            </label>
                            <input type="text" class="form-control form-control-sm" name="kitchen_equipment" id="kitchen_equipment" data-role="tagsinput" placeholder="{{ __('accommodations.add_kitchen_equipment') }}" data-bs-toggle="tooltip" title="{{ __('accommodations.tooltip_add_kitchen_equipment') }}">
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="bathroom_amenities" class="form-label fw-bold fs-6">
                                {{ __('accommodations.bathroom_amenities') }}
                                <i class="fas fa-info-circle ms-2 fs-6" data-bs-toggle="tooltip" data-bs-placement="top" 
                                   title="{{ __('accommodations.tooltip_bathroom_amenities') }}"></i>
                            </label>
                            <input type="text" class="form-control form-control-sm" name="bathroom_amenities" id="bathroom_amenities" data-role="tagsinput" placeholder="{{ __('accommodations.add_bathroom_amenities') }}" data-bs-toggle="tooltip" title="{{ __('accommodations.tooltip_add_bathroom_amenities') }}">
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="policies" class="form-label fw-bold fs-6">
                                {{ __('accommodations.policies') }}
                                <i class="fas fa-info-circle ms-2 fs-6" data-bs-toggle="tooltip" data-bs-placement="top" 
                                   title="{{ __('accommodations.tooltip_policies') }}"></i>
                            </label>
                            <input type="text" class="form-control form-control-sm" name="policies" id="policies" data-role="tagsinput" placeholder="{{ __('accommodations.add_policies') }}" data-bs-toggle="tooltip" title="{{ __('accommodations.tooltip_add_policies') }}">
                        </div>
                    </div>
                </div>

                <hr>
                    
                <hr>

                <div class="form-group">
                    <label for="rental_conditions" class="form-label fw-bold fs-5">
                        {{ __('accommodations.rental_conditions') }}
                        <i class="fas fa-info-circle ms-2 fs-6" data-bs-toggle="tooltip" data-bs-placement="top" 
                           title="{{ __('accommodations.tooltip_rental_conditions') }}"></i>
                    </label>
                    
                    <div class="accommodation-details-grid">
                        @foreach($accommodationRentalConditions ?? [] as $rentalCondition)
                            <div class="btn-checkbox-container">
                                <input type="checkbox" name="rental_condition_checkboxes[]" value="{{ $rentalCondition->id }}" id="rental_condition_{{ $rentalCondition->id }}">
                                <label for="rental_condition_{{ $rentalCondition->id }}" class="btn btn-outline-primary btn-checkbox">
                                    {{ $rentalCondition->name }}
                                </label>
                                <input type="{{ $rentalCondition->input_type }}" class="form-control extra-input" name="rental_condition_{{ $rentalCondition->id }}" placeholder="{{ $rentalCondition->placeholder }}" min="{{ $rentalCondition->input_type === 'number' ? '0' : '' }}">
                            </div>
                        @endforeach
                    </div>
                </div>

                <hr>

                <div class="form-group">
                    <label for="extras" class="form-label fw-bold fs-5">
                        {{ __('accommodations.extras') }}
                        <i class="fas fa-info-circle ms-2 fs-6" data-bs-toggle="tooltip" data-bs-placement="top" 
                           title="{{ __('accommodations.tooltip_extras') }}"></i>
                    </label>
                    <input type="text" class="form-control" name="extras" id="extras" data-role="tagsinput" placeholder="{{ __('accommodations.add_extras') }}" data-bs-toggle="tooltip" title="{{ __('accommodations.tooltip_add_extras') }}">
                </div>

                <hr>

                <div class="form-group">
                    <label for="inclusives" class="form-label fw-bold fs-5">
                        {{ __('accommodations.inclusives') }}
                        <i class="fas fa-info-circle ms-2 fs-6" data-bs-toggle="tooltip" data-bs-placement="top" 
                           title="{{ __('accommodations.tooltip_inclusives') }}"></i>
                    </label>
                    <input type="text" class="form-control" name="inclusives" id="inclusives" data-role="tagsinput" placeholder="{{ __('accommodations.add_inclusives') }}" data-bs-toggle="tooltip" title="{{ __('accommodations.tooltip_add_inclusives') }}">
                </div>


                <div class="button-group">
                    <div class="left-buttons">
                        <button type="button" class="btn btn-secondary" id="saveDraftBtn6">
                            {{ __('accommodations.leave_save_draft') }}
                        </button>
                    </div>
                    <div class="right-buttons">
                        <div class="row-button">
                            <button type="button" class="btn btn-info" id="prevBtn6">
                                {{ __('accommodations.previous') }}
                            </button>
                            <div></div>
                        </div>
                        <button type="submit" class="btn btn-primary" id="submitBtn6" onclick="document.getElementById('is_draft').value = '0';">
                            {{ __('accommodations.submit_publish') }}
                        </button>
                    </div>
                </div>
            </div>
        </form>
    </div>
</div>

@include('components.accommodation-form-scripts')
