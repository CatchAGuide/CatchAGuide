@include('components.rental-boat-form-styles')
<div id="rental-boat-form" class="card">
    <div class="card-body">
        
        <div class="step-wrapper">
            <div class="step-buttons">
                <div class="step-button active" data-step="1">
                    <i class="fas fa-images"></i>
                </div>
                <div class="step-button" data-step="2">
                    <i class="fas fa-ship"></i>
                </div>
                <div class="step-button" data-step="3">
                    <i class="fas fa-info-circle"></i>
                </div>
                <div class="step-button" data-step="4">
                    <i class="fas fa-list-alt"></i>
                </div>
                <div class="step-button" data-step="5">
                    <i class="fas fa-dollar-sign"></i>
                </div>
                <div class="step-button" data-step="6">
                    <i class="fas fa-calendar-alt"></i>
                </div>
            </div>

            <div class="step-line"></div>
        </div>
        <div id="error-container" class="alert alert-danger" style="display: none;"></div>

        <form action="{{ $formAction ?? route('rental-boats.store') }}" method="POST" id="rentalBoatForm" enctype="multipart/form-data">
            @csrf
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

            <input type="hidden" name="target_redirect" id="target_redirect" value="{{ $targetRedirect ?? route('rental-boats.index') }}">
            <input type="hidden" name="is_update" id="is_update" value="{{ $formData['is_update'] ?? 0 }}">
            <input type="hidden" name="is_draft" id="is_draft" value="{{ isset($formData['status']) && $formData['status'] == 'draft' ? 1 : 0 }}">
            <input type="hidden" name="rental_boat_id" id="rental_boat_id" value="{{ $formData['id'] ?? 0 }}">
            <input type="hidden" name="thumbnail_path" id="thumbnail_path" value="{{ $formData['thumbnail_path'] ?? '' }}">
            <input type="hidden" name="existing_images" id="existing_images" value="{{ $formData['gallery_images'] ?? '' }}">
            <input type="hidden" name="user_id" id="user_id" value="{{ $formData['user_id'] ?? auth()->id() }}">
            <input type="hidden" id="image_list" name="image_list">

            <!-- Step 1: Images and Basic Info -->
            <div class="step active" id="step1">
                <h5>{{ __('rental_boats.upload_images_title') }}</h5>

                <label for="title_image" class="form-label fw-bold fs-5">
                    {{ __('rental_boats.upload_image') }}
                    <i class="fas fa-info-circle ms-2 fs-6" data-bs-toggle="tooltip" data-bs-placement="top" 
                       title="{{ __('rental_boats.tooltip_upload_image') }}"></i>
                </label>
                <div class="card mb-4">
                    <div class="card-body">
                        <div class="file-upload-wrapper">
                            <input id="title_image" name="title_image[]" type="file" multiple />
                            <input id="cropped_image" name="cropped_image[]" type="file" multiple hidden/>
                            <label for="title_image" class="file-upload-btn">{{ __('rental_boats.choose_files') }}</label>
                        </div>
                        <div id="croppedImagesContainer"></div>
                    </div>

                    <div class="image-area" id="imagePreviewContainer"></div>
                    <input type="hidden" name="primaryImage" id="primaryImageInput">
                </div>

                <hr>

                <div class="form-group">
                    <label for="location" class="form-label fw-bold fs-5">
                        {{ __('rental_boats.location') }}
                        <i class="fas fa-info-circle ms-2 fs-6" data-bs-toggle="tooltip" data-bs-placement="top" 
                           title="{{ __('rental_boats.tooltip_location') }}"></i>
                    </label>
                    <input type="search" class="form-control" id="location" name="location" value="{{ $formData['location'] ?? '' }}" placeholder="{{ __('rental_boats.location_placeholder') }}">
                    <input type="hidden" name="latitude" id="latitude" value="{{ $formData['lat'] ?? '' }}">
                    <input type="hidden" name="longitude" id="longitude" value="{{ $formData['lng'] ?? '' }}">
                    <input type="hidden" name="country" id="country" value="{{ $formData['country'] ?? '' }}">
                    <input type="hidden" name="city" id="city" value="{{ $formData['city'] ?? '' }}">
                    <input type="hidden" name="region" id="region" value="{{ $formData['region'] ?? '' }}">
                </div>

                <hr>

                <div class="form-group">
                    <label for="title" class="form-label fw-bold fs-5">
                        {{ __('rental_boats.title') }}
                        <i class="fas fa-info-circle ms-2 fs-6" data-bs-toggle="tooltip" data-bs-placement="top" title="{{ __('rental_boats.tooltip_title') }}"></i>
                    </label>
                    <input type="text" class="form-control" id="title" name="title" value="{{ $formData['title'] ?? '' }}" placeholder="{{ __('rental_boats.enter_catchy_title') }}">
                </div>

                <div class="button-group">
                    <div class="left-buttons">
                        {{-- <button type="button" class="btn btn-secondary" id="saveDraftBtn1">
                            {{ __('rental_boats.leave_save_draft') }}
                        </button> --}}
                    </div>
                    <div class="right-buttons">
                        <div class="row-button">
                            <div></div>
                            <button type="button" class="btn btn-primary" id="nextBtn1">
                                {{ __('rental_boats.next') }}
                            </button>
                        </div>
                        <button type="submit" class="btn btn-primary" id="submitBtn1" style="display: none;">
                            {{ __('rental_boats.submit_publish') }}
                        </button>
                    </div>
                </div>
            </div>

            <!-- Step 2: Boat Type and Description -->
            <div class="step" id="step2">
                <h5>{{ __('rental_boats.boat_type_description_title') }}</h5>

                <div class="form-group">
                    <label for="boat_type" class="form-label fw-bold fs-5">
                        {{ __('rental_boats.boat_type') }}
                        <i class="fas fa-info-circle ms-2 fs-6" data-bs-toggle="tooltip" data-bs-placement="top" 
                           title="{{ __('rental_boats.tooltip_boat_type') }}"></i>
                    </label>
                    <select class="form-control" id="boat_type" name="boat_type">
                        <option value="">{{ __('rental_boats.select_boat_type') }}</option>
                        <option value="sailboat" {{ ($formData['boat_type'] ?? '') == 'sailboat' ? 'selected' : '' }}>{{ __('rental_boats.sailboat') }}</option>
                        <option value="motorboat" {{ ($formData['boat_type'] ?? '') == 'motorboat' ? 'selected' : '' }}>{{ __('rental_boats.motorboat') }}</option>
                        <option value="yacht" {{ ($formData['boat_type'] ?? '') == 'yacht' ? 'selected' : '' }}>{{ __('rental_boats.yacht') }}</option>
                        <option value="catamaran" {{ ($formData['boat_type'] ?? '') == 'catamaran' ? 'selected' : '' }}>{{ __('rental_boats.catamaran') }}</option>
                        <option value="fishing_boat" {{ ($formData['boat_type'] ?? '') == 'fishing_boat' ? 'selected' : '' }}>{{ __('rental_boats.fishing_boat') }}</option>
                        <option value="other" {{ ($formData['boat_type'] ?? '') == 'other' ? 'selected' : '' }}>{{ __('rental_boats.other') }}</option>
                    </select>
                </div>

                <hr>

                <div class="form-group">
                    <label for="desc_of_boat" class="form-label fw-bold fs-5">
                        {{ __('rental_boats.boat_description') }}
                        <i class="fas fa-info-circle ms-2 fs-6" data-bs-toggle="tooltip" data-bs-placement="top" 
                           title="{{ __('rental_boats.tooltip_boat_description') }}"></i>
                    </label>
                    <textarea class="form-control" id="desc_of_boat" name="desc_of_boat" rows="5" placeholder="{{ __('rental_boats.describe_your_boat') }}">{{ $formData['desc_of_boat'] ?? '' }}</textarea>
                </div>

                <div class="button-group">
                    <div class="left-buttons">
                        <button type="button" class="btn btn-secondary" id="saveDraftBtn2">
                            {{ __('rental_boats.leave_save_draft') }}
                        </button>
                    </div>
                    <div class="right-buttons">
                        <div class="row-button">
                            <button type="button" class="btn btn-info" id="prevBtn2">
                                {{ __('rental_boats.previous') }}
                            </button>
                            <button type="button" class="btn btn-primary" id="nextBtn2">
                                {{ __('rental_boats.next') }}
                            </button>
                        </div>
                        <button type="submit" class="btn btn-primary" id="submitBtn2" style="display: none;">
                            {{ __('rental_boats.submit_publish') }}
                        </button>
                    </div>
                </div>
            </div>

            <!-- Step 3: Boat Information and Extras -->
            <div class="step" id="step3">
                <h5>{{ __('rental_boats.boat_information_extras') }}</h5>

                <div class="form-group">
                    <label for="boat_information" class="form-label fw-bold fs-5">
                        {{ __('rental_boats.boat_information') }}
                        <i class="fas fa-info-circle ms-2 fs-6" data-bs-toggle="tooltip" data-bs-placement="top" 
                           title="{{ __('rental_boats.tooltip_boat_information') }}"></i>
                    </label>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group mb-3">
                                <label for="length" class="form-label">{{ __('rental_boats.length') }}</label>
                                <input type="text" class="form-control" id="length" name="boat_info[length]" value="{{ $formData['boat_information']['length'] ?? '' }}" placeholder="{{ __('rental_boats.boat_length') }}">
                            </div>
                            <div class="form-group mb-3">
                                <label for="capacity" class="form-label">{{ __('rental_boats.capacity') }}</label>
                                <input type="number" class="form-control" id="capacity" name="boat_info[capacity]" value="{{ $formData['boat_information']['capacity'] ?? '' }}" placeholder="{{ __('rental_boats.max_passengers') }}" min="1" max="100" step="1">
                            </div>
                            <div class="form-group mb-3">
                                <label for="engine" class="form-label">{{ __('rental_boats.engine') }}</label>
                                <input type="text" class="form-control" id="engine" name="boat_info[engine]" value="{{ $formData['boat_information']['engine'] ?? '' }}" placeholder="{{ __('rental_boats.engine_type') }}">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group mb-3">
                                <label for="year" class="form-label">{{ __('rental_boats.year') }}</label>
                                <input type="number" class="form-control" id="year" name="boat_info[year]" value="{{ $formData['boat_information']['year'] ?? '' }}" placeholder="{{ __('rental_boats.manufacturing_year') }}" min="1900" max="2025" step="1">
                            </div>
                            <div class="form-group mb-3">
                                <label for="fuel_type" class="form-label">{{ __('rental_boats.fuel_type') }}</label>
                                <select class="form-control" id="fuel_type" name="boat_info[fuel_type]">
                                    <option value="">{{ __('rental_boats.select_fuel_type') }}</option>
                                    <option value="diesel" {{ ($formData['boat_information']['fuel_type'] ?? '') == 'diesel' ? 'selected' : '' }}>{{ __('rental_boats.diesel') }}</option>
                                    <option value="gasoline" {{ ($formData['boat_information']['fuel_type'] ?? '') == 'gasoline' ? 'selected' : '' }}>{{ __('rental_boats.gasoline') }}</option>
                                    <option value="electric" {{ ($formData['boat_information']['fuel_type'] ?? '') == 'electric' ? 'selected' : '' }}>{{ __('rental_boats.electric') }}</option>
                                </select>
                            </div>
                            <div class="form-group mb-3">
                                <label for="safety_equipment" class="form-label">{{ __('rental_boats.safety_equipment') }}</label>
                                <input type="text" class="form-control" id="safety_equipment" name="boat_info[safety_equipment]" value="{{ $formData['boat_information']['safety_equipment'] ?? '' }}" placeholder="{{ __('rental_boats.safety_equipment_list') }}">
                            </div>
                        </div>
                    </div>
                </div>

                <hr>

                <div class="form-group">
                    <label for="boat_extras" class="form-label fw-bold fs-5">
                        {{ __('rental_boats.boat_extras') }}
                        <i class="fas fa-info-circle ms-2 fs-6" data-bs-toggle="tooltip" data-bs-placement="top" 
                           title="{{ __('rental_boats.tooltip_boat_extras') }}"></i>
                    </label>
                    <input type="text" class="form-control" name="boat_extras" id="boat_extras" data-role="tagsinput" placeholder="{{ __('rental_boats.add_extras') }}" data-bs-toggle="tooltip" title="{{ __('rental_boats.tooltip_add_extras') }}">
                </div>

                <div class="button-group">
                    <div class="left-buttons">
                        <button type="button" class="btn btn-secondary" id="saveDraftBtn3">
                            {{ __('rental_boats.leave_save_draft') }}
                        </button>
                    </div>
                    <div class="right-buttons">
                        <div class="row-button">
                            <button type="button" class="btn btn-info" id="prevBtn3">
                                {{ __('rental_boats.previous') }}
                            </button>
                            <button type="button" class="btn btn-primary" id="nextBtn3">
                                {{ __('rental_boats.next') }}
                            </button>
                        </div>
                        <button type="submit" class="btn btn-primary" id="submitBtn3" style="display: none;">
                            {{ __('rental_boats.submit_publish') }}
                        </button>
                    </div>
                </div>
            </div>

            <!-- Step 4: Requirements and Additional Info -->
            <div class="step" id="step4">
                <h5>{{ __('rental_boats.requirements_additional_info') }}</h5>

                <div class="form-group">
                    <label for="requirements" class="form-label fw-bold fs-5">
                        {{ __('rental_boats.requirements') }}
                        <i class="fas fa-info-circle ms-2 fs-6" data-bs-toggle="tooltip" data-bs-placement="top" 
                           title="{{ __('rental_boats.tooltip_requirements') }}"></i>
                    </label>
                    <textarea class="form-control" id="requirements" name="requirements" rows="4" placeholder="{{ __('rental_boats.rental_requirements') }}">{{ $formData['requirements'] ?? '' }}</textarea>
                </div>

                <hr>

                <div class="form-group">
                    <label for="inclusions" class="form-label fw-bold fs-5">
                        {{ __('rental_boats.inclusions') }}
                        <i class="fas fa-info-circle ms-2 fs-6" data-bs-toggle="tooltip" data-bs-placement="top" 
                           title="{{ __('rental_boats.tooltip_inclusions') }}"></i>
                    </label>
                    <input type="text" class="form-control" name="inclusions" id="inclusions" data-role="tagsinput" placeholder="{{ __('rental_boats.inclusions_placeholder') }}">
                </div>

                <div class="button-group">
                    <div class="left-buttons">
                        <button type="button" class="btn btn-secondary" id="saveDraftBtn4">
                            {{ __('rental_boats.leave_save_draft') }}
                        </button>
                    </div>
                    <div class="right-buttons">
                        <div class="row-button">
                            <button type="button" class="btn btn-info" id="prevBtn4">
                                {{ __('rental_boats.previous') }}
                            </button>
                            <button type="button" class="btn btn-primary" id="nextBtn4">
                                {{ __('rental_boats.next') }}
                            </button>
                        </div>
                        <button type="submit" class="btn btn-primary" id="submitBtn4" style="display: none;">
                            {{ __('rental_boats.submit_publish') }}
                        </button>
                    </div>
                </div>
            </div>

            <!-- Step 5: Pricing Structure -->
            <div class="step" id="step5">
                <h5>{{ __('rental_boats.set_pricing_structure') }}</h5>

                <div class="form-group">
                    <label for="price_type" class="form-label fw-bold fs-5">
                        {{ __('rental_boats.price_type') }}
                        <i class="fas fa-info-circle ms-2 fs-6" data-bs-toggle="tooltip" data-bs-placement="top" 
                           title="{{ __('rental_boats.tooltip_price_type') }}"></i>
                    </label>
                    <div class="d-flex flex-wrap btn-group-toggle">
                        <input type="radio" name="price_type" value="per_hour" id="per_hour">
                        <label for="per_hour" class="btn btn-outline-primary m-2 flex-fill btn-checkbox" style="flex-basis: calc(33.33% - 20px);">
                            {{ __('rental_boats.per_hour') }}
                        </label>
                        
                        <input type="radio" name="price_type" value="per_day" id="per_day">
                        <label for="per_day" class="btn btn-outline-primary m-2 flex-fill btn-checkbox" style="flex-basis: calc(33.33% - 20px);">
                            {{ __('rental_boats.per_day') }}
                        </label>
                        
                        <input type="radio" name="price_type" value="per_week" id="per_week">
                        <label for="per_week" class="btn btn-outline-primary m-2 flex-fill btn-checkbox" style="flex-basis: calc(33.33% - 20px);">
                            {{ __('rental_boats.per_week') }}
                        </label>
                    </div>
                </div>

                <hr>

                <div class="form-group">
                    <label for="base_price" class="form-label fw-bold fs-5">
                        {{ __('rental_boats.base_price') }}
                        <i class="fas fa-info-circle ms-2 fs-6" data-bs-toggle="tooltip" data-bs-placement="top" 
                           title="{{ __('rental_boats.tooltip_base_price') }}"></i>
                    </label>
                    <div class="input-group">
                        <span class="input-group-text">€</span>
                        <input type="number" class="form-control" id="base_price" name="base_price" value="{{ $formData['prices']['base_price'] ?? '' }}" placeholder="0.00" step="0.01" min="0">
                    </div>
                </div>

                <hr>

                <div class="form-group">
                    <label for="pricing_extra" class="form-label fw-bold fs-5">
                        {{ __('rental_boats.extra_pricing') }}
                        <i class="fas fa-info-circle ms-2 fs-6" data-bs-toggle="tooltip" data-bs-placement="top" 
                           title="{{ __('rental_boats.tooltip_extra_pricing') }}"></i>
                        <button type="button" id="add-extra-pricing" class="btn btn-sm btn-secondary ms-2"><i class="fas fa-plus"></i></button>
                    </label>
                    <div id="extra-pricing-container"></div>
                </div>

                <div class="button-group">
                    <div class="left-buttons">
                        <button type="button" class="btn btn-secondary" id="saveDraftBtn5">
                            {{ __('rental_boats.leave_save_draft') }}
                        </button>
                    </div>
                    <div class="right-buttons">
                        <div class="row-button">
                            <button type="button" class="btn btn-info" id="prevBtn5">
                                {{ __('rental_boats.previous') }}
                            </button>
                            <button type="button" class="btn btn-primary" id="nextBtn5">
                                {{ __('rental_boats.next') }}
                            </button>
                        </div>
                        <button type="submit" class="btn btn-primary" id="submitBtn5" style="display: none;">
                            {{ __('rental_boats.submit_publish') }}
                        </button>
                    </div>
                </div>
            </div>

            <!-- Step 6: Availability and Booking Options -->
            <div class="step" id="step6">
                <h5>{{ __('rental_boats.availability_booking_options') }}</h5>

                <div class="form-group">
                    <label for="booking_advance" class="form-label fw-bold fs-5">
                        {{ __('rental_boats.booking_advance') }}
                        <i class="fas fa-info-circle ms-2 fs-6" data-bs-toggle="tooltip" data-bs-placement="top" 
                           title="{{ __('rental_boats.tooltip_booking_advance') }}"></i>
                    </label>
                    <div class="d-flex flex-wrap btn-group-toggle">
                        <input type="radio" name="booking_advance" value="same_day" id="same_day">
                        <label for="same_day" class="btn btn-outline-primary m-2 flex-fill btn-checkbox" style="flex-basis: calc(25% - 20px);">
                            {{ __('rental_boats.same_day') }}
                        </label>
                        
                        <input type="radio" name="booking_advance" value="one_day" id="one_day">
                        <label for="one_day" class="btn btn-outline-primary m-2 flex-fill btn-checkbox" style="flex-basis: calc(25% - 20px);">
                            {{ __('rental_boats.one_day') }}
                        </label>
                        
                        <input type="radio" name="booking_advance" value="three_days" id="three_days">
                        <label for="three_days" class="btn btn-outline-primary m-2 flex-fill btn-checkbox" style="flex-basis: calc(25% - 20px);">
                            {{ __('rental_boats.three_days') }}
                        </label>
                        
                        <input type="radio" name="booking_advance" value="one_week" id="one_week">
                        <label for="one_week" class="btn btn-outline-primary m-2 flex-fill btn-checkbox" style="flex-basis: calc(25% - 20px);">
                            {{ __('rental_boats.one_week') }}
                        </label>
                    </div>
                </div>

                <div class="button-group">
                    <div class="left-buttons">
                        <button type="button" class="btn btn-secondary" id="saveDraftBtn6">
                            {{ __('rental_boats.leave_save_draft') }}
                        </button>
                    </div>
                    <div class="right-buttons">
                        <div class="row-button">
                            <button type="button" class="btn btn-info" id="prevBtn6">
                                {{ __('rental_boats.previous') }}
                            </button>
                            <div></div>
                        </div>
                        <button type="submit" class="btn btn-primary" id="submitBtn6" onclick="document.getElementById('is_draft').value = '0';">
                            {{ __('rental_boats.submit_publish') }}
                        </button>
                    </div>
                </div>
            </div>
        </form>
    </div>
</div>
@include('components.rental-boat-form-scripts')
