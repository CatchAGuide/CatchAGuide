<script>
document.addEventListener('DOMContentLoaded', function() {
    const form = document.querySelector('.accommodation-form');
    if (!form) return;

    // Form validation
    const validateForm = () => {
        let isValid = true;
        const requiredFields = form.querySelectorAll('[required]');
        
        requiredFields.forEach(field => {
            const errorElement = field.parentNode.querySelector('.error-message');
            if (errorElement) {
                errorElement.remove();
            }
            
            if (!field.value.trim()) {
                isValid = false;
                showFieldError(field, 'This field is required.');
            }
        });
        
        return isValid;
    };

    // Show field error
    const showFieldError = (field, message) => {
        const errorElement = document.createElement('span');
        errorElement.className = 'error-message';
        errorElement.textContent = message;
        field.parentNode.appendChild(errorElement);
        field.classList.add('is-invalid');
    };

    // Clear field error
    const clearFieldError = (field) => {
        const errorElement = field.parentNode.querySelector('.error-message');
        if (errorElement) {
            errorElement.remove();
        }
        field.classList.remove('is-invalid');
    };

    // Real-time validation
    const requiredFields = form.querySelectorAll('[required]');
    requiredFields.forEach(field => {
        field.addEventListener('blur', () => {
            if (field.value.trim()) {
                clearFieldError(field);
            } else {
                showFieldError(field, 'This field is required.');
            }
        });
    });

    // Auto-generate slug from title
    const titleField = document.getElementById('title');
    const slugField = document.getElementById('slug');
    
    if (titleField && !slugField) {
        titleField.addEventListener('input', function() {
            const slug = this.value
                .toLowerCase()
                .replace(/[^a-z0-9\s-]/g, '')
                .replace(/\s+/g, '-')
                .replace(/-+/g, '-')
                .trim('-');
            
            // If there's a hidden slug field for auto-generation
            const hiddenSlugField = document.querySelector('input[name="auto_slug"]');
            if (hiddenSlugField) {
                hiddenSlugField.value = slug;
            }
        });
    }

    // File upload preview
    const thumbnailInput = document.getElementById('thumbnail_path');
    if (thumbnailInput) {
        thumbnailInput.addEventListener('change', function(e) {
            const file = e.target.files[0];
            if (file) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    // Create or update preview
                    let preview = document.querySelector('.thumbnail-preview');
                    if (!preview) {
                        preview = document.createElement('div');
                        preview.className = 'thumbnail-preview';
                        thumbnailInput.parentNode.appendChild(preview);
                    }
                    preview.innerHTML = `
                        <img src="${e.target.result}" alt="Thumbnail preview" style="max-width: 200px; max-height: 150px; border-radius: 4px; margin-top: 10px;">
                    `;
                };
                reader.readAsDataURL(file);
            }
        });
    }

    // Gallery images preview
    const galleryInput = document.getElementById('gallery_images');
    if (galleryInput) {
        galleryInput.addEventListener('change', function(e) {
            const files = Array.from(e.target.files);
            if (files.length > 0) {
                // Create or update preview
                let preview = document.querySelector('.gallery-preview');
                if (!preview) {
                    preview = document.createElement('div');
                    preview.className = 'gallery-preview';
                    galleryInput.parentNode.appendChild(preview);
                }
                
                preview.innerHTML = '<h4>Selected Images:</h4><div class="gallery-grid"></div>';
                const galleryGrid = preview.querySelector('.gallery-grid');
                
                files.forEach(file => {
                    const reader = new FileReader();
                    reader.onload = function(e) {
                        const imgContainer = document.createElement('div');
                        imgContainer.className = 'gallery-item';
                        imgContainer.innerHTML = `
                            <img src="${e.target.result}" alt="Gallery preview" style="width: 100px; height: 100px; object-fit: cover; border-radius: 4px;">
                            <div class="file-name">${file.name}</div>
                        `;
                        galleryGrid.appendChild(imgContainer);
                    };
                    reader.readAsDataURL(file);
                });
            }
        });
    }

    // Form submission
    form.addEventListener('submit', function(e) {
        e.preventDefault();
        
        if (!validateForm()) {
            // Scroll to first error
            const firstError = form.querySelector('.is-invalid');
            if (firstError) {
                firstError.scrollIntoView({ behavior: 'smooth', block: 'center' });
                firstError.focus();
            }
            return;
        }

        // Show loading state
        form.classList.add('form-loading');
        const submitBtn = form.querySelector('button[type="submit"]');
        const originalText = submitBtn.textContent;
        submitBtn.textContent = 'Saving...';
        submitBtn.disabled = true;

        // Submit form
        const formData = new FormData(form);
        
        fetch(form.action, {
            method: form.method,
            body: formData,
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            }
        })
        .then(response => {
            if (response.ok) {
                return response.json();
            }
            throw new Error('Network response was not ok');
        })
        .then(data => {
            if (data.success) {
                // Show success message
                showAlert('Accommodation saved successfully!', 'success');
                
                // Redirect if needed
                if (data.redirect) {
                    setTimeout(() => {
                        window.location.href = data.redirect;
                    }, 1500);
                }
            } else {
                throw new Error(data.message || 'An error occurred');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            showAlert('An error occurred while saving. Please try again.', 'danger');
        })
        .finally(() => {
            // Reset loading state
            form.classList.remove('form-loading');
            submitBtn.textContent = originalText;
            submitBtn.disabled = false;
        });
    });

    // Show alert message
    const showAlert = (message, type) => {
        // Remove existing alerts
        const existingAlerts = form.querySelectorAll('.alert');
        existingAlerts.forEach(alert => alert.remove());

        // Create new alert
        const alert = document.createElement('div');
        alert.className = `alert alert-${type}`;
        alert.textContent = message;
        
        // Insert at top of form
        form.insertBefore(alert, form.firstChild);
        
        // Auto-remove after 5 seconds
        setTimeout(() => {
            alert.remove();
        }, 5000);
    };

    // Section navigation (if needed)
    const createSectionNavigation = () => {
        const sections = form.querySelectorAll('.form-section');
        if (sections.length < 3) return; // Only create nav for forms with 3+ sections

        const nav = document.createElement('div');
        nav.className = 'section-nav';
        nav.innerHTML = `
            <div class="nav-title">Quick Navigation</div>
            <div class="nav-links"></div>
        `;

        const navLinks = nav.querySelector('.nav-links');
        
        sections.forEach((section, index) => {
            const sectionTitle = section.querySelector('.section-title');
            if (sectionTitle) {
                const link = document.createElement('a');
                link.href = `#section-${index}`;
                link.className = 'nav-link';
                link.textContent = sectionTitle.textContent;
                link.addEventListener('click', (e) => {
                    e.preventDefault();
                    section.scrollIntoView({ behavior: 'smooth' });
                });
                navLinks.appendChild(link);
            }
        });

        form.insertBefore(nav, form.querySelector('.form-sections'));
    };

    // Initialize section navigation
    createSectionNavigation();

    // Smooth scrolling for internal links
    document.querySelectorAll('a[href^="#"]').forEach(anchor => {
        anchor.addEventListener('click', function (e) {
            e.preventDefault();
            const target = document.querySelector(this.getAttribute('href'));
            if (target) {
                target.scrollIntoView({
                    behavior: 'smooth',
                    block: 'start'
                });
            }
        });
    });

    // Auto-save functionality (optional)
    let autoSaveTimeout;
    const autoSave = () => {
        clearTimeout(autoSaveTimeout);
        autoSaveTimeout = setTimeout(() => {
            // Only auto-save if form has been modified
            const formData = new FormData(form);
            const hasChanges = Array.from(formData.entries()).some(([key, value]) => {
                return value && value.toString().trim() !== '';
            });

            if (hasChanges) {
                // Implement auto-save logic here
                console.log('Auto-saving form...');
            }
        }, 30000); // Auto-save every 30 seconds
    };

    // Listen for form changes
    form.addEventListener('input', autoSave);
    form.addEventListener('change', autoSave);

    // Cleanup on page unload
    window.addEventListener('beforeunload', () => {
        clearTimeout(autoSaveTimeout);
    });
});

// Utility functions for accommodation management
window.AccommodationForm = {
    // Toggle section visibility
    toggleSection: (sectionId) => {
        const section = document.querySelector(`[data-section="${sectionId}"]`);
        if (section) {
            section.style.display = section.style.display === 'none' ? 'block' : 'none';
        }
    },

    // Add new bed type
    addBedType: () => {
        const bedTypesContainer = document.querySelector('.bed-types-container');
        if (bedTypesContainer) {
            const newBedType = document.createElement('div');
            newBedType.className = 'bed-type-item';
            newBedType.innerHTML = `
                <select name="bed_types[]" class="form-control">
                    <option value="">Select bed type</option>
                    <option value="single">Single</option>
                    <option value="double">Double</option>
                    <option value="queen">Queen</option>
                    <option value="king">King</option>
                    <option value="sofa_bed">Sofa Bed</option>
                    <option value="bunk_bed">Bunk Bed</option>
                </select>
                <button type="button" class="btn btn-sm btn-danger remove-bed-type">Remove</button>
            `;
            bedTypesContainer.appendChild(newBedType);
        }
    },

    // Remove bed type
    removeBedType: (element) => {
        element.closest('.bed-type-item').remove();
    },

    // Calculate total price
    calculateTotalPrice: () => {
        const pricePerNight = parseFloat(document.getElementById('price_per_night')?.value) || 0;
        const pricePerWeek = parseFloat(document.getElementById('price_per_week')?.value) || 0;
        
        if (pricePerNight > 0 && pricePerWeek === 0) {
            // Calculate weekly price from nightly
            document.getElementById('price_per_week').value = (pricePerNight * 7).toFixed(2);
        } else if (pricePerWeek > 0 && pricePerNight === 0) {
            // Calculate nightly price from weekly
            document.getElementById('price_per_night').value = (pricePerWeek / 7).toFixed(2);
        }
    }
};

// Initialize price calculation
document.addEventListener('DOMContentLoaded', function() {
    const pricePerNight = document.getElementById('price_per_night');
    const pricePerWeek = document.getElementById('price_per_week');
    
    if (pricePerNight) {
        pricePerNight.addEventListener('input', window.AccommodationForm.calculateTotalPrice);
    }
    
    if (pricePerWeek) {
        pricePerWeek.addEventListener('input', window.AccommodationForm.calculateTotalPrice);
    }
});
</script>

<style>
.thumbnail-preview,
.gallery-preview {
    margin-top: 10px;
}

.gallery-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(120px, 1fr));
    gap: 10px;
    margin-top: 10px;
}

.gallery-item {
    text-align: center;
}

.file-name {
    font-size: 0.8rem;
    color: #6c757d;
    margin-top: 5px;
    word-break: break-all;
}

.bed-types-container {
    display: flex;
    flex-direction: column;
    gap: 10px;
}

.bed-type-item {
    display: flex;
    gap: 10px;
    align-items: center;
}

.bed-type-item .form-control {
    flex: 1;
}

.remove-bed-type {
    padding: 6px 12px;
    background: #dc3545;
    color: white;
    border: none;
    border-radius: 4px;
    cursor: pointer;
    font-size: 0.8rem;
}

.remove-bed-type:hover {
    background: #c82333;
}

.form-loading {
    opacity: 0.6;
    pointer-events: none;
}

.form-loading::after {
    content: '';
    position: absolute;
    top: 50%;
    left: 50%;
    width: 40px;
    height: 40px;
    margin: -20px 0 0 -20px;
    border: 4px solid #f3f3f3;
    border-top: 4px solid #3498db;
    border-radius: 50%;
    animation: spin 1s linear infinite;
}

@keyframes spin {
    0% { transform: rotate(0deg); }
    100% { transform: rotate(360deg); }
}
</style>
