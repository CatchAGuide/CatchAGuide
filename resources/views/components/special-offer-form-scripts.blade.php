@push('js_push')
<script src="https://cdnjs.cloudflare.com/ajax/libs/cropperjs/1.5.12/cropper.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/@yaireo/tagify"></script>
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
    window.totalSteps = window.totalSteps || 3;

    // Initialize form when document is ready
    document.addEventListener('DOMContentLoaded', function() {
        // Wait for jQuery to be available
        if (typeof $ !== 'undefined') {
            initializeSpecialOfferForm();
        } else {
            // Retry after a short delay
            setTimeout(function() {
                if (typeof $ !== 'undefined') {
                    initializeSpecialOfferForm();
                } else {
                    console.error('jQuery not available');
                }
            }, 100);
        }
    });

    function initializeSpecialOfferForm() {
        // Prevent multiple initialization
        if (window.specialOfferFormInitialized) {
            return;
        }
        
        window.specialOfferFormInitialized = true;
        
        // Initialize image manager using the same pattern as camp form
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
        if (document.getElementById('is_update') && document.getElementById('is_update').value === '1') {
            // Add a delay to ensure ImageManager is fully initialized
            setTimeout(() => {
                const existingImagesInput = document.getElementById('existing_images');
                const thumbnailPath = document.getElementById('thumbnail_path') ? document.getElementById('thumbnail_path').value : '';
                
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

        // Initialize location autocomplete
        initializeLocationAutocomplete();
        
        // Initialize Tagify for accommodations, rental boats, and guidings
        setTimeout(() => {
            if (typeof Tagify !== 'undefined') {
                initializeAssembliesTagify();
            } else {
                console.error('Tagify library not loaded');
            }
        }, 500);
        
        // Initialize Tagify for whats_included
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
        
        // Set up step navigation
        setupStepNavigation();
        
        // Pricing management
        setupPricing();
        
        // Set up form submission
        setupFormSubmission();
        
        // Load existing data if in edit mode
        if (document.getElementById('is_update') && document.getElementById('is_update').value == '1') {
            setTimeout(() => {
                loadExistingData();
            }, 100);
            
            // Additional fallback for image loading if ImageManager wasn't ready initially
            setTimeout(() => {
                if (window.imageManagerLoaded && document.getElementById('is_update').value === '1') {
                    const existingImagesInput = document.getElementById('existing_images');
                    const thumbnailPath = document.getElementById('thumbnail_path') ? document.getElementById('thumbnail_path').value : '';
                    
                    if (existingImagesInput && existingImagesInput.value) {
                        try {
                            const existingImages = JSON.parse(existingImagesInput.value);
                            if (Array.isArray(existingImages) && existingImages.length > 0) {
                                window.imageManagerLoaded.loadExistingImages(existingImages, thumbnailPath);
                            }
                        } catch (e) {
                            console.error('Fallback: Error loading existing images:', e);
                        }
                    }
                }
            }, 1000);
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
        }
    }

    function initializeLocationAutocomplete() {
        if (typeof google !== 'undefined' && google.maps) {
            const locationInput = document.getElementById('location');
            if (locationInput) {
                const autocomplete = new google.maps.places.Autocomplete(locationInput);
                autocomplete.addListener('place_changed', function() {
                    const place = autocomplete.getPlace();
                    if (place.geometry) {
                        const latInput = document.getElementById('latitude');
                        const lngInput = document.getElementById('longitude');
                        const countryInput = document.getElementById('country');
                        const cityInput = document.getElementById('city');
                        const regionInput = document.getElementById('region');
                        
                        if (latInput) latInput.value = place.geometry.location.lat();
                        if (lngInput) lngInput.value = place.geometry.location.lng();
                        
                        place.address_components.forEach(component => {
                            if (component.types.includes('country') && countryInput) {
                                countryInput.value = component.long_name;
                            }
                            if (component.types.includes('locality') && cityInput) {
                                cityInput.value = component.long_name;
                            }
                            if (component.types.includes('administrative_area_level_1') && regionInput) {
                                regionInput.value = component.long_name;
                            }
                        });
                    }
                });
            }
        }
    }

    function initializeAssembliesTagify() {
        // Prepare data for accommodations
        @if(isset($accommodations) && count($accommodations) > 0)
        const accommodationsData = {!! json_encode($accommodations->map(function($item) { 
            return ['value' => '(' . $item->id . ') | ' . $item->title, 'id' => $item->id, 'title' => $item->title]; 
        })->sortBy('title')->values()->toArray()) !!};
        
        const accommodationsInput = document.querySelector('#accommodations');
        if (accommodationsInput) {
            // Store Tagify instance on the input element so it can be accessed later
            accommodationsInput.tagify = new Tagify(accommodationsInput, {
                whitelist: accommodationsData,
                dropdown: {
                    maxItems: Infinity,
                    classname: "tagify__dropdown",
                    enabled: 0,
                    closeOnSelect: false
                },
                templates: {
                    tag: function(tagData) {
                        return `<tag title="${tagData.title || tagData.value}" 
                                    contenteditable='false' 
                                    spellcheck='false' 
                                    tabIndex="-1" 
                                    class="${this.settings.classNames.tag} ${tagData.class ? tagData.class : ""}" 
                                    ${this.getAttributes(tagData)}>
                                    <x title='remove tag' class='tagify__tag__removeBtn'></x>
                                    <div>
                                        <span class='tagify__tag-text'>${tagData.value}</span>
                                    </div>
                                </tag>`;
                    }
                }
            });
            
            accommodationsInput.tagify.on('add', function(e) {
                updateAccommodationsIds();
                updateSelectedAccommodationsCards();
            });
            
            accommodationsInput.tagify.on('remove', function(e) {
                updateAccommodationsIds();
                updateSelectedAccommodationsCards();
            });
            
            // Load existing data
            @if(isset($formData['accommodations']) && is_array($formData['accommodations']))
            const existingAccommodations = {!! json_encode(array_map(function($id) use ($accommodations) {
                $acc = $accommodations->firstWhere('id', $id);
                return $acc ? ['value' => '(' . $acc->id . ') | ' . $acc->title, 'id' => $acc->id, 'title' => $acc->title] : null;
            }, $formData['accommodations'])) !!};
            if (existingAccommodations && Array.isArray(existingAccommodations)) {
                accommodationsInput.tagify.addTags(existingAccommodations.filter(Boolean));
            }
            @endif
        }
        @endif
        
        // Prepare data for rental boats
        @if(isset($rentalBoats) && count($rentalBoats) > 0)
        const rentalBoatsData = {!! json_encode($rentalBoats->map(function($item) { 
            return ['value' => '(' . $item->id . ') | ' . $item->title, 'id' => $item->id, 'title' => $item->title]; 
        })->sortBy('title')->values()->toArray()) !!};
        
        const rentalBoatsInput = document.querySelector('#rental_boats');
        if (rentalBoatsInput) {
            // Store Tagify instance on the input element so it can be accessed later
            rentalBoatsInput.tagify = new Tagify(rentalBoatsInput, {
                whitelist: rentalBoatsData,
                dropdown: {
                    maxItems: Infinity,
                    classname: "tagify__dropdown",
                    enabled: 0,
                    closeOnSelect: false
                },
                templates: {
                    tag: function(tagData) {
                        return `<tag title="${tagData.title || tagData.value}" 
                                    contenteditable='false' 
                                    spellcheck='false' 
                                    tabIndex="-1" 
                                    class="${this.settings.classNames.tag} ${tagData.class ? tagData.class : ""}" 
                                    ${this.getAttributes(tagData)}>
                                    <x title='remove tag' class='tagify__tag__removeBtn'></x>
                                    <div>
                                        <span class='tagify__tag-text'>${tagData.value}</span>
                                    </div>
                                </tag>`;
                    }
                }
            });
            
            rentalBoatsInput.tagify.on('add', function(e) {
                updateRentalBoatsIds();
                updateSelectedRentalBoatsCards();
            });
            
            rentalBoatsInput.tagify.on('remove', function(e) {
                updateRentalBoatsIds();
                updateSelectedRentalBoatsCards();
            });
            
            // Load existing data
            @if(isset($formData['rental_boats']) && is_array($formData['rental_boats']))
            const existingRentalBoats = {!! json_encode(array_map(function($id) use ($rentalBoats) {
                $rb = $rentalBoats->firstWhere('id', $id);
                return $rb ? ['value' => '(' . $rb->id . ') | ' . $rb->title, 'id' => $rb->id, 'title' => $rb->title] : null;
            }, $formData['rental_boats'])) !!};
            if (existingRentalBoats && Array.isArray(existingRentalBoats)) {
                rentalBoatsInput.tagify.addTags(existingRentalBoats.filter(Boolean));
            }
            @endif
        }
        @endif
        
        // Prepare data for guidings
        @if(isset($guidings) && count($guidings) > 0)
        const guidingsData = {!! json_encode($guidings->map(function($item) { 
            return ['value' => '(' . $item->id . ') | ' . $item->title, 'id' => $item->id, 'title' => $item->title]; 
        })->sortBy('title')->values()->toArray()) !!};
        
        const guidingsInput = document.querySelector('#guidings');
        if (guidingsInput) {
            // Store Tagify instance on the input element so it can be accessed later
            guidingsInput.tagify = new Tagify(guidingsInput, {
                whitelist: guidingsData,
                dropdown: {
                    maxItems: Infinity,
                    classname: "tagify__dropdown",
                    enabled: 0,
                    closeOnSelect: false
                },
                templates: {
                    tag: function(tagData) {
                        return `<tag title="${tagData.title || tagData.value}" 
                                    contenteditable='false' 
                                    spellcheck='false' 
                                    tabIndex="-1" 
                                    class="${this.settings.classNames.tag} ${tagData.class ? tagData.class : ""}" 
                                    ${this.getAttributes(tagData)}>
                                    <x title='remove tag' class='tagify__tag__removeBtn'></x>
                                    <div>
                                        <span class='tagify__tag-text'>${tagData.value}</span>
                                    </div>
                                </tag>`;
                    }
                }
            });
            
            guidingsInput.tagify.on('add', function(e) {
                updateGuidingsIds();
                updateSelectedGuidingsCards();
            });
            
            guidingsInput.tagify.on('remove', function(e) {
                updateGuidingsIds();
                updateSelectedGuidingsCards();
            });
            
            // Load existing data
            @if(isset($formData['guidings']) && is_array($formData['guidings']))
            const existingGuidings = {!! json_encode(array_map(function($id) use ($guidings) {
                $g = $guidings->firstWhere('id', $id);
                return $g ? ['value' => '(' . $g->id . ') | ' . $g->title, 'id' => $g->id, 'title' => $g->title] : null;
            }, $formData['guidings'])) !!};
            if (existingGuidings && Array.isArray(existingGuidings)) {
                guidingsInput.tagify.addTags(existingGuidings.filter(Boolean));
            }
            @endif
        }
        @endif
        
        // Initialize cards for existing selections
        updateSelectedAccommodationsCards();
        updateSelectedRentalBoatsCards();
        updateSelectedGuidingsCards();
    }
    
    function updateAccommodationsIds() {
        const input = document.querySelector('#accommodations');
        const hiddenInput = document.querySelector('#accommodations_ids');
        if (input && input.tagify && hiddenInput) {
            const tags = input.tagify.value || [];
            const ids = tags.map(tag => {
                if (typeof tag === 'object' && tag.id) {
                    return tag.id;
                } else if (typeof tag === 'object' && tag.value) {
                    const match = tag.value.match(/^\((\d+)\)/);
                    return match ? match[1] : null;
                } else if (typeof tag === 'string') {
                    const match = tag.match(/^\((\d+)\)/);
                    return match ? match[1] : null;
                }
                return null;
            }).filter(Boolean);
            hiddenInput.value = ids.join(',');
        } else {
            console.warn('updateAccommodationsIds - Missing input or tagify:', { input, hiddenInput, hasTagify: input && input.tagify });
        }
    }
    
    function updateRentalBoatsIds() {
        const input = document.querySelector('#rental_boats');
        const hiddenInput = document.querySelector('#rental_boats_ids');
        if (input && input.tagify && hiddenInput) {
            const tags = input.tagify.value || [];
            const ids = tags.map(tag => {
                if (typeof tag === 'object' && tag.id) {
                    return tag.id;
                } else if (typeof tag === 'object' && tag.value) {
                    const match = tag.value.match(/^\((\d+)\)/);
                    return match ? match[1] : null;
                } else if (typeof tag === 'string') {
                    const match = tag.match(/^\((\d+)\)/);
                    return match ? match[1] : null;
                }
                return null;
            }).filter(Boolean);
            hiddenInput.value = ids.join(',');
        } else {
            console.warn('updateRentalBoatsIds - Missing input or tagify:', { input, hiddenInput, hasTagify: input && input.tagify });
        }
    }
    
    function updateGuidingsIds() {
        const input = document.querySelector('#guidings');
        const hiddenInput = document.querySelector('#guidings_ids');
        if (input && input.tagify && hiddenInput) {
            const tags = input.tagify.value || [];
            const ids = tags.map(tag => {
                if (typeof tag === 'object' && tag.id) {
                    return tag.id;
                } else if (typeof tag === 'object' && tag.value) {
                    const match = tag.value.match(/^\((\d+)\)/);
                    return match ? match[1] : null;
                } else if (typeof tag === 'string') {
                    const match = tag.match(/^\((\d+)\)/);
                    return match ? match[1] : null;
                }
                return null;
            }).filter(Boolean);
            hiddenInput.value = ids.join(',');
        } else {
            console.warn('updateGuidingsIds - Missing input or tagify:', { input, hiddenInput, hasTagify: input && input.tagify });
        }
    }
    
    function updateSelectedAccommodationsCards() {
        const input = document.querySelector('#accommodations');
        const container = $('#selected-accommodations-container');
        const cardsContainer = $('#selected-accommodations-cards');
        
        if (!input || !input.tagify) {
            container.hide();
            return;
        }
        
        const tags = input.tagify.value || [];
        const selectedIds = tags.map(tag => tag.id || tag.value.match(/^\((\d+)\)/)?.[1]).filter(Boolean);
        
        if (selectedIds.length === 0) {
            container.hide();
            return;
        }
        
        // Show loading state
        cardsContainer.html('<div class="col-12 text-center"><div class="spinner-border text-primary" role="status"><span class="visually-hidden">Loading...</span></div></div>');
        container.show();
        
        // For now, just show a placeholder - you'll need to create routes/controllers for accommodations cards
        cardsContainer.html('<div class="col-12"><div class="alert alert-info">Accommodation cards feature coming soon</div></div>');
    }
    
    function updateSelectedRentalBoatsCards() {
        const input = document.querySelector('#rental_boats');
        const container = $('#selected-rental-boats-container');
        const cardsContainer = $('#selected-rental-boats-cards');
        
        if (!input || !input.tagify) {
            container.hide();
            return;
        }
        
        const tags = input.tagify.value || [];
        const selectedIds = tags.map(tag => tag.id || tag.value.match(/^\((\d+)\)/)?.[1]).filter(Boolean);
        
        if (selectedIds.length === 0) {
            container.hide();
            return;
        }
        
        // Show loading state
        cardsContainer.html('<div class="col-12 text-center"><div class="spinner-border text-primary" role="status"><span class="visually-hidden">Loading...</span></div></div>');
        container.show();
        
        // For now, just show a placeholder - you'll need to create routes/controllers for rental boats cards
        cardsContainer.html('<div class="col-12"><div class="alert alert-info">Rental boat cards feature coming soon</div></div>');
    }
    
    function updateSelectedGuidingsCards() {
        const input = document.querySelector('#guidings');
        const container = $('#selected-guidings-container');
        const cardsContainer = $('#selected-guidings-cards');
        
        if (!input || !input.tagify) {
            container.hide();
            return;
        }
        
        const tags = input.tagify.value || [];
        const selectedIds = tags.map(tag => tag.id || tag.value.match(/^\((\d+)\)/)?.[1]).filter(Boolean);
        
        if (selectedIds.length === 0) {
            container.hide();
            return;
        }
        
        // Show loading state
        cardsContainer.html('<div class="col-12 text-center"><div class="spinner-border text-primary" role="status"><span class="visually-hidden">Loading...</span></div></div>');
        container.show();
        
        // Fetch guiding data and display cards (using existing route from camps)
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
        const whatsIncludedInput = document.querySelector('#whats_included');
        if (whatsIncludedInput && typeof Tagify !== 'undefined') {
            // Check if Tagify is already initialized
            if (whatsIncludedInput.tagify) {
                whatsIncludedInput.tagify.destroy();
            }
            
            whatsIncludedInput.tagify = new Tagify(whatsIncludedInput, {
                placeholder: 'Add items...',
                duplicates: false,
                trim: true,
                dropdown: {
                    maxItems: 20,
                    enabled: 0,
                    classname: 'tags-look'
                }
            });
        }
    }

    function setupStepNavigation() {
        const totalSteps = 3;
        let currentStep = 1;
        
        // Step buttons
        $('.step-button').on('click', function() {
            const step = parseInt($(this).data('step'));
            goToStep(step);
        });
        
        // Next buttons
        $('#nextBtn1').on('click', () => goToStep(2));
        $('#nextBtn2').on('click', () => goToStep(3));
        
        // Previous buttons
        $('#prevBtn2').on('click', () => goToStep(1));
        $('#prevBtn3').on('click', () => goToStep(2));
        
        // Draft buttons
        $('#saveDraftBtn1, #saveDraftBtn2, #saveDraftBtn3').on('click', function() {
            $('#is_draft').val('1');
            $('#specialOfferForm').submit();
        });
        
        function goToStep(step) {
            $('.step').removeClass('active');
            $(`#step${step}`).addClass('active');
            $('.step-button').removeClass('active');
            $(`.step-button[data-step="${step}"]`).addClass('active');
            currentStep = step;
        }
    }

    function setupPricing() {
        // Add pricing tier
        // $('#add-pricing-tier').on('click', function() {
        //     const tierHtml = `
        //         <div class="pricing-tier mb-3 p-3 border rounded">
        //             <div class="row">
        //                 <div class="col-md-12">
        //                     <label>Price</label>
        //                     <input type="number" class="form-control pricing-amount" step="0.01" min="0" placeholder="0.00">
        //                 </div>
        //             </div>
        //             <button type="button" class="btn btn-sm btn-danger mt-2 remove-tier">Remove</button>
        //         </div>
        //     `;
        //     $('#pricing-container').append(tierHtml);
        //     $('.remove-tier').show();
        // });
        
        // Remove pricing tier
        $(document).on('click', '.remove-tier', function() {
            $(this).closest('.pricing-tier').remove();
            if ($('.pricing-tier').length <= 1) {
                $('.remove-tier').hide();
            }
        });
        
        // Collect pricing data before form submit
        $('#specialOfferForm').on('submit', function() {
            const pricing = [];
            $('.pricing-tier').each(function() {
                const amount = $(this).find('.pricing-amount').val();
                
                if (amount) {
                    pricing.push({
                        amount: parseFloat(amount) || 0,
                        currency: 'EUR',
                        type: 'fixed'
                    });
                }
            });
            
            $('#pricing_input').val(JSON.stringify(pricing));
        });
    }

    function setupFormSubmission() {
        $('#specialOfferForm').on('submit', function(e) {
            e.preventDefault();
            
            // Update hidden ID fields before form submission
            updateAccommodationsIds();
            updateRentalBoatsIds();
            updateGuidingsIds();
            
            const formData = new FormData(this);
            const submitUrl = $(this).attr('action');
            
            // Collect tagify data properly
            collectTagifyData(formData);
            
            // Collect image list for tracking
            const imageList = [];
            document.querySelectorAll('#croppedImagesContainer .image-preview-wrapper').forEach(wrapper => {
                if (wrapper.dataset.filename) {
                    imageList.push(wrapper.dataset.filename);
                }
            });
            $('#image_list').val(JSON.stringify(imageList));
            
            if (window.imageManagerLoaded && typeof window.imageManagerLoaded.getCroppedImages === 'function') {
                const croppedImages = window.imageManagerLoaded.getCroppedImages();
                
                if (croppedImages.length > 0) {
                    // Remove any existing title_image[] from FormData
                    formData.delete('title_image[]');
                    croppedImages.forEach((imgObj, idx) => {
                        if (imgObj && imgObj.dataUrl) {
                            // Convert dataURL to Blob
                            const blob = dataURLtoBlob(imgObj.dataUrl);
                            const filename = imgObj.filename || `cropped_${idx}.png`;
                            formData.append('title_image[]', blob, filename);
                        }
                    });
                }
            }
            
            // Show loading state
            const submitBtn = $(this).find('button[type="submit"]');
            const originalText = submitBtn.text();
            submitBtn.prop('disabled', true).text('Saving...');
            
            $.ajax({
                url: submitUrl,
                type: 'POST',
                data: formData,
                processData: false,
                contentType: false,
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                success: function(response) {
                    if (response.success) {
                        showSuccessMessage(response.message || 'Saved successfully!');
                        setTimeout(function() {
                            window.location.href = response.redirect_url || $('#target_redirect').val();
                        }, 1500);
                    } else {
                        showErrorMessage(response.message || 'Save failed');
                    }
                },
                error: function(xhr) {
                    let errorMessage = 'Save failed';
                    
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

    function collectTagifyData(formData) {
        // Collect whats_included from Tagify
        const whatsIncludedInput = document.querySelector('#whats_included');
        if (whatsIncludedInput && whatsIncludedInput.tagify) {
            const tagifyData = whatsIncludedInput.tagify.value;
            if (Array.isArray(tagifyData) && tagifyData.length > 0) {
                const values = tagifyData.map(tag => typeof tag === 'string' ? tag : tag.value);
                formData.set('whats_included', JSON.stringify(values));
            }
        }
    }

    function showSuccessMessage(message) {
        const errorContainer = $('#error-container');
        errorContainer.removeClass('alert-danger').addClass('alert-success');
        errorContainer.html(message).fadeIn();
        setTimeout(() => errorContainer.fadeOut(), 5000);
    }

    function showErrorMessage(message) {
        const errorContainer = $('#error-container');
        errorContainer.removeClass('alert-success').addClass('alert-danger');
        errorContainer.html(message).fadeIn();
    }

    function loadExistingData() {
        // Load existing pricing data if editing
        const pricingInput = document.getElementById('pricing_input');
        if (pricingInput && pricingInput.value) {
            try {
                const pricing = JSON.parse(pricingInput.value);
                if (Array.isArray(pricing) && pricing.length > 0) {
                    // Clear existing pricing tiers except the first one
                    $('.pricing-tier').slice(1).remove();
                    
                    // Update first tier or create new ones
                    pricing.forEach((tier, index) => {
                        // Support both old format (price_per_night, price_per_week) and new format (amount)
                        const amount = tier.amount || tier.price_per_night || tier.price_per_week || 0;
                        
                        if (index === 0) {
                            // Update first tier
                            $('.pricing-tier').first().find('.pricing-amount').val(amount);
                        } else {
                            // Create new tier
                            const tierHtml = `
                                <div class="pricing-tier mb-3 p-3 border rounded">
                                    <div class="row">
                                        <div class="col-md-12">
                                            <label>Price</label>
                                            <input type="number" class="form-control pricing-amount" step="0.01" min="0" value="${amount}" placeholder="0.00">
                                        </div>
                                    </div>
                                    <button type="button" class="btn btn-sm btn-danger mt-2 remove-tier">Remove</button>
                                </div>
                            `;
                            $('#pricing-container').append(tierHtml);
                        }
                    });
                    
                    if ($('.pricing-tier').length > 1) {
                        $('.remove-tier').show();
                    }
                }
            } catch (e) {
                console.error('Error loading pricing data:', e);
            }
        }
    }

    // Helper function to convert data URL to blob
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
</script>
@endpush
