<script>
document.addEventListener('DOMContentLoaded', function() {
    // Step navigation
    const steps = document.querySelectorAll('.step');
    const stepButtons = document.querySelectorAll('.step-button');
    const stepLine = document.querySelector('.step-line');
    let currentStep = 1;

    // Initialize form
    initializeForm();

    function initializeForm() {
        // Set up step navigation
        stepButtons.forEach((button, index) => {
            button.addEventListener('click', () => {
                if (index + 1 <= currentStep) {
                    goToStep(index + 1);
                }
            });
        });

        // Set up next/previous buttons
        setupNavigationButtons();
        
        // Set up image upload
        setupImageUpload();
        
        // Set up location search
        setupLocationSearch();
        
        // Set up form validation
        setupFormValidation();
        
        // Set up save draft functionality
        setupSaveDraft();
    }

    function goToStep(stepNumber) {
        // Hide all steps
        steps.forEach(step => step.classList.remove('active'));
        
        // Show current step
        document.getElementById(`step${stepNumber}`).classList.add('active');
        
        // Update step buttons
        stepButtons.forEach((button, index) => {
            button.classList.remove('active', 'completed');
            if (index + 1 < stepNumber) {
                button.classList.add('completed');
            } else if (index + 1 === stepNumber) {
                button.classList.add('active');
            }
        });
        
        // Update step line
        stepLine.className = `step-line step-${stepNumber}`;
        
        currentStep = stepNumber;
    }

    function setupNavigationButtons() {
        // Next buttons
        for (let i = 1; i <= 6; i++) {
            const nextBtn = document.getElementById(`nextBtn${i}`);
            if (nextBtn) {
                nextBtn.addEventListener('click', () => {
                    if (validateStep(i)) {
                        goToStep(i + 1);
                    }
                });
            }
        }

        // Previous buttons
        for (let i = 2; i <= 6; i++) {
            const prevBtn = document.getElementById(`prevBtn${i}`);
            if (prevBtn) {
                prevBtn.addEventListener('click', () => {
                    goToStep(i - 1);
                });
            }
        }
    }

    function validateStep(stepNumber) {
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
        
        if (!isValid) {
            showError('Please fill in all required fields');
        }
        
        return isValid;
    }

    function setupImageUpload() {
        const fileInput = document.getElementById('title_image');
        const imageContainer = document.getElementById('imagePreviewContainer');
        const primaryImageInput = document.getElementById('primaryImageInput');
        let uploadedImages = [];

        fileInput.addEventListener('change', function(e) {
            const files = Array.from(e.target.files);
            
            files.forEach(file => {
                if (file.type.startsWith('image/')) {
                    const reader = new FileReader();
                    reader.onload = function(e) {
                        const imageId = Date.now() + Math.random();
                        const imageData = {
                            id: imageId,
                            file: file,
                            url: e.target.result
                        };
                        
                        uploadedImages.push(imageData);
                        displayImage(imageData);
                    };
                    reader.readAsDataURL(file);
                }
            });
        });

        function displayImage(imageData) {
            const imagePreview = document.createElement('div');
            imagePreview.className = 'image-preview';
            imagePreview.dataset.imageId = imageData.id;
            
            imagePreview.innerHTML = `
                <img src="${imageData.url}" alt="Preview">
                <button type="button" class="remove-btn" onclick="removeImage('${imageData.id}')">
                    <i class="fas fa-times"></i>
                </button>
                <button type="button" class="set-primary-btn" onclick="setPrimaryImage('${imageData.id}')">
                    Set as Primary
                </button>
            `;
            
            imageContainer.appendChild(imagePreview);
        }

        // Make functions global for onclick handlers
        window.removeImage = function(imageId) {
            uploadedImages = uploadedImages.filter(img => img.id !== imageId);
            const preview = document.querySelector(`[data-image-id="${imageId}"]`);
            if (preview) {
                preview.remove();
            }
        };

        window.setPrimaryImage = function(imageId) {
            // Remove primary badge from all images
            document.querySelectorAll('.primary-badge').forEach(badge => badge.remove());
            
            // Add primary badge to selected image
            const preview = document.querySelector(`[data-image-id="${imageId}"]`);
            if (preview) {
                const badge = document.createElement('div');
                badge.className = 'primary-badge';
                badge.textContent = 'Primary';
                preview.appendChild(badge);
                
                // Set primary image input
                const imageData = uploadedImages.find(img => img.id === imageId);
                if (imageData) {
                    primaryImageInput.value = imageData.id;
                }
            }
        };
    }

    function setupLocationSearch() {
        const locationInput = document.getElementById('location');
        const latitudeInput = document.getElementById('latitude');
        const longitudeInput = document.getElementById('longitude');
        const countryInput = document.getElementById('country');
        const cityInput = document.getElementById('city');
        const regionInput = document.getElementById('region');

        // Simple location search implementation
        locationInput.addEventListener('blur', function() {
            const location = this.value;
            if (location) {
                // In a real implementation, you would use a geocoding service
                // For now, we'll just set some default values
                latitudeInput.value = '55.6761';
                longitudeInput.value = '12.5683';
                countryInput.value = 'Denmark';
                cityInput.value = 'Copenhagen';
                regionInput.value = 'Capital Region';
            }
        });
    }

    function setupFormValidation() {
        const form = document.getElementById('accommodationForm');
        
        form.addEventListener('submit', function(e) {
            if (!validateAllSteps()) {
                e.preventDefault();
                showError('Please complete all required fields before submitting');
            }
        });
    }

    function validateAllSteps() {
        for (let i = 1; i <= 6; i++) {
            if (!validateStep(i)) {
                goToStep(i);
                return false;
            }
        }
        return true;
    }

    function setupSaveDraft() {
        const saveDraftButtons = document.querySelectorAll('[id^="saveDraftBtn"]');
        
        saveDraftButtons.forEach(button => {
            button.addEventListener('click', function() {
                const isDraftInput = document.getElementById('is_draft');
                isDraftInput.value = '1';
                
                // Show loading state
                this.classList.add('loading');
                this.disabled = true;
                
                // Submit form
                document.getElementById('accommodationForm').submit();
            });
        });
    }

    function showError(message) {
        const errorContainer = document.getElementById('error-container');
        errorContainer.textContent = message;
        errorContainer.style.display = 'block';
        
        // Hide error after 5 seconds
        setTimeout(() => {
            errorContainer.style.display = 'none';
        }, 5000);
    }

    // Initialize tooltips
    const tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
    tooltipTriggerList.map(function (tooltipTriggerEl) {
        return new bootstrap.Tooltip(tooltipTriggerEl);
    });
});
</script>