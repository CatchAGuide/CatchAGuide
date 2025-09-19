@push('js_push')
<script src="https://cdnjs.cloudflare.com/ajax/libs/cropperjs/1.5.12/cropper.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/browser-image-compression@latest/dist/browser-image-compression.js"></script>
<script src="{{ asset('assets/js/ImageManager.js') }}"></script>

<script>
/**
 * Accommodation Form Manager - OOP approach for better organization
 */
class AccommodationFormManager {
    constructor() {
        this.currentStep = 1;
        this.totalSteps = 6;
        this.pricingTierCounter = 0;
        this.imageManager = null;
        this.elements = this.initializeElements();
        this.init();
    }

    initializeElements() {
        return {
            form: document.getElementById('accommodation-form'),
            steps: document.querySelectorAll('.step'),
            stepButtons: document.querySelectorAll('.step-button'),
            stepLine: document.querySelector('.step-line'),
            errorContainer: document.getElementById('error-container'),
            imageContainer: document.getElementById('croppedImagesContainer'),
            imageInput: document.getElementById('title_image'),
            locationInput: document.getElementById('location'),
            maxOccupancyInput: document.getElementById('max_occupancy'),
            priceTypeRadios: document.querySelectorAll('input[name="price_type"]'),
            perAccommodationDiv: document.getElementById('per_accommodation_pricing'),
            perPersonDiv: document.getElementById('per_person_pricing'),
            dynamicPersonPricingContainer: document.getElementById('dynamic-person-pricing-container')
        };
    }

    init() {
        this.setupEventListeners();
        this.initializeImageManager();
        this.initializeBedTypes();
        this.initializeTooltips();
        this.initializePricing();
        this.initializeLocationAutocomplete();
        this.initializePolicyCheckboxes();
    }

    setupEventListeners() {
    // Step navigation
        this.elements.stepButtons.forEach((button, index) => {
            button.addEventListener('click', () => this.goToStep(index + 1));
        });

        // Navigation buttons
        this.setupNavigationButtons();
    }

    setupNavigationButtons() {
        // Next buttons
        for (let i = 1; i <= this.totalSteps; i++) {
            const nextBtn = document.getElementById(`nextBtn${i}`);
            if (nextBtn) {
                nextBtn.addEventListener('click', () => {
                    if (this.validateStep(i)) {
                        this.goToStep(i + 1);
                    }
                });
            }
        }

        // Previous buttons
        for (let i = 2; i <= this.totalSteps; i++) {
            const prevBtn = document.getElementById(`prevBtn${i}`);
            if (prevBtn) {
                prevBtn.addEventListener('click', () => this.goToStep(i - 1));
            }
        }
    }

    initializeBedTypes() {
        // Handle bed type checkboxes with show/hide functionality (new structure)
        const bedTypeCheckboxes = document.querySelectorAll('input[name="bed_type_checkboxes[]"]');
        
        bedTypeCheckboxes.forEach(checkbox => {
            // Initialize state - hide all quantity inputs by default
            const quantityInput = checkbox.parentElement.querySelector('.extra-input');
            if (quantityInput) {
                quantityInput.style.display = checkbox.checked ? 'block' : 'none';
            }
            
            // Add event listener for checkbox changes
            checkbox.addEventListener('change', (e) => {
                const quantityInput = e.target.parentElement.querySelector('.extra-input');
                if (quantityInput) {
                    quantityInput.style.display = e.target.checked ? 'block' : 'none';
                    
                    // Focus on quantity input when shown
                    if (e.target.checked) {
                        setTimeout(() => quantityInput.focus(), 100);
                    }
                }
            });
        });
    }

    setupBedTypeCheckbox(checkbox) {
        // This method is kept for backward compatibility but no longer used
        console.warn('setupBedTypeCheckbox is deprecated. New bed configuration uses checkbox+input pattern.');
    }

    updateBedTypeVisibility(checkbox, quantityContainer, quantityInput) {
        // This method is kept for backward compatibility but no longer used
        console.warn('updateBedTypeVisibility is deprecated. New bed configuration uses checkbox+input pattern.');
    }

    initializeImageManager() {
        if (typeof ImageManager !== 'undefined' && !this.imageManager) {
            this.imageManager = new ImageManager('#croppedImagesContainer', '#title_image');
            
            if (document.getElementById('is_update').value === '1') {
                const existingImagesInput = document.getElementById('existing_images');
                const thumbnailPath = document.getElementById('thumbnail_path').value;
                
                if (existingImagesInput && existingImagesInput.value) {
                    this.imageManager.loadExistingImages(existingImagesInput.value, thumbnailPath);
                }
            }
        }

        // File input event listener
        if (this.elements.imageInput) {
            this.elements.imageInput.addEventListener('change', (event) => {
                if (this.imageManager) {
                    try {
                        this.imageManager.handleFileSelect(event.target.files);
                    } catch (error) {
                        console.error('Error in handleFileSelect:', error);
                    }
                }
            });
        }
    }

    initializePricing() {
        // Price type change handlers
        this.elements.priceTypeRadios.forEach(radio => {
            radio.addEventListener('change', () => {
                this.handlePriceTypeChange(radio.value);
            });
        });
        
        // Max occupancy change handler for auto-generating pricing rows
        if (this.elements.maxOccupancyInput) {
            this.elements.maxOccupancyInput.addEventListener('input', () => {
                this.generatePricingRowsBasedOnOccupancy();
            });
        }
        
        // Initialize pricing display based on current selection
        const selectedPriceType = document.querySelector('input[name="price_type"]:checked');
        if (selectedPriceType) {
            this.handlePriceTypeChange(selectedPriceType.value);
        }
        
        // Initialize pricing rows based on current max occupancy
        this.generatePricingRowsBasedOnOccupancy();
    }

    initializeTooltips() {
        const tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
        tooltipTriggerList.map(function (tooltipTriggerEl) {
            return new bootstrap.Tooltip(tooltipTriggerEl);
        });
    }

    initializeLocationAutocomplete() {
        const locationInput = this.elements.locationInput;
        if (locationInput && typeof google !== 'undefined' && google.maps && google.maps.places) {
            try {
                const autocomplete = new google.maps.places.Autocomplete(locationInput, {
                    types: ['establishment', 'geocode'],
                    fields: ['place_id', 'formatted_address', 'geometry', 'address_components']
                });

                autocomplete.addListener('place_changed', () => {
                    const place = autocomplete.getPlace();
                    if (place.geometry) {
                        document.getElementById('latitude').value = place.geometry.location.lat();
                        document.getElementById('longitude').value = place.geometry.location.lng();
                        
                        // Extract address components
                        const addressComponents = place.address_components;
                        let country = '', city = '', region = '';
                        
                        if (addressComponents) {
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
                        }
                        
                        document.getElementById('country').value = country;
                        document.getElementById('city').value = city;
                        document.getElementById('region').value = region;
                    }
                });
            } catch (error) {
                console.warn('Google Places API not available:', error);
            }
        } else {
            console.warn('Google Maps API not loaded or location input not found');
        }
    }

    initializePolicyCheckboxes() {
        // Handle policy checkboxes with show/hide functionality
        const policyCheckboxes = document.querySelectorAll('input[name="policy_checkboxes[]"]');
        
        policyCheckboxes.forEach(checkbox => {
            // Initialize state - hide all textareas by default
            const textarea = checkbox.parentElement.querySelector('.extra-input');
            const container = checkbox.parentElement;
            
            if (textarea) {
                textarea.style.display = checkbox.checked ? 'block' : 'none';
            }
            
            // Add/remove active class to container based on checkbox state
            if (container) {
                if (checkbox.checked) {
                    container.classList.add('active');
                } else {
                    container.classList.remove('active');
                }
            }
            
            // Add event listener for checkbox changes
            checkbox.addEventListener('change', (e) => {
                const textarea = e.target.parentElement.querySelector('.extra-input');
                const container = e.target.parentElement;
                
                if (textarea) {
                    textarea.style.display = e.target.checked ? 'block' : 'none';
                    
                    // Focus on textarea when shown
                    if (e.target.checked) {
                        setTimeout(() => textarea.focus(), 100);
                    }
                }
                
                // Toggle active class on container
                if (container) {
                    if (e.target.checked) {
                        container.classList.add('active');
                    } else {
                        container.classList.remove('active');
                    }
                }
            });
        });
    }

    canNavigateToStep(targetStep) {
        // Allow navigation to step 1 always
        if (targetStep === 1) return true;
        
        // For other steps, check if previous steps are valid
        for (let i = 1; i < targetStep; i++) {
            if (!this.validateStep(i)) {
                this.showError(`Please complete step ${i} before proceeding.`);
                return false;
            }
        }
        
        return true;
    }

    goToStep(stepNumber) {
        // Validate step before allowing navigation
        if (!this.canNavigateToStep(stepNumber)) {
            return false;
        }
        
        // Hide all steps
        this.elements.steps.forEach(step => step.classList.remove('active'));
        
        // Show current step
        const targetStep = document.getElementById(`step${stepNumber}`);
        if (targetStep) {
            targetStep.classList.add('active');
        }
        
        // Update step buttons
        this.elements.stepButtons.forEach((button, index) => {
            button.classList.remove('active', 'completed');
            if (index + 1 < stepNumber) {
                button.classList.add('completed');
            } else if (index + 1 === stepNumber) {
                button.classList.add('active');
            }
        });
        
        // Update step line
        if (this.elements.stepLine) {
            this.elements.stepLine.className = `step-line step-${stepNumber}`;
        }
        
        this.currentStep = stepNumber;
        return true;
    }

    validateStep(stepNumber) {
        let isValid = true;
        const step = document.getElementById(`step${stepNumber}`);
        const requiredFields = step.querySelectorAll('[required]');
        
        requiredFields.forEach(field => {
            if (!field.value.trim()) {
                field.classList.add('is-invalid');
                isValid = false;
            } else {
                field.classList.remove('is-invalid');
            }
        });
        
        // Special validation for pricing step
        if (stepNumber === 6) {
            isValid = this.validatePricingStep() && isValid;
        }
        
        return isValid;
    }

    validatePricingStep() {
        const selectedPriceType = document.querySelector('input[name="price_type"]:checked');
        if (!selectedPriceType) {
            this.showError('Please select a pricing type.');
            return false;
        }

        if (selectedPriceType.value === 'per_accommodation') {
            const pricePerNight = document.querySelector('input[name="price_per_night"]');
            if (!pricePerNight || !pricePerNight.value) {
                this.showError('Please enter a price per night.');
                return false;
            }
        } else if (selectedPriceType.value === 'per_person') {
            const pricingTiers = document.querySelectorAll('.pricing-tier');
            if (pricingTiers.length === 0) {
                this.showError('Please add at least one pricing tier.');
                return false;
            }
        }

        return true;
    }

    handlePriceTypeChange(priceType) {
        // Hide all pricing sections
        if (this.elements.perAccommodationDiv) this.elements.perAccommodationDiv.style.display = 'none';
        if (this.elements.perPersonDiv) this.elements.perPersonDiv.style.display = 'none';
        
        // Show relevant pricing section
        if (priceType === 'per_accommodation') {
            if (this.elements.perAccommodationDiv) this.elements.perAccommodationDiv.style.display = 'block';
        } else if (priceType === 'per_person') {
            if (this.elements.perPersonDiv) this.elements.perPersonDiv.style.display = 'block';
            // Generate pricing rows based on max occupancy
            this.generatePricingRowsBasedOnOccupancy();
        }
    }

    generatePricingRowsBasedOnOccupancy() {
        const container = this.elements.dynamicPersonPricingContainer;
        const maxOccupancyInput = this.elements.maxOccupancyInput;
        
        if (!container || !maxOccupancyInput) return;
        
        const maxOccupancy = parseInt(maxOccupancyInput.value) || 0;
        
        // Clear existing rows
        container.innerHTML = '';
        
        // Generate rows for each guest count from 1 to max occupancy
        for (let guestCount = 1; guestCount <= maxOccupancy; guestCount++) {
            const fieldGroup = document.createElement('div');
            fieldGroup.className = 'form-group mb-3 pricing-tier';
            fieldGroup.setAttribute('data-tier', guestCount);
            fieldGroup.innerHTML = `
                <div class="row align-items-center">
                    <div class="col-md-2">
                        <label class="form-label">Number of guests</label>
                        <div class="form-control-static d-flex align-items-center" style="height: 38px; padding: 6px 12px; background-color: #f8f9fa; border: 1px solid #ced4da; border-radius: 4px;">
                            ${guestCount}
                        </div>
                        <input type="hidden" name="guest_count_${guestCount}" value="${guestCount}">
                    </div>
                    <div class="col-md-5">
                        <label class="form-label">Price per Night</label>
                        <div class="input-group">
                            <span class="input-group-text">€</span>
                            <input type="number" class="form-control" name="price_per_person_night_${guestCount}" placeholder="0.00" min="0" step="0.01" value="">
                        </div>
                    </div>
                    <div class="col-md-5">
                        <label class="form-label">Price per Week</label>
                        <div class="input-group">
                            <span class="input-group-text">€</span>
                            <input type="number" class="form-control" name="price_per_person_week_${guestCount}" placeholder="0.00" min="0" step="0.01" value="">
                        </div>
                    </div>
                </div>
            `;
            container.appendChild(fieldGroup);
        }
        
        // Update counter
        this.pricingTierCounter = maxOccupancy;
    }

    addPersonPricingTier() {
        // This method is kept for backward compatibility but no longer used
        // Pricing rows are now auto-generated based on max occupancy
        console.warn('addPersonPricingTier is deprecated. Use generatePricingRowsBasedOnOccupancy instead.');
    }

    removePersonPricingTier(tierId) {
        // This method is kept for backward compatibility but no longer used
        // Pricing rows are now auto-generated based on max occupancy
        console.warn('removePersonPricingTier is deprecated. Pricing rows are auto-generated.');
    }

    showError(message) {
        if (this.elements.errorContainer) {
            this.elements.errorContainer.textContent = message;
            this.elements.errorContainer.style.display = 'block';
        
        // Hide error after 5 seconds
        setTimeout(() => {
                this.elements.errorContainer.style.display = 'none';
        }, 5000);
        }
    }
}

// Initialize the form manager when DOM is loaded
document.addEventListener('DOMContentLoaded', function() {
    window.accommodationFormManager = new AccommodationFormManager();
});
</script>
@endpush