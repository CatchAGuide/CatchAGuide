@push('js_push')

<script src="https://cdnjs.cloudflare.com/ajax/libs/cropperjs/1.5.12/cropper.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/@yaireo/tagify"></script>
<script src="{{ asset('assets/js/ImageManager.js') }}"></script>

<script>
    let imageManager;
    let currentStep = 1;
    const totalSteps = 8; 
    let autocomplete;
    let city = '';
    let country = '';
    let postal_code = '';
    
    function initAutocomplete() {
        autocomplete = new google.maps.places.Autocomplete(
            document.getElementById('location'),
            {
                types: ['(regions)']
            }
        );

        autocomplete.addListener('place_changed', function () {
            const place = autocomplete.getPlace();
            place.address_components.forEach(component => {
                const types = component.types;

                $('#latitude').val(place.geometry.location.lat());
                $('#longitude').val(place.geometry.location.lng());

                if (types.includes('locality')) {
                    city = component.long_name;
                } else if (types.includes('country')) {
                    country = component.long_name;
                    $('#country').val(country);
                } else if (types.includes('postal_code')) {
                    postal_code = component.long_name;
                    $('#postal_code').val(postal_code);
                }
            });
        });
    }

    function handleImageUpload(event) {
        const files = event.target.files;
        const galleryContainer = document.getElementById('image-gallery');
        
        for (let i = 0; i < files.length; i++) {
            const file = files[i];
            const reader = new FileReader();
            
            reader.onload = function(e) {
                const img = document.createElement('img');
                img.src = e.target.result;
                img.classList.add('gallery-image');
                
                const imgContainer = document.createElement('div');
                imgContainer.classList.add('image-container');
                imgContainer.appendChild(img);
                
                galleryContainer.appendChild(imgContainer);
            }
            
            reader.readAsDataURL(file);
        }
    }
    
    function initializeImageManager() {
        if (typeof ImageManager === 'undefined') {
            console.error('ImageManager class not found');
            setTimeout(initializeImageManager, 100);
            return;
        }

        imageManager = new ImageManager('#croppedImagesContainer', '#title_image');
        
        if (document.getElementById('is_update').value === '1') {
            const existingImagesInput = document.getElementById('existing_images');
            const thumbnailPath = document.getElementById('thumbnail_path').value;
            
            if (existingImagesInput && existingImagesInput.value) {
                imageManager.loadExistingImages(existingImagesInput.value, thumbnailPath);
            }
        }

        setFormDataIfEdit();
        
        document.getElementById('newGuidingForm').addEventListener('keypress', function(e) {
            if (e.key === 'Enter') {
                e.preventDefault();
            }
        });

        $('#title_image').on('change', function(event) {
            imageManager.handleFileSelect(event.target.files);
        });
    }

    $(document).on('click', '#saveDraftBtn', function(e) {
        e.preventDefault();
        saveDraft();
    });
    
    function scrollToFormCenter() {
        const form = document.getElementById('newGuidingForm');
        if (form) {
            form.scrollIntoView({ behavior: 'smooth', block: 'center' });
        }
    }


    function showLoadingScreen() {
        const loadingScreen = document.createElement('div');
        loadingScreen.id = 'loadingScreen';
        loadingScreen.style.cssText = `
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0, 0, 0, 0.5);
            display: flex;
            justify-content: center;
            align-items: center;
            z-index: 9999;
        `;
        
        const spinner = document.createElement('div');
        spinner.style.cssText = `
            border: 5px solid #f3f3f3;
            border-top: 5px solid #3498db;
            border-radius: 50%;
            width: 50px;
            height: 50px;
            animation: spin 1s linear infinite;
        `;
        
        loadingScreen.appendChild(spinner);
        document.body.appendChild(loadingScreen);
    }

    function saveDraft() {
        const form = document.getElementById('newGuidingForm');
        if (!form) {
            console.error('Form not found');
            return;
        }

        if (currentStep !== totalSteps) {
            const draftInput = document.createElement('input');
            draftInput.type = 'hidden';
            draftInput.name = 'is_draft';
            draftInput.value = '1';
            form.appendChild(draftInput);

            form.noValidate = true;
        }

        form.submit();
    }

    function setFormDataIfEdit() {
        if (document.getElementById('is_update').value === '1') {
            const typeOfFishingData = '{{ $formData['type_of_fishing'] ?? '' }}';
            if (typeOfFishingData) {
                const radioButton = document.querySelector(`input[name="type_of_fishing_radio"][value="${typeOfFishingData}"]`);
                if (radioButton) {
                    radioButton.checked = true;
                    selectOption(typeOfFishingData);
                }
            }

            const boatTypeData = '{{ $formData['boat_type'] ?? '' }}';
            if (boatTypeData) {
                const boatRadio = document.querySelector(`input[name="type_of_boat"][value="${boatTypeData}"]`);
                if (boatRadio) {
                    boatRadio.checked = true;
                    const label = boatRadio.closest('label');
                    if (label) {
                        label.classList.add('active');
                    }
                }
            }

            const boatInformationData = {!! json_encode($formData['boat_information'] ?? []) !!};
            Object.entries(boatInformationData).forEach(([key, value]) => {
                const checkbox = document.querySelector(`input[name="descriptions[]"][value="${key}"]`);
                if (checkbox) {
                    checkbox.checked = true;
                    const container = checkbox.closest('.btn-checkbox-container');
                    if (container) {
                        container.classList.add('active');
                        const textarea = container.querySelector('textarea');
                        if (textarea) {
                            textarea.value = value;
                            textarea.style.display = 'block';
                        }
                    }
                }
            });

            const extrasInputData = document.querySelector('input[name="extras"]');
            if (extrasInputData) {
                const extrasTagify = new Tagify(extrasInputData);
                const extrasData = {!! json_encode($formData['boat_extras'] ?? []) !!};
                extrasTagify.addTags(extrasData);
            }

            const targetFishInputData = document.querySelector('input[name="target_fish"]');
            if (targetFishInputData) {
                const targetFishTagify = new Tagify(targetFishInputData);
                const targetFishData = {!! json_encode($formData['target_fish'] ?? []) !!};
                targetFishTagify.addTags(targetFishData);
            }

            const methodsInputData = document.querySelector('input[name="methods"]');
            if (methodsInputData) {
                const methodsTagify = new Tagify(methodsInputData);
                const methodsData = {!! json_encode($formData['methods'] ?? []) !!};
                methodsTagify.addTags(methodsData);
            }

            const waterTypesInputData = document.querySelector('input[name="water_types"]');
            if (waterTypesInputData) {
                const waterTypesTagify = new Tagify(waterTypesInputData);
                const waterTypesData = {!! json_encode($formData['water_types'] ?? []) !!};
                waterTypesTagify.addTags(waterTypesData);
            }

            const inclussionsInputData = document.querySelector('input[name="inclussions"]');
            if (inclussionsInputData) {
                const inclussionsTagify = new Tagify(inclussionsInputData);
                const inclussionsData = {!! json_encode($formData['inclussions'] ?? []) !!};
                inclussionsTagify.addTags(inclussionsData);
            }
            
            const experinceLevelData = {!! json_encode($formData['experience_level'] ?? []) !!};
            Object.entries(experinceLevelData).forEach(([key, value]) => {
                const checkbox = document.querySelector(`input[name="experience_level[]"][value="${value}"]`);
                if (checkbox) {
                    checkbox.checked = true;
                    const container = checkbox.closest('.btn-checkbox-container');
                    if (container) {
                        container.classList.add('active');
                    }
                }
            });

            const styleOfFishingData = '{{ $formData['style_of_fishing'] ?? '' }}';
            if (styleOfFishingData) {
                const styleOfFishing = document.querySelector(`input[name="style_of_fishing"][value="${styleOfFishingData}"]`);
                if (styleOfFishing) {
                    styleOfFishing.checked = true;
                    const label = styleOfFishing.closest('label');
                    if (label) {
                        label.classList.add('active');
                    }
                }
            }

            const otherInformationData = {!! json_encode($formData['other_information'] ?? []) !!};
            Object.entries(otherInformationData).forEach(([key, value]) => {
                const checkbox = document.querySelector(`input[name="other_information[]"][value="${key}"]`);
                if (checkbox) {
                    checkbox.checked = true;
                    const container = checkbox.closest('.btn-checkbox-container');
                    if (container) {
                        container.classList.add('active');
                        const textarea = container.querySelector('textarea');
                        if (textarea) {
                            textarea.value = value;
                            textarea.style.display = 'block';
                        }
                    }
                }
            });

            const requirementsData = {!! json_encode($formData['requirements'] ?? []) !!};
            Object.entries(requirementsData).forEach(([key, value]) => {
                const checkbox = document.querySelector(`input[name="requiements_taking_part[]"][value="${key}"]`);
                if (checkbox) {
                    checkbox.checked = true;
                    const container = checkbox.closest('.btn-checkbox-container');
                    if (container) {
                        container.classList.add('active');
                        const textarea = container.querySelector('textarea');
                        if (textarea) {
                            textarea.value = value;
                            textarea.style.display = 'block';
                        }
                    }
                }
            });

            const recommendationsData = {!! json_encode($formData['recommendations'] ?? []) !!};
            Object.entries(recommendationsData).forEach(([key, value]) => {
                const checkbox = document.querySelector(`input[name="recommended_preparation[]"][value="${key}"]`);
                if (checkbox) {
                    checkbox.checked = true;
                    const container = checkbox.closest('.btn-checkbox-container');
                    if (container) {
                        container.classList.add('active');
                        const textarea = container.querySelector('textarea');
                        if (textarea) {
                            textarea.value = value;
                            textarea.style.display = 'block';
                        }
                    }
                }
            });

            const tourTypeData = '{{ $formData['tour_type'] ?? '' }}';
            if (tourTypeData) {
                const tourRadio = document.querySelector(`input[name="tour_type"][value="${tourTypeData}"]`);
                if (tourRadio) {
                    tourRadio.checked = true;
                    const label = tourRadio.closest('label');
                    if (label) {
                        label.classList.add('active');
                    }
                }
            }
            
            const durationType = '{{ $formData['duration_type'] ?? '' }}';
            const durationCount = '{{ $formData['duration'] ?? '' }}';
            if (durationType) {
                const durationRadio = document.querySelector(`input[name="duration"][value="${durationType}"]`);
                if (durationRadio) {
                    durationRadio.checked = true;
                    durationRadio.dispatchEvent(new Event('change')); // Trigger change event
                    
                    document.getElementById('duration_details').style.display = 'block';
                    if (durationType === 'multi_day') {
                        document.getElementById('duration_days').style.display = 'block';
                        document.getElementById('duration_hours').style.display = 'none';
                        document.getElementById('duration_days').value = durationCount;
                    } else {
                        document.getElementById('duration_hours').style.display = 'block';
                        document.getElementById('duration_days').style.display = 'none';
                        document.getElementById('duration_hours').value = durationCount;
                    }
                }
            }
            
            const priceType = '{{ $formData['price_type'] ?? '' }}';
            if (priceType) {
                const priceTypeRadio = document.querySelector(`input[name="price_type"][value="${priceType}"]`);
                if (priceTypeRadio) {
                    priceTypeRadio.checked = true;
                    priceTypeRadio.dispatchEvent(new Event('change')); // Trigger change event
                }
            }

            const extras = {!! json_encode($formData['pricing_extra'] ?? []) !!};
            if (extras && extras.length > 0) {
                extras.forEach((extra, index) => {
                    if (index > 0) {
                        $('#add-extra').click();
                    }
                    const nameInput = document.querySelector(`input[name="extra_name_${index + 1}"]`);
                    const priceInput = document.querySelector(`input[name="extra_price_${index + 1}"]`);
                    if (nameInput && priceInput) {
                        nameInput.value = extra.name;
                        priceInput.value = extra.price;
                    }
                });
            }
            
            const allowed_booking_advance = '{{ $formData['allowed_booking_advance'] ?? '' }}';
            if (allowed_booking_advance) {
                document.querySelector(`input[name="allowed_booking_advance"][value="${allowed_booking_advance}"]`).checked = true;
            }
            
            const booking_window = '{{ $formData['booking_window'] ?? '' }}';
            if (booking_window) {
                document.querySelector(`input[name="booking_window"][value="${booking_window}"]`).checked = true;
            }

            const seasonalTripData = '{{ $formData['seasonal_trip'] ?? '' }}';
            const monthsData = {!! json_encode($formData['months'] ?? []) !!};
            if (seasonalTripData) {
                const seasonalTripRadio = document.querySelector(`input[name="seasonal_trip"][value="${seasonalTripData}"]`);
                if (seasonalTripRadio) {
                    seasonalTripRadio.checked = true;
                    if (seasonalTripData === 'season_monthly') {
                        document.getElementById('season_monthly').style.display = 'block';
                        if (monthsData && monthsData.length > 0) {
                            monthsData.forEach(month => {
                                const monthCheckbox = document.querySelector(`input[name="months[]"][value="${month}"]`);
                                if (monthCheckbox) {
                                    monthCheckbox.checked = true;
                                }
                            });
                        }
                    } else {
                        document.getElementById('season_monthly').style.display = 'none';
                    }
                }
            }

            // Trigger change event on seasonal_trip radio buttons to ensure proper visibility
            const seasonalTripInputs = document.querySelectorAll('input[name="seasonal_trip"]');
            seasonalTripInputs.forEach(input => {
                input.dispatchEvent(new Event('change'));
            });
        }
    }

    // Helper function to convert data URL to File object
    function dataURLtoFile(dataurl, filename) {
        var arr = dataurl.split(','),
            mime = arr[0].match(/:(.*?);/)[1],
            bstr = atob(arr[1]), 
            n = bstr.length, 
            u8arr = new Uint8Array(n);
        while(n--){
            u8arr[n] = bstr.charCodeAt(n);
        }
        return new File([u8arr], filename, {type:mime});
    }

    // Radio button functionality
    $('input[type="radio"]').change(function() {
        $(this).closest('.btn-group-toggle').find('label').removeClass('active');
        $(this).next('label').addClass('active');
    });

    // Checkbox with additional fields functionality
    $('.btn-checkbox-container input[type="checkbox"]').change(function() {
        var $container = $(this).closest('.btn-checkbox-container');
        var $label = $container.find('label');
        var $textarea = $container.find('textarea');

        if (this.checked) {
            $label.addClass('active');
            $textarea.show();
        } else {
            $label.removeClass('active');
            $textarea.hide();
        }
    });

    // Boat/Shore selection
    function selectOption(option) {
        $('#boatOption, #shoreOption').removeClass('active');
        $(`#${option}Option`).addClass('active');
        $('input[name="type_of_fishing"]').val(option);
        
        if (option === 'boat') {
            $('#extraFields').show();
        } else {
            $('#extraFields').hide();
            // Proceed to step 3 when 'shore' is selected
            showStep(3);
        }
    }

    // Dynamic price fields
    $('input[name="price_type"]').change(function() {
        var priceType = $(this).val();
        var container = $('#dynamic-price-fields-container');
        container.empty();

        if (priceType === 'per_person') {
            var guestCount = parseInt($('#no_guest').val()) || 1;
            for (var i = 1; i <= guestCount; i++) {
                container.append(`<div class="input-group mt-2">
                    <span class="input-group-text">Price for ${i} person(s)</span>
                    <input type="number" class="form-control" name="price_per_person_${i}" placeholder="Price for ${i} person(s)">
                    <span class="input-group-text">€ per Person</span>
                </div>`);
            }
        } else if (priceType === 'per_boat') {
            container.append('<div class="input-group mt-2"><span class="input-group-text">Price</span><input type="number" class="form-control" name="price_per_boat" placeholder="Price per boat"><span class="input-group-text">€ per Boat</span></div>');
        }

        // Populate fields if editing
        if ($('#is_update').val() === '1') {
            populatePriceFields(priceType);
        }
    });

    $('#no_guest').change(function() {
        if ($('input[name="price_type"]:checked').val() === 'per_person') {
            $('input[name="price_type"]:checked').change();
        }
    });

    // Add this function to populate price fields when editing
    function populatePriceFields(priceType) {
        var prices = {!! json_encode($formData['prices'] ?? []) !!};
        console.log(prices);
        if (priceType === 'per_person') {
            Object.entries(prices).forEach(([key, value]) => {
                console.log(key);
                console.log(value);
                $(`input[name="price_per_person_${value.person}"]`).val(value.amount);
            });
        } else if (priceType === 'per_boat') {
            $('input[name="price_per_boat"]').val(prices.amount);
        }
    }

    // Seasonal trip selection
    $('input[name="seasonal_trip"]').change(function() {
        if ($(this).val() === 'season_monthly') {
            $('#season_monthly').show();
        } else {
            $('#season_monthly').hide();
        }
    });

    // Add extra pricing
    let extraCount = 0;
    $('#add-extra').click(function() {
        extraCount++;
        const newRow = `
            <div class="extra-row d-flex mb-2">
                <div class="input-group mt-2">
                    <span class="input-group-text">Additional Offer</span>
                    <input type="text" class="form-control mr-2" name="extra_name_${extraCount}" placeholder="Extra name">
                    <span class="input-group-text">Price</span>
                    <input type="number" class="form-control mr-2" name="extra_price_${extraCount}" placeholder="100.00">
                    <span class="input-group-text">€ per Person</span>
                </div>
                <button type="button" class="btn btn-danger btn-sm remove-extra"><i class="fas fa-trash"></i></button>
            </div>
        `;
        $('#extras-container').append(newRow);
    });

    $(document).on('click', '.remove-extra', function() {
        $(this).closest('.extra-row').remove();
    });

    // Modify the handleSubmit function
    function handleSubmit(event) {
        event.preventDefault();
        const form = event.target;
        const isDraft = form.querySelector('input[name="is_draft"]');
        
        // Check if the click originated from an image control button
        if (event.submitter && event.submitter.closest('.image-controls')) {
            return;
        }
        
        // Show loading screen
        const loadingScreen = document.getElementById('loadingScreen');
        if (loadingScreen) {
            loadingScreen.style.display = 'block';
        }
        
        if (isDraft && isDraft.value === '1') {
            submitForm(form);
        } else if (form.noValidate || validateStep(currentStep)) {
            submitForm(form);
        } else {
            // Hide loading screen if validation fails
            if (loadingScreen) {
                loadingScreen.style.display = 'none';
            }
        }
    }

    // Add this function to handle form submission
    function submitForm(form) {
        const formData = new FormData(form);

        try {
            if (!imageManager) {
                console.error('ImageManager not initialized');
                return;
            }

            const croppedImages = imageManager.getCroppedImages();

            formData.delete('title_image[]');

            croppedImages.forEach((image, index) => {
                if (image && image.dataUrl) {
                    const blob = dataURItoBlob(image.dataUrl);
                    formData.append(`title_image[]`, blob, `cropped_image_${index}.png`);
                }
            });

            const primaryImageIndex = imageManager.getPrimaryImageIndex();

            fetch(form.action, {
                method: 'POST',
                body: formData,
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                    'Accept': 'application/json'
                }
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
                console.log('Form submitted successfully:', data);
                if (data.redirect_url) {
                    window.location.href = data.redirect_url;
                } else {
                    alert('Form submitted successfully!');
                }
            })
            .catch(error => {
                console.error('Error submitting form:', error);
                if (error instanceof Error) {
                    if (error.message.startsWith('<!DOCTYPE html>')) {
                        console.error('Server returned an HTML error page. Check server logs for details.');
                        alert('An unexpected error occurred. Please try again later.');
                    } else {
                        console.error(error.message);
                        alert(error.message);
                    }
                } else if (typeof error === 'object' && error !== null) {
                    displayValidationErrors(error.errors || {});
                } else {
                    alert('An unexpected error occurred. Please try again.');
                }
            })
            .finally(() => {
                const loadingScreen = document.getElementById('loadingScreen');
                if (loadingScreen) {
                    loadingScreen.style.display = 'none';
                }
            });
        } catch (error) {
            console.error('Error preparing form data:', error);
            alert('An error occurred while preparing the form data. Please try again.');
        }
    }

    function displayValidationErrors(errors) {
        const errorContainer = document.getElementById('error-container');
        errorContainer.innerHTML = '';
        errorContainer.style.display = 'block';

        for (const field in errors) {
            const errorMessage = errors[field][0]; // Get the first error message for each field
            const errorElement = document.createElement('p');
            errorElement.textContent = `${field}: ${errorMessage}`;
            errorContainer.appendChild(errorElement);
        }
    }

    // Helper function to convert data URI to Blob
    function dataURItoBlob(dataURI) {
        const byteString = atob(dataURI.split(',')[1]);
        const mimeString = dataURI.split(',')[0].split(':')[1].split(';')[0];
        const ab = new ArrayBuffer(byteString.length);
        const ia = new Uint8Array(ab);
        for (let i = 0; i < byteString.length; i++) {
            ia[i] = byteString.charCodeAt(i);
        }
        return new Blob([ab], {type: mimeString});
    }

    function validateStep(step) {
        const errorContainer = document.getElementById('error-container');
        errorContainer.style.display = 'none';
        errorContainer.innerHTML = '';
        return true;

        let isValid = true;
        let errors = [];

        // Check if it's a draft submission
        const isDraft = document.querySelector('input[name="is_draft"]');
        if (isDraft && isDraft.value === '1') {
            return true; // Skip validation for drafts
        }

        switch(step) {
            case 1:
                if (!document.getElementById('title_image').files.length) {
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
                if (!document.querySelector('input[name="type_of_fishing_radio"]:checked')) {
                    errors.push('Please select a type of fishing.');
                    // isValid = false;
                } 
                if (document.getElementById('type_of_fishing').value === 'boat' && !document.querySelector('input[name="type_of_boat"]:checked')) {
                    errors.push('Please select a type of boat.');
                    isValid = false;
                }
                break;
            case 3:
                if (!document.getElementById('target_fish').value.trim()) {
                    errors.push('Target fish is required.');
                    isValid = false;
                }
                if (!document.getElementById('methods').value.trim()) {
                    errors.push('Methods are required.');
                    isValid = false;
                }
                if (!document.getElementById('water_types').value.trim()) {
                    errors.push('Water types are required.');
                    isValid = false;
                }
                break;
            case 4:
                if (!document.querySelector('input[name="experience_level[]"]:checked')) {
                    errors.push('Please select at least one experience level.');
                    isValid = false;
                }
                if (!document.getElementById('inclussions').value.trim()) {
                    errors.push('Inclusions are required.');
                    isValid = false;
                }
                if (!document.querySelector('input[name="style_of_fishing"]:checked')) {
                    errors.push('Please select a style of fishing.');
                    isValid = false;
                }
                break;
            case 5:
                if (!document.getElementById('is_update').value.trim() || document.getElementById('is_update').value.trim() === '0') {
                    if (!document.getElementById('desc_course_of_action').value.trim()) {
                        errors.push('Course of action description is required.');
                        isValid = false;
                    }
                    if (!document.getElementById('desc_starting_time').value.trim()) {
                        errors.push('Starting time description is required.');
                        isValid = false;
                    }
                    if (!document.getElementById('desc_meeting_point').value.trim()) {
                        errors.push('Meeting point description is required.');
                        isValid = false;
                    }
                    if (!document.getElementById('desc_tour_unique').value.trim()) {
                        errors.push('Tour uniqueness description is required.');
                        isValid = false;
                    }
                } else {
                    if (!document.getElementById('long_description').value.trim()) {
                        errors.push('description is required.');
                        isValid = false;
                    }
                }
                break;
            case 6:
                // No specific validation for this step
                break;
            case 7:
                if (!document.querySelector('input[name="tour_type"]:checked')) {
                    errors.push('Please select a tour type.');
                    isValid = false;
                }
                if (!document.querySelector('input[name="duration"]:checked')) {
                    errors.push('Please select a duration.');
                    isValid = false;
                }
                if (!document.getElementById('no_guest').value.trim()) {
                    errors.push('Number of guests is required.');
                    isValid = false;
                }
                if (!document.querySelector('input[name="price_type"]:checked')) {
                    errors.push('Please select a price type.');
                    isValid = false;
                }
                // Add more price validation based on the selected price type
                break;
            case 8:
                if (!document.querySelector('input[name="allowed_booking_advance"]:checked')) {
                    errors.push('Please select an allowed booking advance.');
                    isValid = false;
                }
                if (!document.querySelector('input[name="booking_window"]:checked')) {
                    errors.push('Please select a booking window.');
                    isValid = false;
                }
                if (!document.querySelector('input[name="seasonal_trip"]:checked')) {
                    errors.push('Please select a seasonal trip option.');
                    isValid = false;
                }
                if (document.querySelector('input[name="seasonal_trip"]:checked').value === 'season_monthly' && !document.querySelector('input[name="months[]"]:checked')) {
                    errors.push('Please select at least one month for seasonal trips.');
                    isValid = false;
                }
                break;
        }

        if (!isValid) {
            errorContainer.style.display = 'block';
            errorContainer.innerHTML = '<ul>' + errors.map(error => `<li>${error}</li>`).join('') + '</ul>';
            return false;
        }

        return true;
    }

    // Step navigation
    function showStep(stepNumber) {
        if (stepNumber > currentStep && !validateStep(currentStep)) {
            return;
        }

        $('.step').removeClass('active');
        $(`#step${stepNumber}`).addClass('active');
        $('.step-button').removeClass('active');
        $(`.step-button[data-step="${stepNumber}"]`).addClass('active');
        currentStep = stepNumber;

        scrollToFormCenter();

        // Update button visibility
        $('#prevBtn').toggle(currentStep > 1);
        $('#nextBtn').toggle(currentStep < totalSteps);
        $('#submitBtn').toggle(currentStep === totalSteps);
        
        // Hide nextBtn on last step
        if (currentStep === totalSteps) {
            $('#nextBtn').hide();
        }
    }

    $('.step-button').click(function() {
        showStep($(this).data('step'));
    });
    

    // Add this function at the beginning of your script
    function initTagify(selector, options = {}) {
        const element = document.querySelector(selector);
        if (element && !element.tagify) {
            new Tagify(element, options);
        }
    }

    // Then, in your DOMContentLoaded event listener, replace the existing Tagify initializations with:
    document.addEventListener('DOMContentLoaded', function() {
        initializeImageManager();

        // Extras
        initTagify('input[name="extras"]', {
            whitelist: [
                'GPS', 'Echolot', 'Live Scope', 'Radar', 'Funk', 'Flybridge', 'WC', 
                'Roofing', 'Dusche', 'Küche', 'Bett', 'Wifi', 'Ice box/ Kühlschrank', 
                'Air conditioning', 'Fighting chair', 'E-Motor', 'Felitiertisch'
            ].sort(),
            dropdown: {
                maxItems: Infinity,
                classname: "tagify__dropdown",
                enabled: 0,
                closeOnSelect: false
            }
        });

        // Target Fish
        initTagify('input[name="target_fish"]', {
            whitelist: {!! json_encode($targets->toArray()) !!}.sort(),
            dropdown: {
                maxItems: Infinity,
                classname: "tagify__dropdown",
                enabled: 0,
                closeOnSelect: false
            }
        });

        // Methods
        initTagify('input[name="methods"]', {
            whitelist: {!! json_encode($methods->toArray()) !!}.sort(),
            maxTags: 10,
            dropdown: {
                maxItems: Infinity,
                classname: "tagify__dropdown",
                enabled: 0,
                closeOnSelect: false
            }
        });

        // Water Types
        initTagify('input[name="water_types"]', {
            whitelist: {!! json_encode($waters->toArray()) !!}.sort(),
            maxTags: 10,
            dropdown: {
                maxItems: Infinity,
                classname: "tagify__dropdown",
                enabled: 0,
                closeOnSelect: false
            }
        });

        // Inclusions
        initTagify('input[name="inclussions"]', {
            whitelist: {!! json_encode($inclussions->toArray()) !!}.sort(),
            maxTags: 10,
            dropdown: {
                maxItems: Infinity,
                classname: "tagify__dropdown",
                enabled: 0,
                closeOnSelect: false
            }
        });

        // Show/hide monthly selection based on seasonal trip selection
        $('input[name="seasonal_trip"]').change(function() {
            $('#monthly_selection').toggle($(this).val() === 'season_monthly');
        });
        
        // Initialize tooltips
        var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'))
        var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
            return new bootstrap.Tooltip(tooltipTriggerEl)
        });

        // Initialize checkbox states
        $('.btn-checkbox-container input[type="checkbox"]').each(function() {
            var $container = $(this).closest('.btn-checkbox-container');
            var $textarea = $container.find('textarea');
            $textarea.toggle(this.checked);
        });

        initAutocomplete();

        // Duration selection logic
        $('input[name="duration"]').change(function() {
            var durationType = $(this).val();
            $('#duration_details').show();
            
            if (durationType === 'half_day' || durationType === 'full_day') {
                $('#hours_input').show();
                $('#days_input').hide();
            } else if (durationType === 'multi_day') {
                $('#hours_input').hide();
                $('#days_input').show();
            } else {
                $('#duration_details').hide();
            }
        });

        // Next button functionality
        $(document).on('click', '#nextBtn', function() {
            if (validateStep(currentStep)) {
                showStep(currentStep + 1);
            }
        });

        // Previous button functionality
        $(document).on('click', '#prevBtn', function() {
            showStep(currentStep - 1);
        });

        //If edit is requested, set the form data
        setFormDataIfEdit();
        
        const imageUploadInput = document.getElementById('title_image');
        if (imageUploadInput) {
            imageUploadInput.addEventListener('change', function(event) {
                if (imageManager) {
                    try {
                        imageManager.handleFileSelect(event.target.files);
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
    });

    // Update the form's submit event listener
    document.getElementById('newGuidingForm').addEventListener('submit', handleSubmit);
</script>

<script src="https://maps.googleapis.com/maps/api/js?key={{ env('GOOGLE_MAP_API_KEY') }}&libraries=places&callback=initAutocomplete" async defer></script>
@endpush