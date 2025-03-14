@include('pages.guidings.includes.styles.multi-step-form-style')
<div id="guidings-form"  class="card">
    <div class="card-body">
        
        <div class="step-wrapper">
            <div class="step-buttons">
                <div class="step-button active" data-step="1">
                    <i class="fas fa-ship"></i>
                </div>
                <div class="step-button" data-step="2">
                    <i class="fas fa-water"></i>
                </div>
                <div class="step-button" data-step="3">
                    <i class="fas fa-anchor"></i>
                </div>
                <div class="step-button" data-step="4">
                    <i class="fas fa-file-alt"></i>
                </div>
                <div class="step-button" data-step="5">
                    <i class="fas fa-info-circle"></i>
                </div>
                <div class="step-button" data-step="6">
                    <i class="fas fa-dollar-sign"></i>
                </div>
                <div class="step-button" data-step="7">
                    <i class="fas fa-calendar-alt"></i>
                </div>
            </div>

            <div class="step-line"></div>
        </div>
        <div id="error-container" class="alert alert-danger" style="display: none;"></div>

        <form action="{{ route('profile.newguiding.store') }}" method="POST" id="newGuidingForm" enctype="multipart/form-data">
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

            <input type="hidden" name="target_redirect" id="target_redirect" value="{{ $target_redirect ?? route('profile.myguidings') }}">
            <input type="hidden" name="is_update" id="is_update" value="{{ $formData['is_update'] ?? 0 }}">
            <input type="hidden" name="guiding_id" id="guiding_id" value="{{ $formData['id'] ?? 0 }}">
            <input type="hidden" name="thumbnail_path" id="thumbnail_path" value="{{ $formData['thumbnail_path'] ?? '' }}">
            <input type="hidden" name="existing_images" id="existing_images" value="{{ $formData['gallery_images'] ?? "" }}">
            <input type="hidden" id="image_list" name="image_list">

            <!-- Step 1 -->
            <div class="step active" id="step1">
                <h5>{{ __('newguidings.upload_images_title') }}</h5>

                <label for="title_image" class="form-label fw-bold fs-5">
                    {{ __('newguidings.upload_image') }}
                    <i class="fas fa-info-circle ms-2 fs-6" data-bs-toggle="tooltip" data-bs-placement="top" 
                       title="{{ __('newguidings.tooltip_upload_image') }}"></i>
                </label>
                <div class="card mb-4">
                    <div class="card-body">
                        <div class="file-upload-wrapper">
                            <input id="title_image" name="title_image[]" type="file" multiple />
                            <input id="cropped_image" name="cropped_image[]" type="file" multiple hidden/>
                            <label for="title_image" class="file-upload-btn">{{ __('newguidings.choose_files') }}</label>
                        </div>
                        <div id="croppedImagesContainer"></div>
                    </div>

                    <div class="image-area" id="imagePreviewContainer"></div>
                    <input type="hidden" name="primaryImage" id="primaryImageInput">

                </div>

                <hr>

                <div class="form-group">
                    <label for="location" class="form-label fw-bold fs-5">
                        {{ __('newguidings.location') }}
                        <i class="fas fa-info-circle ms-2 fs-6" data-bs-toggle="tooltip" data-bs-placement="top" 
                           title="{{ __('newguidings.tooltip_location') }}"></i>
                    </label>
                    <input type="search" class="form-control" id="location" name="location" value="{{ $formData['location'] ?? '' }}" placeholder="{{ __('newguidings.location_placeholder') }}">
                    <input type="hidden" name="latitude" id="latitude" value="{{ $formData['latitude'] ?? '' }}">
                    <input type="hidden" name="longitude" id="longitude" value="{{ $formData['longitude'] ?? '' }}">
                    <input type="hidden" name="country" id="country" value="{{ $formData['country'] ?? '' }}">
                    <input type="hidden" name="city" id="city" value="{{ $formData['city'] ?? '' }}">
                    <input type="hidden" name="region" id="region" value="{{ $formData['region'] ?? '' }}">
                    <input type="hidden" name="postal_code" id="postal_code" value="{{ $formData['postal_code'] ?? '' }}">
                </div>

                <hr>

                <div class="form-group">
                    <label for="title" class="form-label fw-bold fs-5">
                        {{ __('newguidings.title') }}
                        <i class="fas fa-info-circle ms-2 fs-6" data-bs-toggle="tooltip" data-bs-placement="top" title="{{ __('newguidings.tooltip_title') }}"></i>
                    </label>
                    <input type="text" class="form-control" id="title" name="title" value="{{ $formData['title'] ?? '' }}" placeholder="{{ __('newguidings.enter_catchy_title') }}">
                </div>

                <div class="button-group">
                    <div class="left-buttons">
                        {{-- <button type="button" class="btn btn-secondary" id="saveDraftBtn1">
                            {{ __('newguidings.leave_save_draft') }}
                        </button> --}}
                    </div>
                    <div class="right-buttons">
                        <div class="row-button">
                            <div>

                            </div>
                            <button type="button" class="btn btn-primary" id="nextBtn1">
                                {{ __('newguidings.next') }}
                            </button>
                        </div>
                        <button type="submit" class="btn btn-primary" id="submitBtn1" style="display: none;">
                            {{ __('newguidings.submit_publish') }}
                        </button>
                    </div>
                </div>
            </div>

            <!-- Step 2 -->
            <div class="step" id="step2">
                <h5>{{ __('newguidings.boat_description_title') }}</h5>

                <div class="form-group">
                    <label for="type_of_fishing" class="form-label fw-bold fs-5">
                        {{ __('newguidings.type_of_fishing') }}
                        <i class="fas fa-info-circle ms-2 fs-6" data-bs-toggle="tooltip" data-bs-placement="top" 
                           title="{{ __('newguidings.tooltip_type_of_fishing') }}"></i>
                    </label>
                    <div class="row justify-content-center">
                        <div class="col-6">
                            <div class="option-card" id="boatOption" onclick="selectOption('boat')">
                                <i class="fas fa-ship option-icon"></i>
                                <p class="option-label">{{ __('newguidings.boat') }}</p>
                                <input type="radio" name="type_of_fishing_radio" value="boat" class="d-none">
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="option-card" id="shoreOption" onclick="selectOption('shore')">
                                <i class="fas fa-umbrella-beach option-icon"></i>
                                <p class="option-label">{{ __('newguidings.shore') }}</p>
                                <input type="radio" name="type_of_fishing_radio" value="shore" class="d-none">
                            </div>
                        </div>
                        <input type="hidden" name="type_of_fishing" id="type_of_fishing">
                    </div>
                </div>

                <div id="extraFields" style="display: none;">
                    <div class="form-group">
                        
                        <label for="type_of_boat" class="form-label fw-bold fs-5">
                            <span class="text-capitalize">{{ __('newguidings.type_of_boat') }}</span>
                            <i class="fas fa-info-circle ms-2 fs-6" data-bs-toggle="tooltip" data-bs-placement="top" 
                               title="{{ __('newguidings.tooltip_type_of_boat') }}"></i>
                        </label>
                        <div class="d-flex flex-wrap btn-group-toggle">
                            @foreach($guiding_boat_types as $guiding_boat_type)
                                <input type="radio" name="type_of_boat" value="{{ $guiding_boat_type['id'] }}" id="boat_type_{{ $guiding_boat_type['id'] }}">
                                <label for="boat_type_{{ $guiding_boat_type['id'] }}" class="btn btn-outline-primary m-2 flex-fill btn-checkbox" 
                                       style="flex-basis: calc(33.33% - 20px);">{{ $guiding_boat_type['value'] }}</label>
                            @endforeach
                        </div>
                    </div>

                    <hr>

                    <div class="form-group">
                        <label for="descriptions" class="form-label fw-bold fs-5">
                            {{ __('newguidings.boat_description') }}
                            <i class="fas fa-info-circle ms-2 fs-6" data-bs-toggle="tooltip" data-bs-placement="top" 
                               title="{{ __('newguidings.tooltip_boat_description') }}"></i>
                        </label>
                        <div class="form-group mb-3">
                            <label for="other_boat_info" class="form-label">
                                {{ __('newguidings.other_boat_info') }}
                            </label>
                            <textarea class="form-control" id="other_boat_info" name="other_boat_info" rows="3">{{ $formData['other_boat_info'] ?? '' }}</textarea>
                        </div>
                        <div class="btn-group-toggle">
                            @foreach($guiding_boat_descriptions as $guiding_boat_description)
                                <div class="btn-checkbox-container">
                                    <input type="checkbox" name="descriptions[]" value="{{ $guiding_boat_description['id'] }}" id="boat_description_{{ $guiding_boat_description['id'] }}">
                                    <label for="boat_description_{{ $guiding_boat_description['id'] }}" class="btn btn-outline-primary m-2 btn-checkbox">
                                        {{ $guiding_boat_description['value'] }}
                                    </label>
                                    <textarea class="form-control extra-input" name="boat_description_{{ $guiding_boat_description['id'] }}" placeholder="{{ __('guidings.Enter_value_for') . ' ' . $guiding_boat_description['value'] }}"></textarea>
                                </div>
                            @endforeach
                        </div> 
                    </div>

                    <hr>

                    <div class="form-group">
                        <label for="boat_extras" class="form-label fw-bold fs-5">
                            {{ __('newguidings.extras_boat_equipment') }}
                            <i class="fas fa-info-circle ms-2 fs-6" data-bs-toggle="tooltip" data-bs-placement="top" 
                               title="{{ __('newguidings.tooltip_boat_extras') }}"></i>
                        </label>
                        <input  class="form-control" name="boat_extras" id="boat_extras" placeholder="{{ __('newguidings.add_extras') }}" data-bs-toggle="tooltip" title="{{ __('newguidings.tooltip_add_extras') }}">
                    </div>
                </div>

                <div class="button-group">
                    <div class="left-buttons">
                        {{-- <button type="button" class="btn btn-secondary" id="saveDraftBtn2">
                            {{ __('newguidings.leave_save_draft') }}
                        </button> --}}
                    </div>
                    <div class="right-buttons">
                        <div class="row-button">
                            <button type="button" class="btn btn-info" id="prevBtn2">
                                {{ __('newguidings.previous') }}
                            </button>
                            <button type="button" class="btn btn-primary" id="nextBtn2">
                                {{ __('newguidings.next') }}
                            </button>
                        </div>
                        <button type="submit" class="btn btn-primary" id="submitBtn2" style="display: none;">
                            {{ __('newguidings.submit_publish') }}
                        </button>
                    </div>
                </div>
            </div>

            <!-- Step 3 -->
            <div class="step" id="step3">
                <h5>{{ __('newguidings.target_fish_fishing_method') }}</h5>
                
                <div class="form-group">
                    <label for="target_fish" class="form-label fw-bold fs-5">
                        {{ __('newguidings.target_fish') }}
                        <i class="fas fa-info-circle ms-2 fs-6" data-bs-toggle="tooltip" data-bs-placement="top" 
                           title="{{ __('newguidings.tooltip_target_fish') }}"></i>
                    </label>
                    <input type="text" class="form-control" name="target_fish" id="target_fish" data-role="tagsinput" placeholder="{{ __('newguidings.add_target_fish') }}">
                </div>

                <hr>
                
                <div class="form-group">
                    <label for="methods" class="form-label fw-bold fs-5">
                        {{ __('newguidings.fishing_methods') }}
                        <i class="fas fa-info-circle ms-2 fs-6" data-bs-toggle="tooltip" data-bs-placement="top" 
                           title="{{ __('newguidings.tooltip_fishing_methods') }}"></i>
                    </label>
                    <input type="text" class="form-control" name="methods" id="methods" data-role="tagsinput" placeholder="{{ __('newguidings.select_methods') }}">
                </div>
                
                <hr>
                
                <div class="form-group">
                    <label for="style_of_fishing" class="form-label fw-bold fs-5">
                        {{ __('newguidings.style_of_fishing') }}
                        <i class="fas fa-info-circle ms-2 fs-6" data-bs-toggle="tooltip" data-bs-placement="top" 
                           title="{{ __('newguidings.tooltip_style_of_fishing') }}"></i>
                    </label>
                    <div class="d-flex flex-wrap btn-group-toggle">
                        <input type="radio" name="style_of_fishing" value="1" id="active">
                        <label for="active" class="btn btn-outline-primary m-2 flex-fill btn-checkbox" style="flex-basis: calc(33.33% - 20px);">
                            {{ __('newguidings.active') }}
                        </label>
                        
                        <input type="radio" name="style_of_fishing" value="2" id="passive">
                        <label for="passive" class="btn btn-outline-primary m-2 flex-fill btn-checkbox" style="flex-basis: calc(33.33% - 20px);">
                            {{ __('newguidings.passive') }}
                        </label>
                        
                        <input type="radio" name="style_of_fishing" value="3" id="active_passive">
                        <label for="active_passive" class="btn btn-outline-primary m-2 flex-fill btn-checkbox" style="flex-basis: calc(33.33% - 20px);">
                            {{ __('newguidings.active_passive') }}
                        </label>
                    </div>
                </div>

                <hr>
                
                <div class="form-group">
                    <label for="water_types" class="form-label fw-bold fs-5">
                        {{ __('newguidings.water_types') }}
                        <i class="fas fa-info-circle ms-2 fs-6" data-bs-toggle="tooltip" data-bs-placement="top" 
                           title="{{ __('newguidings.tooltip_water_types') }}"></i>
                    </label>
                    <input type="text" class="form-control" name="water_types" id="water_types" data-role="tagsinput" placeholder="{{ __('newguidings.select_water_types') }}">
                </div>

                <hr>

                <div class="button-group">
                    <div class="left-buttons">
                        {{-- <button type="button" class="btn btn-secondary" id="saveDraftBtn3">
                            {{ __('newguidings.leave_save_draft') }}
                        </button> --}}
                    </div>
                    <div class="right-buttons">
                    <div class="row-button">
                        <button type="button" class="btn btn-primary" id="prevBtn3">
                            {{ __('newguidings.previous') }}
                        </button>
                        <button type="button" class="btn btn-primary" id="nextBtn3">
                            {{ __('newguidings.next') }}
                        </button>
                    </div>
                        <button type="submit" class="btn btn-primary" id="submitBtn3" style="display: none;">
                            {{ __('newguidings.submit_publish') }}
                        </button>
                    </div>
                </div>
            </div>

            <!-- Step 4 -->
            <div class="step" id="step4">
                <h5>{{ __('newguidings.write_detailed_description') }}</h5>
                
                <div class="form-group">
                    <label for="desc_course_of_action" class="form-label fw-bold fs-5">
                        {{ __('newguidings.course_of_action') }}
                        <i class="fas fa-info-circle ms-2 fs-6" data-bs-toggle="tooltip" data-bs-placement="top" 
                           title="{{ __('newguidings.tooltip_course_of_action') }}"></i>
                    </label>
                    <textarea name="desc_course_of_action" id="desc_course_of_action" class="form-control" placeholder="{{ __('newguidings.tell_guests_what_they_can_expect') }}">{{ $formData['desc_course_of_action'] ?? '' }}</textarea>
                </div>

                <hr>
                
                <div class="form-group">
                    <label for="desc_starting_time" class="form-label fw-bold fs-5">
                        {{ __('newguidings.starting_time') }}
                        <i class="fas fa-info-circle ms-2 fs-6" data-bs-toggle="tooltip" data-bs-placement="top" 
                           title="{{ __('newguidings.tooltip_starting_time') }}"></i>
                    </label>
                    <textarea name="desc_starting_time" id="desc_starting_time" class="form-control" placeholder="{{ __('newguidings.let_guests_know_when_you_begin') }}">{{ $formData['desc_starting_time'] ?? '' }}</textarea>
                </div>

                <hr>
                
                {{-- <div class="form-group">
                    <label for="desc_departure_time" class="form-label fw-bold fs-5">
                        {{ __('newguidings.departure_time') }}
                        <i class="fas fa-info-circle ms-2 fs-6" data-bs-toggle="tooltip" data-bs-placement="top" 
                           title="{{ __('newguidings.tooltip_departure_time') }}"></i>
                    </label>
                    <textarea name="desc_departure_time" id="desc_departure_time" class="form-control" placeholder="{{ __('newguidings.let_guests_know_about_departure_details') }}">{{ $formData['desc_departure_time'] ?? '' }}</textarea>
                </div> --}}

                <hr>
                
                <div class="form-group">
                    <label for="desc_departure_time" class="form-label fw-bold fs-5">
                        {{ __('newguidings.departure_time') }}
                        <i class="fas fa-info-circle ms-2 fs-6" data-bs-toggle="tooltip" data-bs-placement="top" 
                           title="{{ __('newguidings.tooltip_departure_time') }}"></i>
                    </label>
                    <textarea name="desc_departure_time" id="desc_departure_time" class="form-control" placeholder="{{ __('newguidings.let_guests_know_about_departure_details') }}">{{ $formData['desc_departure_time'] ?? '' }}</textarea>
                </div>

                <hr>
                
                <div class="form-group">
                    <label for="desc_meeting_point" class="form-label fw-bold fs-5">
                        {{ __('newguidings.meeting_point') }}
                        <i class="fas fa-info-circle ms-2 fs-6" data-bs-toggle="tooltip" data-bs-placement="top" 
                           title="{{ __('newguidings.tooltip_meeting_point') }}"></i>
                    </label>
                    <textarea name="desc_meeting_point" id="desc_meeting_point" class="form-control" placeholder="{{ __('newguidings.give_guests_information_about_where_they_will_meet') }}">{{ $formData['desc_meeting_point'] ?? '' }}</textarea>
                </div>

                <hr>
                
                <div class="form-group">
                    <label for="desc_tour_unique" class="form-label fw-bold fs-5">
                        {{ __('newguidings.tour_highlights') }}
                        <i class="fas fa-info-circle ms-2 fs-6" data-bs-toggle="tooltip" data-bs-placement="top" 
                           title="{{ __('newguidings.tooltip_tour_highlights') }}"></i>
                    </label>
                    <textarea name="desc_tour_unique" id="desc_tour_unique" class="form-control" placeholder="{{ __('newguidings.tell_guests_about_special_highlights') }}">{{ $formData['desc_tour_unique'] ?? '' }}</textarea>
                </div>
                
                @if(isset($formData) && $formData['is_update'] == 1)
                    {{-- <div class="form-group">
                        <label for="long_description">Overall summary of the service and what it offers</label>
                        <textarea name="long_description" id="long_description" class="form-control" placeholder="course of action. . . ." readonly style="width: 100%; height: auto; min-height: 100px;" oninput="this.style.height = ''; this.style.height = this.scrollHeight + 'px'">{{ $formData['long_description'] ?? '' }}</textarea>
                    </div>

                    <hr> --}}
                @endif

                <div class="button-group">
                    <div class="left-buttons">
                        {{-- <button type="button" class="btn btn-secondary" id="saveDraftBtn4">
                            {{ __('newguidings.leave_save_draft') }}
                        </button> --}}
                    </div>
                    <div class="right-buttons">
                    <div class="row-button">
                        <button type="button" class="btn btn-primary" id="prevBtn4">
                            {{ __('newguidings.previous') }}
                        </button>
                        <button type="button" class="btn btn-primary" id="nextBtn4">
                            {{ __('newguidings.next') }}
                        </button>
                    </div>
                        <button type="submit" class="btn btn-primary" id="submitBtn4" style="display: none;">
                            {{ __('newguidings.submit_publish') }}
                        </button>
                    </div>
                </div>
            </div>

            <!-- Step 5 -->
            <div class="step" id="step5">
                <h5>{{ __('newguidings.add_any_additional_information') }}</h5>

                <div class="form-group">
                    <label for="group" class="form-label fw-bold fs-5">
                        {{ __('newguidings.other_information') }}
                        <i class="fas fa-info-circle ms-2 fs-6" data-bs-toggle="tooltip" data-bs-placement="top" 
                           title="{{ __('newguidings.tooltip_other_information') }}"></i>
                    </label>
                    <div class="btn-group-toggle">
                        @foreach($guiding_additional_infos as $guiding_additional_info)
                            <div class="btn-checkbox-container">
                                <input type="checkbox" name="other_information[]" value="{{ $guiding_additional_info['id'] }}" id="additional_info_{{ $guiding_additional_info['id'] }}">
                                <label for="additional_info_{{ $guiding_additional_info['id'] }}" class="btn btn-outline-primary m-2 btn-checkbox">
                                    {{ $guiding_additional_info['value'] }}
                                </label>
                                <textarea class="form-control extra-input" name="other_information_{{ $guiding_additional_info['id'] }}" placeholder="{{ __('newguidings.add_a_comment_or_additional_information') }}"></textarea>
                            </div>
                        @endforeach
                    </div>
                </div>

                <hr>
                
                <div class="form-group">
                    <label for="requiements_taking_part" class="form-label fw-bold fs-5">
                        {{ __('newguidings.requirements_taking_part') }}
                        <i class="fas fa-info-circle ms-2 fs-6" data-bs-toggle="tooltip" data-bs-placement="top" 
                           title="{{ __('newguidings.tooltip_requirements') }}"></i>
                    </label>
                    <div class="btn-group-toggle">
                        @foreach($guiding_requirements as $guiding_requirement)
                            <div class="btn-checkbox-container">
                                <input type="checkbox" name="requiements_taking_part[]" value="{{ $guiding_requirement['id'] }}" id="requiements_taking_part_{{ $guiding_requirement['id'] }}">
                                <label for="requiements_taking_part_{{ $guiding_requirement['id'] }}" class="btn btn-outline-primary m-2 btn-checkbox">
                                    {{ $guiding_requirement['value'] }}
                                </label>
                                <textarea class="form-control extra-input" name="requiements_taking_part_{{ $guiding_requirement['id'] }}" placeholder="{{ __('newguidings.add_a_comment_or_additional_information') }}"></textarea>
                            </div>
                        @endforeach
                    </div>
                </div>

                <hr>
                
                <div class="form-group">
                    <label for="recommended_preparation" class="form-label fw-bold fs-5">
                        {{ __('newguidings.recommended_preparation') }}
                        <i class="fas fa-info-circle ms-2 fs-6" data-bs-toggle="tooltip" data-bs-placement="top" 
                           title="{{ __('newguidings.tooltip_recommended_preparation') }}"></i>
                    </label>
                    <div class="btn-group-toggle">
                        @foreach($guiding_recommendations as $guiding_recommendation)
                            <div class="btn-checkbox-container">
                                <input type="checkbox" name="recommended_preparation[]" value="{{ $guiding_recommendation['id'] }}" id="recommended_preparation_{{ $guiding_recommendation['id'] }}">
                                <label for="recommended_preparation_{{ $guiding_recommendation['id'] }}" class="btn btn-outline-primary m-2 btn-checkbox">
                                    {{ $guiding_recommendation['value'] }}
                                </label>
                                <textarea class="form-control extra-input" name="recommended_preparation_{{ $guiding_recommendation['id'] }}" placeholder="{{ __('newguidings.add_a_comment_or_additional_information') }}"></textarea>
                            </div>
                        @endforeach
                    </div>
                </div>

                <div class="button-group">
                    <div class="left-buttons">
                        {{-- <button type="button" class="btn btn-secondary" id="saveDraftBtn5">
                            {{ __('newguidings.leave_save_draft') }}
                        </button> --}}
                    </div>
                    <div class="right-buttons">
                    <div class="row-button">
                        <button type="button" class="btn btn-primary" id="prevBtn5">
                            {{ __('newguidings.previous') }}
                        </button>
                        <button type="button" class="btn btn-primary" id="nextBtn5">
                            {{ __('newguidings.next') }}
                        </button>
                    </div>
                        <button type="submit" class="btn btn-primary" id="submitBtn5" style="display: none;">
                            {{ __('newguidings.submit_publish') }}
                        </button>
                    </div>
                </div>
            </div>

            <!-- Step 6 -->
            <div class="step" id="step6">
                <h5>{{ __('newguidings.set_your_pricing_structure') }}</h5>
                <div class="form-group">
                    <label for="tour_type" class="form-label fw-bold fs-5">
                        {{ __('newguidings.tour_type') }}
                        <i class="fas fa-info-circle ms-2 fs-6" data-bs-toggle="tooltip" data-bs-placement="top" 
                           title="{{ __('newguidings.tooltip_tour_type') }}"></i>
                    </label>
                    <div class="d-flex flex-wrap btn-group-toggle">
                        <input type="radio" name="tour_type" value="private" id="private">
                        <label for="private" class="btn btn-outline-primary m-2 flex-fill btn-checkbox" style="flex-basis: calc(33.33% - 20px);">
                            {{ __('newguidings.private_tours_only') }}
                        </label>
                        
                        <input type="radio" name="tour_type" value="shared" id="shared">
                        <label for="shared" class="btn btn-outline-primary m-2 flex-fill btn-checkbox" style="flex-basis: calc(33.33% - 20px);">
                            {{ __('newguidings.shared_tours_possible') }}
                        </label>
                    </div>
                </div>

                <hr>
                
                <div class="form-group">
                    <label for="duration" class="form-label fw-bold fs-5">
                        {{ __('newguidings.duration_type') }}
                        <i class="fas fa-info-circle ms-2 fs-6" data-bs-toggle="tooltip" data-bs-placement="top" 
                           title="{{ __('newguidings.tooltip_duration_type') }}"></i>
                    </label>
                    <div class="d-flex flex-wrap btn-group-toggle">
                        <input type="radio" name="duration" value="half_day" id="half_day">
                        <label for="half_day" class="btn btn-outline-primary m-2 flex-fill btn-checkbox" style="flex-basis: calc(33.33% - 20px);">
                            {{ __('newguidings.half_day') }}
                        </label>
                        
                        <input type="radio" name="duration" value="full_day" id="full_day">
                        <label for="full_day" class="btn btn-outline-primary m-2 flex-fill btn-checkbox" style="flex-basis: calc(33.33% - 20px);">
                            {{ __('newguidings.full_day') }}
                        </label>
                        
                        <input type="radio" name="duration" value="multi_day" id="multi_day">
                        <label for="multi_day" class="btn btn-outline-primary m-2 flex-fill btn-checkbox" style="flex-basis: calc(33.33% - 20px);">
                            {{ __('newguidings.multi_day') }}
                        </label>
                    </div>
                    <div id="duration_details" class="mt-3" style="display: none;">
                        <div id="hours_input" class="input-group mt-2">
                            <span class="input-group-text">{{ __('newguidings.number_of_hours') }}:</span>
                            <input type="number" id="duration_hours" name="duration_hours" class="form-control" value="{{ $formData['duration_hours'] ?? '' }}" min="1" max="24">
                        </div>
                        <div id="days_input" class="input-group mt-2">
                            <span class="input-group-text">{{ __('newguidings.number_of_days') }}:</span>
                            <input type="number" id="duration_days" name="duration_days" class="form-control" value="{{ $formData['duration_days'] ?? '' }}" min="2">
                        </div>
                    </div>
                </div>

                <hr>
                
                <div class="form-group">
                    <label for="no_guest" class="form-label fw-bold fs-5">
                        {{ __('newguidings.max_guests') }}
                        <i class="fas fa-info-circle ms-2 fs-6" data-bs-toggle="tooltip" data-bs-placement="top" 
                           title="{{ __('newguidings.tooltip_max_number_of_guests') }}"></i>
                    </label>
                    <input type="number" class="form-control" id="no_guest" name="no_guest" value="{{ $formData['no_guest'] ?? '' }}" placeholder="0">
                </div>

                <hr>
                
                <div class="form-group">
                    <label for="price" class="form-label fw-bold fs-5">
                        {{ __('newguidings.pricing') }}
                        <i class="fas fa-info-circle ms-2 fs-6" data-bs-toggle="tooltip" data-bs-placement="top" 
                           title="{{ __('newguidings.tooltip_pricing') }}"></i>
                    </label>
                    <div class="d-flex flex-wrap btn-group-toggle">
                        <input type="radio" name="price_type" value="per_person" id="per_person_checkbox">
                        <label for="per_person_checkbox" class="btn btn-outline-primary m-2 flex-fill btn-checkbox" style="flex-basis: calc(33.33% - 20px);">
                            {{ __('newguidings.per_person') }}
                        </label>
                        
                        <input type="radio" name="price_type" value="per_boat" id="per_boat_checkbox">
                        <label for="per_boat_checkbox" class="btn btn-outline-primary m-2 flex-fill btn-checkbox" style="flex-basis: calc(33.33% - 20px);">
                            {{ __('newguidings.per_boat') }}
                        </label>
                    </div>
                    
                    {{-- <div id="min_guests_container" style="display: none; margin-top: 15px; padding: 15px; border: 1px solid #ddd; border-radius: 5px; background-color: #f9f9f9;">
                        <label for="min_guests_switch" class="form-label fw-bold">
                            {{ __('newguidings.min_guests_required') }}
                            <i class="fas fa-info-circle ms-2 fs-6" data-bs-toggle="tooltip" data-bs-placement="top" 
                               title="{{ __('newguidings.tooltip_min_number_of_guests') }}"></i>
                        </label>
                        <div class="d-flex flex-column flex-md-row align-items-md-center">
                            <div class="form-check form-switch me-md-3 mb-2 mb-md-0">
                                <input class="form-check-input" type="checkbox" id="min_guests_switch" name="has_min_guests" {{ isset($formData['min_guests']) && $formData['min_guests'] > 0 ? 'checked' : '' }}>
                                <label class="form-check-label" for="min_guests_switch">{{ __('newguidings.enable_min_guests') }}</label>
                            </div>
                            <div id="min_guests_input_container" style="display: none; flex: 1;">
                                <input type="number" class="form-control" id="min_guests" name="min_guests" value="{{ $formData['min_guests'] ?? '' }}" placeholder="1" min="1">
                            </div>
                        </div>
                    </div> --}}
                    
                    <div class="form-group" id="dynamic-price-fields-container"></div>
                </div>

                
                <hr>
                <div class="form-group">
                    <label for="inclusions" class="form-label fw-bold fs-5">
                        {{ __('newguidings.inclusions') }}
                        <i class="fas fa-info-circle ms-2 fs-6" data-bs-toggle="tooltip" data-bs-placement="top" 
                           title="{{ __('newguidings.tooltip_included_in_price') }}"></i>
                    </label>
                    <input type="text" class="form-control" name="inclusions" id="inclusions" data-role="tagsinput" placeholder="{{ __('newguidings.inclusions_placeholder') }}">
                </div>

                <hr>
                <div class="form-group">
                    <label for="extra_pricing" class="form-label fw-bold fs-5">
                        <span>{{ __('newguidings.extra_pricing') }}</span>
                        <i class="fas fa-info-circle ms-2 fs-6" data-bs-toggle="tooltip" data-bs-placement="top" 
                           title="{{ __('newguidings.tooltip_extras_booked_additionally') }}"></i>
                        <button type="button" id="add-extra" class="btn btn-sm btn-secondary ms-2"><i class="fas fa-plus"></i></button>
                    </label>
                    <div id="extras-container"></div>
                </div>

                <div class="button-group">
                    <div class="left-buttons">
                        {{-- <button type="button" class="btn btn-secondary" id="saveDraftBtn6">
                            {{ __('newguidings.leave_save_draft') }}
                        </button> --}}
                    </div>
                    <div class="right-buttons">
                        <div class="row-button">
                            <button type="button" class="btn btn-primary" id="prevBtn6">
                                {{ __('newguidings.previous') }}
                            </button>
                            <button type="button" class="btn btn-primary" id="nextBtn6">
                                {{ __('newguidings.next') }}
                            </button>
                        </div>
                        <button type="submit" class="btn btn-primary" id="submitBtn6" style="display: none;">
                            {{ __('newguidings.submit_publish') }}
                        </button>
                    </div>
                </div>
            </div>

            <!-- Step 7 -->
            <div class="step" id="step7">
                <h5>{{ __('newguidings.define_availability_booking_options') }}</h5>
                <div class="form-group">
                    <label for="allowed_booking_advance" class="form-label fw-bold fs-5">
                        {{ __('newguidings.how_last_minute_can_a_guest_book_tour') }}
                        <i class="fas fa-info-circle ms-2 fs-6" data-bs-toggle="tooltip" data-bs-placement="top" 
                           title="{{ __('newguidings.tooltip_how_last_minute_can_a_guest_book_tour') }}"></i>
                    </label>
                    <div class="d-flex flex-wrap btn-group-toggle">
                        <input type="radio" name="allowed_booking_advance" value="same_day" id="same_day">
                        <label for="same_day" class="btn btn-outline-primary m-2 flex-fill btn-checkbox" style="flex-basis: calc(33.33% - 20px);">
                            {{ __('newguidings.on_the_same_day') }}
                        </label>
                        
                        <input type="radio" name="allowed_booking_advance" value="three_days" id="three_days">
                        <label for="three_days" class="btn btn-outline-primary m-2 flex-fill btn-checkbox" style="flex-basis: calc(33.33% - 20px);">
                            {{ __('newguidings.three_days_upfront') }}
                        </label>
                        
                        <input type="radio" name="allowed_booking_advance" value="one_week" id="one_week">
                        <label for="one_week" class="btn btn-outline-primary m-2 flex-fill btn-checkbox" style="flex-basis: calc(33.33% - 20px);">
                            {{ __('newguidings.one_week_upfront') }}
                        </label>
                        
                        <input type="radio" name="allowed_booking_advance" value="one_month" id="one_month">
                        <label for="one_month" class="btn btn-outline-primary m-2 flex-fill btn-checkbox" style="flex-basis: calc(33.33% - 20px);">
                            {{ __('newguidings.one_month_upfront') }}
                        </label>
                    </div>
                </div>

                <hr>

                <div class="form-group">
                    <label for="booking_window" class="form-label fw-bold fs-5">
                        {{ __('newguidings.how_far_into_future_can_a_guest_book_tour') }}
                        <i class="fas fa-info-circle ms-2 fs-6" data-bs-toggle="tooltip" data-bs-placement="top" 
                           title="{{ __('newguidings.tooltip_how_far_into_future_can_a_guest_book_tour') }}"></i>
                    </label>
                    <div class="d-flex flex-wrap btn-group-toggle">
                        <input type="radio" name="booking_window" value="no_limitation" id="no_limitation">
                        <label for="no_limitation" class="btn btn-outline-primary m-2 flex-fill btn-checkbox" style="flex-basis: calc(33.33% - 20px);">
                            {{ __('newguidings.no_limitation') }}
                        </label>
                        
                        <input type="radio" name="booking_window" value="six_months" id="six_months">
                        <label for="six_months" class="btn btn-outline-primary m-2 flex-fill btn-checkbox" style="flex-basis: calc(33.33% - 20px);">
                            {{ __('newguidings.six_months_in_advance') }}
                        </label>
                        
                        <input type="radio" name="booking_window" value="nine_months" id="nine_months">
                        <label for="nine_months" class="btn btn-outline-primary m-2 flex-fill btn-checkbox" style="flex-basis: calc(33.33% - 20px);">
                            {{ __('newguidings.nine_months_in_advance') }}
                        </label>
                        
                        <input type="radio" name="booking_window" value="twelve_months" id="twelve_months">
                        <label for="twelve_months" class="btn btn-outline-primary m-2 flex-fill btn-checkbox" style="flex-basis: calc(33.33% - 20px);">
                            {{ __('newguidings.twelve_months_in_advance') }}
                        </label>
                    </div>
                </div>
                
                <hr>

                <div class="form-group">
                    <label for="seasonal_trip" class="form-label fw-bold fs-5">
                        {{ __('newguidings.seasonal_trip') }}
                        <i class="fas fa-info-circle ms-2 fs-6" data-bs-toggle="tooltip" data-bs-placement="top" 
                           title="{{ __('newguidings.tooltip_seasonal_trip') }}"></i>
                    </label>
                    <div class="d-flex flex-wrap btn-group-toggle">
                        <input type="radio" name="seasonal_trip" value="season_year" id="season_year">
                        <label for="season_year" class="btn btn-outline-primary m-2 flex-fill btn-checkbox" style="flex-basis: calc(50% - 20px);">
                            {{ __('newguidings.available_all_year') }}
                        </label>
                        
                        <input type="radio" name="seasonal_trip" value="season_monthly" id="season_monthly">
                        <label for="season_monthly" class="btn btn-outline-primary m-2 flex-fill btn-checkbox" style="flex-basis: calc(50% - 20px);">
                            {{ __('newguidings.available_on_certain_months_only') }}
                        </label>
                    </div>
                    <div id="monthly_selection" style="display: none;">
                        <p class="mb-0" style="text-align:center;">Please select available months</p>
                        <div class="d-flex flex-wrap btn-group-toggle">
                            @foreach(__('newguidings.months') as $index => $month)
                                <div class="btn-checkbox-container" style="flex: 0 0 20%; max-width: 20%; padding: 5px;">
                                    <input type="checkbox" name="months[]" value="{{ $index }}" id="avail_{{ strtolower($month) }}">
                                    <label for="avail_{{ strtolower($month) }}" class="btn btn-outline-primary btn-checkbox w-100">{{ $month }}</label>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>


                <div class="button-group">
                    <div class="left-buttons">
                    </div>
                    <div class="right-buttons">
                        <div class="row-button">
                            <button type="button" class="btn btn-info" id="prevBtn7">
                                {{ __('newguidings.previous') }}
                            </button>
                            <div>

                            </div>
                        </div>
                        <button type="submit" class="btn btn-primary" id="submitBtn7" onclick="console.log('Form submitted');">
                            {{ __('newguidings.submit_publish') }}
                        </button>
                    </div>
                </div>
            </div>
            <div id="loadingScreen" style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background-color: rgba(0,0,0,0.5); z-index: 9999;">
                <div style="position: absolute; top: 50%; left: 50%; transform: translate(-50%, -50%); color: white; font-size: 24px;">
                    Loading...
                </div>
            </div>
        </form>
    </div>
</div>
@include('pages.guidings.includes.scripts.multi-step-form-script')

