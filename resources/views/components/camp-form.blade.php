{{-- Styles are now in SCSS --}}
<div id="camp-form" class="card">
    <div class="card-body">
        
        <div class="step-wrapper">
            <div class="step-buttons">
                <button type="button" class="step-button active" data-step="1">
                    <i class="fas fa-images"></i>
                </button>
                <button type="button" class="step-button" data-step="2">
                    <i class="fas fa-campground"></i>
                </button>
                <button type="button" class="step-button" data-step="3">
                    <i class="fas fa-list-alt"></i>
                </button>
                <button type="button" class="step-button" data-step="4">
                    <i class="fas fa-fish"></i>
                </button>
                <button type="button" class="step-button" data-step="5">
                    <i class="fas fa-home"></i>
                </button>
                <button type="button" class="step-button" data-step="6">
                    <i class="fas fa-ship"></i>
                </button>
                <button type="button" class="step-button" data-step="7">
                    <i class="fas fa-anchor"></i>
                </button>
            </div>

            <div class="step-line"></div>
        </div>
        <div id="error-container" class="alert alert-danger" style="display: none;"></div>

        <form action="{{ $formAction ?? (isset($formData['id']) && $formData['id'] ? route('admin.camps.update', $formData['id']) : route('admin.camps.store')) }}" method="POST" id="campForm" enctype="multipart/form-data">
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

            <input type="hidden" name="target_redirect" id="target_redirect" value="{{ $targetRedirect ?? route('admin.camps.index') }}">
            <input type="hidden" name="is_update" id="is_update" value="{{ $formData['is_update'] ?? 0 }}">
            <input type="hidden" name="is_draft" id="is_draft" value="{{ isset($formData['status']) && $formData['status'] == 'draft' ? 1 : 0 }}">
            <input type="hidden" name="camp_id" id="camp_id" value="{{ $formData['id'] ?? 0 }}">
            <input type="hidden" name="thumbnail_path" id="thumbnail_path" value="{{ $formData['thumbnail_path'] ?? '' }}">
            <input type="hidden" name="existing_images" id="existing_images" value="{{ isset($formData['gallery_images']) && is_array($formData['gallery_images']) ? json_encode($formData['gallery_images']) : (isset($formData['gallery_images']) ? $formData['gallery_images'] : '') }}">
            <input type="hidden" name="user_id" id="user_id" value="{{ $formData['user_id'] ?? auth()->id() }}">
            <input type="hidden" name="status" id="status" value="{{ $formData['status'] ?? 'active' }}">
            <input type="hidden" id="image_list" name="image_list">

            <!-- Step 1: Gallery, Location and Title -->
            <div class="step active" id="step1">
                <h5>{{ __('camps.upload_images_title') }}</h5>

                <label for="title_image" class="form-label fw-bold fs-5">
                    {{ __('camps.upload_image') }}
                    <i class="fas fa-info-circle ms-2 fs-6" data-bs-toggle="tooltip" data-bs-placement="top" 
                       title="{{ __('camps.tooltip_upload_image') }}"></i>
                </label>
                <div class="card mb-4">
                    <div class="card-body">
                        <div class="file-upload-wrapper">
                            <input id="title_image" name="title_image[]" type="file" multiple />
                            <input id="cropped_image" name="cropped_image[]" type="file" multiple hidden/>
                            <label for="title_image" class="file-upload-btn">{{ __('camps.choose_files') }}</label>
                        </div>
                    </div>

                    <div class="image-area" id="imagePreviewContainer"></div>
                    <input type="hidden" name="primaryImage" id="primaryImageInput">
                    <div id="croppedImagesContainer"></div>
                </div>

                <hr>

                <div class="form-group">
                    <label for="location" class="form-label fw-bold fs-5">
                        {{ __('camps.location') }}
                        <i class="fas fa-info-circle ms-2 fs-6" data-bs-toggle="tooltip" data-bs-placement="top" 
                           title="{{ __('camps.tooltip_location') }}"></i>
                    </label>
                    <input type="search" class="form-control" id="location" name="location" value="{{ $formData['location'] ?? '' }}" placeholder="{{ __('camps.location_placeholder') }}">
                    <input type="hidden" name="latitude" id="latitude" value="{{ $formData['lat'] ?? '' }}">
                    <input type="hidden" name="longitude" id="longitude" value="{{ $formData['lng'] ?? '' }}">
                    <input type="hidden" name="country" id="country" value="{{ $formData['country'] ?? '' }}">
                    <input type="hidden" name="city" id="city" value="{{ $formData['city'] ?? '' }}">
                    <input type="hidden" name="region" id="region" value="{{ $formData['region'] ?? '' }}">
                </div>

                <hr>

                <div class="form-group">
                    <label for="title" class="form-label fw-bold fs-5">
                        {{ __('camps.title') }}
                        <i class="fas fa-info-circle ms-2 fs-6" data-bs-toggle="tooltip" data-bs-placement="top" title="{{ __('camps.tooltip_title') }}"></i>
                    </label>
                    <input type="text" class="form-control" id="title" name="title" value="{{ $formData['title'] ?? '' }}" placeholder="{{ __('camps.enter_catchy_title') }}">
                </div>

                <div class="button-group">
                    <div class="left-buttons">
                        {{-- <button type="button" class="btn btn-secondary" id="saveDraftBtn1">
                            {{ __('camps.leave_save_draft') }}
                        </button> --}}
                    </div>
                    <div class="right-buttons">
                        <div class="row-button">
                            <div></div>
                            <button type="button" class="btn btn-primary" id="nextBtn1">
                                {{ __('camps.next') }}
                            </button>
                        </div>
                        <button type="submit" class="btn btn-primary" id="submitBtn1" style="display: none;">
                            {{ __('camps.submit_publish') }}
                        </button>
                    </div>
                </div>
            </div>

            <!-- Step 2: Camp Descriptions and Distances -->
            <div class="step" id="step2">
                <h5>{{ __('camps.camp_descriptions_title') }}</h5>

                <div class="form-group">
                    <label for="description_camp" class="form-label fw-bold fs-5">
                        {{ __('camps.description_camp') }}
                        <i class="fas fa-info-circle ms-2 fs-6" data-bs-toggle="tooltip" data-bs-placement="top" 
                           title="{{ __('camps.tooltip_description_camp') }}"></i>
                    </label>
                    <textarea class="form-control" id="description_camp" name="description_camp" rows="5" placeholder="{{ __('camps.describe_your_camp') }}">{{ $formData['description_camp'] ?? '' }}</textarea>
                </div>

                <hr>

                <div class="form-group">
                    <label for="description_area" class="form-label fw-bold fs-5">
                        {{ __('camps.description_area') }}
                        <i class="fas fa-info-circle ms-2 fs-6" data-bs-toggle="tooltip" data-bs-placement="top" 
                           title="{{ __('camps.tooltip_description_area') }}"></i>
                    </label>
                    <textarea class="form-control" id="description_area" name="description_area" rows="5" placeholder="{{ __('camps.describe_area') }}">{{ $formData['description_area'] ?? '' }}</textarea>
                </div>

                <hr>

                <div class="form-group">
                    <label for="description_fishing" class="form-label fw-bold fs-5">
                        {{ __('camps.description_fishing') }}
                        <i class="fas fa-info-circle ms-2 fs-6" data-bs-toggle="tooltip" data-bs-placement="top" 
                           title="{{ __('camps.tooltip_description_fishing') }}"></i>
                    </label>
                    <textarea class="form-control" id="description_fishing" name="description_fishing" rows="5" placeholder="{{ __('camps.describe_fishing') }}">{{ $formData['description_fishing'] ?? '' }}</textarea>
                </div>

                <hr>

                <h5>{{ __('camps.distances_title') }}</h5>
                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group mb-3">
                            <label for="distance_to_store" class="form-label">{{ __('camps.distance_to_store') }}</label>
                            <input type="text" class="form-control" id="distance_to_store" name="distance_to_store" value="{{ $formData['distance_to_store'] ?? '' }}" placeholder="{{ __('camps.distance_to_store_placeholder') }}">
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group mb-3">
                            <label for="distance_to_nearest_town" class="form-label">{{ __('camps.distance_to_nearest_town') }}</label>
                            <input type="text" class="form-control" id="distance_to_nearest_town" name="distance_to_nearest_town" value="{{ $formData['distance_to_nearest_town'] ?? '' }}" placeholder="{{ __('camps.distance_to_nearest_town_placeholder') }}">
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group mb-3">
                            <label for="distance_to_airport" class="form-label">{{ __('camps.distance_to_airport') }}</label>
                            <input type="text" class="form-control" id="distance_to_airport" name="distance_to_airport" value="{{ $formData['distance_to_airport'] ?? '' }}" placeholder="{{ __('camps.distance_to_airport_placeholder') }}">
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group mb-3">
                            <label for="distance_to_ferry_port" class="form-label">{{ __('camps.distance_to_ferry_port') }}</label>
                            <input type="text" class="form-control" id="distance_to_ferry_port" name="distance_to_ferry_port" value="{{ $formData['distance_to_ferry_port'] ?? '' }}" placeholder="{{ __('camps.distance_to_ferry_port_placeholder') }}">
                        </div>
                    </div>
                </div>

                <div class="button-group">
                    <div class="left-buttons">
                        <button type="button" class="btn btn-secondary" id="saveDraftBtn2">
                            {{ __('camps.leave_save_draft') }}
                        </button>
                    </div>
                    <div class="right-buttons">
                        <div class="row-button">
                            <button type="button" class="btn btn-info" id="prevBtn2">
                                {{ __('camps.previous') }}
                            </button>
                            <button type="button" class="btn btn-primary" id="nextBtn2">
                                {{ __('camps.next') }}
                            </button>
                        </div>
                        <button type="submit" class="btn btn-primary" id="submitBtn2" style="display: none;">
                            {{ __('camps.submit_publish') }}
                        </button>
                    </div>
                </div>
            </div>

            <!-- Step 3: Camp Facilities and Rental Conditions -->
            <div class="step" id="step3">
                <h5>{{ __('camps.camp_facilities_title') }}</h5>

                <div class="form-group">
                    <label for="camp_facilities" class="form-label fw-bold fs-5">
                        {{ __('camps.camp_facilities') }}
                        <i class="fas fa-info-circle ms-2 fs-6" data-bs-toggle="tooltip" data-bs-placement="top" 
                           title="{{ __('camps.tooltip_camp_facilities') }}"></i>
                    </label>
                    <input type="text" class="form-control" name="camp_facilities" id="camp_facilities" placeholder="{{ __('camps.add_camp_facilities') }}" data-bs-toggle="tooltip" title="{{ __('camps.tooltip_add_camp_facilities') }}">
                </div>

                <hr>

                <h5>{{ __('camps.rental_conditions_title') }}</h5>

                <div class="form-group">
                    <label for="policies_regulations" class="form-label fw-bold fs-5">
                        {{ __('camps.policies_regulations') }}
                        <i class="fas fa-info-circle ms-2 fs-6" data-bs-toggle="tooltip" data-bs-placement="top" 
                           title="{{ __('camps.tooltip_policies_regulations') }}"></i>
                    </label>
                    <textarea class="form-control" id="policies_regulations" name="policies_regulations" rows="5" placeholder="{{ __('camps.enter_policies_regulations') }}">{{ $formData['policies_regulations'] ?? '' }}</textarea>
                </div>

                <div class="button-group">
                    <div class="left-buttons">
                        <button type="button" class="btn btn-secondary" id="saveDraftBtn3">
                            {{ __('camps.leave_save_draft') }}
                        </button>
                    </div>
                    <div class="right-buttons">
                        <div class="row-button">
                            <button type="button" class="btn btn-info" id="prevBtn3">
                                {{ __('camps.previous') }}
                            </button>
                            <button type="button" class="btn btn-primary" id="nextBtn3">
                                {{ __('camps.next') }}
                            </button>
                        </div>
                        <button type="submit" class="btn btn-primary" id="submitBtn3" style="display: none;">
                            {{ __('camps.submit_publish') }}
                        </button>
                    </div>
                </div>
            </div>

            <!-- Step 4: Target Fish and Travel Information -->
            <div class="step" id="step4">
                <h5>{{ __('camps.target_fish_title') }}</h5>

                <div class="form-group">
                    <label for="target_fish" class="form-label fw-bold fs-5">
                        {{ __('camps.target_fish') }}
                        <i class="fas fa-info-circle ms-2 fs-6" data-bs-toggle="tooltip" data-bs-placement="top" 
                           title="{{ __('camps.tooltip_target_fish') }}"></i>
                    </label>
                    <input type="text" class="form-control" name="target_fish" id="target_fish" placeholder="{{ __('camps.add_target_fish') }}">
                </div>

                <hr>

                <div class="form-group">
                    <label for="best_travel_times" class="form-label fw-bold fs-5">
                        {{ __('camps.best_travel_times') }}
                        <i class="fas fa-info-circle ms-2 fs-6" data-bs-toggle="tooltip" data-bs-placement="top" 
                           title="{{ __('camps.tooltip_best_travel_times') }}"></i>
                    </label>
                    <textarea class="form-control" id="best_travel_times" name="best_travel_times" rows="3" placeholder="{{ __('camps.enter_best_travel_times') }}">{{ $formData['best_travel_times'] ?? '' }}</textarea>
                </div>

                <hr>

                <div class="form-group">
                    <label for="travel_information" class="form-label fw-bold fs-5">
                        {{ __('camps.travel_information') }}
                        <i class="fas fa-info-circle ms-2 fs-6" data-bs-toggle="tooltip" data-bs-placement="top" 
                           title="{{ __('camps.tooltip_travel_information') }}"></i>
                    </label>
                    <textarea class="form-control" id="travel_information" name="travel_information" rows="3" placeholder="{{ __('camps.enter_travel_information') }}">{{ $formData['travel_information'] ?? '' }}</textarea>
                </div>

                <hr>

                <div class="form-group">
                    <label for="extras" class="form-label fw-bold fs-5">
                        {{ __('camps.extras') }}
                        <i class="fas fa-info-circle ms-2 fs-6" data-bs-toggle="tooltip" data-bs-placement="top" 
                           title="{{ __('camps.tooltip_extras') }}"></i>
                    </label>
                    <input type="text" class="form-control" name="extras" id="extras" placeholder="{{ __('camps.add_extras') }}">
                </div>

                <div class="button-group">
                    <div class="left-buttons">
                        <button type="button" class="btn btn-secondary" id="saveDraftBtn4">
                            {{ __('camps.leave_save_draft') }}
                        </button>
                    </div>
                    <div class="right-buttons">
                        <div class="row-button">
                            <button type="button" class="btn btn-info" id="prevBtn4">
                                {{ __('camps.previous') }}
                            </button>
                            <button type="button" class="btn btn-primary" id="nextBtn4">
                                {{ __('camps.next') }}
                            </button>
                        </div>
                        <button type="submit" class="btn btn-primary" id="submitBtn4" style="display: none;">
                            {{ __('camps.submit_publish') }}
                        </button>
                    </div>
                </div>
            </div>

            <!-- Step 5: Accommodations -->
            <div class="step" id="step5">
                <h5>{{ __('camps.accommodations_title') }}</h5>

                <div class="form-group">
                    <label for="accommodations" class="form-label fw-bold fs-5">
                        {{ __('camps.accommodations') }}
                        <i class="fas fa-info-circle ms-2 fs-6" data-bs-toggle="tooltip" data-bs-placement="top" 
                           title="{{ __('camps.tooltip_accommodations') }}"></i>
                    </label>
                    <select class="form-control" name="accommodations[]" id="accommodations" multiple>
                        @foreach($accommodations ?? [] as $accommodation)
                            <option value="{{ $accommodation->id }}">{{ $accommodation->title }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="button-group">
                    <div class="left-buttons">
                        <button type="button" class="btn btn-secondary" id="saveDraftBtn5">
                            {{ __('camps.leave_save_draft') }}
                        </button>
                    </div>
                    <div class="right-buttons">
                        <div class="row-button">
                            <button type="button" class="btn btn-info" id="prevBtn5">
                                {{ __('camps.previous') }}
                            </button>
                            <button type="button" class="btn btn-primary" id="nextBtn5">
                                {{ __('camps.next') }}
                            </button>
                        </div>
                        <button type="submit" class="btn btn-primary" id="submitBtn5" style="display: none;">
                            {{ __('camps.submit_publish') }}
                        </button>
                    </div>
                </div>
            </div>

            <!-- Step 6: Rental Boats -->
            <div class="step" id="step6">
                <h5>{{ __('camps.rental_boats_title') }}</h5>

                <div class="form-group">
                    <label for="rental_boats" class="form-label fw-bold fs-5">
                        {{ __('camps.rental_boats') }}
                        <i class="fas fa-info-circle ms-2 fs-6" data-bs-toggle="tooltip" data-bs-placement="top" 
                           title="{{ __('camps.tooltip_rental_boats') }}"></i>
                    </label>
                    <select class="form-control" name="rental_boats[]" id="rental_boats" multiple>
                        @foreach($rentalBoats ?? [] as $rentalBoat)
                            <option value="{{ $rentalBoat->id }}">{{ $rentalBoat->title }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="button-group">
                    <div class="left-buttons">
                        <button type="button" class="btn btn-secondary" id="saveDraftBtn6">
                            {{ __('camps.leave_save_draft') }}
                        </button>
                    </div>
                    <div class="right-buttons">
                        <div class="row-button">
                            <button type="button" class="btn btn-info" id="prevBtn6">
                                {{ __('camps.previous') }}
                            </button>
                            <button type="button" class="btn btn-primary" id="nextBtn6">
                                {{ __('camps.next') }}
                            </button>
                        </div>
                        <button type="submit" class="btn btn-primary" id="submitBtn6" style="display: none;">
                            {{ __('camps.submit_publish') }}
                        </button>
                    </div>
                </div>
            </div>

            <!-- Step 7: Guidings -->
            <div class="step" id="step7">
                <h5>{{ __('camps.guidings_title') }}</h5>

                <div class="form-group">
                    <label for="guidings" class="form-label fw-bold fs-5">
                        {{ __('camps.guidings') }}
                        <i class="fas fa-info-circle ms-2 fs-6" data-bs-toggle="tooltip" data-bs-placement="top" 
                           title="{{ __('camps.tooltip_guidings') }}"></i>
                    </label>
                    <select class="form-control" name="guidings[]" id="guidings" multiple>
                        @foreach($guidings ?? [] as $guiding)
                            <option value="{{ $guiding->id }}">{{ $guiding->title }}</option>
                        @endforeach
                    </select>
                </div>

                <!-- Selected Guidings Cards Display -->
                <div class="form-group" id="selected-guidings-container" style="display: none;">
                    <label class="form-label fw-bold fs-5">{{ __('camps.selected_guidings') }}</label>
                    <div id="selected-guidings-cards" class="row">
                        <!-- Selected guiding cards will be displayed here -->
                    </div>
                </div>

                <div class="button-group">
                    <div class="left-buttons">
                        <button type="button" class="btn btn-secondary" id="saveDraftBtn7">
                            {{ __('camps.leave_save_draft') }}
                        </button>
                    </div>
                    <div class="right-buttons">
                        <div class="row-button">
                            <button type="button" class="btn btn-info" id="prevBtn7">
                                {{ __('camps.previous') }}
                            </button>
                            <div></div>
                        </div>
                        <button type="submit" class="btn btn-primary" id="submitBtn7" onclick="document.getElementById('is_draft').value = '0';">
                            {{ __('camps.submit_publish') }}
                        </button>
                    </div>
                </div>
            </div>
        </form>
    </div>
</div>
@include('components.camp-form-scripts')
