{{-- Trip form mirrors camp-form multi-step wizard, adapted for trips --}}
<div id="trip-form" class="card">
    <div class="card-body">
        <div class="step-wrapper">
            <div class="step-buttons">
                <button type="button" class="step-button active" data-step="1">
                    <i class="fas fa-images"></i>
                </button>
                <button type="button" class="step-button" data-step="2">
                    <i class="fas fa-fish"></i>
                </button>
                <button type="button" class="step-button" data-step="3">
                    <i class="fas fa-hotel"></i>
                </button>
                <button type="button" class="step-button" data-step="4">
                    <i class="fas fa-align-left"></i>
                </button>
                <button type="button" class="step-button" data-step="5">
                    <i class="fas fa-check-circle"></i>
                </button>
                <button type="button" class="step-button" data-step="6">
                    <i class="fas fa-info-circle"></i>
                </button>
                <button type="button" class="step-button" data-step="7">
                    <i class="fas fa-tag"></i>
                </button>
                <button type="button" class="step-button" data-step="8">
                    <i class="fas fa-calendar-check"></i>
                </button>
            </div>
            <div class="step-line"></div>
        </div>

        <div id="trip-error-container" class="alert alert-danger" style="display: none;"></div>

        <form action="{{ $formAction ?? (isset($formData['id']) && $formData['id'] ? route('admin.trips.update', $formData['id']) : route('admin.trips.store')) }}"
              method="POST"
              id="tripForm"
              enctype="multipart/form-data">
            @csrf
            @if(isset($formData['id']) && $formData['id'])
                @method('PUT')
            @endif
            <meta name="csrf-token" content="{{ csrf_token() }}">

            <input type="hidden" name="target_redirect" id="target_redirect" value="{{ $targetRedirect ?? route('admin.trips.index') }}">
            <input type="hidden" name="is_update" id="is_update" value="{{ $formData['is_update'] ?? 0 }}">
            <input type="hidden" name="is_draft" id="is_draft" value="{{ isset($formData['status']) && $formData['status'] === 'draft' ? 1 : 0 }}">
            <input type="hidden" name="trip_id" id="trip_id" value="{{ $formData['id'] ?? 0 }}">
            <input type="hidden" name="thumbnail_path" id="thumbnail_path" value="{{ $formData['thumbnail_path'] ?? '' }}">
            <input type="hidden" name="existing_images" id="existing_images" value="{{ isset($formData['gallery_images']) && is_array($formData['gallery_images']) ? json_encode($formData['gallery_images']) : (isset($formData['gallery_images']) ? $formData['gallery_images'] : '') }}">
            <input type="hidden" name="user_id" id="user_id" value="{{ $formData['user_id'] ?? auth()->id() }}">
            <input type="hidden" name="status" id="status" value="{{ $formData['status'] ?? 'active' }}">
            <input type="hidden" id="image_list" name="image_list">

            {{-- STEP 1 — IMAGES & BASICS --}}
            <div class="step active" id="step1">
                <h5>{{ __('trips.upload_images_title') }}</h5>

                <label for="title_image" class="form-label fw-bold fs-5">
                    {{ __('trips.upload_image') }}
                </label>
                <div class="card mb-4">
                    <div class="card-body">
                        <div class="file-upload-wrapper">
                            <input id="title_image" name="title_image[]" type="file" multiple accept="image/*"/>
                            <input id="cropped_image" name="cropped_image[]" type="file" multiple hidden />
                            <label for="title_image" class="file-upload-btn">{{ __('trips.choose_files') }}</label>
                        </div>
                        <div id="croppedImagesContainer"></div>
                    </div>
                    <div class="image-area" id="imagePreviewContainer" style="display: none;"></div>
                    <input type="hidden" name="primaryImage" id="primaryImageInput">
                </div>

                <hr>

                <div class="form-group">
                    <label for="location" class="form-label fw-bold fs-5">
                        {{ __('trips.location') }}
                    </label>
                    <input type="search"
                           class="form-control"
                           id="location"
                           name="location"
                           value="{{ $formData['location'] ?? '' }}"
                           placeholder="{{ __('trips.location_placeholder') }}">
                    <input type="hidden" name="latitude" id="latitude" value="{{ $formData['latitude'] ?? '' }}">
                    <input type="hidden" name="longitude" id="longitude" value="{{ $formData['longitude'] ?? '' }}">
                    <input type="hidden" name="country" id="country" value="{{ $formData['country'] ?? '' }}">
                    <input type="hidden" name="city" id="cityField" value="{{ $formData['city'] ?? '' }}">
                    <input type="hidden" name="region" id="regionField" value="{{ $formData['region'] ?? '' }}">
                </div>

                <hr>

                <div class="form-group">
                    <label for="title" class="form-label fw-bold fs-5">
                        {{ __('trips.title') }}
                    </label>
                    <input type="text"
                           class="form-control"
                           id="title"
                           name="title"
                           value="{{ $formData['title'] ?? '' }}"
                           placeholder="{{ __('trips.title_placeholder') }}">
                </div>

                <div class="button-group">
                    <div class="left-buttons"></div>
                    <div class="right-buttons">
                        <div class="row-button">
                            <div></div>
                            <button type="button" class="btn btn-primary" id="nextBtn1">
                                {{ __('trips.next') }}
                            </button>
                        </div>
                        <button type="submit" class="btn btn-primary" id="submitBtn1" style="display:none;">
                            {{ __('trips.submit_publish') }}
                        </button>
                    </div>
                </div>
            </div>

            {{-- STEP 2 — FISHING & GENERAL DETAILS --}}
            <div class="step" id="step2">
                <h5>{{ __('trips.fishing_details_title') }}</h5>

                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label class="form-label fw-bold fs-5">{{ __('trips.target_species') }}</label>
                            <input type="text"
                                   class="form-control"
                                   name="target_species"
                                   id="target_species"
                                   placeholder="{{ __('trips.target_species') }}">
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label class="form-label fw-bold fs-5">{{ __('trips.fishing_methods') }}</label>
                            <input type="text"
                                   class="form-control"
                                   name="fishing_methods"
                                   id="fishing_methods"
                                   placeholder="{{ __('trips.fishing_methods') }}">
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label class="form-label fw-bold fs-5">{{ __('trips.fishing_style') }}</label>
                            <div class="d-flex flex-wrap btn-group-toggle">
                                @php
                                    $style = $formData['fishing_style'] ?? null;
                                @endphp
                                <div class="btn-checkbox-container me-2">
                                    <input class="form-check-input" type="radio" name="fishing_style" id="fishing_style_active" value="active" {{ $style === 'active' ? 'checked' : '' }}>
                                    <label class="btn-checkbox" for="fishing_style_active">{{ __('trips.fishing_style_active') }}</label>
                                </div>
                                <div class="btn-checkbox-container me-2">
                                    <input class="form-check-input" type="radio" name="fishing_style" id="fishing_style_passive" value="passive" {{ $style === 'passive' ? 'checked' : '' }}>
                                    <label class="btn-checkbox" for="fishing_style_passive">{{ __('trips.fishing_style_passive') }}</label>
                                </div>
                                <div class="btn-checkbox-container me-2">
                                    <input class="form-check-input" type="radio" name="fishing_style" id="fishing_style_both" value="both" {{ $style === 'both' ? 'checked' : '' }}>
                                    <label class="btn-checkbox" for="fishing_style_both">{{ __('trips.fishing_style_both') }}</label>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label class="form-label fw-bold fs-5">{{ __('trips.water_types') }}</label>
                            <input type="text"
                                   class="form-control"
                                   name="water_types"
                                   id="water_types"
                                   placeholder="{{ __('trips.water_types') }}">
                        </div>
                    </div>
                </div>

                <div class="form-group">
                    <label class="form-label fw-bold fs-5">{{ __('trips.skill_level') }}</label>
                    @php $skill = $formData['skill_level'] ?? null; @endphp
                    <div class="d-flex flex-wrap btn-group-toggle">
                        <div class="btn-checkbox-container me-2">
                            <input class="form-check-input" type="radio" name="skill_level" id="skill_beginner" value="beginner" {{ $skill === 'beginner' ? 'checked' : '' }}>
                            <label class="btn-checkbox" for="skill_beginner">Beginner</label>
                        </div>
                        <div class="btn-checkbox-container me-2">
                            <input class="form-check-input" type="radio" name="skill_level" id="skill_intermediate" value="intermediate" {{ $skill === 'intermediate' ? 'checked' : '' }}>
                            <label class="btn-checkbox" for="skill_intermediate">Intermediate</label>
                        </div>
                        <div class="btn-checkbox-container me-2">
                            <input class="form-check-input" type="radio" name="skill_level" id="skill_advanced" value="advanced" {{ $skill === 'advanced' ? 'checked' : '' }}>
                            <label class="btn-checkbox" for="skill_advanced">Advanced</label>
                        </div>
                        <div class="btn-checkbox-container me-2">
                            <input class="form-check-input" type="radio" name="skill_level" id="skill_all_levels" value="all_levels" {{ $skill === 'all_levels' ? 'checked' : '' }}>
                            <label class="btn-checkbox" for="skill_all_levels">All levels</label>
                        </div>
                    </div>
                </div>

                <hr>

                <h5>{{ __('trips.general_details_title') }}</h5>
                <div class="row">
                    <div class="col-md-3">
                        <div class="form-group">
                            <label class="form-label">{{ __('trips.duration_nights') }}</label>
                            <input type="number" min="0" class="form-control" name="duration_nights" value="{{ $formData['duration_nights'] ?? '' }}">
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="form-group">
                            <label class="form-label">{{ __('trips.duration_days') }}</label>
                            <input type="number" min="0" class="form-control" name="duration_days" value="{{ $formData['duration_days'] ?? '' }}">
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="form-group">
                            <label class="form-label">{{ __('trips.group_size_min') }}</label>
                            <input type="number" min="1" class="form-control" name="group_size_min" value="{{ $formData['group_size_min'] ?? '' }}">
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="form-group">
                            <label class="form-label">{{ __('trips.group_size_max') }}</label>
                            <input type="number" min="1" class="form-control" name="group_size_max" value="{{ $formData['group_size_max'] ?? '' }}">
                        </div>
                    </div>
                </div>

                <div class="form-group">
                    <label class="form-label fw-bold fs-5">{{ __('trips.trip_schedule') }}</label>
                    <div id="trip_schedule_container">
                        {{-- rows managed by JS using existing data --}}
                    </div>
                    <button type="button" class="btn btn-sm btn-outline-primary mt-2" id="addScheduleRowBtn">
                        <i class="fas fa-plus"></i> Add Day
                    </button>
                </div>

                <div class="form-group">
                    <label class="form-label fw-bold fs-5">{{ __('trips.meeting_point') }}</label>
                    <textarea class="form-control" name="meeting_point" rows="3">{{ $formData['meeting_point'] ?? '' }}</textarea>
                </div>

                <div class="row">
                    @php
                        $bestSeasonFromRaw = $formData['best_season_from'] ?? '';
                        $bestSeasonToRaw   = $formData['best_season_to'] ?? '';
                        $normalizeMonth = function ($v) {
                            if ($v === null || $v === '') return '';
                            $num = (int) preg_replace('/\D/', '', (string) $v);
                            return ($num >= 1 && $num <= 12) ? str_pad((string) $num, 2, '0', STR_PAD_LEFT) : '';
                        };
                        $bestSeasonFrom = $normalizeMonth($bestSeasonFromRaw);
                        $bestSeasonTo   = $normalizeMonth($bestSeasonToRaw);
                        $monthsOptions     = [
                            '01' => 'January',
                            '02' => 'February',
                            '03' => 'March',
                            '04' => 'April',
                            '05' => 'May',
                            '06' => 'June',
                            '07' => 'July',
                            '08' => 'August',
                            '09' => 'September',
                            '10' => 'October',
                            '11' => 'November',
                            '12' => 'December',
                        ];
                    @endphp
                    <div class="col-md-4">
                        <div class="form-group">
                            <label class="form-label">{{ __('trips.best_season_from') }}</label>
                            <select name="best_season_from" class="form-control">
                                <option value="">{{ __('trips.select_options') }}</option>
                                @foreach($monthsOptions as $value => $label)
                                    <option value="{{ $value }}" {{ $bestSeasonFrom === $value ? 'selected' : '' }}>
                                        {{ $label }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="form-group">
                            <label class="form-label">{{ __('trips.best_season_to') }}</label>
                            <select name="best_season_to" class="form-control">
                                <option value="">{{ __('trips.select_options') }}</option>
                                @foreach($monthsOptions as $value => $label)
                                    <option value="{{ $value }}" {{ $bestSeasonTo === $value ? 'selected' : '' }}>
                                        {{ $label }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="form-group">
                            <label class="form-label">{{ __('trips.catering') }}</label>
                            @php
                                $cateringRaw = $formData['catering'] ?? '';
                                if (is_array($cateringRaw)) {
                                    $cateringValue = collect($cateringRaw)
                                        ->map(function ($item) {
                                            return is_array($item) ? ($item['name'] ?? null) : $item;
                                        })
                                        ->filter()
                                        ->implode(',');
                                } else {
                                    $cateringValue = $cateringRaw;
                                }
                            @endphp
                            <input type="text"
                                   class="form-control"
                                   name="catering"
                                   id="catering"
                                   value="{{ $cateringValue }}"
                                   placeholder="{{ __('trips.catering') }}">
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label class="form-label">{{ __('trips.best_arrival_options') }}</label>
                            <input type="text" class="form-control" name="best_arrival_options" value="{{ $formData['best_arrival_options'] ?? '' }}">
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label class="form-label">{{ __('trips.arrival_day') }}</label>
                            <input type="text" class="form-control" name="arrival_day" value="{{ $formData['arrival_day'] ?? '' }}">
                        </div>
                    </div>
                </div>

                <div class="button-group">
                    <div class="left-buttons">
                        <button type="button" class="btn btn-secondary" id="saveDraftBtn2">
                            {{ __('trips.leave_save_draft') }}
                        </button>
                    </div>
                    <div class="right-buttons">
                        <div class="row-button">
                            <button type="button" class="btn btn-info" id="prevBtn2">
                                {{ __('trips.previous') }}
                            </button>
                            <button type="button" class="btn btn-primary" id="nextBtn2">
                                {{ __('trips.next') }}
                            </button>
                        </div>
                        <button type="submit" class="btn btn-primary" id="submitBtn2" style="display:none;">
                            {{ __('trips.submit_publish') }}
                        </button>
                    </div>
                </div>
            </div>

            {{-- STEP 3 — BOAT, ACCOMMODATION & PROVIDER --}}
            <div class="step" id="step3">
                <h5>{{ __('trips.boat_information_title') }}</h5>

                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label class="form-label fw-bold fs-5">{{ __('trips.boat_type') }}</label>
                            @php
                                $selectedBoatType = $formData['boat_type'] ?? null;
                            @endphp
                            @if(!empty($guiding_boat_types ?? []))
                                <div class="d-flex flex-wrap btn-group-toggle">
                                    @foreach($guiding_boat_types as $boatType)
                                        <div class="btn-checkbox-container me-2 mb-2">
                                            <input
                                                class="form-check-input"
                                                type="radio"
                                                name="boat_type"
                                                id="boat_type_{{ $boatType['id'] }}"
                                                value="{{ $boatType['value'] }}"
                                                {{ $selectedBoatType === $boatType['value'] ? 'checked' : '' }}>
                                            <label class="btn-checkbox" for="boat_type_{{ $boatType['id'] }}">
                                                {{ $boatType['value'] }}
                                            </label>
                                        </div>
                                    @endforeach
                                </div>
                            @else
                                <input type="text"
                                       class="form-control"
                                       name="boat_type"
                                       value="{{ $selectedBoatType }}">
                            @endif
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label class="form-label fw-bold fs-5">{{ __('trips.boat_features') }}</label>
                            <input type="text"
                                   class="form-control"
                                   name="boat_features"
                                   id="boat_features"
                                   placeholder="{{ __('trips.boat_features') }}">
                        </div>
                    </div>
                </div>

                <div class="form-group">
                    <label class="form-label fw-bold fs-5">{{ __('trips.boat_information') }}</label>
                    <textarea class="form-control" name="boat_information" rows="3">{{ $formData['boat_information'] ?? '' }}</textarea>
                </div>

                <hr>

                <h5>{{ __('trips.accommodation_title') }}</h5>

                <div class="form-group">
                    <label class="form-label fw-bold fs-5">{{ __('trips.accommodation_description') }}</label>
                    <textarea class="form-control" name="accommodation_description" rows="3">{{ $formData['accommodation_description'] ?? '' }}</textarea>
                </div>

                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label class="form-label fw-bold fs-5">{{ __('trips.accommodation_type') }}</label>
                            @php
                                $accType = $formData['accommodation_type'] ?? null;
                                $accommodationTypes = __('accommodations.options.accommodation_types');
                            @endphp
                            @if(is_array($accommodationTypes) && count($accommodationTypes))
                                <div class="d-flex flex-wrap btn-group-toggle">
                                    @foreach($accommodationTypes as $value => $label)
                                        <div class="btn-checkbox-container me-2 mb-2">
                                            <input
                                                class="form-check-input"
                                                type="radio"
                                                name="accommodation_type"
                                                id="acc_{{ $value }}"
                                                value="{{ $value }}"
                                                {{ $accType === $value ? 'checked' : '' }}>
                                            <label class="btn-checkbox" for="acc_{{ $value }}">{{ $label }}</label>
                                        </div>
                                    @endforeach
                                </div>
                            @else
                                <input
                                    class="form-control"
                                    type="text"
                                    name="accommodation_type"
                                    id="accommodation_type"
                                    value="{{ $accType }}">
                            @endif
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label class="form-label fw-bold fs-5">{{ __('trips.room_types') }}</label>
                            @php
                                $roomTypesRaw = $formData['room_types'] ?? '';
                                if (is_array($roomTypesRaw)) {
                                    $roomTypesValue = collect($roomTypesRaw)
                                        ->map(function ($item) {
                                            return is_array($item) ? ($item['name'] ?? null) : $item;
                                        })
                                        ->filter()
                                        ->implode(',');
                                } else {
                                    $roomTypesValue = $roomTypesRaw;
                                }
                            @endphp
                            <input type="text"
                                   class="form-control"
                                   name="room_types"
                                   id="room_types"
                                   value="{{ $roomTypesValue }}"
                                   placeholder="{{ __('trips.room_types') }}">
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label class="form-label">{{ __('trips.distance_to_water') }}</label>
                            <input type="text" class="form-control" name="distance_to_water" value="{{ $formData['distance_to_water'] ?? '' }}">
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label class="form-label">{{ __('trips.nearest_airport') }}</label>
                            <input type="text" class="form-control" name="nearest_airport" value="{{ $formData['nearest_airport'] ?? '' }}">
                        </div>
                    </div>
                </div>

                <hr>

                <h5>{{ __('trips.provider_title') }}</h5>

                <div class="form-group">
                    <label class="form-label fw-bold fs-5">{{ __('trips.provider_name') }}</label>
                    <input type="text" class="form-control" name="provider_name" value="{{ $formData['provider_name'] ?? '' }}">
                </div>

                <div class="form-group">
                    <label class="form-label fw-bold fs-5">{{ __('trips.provider_photo') }}</label>
                    <div class="card mb-3">
                        <div class="card-body">
                            <div class="file-upload-wrapper">
                                <input id="provider_photo" name="provider_photo" type="file" accept="image/*">
                                <label for="provider_photo" class="file-upload-btn">{{ __('trips.choose_files') }}</label>
                            </div>
                            <div id="providerPhotoPreviewContainer" class="mt-3"></div>
                        </div>
                    </div>
                    @if(!empty($formData['provider_photo']))
                        <div class="mt-2">
                            <img src="{{ asset('storage/' . $formData['provider_photo']) }}"
                                 alt="Provider"
                                 class="img-thumbnail"
                                 style="width:80px;height:80px;object-fit:cover;">
                        </div>
                    @endif
                </div>

                <div class="form-group">
                    <label class="form-label fw-bold fs-5">{{ __('trips.provider_experience') }}</label>
                    <textarea class="form-control" name="provider_experience" rows="2">{{ $formData['provider_experience'] ?? '' }}</textarea>
                </div>

                <div class="form-group">
                    <label class="form-label fw-bold fs-5">{{ __('trips.provider_certifications') }}</label>
                    <textarea class="form-control" name="provider_certifications" rows="2">{{ $formData['provider_certifications'] ?? '' }}</textarea>
                </div>

                <div class="form-group">
                    <label class="form-label fw-bold fs-5">{{ __('trips.boat_staff') }}</label>
                    <input type="text" class="form-control" name="boat_staff" value="{{ $formData['boat_staff'] ?? '' }}">
                </div>

                <div class="form-group">
                    <label class="form-label fw-bold fs-5">{{ __('trips.guide_languages') }}</label>
                    @php
                        $guideLangRaw = $formData['guide_languages'] ?? '';
                        if (is_array($guideLangRaw)) {
                            $guideLangValue = collect($guideLangRaw)
                                ->map(function ($item) {
                                    return is_array($item) ? ($item['name'] ?? null) : $item;
                                })
                                ->filter()
                                ->implode(',');
                        } else {
                            $guideLangValue = $guideLangRaw;
                        }
                    @endphp
                    <input type="text"
                           class="form-control"
                           name="guide_languages"
                           id="guide_languages"
                           value="{{ $guideLangValue }}"
                           placeholder="{{ __('trips.guide_languages') }}">
                </div>

                <div class="button-group">
                    <div class="left-buttons">
                        <button type="button" class="btn btn-secondary" id="saveDraftBtn3">
                            {{ __('trips.leave_save_draft') }}
                        </button>
                    </div>
                    <div class="right-buttons">
                        <div class="row-button">
                            <button type="button" class="btn btn-info" id="prevBtn3">
                                {{ __('trips.previous') }}
                            </button>
                            <button type="button" class="btn btn-primary" id="nextBtn3">
                                {{ __('trips.next') }}
                            </button>
                        </div>
                        <button type="submit" class="btn btn-primary" id="submitBtn3" style="display:none;">
                            {{ __('trips.submit_publish') }}
                        </button>
                    </div>
                </div>
            </div>

            {{-- STEP 4 — DESCRIPTION & ITINERARY --}}
            <div class="step" id="step4">
                <h5>{{ __('trips.description_title') }}</h5>

                <div class="form-group">
                    <label class="form-label fw-bold fs-5">{{ __('trips.description') }}</label>
                    <textarea class="form-control"
                              name="description"
                              id="trip_description_editor"
                              rows="6">{!! str_replace('</textarea>', '&lt;/textarea&gt;', $formData['description'] ?? '') !!}</textarea>
                </div>

                <div class="form-group">
                    <label class="form-label fw-bold fs-5">{{ __('trips.trip_highlights') }}</label>
                    <div id="trip_highlights_container">
                        {{-- bullet items managed via JS --}}
                    </div>
                    <button type="button" class="btn btn-sm btn-outline-primary mt-2" id="addHighlightBtn">
                        <i class="fas fa-plus"></i> {{ __('trips.add_highlight') }}
                    </button>
                </div>

                <div class="row g-3 mt-2">
                    <div class="col-md-4">
                        <div class="btn-checkbox-container w-100">
                            <input class="form-check-input d-none" type="checkbox" id="highlight_accommodation_enabled" name="highlight_accommodation_enabled" {{ ($formData['trip_highlights']['accommodation_nights']['enabled'] ?? false) ? 'checked' : '' }}>
                            <label class="btn btn-outline-primary btn-checkbox w-100 d-flex justify-content-between align-items-center" for="highlight_accommodation_enabled">
                                <span>{{ __('trips.highlight_accommodation') }}</span>
                                <span class="ms-2"><i class="fas fa-bed"></i></span>
                            </label>
                        </div>
                        <input type="number"
                               class="form-control mt-2 highlight-input"
                               name="highlight_accommodation_nights"
                               value="{{ $formData['trip_highlights']['accommodation_nights']['value'] ?? '' }}"
                               placeholder="Nights"
                               @if(empty($formData['trip_highlights']['accommodation_nights']['enabled'])) style="display:none;" @endif>
                    </div>
                    <div class="col-md-4">
                        <div class="btn-checkbox-container w-100">
                            <input class="form-check-input d-none" type="checkbox" id="highlight_fishing_enabled" name="highlight_fishing_enabled" {{ ($formData['trip_highlights']['fishing_days']['enabled'] ?? false) ? 'checked' : '' }}>
                            <label class="btn btn-outline-primary btn-checkbox w-100 d-flex justify-content-between align-items-center" for="highlight_fishing_enabled">
                                <span>{{ __('trips.highlight_fishing') }}</span>
                                <span class="ms-2"><i class="fas fa-fish"></i></span>
                            </label>
                        </div>
                        <input type="number"
                               class="form-control mt-2 highlight-input"
                               name="highlight_fishing_days"
                               value="{{ $formData['trip_highlights']['fishing_days']['value'] ?? '' }}"
                               placeholder="Days"
                               @if(empty($formData['trip_highlights']['fishing_days']['enabled'])) style="display:none;" @endif>
                    </div>
                    <div class="col-md-4">
                        <div class="btn-checkbox-container w-100">
                            <input class="form-check-input d-none" type="checkbox" id="highlight_travel_enabled" name="highlight_travel_enabled" {{ ($formData['trip_highlights']['travel_days']['enabled'] ?? false) ? 'checked' : '' }}>
                            <label class="btn btn-outline-primary btn-checkbox w-100 d-flex justify-content-between align-items-center" for="highlight_travel_enabled">
                                <span>{{ __('trips.highlight_travel') }}</span>
                                <span class="ms-2"><i class="fas fa-route"></i></span>
                            </label>
                        </div>
                        <input type="number"
                               class="form-control mt-2 highlight-input"
                               name="highlight_travel_days"
                               value="{{ $formData['trip_highlights']['travel_days']['value'] ?? '' }}"
                               placeholder="Days"
                               @if(empty($formData['trip_highlights']['travel_days']['enabled'])) style="display:none;" @endif>
                    </div>
                </div>

                <div class="button-group">
                    <div class="left-buttons">
                        <button type="button" class="btn btn-secondary" id="saveDraftBtn4">
                            {{ __('trips.leave_save_draft') }}
                        </button>
                    </div>
                    <div class="right-buttons">
                        <div class="row-button">
                            <button type="button" class="btn btn-info" id="prevBtn4">
                                {{ __('trips.previous') }}
                            </button>
                            <button type="button" class="btn btn-primary" id="nextBtn4">
                                {{ __('trips.next') }}
                            </button>
                        </div>
                        <button type="submit" class="btn btn-primary" id="submitBtn4" style="display:none;">
                            {{ __('trips.submit_publish') }}
                        </button>
                    </div>
                </div>
            </div>

            {{-- STEP 5 — INCLUDED & EXCLUDED --}}
            <div class="step" id="step5">
                <h5>{{ __('trips.included_excluded_title') }}</h5>

                @php
                    $includedPreset = $includedPreset ?? [];
                    $excludedPreset = $excludedPreset ?? [];
                    $combinedPreset = array_values(array_unique(array_filter(array_merge($includedPreset, $excludedPreset))));

                    $includedRaw = $formData['included'] ?? [];
                    $includedValue = collect(is_array($includedRaw) ? $includedRaw : [])
                        ->map(function ($item) {
                            return is_array($item) ? ($item['name'] ?? null) : $item;
                        })
                        ->filter()
                        ->implode(',');

                    $excludedRaw = $formData['excluded'] ?? [];
                    $excludedValue = collect(is_array($excludedRaw) ? $excludedRaw : [])
                        ->map(function ($item) {
                            return is_array($item) ? ($item['name'] ?? null) : $item;
                        })
                        ->filter()
                        ->implode(',');
                @endphp

                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label class="form-label fw-bold fs-5">{{ __('trips.included') }}</label>
                            <input type="text"
                                   class="form-control"
                                   name="included"
                                   id="included"
                                   placeholder="{{ __('trips.included') }}"
                                   value="{{ $includedValue }}"
                                   data-preset='@json($combinedPreset)'>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label class="form-label fw-bold fs-5">{{ __('trips.excluded') }}</label>
                            <input type="text"
                                   class="form-control"
                                   name="excluded"
                                   id="excluded"
                                   placeholder="{{ __('trips.excluded') }}"
                                   value="{{ $excludedValue }}"
                                   data-preset='@json($combinedPreset)'>
                        </div>
                    </div>
                </div>

                <div class="button-group">
                    <div class="left-buttons">
                        <button type="button" class="btn btn-secondary" id="saveDraftBtn5">
                            {{ __('trips.leave_save_draft') }}
                        </button>
                    </div>
                    <div class="right-buttons">
                        <div class="row-button">
                            <button type="button" class="btn btn-info" id="prevBtn5">
                                {{ __('trips.previous') }}
                            </button>
                            <button type="button" class="btn btn-primary" id="nextBtn5">
                                {{ __('trips.next') }}
                            </button>
                        </div>
                        <button type="submit" class="btn btn-primary" id="submitBtn5" style="display:none;">
                            {{ __('trips.submit_publish') }}
                        </button>
                    </div>
                </div>
            </div>

            {{-- STEP 6 — ADDITIONAL INFORMATION --}}
            <div class="step" id="step6">
                <h5>{{ __('trips.additional_info_title') }}</h5>
                @php $additional = $formData['additional_info'] ?? []; @endphp
                @php
                    $toggleFields = [
                        'child_friendly' => __('trips.child_friendly'),
                        'accessible' => __('trips.accessible'),
                        'smoking_allowed' => __('trips.smoking_allowed'),
                        'alcohol_allowed' => __('trips.alcohol_allowed'),
                        'catch_and_release' => __('trips.catch_and_release'),
                        'catch_success' => __('trips.catch_success_label'),
                        'license_required' => __('trips.license_required'),
                        'clothing_recommendations' => __('trips.clothing_recommendations'),
                        'experience_level_required' => __('trips.experience_level_required'),
                        'equipment_to_bring' => __('trips.equipment_to_bring'),
                        'minimum_age' => __('trips.minimum_age'),
                        'maximum_age' => __('trips.maximum_age'),
                        'non_fishing_activities' => __('trips.non_fishing_activities'),
                        'cuisine_food_style' => __('trips.cuisine_food_style'),
                    ];
                @endphp

                <div class="row">
                    @foreach($toggleFields as $key => $label)
                        @php
                            $data = $additional[$key] ?? ['enabled' => false, 'details' => null];
                            $isEnabled = !empty($data['enabled']);
                        @endphp
                        <div class="col-md-6 mb-3">
                            <div class="btn-checkbox-container">
                                <input
                                    type="checkbox"
                                    class="form-check-input d-none trip-additional-toggle"
                                    id="{{ $key }}_enabled"
                                    name="{{ $key }}_enabled"
                                    {{ $isEnabled ? 'checked' : '' }}>
                                <label class="btn btn-outline-primary btn-checkbox text-start px-3 py-2" for="{{ $key }}_enabled">
                                    {{ $label }}
                                </label>
                            </div>
                            <input type="text"
                                   class="form-control mt-2 trip-additional-input"
                                   name="{{ $key }}_details"
                                   value="{{ $data['details'] ?? '' }}"
                                   placeholder="Details (optional)"
                                   @unless($isEnabled) style="display:none;" @endunless>
                        </div>
                    @endforeach
                </div>

                <div class="button-group">
                    <div class="left-buttons">
                        <button type="button" class="btn btn-secondary" id="saveDraftBtn6">
                            {{ __('trips.leave_save_draft') }}
                        </button>
                    </div>
                    <div class="right-buttons">
                        <div class="row-button">
                            <button type="button" class="btn btn-info" id="prevBtn6">
                                {{ __('trips.previous') }}
                            </button>
                            <button type="button" class="btn btn-primary" id="nextBtn6">
                                {{ __('trips.next') }}
                            </button>
                        </div>
                        <button type="submit" class="btn btn-primary" id="submitBtn6" style="display:none;">
                            {{ __('trips.submit_publish') }}
                        </button>
                    </div>
                </div>
            </div>

            {{-- STEP 7 — PRICING --}}
            <div class="step" id="step7">
                <h5>{{ __('trips.pricing_title') }}</h5>

                <div class="form-group">
                    <label class="form-label fw-bold fs-5">{{ __('trips.cancellation_policy') }}</label>
                    <textarea class="form-control" name="cancellation_policy" rows="3">{{ $formData['cancellation_policy'] ?? '' }}</textarea>
                </div>

                <div class="row">
                    <div class="col-md-4">
                        <div class="form-group">
                            <label class="form-label fw-bold fs-5">{{ __('trips.price_per_person') }}</label>
                            <input type="number" step="0.01" min="0" class="form-control" name="price_per_person" value="{{ $formData['price_per_person'] ?? '' }}">
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="form-group">
                            <label class="form-label fw-bold fs-5">{{ __('trips.price_single_room_addition') }}</label>
                            <input type="number" step="0.01" min="0" class="form-control" name="price_single_room_addition" value="{{ $formData['price_single_room_addition'] ?? '' }}">
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="form-group">
                            <label class="form-label fw-bold fs-5">{{ __('trips.currency') }}</label>
                            <input type="text" class="form-control" name="currency" value="{{ $formData['currency'] ?? 'EUR' }}" placeholder="EUR" maxlength="3">
                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="col-12">
                        <div class="form-group">
                            <label class="form-label fw-bold fs-5">{{ __('trips.downpayment_policy') }}</label>
                            <textarea class="form-control" name="downpayment_policy" rows="2">{{ $formData['downpayment_policy'] ?? '' }}</textarea>
                        </div>
                    </div>
                </div>

                <div class="button-group">
                    <div class="left-buttons">
                        <button type="button" class="btn btn-secondary" id="saveDraftBtn7">
                            {{ __('trips.leave_save_draft') }}
                        </button>
                    </div>
                    <div class="right-buttons">
                        <div class="row-button">
                            <button type="button" class="btn btn-info" id="prevBtn7">
                                {{ __('trips.previous') }}
                            </button>
                            <button type="button" class="btn btn-primary" id="nextBtn7">
                                {{ __('trips.next') }}
                            </button>
                        </div>
                        <button type="submit" class="btn btn-primary" id="submitBtn7" style="display:none;">
                            {{ __('trips.submit_publish') }}
                        </button>
                    </div>
                </div>
            </div>

            {{-- STEP 8 — AVAILABILITY --}}
            <div class="step" id="step8">
                <h5>{{ __('trips.availability_title') }}</h5>

                <div class="table-responsive">
                    <table class="table table-sm" id="availabilityTable">
                        <thead>
                            <tr>
                                <th>{{ __('trips.availability_departure_date') }}</th>
                                <th>{{ __('trips.availability_spots') }}</th>
                                <th>{{ __('trips.availability_status') }}</th>
                                <th></th>
                            </tr>
                        </thead>
                        <tbody>
                            {{-- rows managed dynamically in JS from availability data --}}
                        </tbody>
                    </table>
                    <button type="button" class="btn btn-sm btn-outline-primary" id="addAvailabilityRowBtn">
                        <i class="fas fa-plus"></i> Add Date
                    </button>
                </div>

                <div class="button-group mt-4">
                    <div class="left-buttons">
                        <button type="button" class="btn btn-secondary" id="saveDraftBtn8">
                            {{ __('trips.leave_save_draft') }}
                        </button>
                    </div>
                    <div class="right-buttons">
                        <div class="row-button">
                            <button type="button" class="btn btn-info" id="prevBtn8">
                                {{ __('trips.previous') }}
                            </button>
                            <div></div>
                        </div>
                        <button type="submit" class="btn btn-primary" id="submitBtn8" onclick="document.getElementById('is_draft').value = '0';">
                            {{ __('trips.submit_publish') }}
                        </button>
                    </div>
                </div>
            </div>
        </form>
    </div>
</div>
@include('components.trip-form-scripts')

