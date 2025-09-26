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

    // Initialize form when document is ready - exactly like rental-boat form
    $(document).ready(function() {
        initializeAccommodationForm();
    });

    function initializeAccommodationForm() {
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
            @if(isset($formData['facilities']) && is_array($formData['facilities']))
            const facilitiesData = {!! json_encode($formData['facilities']) !!};
            if (facilitiesTagify && facilitiesData && Array.isArray(facilitiesData)) {
                facilitiesTagify.addTags(facilitiesData.filter(Boolean));
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
        }
        @else
        if (document.getElementById('bathroom_amenities')) {
            initTagify('#bathroom_amenities', {});
        }
        @endif

        // Initialize tagify for policies
        if (document.getElementById('policies')) {
            initTagify('#policies', {});
        }

        // Initialize tagify for rental conditions
        if (document.getElementById('rental_conditions')) {
            initTagify('#rental_conditions', {});
        }

        // Initialize tagify for extras
        if (document.getElementById('extras')) {
            initTagify('#extras', {});
        }

        // Initialize tagify for inclusives
        if (document.getElementById('inclusives')) {
            initTagify('#inclusives', {});
        }
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
        // Submit button handlers
        document.querySelectorAll('[id^="submitBtn"]').forEach(button => {
            button.addEventListener('click', function(e) {
                e.preventDefault();
                handleSubmit(e);
            });
        });

        // Form submit handler
        const form = document.getElementById('accommodationForm');
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
            method: formData.get('_method') || 'POST',
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
                $input.show();
                $input.prop('required', true);
            } else {
                $label.removeClass('active');
                $input.hide();
                $input.prop('required', false);
                $input.val('');
            }
        });

        // Initialize checkbox states on page load
        $('.btn-checkbox-container input[type="checkbox"]').each(function() {
            var $container = $(this).closest('.btn-checkbox-container');
            var $label = $container.find('label');
            var $input = $container.find('.extra-input');
            
            if (this.checked) {
                $label.addClass('active');
                $input.show();
            } else {
                $input.hide();
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
</script>
@endpush
