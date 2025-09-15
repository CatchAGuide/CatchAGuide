@push('js_push')
<script src="https://cdnjs.cloudflare.com/ajax/libs/cropperjs/1.5.12/cropper.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/@yaireo/tagify"></script>
<script src="https://cdn.jsdelivr.net/npm/browser-image-compression@latest/dist/browser-image-compression.js"></script>
<script src="{{ asset('assets/js/ImageManager.js') }}"></script>

<script>
    window.imageManagerLoaded = window.imageManagerLoaded || null;
    window.currentStep = window.currentStep || 1;
    window.totalSteps = window.totalSteps || 6;
    window.autocomplete = window.autocomplete || null;
    window.city = window.city || null;
    window.region = window.region || null;
    window.country = window.country || null;
    window.errorMapping = window.errorMapping || {
        title: { field: 'Title', step: 1 },
        title_image: { field: 'Gallery Image', step: 1 },
        primaryImage: { field: 'Primary Image', step: 1 },
        location: { field: 'Location', step: 1 },
        boat_type: { field: 'Boat Type', step: 2 },
        desc_of_boat: { field: 'Boat Description', step: 2 },
        base_price: { field: 'Base Price', step: 5 },
        price_type: { field: 'Price Type', step: 5 },
        status: { field: 'Status', step: 6 },
        booking_advance: { field: 'Booking Advance', step: 6 }
    };

    // Initialize form when document is ready
    $(document).ready(function() {
        initializeRentalBoatForm();
    });

    function initializeRentalBoatForm() {
        // Initialize image manager using the same pattern as guidings
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

        // Initialize number inputs validation
        initializeNumberInputs();
        
        // Initialize tagify for tags input
        initializeTagify();

        // Initialize extra pricing functionality
        initializeExtraPricing();

        // Set up step navigation
        setupStepNavigation();

        // Set up form submission
        setupFormSubmission();

        // Load existing data if in edit mode
        if (document.getElementById('is_update').value == '1') {
            loadExistingData();
        }

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

    function initializeNumberInputs() {
        // Handle number input validation to prevent "not focusable" errors
        const capacityInput = document.getElementById('capacity');
        const yearInput = document.getElementById('year');
        
        if (capacityInput) {
            capacityInput.addEventListener('input', function() {
                if (this.value === '') {
                    this.removeAttribute('min');
                } else {
                    this.setAttribute('min', '1');
                }
            });
        }
        
        if (yearInput) {
            yearInput.addEventListener('input', function() {
                if (this.value === '') {
                    this.removeAttribute('min');
                } else {
                    this.setAttribute('min', '1900');
                }
            });
        }
    }

    function initializeTagify() {
        // Initialize tagify for boat extras with predefined options
        @if(isset($boatExtras) && count($boatExtras) > 0)
        if (document.getElementById('boat_extras')) {
            const boatExtrasTagify = initTagify('#boat_extras', {
                whitelist: {!! json_encode(collect($boatExtras)->sortBy('value')->values()->toArray()) !!},
                dropdown: {
                    maxItems: Infinity,
                    classname: "tagify__dropdown",
                    enabled: 0,
                    closeOnSelect: false
                },
                placeholder: 'Add boat extras...',
                delimiters: ',|',
                maxTags: 10
            });
            
            // Populate with existing data
            @if(isset($formData['boat_extras']) && is_array($formData['boat_extras']))
            const boatExtrasData = {!! json_encode(collect($formData['boat_extras'])->pluck('name')->toArray()) !!};
            if (boatExtrasTagify && boatExtrasData) {
                boatExtrasTagify.addTags(boatExtrasData.filter(Boolean));
            }
            @endif
        }
        @else
        // Initialize tagify for boat extras without predefined options
        if (document.getElementById('boat_extras')) {
            initTagify('#boat_extras', {
                placeholder: 'Add boat extras...',
                delimiters: ',|',
                maxTags: 10
            });
        }
        @endif

        // Initialize tagify for inclusions with predefined options
        @if(isset($inclusions) && count($inclusions) > 0)
        if (document.getElementById('inclusions')) {
            const inclusionsTagify = initTagify('#inclusions', {
                whitelist: {!! json_encode(collect($inclusions)->sortBy('value')->values()->toArray()) !!},
                dropdown: {
                    maxItems: Infinity,
                    classname: "tagify__dropdown",
                    enabled: 0,
                    closeOnSelect: false
                },
                placeholder: 'Add inclusions...',
                delimiters: ',|',
                maxTags: 15
            });
            
            // Populate with existing data
            @if(isset($formData['inclusions']) && is_array($formData['inclusions']))
            const inclusionsData = {!! json_encode(collect($formData['inclusions'])->pluck('name')->toArray()) !!};
            if (inclusionsTagify && inclusionsData) {
                inclusionsTagify.addTags(inclusionsData.filter(Boolean));
            }
            @endif
        }
        @else
        // Initialize tagify for inclusions without predefined options
        if (document.getElementById('inclusions')) {
            initTagify('#inclusions', {
                placeholder: 'Add inclusions...',
                delimiters: ',|',
                maxTags: 15
            });
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

    function initializeExtraPricing() {
        let extraPricingCount = 0;

        // Add extra pricing button
        document.getElementById('add-extra-pricing')?.addEventListener('click', function() {
            addExtraPricingItem();
        });

        function addExtraPricingItem() {
            const container = document.getElementById('extra-pricing-container');
            const item = document.createElement('div');
            item.className = 'extra-pricing-item';
            item.innerHTML = `
                <div class="form-group">
                    <label class="form-label">Item Name</label>
                    <input type="text" class="form-control" name="extra_pricing[${extraPricingCount}][name]" placeholder="e.g., Captain, Fuel, etc.">
                </div>
                <div class="form-group">
                    <label class="form-label">Price</label>
                    <div class="input-group">
                        <span class="input-group-text">€</span>
                        <input type="number" class="form-control" name="extra_pricing[${extraPricingCount}][price]" placeholder="0.00" step="0.01" min="0">
                    </div>
                </div>
                <button type="button" class="remove-extra" onclick="removeExtraPricingItem(this)">×</button>
            `;
            container.appendChild(item);
            extraPricingCount++;
        }

        // Remove extra pricing item
        window.removeExtraPricingItem = function(button) {
            button.parentElement.remove();
        };
    }

    function setupStepNavigation() {
        // Next button handlers
        $(document).off('click', '[id^="nextBtn"]').on('click', '[id^="nextBtn"]', async function(e) {
            e.preventDefault();
            e.stopPropagation();
            
            showLoadingScreen();
            
            setTimeout(async () => {
                try {
                    if (validateStep(currentStep)) {
                        const nextStep = currentStep + 1;
                        await showStep(nextStep);
                    } else {
                        hideLoadingScreen();
                    }
                } catch (error) {
                    console.error('Error during step transition:', error);
                    hideLoadingScreen();
                }
            }, 100);
        });

        // Previous button handlers
        $(document).off('click', '[id^="prevBtn"]').on('click', '[id^="prevBtn"]', function(e) {
            e.preventDefault();
            e.stopPropagation();
            showStep(currentStep - 1);
        });

        // Step button handlers
        document.querySelectorAll('.step-button').forEach(button => {
            button.addEventListener('click', async function() {
                const targetStep = parseInt(this.dataset.step);
                const currentStepElement = document.querySelector('.step.active');
                const currentStepNumber = parseInt(currentStepElement.id.replace('step', ''));

                if (targetStep < currentStepNumber || targetStep === currentStepNumber + 1) {
                    showLoadingScreen();
                    
                    setTimeout(async () => {
                        try {
                            if (targetStep > currentStepNumber && !validateStep(currentStepNumber)) {
                                hideLoadingScreen();
                                return;
                            }
                            await showStep(targetStep);
                        } catch (error) {
                            console.error('Error during step button navigation:', error);
                            hideLoadingScreen();
                        }
                    }, 100);
                }
            });
        });

        // Draft save buttons
        $(document).off('click', '[id^="saveDraftBtn"]').on('click', '[id^="saveDraftBtn"]', function(e) {
            e.preventDefault();
            document.getElementById('is_draft').value = '1';
            document.getElementById('rentalBoatForm').submit();
        });
    }

    function setupFormSubmission() {
        // Submit button handlers
        $(document).on('click', '[id^="submitBtn"]', function(e) {
            showLoadingScreen();
        });

        // Form submit handler
        document.getElementById('rentalBoatForm').addEventListener('submit', function(e) {
            if (validateAllSteps()) {
                showLoadingScreen();
                // Process images before submission
                if (window.imageManagerLoaded && typeof window.imageManagerLoaded.getCroppedImages === 'function') {
                    const croppedImages = window.imageManagerLoaded.getCroppedImages();
                    if (croppedImages.length > 0) {
                        // Remove any existing title_image[] from FormData
                        const formData = new FormData(this);
                        formData.delete('title_image[]');
                        croppedImages.forEach((imgObj, idx) => {
                            // Convert dataURL to Blob
                            const blob = dataURLtoBlob(imgObj.dataUrl);
                            const filename = imgObj.filename || `cropped_${idx}.png`;
                            formData.append('title_image[]', blob, filename);
                        });
                    }
                }
            } else {
                e.preventDefault();
                hideLoadingScreen();
            }
        });
    }

    // Helper function to convert data URL to Blob (same as guidings)
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
                if (!document.getElementById('boat_type').value) {
                    errors.push('Please select a boat type.');
                    isValid = false;
                }
                if (!document.getElementById('desc_of_boat').value.trim()) {
                    errors.push('Boat description is required.');
                    isValid = false;
                }
                break;

            case 3:
                // Boat information is optional, but validate if provided
                const capacity = document.getElementById('capacity');
                if (capacity && capacity.value && (isNaN(capacity.value) || capacity.value < 1)) {
                    errors.push('Capacity must be a positive number.');
                    isValid = false;
                }
                break;

            case 4:
                // Requirements and inclusions are optional
                break;

            case 5:
                if (!document.querySelector('input[name="price_type"]:checked')) {
                    errors.push('Please select a price type.');
                    isValid = false;
                }
                if (!document.getElementById('base_price').value.trim()) {
                    errors.push('Base price is required.');
                    isValid = false;
                }
                const basePrice = parseFloat(document.getElementById('base_price').value);
                if (isNaN(basePrice) || basePrice < 0) {
                    errors.push('Base price must be a valid positive number.');
                    isValid = false;
                }
                break;

            case 6:
                if (!document.querySelector('input[name="status"]:checked')) {
                    errors.push('Please select availability status.');
                    isValid = false;
                }
                if (!document.querySelector('input[name="booking_advance"]:checked')) {
                    errors.push('Please select booking advance requirement.');
                    isValid = false;
                }
                break;
        }

        if (!isValid) {
            hideLoadingScreen();
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

    async function showStep(stepNumber) {
        if (stepNumber < 1 || stepNumber > totalSteps) {
            console.error('Invalid step number:', stepNumber);
            hideLoadingScreen();
            return;
        }

        if (stepNumber > currentStep && !validateStep(currentStep)) {
            console.error('Validation failed for current step');
            hideLoadingScreen();
            return;
        }

        // Save progress when moving forward
        if (stepNumber > currentStep) {
            try {
                await saveStepProgress(currentStep);
            } catch (error) {
                console.error('Error saving step progress:', error);
                hideLoadingScreen();
                return;
            }
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

        hideLoadingScreen();
    }

    function updateButtonVisibility(stepNumber) {
        // Hide all submit buttons
        document.querySelectorAll('[id^="submitBtn"]').forEach(btn => {
            btn.style.display = 'none';
        });

        // Show appropriate buttons
        $(`#prevBtn${stepNumber}`).toggle(stepNumber > 1);
        $(`#nextBtn${stepNumber}`).toggle(stepNumber < totalSteps);
        $(`#submitBtn${stepNumber}`).toggle(stepNumber === totalSteps);
    }

    async function saveStepProgress(step) {
        // This would typically make an AJAX call to save the current step data
        // For now, we'll just simulate a delay
        return new Promise(resolve => {
            setTimeout(resolve, 500);
        });
    }

    function loadExistingData() {
        // Load existing form data for edit mode
        const formData = @json($formData ?? []);
        
        // Load images if they exist
        if (formData.gallery_images && Array.isArray(formData.gallery_images)) {
            // This would load existing images into the preview
            console.log('Loading existing images:', formData.gallery_images);
        }

        // Load other form data
        if (formData.boat_information) {
            Object.keys(formData.boat_information).forEach(key => {
                const input = document.querySelector(`input[name="boat_info[${key}]"], select[name="boat_info[${key}]"]`);
                if (input) {
                    input.value = formData.boat_information[key];
                }
            });
        }

        // Load tagify data
        if (formData.boat_extras && document.getElementById('boat_extras').tagify) {
            document.getElementById('boat_extras').tagify.addTags(formData.boat_extras);
        }

        if (formData.inclusions && document.getElementById('inclusions').tagify) {
            document.getElementById('inclusions').tagify.addTags(formData.inclusions);
        }
    }

    function showLoadingScreen() {
        if (document.getElementById('loadingScreen')) return;

        const loadingScreen = document.createElement('div');
        loadingScreen.id = 'loadingScreen';
        loadingScreen.className = 'loading-screen';
        loadingScreen.innerHTML = `
            <div class="loading-content">
                <div class="spinner"></div>
                <div style="font-size: 1.2rem; font-weight: 500;">Processing...</div>
            </div>
        `;
        document.body.appendChild(loadingScreen);
    }

    function hideLoadingScreen() {
        const loadingScreen = document.getElementById('loadingScreen');
        if (loadingScreen) {
            loadingScreen.remove();
        }
    }
</script>
@endpush
