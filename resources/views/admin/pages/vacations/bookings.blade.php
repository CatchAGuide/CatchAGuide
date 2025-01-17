@extends('admin.layouts.app')

@section('title', 'Alle Vacations')

@section('custom_style')
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@yaireo/tagify/dist/tagify.css">
<style>
    /* Ensure Google Maps autocomplete appears above the modal */
    .pac-container {
        z-index: 1055 !important; /* Bootstrap modal has z-index: 1050 */
        position: fixed !important;
    }

    .item-card {
        border: 1px solid rgba(0,0,0,.125);
        box-shadow: 0 2px 4px rgba(0,0,0,.05);
    }

    .section-wrapper {
        background-color: #f8f9fa;
        padding: 1rem;
        border-radius: 0.25rem;
    }

    .items-container {
        max-height: 400px;
        overflow-y: auto;
    }

    .price-input-group {
        background-color: #f8f9fa;
        padding: 1rem;
        border-radius: 0.25rem;
    }

    .price-input-group:hover {
        background-color: #e9ecef;
    }

    .total-price-display {
        background-color: #e9ecef;
        padding: 1rem;
        border-radius: 0.25rem;
        margin-top: 1rem;
    }

    .card {
        transition: all 0.3s ease;
    }

    .card:hover {
        box-shadow: 0 4px 8px rgba(0,0,0,.1);
    }

    @media (max-width: 768px) {
        .price-input-group {
            padding: 0.5rem;
        }
    }
</style>
@endsection

@section('content')
    <div class="side-app">
        <div class="main-container container-fluid">
            <div class="page-header">
                <h1 class="page-title">@yield('title')</h1>
                <div>
                    <ol class="breadcrumb">
                        <li class="breadcrumb-item"><a href="#">Verwaltung</a></li>
                        <li class="breadcrumb-item active" aria-current="page">@yield('title')</li>
                    </ol>
                </div>

            </div>
            <div class="row ">
                <div class="col-lg-12">
                    <div class="card">
                        <div class="card-header">
                            <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addVacationModal">
                                <i class="fas fa-plus"></i>Bookings
                            </button>
                        </div>
                        <div class="card-body table-responsive">
                            <table id="vacationtable" class="table">
                                <thead>
                                    <tr>
                                        <th class="wd-15p border-bottom-0">ID</th>
                                        <th class="wd-15p border-bottom-0">Guest Name</th>
                                        <th class="wd-10p border-bottom-0">Contact Information</th>
                                        <th class="wd-10p border-bottom-0">Booking Details</th>
                                        <th class="wd-10p border-bottom-0">Total Booking Price</th>
                                        <th class="wd-10p border-bottom-0">Status</th>
                                        <th class="wd-25p border-bottom-0">Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($bookings as $booking)
                                    <tr>
                                        <td>{{$booking->id}}</td>
                                        <td>
                                            {{$booking->title}} {{$booking->name}} {{$booking->surname}}
                                        </td>
                                        <td>
                                            <div class="d-flex flex-column">
                                                @if($booking->package)
                                                    <div class="text-info">Package: {{$booking->package->title ?? $booking->package->id}}</div>
                                                @endif
                                                @if($booking->accommodation)
                                                    <div class="text-info">Accommodation: {{$booking->accommodation->title ?? $booking->accommodation->id}}</div>
                                                @endif
                                                @if($booking->boat)
                                                    <div class="text-info">Boat: {{$booking->boat->title ?? $booking->boat->id}}</div>
                                                @endif
                                                @if($booking->guiding)
                                                    <div class="text-info">Guiding: {{$booking->guiding->title ?? $booking->guiding->id}}</div>
                                                @endif
                                            </div>
                                        </td>
                                        <td>
                                            <div class="d-flex flex-column">
                                                <div class="fa fa-phone">{{$booking->phone}}</div>
                                                <div class="fa fa-envelope">{{$booking->email}}</div>
                                            </div>
                                        </td>
                                        <td>
                                            {{$booking->total_price}}
                                        </td>
                                        <td>
                                            {{$booking->status}}
                                        </td>
                                        <td class="text-center">
                                            <div class="btn-group">
                                                @if($booking->status == 1)
                                                    <a href="#" title="Diactivate" class="btn btn-sm btn-danger"><i class="fa fa-times"></i></a>
                                                @else
                                                    <a href="#" title="Activate" class="btn btn-sm btn-success"><i class="fa fa-check"></i></a>
                                                @endif
                                                <a href="{{ route('admin.vacations.bookings.show', $booking->id) }}" class="btn btn-sm btn-primary"><i class="fa fa-search"></i></a>
                                            </div>
                                        </td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>

            <div class="row bg-white p-2">
            </div>
        </div>

    </div>

    @include('admin.pages.vacations.modals.form')
@endsection

@section('js_after')
<script src="https://unpkg.com/@yaireo/tagify"></script>
<script>
    function initAutocomplete() {
        const locationInput = document.querySelector('#location');
        
        if (locationInput) {
            
            const autocomplete = new google.maps.places.Autocomplete(locationInput, {
                types: ['(cities)'],
                fields: ['address_components', 'geometry', 'formatted_address']
            });

            autocomplete.addListener('place_changed', function() {
                const place = autocomplete.getPlace();
                const form = locationInput.closest('form');
                
                if (!place.geometry) {
                    window.alert("No details available for input: '" + place.name + "'");
                    return;
                }

                // Set latitude and longitude
                form.querySelector('input[name="latitude"]').value = place.geometry.location.lat();
                form.querySelector('input[name="longitude"]').value = place.geometry.location.lng();

                // Process address components
                let city = '', country = '', region = '';
                
                place.address_components.forEach(component => {
                    const componentType = component.types[0];

                    switch (componentType) {
                        case 'locality':
                            city = component.long_name;
                            break;
                        case 'country':
                            country = component.long_name;
                            break;
                        case 'administrative_area_level_1':
                            region = component.long_name;
                            break;
                    }
                });

                // Update hidden fields
                form.querySelector('input[name="city"]').value = city;
                form.querySelector('input[name="country"]').value = country;
                form.querySelector('input[name="region"]').value = region;
                
                // Set the full formatted address in the location input
                locationInput.value = place.formatted_address;
            });
        }
    }

    // Load Google Maps API using the recommended pattern
    function loadGoogleMapsAPI() {
        const script = document.createElement('script');
        script.src = `https://maps.googleapis.com/maps/api/js?key={{ env('GOOGLE_MAP_API_KEY') }}&libraries=places&callback=initAutocomplete`;
        script.async = true;
        script.defer = true;
        document.head.appendChild(script);
    }

    // Load the API when the document is ready
    document.addEventListener('DOMContentLoaded', loadGoogleMapsAPI);

    // Add modal event listener to reinitialize autocomplete when modal opens
    document.querySelector('#addVacationModal').addEventListener('shown.bs.modal', function () {
        CKEDITOR.replace('surroundings_description');
        initializeDynamicCKEditors();
        
        // Initialize add item buttons
        const addButtons = document.querySelectorAll('.add-item');
        addButtons.forEach(button => {
            button.removeEventListener('click', addItemHandler);
            button.addEventListener('click', function(e) {
                addItemHandler.call(this, e);
                // Initialize CKEditor for newly added textarea
                setTimeout(() => {
                    initializeDynamicCKEditors();
                }, 100);
            });
        });

        if (window.google && window.google.maps) {
            initAutocomplete();
        }
    });

    // Handler function for adding items
    function addItemHandler(e) {
        e.preventDefault();
        
        const type = this.dataset.type;
        
        const container = document.getElementById(`${type}-items`);
        if (container) {
            const index = container.children.length;
            container.insertAdjacentHTML('beforeend', getItemTemplate(type, index));
        } else {
            console.error('Container not found for type:', type);
        }
    }

    // Template for dynamic items
    function getItemTemplate(type, index) {
        // Additional fields for accommodation
        const accommodationFields = `
            <div class="row mb-3">
                <div class="col-md-4">
                    <label class="form-label">Living Area</label>
                    <input type="text" 
                           name="${type}s[${index}][living_area]" 
                           class="form-control" 
                           placeholder="e.g., 120 m²">
                </div>
                <div class="col-md-4">
                    <label class="form-label">Bed Count</label>
                    <input type="text" 
                           name="${type}s[${index}][bed_count]" 
                           class="form-control" 
                           placeholder="e.g., 2 double beds, 1 single">
                </div>
                <div class="col-md-4">
                    <label class="form-label">Facilities</label>
                    <input type="text" 
                           name="${type}s[${index}][facilities]" 
                           class="form-control" 
                           placeholder="e.g., WiFi, TV, Kitchen">
                </div>
                <div class="col-md-4">
                    <label class="form-label">Min Rental Days</label>
                    <input type="text" 
                           name="${type}s[${index}][min_rental_days]" 
                           class="form-control" 
                           placeholder="e.g., 3 days">
                </div>
            </div>
        `;

        // Additional fields for boat
        const boatFields = `
            <div class="row mb-3">
                <div class="col-12">
                    <label class="form-label">Facilities</label>
                    <input type="text" 
                           name="${type}s[${index}][facilities]" 
                           class="form-control" 
                           placeholder="e.g., GPS, Fish Finder, Life Jackets">
                </div>
            </div>
        `;
        
        const packageFields = `
            <div class="row mb-3">
                <div class="col-12">
                    <label class="form-label">Catering Info</label>
                    <input type="text" 
                           name="${type}s[${index}][catering_info]" 
                           class="form-control" 
                           placeholder="e.g., Breakfast, Lunch, Dinner">
                </div>
            </div>
        `;

        const extraFields = `
            <div class="row mb-3">
                <!-- Description -->
                <div class="col-12 mb-3">
                    <label class="form-label">Description</label>
                    <textarea name="${type}s[${index}][description]" 
                              class="form-control" 
                              rows="3"
                              placeholder="Enter description"></textarea>
                </div>
                <!-- Price and Price Type -->
                <div class="col-md-6">
                    <label class="form-label">Price</label>
                    <input type="number" 
                           name="${type}s[${index}][price]" 
                           class="form-control" 
                           step="0.01"
                           min="0"
                           placeholder="Enter price">
                </div>
                <div class="col-md-6">
                    <label class="form-label">Price Type</label>
                    <select name="${type}s[${index}][price_type]" class="form-control">
                        <option value="per_person">Per Person</option>
                        <option value="overall">Overall</option>
                    </select>
                </div>
            </div>
        `;

        return `
            <div class="card mb-3 item-card">
                <div class="card-body">
                    <!-- Header -->
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <h6 class="card-title mb-0">${type.charAt(0).toUpperCase() + type.slice(1)} Option ${index + 1}</h6>
                        <button type="button" class="btn btn-sm btn-danger remove-item">
                            <i class="fas fa-times"></i>
                        </button>
                    </div>

                    ${type === 'extra' ? extraFields : `
                        <!-- Title field -->
                        <div class="mb-3">
                            <label class="form-label">Title (Optional)</label>
                            <input type="text" 
                                   name="${type}s[${index}][title]" 
                                   class="form-control" 
                                   placeholder="Enter title">
                        </div>

                        <!-- Description -->
                        <div class="mb-4">
                            <label class="form-label">Description</label>
                            <textarea name="${type}s[${index}][description]" 
                                      class="form-control ckeditor-dynamic" 
                                      rows="3"></textarea>
                        </div>

                        <!-- Type-specific fields -->
                        ${type === 'accommodation' ? accommodationFields : ''}
                        ${type === 'boat' ? boatFields : ''}
                        ${type === 'package' ? packageFields : ''}

                        <!-- Capacity Input -->
                        <div class="mb-3">
                            <label class="form-label">Capacity (persons)</label>
                            <input type="number" 
                                   name="${type}s[${index}][capacity]" 
                                   class="form-control capacity-input"
                                   min="1" 
                                   max="20">
                            <small class="text-muted">Maximum 20 persons</small>
                        </div>

                        <!-- Dynamic Price Inputs Container -->
                        <div class="price-inputs-container mb-3">
                            <!-- Price inputs will be dynamically added here -->
                        </div>

                        <!-- Total Price Display -->
                        <div class="total-price-display text-end">
                            <h6>Total Price: <span class="total-price">0.00</span> €</h6>
                        </div>
                    `}
                </div>
            </div>
        `;
    }

    // Function to create price input fields
    function createPriceInputs(container, capacity, type, index) {
        const priceContainer = container.querySelector('.price-inputs-container');
        priceContainer.innerHTML = '';

        if (capacity > 0 && capacity <= 20) {
            priceContainer.innerHTML = '<h6 class="border-bottom pb-2 mb-3">Price per Person</h6>';
            
            const gridContainer = document.createElement('div');
            gridContainer.className = 'row row-cols-1 row-cols-md-2 row-cols-lg-3 g-3';

            for (let i = 0; i < capacity; i++) {
                const col = document.createElement('div');
                col.className = 'col';
                col.innerHTML = `
                    <div class="price-input-group">
                        <label class="form-label">Person ${i + 1}</label>
                        <div class="input-group">
                            <input type="number" 
                                   name="${type}[${index}][prices][${i}]" 
                                   class="form-control individual-price"
                                   min="0" 
                                   step="0.01" 
                                   placeholder="0.00">
                            <span class="input-group-text">€</span>
                        </div>
                    </div>
                `;
                gridContainer.appendChild(col);
            }

            priceContainer.appendChild(gridContainer);

            // Add event listeners to update total
            const priceInputs = priceContainer.querySelectorAll('.individual-price');
            priceInputs.forEach(input => {
                input.addEventListener('input', function() {
                    updateTotalPrice(container);
                });
            });
        }
    }

    // Function to update total price
    function updateTotalPrice(container) {
        const priceInputs = container.querySelectorAll('.individual-price');
        let total = 0;

        priceInputs.forEach(input => {
            total += parseFloat(input.value || 0);
        });

        container.querySelector('.total-price').textContent = total.toFixed(2);
    }

    // Update the capacity input event listener
    document.querySelector('#addVacationModal').addEventListener('input', function(e) {
        if (e.target.classList.contains('capacity-input')) {
            const card = e.target.closest('.card');
            const capacity = parseInt(e.target.value) || 0;
            const type = e.target.name.split('[')[0];
            const index = parseInt(e.target.name.split('[')[1]) || 0;

            if (capacity > 20) {
                e.target.value = 20;
                alert('Maximum capacity is 20 persons');
                return;
            }

            createPriceInputs(card, capacity, type, index);
        }
    });

    // Remove item handler (using event delegation)
    document.querySelector('#addVacationModal').addEventListener('click', function(e) {
        if (e.target.closest('.remove-item')) {
            e.preventDefault();
            const card = e.target.closest('.item-card');
            if (card) {
                card.remove();
                console.log('Removed item');
            }
        }
    });

    // Auto-calculate price based on capacity (using event delegation)
    document.querySelector('#addVacationModal').addEventListener('input', function(e) {
        if (e.target.classList.contains('capacity-input')) {
            const card = e.target.closest('.card');
            const priceInput = card.querySelector('.price-input');
            const capacity = parseInt(e.target.value) || 0;
            
            // Base price calculation (you can adjust this formula)
            const basePrice = capacity * 100; // Example: $100 per person
            priceInput.value = basePrice.toFixed(2);
        }
    });

    // Tagify initialization
    document.querySelectorAll('.tagify-input').forEach(input => {
        new Tagify(input, {
            maxTags: 10,
            dropdown: {
                maxItems: Infinity,
                classname: "tagify__dropdown",
                enabled: 0,
                closeOnSelect: false
            },
        });
    });

    // Slug functionality
    function slugify(text) {
        if (!text) return 'n-a';
        
        return text
            .toLowerCase()
            .normalize('NFD')
            .replace(/[\u0300-\u036f]/g, '')
            .replace(/[^\w\s-]/g, '-')
            .replace(/\s+/g, '-')
            .replace(/-+/g, '-')
            .replace(/^-+|-+$/g, '');
    }

    document.addEventListener('input', function(e) {
        if (e.target.matches('input[name="title"]')) {
            const modal = e.target.closest('.modal');
            const slugInput = modal.querySelector('input[name="slug"]');
            
            if (slugInput) {
                slugInput.value = slugify(e.target.value);
            }
        }
    });

    // Make slug inputs readonly
    document.addEventListener('shown.bs.modal', function(e) {
        if (e.target.matches('#addVacationModal')) {
            const slugInput = e.target.querySelector('input[name="slug"]');
            if (slugInput) {
                slugInput.setAttribute('readonly', true);
            }
        }
    });

    // Initialize DataTable
    let vacationtable = new DataTable('#vacationtable');

    function previewImages(input) {
        const preview = document.getElementById('imagePreview');
        preview.innerHTML = '';

        // First, check if there are existing images in the hidden input
        const existingGallery = document.getElementById('existingGallery').value;
        if (existingGallery) {
            const existingImages = JSON.parse(existingGallery);
            existingImages.forEach(imagePath => {
                addImagePreview(imagePath, true);
            });
        }

        // Then handle any newly selected files
        if (input.files) {
            Array.from(input.files).forEach(file => {
                const reader = new FileReader();
                reader.onload = function(e) {
                    addImagePreview(e.target.result, false);
                }
                reader.readAsDataURL(file);
            });
        }
    }

    function addImagePreview(src, isExisting) {
        const preview = document.getElementById('imagePreview');
        
        const div = document.createElement('div');
        div.className = 'position-relative';
        div.style.width = '100px';
        div.style.height = '100px';
        div.style.marginRight = '10px';
        div.style.marginBottom = '10px';
        
        const img = document.createElement('img');
        img.src = isExisting ? `/${src}` : src;
        img.style.width = '100%';
        img.style.height = '100%';
        img.style.objectFit = 'cover';
        img.style.borderRadius = '4px';
        
        // Add delete button
        const deleteBtn = document.createElement('button');
        deleteBtn.className = 'btn btn-danger btn-sm position-absolute top-0 end-0';
        deleteBtn.innerHTML = '×';
        deleteBtn.style.padding = '0 6px';
        deleteBtn.onclick = function(e) {
            e.preventDefault();
            div.remove();
            updateExistingGallery();
        };
        
        div.appendChild(img);
        div.appendChild(deleteBtn);
        
        if (isExisting) {
            div.dataset.path = src;
        }
        
        preview.appendChild(div);
    }

    function updateExistingGallery() {
        const preview = document.getElementById('imagePreview');
        const existingImages = Array.from(preview.querySelectorAll('[data-path]')).map(div => div.dataset.path);
        document.getElementById('existingGallery').value = JSON.stringify(existingImages);
    }

    // Add this function to handle editing
    function editVacation(id) {
        // Change modal title
        document.getElementById('addVacationModalLabel').textContent = 'Edit Vacation';
        
        // Change form action
        const form = document.querySelector('#addVacationModal form');
        form.action = `/admin/vacations/${id}`;
        
        // Add method spoofing for PUT request
        if (!form.querySelector('input[name="_method"]')) {
            const methodField = document.createElement('input');
            methodField.type = 'hidden';
            methodField.name = '_method';
            methodField.value = 'PUT';
            form.appendChild(methodField);
        }

        // Show loading state
        const modal = document.querySelector('#addVacationModal');
        if (modal) {
            modal.querySelector('.modal-content').style.opacity = '0.5';
        }

        // Fetch vacation data
        fetch(`/admin/vacations/${id}/edit`)
            .then(response => response.json())
            .then(data => {
                // Helper function to safely set form values
                const setFieldValue = (fieldName, value) => {
                    const field = form.querySelector(`[name="${fieldName}"]`);
                    if (field) {
                        if (field.type === 'checkbox') {
                            field.checked = !!value;
                        } else {
                            field.value = value || '';
                        }
                    }
                };

                // Set basic fields
                const fields = [
                    'title', 'slug', 'location', 'city', 'country', 'region',
                    'latitude', 'longitude', 'best_travel_times', 'surroundings_description',
                    'airport_distance', 'water_distance', 'shopping_distance',
                    'travel_included', 'travel_options', 'target_fish', 'included_services'
                ];

                // Set values for all basic fields
                fields.forEach(field => {
                    setFieldValue(field, data[field]);
                });

                // Set checkbox fields
                ['pets_allowed', 'smoking_allowed', 'disability_friendly'].forEach(field => {
                    setFieldValue(field, data[field]);
                });

                // Handle Tagify inputs
                const tagifyFields = ['target_fish', 'included_services'];
                tagifyFields.forEach(field => {
                    const tagifyInput = form.querySelector(`[name="${field}"]`);
                    if (tagifyInput && tagifyInput.tagify) {
                        tagifyInput.tagify.removeAllTags();
                        if (data[field]) {
                            tagifyInput.tagify.addTags(data[field]);
                        }
                    }
                });

                // Load dynamic items
                ['accommodations', 'boats', 'packages', 'guidings'].forEach(type => {
                    const container = document.getElementById(`${type.slice(0, -1)}-items`);
                    
                    if (container && data[type] && Array.isArray(data[type])) {
                        container.innerHTML = ''; // Clear existing items
                        
                        data[type].forEach((item, index) => {
                            
                            // Add the item template
                            container.insertAdjacentHTML('beforeend', getItemTemplate(type.slice(0, -1), index));
                            
                            // Get the newly added card
                            const card = container.lastElementChild;
                            
                            // Set basic fields
                            const descriptionField = card.querySelector(`textarea[name="${type.slice(0, -1)}s[${index}][description]"]`);
                            const capacityField = card.querySelector(`input[name="${type.slice(0, -1)}s[${index}][capacity]"]`);
                            
                            if (descriptionField) descriptionField.value = item.description || '';
                            if (capacityField) {
                                capacityField.value = item.capacity || '';
                                // Trigger capacity change to create price inputs
                                const event = new Event('input', { bubbles: true });
                                capacityField.dispatchEvent(event);
                            }
                            
                            // Handle dynamic fields
                            if (item.dynamic_fields) {
                                const dynamicFields = typeof item.dynamic_fields === 'string' 
                                    ? JSON.parse(item.dynamic_fields) 
                                    : item.dynamic_fields;
                                
                                // Set type-specific fields
                                if (type === 'accommodations') {
                                    ['living_area', 'bed_count', 'facilities', 'min_rental_days'].forEach(field => {
                                        const inputField = card.querySelector(`input[name="${type.slice(0, -1)}s[${index}][${field}]"]`);
                                        if (inputField) {
                                            inputField.value = dynamicFields[field] || '';
                                        }
                                    });
                                }
                                
                                if (type === 'boats') {
                                    const facilitiesField = card.querySelector(`input[name="${type.slice(0, -1)}s[${index}][facilities]"]`);
                                    if (facilitiesField) {
                                        facilitiesField.value = dynamicFields.facilities || '';
                                    }
                                }
                                
                                if (type === 'packages') {
                                    const cateringInfoField = card.querySelector(`input[name="${type.slice(0, -1)}s[${index}][catering_info]"]`);
                                    if (cateringInfoField) {
                                        cateringInfoField.value = dynamicFields.catering_info || '';
                                    }
                                }

                                // Set prices after a small delay to ensure inputs are created
                                setTimeout(() => {
                                    if (dynamicFields.prices) {
                                        const priceInputs = card.querySelectorAll('.individual-price');
                                        dynamicFields.prices.forEach((price, i) => {
                                            if (priceInputs[i]) {
                                                priceInputs[i].value = price;
                                            }
                                        });
                                        updateTotalPrice(card);
                                    }
                                }, 100);
                            }
                        });
                    }
                });

                // Handle gallery images
                if (data.gallery) {
                    let galleryImages;
                    try {
                        galleryImages = typeof data.gallery === 'string' ? JSON.parse(data.gallery) : data.gallery;
                    } catch (e) {
                        galleryImages = [];
                    }
                    
                    document.getElementById('existingGallery').value = JSON.stringify(galleryImages);
                    
                    // Clear and reload image preview
                    const preview = document.getElementById('imagePreview');
                    preview.innerHTML = '';
                    galleryImages.forEach(imagePath => {
                        addImagePreview(imagePath, true);
                    });
                }

                // Update CKEditor content
                if (CKEDITOR.instances.surroundings_description) {
                    CKEDITOR.instances.surroundings_description.setData(data.surroundings_description || '');
                }

                // Restore modal state
                if (modal) {
                    modal.querySelector('.modal-content').style.opacity = '1';
                }

                // Clear location field if coordinates are null
                const locationField = form.querySelector('#location');
                if (locationField) {
                    if (!data.latitude || !data.longitude) {
                        locationField.value = '';
                        
                        // Also clear related hidden fields
                        ['city', 'country', 'region', 'latitude', 'longitude'].forEach(field => {
                            const hiddenField = form.querySelector(`input[name="${field}"]`);
                            if (hiddenField) {
                                hiddenField.value = '';
                            }
                        });
                    }
                }
            })
            .catch(error => {
                console.error('Error fetching vacation data:', error);
                alert('Error loading vacation data. Please try again.');
                
                // Restore modal state
                if (modal) {
                    modal.querySelector('.modal-content').style.opacity = '1';
                }
            });
    }

    // Add event listener to reset form when modal is closed
    document.querySelector('#addVacationModal').addEventListener('hidden.bs.modal', function () {
        // Reset modal title
        document.getElementById('addVacationModalLabel').textContent = 'Add New Vacation';
        
        // Reset form action and remove method spoofing
        const form = this.querySelector('form');
        form.action = "{{ route('admin.vacations.store') }}";
        const methodField = form.querySelector('input[name="_method"]');
        if (methodField) methodField.remove();
        
        // Reset form
        form.reset();
        
        // Reset Tagify inputs
        document.querySelectorAll('.tagify-input').forEach(input => {
            input._tagify.removeAllTags();
        });
        
        // Reset image preview
        document.getElementById('imagePreview').innerHTML = '';

        // Reset CKEditor
        if (CKEDITOR.instances.surroundings_description) {
            CKEDITOR.instances.surroundings_description.setData('');
        }
    });

    function removeAllImages() {
        // Clear the preview container
        const preview = document.getElementById('imagePreview');
        preview.innerHTML = '';
        
        // Clear the file input
        const fileInput = document.querySelector('input[name="gallery[]"]');
        fileInput.value = '';
        
        // Clear the hidden input storing existing images
        document.getElementById('existingGallery').value = '';
    }

    // Add function to initialize CKEditor for dynamic textareas
    function initializeDynamicCKEditors() {
        document.querySelectorAll('.ckeditor-dynamic').forEach(textarea => {
            if (!textarea.classList.contains('ckeditor-initialized')) {
                CKEDITOR.replace(textarea);
                textarea.classList.add('ckeditor-initialized');
            }
        });
    }
</script>
@endsection
