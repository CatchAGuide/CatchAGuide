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

<script>
    window.imageManagerLoaded = window.imageManagerLoaded || null;
    window.currentStep = window.currentStep || 1;
    window.totalSteps = window.totalSteps || 6;
    window.autocomplete = window.autocomplete || null;
    window.city = window.city || null;
    window.region = window.region || null;
    window.country = window.country || null;

    // Initialize form when document is ready - exactly like rental-boat form
    document.addEventListener('DOMContentLoaded', function() {
        // Wait for jQuery to be available
        if (typeof $ !== 'undefined') {
        initializeAccommodationForm();
        } else {
            // Retry after a short delay
            setTimeout(function() {
                if (typeof $ !== 'undefined') {
                    initializeAccommodationForm();
                } else {
                    console.error('jQuery not available');
                }
            }, 100);
        }
    });

    function initializeAccommodationForm() {
        // Prevent multiple initialization
        if (window.accommodationFormInitialized) {
            console.log('⚠️ Accommodation form already initialized, skipping...');
            return;
        }
        
        window.accommodationFormInitialized = true;
        // Initialize image manager using the same pattern as guidings
        function initializeImageManager() {
        if (typeof ImageManager !== 'undefined' && !window.imageManagerLoaded) {
                try {
            window.imageManagerLoaded = new ImageManager('#croppedImagesContainer', '#title_image');
                } catch (e) {
                    console.error('Error creating ImageManager:', e);
                    window.imageManagerLoaded = null;
                }
            } else if (typeof ImageManager === 'undefined') {
                // Wait for ImageManager to load
                setTimeout(initializeImageManager, 100);
            }
        }
        
        // Start initialization
        initializeImageManager();
        
        // Load existing images if editing using ImageManager
            if (document.getElementById('is_update').value === '1') {
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
        }

    function loadExistingFormData() {
        // Load existing form data if editing
        const formData = @json($formData ?? []);
        
        if (!formData || !formData.is_update) {
            return;
        }

        // Load accommodation type (radio buttons)
        if (formData.accommodation_type) {
            const accommodationTypeField = document.querySelector(`input[name="accommodation_type"][id="accommodation_type_${formData.accommodation_type}"]`);
            if (accommodationTypeField) {
                accommodationTypeField.checked = true;
            }
        }


        // Load distance fields (matching actual form field names)
        if (formData.distance_to_water_m) {
            const fieldElement = document.querySelector('input[name="distance_to_water"]');
            if (fieldElement) {
                fieldElement.value = formData.distance_to_water_m;
            }
        }

        if (formData.distance_to_boat_berth_m) {
            const fieldElement = document.querySelector('input[name="distance_to_boat_mooring"]');
            if (fieldElement) {
                fieldElement.value = formData.distance_to_boat_berth_m;
            }
        }

        if (formData.distance_to_parking_m) {
            const fieldElement = document.querySelector('input[name="distance_to_parking_lot"]');
            if (fieldElement) {
                fieldElement.value = formData.distance_to_parking_m;
            }
        }

        // Load pricing data
        if (formData.price_per_night) {
            const fieldElement = document.querySelector('input[name="price_per_night"]');
            if (fieldElement) {
                fieldElement.value = formData.price_per_night;
            }
        }

        if (formData.price_per_week) {
            const fieldElement = document.querySelector('input[name="price_per_week"]');
            if (fieldElement) {
                fieldElement.value = formData.price_per_week;
            }
        }

        // Load other form fields
        if (formData.description) {
            const fieldElement = document.querySelector('textarea[name="description"]');
            if (fieldElement) {
                fieldElement.value = formData.description;
            }
        }

        if (formData.condition_or_style) {
            const fieldElement = document.querySelector('input[name="condition_or_style"]');
            if (fieldElement) {
                fieldElement.value = formData.condition_or_style;
            }
        }

        if (formData.living_area_sqm) {
            const fieldElement = document.querySelector('input[name="living_area_sqm"]');
            if (fieldElement) {
                fieldElement.value = formData.living_area_sqm;
            }
        }

        if (formData.max_occupancy) {
            const fieldElement = document.querySelector('input[name="max_occupancy"]');
            if (fieldElement) {
                fieldElement.value = formData.max_occupancy;
            }
        }

        if (formData.number_of_bedrooms) {
            const fieldElement = document.querySelector('input[name="number_of_bedrooms"]');
            if (fieldElement) {
                fieldElement.value = formData.number_of_bedrooms;
            }
        }

        if (formData.location_description) {
            const fieldElement = document.querySelector('textarea[name="location_description"]');
            if (fieldElement) {
                fieldElement.value = formData.location_description;
            }
        }

        if (formData.changeover_day) {
            const fieldElement = document.querySelector('input[name="changeover_day"]');
            if (fieldElement) {
                fieldElement.value = formData.changeover_day;
            }
        }

        if (formData.minimum_stay_nights) {
            const fieldElement = document.querySelector('input[name="minimum_stay_nights"]');
            if (fieldElement) {
                fieldElement.value = formData.minimum_stay_nights;
            }
        }

        // Load pricing checkboxes based on existing data
        if (formData.price_per_night && parseFloat(formData.price_per_night) > 0) {
            const perNightCheckbox = document.querySelector('input[name="price_type_checkboxes[]"][value="per_night"]');
            if (perNightCheckbox) {
                perNightCheckbox.checked = true;
                const container = perNightCheckbox.closest('.btn-checkbox-container');
                if (container) {
                    const label = container.querySelector('label');
                    const inputGroup = container.querySelector('.input-group');
                    if (label) label.classList.add('active');
                    if (inputGroup) inputGroup.style.display = 'block';
                }
            }
        }

        if (formData.price_per_week && parseFloat(formData.price_per_week) > 0) {
            const perWeekCheckbox = document.querySelector('input[name="price_type_checkboxes[]"][value="per_week"]');
            if (perWeekCheckbox) {
                perWeekCheckbox.checked = true;
                const container = perWeekCheckbox.closest('.btn-checkbox-container');
                if (container) {
                    const label = container.querySelector('label');
                    const inputGroup = container.querySelector('.input-group');
                    if (label) label.classList.add('active');
                    if (inputGroup) inputGroup.style.display = 'block';
                }
            }
        }

        // Load accommodation details checkboxes
        if (formData.accommodation_details && Array.isArray(formData.accommodation_details)) {
            formData.accommodation_details.forEach(detail => {
                if (detail.id && detail.value) {
                    const checkbox = document.querySelector(`input[name="accommodation_detail_checkboxes[]"][value="${detail.id}"]`);
                    if (checkbox) {
                        checkbox.checked = true;
                        const container = checkbox.closest('.btn-checkbox-container');
                        if (container) {
                            const label = container.querySelector('label');
                            const input = container.querySelector('input.extra-input');
                            if (label) label.classList.add('active');
                            if (input) input.value = detail.value;
                        }
                    }
                }
            });
        }

        // Load room configurations checkboxes
        if (formData.room_configurations && Array.isArray(formData.room_configurations)) {
            formData.room_configurations.forEach(config => {
                if (config.id && config.value) {
                    const checkbox = document.querySelector(`input[name="room_config_checkboxes[]"][value="${config.id}"]`);
                    if (checkbox) {
                        checkbox.checked = true;
                        const container = checkbox.closest('.btn-checkbox-container');
                        if (container) {
                            const label = container.querySelector('label');
                            const input = container.querySelector('input.extra-input');
                            if (label) label.classList.add('active');
                            if (input) input.value = config.value;
                        }
                    }
                }
            });
        }

        // Load rental conditions checkboxes
        if (formData.rental_conditions && Array.isArray(formData.rental_conditions)) {
            formData.rental_conditions.forEach(condition => {
                if (condition.id && condition.value) {
                    const checkbox = document.querySelector(`input[name="rental_condition_checkboxes[]"][value="${condition.id}"]`);
                    if (checkbox) {
                        checkbox.checked = true;
                        const container = checkbox.closest('.btn-checkbox-container');
                        if (container) {
                            const label = container.querySelector('label');
                            const input = container.querySelector('input.extra-input');
                            if (label) label.classList.add('active');
                            if (input) input.value = condition.value;
                        }
                    }
                }
            });
        }
    }

    function fixPriceTypeUI() {
        // Wait for DOM to be ready
        setTimeout(function() {
            const inputGroups = document.querySelectorAll('.pricing-types-grid .btn-checkbox-container .input-group');
            
            inputGroups.forEach(function(inputGroup, index) {
                // Make input group visible
                inputGroup.style.display = 'block';
                inputGroup.style.width = '100%';
                inputGroup.style.marginTop = '10px';
                inputGroup.style.opacity = '1';
                inputGroup.style.visibility = 'visible';
                
                // Fix input field
                const input = inputGroup.querySelector('input');
                if (input) {
                    input.style.width = '100%';
                    input.style.minWidth = '120px';
                    input.style.height = '40px';
                    input.style.padding = '8px 12px';
                    input.style.fontSize = '14px';
                    input.style.border = '1px solid #ddd';
                    input.style.borderRadius = '4px';
                    input.style.backgroundColor = '#fff';
                }
                
                // Fix input group text (Euro symbol)
                const inputGroupText = inputGroup.querySelector('.input-group-text');
                if (inputGroupText) {
                    inputGroupText.style.height = '40px';
                    inputGroupText.style.padding = '8px 12px';
                    inputGroupText.style.fontSize = '14px';
                    inputGroupText.style.backgroundColor = '#f8f9fa';
                    inputGroupText.style.border = '1px solid #ddd';
                }
            });
        }, 100);
        }

        // Initialize location autocomplete
        initializeLocationAutocomplete();
        
        // Load all existing form data if editing (after functions are defined)
        loadExistingFormData();
        
        // Fix Price Type UI immediately
        fixPriceTypeUI();

        // Initialize tagify for tags input with a small delay
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
        }, 100);

        // Set up step navigation
        setupStepNavigation();

        // Set up form submission
        setupFormSubmission();

        // Initialize radio button functionality
        initializeRadioButtons();

        // Initialize checkbox functionality (wait for jQuery) - only once
        if (!window.checkboxesInitialized) {
            setTimeout(function() {
                if (typeof $ !== 'undefined' && !window.checkboxesInitialized) {
                    window.checkboxesInitialized = true;
        initializeCheckboxes();
                    initializePricingCheckboxes();
                } else if (!window.checkboxesInitialized) {
                    console.log('❌ jQuery not available, retrying...');
                    // Retry after another delay
                    setTimeout(function() {
                        if (typeof $ !== 'undefined' && !window.checkboxesInitialized) {
                            window.checkboxesInitialized = true;
                            initializeCheckboxes();
                            initializePricingCheckboxes();
                        }
                    }, 500);
                }
            }, 200);
        }

        // Initialize radio button styling
        initializeRadioButtonStyling();

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
        } else {
            console.error('File input element not found');
        }
    }

    function initializeLocationAutocomplete() {
        const locationInput = document.getElementById('location');
        if (locationInput && typeof google !== 'undefined') {
                const autocomplete = new google.maps.places.Autocomplete(locationInput, {
                    types: ['establishment', 'geocode'],
                    fields: ['place_id', 'formatted_address', 'geometry', 'address_components']
                });

            autocomplete.addListener('place_changed', function() {
                    const place = autocomplete.getPlace();
                    if (place.geometry) {
                        document.getElementById('latitude').value = place.geometry.location.lat();
                        document.getElementById('longitude').value = place.geometry.location.lng();
                        
                        // Extract address components
                        const addressComponents = place.address_components;
                        let country = '', city = '', region = '';
                        
                            addressComponents.forEach(component => {
                                if (component.types.includes('country')) {
                                    country = component.long_name;
                                }
                                if (component.types.includes('locality') || component.types.includes('administrative_area_level_1')) {
                                    city = component.long_name;
                                }
                                if (component.types.includes('administrative_area_level_1')) {
                                    region = component.long_name;
                                }
                            });
                        
                        document.getElementById('country').value = country;
                        document.getElementById('city').value = city;
                        document.getElementById('region').value = region;
                    }
                });
        }
    }

    function initializeTagify() {
        // Initialize tagify for facilities - EXACT COPY from guidings form
        @if(isset($facilities) && count($facilities) > 0)
        if (document.getElementById('facilities')) {
            const facilitiesTagify = initTagify('#facilities', {
                whitelist: {!! json_encode($facilities->sortBy('value')->values()->toArray()) !!},
                dropdown: {
                    maxItems: Infinity,
                    classname: "tagify__dropdown",
                    enabled: 0,
                    closeOnSelect: false
                }
            });
            
            // Populate with existing data
            @if(isset($formData['amenities']) && is_array($formData['amenities']))
            const facilitiesData = {!! json_encode($formData['amenities']) !!};
            if (facilitiesTagify && facilitiesData && Array.isArray(facilitiesData)) {
                // Handle malformed JSON data by parsing each item
                const parsedData = facilitiesData.map(item => {
                    if (typeof item === 'string') {
                        try {
                            return JSON.parse(item);
                        } catch (e) {
                            return { value: item, id: Math.random() };
                        }
                    }
                    return item;
                }).filter(Boolean);
                facilitiesTagify.addTags(parsedData);
            }
            @endif
        }
        @else
        // Initialize tagify for facilities without predefined options
        if (document.getElementById('facilities')) {
            initTagify('#facilities', {});
        }
        @endif

        // Initialize tagify for kitchen equipment
        @if(isset($kitchenEquipment) && count($kitchenEquipment) > 0)
        if (document.getElementById('kitchen_equipment')) {
            const kitchenEquipmentTagify = initTagify('#kitchen_equipment', {
                whitelist: {!! json_encode($kitchenEquipment->sortBy('value')->values()->toArray()) !!},
                dropdown: {
                    maxItems: Infinity,
                    classname: "tagify__dropdown",
                    enabled: 0,
                    closeOnSelect: false
                }
            });
            
            // Populate with existing data
            @if(isset($formData['kitchen_equipment']) && is_array($formData['kitchen_equipment']))
            const kitchenEquipmentData = {!! json_encode($formData['kitchen_equipment']) !!};
            if (kitchenEquipmentTagify && kitchenEquipmentData && Array.isArray(kitchenEquipmentData)) {
                const parsedData = kitchenEquipmentData.map(item => {
                    if (typeof item === 'string') {
                        try {
                            return JSON.parse(item);
                        } catch (e) {
                            return { value: item, id: Math.random() };
                        }
                    }
                    return item;
                }).filter(Boolean);
                kitchenEquipmentTagify.addTags(parsedData);
            }
            @endif
        }
        @else
        if (document.getElementById('kitchen_equipment')) {
            initTagify('#kitchen_equipment', {});
        }
        @endif

        // Initialize tagify for bathroom amenities
        @if(isset($bathroomAmenities) && count($bathroomAmenities) > 0)
        if (document.getElementById('bathroom_amenities')) {
            const bathroomAmenitiesTagify = initTagify('#bathroom_amenities', {
                whitelist: {!! json_encode($bathroomAmenities->sortBy('value')->values()->toArray()) !!},
                dropdown: {
                    maxItems: Infinity,
                    classname: "tagify__dropdown",
                    enabled: 0,
                    closeOnSelect: false
                }
            });
            
            // Populate with existing data
            @if(isset($formData['bathroom_amenities']) && is_array($formData['bathroom_amenities']))
            const bathroomAmenitiesData = {!! json_encode($formData['bathroom_amenities']) !!};
            if (bathroomAmenitiesTagify && bathroomAmenitiesData && Array.isArray(bathroomAmenitiesData)) {
                const parsedData = bathroomAmenitiesData.map(item => {
                    if (typeof item === 'string') {
                        try {
                            return JSON.parse(item);
                        } catch (e) {
                            return { value: item, id: Math.random() };
                        }
                    }
                    return item;
                }).filter(Boolean);
                bathroomAmenitiesTagify.addTags(parsedData);
            }
            @endif
        }
        @else
        if (document.getElementById('bathroom_amenities')) {
            initTagify('#bathroom_amenities', {});
        }
        @endif

        // Initialize tagify for policies
        @if(isset($accommodationPolicies) && count($accommodationPolicies) > 0)
        if (document.getElementById('policies')) {
            const policiesTagify = initTagify('#policies', {
                whitelist: {!! json_encode($accommodationPolicies->map(function($item) { return ['value' => $item->name, 'id' => $item->id]; })->sortBy('value')->values()->toArray()) !!},
                dropdown: {
                    maxItems: Infinity,
                    classname: "tagify__dropdown",
                    enabled: 0,
                    closeOnSelect: false
                }
            });
            
            // Populate with existing data
            @if(isset($formData['policies']) && is_array($formData['policies']))
            const policiesData = {!! json_encode($formData['policies']) !!};
            if (policiesTagify && policiesData && Array.isArray(policiesData)) {
                const parsedData = policiesData.map(item => {
                    if (typeof item === 'string') {
                        try {
                            return JSON.parse(item);
                        } catch (e) {
                            return { value: item, id: Math.random() };
                        }
                    }
                    return item;
                }).filter(Boolean);
                policiesTagify.addTags(parsedData);
            }
            @endif
        }
        @else
        if (document.getElementById('policies')) {
            initTagify('#policies', {});
        }
        @endif

        // Initialize tagify for extras
        @if(isset($accommodationExtras) && count($accommodationExtras) > 0)
        if (document.getElementById('extras')) {
            const extrasTagify = initTagify('#extras', {
                whitelist: {!! json_encode($accommodationExtras->map(function($item) { return ['value' => $item->name, 'id' => $item->id]; })->sortBy('value')->values()->toArray()) !!},
                dropdown: {
                    maxItems: Infinity,
                    classname: "tagify__dropdown",
                    enabled: 0,
                    closeOnSelect: false
                }
            });
            
            // Populate with existing data
            @if(isset($formData['extras']) && is_array($formData['extras']))
            const extrasData = {!! json_encode($formData['extras']) !!};
            if (extrasTagify && extrasData && Array.isArray(extrasData)) {
                const parsedData = extrasData.map(item => {
                    if (typeof item === 'string') {
                        try {
                            return JSON.parse(item);
                        } catch (e) {
                            return { value: item, id: Math.random() };
                        }
                    }
                    return item;
                }).filter(Boolean);
                extrasTagify.addTags(parsedData);
            }
            @endif
        }
        @else
        if (document.getElementById('extras')) {
            initTagify('#extras', {});
        }
        @endif

        // Initialize tagify for inclusives
        @if(isset($accommodationInclusives) && count($accommodationInclusives) > 0)
        if (document.getElementById('inclusives')) {
            const inclusivesTagify = initTagify('#inclusives', {
                whitelist: {!! json_encode($accommodationInclusives->map(function($item) { return ['value' => $item->name, 'id' => $item->id]; })->sortBy('value')->values()->toArray()) !!},
                dropdown: {
                    maxItems: Infinity,
                    classname: "tagify__dropdown",
                    enabled: 0,
                    closeOnSelect: false
                }
            });
            
            // Populate with existing data
            @if(isset($formData['inclusives']) && is_array($formData['inclusives']))
            const inclusivesData = {!! json_encode($formData['inclusives']) !!};
            if (inclusivesTagify && inclusivesData && Array.isArray(inclusivesData)) {
                const parsedData = inclusivesData.map(item => {
                    if (typeof item === 'string') {
                        try {
                            return JSON.parse(item);
                        } catch (e) {
                            return { value: item, id: Math.random() };
                        }
                    }
                    return item;
                }).filter(Boolean);
                inclusivesTagify.addTags(parsedData);
            }
            @endif
        }
        @else
        if (document.getElementById('inclusives')) {
            initTagify('#inclusives', {});
        }
        @endif
    }

    function initTagify(selector, options = {}) {
        const element = document.querySelector(selector);
        if (element && !element.tagify) {
            const tagify = new Tagify(element, options);
            element.tagify = tagify;
            return tagify;
        }
        return element.tagify;
    }

    function initializeRadioButtons() {
        // Radio button functionality for accommodation types and other radio groups
        document.querySelectorAll('input[type="radio"]').forEach(radio => {
            radio.addEventListener('change', function() {
                const groupToggle = this.closest('.btn-group-toggle');
                if (groupToggle) {
                    groupToggle.querySelectorAll('label').forEach(label => label.classList.remove('active'));
                    const nextLabel = this.nextElementSibling;
                    if (nextLabel && nextLabel.tagName === 'LABEL') {
                        nextLabel.classList.add('active');
                    }
                }
            });
        });
    }

    function setupStepNavigation() {
        // Next button handlers
        document.querySelectorAll('[id^="nextBtn"]').forEach(button => {
            button.addEventListener('click', function(e) {
                e.preventDefault();
                e.stopPropagation();
                
                if (validateStep(currentStep)) {
                    const nextStep = currentStep + 1;
                    showStep(nextStep);
                }
            });
        });

        // Previous button handlers
        document.querySelectorAll('[id^="prevBtn"]').forEach(button => {
            button.addEventListener('click', function(e) {
                e.preventDefault();
                e.stopPropagation();
                showStep(currentStep - 1);
            });
        });

        // Step button handlers - allow free movement
        document.querySelectorAll('.step-button').forEach(button => {
            button.addEventListener('click', function() {
                const targetStep = parseInt(this.dataset.step);
                showStep(targetStep);
            });
        });
    }

    function setupFormSubmission() {
        // Prevent multiple initialization
        if (window.formSubmissionSetup) {
            console.log('⚠️ Form submission already setup, skipping...');
            return;
        }
        
        window.formSubmissionSetup = true;
        
        // Remove any existing event listeners to prevent duplicates
        document.querySelectorAll('[id^="submitBtn"]').forEach(button => {
            button.removeEventListener('click', handleSubmit);
        });
        
        const form = document.getElementById('accommodationForm');
        if (form) {
            form.removeEventListener('submit', handleSubmit);
        }
        
        // Submit button handlers
        document.querySelectorAll('[id^="submitBtn"]').forEach(button => {
            button.addEventListener('click', function(e) {
                e.preventDefault();
                handleSubmit(e);
            });
        });

        // Form submit handler
        if (form) {
            form.addEventListener('submit', function(e) {
                e.preventDefault();
                handleSubmit(e);
            });
        }

        // Draft save buttons
        document.querySelectorAll('[id^="saveDraftBtn"]').forEach(button => {
            button.addEventListener('click', function(e) {
                e.preventDefault();
                saveDraft();
            });
        });
    }

    function handleSubmit(event) {
        const form = document.getElementById('accommodationForm');
        const isDraft = document.getElementById('is_draft').value === '1';
        
        if (isDraft || validateAllSteps()) {
            submitForm(form);
        } else {
            // Scroll to error container if there are validation errors
            const errorContainer = document.getElementById('error-container');
            if (errorContainer && errorContainer.style.display === 'block') {
                errorContainer.scrollIntoView({ behavior: 'smooth', block: 'center' });
            }
        }
    }

    async function saveDraft() {
        const form = document.getElementById('accommodationForm');
        if (!form) {
            console.error('Form not found');
            return;
        }

        try {
            const formData = new FormData(form);
            
            // Force draft mode
            formData.set('is_draft', '1');
            formData.set('current_step', currentStep);
            formData.set('status', 'draft');

            // Always append these if present
            const accommodationId = document.getElementById('accommodation_id')?.value;
            const isUpdate = document.getElementById('is_update')?.value;
            if (accommodationId) formData.set('accommodation_id', accommodationId);
            if (isUpdate) formData.set('is_update', isUpdate);

            // Append cropped images as files if available
            if (window.imageManagerLoaded && typeof window.imageManagerLoaded.getCroppedImages === 'function') {
                const croppedImages = window.imageManagerLoaded.getCroppedImages();
                if (croppedImages.length > 0) {
                    // Remove any existing title_image[] from FormData
                    formData.delete('title_image[]');
                    croppedImages.forEach((imgObj, idx) => {
                        // Convert dataURL to Blob
                        const blob = dataURLtoBlob(imgObj.dataUrl);
                        const filename = imgObj.filename || `cropped_${idx}.png`;
                        formData.append('title_image[]', blob, filename);
                    });
                }
            }

            // Submit the form
            const response = await fetch(form.action, {
                method: 'POST', // Always use POST for Laravel - _method field handles PUT/PATCH/DELETE
                body: formData,
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                    'Accept': 'application/json',
                },
            });

            const data = await response.json();

            if (data.success) {
                if (data.redirect_url) {
                    window.location.href = data.redirect_url;
                } else {
                    // Update form with new accommodation ID if it's a new record
                    if (data.accommodation_id) {
                        const accommodationIdInput = document.getElementById('accommodation_id');
                        const isUpdateInput = document.getElementById('is_update');
                        if (accommodationIdInput) accommodationIdInput.value = data.accommodation_id;
                        if (isUpdateInput) isUpdateInput.value = '1';
                    }
                    alert(data.message || 'Draft saved successfully!');
                }
            } else {
                throw new Error(data.message || 'Failed to save draft');
            }
        } catch (error) {
            console.error('Error saving draft:', error);
            alert('Error saving draft: ' + error.message);
        }
    }

    function submitForm(form) {
        const formData = new FormData(form);
        
        // Process images before submission
        if (window.imageManagerLoaded && typeof window.imageManagerLoaded.getCroppedImages === 'function') {
            const croppedImages = window.imageManagerLoaded.getCroppedImages();
            if (croppedImages.length > 0) {
                // Remove any existing title_image[] from FormData
                formData.delete('title_image[]');
                croppedImages.forEach((imgObj, idx) => {
                    // Convert dataURL to Blob
                    const blob = dataURLtoBlob(imgObj.dataUrl);
                    const filename = imgObj.filename || `cropped_${idx}.png`;
                    formData.append('title_image[]', blob, filename);
                });
            }
        }

        // Submit the form
        fetch(form.action, {
            method: 'POST', // Always use POST for Laravel - _method field handles PUT/PATCH/DELETE
            body: formData,
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                'Accept': 'application/json',
            },
        })
        .then(response => {
            if (!response.ok) {
                return response.text().then(text => {
                    try {
                        return JSON.parse(text);
                    } catch (e) {
                        throw new Error(text);
                    }
                });
            }
            return response.json();
        })
        .then(data => {
            if (data.redirect_url) {
                window.location.href = data.redirect_url;
            } else {
                displayValidationErrors(data.errors);
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Error submitting form: ' + error.message);
        });
    }

    function displayValidationErrors(errors) {
        const errorContainer = document.getElementById('error-container');
        if (errors && Object.keys(errors).length > 0) {
            let errorHtml = '<ul>';
            Object.values(errors).forEach(errorArray => {
                if (Array.isArray(errorArray)) {
                    errorArray.forEach(error => {
                        errorHtml += `<li>${error}</li>`;
                    });
                } else {
                    errorHtml += `<li>${errorArray}</li>`;
                }
            });
            errorHtml += '</ul>';
            errorContainer.innerHTML = errorHtml;
            errorContainer.style.display = 'block';
            errorContainer.scrollIntoView({ behavior: 'smooth', block: 'center' });
        }
    }

    // Helper function to convert data URL to Blob
    function dataURLtoBlob(dataurl) {
        const arr = dataurl.split(',');
        const mime = arr[0].match(/:(.*?);/)[1];
        const bstr = atob(arr[1]);
        let n = bstr.length;
        const u8arr = new Uint8Array(n);
        while (n--) {
            u8arr[n] = bstr.charCodeAt(n);
        }
        return new Blob([u8arr], { type: mime });
    }

    function validateStep(step) {
        // DISABLED: Always return true to allow proceeding through steps without validation
        return true;
        
        const errorContainer = document.getElementById('error-container');
        errorContainer.style.display = 'none';
        errorContainer.innerHTML = '';
        let isValid = true;
        let errors = [];

        // Check if it's a draft submission
        const isDraft = document.querySelector('input[name="is_draft"]');
        if (isDraft && isDraft.value === '1') {
        return true;
    }

        switch(step) {
            case 1:
                const fileInput = document.getElementById('title_image');
                const previewWrappers = document.querySelectorAll('.image-preview-wrapper');
                
                if (!fileInput.files.length && !previewWrappers.length) {
                    errors.push('Please upload at least one image.');
                    isValid = false;
                }

                if (!document.getElementById('location').value.trim()) {
                    errors.push('Location is required.');
                    isValid = false;
                }
                if (!document.getElementById('title').value.trim()) {
                    errors.push('Title is required.');
                isValid = false;
                }
                break;

            case 2:
                if (!document.querySelector('input[name="accommodation_type"]:checked')) {
                    errors.push('Please select an accommodation type.');
                    isValid = false;
                }
                break;

            case 3:
            case 4:
            case 5:
            case 6:
                // These steps are optional
                break;
        }

        if (!isValid) {
            errorContainer.style.display = 'block';
            errorContainer.innerHTML = '<ul>' + errors.map(error => `<li>${error}</li>`).join('') + '</ul>';
            return false;
        }

        return true;
    }

    function validateAllSteps() {
        for (let i = 1; i <= totalSteps; i++) {
            if (!validateStep(i)) {
                showStep(i);
                return false;
            }
        }
        return true;
    }

    function showStep(stepNumber) {
        if (stepNumber < 1 || stepNumber > totalSteps) {
            console.error('Invalid step number:', stepNumber);
            return;
        }

        // Hide all steps
        document.querySelectorAll('.step').forEach(step => {
            step.classList.remove('active');
        });

        // Show target step
        document.getElementById(`step${stepNumber}`).classList.add('active');

        // Update step buttons
        document.querySelectorAll('.step-button').forEach((button, index) => {
            button.classList.remove('active', 'completed');
            if (index + 1 === stepNumber) {
                button.classList.add('active');
            } else if (index + 1 < stepNumber) {
                button.classList.add('completed');
            }
        });

        // Update current step
        currentStep = stepNumber;

        // Update button visibility
        updateButtonVisibility(stepNumber);
    }

    function updateButtonVisibility(stepNumber) {
        // Hide all submit buttons
        document.querySelectorAll('[id^="submitBtn"]').forEach(btn => {
            btn.style.display = 'none';
        });

        // Show appropriate buttons
        const prevBtn = document.getElementById(`prevBtn${stepNumber}`);
        const nextBtn = document.getElementById(`nextBtn${stepNumber}`);
        const submitBtn = document.getElementById(`submitBtn${stepNumber}`);
        
        if (prevBtn) prevBtn.style.display = stepNumber > 1 ? 'block' : 'none';
        if (nextBtn) nextBtn.style.display = stepNumber < totalSteps ? 'block' : 'none';
        if (submitBtn) submitBtn.style.display = stepNumber === totalSteps ? 'block' : 'none';
    }

    function initializeCheckboxes() {
        // Checkbox with additional fields functionality - exactly like rental-boat form
        $('.btn-checkbox-container input[type="checkbox"]').change(function() {
            var $container = $(this).closest('.btn-checkbox-container');
            var $label = $container.find('label');
            var $input = $container.find('.extra-input');

            if (this.checked) {
                $label.addClass('active');
                if ($input.length > 0) {
                    $input.show();
                    $input.prop('required', true);
                }
            } else {
                $label.removeClass('active');
                if ($input.length > 0) {
                    $input.hide();
                    $input.prop('required', false);
                    $input.val('');
                }
            }
        });

        // Initialize checkbox states on page load
        $('.btn-checkbox-container input[type="checkbox"]').each(function() {
            var $container = $(this).closest('.btn-checkbox-container');
            var $label = $container.find('label');
            var $input = $container.find('.extra-input');
            
            if (this.checked) {
                $label.addClass('active');
                if ($input.length > 0) {
                    $input.show();
                }
            } else {
                if ($input.length > 0) {
                    $input.hide();
                }
            }
        });

        // Add click handlers to labels to toggle checkboxes
        $('.btn-checkbox-container label').click(function(e) {
            e.preventDefault();
            var $checkbox = $(this).prev('input[type="checkbox"]');
            $checkbox.prop('checked', !$checkbox.prop('checked'));
            $checkbox.trigger('change');
        });
    }

    function initializeRadioButtonStyling() {
        // Initialize radio button states on page load
        $('input[type="radio"]').each(function() {
            var $label = $(this).next('label');
            if (this.checked) {
                $label.addClass('active');
            }
        });

        // Add change handlers for radio buttons
        $('input[type="radio"]').change(function() {
            var name = $(this).attr('name');
            $(`input[name="${name}"]`).next('label').removeClass('active');
            if (this.checked) {
                $(this).next('label').addClass('active');
            }
        });

        // Add click handlers for radio button labels
        $('.btn-checkbox').click(function(e) {
            e.preventDefault();
            var $radio = $(this).prev('input[type="radio"]');
            if ($radio.length) {
                $radio.prop('checked', true);
                $radio.trigger('change');
            }
        });
    }

    function initializePricingCheckboxes() {
        // Remove any existing event listeners to prevent duplicates
        $('.pricing-types-grid .btn-checkbox-container input[type="checkbox"]').off('change.pricing');
        
        // Pricing checkbox functionality - exactly like rental boats
        $('.pricing-types-grid .btn-checkbox-container input[type="checkbox"]').on('change.pricing', function() {
            var $container = $(this).closest('.btn-checkbox-container');
            var $label = $container.find('label');
            var $inputGroup = $container.find('.input-group');

            if (this.checked) {
                $label.addClass('active');
                $inputGroup.show();
            } else {
                $label.removeClass('active');
                $inputGroup.hide();
                // Clear the input value when unchecked
                $inputGroup.find('input').val('');
            }
        });

        // Initialize checkbox states on page load
        $('.pricing-types-grid .btn-checkbox-container input[type="checkbox"]').each(function() {
            var $container = $(this).closest('.btn-checkbox-container');
            var $label = $container.find('label');
            var $inputGroup = $container.find('.input-group');
            
            if (this.checked) {
                $label.addClass('active');
                $inputGroup.show();
            } else {
                $label.removeClass('active');
                $inputGroup.hide();
            }
        });
        
        // Make sure input groups are visible by default (show them)
        $('.pricing-types-grid .btn-checkbox-container .input-group').show();
        
        // Fix Price Type UI - make input fields visible and properly sized
        $('.pricing-types-grid .btn-checkbox-container .input-group').css({
            'display': 'block',
            'width': '100%',
            'margin-top': '10px'
        });
        
        $('.pricing-types-grid .btn-checkbox-container .input-group input').css({
            'width': '100%',
            'min-width': '120px',
            'height': '40px',
            'padding': '8px 12px',
            'font-size': '14px',
            'border': '1px solid #ddd',
            'border-radius': '4px'
        });
        
        $('.pricing-types-grid .btn-checkbox-container .input-group-text').css({
            'height': '40px',
            'padding': '8px 12px',
            'font-size': '14px'
        });

        // Load existing pricing data if editing
        loadPricingData();
    }

    function loadPricingData() {
        // Load pricing data from form data (for editing)
        const formData = @json($formData ?? []);
        
        if (formData.price_per_night && parseFloat(formData.price_per_night) > 0) {
            const checkbox = document.querySelector('input[name="price_type_checkboxes[]"][value="per_night"]');
            const priceInput = document.querySelector('input[name="price_per_night"]');
            
            if (checkbox && priceInput) {
                checkbox.checked = true;
                checkbox.dispatchEvent(new Event('change'));
                priceInput.value = formData.price_per_night;
            }
        }
        
        if (formData.price_per_week && parseFloat(formData.price_per_week) > 0) {
            const checkbox = document.querySelector('input[name="price_type_checkboxes[]"][value="per_week"]');
            const priceInput = document.querySelector('input[name="price_per_week"]');
            
            if (checkbox && priceInput) {
                checkbox.checked = true;
                checkbox.dispatchEvent(new Event('change'));
                priceInput.value = formData.price_per_week;
            }
        }
    }
</script>
@endpush
