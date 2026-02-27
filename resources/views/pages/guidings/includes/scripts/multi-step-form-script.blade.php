@push('js_push')

<script src="https://cdnjs.cloudflare.com/ajax/libs/cropperjs/1.5.12/cropper.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/@yaireo/tagify"></script>
<script src="https://cdn.jsdelivr.net/npm/browser-image-compression@latest/dist/browser-image-compression.js"></script>
{{-- HEIC/HEIF to JPEG converter for broader mobile support --}}
<script src="https://unpkg.com/heic2any@0.0.4/dist/heic2any.min.js"></script>
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
    // Track if we've already uploaded images as part of a draft save
    window.hasUploadedImagesInDraft = window.hasUploadedImagesInDraft || false;
    function isAdminEdit() {
        const el = document.getElementById('is_admin_guiding_form');
        const isUpdateEl = document.getElementById('is_update');
        return el && el.value === '1' && isUpdateEl && isUpdateEl.value === '1';
    }
    window.isAdminEdit = isAdminEdit;
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
        // desc_departure_time: { field: 'Departure Time', step: 5 },
        tour_type: { field: 'Tour Type', step: 7 },
        duration: { field: 'Duration', step: 7 },
        no_guest: { field: 'Number of Guests', step: 7 },
        price_type: { field: 'Price Type', step: 7 },
        allowed_booking_advance: { field: 'Allowed Booking Advance', step: 8 },
        booking_window: { field: 'Booking Window', step: 8 },
        seasonal_trip: { field: 'Seasonal Trip', step: 8 },
    };
    
    function initAutocomplete() {
        const locationInput = document.getElementById('location');
        if (!locationInput) {
            return;
        }

        // Gracefully handle cases where Google Maps / Places is not loaded
        if (
            typeof window.google === 'undefined' ||
            !google.maps ||
            !google.maps.places ||
            !google.maps.places.Autocomplete
        ) {
            console.warn('Google Places Autocomplete is not available on this page.');
            return;
        }

        autocomplete = new google.maps.places.Autocomplete(
            locationInput,
            {
                types: ['(regions)']
            }
        );

        autocomplete.addListener('place_changed', function () {
            const place = autocomplete.getPlace();
            if (!place.geometry || !place.address_components) return;
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

    // Load Google Maps API with Places library when not already loaded (required for Create Guiding location autocomplete)
    function loadGoogleMapsAPIForGuiding() {
        if (typeof window.google !== 'undefined' && window.google.maps && window.google.maps.places) {
            initAutocomplete();
            return;
        }
        if (window.__guidingMapsScriptLoading) return;
        window.__guidingMapsScriptLoading = true;
        var script = document.createElement('script');
        script.src = 'https://maps.googleapis.com/maps/api/js?key={{ env("GOOGLE_MAPS_API_KEY") }}&libraries=places&callback=initAutocomplete';
        script.async = true;
        script.defer = true;
        document.head.appendChild(script);
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

    $(document).on('click', '[id^="saveDraftBtn"]', async function(e) {
        e.preventDefault();
        if (isAdminEdit()) return;
        // Always redirect after saving draft
        await saveDraft(true);
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
        let loadingScreen = document.getElementById('loadingScreen');
        if (!loadingScreen) {
            loadingScreen = document.createElement('div');
            loadingScreen.id = 'loadingScreen';
            loadingScreen.style.cssText = `
                position: fixed;
                top: 0;
                left: 0;
                width: 100%;
                height: 100%;
                background: linear-gradient(135deg, rgba(52, 152, 219, 0.9), rgba(41, 128, 185, 0.9));
                display: flex;
                flex-direction: column;
                justify-content: center;
                align-items: center;
                z-index: 9999;
                backdrop-filter: blur(10px);
            `;
            
            // Create the main container
            const container = document.createElement('div');
            container.style.cssText = `
                display: flex;
                flex-direction: column;
                align-items: center;
                text-align: center;
            `;
            
            // Create fishing boat icon with animation
            const boatIcon = document.createElement('div');
            boatIcon.innerHTML = '🚤';
            boatIcon.style.cssText = `
                font-size: 4rem;
                margin-bottom: 1rem;
                animation: float 3s ease-in-out infinite;
            `;
            
            // Create waves animation
            const wavesContainer = document.createElement('div');
            wavesContainer.style.cssText = `
                position: relative;
                width: 200px;
                height: 20px;
                margin-bottom: 2rem;
            `;
            
            for (let i = 0; i < 3; i++) {
                const wave = document.createElement('div');
                wave.style.cssText = `
                    position: absolute;
                    top: 0;
                    left: 50%;
                    transform: translateX(-50%);
                    width: ${60 + i * 40}px;
                    height: 4px;
                    background: rgba(255, 255, 255, ${0.7 - i * 0.2});
                    border-radius: 2px;
                    animation: wave ${2 + i * 0.5}s ease-in-out infinite;
                    animation-delay: ${i * 0.3}s;
                `;
                wavesContainer.appendChild(wave);
            }
            
            // Create loading dots
            const dotsContainer = document.createElement('div');
            dotsContainer.style.cssText = `
                display: flex;
                gap: 8px;
                margin-bottom: 1rem;
            `;
            
            for (let i = 0; i < 3; i++) {
                const dot = document.createElement('div');
                dot.style.cssText = `
                    width: 12px;
                    height: 12px;
                    background: white;
                    border-radius: 50%;
                    animation: bounce 1.4s ease-in-out infinite both;
                    animation-delay: ${i * 0.16}s;
                `;
                dotsContainer.appendChild(dot);
            }
            
            // Create loading text with typing effect
            const loadingText = document.createElement('div');
            loadingText.style.cssText = `
                color: white;
                font-size: 1.2rem;
                font-weight: 500;
                font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
                margin-bottom: 0.5rem;
            `;
            
            const subText = document.createElement('div');
            subText.style.cssText = `
                color: rgba(255, 255, 255, 0.8);
                font-size: 0.9rem;
                font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            `;
            
            // Add all elements to container
            container.appendChild(boatIcon);
            container.appendChild(wavesContainer);
            container.appendChild(dotsContainer);
            container.appendChild(loadingText);
            container.appendChild(subText);
            
            loadingScreen.appendChild(container);
            
            // Add CSS animations
            const keyframes = `
                @keyframes spin {
                    0% { transform: rotate(0deg); }
                    100% { transform: rotate(360deg); }
                }
                
                @keyframes float {
                    0%, 100% { transform: translateY(0px); }
                    50% { transform: translateY(-10px); }
                }
                
                @keyframes wave {
                    0%, 100% { transform: translateX(-50%) scaleX(1); opacity: 0.7; }
                    50% { transform: translateX(-50%) scaleX(1.2); opacity: 1; }
                }
                
                @keyframes bounce {
                    0%, 80%, 100% { 
                        transform: scale(0);
                        opacity: 0.5;
                    } 
                    40% { 
                        transform: scale(1);
                        opacity: 1;
                    }
                }
                
                @keyframes fadeIn {
                    from { opacity: 0; transform: translateY(20px); }
                    to { opacity: 1; transform: translateY(0); }
                }
            `;
            
            if (!document.getElementById('interactive-loader-styles')) {
                const style = document.createElement('style');
                style.id = 'interactive-loader-styles';
                style.textContent = keyframes;
                document.head.appendChild(style);
            }
            
            // Animate text changes
            const messages = [
                'Preparing your fishing adventure...',
                'Setting up your guide experience...',
                'Loading fishing spots...',
                'Getting everything ready...'
            ];
            
            let messageIndex = 0;
            loadingText.textContent = messages[0];
            subText.textContent = 'Please wait a moment';
            
            const textInterval = setInterval(() => {
                messageIndex = (messageIndex + 1) % messages.length;
                loadingText.style.animation = 'fadeIn 0.5s ease-in-out';
                loadingText.textContent = messages[messageIndex];
                
                setTimeout(() => {
                    loadingText.style.animation = '';
                }, 500);
            }, 2000);
            
            // Store interval reference to clear it later
            loadingScreen.textInterval = textInterval;
            
            document.body.appendChild(loadingScreen);
        }
        loadingScreen.style.display = 'flex';
    }

    function hideLoadingScreen() {
        const loadingScreen = document.getElementById('loadingScreen');
        if (loadingScreen) {
            // Clear the text animation interval to prevent memory leaks
            if (loadingScreen.textInterval) {
                clearInterval(loadingScreen.textInterval);
                loadingScreen.textInterval = null;
            }
            loadingScreen.style.display = 'none';
        }
    }

    async function saveDraft(shouldRedirect = false) {
        const form = document.getElementById('newGuidingForm');
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

            // Always append these if present
            const guidingId = $('#guiding_id').val();
            const isUpdate = $('#is_update').val();
            if (guidingId) formData.set('guiding_id', guidingId);
            if (isUpdate) formData.set('is_update', isUpdate);

            // Append cropped images as files if available
            if (window.imageManagerLoaded && typeof imageManagerLoaded.getCroppedImages === 'function') {
                const croppedImages = imageManagerLoaded.getCroppedImages();
                if (croppedImages.length > 0) {
                    // Remove any existing title_image[] from FormData
                    formData.delete('title_image[]');
                    croppedImages.forEach((imgObj, idx) => {
                        // Convert dataURL to Blob
                        const blob = dataURLtoBlob(imgObj.dataUrl);
                        // Use the original filename if available, otherwise fallback
                        const filename = imgObj.filename || `cropped_${idx}.png`;
                        formData.append('title_image[]', blob, filename);
                    });
                }
            }

            const response = await fetch(window.saveDraftUrl, {
                method: 'POST',
                body: formData,
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                }
            });

            if (!response.ok) {
                throw new Error(`HTTP error! status: ${response.status}`);
            }

            const data = await response.json();
            
            if (data.guiding_id) {
                $('#guiding_id').val(data.guiding_id);
                $('#is_update').val(1);
                // Mark that we have already sent images at least once for this draft
                window.hasUploadedImagesInDraft = true;
            }

            if (shouldRedirect) {
                // Redirect to my guidings page
                window.location.href = '{{ route("profile.myguidings") }}';
            } else {
                // Show success message
                const errorContainer = document.getElementById('error-container');
                if (errorContainer) {
                    errorContainer.style.display = 'block';
                    errorContainer.innerHTML = '<div class="alert alert-success">Draft saved successfully!</div>';
                    scrollToFormCenter();
                    
                    // Hide success message after 3 seconds
                    setTimeout(() => {
                        errorContainer.style.display = 'none';
                    }, 3000);
                }
            }

        } catch (error) {
            console.error('Failed to save draft:', error);
            
            // Show error message
            const errorContainer = document.getElementById('error-container');
            if (errorContainer) {
                errorContainer.style.display = 'block';
                errorContainer.innerHTML = '<div class="alert alert-danger">Failed to save draft. Please try again.</div>';
                scrollToFormCenter();
            }
        } finally {
            // Hide loading screen
            hideLoadingScreen();
        }
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
                    
                    // Show min guests container if per_person is selected
                    if (priceType === 'per_person') {
                        document.getElementById('min_guests_container').style.display = 'block';
                        
                        // Handle min_guests setting
                        const hasMinGuests = {!! isset($formData['min_guests']) && $formData['min_guests'] > 0 ? 'true' : 'false' !!};
                        const minGuestsValue = {{ $formData['min_guests'] ?? 0 }};
                        
                        if (hasMinGuests) {
                            const minGuestsSwitch = document.getElementById('min_guests_switch');
                            if (minGuestsSwitch) {
                                minGuestsSwitch.checked = true;
                                $('#min_guests_input_container').show();
                                $('#min_guests').val(minGuestsValue);
                                
                                // Trigger the change event to ensure all related UI updates occur
                                $(minGuestsSwitch).trigger('change');
                            }
                        }
                    }
                    
                    // Set min_price if available
                    const minPrice = {{ $formData['min_price'] ?? 0 }};
                    if (minPrice > 0) {
                        // Find the price input field and set its value
                        setTimeout(() => {
                            const priceInput = document.querySelector('input[name="price"]');
                            if (priceInput) {
                                priceInput.value = minPrice;
                            }
                        }, 100); // Small delay to ensure dynamic fields are created
                    }
                }
            }

            const extras = {!! json_encode($formData['pricing_extra'] ?? []) !!};
            
            if (extras && extras.length > 0) {
                // Clear existing extras first
                $('#extras-container').empty();
                
                // Reset extraCount
                extraCount = 0;
                
                extras.forEach((extra, index) => {
                    extraCount++;
                    const newRow = `
                        <div class="extra-row d-flex mb-2">
                            <div class="input-group mt-2">
                                <div class="dropdown extras-dropdown">
                                    <span class="input-group-text d-none d-md-block">{{__('newguidings.additional_offer')}}</span>
                                    <input type="text" id="customInput_${extraCount}" name="extra_name_${extraCount}" class="form-control dropdown-toggle extras" value="${extra.name}" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false" placeholder="{{__('newguidings.select_or_add_value')}}">
                                    <div class="dropdown-menu w-100" id="suggestionsList_${extraCount}"></div>
                                </div>
                                <div class="price">
                                    <span class="input-group-text d-none d-md-block">{{__('newguidings.price')}}</span>
                                    <input type="number" class="form-control mr-2" name="extra_price_${extraCount}" value="${extra.price}" placeholder="{{__('newguidings.enter_price_per_person')}}">
                                    <span class="input-group-text">€ {{ __('newguidings.per_person') }}</span>
                                </div>
                            </div>
                            <button type="button" class="btn btn-danger btn-sm remove-extra"><i class="fas fa-trash"></i></button>
                        </div>
                    `;
                    $('#extras-container').append(newRow);
                    
                    // Initialize dropdown for this row
                    initializeDropdown(`customInput_${extraCount}`, `suggestionsList_${extraCount}`);
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



            const weekdaysData = {!! json_encode($formData['weekdays'] ?? []) !!};
            if (weekdaysData && weekdaysData.length > 0) {
                weekdaysData.forEach(weekday => {
                    const checkbox = document.querySelector(`input[name="weekdays[]"][value="${weekday}"]`);
                    if (checkbox) {
                        checkbox.checked = true;
                        checkbox.dispatchEvent(new Event('change'));
                    }
                });
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

    // Time-of-day (Startzeit) checkbox button functionality
    $('.time-of-day-checkbox').change(function() {
        var $label = $('label[for="' + this.id + '"]');
        $label.toggleClass('active', this.checked);
    });

    // Initialize time-of-day buttons on load (for edit mode / pre-checked values)
    $('.time-of-day-checkbox').each(function() {
        var $label = $('label[for="' + this.id + '"]');
        $label.toggleClass('active', this.checked);
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

    // Function to update price fields based on min guests
    function updatePriceFieldsBasedOnMinGuests() {
        const minGuestsSwitch = document.getElementById('min_guests_switch');
        const minGuestsInput = document.getElementById('min_guests');
        const container = $('#dynamic-price-fields-container');
        
        if (!minGuestsSwitch || !minGuestsInput || !container) return;
        
        const isMinGuestsEnabled = minGuestsSwitch.checked;
        const minGuests = isMinGuestsEnabled ? parseInt(minGuestsInput.value) || 1 : 1;
        const maxGuests = parseInt($('#no_guest').val()) || 1;
        
        // Update all price input fields
        for (let i = 1; i <= maxGuests; i++) {
            const priceInput = container.find(`input[name="price_per_person_${i}"]`);
            if (priceInput.length) {
                const shouldBeDisabled = isMinGuestsEnabled && i < minGuests;
                priceInput.prop('disabled', shouldBeDisabled);
                
                // If disabled, set value to 0
                if (shouldBeDisabled) {
                    priceInput.val(0);
                }
                
                // Add visual indication for disabled fields
                const inputGroup = priceInput.closest('.input-group');
                if (inputGroup.length) {
                    if (shouldBeDisabled) {
                        inputGroup.addClass('opacity-50');
                    } else {
                        inputGroup.removeClass('opacity-50');
                        inputGroup.find('.min-guests-note').remove();
                    }
                }
            }
        }
    }

    // Add event listener for min guests switch
    $('#min_guests_switch').change(function() {
        const isChecked = $(this).is(':checked');
        $('#min_guests_input_container').toggle(isChecked);
        
        // Initialize or update the min guests input
        if (isChecked) {
            const maxGuests = parseInt($('#no_guest').val()) || 1;
            $('#min_guests').attr({
                'max': maxGuests,
                'min': 1,
                'required': true
            }).val(Math.min($('#min_guests').val() || 1, maxGuests));
        } else {
            $('#min_guests').prop('required', false);
        }
        
        // Only update price fields if price type is per_person
        if ($('input[name="price_type"]:checked').val() === 'per_person') {
            updatePriceFieldsBasedOnMinGuests();
        }
    });

    // Ensure the min_guests_container is only shown when price_type is per_person
    $('input[name="price_type"]').change(function() {
        var priceType = $(this).val();
        var container = $('#dynamic-price-fields-container');
        container.empty();

        // Show min guests option only for per_person pricing
        $('#min_guests_container').toggle(priceType === 'per_person');

        if (priceType === 'per_person') {
            var guestCount = parseInt($('#no_guest').val()) || 1;
            for (var i = 1; i <= guestCount; i++) {
                container.append(`<div class="input-group mt-2">
                    <span class="input-group-text" style="min-width: 250px; flex: 0 0 auto;">{{__('newguidings.total_price_for_number_of_guests', ['number' => '${i}'])}}</span>
                    <input type="number" class="form-control" name="price_per_person_${i}" placeholder="{{__('newguidings.price_per_person', ['number' => '${i}'])}}">
                    <span class="input-group-text">€</span>
                </div>`);
            }
            
            // Apply min guests logic after creating the fields
            updatePriceFieldsBasedOnMinGuests();
        } else if (priceType === 'per_boat') {
            container.append(`<div class="input-group mt-2">
                <span class="input-group-text" style="min-width: 250px; flex: 0 0 auto;">{{__('newguidings.price')}}</span>
                <input type="number" class="form-control" name="price_per_boat" placeholder="{{ __('newguidings.price') }} {{ __('newguidings.per_boat') }}">
                <span class="input-group-text">€ {{ __('newguidings.per_boat') }}</span>
            </div>`);
            
            // Hide min guests container when switching to per_boat
            $('#min_guests_switch').prop('checked', false).trigger('change');
        }

        // Populate fields if editing
        if ($('#is_update').val() === '1') {
            populatePriceFields(priceType);
        }
    });

    // Modify the populatePriceFields function to respect min guests
    function populatePriceFields(priceType) {
        var prices = {!! json_encode($formData['prices'] ?? []) !!};
        var price = {!! json_encode($formData['price'] ?? []) !!};
        if (priceType === 'per_person') {
            Object.entries(prices).forEach(([key, value]) => {
                $(`input[name="price_per_person_${value.person}"]`).val(value.amount);
            });
            
            // Apply min guests logic after populating the fields
            updatePriceFieldsBasedOnMinGuests();
        } else if (priceType === 'per_boat') {
            $('input[name="price_per_boat"]').val(price);
        }
    }

    // Update the no_guest change handler to properly limit min guests
    $('#no_guest').change(function() {
        if ($('input[name="price_type"]:checked').val() === 'per_person') {
            $('input[name="price_type"]:checked').change();
            
            // Make sure min guests is not greater than max guests - 1
            const maxGuests = parseInt($(this).val()) || 1;
            const minGuestsInput = $('#min_guests');
            const currentMinGuests = parseInt(minGuestsInput.val()) || 1;
            
            if (currentMinGuests > maxGuests) {
                minGuestsInput.val(maxGuests);
            }
            
            // Update min guests max attribute
            minGuestsInput.attr('max', maxGuests);
            minGuestsInput.attr('min', 1);
            
            // Update price fields to reflect the new min guests value
            updatePriceFieldsBasedOnMinGuests();
        }
    });

    // Also update the min guests input handler to enforce limits
    $('#min_guests').on('change input', function() {
        const maxGuests = parseInt($('#no_guest').val()) || 1;
        const currentValue = parseInt($(this).val()) || 1;
        
        // Enforce the upper limit
        if (currentValue > maxGuests) {
            $(this).val(maxGuests);
        }
        
        // Enforce the lower limit
        if (currentValue < 1) {
            $(this).val(1);
        }
        
        if ($('input[name="price_type"]:checked').val() === 'per_person') {
            updatePriceFieldsBasedOnMinGuests();
        }
    });

    // Make sure document ready initializes everything properly
    $(document).ready(function() {
        // ... existing code ...
        
        // Initialize min guests container and input
        const minGuestsSwitch = document.getElementById('min_guests_switch');
        if (minGuestsSwitch) {
            // Set initial state of min guests input container
            $('#min_guests_input_container').toggle(minGuestsSwitch.checked);
            
            // Set initial min/max attributes for min_guests input
            const maxGuests = parseInt($('#no_guest').val()) || 1;
            $('#min_guests').attr({
                'max': maxGuests,
                'min': 1
            });
            
            // Only show min guests container if price type is per_person
            const priceType = $('input[name="price_type"]:checked').val();
            $('#min_guests_container').toggle(priceType === 'per_person');
        }
        
        // ... existing code ...
    });

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
        // Check if any empty extra rows exist
        const emptyRows = Array.from(document.querySelectorAll('.extra-row')).filter(row => {
            const input = row.querySelector('input[name^="extra_name_"]');
            return input && !input.value.trim();
        });

        // If there are empty rows and we're in edit mode, don't add new row
        const isEditMode = document.getElementById('is_update').value === '1';
        if (isEditMode && emptyRows.length > 0) {
            return;
        }

        extraCount++;
        const newRow = `
            <div class="extra-row d-flex mb-2">
                <div class="input-group mt-2">
                    <div class="dropdown extras-dropdown">
                        <span class="input-group-text d-none d-md-block">{{__('newguidings.additional_offer')}}</span>
                        <input type="text" id="customInput_${extraCount}" name="extra_name_${extraCount}" class="form-control dropdown-toggle extras" value="" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false" placeholder="{{__('newguidings.select_or_add_value')}}">
                        <div class="dropdown-menu w-100" id="suggestionsList_${extraCount}"></div>
                    </div>
                    <div class="price">
                        <span class="input-group-text d-none d-md-block">{{__('newguidings.price')}}</span>
                        <input type="number" class="form-control mr-2" name="extra_price_${extraCount}" value="" placeholder="{{__('newguidings.enter_price_per_person')}}">
                        <span class="input-group-text">€ {{ __('newguidings.per_person') }}</span>
                    </div>
                </div>
                <button type="button" class="btn btn-danger btn-sm remove-extra"><i class="fas fa-trash"></i></button>
            </div>
        `;
        $('#extras-container').append(newRow);
        
        // Initialize dropdown for this row
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

    // Function to check if an element is hidden
    function isElementHidden(element) {
        if (!element) return true;
        
        const computedStyle = window.getComputedStyle(element);
        const parentContainer = element.closest('#duration_details, #hours_input, #days_input, .step');
        
        return (
            element.offsetParent === null || 
            computedStyle.display === 'none' ||
            computedStyle.visibility === 'hidden' ||
            (parentContainer && window.getComputedStyle(parentContainer).display === 'none') ||
            element.closest('[style*="display: none"]') !== null ||
            element.closest('[style*="display:none"]') !== null ||
            (parentContainer && parentContainer.classList.contains('step') && !parentContainer.classList.contains('active'))
        );
    }

    // Function to remove validation constraints from hidden fields
    function removeValidationFromHiddenFields(form) {
        if (!form) return;
        
        // Find all input fields with min/max attributes
        const inputs = form.querySelectorAll('input[type="number"][min], input[type="number"][max], input[type="number"][required]');
        
        inputs.forEach(input => {
            if (isElementHidden(input)) {
                // Remove validation attributes from hidden fields
                input.removeAttribute('min');
                input.removeAttribute('max');
                input.removeAttribute('required');
            }
        });
    }

    // Function to initialize validation attributes based on field visibility
    function initializeFieldValidation() {
        const form = document.getElementById('newGuidingForm');
        if (!form) return;
        
        // Remove validation from all hidden fields on page load
        removeValidationFromHiddenFields(form);
        
        // Prevent HTML5 validation errors for hidden fields
        form.addEventListener('invalid', function(e) {
            const input = e.target;
            if (isElementHidden(input)) {
                // Remove validation attributes to prevent the error
                input.removeAttribute('min');
                input.removeAttribute('max');
                input.removeAttribute('required');
                // Prevent the default validation message
                e.preventDefault();
                // Clear any custom validity
                input.setCustomValidity('');
            }
        }, true); // Use capture phase to catch early
        
        // Also handle on individual inputs
        form.querySelectorAll('input[type="number"]').forEach(input => {
            input.addEventListener('invalid', function(e) {
                if (isElementHidden(this)) {
                    this.removeAttribute('min');
                    this.removeAttribute('max');
                    this.removeAttribute('required');
                    this.setCustomValidity('');
                    e.preventDefault();
                    e.stopPropagation();
                }
            }, true);
        });
        
        // Set up observer to watch for visibility changes
        const observer = new MutationObserver(() => {
            removeValidationFromHiddenFields(form);
        });
        
        // Observe changes to style attributes and class changes
        const durationDetails = document.getElementById('duration_details');
        if (durationDetails) {
            observer.observe(durationDetails, {
                attributes: true,
                attributeFilter: ['style', 'class']
            });
        }
        
        // Observe all steps for visibility changes
        document.querySelectorAll('.step').forEach(step => {
            observer.observe(step, {
                attributes: true,
                attributeFilter: ['class']
            });
        });
    }

    // Modify the handleSubmit function
    function handleSubmit(event) {
        event.preventDefault();
        const form = event.target;
        const isDraft = form.querySelector('input[name="is_draft"]');
        
        // Check if the click originated from an image control button
        if (event.submitter && event.submitter.closest('.image-controls')) {
            return;
        }
        
        // Remove validation constraints from hidden fields before submission
        removeValidationFromHiddenFields(form);
        
        // Show loading screen
        showLoadingScreen();
        
        if (isDraft && isDraft.value === '1') {
            submitForm(form);
        } else if (form.noValidate || validateStep(currentStep)) {
            submitForm(form);
        } else {
            // Hide loading screen if validation fails
            hideLoadingScreen();
            
            // Scroll to error container if there are validation errors
            const errorContainer = document.getElementById('error-container');
            if (errorContainer && errorContainer.style.display === 'block') {
                errorContainer.scrollIntoView({ behavior: 'smooth', block: 'center' });
            }
        }
    }

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
                        let fileName;
                        
                        // Try to use the original filename if available
                        if (image.filename) {
                            fileName = image.filename;
                        } else {
                            // Create a unique filename with timestamp and index
                            const timestamp = new Date().getTime();
                            
                            // Try to extract the MIME type from the data URL
                            let extension = 'jpg'; // Default extension
                            try {
                                const mimeType = image.dataUrl.match(/data:([a-zA-Z0-9]+\/[a-zA-Z0-9-.+]+).*,/)[1];
                                if (mimeType.includes('png')) {
                                    extension = 'png';
                                } else if (mimeType.includes('gif')) {
                                    extension = 'gif';
                                } else if (mimeType.includes('webp')) {
                                    extension = 'webp';
                                }
                            } catch (e) {
                            }
                            
                            fileName = `image_${timestamp}_${index}.${extension}`;
                        }
                        
                        return compressImage(image.dataUrl, maxWidth, quality).then((compressedBlob) => {
                            formData.append(`title_image[]`, compressedBlob, fileName);
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
                        hideLoadingScreen();
                    });
            }).catch(error => {
                console.error('Image compression error:', error);
                hideLoadingScreen();
                alert('Failed to compress images. Please try again.');
            });

        } catch (error) {
            console.error('Error preparing form data:', error);
            hideLoadingScreen();
            alert('An error occurred while preparing the form data. Please try again.');
        }
    }


    function displayValidationErrors(errors) {
        // Hide loading screen when displaying errors
        hideLoadingScreen();
        
        scrollToFormCenter();
        const errorContainer = document.getElementById('error-container');
        errorContainer.innerHTML = ''; // Clear previous errors
        errorContainer.style.display = 'block'; // Show the error container

        const errorList = document.createElement('ul'); // Create a new list for errors

        if (!errors || Object.keys(errors).length === 0) {
            // Show default error message if errors is undefined or empty
            const listItem = document.createElement('li');
            listItem.textContent = 'An unexpected error occurred. Please try again. | If Error persists, contact support.';
            errorList.appendChild(listItem);
        } else {
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

        // Skip all step validation on admin edit (validate only on final Save)
        if (isAdminEdit()) {
            return true;
        }
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
                // Populate form data when step 4 is shown (departure time checkboxes)
                if (typeof populateEditFormData === 'function') {
                    populateEditFormData();
                }
                
                if (!document.getElementById('desc_course_of_action').value.trim()) {
                    errors.push('Course of action description is required.');
                    isValid = false;
                }
                if (!document.getElementById('desc_starting_time').value.trim()) {
                    errors.push('Starting time description is required.');
                    isValid = false;
                }
                // Validate departure time
                if (!document.querySelector('input[name="desc_departure_time[]"]:checked')) {
                    errors.push('Please select at least one departure time.');
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
                
                // Add validation for min guests
                const minGuestsSwitch = document.getElementById('min_guests_switch');
                if (minGuestsSwitch && minGuestsSwitch.checked) {
                    const minGuests = document.getElementById('min_guests').value;
                    if (!minGuests || minGuests < 1) {
                        errors.push('Minimum number of guests is required and must be at least 1.');
                        isValid = false;
                    }
                    
                    // Validate that min guests is less than max guests
                    const maxGuests = parseInt(document.getElementById('no_guest').value);
                    if (parseInt(minGuests) > maxGuests) {
                        errors.push('Minimum number of guests cannot be greater than maximum number of guests.');
                        isValid = false;
                    }
                }
                
                if (!document.getElementById('inclusions').value.trim()) {
                    // errors.push('Included in the price are required.');
                    // isValid = false;
                }
                break;
            case 7:
                // Populate form data when step 7 is shown (weekday availability)
                if (typeof populateEditFormData === 'function') {
                    populateEditFormData();
                }
                
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
            // Hide loading screen when validation fails
            hideLoadingScreen();
            errorContainer.style.display = 'block';
            errorContainer.innerHTML = '<ul>' + errors.map(error => `<li>${error}</li>`).join('') + '</ul>';
            return false;
        }

        return true;
    }

    // Step navigation
    async function showStep(stepNumber) {
        // Prevent invalid step numbers
        if (stepNumber < 1 || stepNumber > totalSteps) {
            console.error('Invalid step number:', stepNumber);
            hideLoadingScreen();
            return;
        }

        // Only validate when moving forward (skip validation on admin edit - validate only on final save)
        if (stepNumber > currentStep && !isAdminEdit() && !validateStep(currentStep)) {
            console.error('Validation failed for current step');
            hideLoadingScreen();
            return;
        }

        // Call AJAX to save progress when moving forward (skip auto-save on admin edit - save only on final step button)
        if (stepNumber > currentStep && !isAdminEdit()) {
            try {                
                // Update loading screen message to indicate saving
                const loadingScreen = document.getElementById('loadingScreen');
                if (loadingScreen) {
                    const loadingText = loadingScreen.querySelector('div[style*="font-size: 1.2rem"]');
                    if (loadingText) {
                        loadingText.textContent = 'Saving your progress...';
                    }
                }
                
                await saveStepProgress(currentStep);
                
                // Update loading screen message to indicate proceeding
                if (loadingScreen) {
                    const loadingText = loadingScreen.querySelector('div[style*="font-size: 1.2rem"]');
                    if (loadingText) {
                        loadingText.textContent = 'Proceeding to next step...';
                    }
                }
                
            } catch (error) {
                console.error('Failed to save draft before proceeding:', error);
                hideLoadingScreen();
                // Show error message to user
                const errorContainer = document.getElementById('error-container');
                if (errorContainer) {
                    errorContainer.style.display = 'block';
                    errorContainer.innerHTML = '<div class="alert alert-danger">Failed to save progress. Please try again.</div>';
                    scrollToFormCenter();
                }
                return; // Don't proceed if save failed
            }
        }

        // Update step visibility
        $('.step').removeClass('active');
        $(`#step${stepNumber}`).addClass('active');
        
        // Update step button states
        $('.step-button').removeClass('active');
        $(`.step-button[data-step="${stepNumber}"]`).addClass('active');
        
        // Update current step
        currentStep = stepNumber;
        
        // Remove validation from hidden fields after step change
        setTimeout(() => {
            removeValidationFromHiddenFields(document.getElementById('newGuidingForm'));
        }, 50);

        // Populate form data when step 4 (departure time) or step 7 (weekday availability) is shown
        if (stepNumber === 4 || stepNumber === 7) {
            setTimeout(() => {
                if (typeof window.populateEditFormData === 'function') {
                    window.populateEditFormData();
                }
            }, 100);
        }

        // Scroll form into view
        scrollToFormCenter();

        // Update button visibility
        const isUpdate = document.getElementById('is_update').value === '1';
        const isDraft = document.getElementById('is_draft').value === '1';
        const showSaveDraft = currentStep < totalSteps && !isAdminEdit();
        // Show save draft button on all steps except the last one; hide on admin edit (save only on final step)
        $(`#saveDraftBtn${stepNumber}`).toggle(showSaveDraft);
        
        $(`#submitBtn${stepNumber}`).toggle((isUpdate && !isDraft) || currentStep === totalSteps);
        $(`#prevBtn${stepNumber}`).toggle(currentStep > 1);
        $(`#nextBtn${stepNumber}`).toggle(currentStep < totalSteps);

        // Hide loading screen after step transition is complete
        hideLoadingScreen();
    }

    // Update the next button click handlers
    $(document).off('click', '[id^="nextBtn"]').on('click', '[id^="nextBtn"]', async function(e) {
        e.preventDefault(); // Prevent any default behavior
        e.stopPropagation(); // Prevent event bubbling
        
        // Show loading screen
        showLoadingScreen();
        
        // Use setTimeout to allow the loading screen to render before validation
        setTimeout(async () => {
            try {
                if (validateStep(currentStep) || isAdminEdit()) {
                    const nextStep = currentStep + 1;
                    await showStep(nextStep); // Save will happen in showStep before proceeding (skipped on admin edit)
                } else {
                    // Hide loading if validation fails
                    hideLoadingScreen();
                }
            } catch (error) {
                console.error('Error during step transition:', error);
                hideLoadingScreen();
            }
        }, 100); // Small delay to ensure loading screen appears
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

    // Function to populate form data from edit mode
    function populateEditFormData() {
        // Check if we're in edit mode
        const isUpdate = document.getElementById('is_update');
        if (!isUpdate || isUpdate.value !== '1') {
            return;
        }

        // Set departure time checkboxes
        const timeOfDayData = {!! json_encode($formData['desc_departure_time'] ?? []) !!};
        if (timeOfDayData && timeOfDayData.length > 0) {
            timeOfDayData.forEach(timeOfDay => {
                const checkbox = document.querySelector(`input[name="desc_departure_time[]"][value="${timeOfDay}"]`);
                if (checkbox) {
                    checkbox.checked = true;
                    checkbox.dispatchEvent(new Event('change'));
                }
            });
        }

        // Set weekday availability
        const weekdayAvailabilityData = '{{ $formData['weekday_availability'] ?? '' }}';
        if (weekdayAvailabilityData) {
            const weekdayRadio = document.querySelector(`input[name="weekday_availability"][value="${weekdayAvailabilityData}"]`);
            if (weekdayRadio) {
                weekdayRadio.checked = true;
                weekdayRadio.dispatchEvent(new Event('change'));

                if (weekdayAvailabilityData === 'certain_days') {
                    const weekdaySelection = document.getElementById('weekday_selection');
                    if (weekdaySelection) {
                        weekdaySelection.style.display = 'block';
                    }

                    const weekdaysData = {!! json_encode($formData['weekdays'] ?? []) !!};
                    if (weekdaysData && weekdaysData.length > 0) {
                        weekdaysData.forEach(weekday => {
                            const checkbox = document.querySelector(`input[name="weekdays[]"][value="${weekday}"]`);
                            if (checkbox) {
                                checkbox.checked = true;
                            }
                        });
                    }
                }
            }
        }
    }

    // Then, in your DOMContentLoaded event listener, replace the existing Tagify initializations with:
    document.addEventListener('DOMContentLoaded', function() {
        // Initialize field validation to remove constraints from hidden fields
        initializeFieldValidation();
        
        showStep(currentStep);
        initializeImageManager();
        
        // Populate form data after a short delay to ensure all elements are rendered
        setTimeout(populateEditFormData, 100);
        setTimeout(populateEditFormData, 500);
        setTimeout(populateEditFormData, 1000);
        
        // Also populate when step 7 is shown (where weekday availability is)
        window.populateEditFormData = populateEditFormData;

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

        loadGoogleMapsAPIForGuiding();

        // Duration selection logic
        $('input[name="duration"]').change(function() {
            const selectedDuration = $(this).val();
            const durationDetails = $('#duration_details');
            const hoursInput = $('#hours_input');
            const daysInput = $('#days_input');
            const durationHoursField = document.getElementById('duration_hours');
            const durationDaysField = document.getElementById('duration_days');

            durationDetails.show(); // Show the duration details section

            if (selectedDuration === 'multi_day') {
                daysInput.show(); // Show days input for multi-day
                hoursInput.hide(); // Hide hours input
                
                // Remove validation constraints from hidden hours field
                if (durationHoursField) {
                    durationHoursField.removeAttribute('min');
                    durationHoursField.removeAttribute('max');
                    durationHoursField.removeAttribute('required');
                    durationHoursField.value = ''; // Clear value when hidden
                }
                
                // Restore validation constraints for visible days field
                if (durationDaysField && !isElementHidden(durationDaysField)) {
                    const dataMin = durationDaysField.getAttribute('data-min');
                    if (dataMin) {
                        durationDaysField.setAttribute('min', dataMin);
                    }
                }
            } else {
                hoursInput.show(); // Show hours input for half/full day
                daysInput.hide(); // Hide days input
                
                // Restore validation constraints for visible hours field
                if (durationHoursField && !isElementHidden(durationHoursField)) {
                    const dataMin = durationHoursField.getAttribute('data-min');
                    const dataMax = durationHoursField.getAttribute('data-max');
                    if (dataMin) {
                        durationHoursField.setAttribute('min', dataMin);
                    }
                    if (dataMax) {
                        durationHoursField.setAttribute('max', dataMax);
                    }
                }
                
                // Remove validation constraints from hidden days field
                if (durationDaysField) {
                    durationDaysField.removeAttribute('min');
                    durationDaysField.removeAttribute('required');
                    durationDaysField.value = ''; // Clear value when hidden
                }
            }
            
            // Re-check all hidden fields after visibility change
            setTimeout(() => {
                removeValidationFromHiddenFields(document.getElementById('newGuidingForm'));
            }, 100);
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
            button.addEventListener('click', async function() {
                const targetStep = parseInt(this.dataset.step);
                const currentStepElement = document.querySelector('.step.active');
                const currentStepNumber = parseInt(currentStepElement.id.replace('step', ''));

                // Only allow moving to previous steps or the next immediate step
                if (targetStep < currentStepNumber || targetStep === currentStepNumber + 1) {
                    // Show loading for step navigation
                    showLoadingScreen();
                    
                    setTimeout(async () => {
                        try {
                            if (targetStep > currentStepNumber && !isAdminEdit() && !validateStep(currentStepNumber)) {
                                hideLoadingScreen();
                                return;
                            }
                            await showStep(targetStep); // Save will happen in showStep if moving forward
                        } catch (error) {
                            console.error('Error during step button navigation:', error);
                            hideLoadingScreen();
                        }
                    }, 100);
                }
            });
        });

        // Show/hide weekday selection based on weekday availability selection
        $('input[name="weekday_availability"]').change(function() {
            $('#weekday_selection').toggle($(this).val() === 'certain_days');
        });

        // Add click handlers for submit buttons to show loading immediately
        $(document).on('click', '[id^="submitBtn"]', function(e) {
            // Don't prevent default as we want the form to submit
            showLoadingScreen();
        });
    });

    // Update the form's submit event listener
    var form = document.getElementById('newGuidingForm');
    if (form) {
        // Remove validation from hidden fields BEFORE browser validation runs
        // This must happen in capture phase, before any other handlers
        form.addEventListener('submit', function(e) {
            // Remove validation from hidden fields FIRST, synchronously
            removeValidationFromHiddenFields(form);
        }, true); // Capture phase runs first
        
        // Then handle the actual submission (this will preventDefault)
        form.addEventListener('submit', handleSubmit);
    }

    // Add these variables at the top of your script
    // Use var instead of let so duplicate script inclusions don't throw
    var saveStepProgressTimeout = null;
    var saveStepProgressLock = false;

    function saveStepProgress(stepNumber) {
        // Return a Promise to allow waiting for completion
        return new Promise((resolve, reject) => {
            // Prevent duplicate calls within 2 seconds
            if (saveStepProgressLock) {
                resolve(); // Resolve immediately for duplicate calls
                return;
            }
            saveStepProgressLock = true;

            // Release the lock after 2 seconds
            clearTimeout(saveStepProgressTimeout);
            saveStepProgressTimeout = setTimeout(() => {
                saveStepProgressLock = false;
            }, 1500);

            const form = document.getElementById('newGuidingForm');
            const formData = new FormData(form);

            formData.append('current_step', stepNumber);
            formData.append('is_draft', 1);

            // Always append these if present
            const guidingId = $('#guiding_id').val();
            const isUpdate = $('#is_update').val();
            if (guidingId) formData.append('guiding_id', guidingId);
            if (isUpdate) formData.append('is_update', isUpdate);

            // Only include image binaries on the very first step-1 draft save.
            // This avoids repeatedly sending large image payloads on every step change.
            const shouldIncludeImages = stepNumber === 1 && !window.hasUploadedImagesInDraft;

            if (!shouldIncludeImages) {
                // Ensure we don't send original large files when not needed
                formData.delete('title_image[]');
            } else if (window.imageManagerLoaded && typeof imageManagerLoaded.getCroppedImages === 'function') {
                const croppedImages = imageManagerLoaded.getCroppedImages();
                if (croppedImages.length > 0) {
                    // Replace any original title_image[] with compressed, cropped versions
                    formData.delete('title_image[]');
                    croppedImages.forEach((imgObj, idx) => {
                        const blob = dataURLtoBlob(imgObj.dataUrl);
                        const filename = imgObj.filename || `cropped_${idx}.png`;
                        formData.append('title_image[]', blob, filename);
                    });
                    // After one successful step save with images, mark as uploaded
                    window.hasUploadedImagesInDraft = true;
                }
            }

            // Use synchronous saving for step progression to avoid status timing issues
            fetch(window.saveDraftSyncUrl, {
                method: 'POST',
                body: formData,
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                }
            })
            .then(response => {
                if (!response.ok) {
                    throw new Error(`HTTP error! status: ${response.status}`);
                }
                return response.json();
            })
            .then(data => {
                if (data.guiding_id) {
                    $('#guiding_id').val(data.guiding_id);
                    $('#is_update').val(1);
                }
                resolve(data); // Resolve the promise on success
            })
            .catch(error => {
                console.error('Failed to save draft:', error);
                reject(error); // Reject the promise on error
            });
        });
    }

    // Helper function to convert data URL to Blob
    function dataURLtoBlob(dataurl) {
        const arr = dataurl.split(','), mime = arr[0].match(/:(.*?);/)[1],
            bstr = atob(arr[1]), n = bstr.length, u8arr = new Uint8Array(n);
        for (let i = 0; i < n; i++) {
            u8arr[i] = bstr.charCodeAt(i);
        }
        return new Blob([u8arr], { type: mime });
    }
    
    window.saveDraftUrl = "{{ route('profile.newguiding.save-draft') }}";
    window.saveDraftSyncUrl = "{{ route('profile.newguiding.save-draft-sync') }}";
</script>

@endpush

