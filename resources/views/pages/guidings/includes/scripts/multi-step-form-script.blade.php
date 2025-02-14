@push('js_push')

<script src="https://cdnjs.cloudflare.com/ajax/libs/cropperjs/1.5.12/cropper.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/@yaireo/tagify"></script>
<script src="https://cdn.jsdelivr.net/npm/browser-image-compression@latest/dist/browser-image-compression.js"></script>
<script src="{{ asset('assets/js/ImageManager.js') }}"></script>

<script>
    window.imageManagerLoaded = window.imageManagerLoaded || null;
    window.currentStep = window.currentStep || 1;
    window.totalSteps = window.totalSteps || 7;
    window.autocomplete = window.autocomplete || null;
    window.city = window.city || null;
    window.region = window.region || null;
    window.country = window.country || null;
    window.postal_code = window.postal_code || null;
    window.errorMapping = window.errorMapping || {
        title: { field: 'Title', step: 1 },
        title_image: { field: 'Galery Image', step: 1 },
        primaryImage: { field: 'Primary Image', step: 1 },
        location: { field: 'Location', step: 1 },
        type_of_fishing: { field: 'Type of Fishing', step: 2 },
        target_fish: { field: 'Target Fish', step: 3 },
        methods: { field: 'Methods', step: 3 },
        water_types: { field: 'Water Types', step: 3 },
        style_of_fishing: { field: 'Style of Fishing', step: 4 },
        desc_course_of_action: { field: 'Course of Action', step: 5 },
        desc_meeting_point: { field: 'Meeting Point', step: 5 },
        desc_tour_unique: { field: 'Tour Unique', step: 5 },
        desc_starting_time: { field: 'Starting Time', step: 5 },
        tour_type: { field: 'Tour Type', step: 7 },
        duration: { field: 'Duration', step: 7 },
        no_guest: { field: 'Number of Guests', step: 7 },
        price_type: { field: 'Price Type', step: 7 },
        allowed_booking_advance: { field: 'Allowed Booking Advance', step: 8 },
        booking_window: { field: 'Booking Window', step: 8 },
        seasonal_trip: { field: 'Seasonal Trip', step: 8 },
    };
    
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
                    $('#city').val(component.long_name);
                    city = component.long_name;
                } else if (types.includes('country')) {
                    country = component.long_name;
                    $('#country').val(country);
                } else if (types.includes('postal_code')) {
                    postal_code = component.long_name;
                    $('#postal_code').val(postal_code);
                } else if (types.includes('administrative_area_level_1')) {
                    region = component.long_name;
                    $('#region').val(region);
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
        imageManagerLoaded = new ImageManager('#croppedImagesContainer', '#title_image', '#cropped_image');
        
        if (document.getElementById('is_update').value === '1') {
            const existingImagesInput = document.getElementById('existing_images');
            const thumbnailPath = document.getElementById('thumbnail_path').value;
            
            if (existingImagesInput && existingImagesInput.value) {
                imageManagerLoaded.loadExistingImages(existingImagesInput.value, thumbnailPath);
            }
        }

        setFormDataIfEdit();
        
        document.getElementById('newGuidingForm').addEventListener('keypress', function(e) {
            if (e.key === 'Enter') {
                e.preventDefault();
            }
        });

        // $('#title_image').on('change', function(event) {
        //     imageManagerLoaded.handleFileSelect(event.target.files);
        // });
    }

    $(document).on('click', '[id^="saveDraftBtn"]', function(e) {
        e.preventDefault();
        saveDraft();
    });
    
    function scrollToFormCenter() {
        const form = document.getElementById('newGuidingForm');
        if (form) {
            // form.scrollIntoView({ behavior: 'smooth', block: 'start' });
            const formTop = form.getBoundingClientRect().top + window.pageYOffset; // Get the element's position relative to the document
        window.scrollTo({ 
            top: formTop - 250, // Adjust for 150px offset
            behavior: 'smooth'  // Smooth scrolling
        });
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

        // form.submit();
    }

    function setFormDataIfEdit() {
        if (document.getElementById('is_update').value === '1') {

            const typeOfFishingData = '{{ $formData['type_of_fishing'] ?? '' }}';
            if (typeOfFishingData) {
                const radioButton = document.querySelector(`input[name="type_of_fishing_radio"][value="${typeOfFishingData}"]`);
                if (radioButton) {
                    radioButton.checked = true;
                    radioButton.dispatchEvent(new Event('change'));
                    selectOption(typeOfFishingData, false);
                }
            }

            const boatTypeData = '{{ $formData['boat_type'] ?? '' }}';
            if (boatTypeData) {
                const boatRadio = document.querySelector(`input[name="type_of_boat"][value="${boatTypeData}"]`);
                if (boatRadio) {
                    boatRadio.checked = true;
                    boatRadio.dispatchEvent(new Event('change'));
                    const label = boatRadio.closest('label');
                    if (label) {
                        label.classList.add('active');
                    }
                }
            }

            const boatInformationData = {!! json_encode($formData['boat_information'] ?? []) !!};
            Object.entries(boatInformationData).forEach(([key, value]) => {
                const checkbox = document.querySelector(`input[name="descriptions[]"][value="${value['id']}"]`);
                if (checkbox) {
                    checkbox.checked = true;
                    checkbox.dispatchEvent(new Event('change'));
                    const container = checkbox.closest('.btn-checkbox-container');
                    if (container) {
                        container.classList.add('active');
                        const textarea = container.querySelector('textarea');
                        if (textarea) {
                            textarea.value = value['value'];
                            textarea.style.display = 'block';
                        }
                    }
                }
            });

            const extrasTagify = initTagify('input[name="boat_extras"]', {
                whitelist: {!! json_encode(collect($boat_extras)->sortBy('value')->values()->toArray()) !!},
                dropdown: {
                    maxItems: Infinity,
                    classname: "tagify__dropdown",
                    enabled: 0,
                    closeOnSelect: false
                }
            });

            const extrasData = {!! json_encode(collect($formData['boat_extras'] ?? [])->pluck('name')->toArray()) !!};
            if (extrasTagify && extrasData) {
                extrasTagify.addTags(extrasData.filter(Boolean));
            }

            //target fish
            const targetFishTagify = initTagify('input[name="target_fish"]', {
                whitelist: {!! json_encode(collect($targets)->sortBy('value')->values()->toArray()) !!}.sort(),
                dropdown: {
                    maxItems: Infinity,
                    classname: "tagify__dropdown",
                    enabled: 0,
                    closeOnSelect: false
                }
            });

            const targetFishData = {!! json_encode(collect($formData['target_fish'] ?? [])->pluck('name')->toArray()) !!};
            if (targetFishTagify && targetFishData) {
                targetFishTagify.addTags(targetFishData.filter(Boolean));
            }
            
            //methods
            const methodsTagify = initTagify('input[name="methods"]', {
                whitelist: {!! json_encode(collect($methods)->sortBy('value')->values()->toArray()) !!},
                dropdown: {
                    maxItems: Infinity,
                    classname: "tagify__dropdown",
                    enabled: 0,
                    closeOnSelect: false
                }
            });

            const methodsData = {!! json_encode(collect($formData['methods'] ?? [])->pluck('name')->toArray()) !!};
            if (methodsTagify && methodsData) {
                methodsTagify.addTags(methodsData.filter(Boolean));
            }

            //water types
            const waterTypesTagify = initTagify('input[name="water_types"]', {
                whitelist: {!! json_encode(collect($waters)->sortBy('value')->values()->toArray()) !!},
                dropdown: {
                    maxItems: Infinity,
                    classname: "tagify__dropdown",
                    enabled: 0,
                    closeOnSelect: false
                }
            });

            const waterTypesData = {!! json_encode(collect($formData['water_types'] ?? [])->pluck('name')->toArray()) !!};
            if (waterTypesTagify && waterTypesData) {
                waterTypesTagify.addTags(waterTypesData.filter(Boolean));
            }

            //inclussions
            const inclusionsTagify = initTagify('input[name="inclusions"]', {
                whitelist: {!! json_encode(collect($inclusions)->sortBy('value')->values()->toArray()) !!},
                dropdown: {
                    maxItems: Infinity,
                    classname: "tagify__dropdown",
                    enabled: 0,
                    closeOnSelect: false
                }
            });
            
            const inclusionsData = {!! json_encode(collect($formData['inclusions'] ?? [])->pluck('name')->toArray()) !!};
            if (inclusionsTagify && inclusionsData) {
                inclusionsTagify.addTags(inclusionsData.filter(Boolean));
            }
            
            const experinceLevelData = {!! json_encode($formData['experience_level'] ?? []) !!};
            Object.entries(experinceLevelData).forEach(([key, value]) => {
                const checkbox = document.querySelector(`input[name="experience_level[]"][value="${value}"]`);
                if (checkbox) {
                    checkbox.checked = true;
                    checkbox.dispatchEvent(new Event('change'));
                    const container = checkbox.closest('.btn-checkbox-container');
                    if (container) {
                        container.classList.add('active');
                    }
                }
            });

            const styleOfFishingData = '{{ $formData['fishing_type'] ?? '' }}';
            if (styleOfFishingData) {
                const styleOfFishing = document.querySelector(`input[name="style_of_fishing"][value="${styleOfFishingData}"]`);
                if (styleOfFishing) {
                    styleOfFishing.checked = true;
                    styleOfFishing.dispatchEvent(new Event('change'));
                    const label = styleOfFishing.closest('label');
                    if (label) {
                        label.classList.add('active');
                    }
                }
            }

            const otherInformationData = {!! json_encode($formData['other_information'] ?? []) !!};
            Object.entries(otherInformationData).forEach(([key, value]) => {
                const checkbox = document.querySelector(`input[name="other_information[]"][value="${value['id']}"]`);
                if (checkbox) {
                    checkbox.checked = true;
                    checkbox.dispatchEvent(new Event('change'));
                    const container = checkbox.closest('.btn-checkbox-container');
                    if (container) {
                        container.classList.add('active');
                        const textarea = container.querySelector('textarea');
                        if (textarea) {
                            textarea.value = value['value'];
                            textarea.style.display = 'block';
                        }
                    }
                }
            });

            const requirementsData = {!! json_encode($formData['requirements'] ?? []) !!};
            Object.entries(requirementsData).forEach(([key, value]) => {
                const checkbox = document.querySelector(`input[name="requiements_taking_part[]"][value="${value['id']}"]`);
                if (checkbox) {
                    checkbox.checked = true;
                    checkbox.dispatchEvent(new Event('change'));
                    const container = checkbox.closest('.btn-checkbox-container');
                    if (container) {
                        container.classList.add('active');
                        const textarea = container.querySelector('textarea');
                        if (textarea) {
                            textarea.value = value['value'];
                            textarea.style.display = 'block';
                        }
                    }
                }
            });

            const recommendationsData = {!! json_encode($formData['recommendations'] ?? []) !!};
            Object.entries(recommendationsData).forEach(([key, value]) => {
                const checkbox = document.querySelector(`input[name="recommended_preparation[]"][value="${value['id']}"]`);
                if (checkbox) {
                    checkbox.checked = true;
                    checkbox.dispatchEvent(new Event('change'));
                    const container = checkbox.closest('.btn-checkbox-container');
                    if (container) {
                        container.classList.add('active');
                        const textarea = container.querySelector('textarea');
                        if (textarea) {
                            textarea.value = value['value'];
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
                    tourRadio.dispatchEvent(new Event('change'));
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
                        document.getElementById('duration_days').value = durationCount;
                    } else {
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
                    seasonalTripRadio.dispatchEvent(new Event('change'));

                    document.getElementById('monthly_selection').style.display = 'block';
                    if (monthsData && monthsData.length > 0) {
                        monthsData.forEach(month => {
                            const monthCheckbox = document.querySelector(`input[name="months[]"][value="${month}"]`);
                            if (monthCheckbox) {
                                monthCheckbox.checked = true;
                                monthCheckbox.dispatchEvent(new Event('change'));
                            }
                        });
                    }
                }
            }
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
            $textarea.prop('required', true);
        } else {
            $label.removeClass('active');
            $textarea.hide();
            $textarea.prop('required', false);
            $textarea.val('');
        }
    });

    // Boat/Shore selection
    function selectOption(option, isUpdate = false) {
        $('#boatOption, #shoreOption').removeClass('active');
        $(`#${option}Option`).addClass('active');
        $('input[name="type_of_fishing"]').val(option);
        
        if (option === 'boat') {
            $('#extraFields').show();
        } else {
            $('#extraFields').hide();
            if (isUpdate) {
                showStep(3);
            }
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
                    <span class="input-group-text">{{__('newguidings.total_price_for_number_of_guests', ['number' => '${i}'])}}</span>
                    <input type="number" class="form-control" name="price_per_person_${i}" placeholder="{{__('newguidings.price_per_person', ['number' => '${i}'])}}">
                    <span class="input-group-text">€</span>
                </div>`);
            }
        } else if (priceType === 'per_boat') {
            container.append(`<div class="input-group mt-2"><span class="input-group-text">{{__('newguidings.price')}}</span><input type="number" class="form-control" name="price_per_boat" placeholder="{{ __('newguidings.price') . ' ' . __('newguidings.per_boat')}}"><span class="input-group-text">€ {{ __('newguidings.per_boat') }}</span></div>`);
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
        var price = {!! json_encode($formData['price'] ?? []) !!};
        if (priceType === 'per_person') {
            Object.entries(prices).forEach(([key, value]) => {
                $(`input[name="price_per_person_${value.person}"]`).val(value.amount);
            });
        } else if (priceType === 'per_boat') {
            $('input[name="price_per_boat"]').val(price);
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
    
    window.extraCount = window.extraCount || 0;
    $('#add-extra').click(function() {
        extraCount++;
        const newRow = `
            <div class="extra-row d-flex mb-2">
                <div class="input-group mt-2">
                <div class="dropdown extras-dropdown">
                        <span class="input-group-text d-none d-md-block">{{__('newguidings.additional_offer')}}</span>
                        <input type="text" id="customInput_${extraCount}" name="extra_name_${extraCount}" class="form-control dropdown-toggle extras" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false" placeholder="{{__('newguidings.select_or_add_value')}}">
                        <div class="dropdown-menu w-100" id="suggestionsList_${extraCount}"></div>
                    </div>
                    <div class="price">
                    <span class="input-group-text d-none d-md-block">{{__('newguidings.price')}}</span>
                    <input type="number" class="form-control mr-2" name="extra_price_${extraCount}" placeholder="{{__('newguidings.enter_price_per_person')}}">
                    <span class="input-group-text">€ {{ __('newguidings.per_person') }}</span>
                    </div>
                </div>
                <button type="button" class="btn btn-danger btn-sm remove-extra"><i class="fas fa-trash"></i></button>
            </div>
        `;
        $('#extras-container').append(newRow);

        // Initialize dropdown logic for the new input
        initializeDropdown(`customInput_${extraCount}`, `suggestionsList_${extraCount}`);
    });

    function initializeDropdown(inputId, suggestionsListId) {
        const input = document.getElementById(inputId);
        const suggestionsList = document.getElementById(suggestionsListId);

        const suggestions = {!! json_encode($extras_prices->toArray()) !!}.sort()

        function showSuggestions() {
            const value = input.value.toLowerCase();
            suggestionsList.innerHTML = "";

            suggestions
                .filter(suggestion => suggestion.value.toLowerCase().includes(value))
                .forEach(suggestion => {
                    const option = document.createElement("a");
                    option.className = "dropdown-item";
                    option.href = "#";
                    option.textContent = suggestion.value;
                    option.addEventListener("click", function (e) {
                        e.preventDefault();
                        input.value = suggestion.value; 
                        suggestionsList.classList.remove("show"); 
                    });
                    suggestionsList.appendChild(option);
                });
            suggestionsList.classList.add("show");
        }

        input.addEventListener("focus", showSuggestions);

        input.addEventListener("input", showSuggestions);

        document.addEventListener("click", function (e) {
            if (!input.contains(e.target)) {
                suggestionsList.classList.remove("show");
            }
        });
    }

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
            
            // Scroll to error container if there are validation errors
            const errorContainer = document.getElementById('error-container');
            if (errorContainer && errorContainer.style.display === 'block') {
                errorContainer.scrollIntoView({ behavior: 'smooth', block: 'center' });
            }
        }
    }

    // // Add this function to handle form submission
    // function submitForm(form) {
    //     const formData = new FormData(form);

    //     try {
    //         if (!imageManagerLoaded) {
    //             console.error('ImageManager not initialized');
    //             return;
    //         }

    //         const croppedImages = imageManagerLoaded.getCroppedImages();
    //         formData.delete('title_image[]');

    //         croppedImages.forEach((image, index) => {
    //             if (image && image.dataUrl) {
                    
    //                 const blob = dataURItoBlob(image.dataUrl);
    //                 formData.append(`title_image[]`, blob, `cropped_image_${index}.png`);
    //             }
    //         });

    //         fetch(form.action, {
    //             method: 'POST',
    //             body: formData,
    //             headers: {
    //                 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
    //                 'Accept': 'application/json'
    //             }
    //         })
    //         .then(response => {
    //             if (!response.ok) {
    //                 return response.text().then(text => {
    //                     try {
    //                         return JSON.parse(text);
    //                     } catch (e) {
    //                         throw new Error(text);
    //                     }
    //                 });
    //             }
    //             return response.json();
    //         })
    //         .then(data => {
    //             if (data.redirect_url) {
    //                 window.location.href = data.redirect_url;
    //             } else {
    //                 displayValidationErrors(data.errors);
    //             }
    //         })
    //         .catch(error => {
    //             console.error('Error submitting form:', error);
    //             if (error instanceof Error) {
    //                 if (error.message.startsWith('<!DOCTYPE html>')) {
    //                     console.error('Server returned an HTML error page. Check server logs for details.');
    //                     alert('An unexpected error occurred. Please try again later.');
    //                 } else {
    //                     console.error(error.message);
    //                     alert(error.message);
    //                 }
    //             } else if (typeof error === 'object' && error !== null) {
    //                 displayValidationErrors(error.errors || {});
    //             } else {
    //                 alert('An unexpected error occurred. Please try again.');
    //             }
    //         })
    //         .finally(() => {
    //             const loadingScreen = document.getElementById('loadingScreen');
    //             if (loadingScreen) {
    //                 loadingScreen.style.display = 'none';
    //             }
    //         });
    //     } catch (error) {
    //         console.error('Error preparing form data:', error);
    //         alert('An error occurred while preparing the form data. Please try again.');
    //     }
    // }
    function submitForm(form) {
        const formData = new FormData(form);

        try {
            if (!imageManagerLoaded) {
                console.error('ImageManager not initialized');
                return;
            }

            const croppedImages = imageManagerLoaded.getCroppedImages();
            formData.delete('title_image[]');

            // Compression options
            const maxWidth = 1024; // Maximum width or height of the image
            const quality = 0.7;   // Compression quality (0.1 = low, 1 = high)

            // Function to compress image
            const compressImage = (dataUrl, maxWidth, quality) => {
                return new Promise((resolve, reject) => {
                    const img = new Image();
                    img.src = dataUrl;

                    img.onload = () => {
                        const canvas = document.createElement('canvas');
                        const ctx = canvas.getContext('2d');

                        // Set new dimensions proportionally
                        let width = img.width;
                        let height = img.height;

                        if (width > height && width > maxWidth) {
                            height *= maxWidth / width;
                            width = maxWidth;
                        } else if (height > width && height > maxWidth) {
                            width *= maxWidth / height;
                            height = maxWidth;
                        }

                        canvas.width = width;
                        canvas.height = height;

                        // Draw and compress the image
                        ctx.drawImage(img, 0, 0, width, height);
                        canvas.toBlob(
                            (blob) => {
                                if (blob) resolve(blob);
                                else reject(new Error('Compression failed'));
                            },
                            'image/jpeg',
                            quality
                        );
                    };

                    img.onerror = () => reject(new Error('Failed to load image for compression'));
                });
            };

            // Process cropped images
            Promise.all(
                croppedImages.map((image, index) => {
                    if (image && image.dataUrl) {
                        return compressImage(image.dataUrl, maxWidth, quality).then((compressedBlob) => {
                            formData.append(`title_image[]`, compressedBlob, `compressed_image_${index}.jpg`);
                        });
                    }
                })
            ).then(() => {
                // Submit the form after compression
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
            }).catch(error => {
                console.error('Image compression error:', error);
                alert('Failed to compress images. Please try again.');
            });

        } catch (error) {
            console.error('Error preparing form data:', error);
            alert('An error occurred while preparing the form data. Please try again.');
        }
    }


    function displayValidationErrors(errors) {
        scrollToFormCenter();
        const errorContainer = document.getElementById('error-container');
        errorContainer.innerHTML = ''; // Clear previous errors
        errorContainer.style.display = 'block'; // Show the error container

        const errorList = document.createElement('ul'); // Create a new list for errors

        for (const field in errors) {
            const errorMessage = errors[field][0]; 
            const fieldInfo = errorMapping[field]; 
            
            const listItem = document.createElement('li');
            if (fieldInfo) {
                listItem.textContent = `${fieldInfo.field} (Step ${fieldInfo.step}): ${errorMessage}`;
            } else {
                listItem.textContent = errorMessage; 
            }
            errorList.appendChild(listItem);
        }

        errorContainer.appendChild(errorList);
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
        let isValid = true;
        let errors = [];

        // Check if it's a draft submission
        const isDraft = document.querySelector('input[name="is_draft"]');
        if (isDraft && isDraft.value === '1') {
            return true;
        }

        // Helper function to validate checkbox groups with textareas
        function validateCheckboxGroup(checkboxName, groupLabel) {
            const checkedBoxes = document.querySelectorAll(`input[name="${checkboxName}"]:checked`);
            checkedBoxes.forEach(checkbox => {
                const container = checkbox.closest('.btn-checkbox-container');
                const textarea = container.querySelector('textarea');
                if (textarea && !textarea.value.trim()) {
                    const checkboxLabel = container.querySelector('label').textContent.trim();
                    errors.push(`Please provide details for the selected "${checkboxLabel}" under ${groupLabel}`);
                    isValid = false;
                }
            });
        }

        switch(step) {
            case 1:
                const fileInput = document.getElementById('title_image');
                const previewWrappers = document.querySelectorAll('.image-preview-wrapper');
                
                if (!fileInput.files.length && !previewWrappers.length) {
                    errors.push('Please upload at least one image.');
                    isValid = false;
                }

                if (!previewWrappers || previewWrappers.length < 5) {
                    errors.push('Please upload at least 5 images.');
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
                const typeOfFishing = document.getElementById('type_of_fishing').value;
                if (!typeOfFishing) {
                    errors.push('Please select a type of fishing.');
                    isValid = false;
                }
                if (typeOfFishing === 'boat') {
                    if (!document.querySelector('input[name="type_of_boat"]:checked')) {
                        errors.push('Please select a type of boat.');
                        isValid = false;
                    }
                    validateCheckboxGroup('descriptions[]', 'Boat Information');
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
                if (!document.querySelector('input[name="style_of_fishing"]:checked')) {
                    errors.push('Please select a style of fishing.');
                    isValid = false;
                }
                break;
            case 4:
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
                break;
            case 5:
                validateCheckboxGroup('other_information[]', 'Other Information');
                validateCheckboxGroup('requiements_taking_part[]', 'Requirements');
                validateCheckboxGroup('recommended_preparation[]', 'Recommendations');
                break;
            case 6:
                if (!document.querySelector('input[name="tour_type"]:checked')) {
                    errors.push('Please select a tour type.');
                    isValid = false;
                }
                if (!document.querySelector('input[name="duration"]:checked')) {
                    errors.push('Please select a duration.');
                    isValid = false;

                    const selectedDuration = document.querySelector('input[name="duration"]:checked').value;
                    if (selectedDuration === 'multi_day') {
                        if (!document.getElementById('duration_days').value.trim()) {
                            errors.push('Number of days is required.');
                            isValid = false;
                        }
                    } else {
                        if (!document.getElementById('duration_hours').value.trim()) {
                            errors.push('Number of hours is required.');
                            isValid = false;
                        }
                    }
                }
                if (!document.getElementById('no_guest').value.trim()) {
                    errors.push('Number of guests is required.');
                    isValid = false;
                }
                if (!document.querySelector('input[name="price_type"]:checked')) {
                    errors.push('Please select a price type.');
                    isValid = false;
                }
                
                // if (!document.getElementById('inclusions').value.trim()) {
                //     errors.push('Included in the price are required.');
                //     isValid = false;
                // }
                break;
            case 7:
                if (!document.querySelector('input[name="allowed_booking_advance"]:checked')) {
                    errors.push('Please select an allowed booking advance.');
                    isValid = false;
                }
                if (!document.querySelector('input[name="booking_window"]:checked')) {
                    errors.push('Please select a booking window.');
                    isValid = false;
                }
                
                const seasonalTripValue = document.querySelector('input[name="seasonal_trip"]').value;
                if (!seasonalTripValue) {
                    errors.push('Please select a seasonal trip option.');
                    isValid = false;
                } else {                    
                    // If seasonal_monthly is selected, check if at least one month is selected
                    if (seasonalTripValue === 'season_monthly') {
                        const selectedMonths = document.querySelectorAll('input[name="months[]"]:checked');
                        if (selectedMonths.length === 0) {
                            errors.push('Please select at least one month for seasonal trips.');
                            isValid = false;
                        }
                    }
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
        // Prevent invalid step numbers
        if (stepNumber < 1 || stepNumber > totalSteps) {
            console.error('Invalid step number:', stepNumber);
            return;
        }

        // Only validate when moving forward
        if (stepNumber > currentStep && !validateStep(currentStep)) {
            console.error('Validation failed for current step');
            return;
        }

        // Update step visibility
        $('.step').removeClass('active');
        $(`#step${stepNumber}`).addClass('active');
        
        // Update step button states
        $('.step-button').removeClass('active');
        $(`.step-button[data-step="${stepNumber}"]`).addClass('active');
        
        // Update current step
        currentStep = stepNumber;

        // Scroll form into view
        scrollToFormCenter();

        // Update button visibility
        const isUpdate = document.getElementById('is_update').value === '1';
        $(`#saveDraftBtn${stepNumber}`).toggle(!isUpdate);
        $(`#submitBtn${stepNumber}`).toggle(isUpdate || currentStep === totalSteps);
        $(`#prevBtn${stepNumber}`).toggle(currentStep > 1);
        $(`#nextBtn${stepNumber}`).toggle(currentStep < totalSteps);
    }

    // Update the next button click handlers
    $(document).off('click', '[id^="nextBtn"]').on('click', '[id^="nextBtn"]', function(e) {
        e.preventDefault(); // Prevent any default behavior
        e.stopPropagation(); // Prevent event bubbling
        
        if (validateStep(currentStep)) {
            const nextStep = currentStep + 1;
            showStep(nextStep);
        }
    });

    // Update the previous button click handlers similarly
    $(document).off('click', '[id^="prevBtn"]').on('click', '[id^="prevBtn"]', function(e) {
        e.preventDefault();
        e.stopPropagation();
        showStep(currentStep - 1);
    });

    // Add this function at the beginning of your script
    function initTagify(selector, options = {}) {
        const element = document.querySelector(selector);
        if (element && !element.tagify) {
            const tagify = new Tagify(element, options);
            element.tagify = tagify;
            return tagify;
        }
        return element.tagify;
    }

    // Then, in your DOMContentLoaded event listener, replace the existing Tagify initializations with:
    document.addEventListener('DOMContentLoaded', function() {
        showStep(currentStep);
        initializeImageManager();

        // Boat Extras
        initTagify('input[name="boat_extras"]', {
            whitelist: {!! json_encode(collect($boat_extras)->sortBy('value')->values()->toArray()) !!},
            dropdown: {
                maxItems: Infinity,
                classname: "tagify__dropdown",
                enabled: 0,
                closeOnSelect: false
            }
        });

        // Target Fish
        initTagify('input[name="target_fish"]', {
            whitelist: {!! json_encode(collect($targets)->sortBy('value')->values()->toArray()) !!},
            dropdown: {
                maxItems: Infinity,
                classname: "tagify__dropdown",
                enabled: 0,
                closeOnSelect: false
            }
        });

        // Methods
        initTagify('input[name="methods"]', {
            whitelist: {!! json_encode(collect($methods)->sortBy('value')->values()->toArray()) !!},
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
            whitelist: {!! json_encode(collect($waters)->sortBy('value')->values()->toArray()) !!},
            maxTags: 10,
            dropdown: {
                maxItems: Infinity,
                classname: "tagify__dropdown",
                enabled: 0,
                closeOnSelect: false
            }
        });

        // Inclusions
        initTagify('input[name="inclusions"]', {
            whitelist: {!! json_encode(collect($inclusions)->sortBy('value')->values()->toArray()) !!},
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
            const selectedDuration = $(this).val();
            const durationDetails = $('#duration_details');
            const hoursInput = $('#hours_input');
            const daysInput = $('#days_input');

            durationDetails.show(); // Show the duration details section

            console.log(selectedDuration);
            if (selectedDuration === 'multi_day') {
                daysInput.show(); // Show days input for multi-day
                hoursInput.hide(); // Hide hours input
            } else {
                hoursInput.show(); // Show hours input for half/full day
                daysInput.hide(); // Hide days input
            }
        });
        
        const imageUploadInput = document.getElementById('title_image');
        if (imageUploadInput) {
            imageUploadInput.addEventListener('change', function(event) {
                if (imageManagerLoaded) {
                    try {
                        imageManagerLoaded.handleFileSelect(event.target.files);
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

        // Add click handlers for step buttons
        document.querySelectorAll('.step-button').forEach(button => {
            button.addEventListener('click', function() {
                const targetStep = parseInt(this.dataset.step);
                const currentStepElement = document.querySelector('.step.active');
                const currentStepNumber = parseInt(currentStepElement.id.replace('step', ''));

                // Only allow moving to previous steps or the next immediate step
                if (targetStep < currentStepNumber || targetStep === currentStepNumber + 1) {
                    if (targetStep > currentStepNumber && !validateStep(currentStepNumber)) {
                        return;
                    }
                    showStep(targetStep);
                }
            });
        });
    });

    // Update the form's submit event listener
    document.getElementById('newGuidingForm').addEventListener('submit', handleSubmit);
</script>

@endpush

