@push('js_push')
<script src="https://cdnjs.cloudflare.com/ajax/libs/cropperjs/1.5.12/cropper.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/@yaireo/tagify"></script>
<script src="https://cdn.jsdelivr.net/npm/browser-image-compression@latest/dist/browser-image-compression.js"></script>
@if(!isset($imageManagerLoaded))
    <script>
        if (!window.imageManagerScriptLoaded) {
            var script = document.createElement('script');
            script.src = '{{ asset("assets/js/ImageManager.js") }}';
            script.async = true;
            document.head.appendChild(script);
            window.imageManagerScriptLoaded = true;
        }
    </script>
    @php $imageManagerLoaded = true; @endphp
@endif

@push('styles')
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@yaireo/tagify/dist/tagify.css">
@endpush

<script>
    window.imageManagerLoaded = window.imageManagerLoaded || null;
    window.currentStep = window.currentStep || 1;
    window.totalSteps = window.totalSteps || 8;

    document.addEventListener('DOMContentLoaded', function () {
        if (typeof $ !== 'undefined') {
            initializeTripForm();
        } else {
            setTimeout(function () {
                if (typeof $ !== 'undefined') {
                    initializeTripForm();
                }
            }, 100);
        }
    });

    function initializeTripForm() {
        if (window.tripFormInitialized) {
            return;
        }
        window.tripFormInitialized = true;

        function initializeImageManagers() {
            if (typeof ImageManager !== 'undefined') {
                try {
                    if (!window.imageManagerLoaded) {
                        window.imageManagerLoaded = new ImageManager('#croppedImagesContainer', '#title_image');
                    }
                    if (!window.providerImageManager) {
                        window.providerImageManager = new ImageManager('#providerPhotoPreviewContainer', '#provider_photo');
                    }
                } catch (e) {
                    console.error('Error creating ImageManager instances:', e);
                }
            } else {
                setTimeout(initializeImageManagers, 100);
            }
        }

        initializeImageManagers();

        if (document.getElementById('is_update').value === '1') {
            setTimeout(() => {
                const existingImagesInput = document.getElementById('existing_images');
                const thumbnailPath = document.getElementById('thumbnail_path').value;
                if (existingImagesInput && existingImagesInput.value && window.imageManagerLoaded) {
                    try {
                        const existingImages = JSON.parse(existingImagesInput.value);
                        if (Array.isArray(existingImages) && existingImages.length > 0) {
                            window.imageManagerLoaded.loadExistingImages(existingImages, thumbnailPath);
                        }
                    } catch (e) {
                        console.error('Error loading existing images:', e);
                    }
                }
            }, 500);
        }

        initializeLocationAutocompleteForTrips();
        initializeTripTagify();
        setupTripStepNavigation();
        setupTripFormSubmission();
        initializeAvailabilityTable();
        initializeScheduleRows();
        // CKEditor for description is initialized when user reaches step 4 (see goToTripStep) so it reads the textarea content correctly
        initializeTripHighlights();

        const imageUploadInput = document.getElementById('title_image');
        if (imageUploadInput) {
            imageUploadInput.addEventListener('change', function (event) {
                if (window.imageManagerLoaded) {
                    try {
                        window.imageManagerLoaded.handleFileSelect(event.target.files);
                    } catch (error) {
                        console.error('Error in handleFileSelect:', error);
                    }
                }
            });
        }

        const providerPhotoInput = document.getElementById('provider_photo');
        if (providerPhotoInput) {
            providerPhotoInput.addEventListener('change', function (event) {
                if (window.providerImageManager) {
                    try {
                        window.providerImageManager.handleFileSelect(event.target.files);
                    } catch (error) {
                        console.error('Error in provider handleFileSelect:', error);
                    }
                }
            });
        }
    }

    function setupTripStepNavigation() {
        const totalSteps = 8;

        $('.step-button').click(function () {
            const step = parseInt($(this).data('step'));
            goToTripStep(step);
        });

        for (let i = 1; i <= totalSteps; i++) {
            $(`#nextBtn${i}`).click(function () {
                goToTripStep(i + 1 <= totalSteps ? i + 1 : totalSteps);
            });
        }
        for (let i = 2; i <= totalSteps; i++) {
            $(`#prevBtn${i}`).click(function () {
                goToTripStep(i - 1);
            });
        }

        for (let i = 1; i <= totalSteps; i++) {
            $(`#saveDraftBtn${i}`).click(function () {
                $('#is_draft').val('1');
                $('#tripForm').trigger('submit');
            });
        }

        function goToTripStep(step) {
            $('.step').removeClass('active');
            $('.step-button').removeClass('active');

            $(`#step${step}`).addClass('active');
            $(`.step-button[data-step="${step}"]`).addClass('active');

            $('.step').each(function () {
                const stepNum = parseInt($(this).attr('id').replace('step', ''));
                if (stepNum === step && step === totalSteps) {
                    $(this).find('button[type="submit"]').show();
                    $(this).find(`#nextBtn${stepNum}`).hide();
                } else {
                    $(this).find('button[type="submit"]').hide();
                    $(this).find(`#nextBtn${stepNum}`).show();
                }
            });

            // Lazy-init CKEditor for description when step 4 is shown so it picks up the textarea content
            if (step === 4 && !window.tripDescriptionEditorInitialized) {
                initializeTripDescriptionEditor();
                window.tripDescriptionEditorInitialized = true;
            }

            window.currentStep = step;
        }
    }

    function setupTripFormSubmission() {
        $('#tripForm').on('submit', function (e) {
            e.preventDefault();

            const formData = new FormData(this);
            const submitUrl = $(this).attr('action');

            collectTripTagifyData(formData);
            collectTripScheduleData(formData);
            collectAvailabilityData(formData);
            // Ensure best season selects are always in payload (they live in step 2, which may be hidden)
            const bestSeasonFrom = document.querySelector('select[name="best_season_from"]');
            const bestSeasonTo = document.querySelector('select[name="best_season_to"]');
            if (bestSeasonFrom) formData.set('best_season_from', bestSeasonFrom.value || '');
            if (bestSeasonTo) formData.set('best_season_to', bestSeasonTo.value || '');

            if (window.imageManagerLoaded && typeof window.imageManagerLoaded.getCroppedImages === 'function') {
                const croppedImages = window.imageManagerLoaded.getCroppedImages();
                if (croppedImages.length > 0) {
                    formData.delete('title_image[]');
                    croppedImages.forEach((imgObj, idx) => {
                        const blob = dataURLtoBlob(imgObj.dataUrl);
                        const filename = imgObj.filename || `cropped_${idx}.png`;
                        formData.append('title_image[]', blob, filename);
                    });
                }
            }

            if (window.providerImageManager && typeof window.providerImageManager.getCroppedImages === 'function') {
                const providerImages = window.providerImageManager.getCroppedImages();
                if (providerImages.length > 0) {
                    formData.delete('provider_photo');
                    const first = providerImages[0];
                    const blob = dataURLtoBlob(first.dataUrl);
                    const filename = first.filename || 'provider_photo.png';
                    formData.append('provider_photo', blob, filename);
                }
            }

            const submitBtn = $(this).find('button[type="submit"]:visible').last();
            const originalText = submitBtn.text();
            submitBtn.prop('disabled', true).text('{{ __("trips.saving") }}');

            $.ajax({
                url: submitUrl,
                type: 'POST',
                data: formData,
                processData: false,
                contentType: false,
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                success: function (response) {
                    if (response.success) {
                        showTripSuccessMessage(response.message || '{{ __("trips.saved_successfully") }}');
                        setTimeout(function () {
                            window.location.href = response.redirect_url || $('#target_redirect').val();
                        }, 1200);
                    } else {
                        showTripErrorMessage(response.message || '{{ __("trips.save_failed") }}');
                    }
                },
                error: function (xhr) {
                    let errorMessage = '{{ __("trips.save_failed") }}';
                    if (xhr.responseJSON && xhr.responseJSON.message) {
                        errorMessage = xhr.responseJSON.message;
                    } else if (xhr.responseJSON && xhr.responseJSON.errors) {
                        const errors = xhr.responseJSON.errors;
                        const errorList = Object.values(errors).flat().join('<br>');
                        errorMessage = errorList;
                    }
                    showTripErrorMessage(errorMessage);
                },
                complete: function () {
                    submitBtn.prop('disabled', false).text(originalText);
                }
            });
        });
    }

    function initializeTripTagify() {
        function init(selector, options = {}) {
            const el = document.querySelector(selector);
            if (!el) return null;
            if (el.tagify) return el.tagify;
            try {
                const t = new Tagify(el, options);
                el.tagify = t;
                return t;
            } catch (e) {
                console.error('Tagify init error', e);
                return null;
            }
        }

        /**
         * DB-backed whitelists for fields that "use our list" (from targets/methods/waters tables).
         * Format: [{id, value}] — mirrors GuidingsController / multi-step-form-script.blade.php pattern.
         * The locale-aware `value` is rendered server-side (name_en or name based on app locale).
         */
        const targetsWhitelist = {!! json_encode($targets->toArray()) !!};
        const methodsWhitelist = {!! json_encode($methods->toArray()) !!};
        const watersWhitelist  = {!! json_encode($waters->toArray()) !!};
        const boatExtrasWhitelist = {!! json_encode(($boat_extras ?? collect())->toArray()) !!};

        const dbTagifyOptions = {
            dropdown: {
                maxItems: Infinity,
                classname: 'tagify__dropdown',
                enabled: 0,
                closeOnSelect: false,
            }
        };

        // Initialize DB-backed fields with their model whitelists
        const targetSpeciesTagify = init('input[name="target_species"]', { ...dbTagifyOptions, whitelist: targetsWhitelist });
        const fishingMethodsTagify = init('input[name="fishing_methods"]', { ...dbTagifyOptions, whitelist: methodsWhitelist });
        const waterTypesTagify = init('input[name="water_types"]', { ...dbTagifyOptions, whitelist: watersWhitelist });

        // Static-but-translated presets for certain free-form fields
        const cateringOptions = {!! json_encode([
            __('trips.catering_breakfast_only'),
            __('trips.catering_half_board'),
            __('trips.catering_full_board'),
            __('trips.catering_not_included'),
        ]) !!};

        const roomRateOptions = {!! json_encode([
            __('trips.room_rate_single'),
            __('trips.room_rate_double'),
            __('trips.room_rate_twin'),
            __('trips.room_rate_shared'),
            __('trips.room_rate_suite'),
        ]) !!};

        const guideLanguagesOptions = {!! json_encode([
            __('trips.language_english'),
            __('trips.language_german'),
            __('trips.language_spanish'),
            __('trips.language_french'),
            __('trips.language_portuguese'),
        ]) !!};

        // Pre-populate DB-backed fields on edit — resolve stored IDs to names via model methods
        @if(isset($formData['is_update']) && $formData['is_update'])
            const targetSpeciesData = {!! json_encode(collect($formData['target_species'] ?? [])->pluck('name')->filter()->values()->toArray()) !!};
            if (targetSpeciesTagify && targetSpeciesData.length) {
                targetSpeciesTagify.addTags(targetSpeciesData);
            }

            const fishingMethodsData = {!! json_encode(collect($formData['fishing_methods'] ?? [])->pluck('name')->filter()->values()->toArray()) !!};
            if (fishingMethodsTagify && fishingMethodsData.length) {
                fishingMethodsTagify.addTags(fishingMethodsData);
            }

            const waterTypesData = {!! json_encode(collect($formData['water_types'] ?? [])->pluck('name')->filter()->values()->toArray()) !!};
            if (waterTypesTagify && waterTypesData.length) {
                waterTypesTagify.addTags(waterTypesData);
            }
            const boatFeaturesData = {!! json_encode(collect($formData['boat_features'] ?? [])->pluck('name')->filter()->values()->toArray()) !!};
        @endif

        // Free-form / DB-backed Tagify fields
        init('input[name="catering"]', {
            whitelist: cateringOptions,
            dropdown: {
                enabled: 0,
                closeOnSelect: false,
            },
        });
        const boatFeaturesTagify = init('input[name="boat_features"]', {
            ...dbTagifyOptions,
            whitelist: boatExtrasWhitelist,
        });
        @if(isset($formData['is_update']) && $formData['is_update'])
            if (boatFeaturesTagify && Array.isArray(boatFeaturesData) && boatFeaturesData.length) {
                boatFeaturesTagify.addTags(boatFeaturesData);
            }
        @endif
        init('input[name="room_types"]', {
            whitelist: roomRateOptions,
            dropdown: {
                enabled: 0,
                closeOnSelect: false,
            },
        });
        init('input[name="guide_languages"]', {
            whitelist: guideLanguagesOptions,
            dropdown: {
                enabled: 0,
                closeOnSelect: false,
            },
        });

        const includedPreset = $('#included').data('preset') || [];
        const excludedPreset = $('#excluded').data('preset') || [];

        init('#included', {
            whitelist: includedPreset,
            dropdown: {
                maxItems: Infinity,
                enabled: 0,
                closeOnSelect: false
            }
        });

        init('#excluded', {
            whitelist: excludedPreset,
            dropdown: {
                maxItems: Infinity,
                enabled: 0,
                closeOnSelect: false
            }
        });
    }

    function collectTripTagifyData(formData) {
        /**
         * For DB-backed fields (target_species, fishing_methods, water_types), we send the full
         * Tagify value objects [{id, value}] so the backend can extract the ID — matching how
         * GuidingsController processes its Tagify fields ($item->id ?? $item->value).
         *
         * For free-form fields, we also send full objects; the backend extracts `value` as fallback.
         */
        function collect(name) {
            const input = document.querySelector(`input[name="${name}"]`);
            if (input && input.tagify) {
                const values = input.tagify.value || [];
                formData.set(name, JSON.stringify(values));
            }
        }

        ['target_species', 'fishing_methods', 'water_types', 'catering', 'boat_features', 'room_types', 'guide_languages', 'included', 'excluded'].forEach(collect);

        // Trip highlights bullet items (min 1, max length enforced in UI)
        const highlightItems = [];
        $('#trip_highlights_container .trip-highlight-input').each(function () {
            const val = $(this).val().trim();
            if (val) {
                highlightItems.push({ text: val });
            }
        });
        if (!highlightItems.length) {
            highlightItems.push({ text: '' });
        }
        formData.set('trip_highlights_items', JSON.stringify(highlightItems));

        // Sync WYSIWYG content into FormData (FormData is built before this runs, so we must set description explicitly)
        if (typeof CKEDITOR !== 'undefined' && CKEDITOR.instances.trip_description_editor) {
            CKEDITOR.instances.trip_description_editor.updateElement();
            formData.set('description', CKEDITOR.instances.trip_description_editor.getData());
        }
    }

    function initializeTripDescriptionEditor() {
        var textarea = document.getElementById('trip_description_editor');
        if (typeof CKEDITOR !== 'undefined' && textarea && !CKEDITOR.instances.trip_description_editor) {
            CKEDITOR.replace('trip_description_editor', {
                height: 300,
                removeButtons: 'Subscript,Superscript,Anchor,Styles,Specialchar',
            });
        }
    }

    function initializeTripHighlights() {
        const container = $('#trip_highlights_container');
        const maxLength = 100;

        function renderItem(text = '') {
            const safe = $('<div>').text(text).html();
            return $(`
                <div class="input-group mb-2 trip-highlight-item">
                    <input type="text"
                           class="form-control trip-highlight-input"
                           maxlength="${maxLength}"
                           value="${safe}">
                    <button type="button" class="btn btn-outline-danger remove-highlight-btn">
                        <i class="fas fa-trash"></i>
                    </button>
                </div>
            `);
        }

        const existingItems = {!! json_encode($formData['trip_highlights']['items'] ?? []) !!};
        if (Array.isArray(existingItems) && existingItems.length) {
            existingItems.forEach(text => container.append(renderItem(text)));
        } else {
            container.append(renderItem(''));
        }

        $('#addHighlightBtn').off('click').on('click', function () {
            container.append(renderItem(''));
        });

        container.on('click', '.remove-highlight-btn', function () {
            if (container.find('.trip-highlight-item').length > 1) {
                $(this).closest('.trip-highlight-item').remove();
            }
        });

        function bindToggle(checkboxId, inputName) {
            const checkbox = $('#' + checkboxId);
            const input = $(`input[name="${inputName}"]`);
            function update() {
                if (checkbox.is(':checked')) {
                    input.show();
                } else {
                    input.hide().val('');
                }
            }
            checkbox.on('change', update);
            update();
        }

        bindToggle('highlight_accommodation_enabled', 'highlight_accommodation_nights');
        bindToggle('highlight_fishing_enabled', 'highlight_fishing_days');
        bindToggle('highlight_travel_enabled', 'highlight_travel_days');
    }

    // Reuseable toggle binding for "button as checkbox + extra input" pattern (Additional Information)
    $(document).ready(function () {
        $('.trip-additional-toggle').each(function () {
            const checkbox = $(this);
            const wrapper = checkbox.closest('.col-md-6');
            const input = wrapper.find('.trip-additional-input');

            function update() {
                if (checkbox.is(':checked')) {
                    input.show();
                } else {
                    input.hide().val('');
                }
            }

            checkbox.on('change', update);
            update();
        });
    });

    function collectTripScheduleData(formData) {
        const rows = [];
        $('#trip_schedule_container .schedule-row').each(function () {
            const time = $(this).find('input[name="trip_schedule_time[]"]').val();
            const dayLabel = $(this).find('input[name="trip_schedule_day_label[]"]').val();
            const description = $(this).find('input[name="trip_schedule_description[]"]').val();
            if (dayLabel || description || time) {
                rows.push({ time: time || null, day_label: dayLabel, description: description });
            }
        });
        formData.set('trip_schedule', JSON.stringify(rows));
    }

    function initializeScheduleRows() {
        const container = $('#trip_schedule_container');
        const existing = @json($formData['trip_schedule'] ?? []);

        function addRow(time = '', dayLabel = '', description = '') {
            const row = $(`
                <div class="schedule-row mb-2">
                    <div class="row">
                        <div class="col-md-2">
                            <input type="text" class="form-control" name="trip_schedule_time[]" placeholder="{{ __('trips.trip_schedule_time') }}" value="${time}">
                        </div>
                        <div class="col-md-3">
                            <input type="text" class="form-control" name="trip_schedule_day_label[]" placeholder="{{ __('trips.trip_schedule_day_label') }}" value="${dayLabel}">
                        </div>
                        <div class="col-md-6 mt-2 mt-md-0">
                            <input type="text" class="form-control" name="trip_schedule_description[]" placeholder="{{ __('trips.trip_schedule_description') }}" value="${description}">
                        </div>
                        <div class="col-md-1 mt-2 mt-md-0 d-flex align-items-center">
                            <button type="button" class="btn btn-sm btn-outline-danger remove-schedule-row">
                                <i class="fas fa-trash"></i>
                            </button>
                        </div>
                    </div>
                </div>
            `);
            container.append(row);
        }

        if (Array.isArray(existing) && existing.length) {
            existing.forEach(r => addRow(r.time || '', r.day_label || '', r.description || ''));
        } else {
            addRow();
        }

        $('#addScheduleRowBtn').on('click', function () {
            addRow();
        });

        container.on('click', '.remove-schedule-row', function () {
            $(this).closest('.schedule-row').remove();
        });
    }

    function initializeAvailabilityTable() {
        const tbody = $('#availabilityTable tbody');
        const statusOptions = @json($availabilityStatusOptions ?? ['available','limited','sold_out']);
        const existing = @json($formData['availability_dates'] ?? []);

        const today = new Date();
        const todayStr = today.toISOString().substring(0, 10);

        function renderStatusSelect(selected) {
            let html = '<select class="form-select form-select-sm availability-status-select" name="availability_dates[][status]">';
            statusOptions.forEach(function (opt) {
                const label = opt.replace('_', ' ');
                html += `<option value="${opt}" ${selected === opt ? 'selected' : ''}>${label.charAt(0).toUpperCase() + label.slice(1)}</option>`;
            });
            html += '</select>';
            return html;
        }

        function addRow(date = '', spots = '', status = 'available') {
            const row = $(`
                <tr>
                    <td><input type="date" class="form-control form-control-sm availability-date-input" name="availability_dates[][departure_date]" value="${date}"></td>
                    <td><input type="number" min="0" class="form-control form-control-sm" name="availability_dates[][spots_available]" value="${spots}"></td>
                    <td>${renderStatusSelect(status)}</td>
                    <td class="text-end">
                        <button type="button" class="btn btn-sm btn-outline-danger remove-availability-row">
                            <i class="fas fa-trash"></i>
                        </button>
                    </td>
                </tr>
            `);
            tbody.append(row);

            // Enforce min date (no past dates)
            const dateInput = row.find('.availability-date-input');
            dateInput.attr('min', todayStr);

            // Prevent duplicate dates
            dateInput.on('change', function () {
                const current = $(this);
                const val = current.val();
                if (!val) return;

                let duplicateFound = false;
                $('.availability-date-input').not(current).each(function () {
                    if ($(this).val() === val) {
                        duplicateFound = true;
                    }
                });

                if (duplicateFound) {
                    alert('This departure date is already added. Please choose a different date.');
                    current.val('');
                }
            });
        }

        if (Array.isArray(existing) && existing.length) {
            existing.forEach(function (row) {
                const date = row.departure_date?.date ? row.departure_date.date.substring(0, 10) : (row.departure_date || '');
                addRow(date, row.spots_available ?? 0, row.status || 'available');
            });
        } else {
            addRow();
        }

        $('#addAvailabilityRowBtn').on('click', function () {
            addRow();
        });

        tbody.on('click', '.remove-availability-row', function () {
            $(this).closest('tr').remove();
        });
    }

    function collectAvailabilityData(formData) {
        const rows = [];
        $('#availabilityTable tbody tr').each(function () {
            const date = $(this).find('input[name="availability_dates[][departure_date]"]').val();
            const spots = $(this).find('input[name="availability_dates[][spots_available]"]').val();
            const status = $(this).find('select[name="availability_dates[][status]"]').val();
            if (date) {
                rows.push({
                    departure_date: date,
                    spots_available: spots || 0,
                    status: status || 'available'
                });
            }
        });
        formData.set('availability_dates', JSON.stringify(rows));
    }

    function initializeLocationAutocompleteForTrips() {
        const locationInput = $('#location');
        const latitudeInput = $('#latitude');
        const longitudeInput = $('#longitude');
        const countryInput = $('#country');
        const cityInput = $('#cityField');
        const regionInput = $('#regionField');

        if (typeof google !== 'undefined' && google.maps) {
            const autocomplete = new google.maps.places.Autocomplete(locationInput[0], {
                types: ['geocode'],
            });

            autocomplete.addListener('place_changed', function () {
                const place = autocomplete.getPlace();

                if (place.geometry && place.geometry.location) {
                    latitudeInput.val(place.geometry.location.lat());
                    longitudeInput.val(place.geometry.location.lng());

                    const addressComponents = place.address_components || [];
                    addressComponents.forEach(component => {
                        if (component.types.includes('country')) {
                            countryInput.val(component.long_name);
                        }
                        if (component.types.includes('locality')) {
                            cityInput.val(component.long_name);
                        }
                        if (component.types.includes('administrative_area_level_1')) {
                            regionInput.val(component.long_name);
                        }
                    });
                }
            });
        }
    }

    function dataURLtoBlob(dataurl) {
        const arr = dataurl.split(',');
        const mime = arr[0].match(/:(.*?);/)[1];
        const bstr = atob(arr[1]);
        let n = bstr.length;
        const u8arr = new Uint8Array(n);
        while (n--) {
            u8arr[n] = bstr.charCodeAt(n);
        }
        return new Blob([u8arr], {type: mime});
    }

    function showTripSuccessMessage(message) {
        const container = $('#trip-error-container');
        const html =
            '<div class="alert alert-success alert-dismissible fade show" role="alert">' +
            '<i class="fas fa-check-circle me-2"></i>' +
            message +
            '<button type="button" class="btn-close" data-bs-dismiss="alert"></button>' +
            '</div>';
        container.html(html).show();
        $('html, body').animate({scrollTop: container.offset().top - 100}, 400);
    }

    function showTripErrorMessage(message) {
        const container = $('#trip-error-container');
        const html =
            '<div class="alert alert-danger alert-dismissible fade show" role="alert">' +
            '<i class="fas fa-exclamation-circle me-2"></i>' +
            message +
            '<button type="button" class="btn-close" data-bs-dismiss="alert"></button>' +
            '</div>';
        container.html(html).show();
        $('html, body').animate({scrollTop: container.offset().top - 100}, 400);
    }

    $(document).ready(function () {
        $('[data-bs-toggle="tooltip"]').tooltip();
    });
</script>
@endpush

