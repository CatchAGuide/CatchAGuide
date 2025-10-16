@push('js_push')
<script src="https://cdnjs.cloudflare.com/ajax/libs/cropperjs/1.5.12/cropper.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/@yaireo/tagify"></script>
<script src="https://cdn.jsdelivr.net/npm/browser-image-compression@latest/dist/browser-image-compression.js"></script>
<script src="{{ asset('assets/js/ImageManager.js') }}"></script>

@push('styles')
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@yaireo/tagify/dist/tagify.css">
@endpush

<script>
    window.imageManagerLoaded = window.imageManagerLoaded || null;
    window.currentStep = window.currentStep || 1;
    window.totalSteps = window.totalSteps || 7;
    window.autocomplete = window.autocomplete || null;
    window.city = window.city || null;
    window.region = window.region || null;
    window.country = window.country || null;
    window.errorMapping = window.errorMapping || {
        title: { field: 'Title', step: 1 },
        title_image: { field: 'Gallery Image', step: 1 },
        primaryImage: { field: 'Primary Image', step: 1 },
        location: { field: 'Location', step: 1 },
        description_camp: { field: 'Camp Description', step: 2 },
        description_area: { field: 'Area Description', step: 2 },
        description_fishing: { field: 'Fishing Description', step: 2 },
        camp_facility_checkboxes: { field: 'Camp Facilities', step: 3 }
    };

    // Initialize form when document is ready
    $(document).ready(function() {
        initializeCampForm();
    });

    function initializeCampForm() {
        // Initialize image manager using the same pattern as rental boats
        if (typeof ImageManager !== 'undefined' && !window.imageManagerLoaded) {
            window.imageManagerLoaded = new ImageManager('#croppedImagesContainer', '#title_image');
            
            if (document.getElementById('is_update').value === '1') {
                const existingImagesInput = document.getElementById('existing_images');
                const thumbnailPath = document.getElementById('thumbnail_path').value;
                
                if (existingImagesInput && existingImagesInput.value) {
                    window.imageManagerLoaded.loadExistingImages(existingImagesInput.value, thumbnailPath);
                }
            }
        }

        // Initialize location autocomplete
        initializeLocationAutocomplete();
        
        // Initialize Select2 for multi-select dropdowns
        initializeSelect2();
        
        // Initialize tagify for camp facilities
        setTimeout(() => {
            if (typeof Tagify !== 'undefined') {
                initializeTagify();
            } else {
                console.error('Tagify library not loaded, retrying in 500ms...');
                setTimeout(() => {
                    if (typeof Tagify !== 'undefined') {
                        initializeTagify();
                    } else {
                        console.error('Tagify library still not loaded after retry');
                    }
                }, 500);
            }
        }, 500);
        
        // Initialize selected guidings cards if there are any pre-selected
        updateSelectedGuidingsCards();
        
        // Set up step navigation
        setupStepNavigation();
        
        // Set up form submission
        setupFormSubmission();
        
        // Load existing data if in edit mode
        if (document.getElementById('is_update').value == '1') {
            setTimeout(() => {
                loadExistingData();
            }, 100);
        }

        // Initialize checkbox functionality
        initializeCheckboxes();

        // Add additional file input event listener for multiple file selection
        const imageUploadInput = document.getElementById('title_image');
        if (imageUploadInput) {
            imageUploadInput.addEventListener('change', function(event) {
                if (window.imageManagerLoaded) {
                    try {
                        window.imageManagerLoaded.handleFileSelect(event.target.files);
                    } catch (error) {
                        console.error('Error in handleFileSelect:', error);
                    }
                } else {
                    console.error('ImageManager not initialized');
                }
            });
        }
    }

function setupStepNavigation() {
    const totalSteps = 7;
    let currentStep = 1;
    
    // Step navigation - Allow free switching between all steps
    $('.step-button').click(function() {
        const step = parseInt($(this).data('step'));
        goToStep(step);
    });
    
    // Next button handlers
    for (let i = 1; i <= totalSteps; i++) {
        $(`#nextBtn${i}`).click(function() {
            if (validateStep(i)) {
                if (i < totalSteps) {
                    goToStep(i + 1);
                } else {
                    submitForm();
                }
            }
        });
    }
    
    // Previous button handlers
    for (let i = 2; i <= totalSteps; i++) {
        $(`#prevBtn${i}`).click(function() {
            goToStep(i - 1);
        });
    }
    
    // Save draft handlers
    for (let i = 2; i <= totalSteps; i++) {
        $(`#saveDraftBtn${i}`).click(function() {
            saveDraft();
        });
    }
    
    function goToStep(step) {
        // Hide all steps
        $('.step').removeClass('active');
        $('.step-button').removeClass('active');
        
        // Show current step
        $(`#step${step}`).addClass('active');
        $(`.step-button[data-step="${step}"]`).addClass('active');
        
        // Update step button states based on completion
        $('.step-button').each(function() {
            const stepNum = parseInt($(this).data('step'));
            $(this).removeClass('completed');
            
            // Mark as completed if step has been visited and has required data
            if (stepNum < step && isStepCompleted(stepNum)) {
                $(this).addClass('completed');
            }
        });
        
        currentStep = step;
        
        // Show/hide submit buttons
        $('.step').each(function() {
            const stepNum = $(this).attr('id').replace('step', '');
            if (stepNum == step && step == totalSteps) {
                $(this).find('button[type="submit"]').show();
                $(this).find('#nextBtn' + stepNum).hide();
            } else {
                $(this).find('button[type="submit"]').hide();
                $(this).find('#nextBtn' + stepNum).show();
            }
        });
    }
    
    function validateStep(step) {
        let isValid = true;
        const errorContainer = $('#error-container');
        errorContainer.hide();
        
        // Step 1 validation
        if (step === 1) {
            const title = $('#title').val().trim();
            const location = $('#location').val().trim();
            const images = $('#imagePreviewContainer .image-preview').length;
            
            if (!title) {
                showError('{{ __("camps.title_required") }}');
                isValid = false;
            } else if (!location) {
                showError('{{ __("camps.location_required") }}');
                isValid = false;
            } else if (images < 5) {
                showError('{{ __("camps.min_images_required") }}');
                isValid = false;
            }
        }
        
        // Step 2 validation
        if (step === 2) {
            const descriptionCamp = $('#description_camp').val().trim();
            const descriptionArea = $('#description_area').val().trim();
            const descriptionFishing = $('#description_fishing').val().trim();
            
            if (!descriptionCamp) {
                showError('{{ __("camps.description_camp_required") }}');
                isValid = false;
            } else if (!descriptionArea) {
                showError('{{ __("camps.description_area_required") }}');
                isValid = false;
            } else if (!descriptionFishing) {
                showError('{{ __("camps.description_fishing_required") }}');
                isValid = false;
            }
        }
        
        // Step 3 validation
        if (step === 3) {
            const facilitiesInput = document.querySelector('input[name="camp_facilities"]');
            let facilities = [];
            if (facilitiesInput && facilitiesInput.tagify) {
                facilities = facilitiesInput.tagify.value;
            }
            if (facilities.length === 0) {
                showError('{{ __("camps.at_least_one_facility_required") }}');
                isValid = false;
            }
        }
        
        return isValid;
    }
    
    function showError(message) {
        const errorContainer = $('#error-container');
        errorContainer.html('<ul><li>' + message + '</li></ul>');
        errorContainer.show();
        $('html, body').animate({
            scrollTop: errorContainer.offset().top - 100
        }, 500);
    }
    
    function saveDraft() {
        $('#is_draft').val('1');
        $('#campForm').submit();
    }
    
    function submitForm() {
        $('#is_draft').val('0');
        $('#campForm').submit();
    }
}

function setupFormSubmission() {
    $('#campForm').on('submit', function(e) {
        e.preventDefault();
        
        const formData = new FormData(this);
        const submitUrl = $(this).attr('action');
        const method = $('input[name="_method"]').val() || 'POST';
        
        // Show loading state
        const submitBtn = $(this).find('button[type="submit"]');
        const originalText = submitBtn.text();
        submitBtn.prop('disabled', true).text('{{ __("camps.saving") }}...');
        
        $.ajax({
            url: submitUrl,
            type: method,
            data: formData,
            processData: false,
            contentType: false,
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            success: function(response) {
                if (response.success) {
                    showSuccessMessage(response.message || '{{ __("camps.saved_successfully") }}');
                    setTimeout(function() {
                        window.location.href = response.redirect || $('#target_redirect').val();
                    }, 1500);
                } else {
                    showErrorMessage(response.message || '{{ __("camps.save_failed") }}');
                }
            },
            error: function(xhr) {
                let errorMessage = '{{ __("camps.save_failed") }}';
                
                if (xhr.responseJSON && xhr.responseJSON.message) {
                    errorMessage = xhr.responseJSON.message;
                } else if (xhr.responseJSON && xhr.responseJSON.errors) {
                    const errors = xhr.responseJSON.errors;
                    const errorList = Object.values(errors).flat().join('<br>');
                    errorMessage = errorList;
                }
                
                showErrorMessage(errorMessage);
            },
            complete: function() {
                submitBtn.prop('disabled', false).text(originalText);
            }
        });
    });
}

function initializeSelect2() {
    // Initialize Select2 for multi-select dropdowns (excluding target_fish which is now a textarea)
    $('#accommodations, #rental_boats, #guidings').select2({
        placeholder: '{{ __("camps.select_options") }}',
        allowClear: true,
        width: '100%'
    });

    // Add change event listener for guidings to show cards
    $('#guidings').on('change', function() {
        updateSelectedGuidingsCards();
    });
}

function updateSelectedGuidingsCards() {
    const selectedIds = $('#guidings').val() || [];
    const container = $('#selected-guidings-container');
    const cardsContainer = $('#selected-guidings-cards');
    
    console.log('updateSelectedGuidingsCards called with IDs:', selectedIds);
    
    if (selectedIds.length === 0) {
        container.hide();
        return;
    }
    
    // Show loading state
    cardsContainer.html('<div class="col-12 text-center"><div class="spinner-border text-primary" role="status"><span class="visually-hidden">Loading...</span></div></div>');
    container.show();
    
    // Fetch guiding data and display cards
    $.ajax({
        url: '{{ route("guidings.generate-cards") }}',
        method: 'POST',
        data: {
            guiding_ids: selectedIds,
            _token: $('meta[name="csrf-token"]').attr('content')
        },
        success: function(response) {
            if (response.success) {
                // Create a grid layout for the cards
                cardsContainer.html('<div class="row">' + response.html + '</div>');
                
                // Apply compact layout based on number of cards
                const cardCount = cardsContainer.find('.col-lg-4').length;
                console.log('Card count:', cardCount);
                
                container.removeClass('compact-2-cards compact-3-cards');
                
                if (cardCount === 2) {
                    container.addClass('compact-2-cards');
                    console.log('Applied compact-2-cards class');
                } else if (cardCount === 3) {
                    container.addClass('compact-3-cards');
                    console.log('Applied compact-3-cards class');
                }
                
                // Initialize carousels for the new cards
                initializeCarousels(cardsContainer[0]);
                initializeLazyLoading(cardsContainer[0]);
            } else {
                cardsContainer.html('<div class="col-12"><div class="alert alert-warning">Failed to load guiding cards</div></div>');
            }
        },
        error: function(xhr, status, error) {
            console.error('AJAX Error:', xhr.responseText);
            cardsContainer.html('<div class="col-12"><div class="alert alert-danger">Error loading guiding cards: ' + error + '</div></div>');
        }
    });
}

function initializeCarousels(container) {
    // Initialize Bootstrap carousels if Bootstrap is available
    if (typeof bootstrap !== 'undefined') {
        const carousels = container.querySelectorAll('.carousel');
        carousels.forEach(carousel => {
            new bootstrap.Carousel(carousel, {
                interval: false,
                wrap: true
            });
        });
    }
}

function initializeLazyLoading(container) {
    // Initialize lazy loading for images if the lazy loading library is available
    if (typeof LazyLoad !== 'undefined') {
        const lazyLoadInstance = new LazyLoad({
            elements_selector: ".lazy"
        });
        lazyLoadInstance.update();
    }
}


function initializeTagify() {
    console.log('Initializing Tagify...');
    
    // Initialize tagify for camp facilities
    @if(isset($campFacilities) && count($campFacilities) > 0)
    const campFacilitiesData = {!! json_encode($campFacilities->map(function($item) { return ['value' => $item->name, 'id' => $item->id]; })->sortBy('value')->values()->toArray()) !!};
    console.log('Camp facilities data:', campFacilitiesData);
    
    const campFacilitiesTagify = initTagify('input[name="camp_facilities"]', {
        whitelist: campFacilitiesData,
        dropdown: {
            maxItems: Infinity,
            classname: "tagify__dropdown",
            enabled: 0,
            closeOnSelect: false
        }
    });
    
    // Populate with existing data
    @if(isset($formData['facilities']) && is_array($formData['facilities']))
    const facilitiesData = {!! json_encode($formData['facilities']) !!};
    console.log('Existing facilities data:', facilitiesData);
    if (campFacilitiesTagify && facilitiesData && Array.isArray(facilitiesData)) {
        campFacilitiesTagify.addTags(facilitiesData.filter(Boolean));
    }
    @endif
    @else
    console.log('No camp facilities data available');
    initTagify('input[name="camp_facilities"]', {});
    @endif

    // Initialize tagify for target fish
    @if(isset($targetFish) && count($targetFish) > 0)
    const targetFishData = {!! json_encode($targetFish->map(function($item) { return ['value' => $item->name, 'id' => $item->id]; })->sortBy('value')->values()->toArray()) !!};
    console.log('Target fish data:', targetFishData);
    
    const targetFishTagify = initTagify('input[name="target_fish"]', {
        whitelist: targetFishData,
        dropdown: {
            maxItems: Infinity,
            classname: "tagify__dropdown",
            enabled: 0,
            closeOnSelect: false
        }
    });
    
    // Populate with existing data
    @if(isset($formData['target_fish']) && !empty($formData['target_fish']))
    const existingTargetFish = {!! json_encode(explode(',', $formData['target_fish'])) !!};
    console.log('Existing target fish data:', existingTargetFish);
    if (targetFishTagify && existingTargetFish && Array.isArray(existingTargetFish)) {
        targetFishTagify.addTags(existingTargetFish.filter(Boolean));
    }
    @endif
    @else
    console.log('No target fish data available');
    initTagify('input[name="target_fish"]', {});
    @endif

    // Initialize tagify for extras (without predefined options)
    const extrasTagify = initTagify('input[name="extras"]', {});
    console.log('Extras tagify initialized');
    
    // Populate with existing data
    @if(isset($formData['extras']) && !empty($formData['extras']))
    const existingExtras = {!! json_encode(explode(',', $formData['extras'])) !!};
    console.log('Existing extras data:', existingExtras);
    if (extrasTagify && existingExtras && Array.isArray(existingExtras)) {
        extrasTagify.addTags(existingExtras.filter(Boolean));
    }
    @endif
}

function initTagify(selector, options = {}) {
    console.log('initTagify called with selector:', selector, 'options:', options);
    const element = document.querySelector(selector);
    console.log('Found element:', element);
    
    if (!element) {
        console.error('Element not found for selector:', selector);
        return null;
    }
    
    if (element.tagify) {
        console.log('Tagify already initialized on element');
        return element.tagify;
    }
    
    try {
        const tagify = new Tagify(element, options);
        element.tagify = tagify;
        console.log('Tagify initialized successfully on element:', element);
        return tagify;
    } catch (error) {
        console.error('Error initializing Tagify:', error);
        return null;
    }
}

function initializeCheckboxes() {
    // Handle tagify functionality for camp facilities
    const facilitiesInput = document.querySelector('input[name="camp_facilities"]');
    if (facilitiesInput && facilitiesInput.tagify) {
        facilitiesInput.tagify.on('add remove', function() {
            updateStepCompletionStatus();
        });
    }

    // Handle tagify functionality for target fish
    const targetFishInput = document.querySelector('input[name="target_fish"]');
    if (targetFishInput && targetFishInput.tagify) {
        targetFishInput.tagify.on('add remove', function() {
            updateStepCompletionStatus();
        });
    }

    // Handle tagify functionality for extras
    const extrasInput = document.querySelector('input[name="extras"]');
    if (extrasInput && extrasInput.tagify) {
        extrasInput.tagify.on('add remove', function() {
            updateStepCompletionStatus();
        });
    }
}

function loadExistingData() {
    // Load existing form data for editing
    if (window.imageManagerLoaded) {
        const existingImages = $('#existing_images').val();
        const thumbnailPath = $('#thumbnail_path').val();
        
        if (existingImages) {
            try {
                const images = JSON.parse(existingImages);
                window.imageManagerLoaded.loadExistingImages(images, thumbnailPath);
            } catch (e) {
                console.error('Error loading existing images:', e);
            }
        }
    }
}

function initializeLocationAutocomplete() {
    const locationInput = $('#location');
    const latitudeInput = $('#latitude');
    const longitudeInput = $('#longitude');
    const countryInput = $('#country');
    const cityInput = $('#city');
    const regionInput = $('#region');
    
    // Initialize Google Places Autocomplete
    if (typeof google !== 'undefined' && google.maps) {
        const autocomplete = new google.maps.places.Autocomplete(locationInput[0], {
            types: ['establishment', 'geocode'],
            componentRestrictions: { country: ['de', 'at', 'ch', 'it', 'fr', 'es', 'pt', 'nl', 'be', 'dk', 'se', 'no', 'fi', 'pl', 'cz', 'sk', 'hu', 'si', 'hr', 'ro', 'bg', 'gr', 'cy', 'mt', 'lu', 'ie', 'gb'] }
        });
        
        autocomplete.addListener('place_changed', function() {
            const place = autocomplete.getPlace();
            
            if (place.geometry && place.geometry.location) {
                latitudeInput.val(place.geometry.location.lat());
                longitudeInput.val(place.geometry.location.lng());
                
                // Extract address components
                const addressComponents = place.address_components;
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

function addStepCompletionListeners() {
    // Step 1 listeners
    $('#title, #location').on('input change', function() {
        updateStepCompletionStatus();
    });
    
    // Step 2 listeners
    $('#description_camp, #description_area, #description_fishing').on('input change', function() {
        updateStepCompletionStatus();
    });
    
    // Step 3 listeners
    $('input[name="camp_facility_checkboxes[]"]').on('change', function() {
        updateStepCompletionStatus();
    });
    
    // Image upload listeners
    $(document).on('DOMNodeInserted', '#imagePreviewContainer', function() {
        updateStepCompletionStatus();
    });
}

function isStepCompleted(step) {
    // Check if step has been completed based on form data
    switch(step) {
        case 1:
            return $('#title').val().trim() !== '' && 
                   $('#location').val().trim() !== '' && 
                   $('#imagePreviewContainer .image-preview').length >= 5;
        case 2:
            return $('#description_camp').val().trim() !== '' && 
                   $('#description_area').val().trim() !== '' && 
                   $('#description_fishing').val().trim() !== '';
        case 3:
            const facilitiesInput = document.querySelector('input[name="camp_facilities"]');
            if (facilitiesInput && facilitiesInput.tagify) {
                return facilitiesInput.tagify.value.length > 0;
            }
            return false;
        case 4:
            return true; // Target fish and travel info are optional
        case 5:
            return true; // Accommodations are optional
        case 6:
            return true; // Rental boats are optional
        case 7:
            return true; // Guidings are optional
        default:
            return false;
    }
}

function updateStepCompletionStatus() {
    // Update step button states based on current completion status
    $('.step-button').each(function() {
        const stepNum = parseInt($(this).data('step'));
        const isCompleted = isStepCompleted(stepNum);
        
        if (isCompleted) {
            $(this).addClass('completed');
        } else {
            $(this).removeClass('completed');
        }
    });
}


function showSuccessMessage(message) {
    const alertHtml = `
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <i class="fas fa-check-circle me-2"></i>
            ${message}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    `;
    
    $('#error-container').html(alertHtml);
    $('#error-container').show();
    
    $('html, body').animate({
        scrollTop: $('#error-container').offset().top - 100
    }, 500);
}

function showErrorMessage(message) {
    const alertHtml = `
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <i class="fas fa-exclamation-circle me-2"></i>
            ${message}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    `;
    
    $('#error-container').html(alertHtml);
    $('#error-container').show();
    
    $('html, body').animate({
        scrollTop: $('#error-container').offset().top - 100
    }, 500);
}

// Initialize tooltips
$(document).ready(function() {
    $('[data-bs-toggle="tooltip"]').tooltip();
});
</script>
@endpush
