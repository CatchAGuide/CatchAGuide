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

    function initAutocomplete() {
        autocomplete = new google.maps.places.Autocomplete(
            document.getElementById('location'),
            {
                types: ['(regions)']
            }
        );

        autocomplete.addListener('place_changed', function () {
            const place = autocomplete.getPlace();

            let city = '';
            let country = '';
            let postal_code = '';

            place.address_components.forEach(component => {
                const types = component.types;

                if (types.includes('locality')) {
                    city = component.long_name; // This is the city name
                } else if (types.includes('country')) {
                    country = component.long_name; // This is the country name
                } else if (types.includes('postal_code')) {
                    postal_code = component.long_name; // This is the postal code
                }
            });
        });
    }


    // Step navigation
    function showStep(stepNumber) {
        $('.step').removeClass('active');
        $(`#step${stepNumber}`).addClass('active');
        $('.step-button').removeClass('active');
        $(`.step-button[data-step="${stepNumber}"]`).addClass('active');
        currentStep = stepNumber;
        updateButtons();
    }

    function updateButtons() {
        $('#prevBtn').toggle(currentStep > 1);
        $('#nextBtn').toggle(currentStep < totalSteps);
        $('#submitBtn').toggle(currentStep === totalSteps);
    }

    $('.step-button').click(function() {
        showStep($(this).data('step'));
    });

    // Next and Previous buttons
    document.addEventListener('DOMContentLoaded', function() {
        document.getElementById('nextBtn').addEventListener('click', function() {
            if (currentStep < totalSteps) {
                showStep(currentStep + 1);
            }
        });

        document.getElementById('prevBtn').addEventListener('click', function() {
            if (currentStep > 1) {
                showStep(currentStep - 1);
            }
        });
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

        // Months (for seasonal trip)
        var monthsInput = document.querySelector('input[name="months"]');
        new Tagify(monthsInput, {
            whitelist: ['January', 'February', 'March', 'April', 'May', 'June', 'July', 'August', 'September', 'October', 'November', 'December'],
            maxTags: 12,
            dropdown: {
                maxItems: 12,
                classname: "tagify__dropdown",
                enabled: 0,
                closeOnSelect: false
            },
            enforceWhitelist: true
        });

        // Show/hide monthly selection based on seasonal trip selection
        $('input[name="seasonal_trip"]').change(function() {
            $('#monthly_selection').toggle($(this).val() === 'season_monthly');
        });
    });

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
        }
    }

    // Dynamic price fields
    $('input[name="price"]').change(function() {
        var priceType = $(this).val();
        var container = $('#dynamic-price-fields-container');
        container.empty();

        if (priceType === 'per_person') {
            var guestCount = parseInt($('#no_guest').val()) || 1;
            for (var i = 1; i <= guestCount; i++) {
                container.append(`<input type="number" class="form-control mt-2" name="price_per_person_${i}" placeholder="Price for ${i} person(s)">`);
            }
        } else if (priceType === 'per_boat') {
            container.append('<input type="number" class="form-control mt-2" name="price_per_boat" placeholder="Price per boat">');
        }
    });

    $('#no_guest').change(function() {
        if ($('input[name="price"]:checked').val() === 'per_person') {
            $('input[name="price"]:checked').change();
        }
    });

    // Seasonal trip selection
    $('input[name="seasonal_trip"]').change(function() {
        if ($(this).val() === 'season_monthly') {
            $('#monthly_selection').show();
            new Tagify(document.getElementById('months'), {
                whitelist: ["January", "February", "March", "April", "May", "June", "July", "August", "September", "October", "November", "December"],
                maxTags: 12
            });
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
                <input type="text" class="form-control mr-2" name="extra_name_${extraCount}" placeholder="Extra name">
                <input type="number" class="form-control mr-2" name="extra_price_${extraCount}" placeholder="Price">
                <button type="button" class="btn btn-danger btn-sm remove-extra"><i class="fas fa-trash"></i></button>
            </div>
        `;
        $('#extras-container').append(newRow);
    });

    $(document).on('click', '.remove-extra', function() {
        $(this).closest('.extra-row').remove();
    });

    // Image preview functionality
    function createButton(innerHTML, onClick) {
        const button = document.createElement('button');
        button.type = 'button';
        button.className = 'image-control-btn';
        button.innerHTML = innerHTML;
        button.onclick = onClick;
        return button;
    }

    function previewImages(input) {
        const container = document.getElementById('imagePreviewContainer');
        
        if (!container) {
            console.error('Image preview container not found');
            return;
        }

        if (input.files) {
            Array.from(input.files).forEach((file) => {
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

                    // Create control buttons
                    const zoomInBtn = createButton('<i class="fas fa-search-plus"></i>', () => croppers[currentIndex].zoom(0.1));
                    const zoomOutBtn = createButton('<i class="fas fa-search-minus"></i>', () => croppers[currentIndex].zoom(-0.1));
                    const rotateBtn = createButton('<i class="fas fa-redo"></i>', () => croppers[currentIndex].rotate(90));
                    const deleteBtn = createButton('<i class="fas fa-trash"></i>', () => deleteImage(wrapper, currentIndex));
                    const setPrimaryBtn = createButton('<i class="fas fa-star"></i>', () => setPrimaryImage(wrapper, currentIndex));

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
                        viewMode: 3, // Changed to viewMode 3
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

                            // Calculate the scaling factor
                            const scale = Math.max(
                                containerData.width / imageData.naturalWidth,
                                containerData.height / imageData.naturalHeight
                            );

                            // Scale the image to fit the container
                            cropper.zoomTo(scale);

                            // Center the image
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

                    imageIndex++;
                }
                reader.readAsDataURL(file);
            });
        }
    }

    function deleteImage(wrapper, index) {
        wrapper.remove();
        delete croppers[index];
    }

    function setPrimaryImage(wrapper, index) {
        $('.image-preview-wrapper').removeClass('primary');
        wrapper.classList.add('primary');
        document.getElementById('primaryImageInput').value = index;
    }

    // Add this event listener to prevent form submission on enter key
    document.querySelector('form').addEventListener('keypress', function(e) {
        if (e.key === 'Enter') {
            e.preventDefault();
        }
    });

    // Modify the form submission logic
    document.querySelector('form').addEventListener('submit', function(e) {
        if (currentStep !== totalSteps) {
            e.preventDefault();
        }
    });

    // Initialize everything when the DOM is ready
    document.addEventListener('DOMContentLoaded', function() {
        showStep(1);
        
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
    });
</script>
@endpush