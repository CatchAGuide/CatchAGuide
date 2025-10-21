@push('js_push')
<script src="https://cdnjs.cloudflare.com/ajax/libs/cropperjs/1.5.12/cropper.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/@yaireo/tagify"></script>
<script src="https://cdn.jsdelivr.net/npm/browser-image-compression@latest/dist/browser-image-compression.js"></script>
<script src="{{ asset('assets/js/ImageManager.js') }}"></script>

<script>
    window.imageManagerLoaded = window.imageManagerLoaded || null;
    window.currentStep = window.currentStep || 1;
    window.totalSteps = window.totalSteps || 4;
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
        price_type_checkboxes: { field: 'Price Types', step: 4 },
        price_per_hour: { field: 'Price Per Hour', step: 4 },
        price_per_day: { field: 'Price Per Day', step: 4 },
        price_per_week: { field: 'Price Per Week', step: 4 },
        status: { field: 'Status', step: 4 },
        booking_advance: { field: 'Booking Advance', step: 4 }
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
            // Add a small delay to ensure all elements are ready
            setTimeout(() => {
                loadExistingData();
            }, 100);
        }

        // Initialize radio button functionality
        initializeRadioButtons();

        // Initialize checkbox functionality
        initializeCheckboxes();

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
                }
            });
            
            // Populate with existing data
            @if(isset($formData['boat_extras']) && is_array($formData['boat_extras']))
            const boatExtrasData = {!! json_encode($formData['boat_extras']) !!};
            if (boatExtrasTagify && boatExtrasData && Array.isArray(boatExtrasData)) {
                boatExtrasTagify.addTags(boatExtrasData.filter(Boolean));
            }
            @endif
        }
        @else
        // Initialize tagify for boat extras without predefined options
        if (document.getElementById('boat_extras')) {
            initTagify('#boat_extras', {});
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
                }
            });
            
            // Populate with existing data
            @if(isset($formData['inclusions']) && is_array($formData['inclusions']))
            const inclusionsData = {!! json_encode($formData['inclusions']) !!};
            if (inclusionsTagify && inclusionsData && Array.isArray(inclusionsData)) {
                inclusionsTagify.addTags(inclusionsData.filter(Boolean));
            }
            @endif
        }
        @else
        // Initialize tagify for inclusions without predefined options
        if (document.getElementById('inclusions')) {
            initTagify('#inclusions', {});
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
        // Radio button functionality for boat types and other radio groups
        $('input[type="radio"]').change(function() {
            $(this).closest('.btn-group-toggle').find('label').removeClass('active');
            $(this).next('label').addClass('active');
        });
    }

    function initializeExtraPricing() {
        let extraPricingCount = 0;

        // Add extra pricing button
        document.getElementById('add-extra-pricing')?.addEventListener('click', function() {
            addExtraPricingItem();
        });

        // Make addExtraPricingItem globally accessible
        window.addExtraPricingItem = function(name = '', price = '') {
            const container = document.getElementById('extra-pricing-container');
            const item = document.createElement('div');
            item.className = 'extra-pricing-item';
            item.innerHTML = `
                <div class="form-group">
                    <label class="form-label">Item Name</label>
                    <input type="text" class="form-control" name="extra_pricing[${extraPricingCount}][name]" placeholder="e.g., Captain, Fuel, etc." value="${name}">
                </div>
                <div class="form-group">
                    <label class="form-label">Price</label>
                    <div class="input-group">
                        <span class="input-group-text">€</span>
                        <input type="number" class="form-control" name="extra_pricing[${extraPricingCount}][price]" placeholder="0.00" step="0.01" min="0" value="${price}">
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

    }

    function setupFormSubmission() {
        // Submit button handlers
        $(document).on('click', '[id^="submitBtn"]', function(e) {
            e.preventDefault();
            handleSubmit(e);
        });

        // Form submit handler
        document.getElementById('rentalBoatForm').addEventListener('submit', function(e) {
            e.preventDefault();
            handleSubmit(e);
        });

        // Draft save buttons
        $(document).off('click', '[id^="saveDraftBtn"]').on('click', '[id^="saveDraftBtn"]', function(e) {
            e.preventDefault();
            saveDraft();
        });
    }

    function handleSubmit(event) {
        // Ensure we get the form element, not the event target (which might be a button)
        const form = document.getElementById('rentalBoatForm');
        const isDraft = document.getElementById('is_draft').value === '1';
        
        // Show loading screen
        showLoadingScreen();
        
        if (isDraft || validateAllSteps()) {
            submitForm(form);
        } else {
            hideLoadingScreen();
            // Scroll to error container if there are validation errors
            const errorContainer = document.getElementById('error-container');
            if (errorContainer && errorContainer.style.display === 'block') {
                errorContainer.scrollIntoView({ behavior: 'smooth', block: 'center' });
            }
        }
    }

    async function saveDraft() {
        const form = document.getElementById('rentalBoatForm');
        if (!form) {
            console.error('Form not found');
            return;
        }

        // Show loading screen
        showLoadingScreen();
        
        // Update loading screen message
        const loadingScreen = document.getElementById('loadingScreen');
        if (loadingScreen) {
            const loadingText = loadingScreen.querySelector('div[style*="font-size: 1.2rem"]');
            if (loadingText) {
                loadingText.textContent = 'Saving draft...';
            }
        }

        try {
            const formData = new FormData(form);
            
            // Force draft mode
            formData.set('is_draft', '1');
            formData.set('current_step', currentStep);
            formData.set('status', 'draft');

            // Always append these if present
            const rentalBoatId = $('#rental_boat_id').val();
            const isUpdate = $('#is_update').val();
            if (rentalBoatId) formData.set('rental_boat_id', rentalBoatId);
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
                method: formData.get('_method') || 'POST',
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
                    // Update form with new rental boat ID if it's a new record
                    if (data.rental_boat_id) {
                        $('#rental_boat_id').val(data.rental_boat_id);
                        $('#is_update').val('1');
                    }
                    hideLoadingScreen();
                    alert(data.message || 'Draft saved successfully!');
                }
            } else {
                throw new Error(data.message || 'Failed to save draft');
            }
        } catch (error) {
            console.error('Error saving draft:', error);
            hideLoadingScreen();
            alert('Error saving draft: ' + error.message);
        }
    }

    function submitForm(form) {
        // Ensure we have a valid form element
        if (!form || !(form instanceof HTMLFormElement)) {
            console.error('Invalid form element:', form);
            hideLoadingScreen();
            alert('Error: Form not found or invalid.');
            return;
        }
        
        const formData = new FormData(form);
        
        // DEBUG: Log what's being sent
        console.log('=== FORM DATA BEING SENT ===');
        for (let [key, value] of formData.entries()) {
            console.log(key + ':', value);
        }
        console.log('=== END FORM DATA ===');
        
        // Ensure status is set to 'active' for final submission (not draft)
        const isDraft = formData.get('is_draft') === '1';
        if (!isDraft && !formData.get('status')) {
            formData.set('status', 'active');
        }
        
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
        // IMPORTANT: Always use POST for FormData, Laravel will handle _method spoofing
        fetch(form.action, {
            method: 'POST',
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
            hideLoadingScreen();
            alert('Error submitting form: ' + error.message);
        });
    }

    function displayValidationErrors(errors) {
        hideLoadingScreen();
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

        return true;

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
                if (!document.querySelector('input[name="boat_type"]:checked')) {
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
                const priceTypeCheckboxes = document.querySelectorAll('input[name="price_type_checkboxes[]"]:checked');
                if (priceTypeCheckboxes.length === 0) {
                    errors.push('Please select at least one price type.');
                    isValid = false;
                } else {
                    // Validate that each selected price type has a valid price
                    priceTypeCheckboxes.forEach(checkbox => {
                        const priceType = checkbox.value;
                        const priceInput = document.querySelector(`input[name="price_${priceType}"]`);
                        if (priceInput) {
                            const price = parseFloat(priceInput.value);
                            if (!priceInput.value.trim() || isNaN(price) || price < 0) {
                                errors.push(`Please enter a valid price for ${priceType.replace('_', ' ')}.`);
                                isValid = false;
                            }
                        }
                    });
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
        
        // Load images if they exist - DISABLED to prevent duplication
        // Images are now handled by the croppedImagesContainer only
        if (formData.gallery_images && Array.isArray(formData.gallery_images)) {
            console.log('Gallery images found:', formData.gallery_images.length, 'images');
            // Note: Images are now displayed in croppedImagesContainer to avoid duplication
        }

        // Load boat type selection
        if (formData.boat_type) {
            console.log('Loading boat type:', formData.boat_type);
            
            let boatTypeRadio = null;
            
            // If it's a numeric ID, find the corresponding boat type from the form data
            if (!isNaN(formData.boat_type)) {
                const boatTypeId = parseInt(formData.boat_type);
                const formDataBoatTypes = @json($rentalBoatTypes ?? []);
                
                // Find the boat type by ID
                const boatType = formDataBoatTypes.find(bt => bt.id === boatTypeId);
                if (boatType) {
                    boatTypeRadio = document.querySelector(`input[name="boat_type"][value="${boatType.value}"]`);
                    console.log('Found boat type by ID:', boatType.value);
                }
            } else {
                // Try to find by value (boat type name)
                boatTypeRadio = document.querySelector(`input[name="boat_type"][value="${formData.boat_type}"]`);
            }
            
            // If still not found, try to find by matching the boat type name
            if (!boatTypeRadio) {
                const allBoatTypes = document.querySelectorAll('input[name="boat_type"]');
                for (let radio of allBoatTypes) {
                    const label = radio.nextElementSibling;
                    if (label && label.textContent.trim().toLowerCase() === formData.boat_type.toLowerCase()) {
                        boatTypeRadio = radio;
                        break;
                    }
                }
            }
            
            if (boatTypeRadio) {
                boatTypeRadio.checked = true;
                boatTypeRadio.dispatchEvent(new Event('change'));
                const label = boatTypeRadio.nextElementSibling;
                if (label && label.tagName === 'LABEL') {
                    label.classList.add('active');
                }
                console.log('Boat type selected:', formData.boat_type);
            } else {
                console.log('Boat type not found:', formData.boat_type);
                console.log('Available boat types:', Array.from(document.querySelectorAll('input[name="boat_type"]')).map(input => input.value));
            }
        }

        // Load requirements checkboxes data
        if (formData.requirements && Array.isArray(formData.requirements)) {
            console.log('Loading requirements:', formData.requirements);
            formData.requirements.forEach((item) => {
                if (item && item.value && item.value.trim() !== '') {
                    const checkbox = document.querySelector(`input[name="rental_requirement_checkboxes[]"][value="${item.id}"]`);
                    if (checkbox) {
                        checkbox.checked = true;
                        checkbox.dispatchEvent(new Event('change'));
                        const container = checkbox.closest('.btn-checkbox-container');
                        if (container) {
                            container.classList.add('active');
                            const input = container.querySelector('.extra-input');
                            if (input) {
                                input.value = item.value;
                                input.style.display = 'block';
                            }
                        }
                    }
                }
            });
        }

        // Load boat info checkboxes data from boat_information
        if (formData.boat_information && Array.isArray(formData.boat_information)) {
            console.log('Loading boat information:', formData.boat_information);
            formData.boat_information.forEach((item) => {
                if (item && item.value && item.value.trim() !== '') {
                    const checkbox = document.querySelector(`input[name="boat_info_checkboxes[]"][value="${item.id}"]`);
                    if (checkbox) {
                        checkbox.checked = true;
                        checkbox.dispatchEvent(new Event('change'));
                        const container = checkbox.closest('.btn-checkbox-container');
                        if (container) {
                            container.classList.add('active');
                            const textarea = container.querySelector('textarea');
                            if (textarea) {
                                textarea.value = item.value;
                                textarea.style.display = 'block';
                            }
                        }
                    }
                }
            });
        }

        // Load pricing data
        if (formData.prices && typeof formData.prices === 'object') {
            Object.entries(formData.prices).forEach(([priceType, price]) => {
                if (priceType !== 'pricing_extra' && price > 0) {
                    const checkbox = document.querySelector(`input[name="price_type_checkboxes[]"][value="${priceType}"]`);
                    const priceInput = document.querySelector(`input[name="price_${priceType}"]`);
                    
                    if (checkbox && priceInput) {
                        checkbox.checked = true;
                        checkbox.dispatchEvent(new Event('change'));
                        priceInput.value = price;
                        
                        const container = checkbox.closest('.btn-checkbox-container');
                        if (container) {
                            container.classList.add('active');
                        }
                    }
                }
            });
        }


        // Load tagify data
        if (formData.boat_extras && Array.isArray(formData.boat_extras)) {
            const boatExtrasElement = document.getElementById('boat_extras');
            if (boatExtrasElement && !boatExtrasElement.tagify) {
                initTagify('#boat_extras', {});
            }
            if (boatExtrasElement && boatExtrasElement.tagify) {
                // Convert array to tagify format
                const tags = formData.boat_extras.map(item => {
                    if (typeof item === 'string') {
                        return { value: item };
                    } else if (item && item.value) {
                        return { value: item.value, id: item.id };
                    }
                    return { value: item };
                });
                boatExtrasElement.tagify.addTags(tags);
            }
        }

        if (formData.inclusions && Array.isArray(formData.inclusions)) {
            const inclusionsElement = document.getElementById('inclusions');
            if (inclusionsElement && !inclusionsElement.tagify) {
                initTagify('#inclusions', {});
            }
            if (inclusionsElement && inclusionsElement.tagify) {
                // Convert array to tagify format
                const tags = formData.inclusions.map(item => {
                    if (typeof item === 'string') {
                        return { value: item };
                    } else if (item && item.value) {
                        return { value: item.value, id: item.id };
                    }
                    return { value: item };
                });
                inclusionsElement.tagify.addTags(tags);
            }
        }

        // Load extra pricing data
        if (formData.pricing_extra && Array.isArray(formData.pricing_extra)) {
            console.log('Loading extra pricing:', formData.pricing_extra);
            formData.pricing_extra.forEach((item, index) => {
                if (item.name && item.price) {
                    addExtraPricingItem(item.name, item.price);
                }
            });
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

    function initializeCheckboxes() {        
        // Checkbox with additional fields functionality - exactly like guidings form
        $('.btn-checkbox-container input[type="checkbox"]').change(function() {
            var $container = $(this).closest('.btn-checkbox-container');
            var $label = $container.find('label');
            var $textarea = $container.find('textarea');
            var $input = $container.find('.extra-input');
            var $inputGroup = $container.find('.input-group');

            if (this.checked) {
                $label.addClass('active');
                $textarea.show();
                $input.show();
                $inputGroup.show();
                $textarea.prop('required', true);
                $input.prop('required', true);
            } else {
                $label.removeClass('active');
                $textarea.hide();
                $input.hide();
                $inputGroup.hide();
                $textarea.prop('required', false);
                $input.prop('required', false);
                $textarea.val('');
                $input.val('');
            }
        });

        // Initialize checkbox states on page load
        $('.btn-checkbox-container input[type="checkbox"]').each(function() {
            var $container = $(this).closest('.btn-checkbox-container');
            var $label = $container.find('label');
            var $textarea = $container.find('textarea');
            var $input = $container.find('.extra-input');
            var $inputGroup = $container.find('.input-group');
            
            if (this.checked) {
                $label.addClass('active');
                $textarea.show();
                $input.show();
                $inputGroup.show();
            } else {
                $label.removeClass('active');
                $textarea.hide();
                $input.hide();
                $inputGroup.hide();
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
            } else {
                $label.removeClass('active');
            }
        });

        // Add change handlers for radio buttons
        $('input[type="radio"]').change(function() {
            var name = $(this).attr('name');
            var $labels = $(`input[name="${name}"]`).next('label');
            $labels.removeClass('active');
            
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
</script>
@endpush
