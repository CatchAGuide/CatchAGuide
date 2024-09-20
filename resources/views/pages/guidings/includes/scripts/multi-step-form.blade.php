@push('js_push')

<script src="https://cdnjs.cloudflare.com/ajax/libs/cropperjs/1.5.12/cropper.min.js"></script>
<script src="https://maps.googleapis.com/maps/api/js?key={{ env('GOOGLE_MAP_API_KEY') }}&libraries=places&callback=initAutocomplete" async defer></script>
<script src="https://cdn.jsdelivr.net/npm/@yaireo/tagify"></script>

<script>
    let croppers = {};
    let croppedImages = [];
    let imageIndex = 0;
    let currentStep = 1;
    const totalSteps = 8; 
    let autocomplete;
    let city = '';
    let country = '';
    let postal_code = '';
    
    $(document).on('click', '#saveDraftBtn', function(e) {
        e.preventDefault();
        saveDraft();
    });

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
                    city = component.long_name; // This is the city name
                } else if (types.includes('country')) {
                    country = component.long_name; // This is the country name
                    $('#country').val(country);
                } else if (types.includes('postal_code')) {
                    postal_code = component.long_name; // This is the postal code
                    $('#postal_code').val(postal_code);
                }
            });
        });
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

        // Update button visibility
        $('#prevBtn').toggle(currentStep > 1);
        $('#nextBtn').toggle(currentStep < totalSteps);
        $('#submitBtn').toggle(currentStep === totalSteps);
        console.log(currentStep);
    }

    $('.step-button').click(function() {
        showStep($(this).data('step'));
    });

    // Initialize Tagify for tag inputs
    document.addEventListener('DOMContentLoaded', function() {        
        // Extras
        var extrasInput = document.querySelector('input[name="extras"]');
        var extrasWhitelist = [
            'GPS', 'Echolot', 'Live Scope', 'Radar', 'Funk', 'Flybridge', 'WC', 
            'Roofing', 'Dusche', 'Küche', 'Bett', 'Wifi', 'Ice box/ Kühlschrank', 
            'Air conditioning', 'Fighting chair', 'E-Motor', 'Felitiertisch'
        ].sort(); // Sort the whitelist alphabetically

        new Tagify(extrasInput, {
            whitelist: extrasWhitelist,
            // maxTags: extrasWhitelist.length, // Set maxTags to the total number of options
            dropdown: {
                maxItems: extrasWhitelist.length, // Show all items in the dropdown
                classname: "tagify__dropdown",
                enabled: 0,
                closeOnSelect: false
            }
        });

        // Target Fish
        var targetFishInput = document.querySelector('input[name="target_fish"]');
        var targetFishList = {!! json_encode($targets->toArray()) !!};
        new Tagify(targetFishInput, {
            whitelist: targetFishList.sort(),
            // maxTags: 10,
            dropdown: {
                maxItems: targetFishInput.length,
                classname: "tagify__dropdown",
                enabled: 0,
                closeOnSelect: false
            }
        });

        // Methods
        var methodsInput = document.querySelector('input[name="methods"]');
        var methodsList = {!! json_encode($methods->toArray()) !!};
        new Tagify(methodsInput, {
            whitelist: methodsList.sort(),
            maxTags: 10,
            dropdown: {
                maxItems: methodsInput.length,
                classname: "tagify__dropdown",
                enabled: 0,
                closeOnSelect: false
            }
        });

        // Water Types
        var waterTypesInput = document.querySelector('input[name="water_types"]');
        var waterTypesList = {!! json_encode($waters->toArray()) !!};
        new Tagify(waterTypesInput, {
            whitelist: waterTypesList.sort(),
            maxTags: 10,
            dropdown: {
                maxItems: waterTypesInput.length,
                classname: "tagify__dropdown",
                enabled: 0,
                closeOnSelect: false
            }
        });

        // Inclusions
        var inclusionsInput = document.querySelector('input[name="inclussions"]');
        var inclusionsList = {!! json_encode($inclussions->toArray()) !!};
        new Tagify(inclusionsInput, {
            whitelist: inclusionsList.sort(),
            maxTags: 10,
            dropdown: {
                maxItems: inclusionsInput.length,
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
    });

    function setFormDataIfEdit() {
        console.log('Form Data:', {!! json_encode($formData ?? []) !!});

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

        const inclussionsInputData = document.querySelector('input[name="inclussions"]');
        if (inclussionsInputData) {
            const inclussionsTagify = new Tagify(inclussionsInputData);
            const inclussionsData = {!! json_encode($formData['inclussions'] ?? []) !!};
            inclussionsTagify.addTags(inclussionsData);
        }

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
                document.getElementById('duration_details').style.display = 'block';
                if (durationType === 'multi_day') {
                    document.getElementById('days_input').style.display = 'block';
                    document.getElementById('hours_input').style.display = 'none';
                    document.getElementById('duration_hours').value = durationCount;
                } else {
                    document.getElementById('hours_input').style.display = 'block';
                    document.getElementById('days_input').style.display = 'none';
                    document.getElementById('duration_days').value = durationCount;
                }
            }
        }
        
        const priceType = '{{ $formData['price_type'] ?? '' }}';
        const prices = {!! json_encode($formData['prices'] ?? []) !!};
        if (priceType) {
            const priceTypeRadio = document.querySelector(`input[name="price_type"][value="${priceType}"]`);
            if (priceTypeRadio) {
                priceTypeRadio.checked = true;
                if (prices && Object.keys(prices).length > 0) {
                    if (priceType === 'per_person') {
                        Object.entries(prices).forEach(([key, value]) => {
                            const input = document.querySelector(`input[name="price_per_person[${key}]"]`);
                            if (input) {
                                input.value = value;
                            }
                        });
                    } else if (priceType === 'per_boat') {
                        const input = document.querySelector('input[name="price_per_boat"]');
                        if (input) {
                            input.value = prices.amount;
                        }
                    }
                }
            }
        }

        // Handle existing images
        const existingImages = {!! json_encode($formData['images'] ?? []) !!};
        const thumbnailPath = '{{ $formData['thumbnail_path'] ?? '' }}';
        
        if (existingImages && existingImages.length > 0) {
            const croppedImagesContainer = document.getElementById('croppedImagesContainer');
            const dataTransfer = new DataTransfer();

            existingImages.forEach((imagePath, index) => {
                const fullImagePath = `/${imagePath}`; // Adjust this path as needed
                const wrapper = document.createElement('div');
                wrapper.className = 'image-preview-wrapper';
                wrapper.dataset.index = index;

                const img = document.createElement('img');
                img.src = fullImagePath;
                img.className = 'image-preview';
                wrapper.appendChild(img);

                const controls = document.createElement('div');
                controls.className = 'image-controls';

                // Create control buttons with tooltips
                const zoomInBtn = createButton('<i class="fas fa-search-plus"></i>', () => {}, 'Zoom In');
                const zoomOutBtn = createButton('<i class="fas fa-search-minus"></i>', () => {}, 'Zoom Out');
                const rotateBtn = createButton('<i class="fas fa-redo"></i>', () => {}, 'Rotate');
                const deleteBtn = createButton('<i class="fas fa-trash"></i>', () => deleteImage(wrapper, index), 'Delete');
                const setPrimaryBtn = createButton('<i class="fas fa-star"></i>', () => setPrimaryImage(wrapper, index), 'Set as Title Image');

                controls.appendChild(zoomInBtn);
                controls.appendChild(zoomOutBtn);
                controls.appendChild(rotateBtn);
                controls.appendChild(deleteBtn);
                controls.appendChild(setPrimaryBtn);

                wrapper.appendChild(controls);
                croppedImagesContainer.appendChild(wrapper);

                // Set as primary if it matches the thumbnail_path
                if (imagePath === thumbnailPath) {
                    setPrimaryImage(wrapper, index);
                }

                // Add the image to the title_image[] input
                fetch(fullImagePath)
                    .then(res => res.blob())
                    .then(blob => {
                        const file = new File([blob], imagePath.split('/').pop(), { type: 'image/jpeg' });
                        dataTransfer.items.add(file);
                        imageFiles.push(file);
                        
                        // Update the file input after each file is added
                        document.getElementById('title_image').files = dataTransfer.files;
                    });
            });

            // Update the file input
            updateFileInput();
        }
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
                container.append(`<div class="input-group mt-2"><span class="input-group-text">Price</span><input type="number" class="form-control" name="price_per_person_${i}" placeholder="Price for ${i} person(s)"><span class="input-group-text">€ per Person</span></div>`);
            }
        } else if (priceType === 'per_boat') {
            container.append('<div class="input-group mt-2"><span class="input-group-text">Price</span><input type="number" class="form-control " name="price_per_boat" placeholder="Price per boat"><span class="input-group-text">€ per Boat</span></div>');
        }
    });

    $('#no_guest').change(function() {
        if ($('input[name="price_type"]:checked').val() === 'per_person') {
            $('input[name="price_type"]:checked').change();
        }
    });

    // Seasonal trip selection
    $('input[name="seasonal_trip"]').change(function() {
        if ($(this).val() === 'season_monthly') {
            $('#monthly_selection').show();
        } else {
            $('#monthly_selection').hide();
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

    function createButton(innerHTML, onClick, tooltip) {
        const button = document.createElement('button');
        button.type = 'button';
        button.className = 'image-control-btn';
        button.innerHTML = innerHTML;
        button.onclick = onClick;
        button.setAttribute('data-bs-toggle', 'tooltip');
        button.setAttribute('title', tooltip);
        return button;
    }

    let imageFiles = []; // Array to store all selected image files

    function previewImages(input) {
        const container = document.getElementById('imagePreviewContainer');
        
        if (!container) {
            console.error('Image preview container not found');
            return;
        }

        if (input.files) {
            Array.from(input.files).forEach((file, index) => {
                // Check if the image is already in the croppedImagesContainer
                const existingImage = document.querySelector(`.image-preview-wrapper[data-index="${imageIndex}"]`);
                if (existingImage) {
                    return; // Skip this image if it already exists
                }

                imageFiles.push(file); // Add new file to the array
                const reader = new FileReader();
                reader.onload = function(e) {
                    const wrapper = document.createElement('div');
                    wrapper.className = 'image-preview-wrapper';
                    wrapper.dataset.index = imageIndex;

                    const img = document.createElement('img');
                    img.src = e.target.result;
                    img.className = 'image-preview';
                    wrapper.appendChild(img);

                    const controls = document.createElement('div');
                    controls.className = 'image-controls';

                    const currentIndex = imageIndex;  // Capture the current index

                    // Create control buttons with tooltips
                    const zoomInBtn = createButton('<i class="fas fa-search-plus"></i>', () => croppers[currentIndex].zoom(0.1), 'Zoom In');
                    const zoomOutBtn = createButton('<i class="fas fa-search-minus"></i>', () => croppers[currentIndex].zoom(-0.1), 'Zoom Out');
                    const rotateBtn = createButton('<i class="fas fa-redo"></i>', () => croppers[currentIndex].rotate(90), 'Rotate');
                    const deleteBtn = createButton('<i class="fas fa-trash"></i>', () => deleteImage(wrapper, currentIndex), 'Delete');
                    const setPrimaryBtn = createButton('<i class="fas fa-star"></i>', () => setPrimaryImage(wrapper, currentIndex), 'Set as Title Image');

                    controls.appendChild(zoomInBtn);
                    controls.appendChild(zoomOutBtn);
                    controls.appendChild(rotateBtn);
                    controls.appendChild(deleteBtn);
                    controls.appendChild(setPrimaryBtn);

                    wrapper.appendChild(controls);
                    container.appendChild(wrapper);

                    // Initialize Cropper
                    croppers[imageIndex] = new Cropper(img, {
                        aspectRatio: 5 / 4,
                        viewMode: 3,
                        dragMode: 'move',
                        autoCropArea: 1,
                        restore: false,
                        guides: false,
                        center: false,
                        highlight: false,
                        cropBoxMovable: false,
                        cropBoxResizable: false,
                        toggleDragModeOnDblclick: false,
                        minCropBoxWidth: wrapper.offsetWidth,
                        minCropBoxHeight: wrapper.offsetHeight,
                        ready: function() {
                            const cropper = this.cropper;
                            const imageData = cropper.getImageData();
                            const containerData = cropper.getContainerData();

                            const scale = Math.max(
                                containerData.width / imageData.naturalWidth,
                                containerData.height / imageData.naturalHeight
                            );

                            cropper.zoomTo(scale);

                            const scaledWidth = imageData.naturalWidth * scale;
                            const scaledHeight = imageData.naturalHeight * scale;
                            const left = (containerData.width - scaledWidth) / 2;
                            const top = (containerData.height - scaledHeight) / 2;

                            cropper.setCanvasData({
                                left: left,
                                top: top,
                                width: scaledWidth,
                                height: scaledHeight
                            });
                        }
                    });

                    // Set the first image as the title image by default
                    if (imageIndex === 0) {
                        setPrimaryImage(wrapper, currentIndex);
                    }

                    imageIndex++;
                }
                reader.readAsDataURL(file);
            });

            // Update the file input with all selected files
            updateFileInput();
        }
    }

    function updateFileInput() {
        const fileInput = document.getElementById('title_image');
        const dataTransfer = new DataTransfer();
        
        imageFiles.forEach(file => {
            dataTransfer.items.add(file);
        });
        
        fileInput.files = dataTransfer.files;
    }

    function deleteImage(wrapper, index) {
        wrapper.remove();
        delete croppers[index];
        imageFiles.splice(index, 1); // Remove the file from the array
        updateFileInput(); // Update the file input after deletion
    }

    function setPrimaryImage(wrapper, index) {
        document.querySelectorAll('.image-preview-wrapper').forEach(w => w.classList.remove('primary'));
        wrapper.classList.add('primary');
        // You may want to update a hidden input field with the primary image index or path
        document.getElementById('primaryImageInput').value = index;
    }

    // Add this event listener to prevent form submission on enter key
    document.getElementById('newGuidingForm').addEventListener('keypress', function(e) {
        if (e.key === 'Enter') {
            e.preventDefault();
        }
    });

    // Update the form submission logic
    document.getElementById('newGuidingForm').addEventListener('submit', function(e) {
        if (currentStep !== totalSteps && !this.noValidate) {
            e.preventDefault();
            if (validateStep(currentStep)) {
                showStep(currentStep + 1);
            }
        }
    });

    function validateStep(step) {
        const errorContainer = document.getElementById('error-container');
        errorContainer.style.display = 'none';
        errorContainer.innerHTML = '';

        let isValid = true;
        let errors = [];

        // Check if it's a draft submission
        const isDraft = document.querySelector('input[name="is_draft"]');
        if (isDraft && isDraft.value === '1') {
            console.log('draft state');
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
                if (!document.getElementById('is_update').value.trim() || document.getElementById('is_update').value.trim() === 0) {
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


    // Modify the handleSubmit function
    function handleSubmit(event) {
        event.preventDefault();
        const form = event.target;
        const isDraft = form.querySelector('input[name="is_draft"]');
        
        console.log('form submitted before loading screen');
        showLoadingScreen();
        console.log('form submitted after loading screen');
        
        if (isDraft && isDraft.value === '1') {
            form.submit();
        } else if (form.noValidate || validateStep(currentStep)) {
            form.submit();
        }
    }

    // Update the form's submit event listener
    document.getElementById('newGuidingForm').addEventListener('submit', handleSubmit);
</script>
@endpush