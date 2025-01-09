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
                                <i class="fas fa-plus"></i>Vacation
                            </button>
                        </div>
                        <div class="card-body table-responsive">
                            <table id="vacationtable" class="table">
                                <thead>
                                    <tr>
                                        <th class="wd-15p border-bottom-0">ID</th>
                                        <th class="wd-15p border-bottom-0">Name Name of the Vacation</th>
                                        <th class="wd-10p border-bottom-0">Price per Person</th>
                                        <th class="wd-10p border-bottom-0">Accommodation Price</th>
                                        <th class="wd-25p border-bottom-0">Guiding Price</th>
                                        <th class="wd-25p border-bottom-0">Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($vacations as $vacation)
                                    <tr>
                                        <td>{{$vacation->id}}</td>
                                        <td>
                                            <div class="d-flex flex-column">
                                                <div class="fw-bold">{{$vacation->title}}</div>
                                                <div class="text-info">{{$vacation->location}}</div>
                                            </div>
       
                                        </td>
                                        <td>
                                            {{$vacation->price_per_person}}
                                        </td>
                                        <td>
                                            {{$vacation->accommodation_price}}
                                        </td>
                                        <td>
                                            {{$vacation->guiding_price}}
                                        </td>
                                        <td class="text-center">
                                            <div class="btn-group">
                                                @if($vacation->status == 1)
                                                    <a href="{{ route('admin.changeVacationStatus', $vacation->id) }}" title="Diactivate" class="btn btn-sm btn-danger"><i class="fa fa-times"></i></a>
                                                @else
                                                    <a href="{{ route('admin.changeVacationStatus', $vacation->id) }}" title="Activate" class="btn btn-sm btn-success"><i class="fa fa-check"></i></a>
                                                @endif
                                                <a href="#" onclick="editVacation({{ $vacation->id }})" class="btn btn-sm btn-secondary" data-bs-toggle="modal" data-bs-target="#addVacationModal">
                                                    <i class="fa fa-pen-to-square"></i>
                                                </a>
                                                <a href="{{ route('admin.vacations.show', $vacation) }}" class="btn btn-sm btn-primary"><i class="fa fa-search"></i></a>
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
    // Define initAutocomplete function before loading the Google Maps API
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
        
        // Initialize add item buttons
        const addButtons = document.querySelectorAll('.add-item');
        
        addButtons.forEach(button => {
            // Remove existing click listeners to prevent duplicates
            button.removeEventListener('click', addItemHandler);
            // Add new click listener
            button.addEventListener('click', addItemHandler);
        });

        // Only initialize if Google Maps API is loaded
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
                           placeholder="e.g., 120 m²" 
                           required>
                </div>
                <div class="col-md-4">
                    <label class="form-label">Bed Count</label>
                    <input type="text" 
                           name="${type}s[${index}][bed_count]" 
                           class="form-control" 
                           placeholder="e.g., 2 double beds, 1 single" 
                           required>
                </div>
                <div class="col-md-4">
                    <label class="form-label">Facilities</label>
                    <input type="text" 
                           name="${type}s[${index}][facilities]" 
                           class="form-control" 
                           placeholder="e.g., WiFi, TV, Kitchen" 
                           required>
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
                           placeholder="e.g., GPS, Fish Finder, Life Jackets" 
                           required>
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

                    <!-- Description -->
                    <div class="mb-4">
                        <label class="form-label">Description</label>
                        <textarea name="${type}s[${index}][description]" class="form-control" rows="3" required></textarea>
                    </div>

                    <!-- Type-specific fields -->
                    ${type === 'accommodation' ? accommodationFields : ''}
                    ${type === 'boat' ? boatFields : ''}

                    <!-- Capacity Input -->
                    <div class="mb-3">
                        <label class="form-label">Capacity (persons)</label>
                        <input type="number" 
                               name="${type}s[${index}][capacity]" 
                               class="form-control capacity-input" 
                               required 
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
                                   required 
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

        if (input.files) {
            Array.from(input.files).forEach(file => {
                const reader = new FileReader();
                
                reader.onload = function(e) {
                    const div = document.createElement('div');
                    div.style.width = '100px';
                    div.style.height = '100px';
                    div.style.position = 'relative';
                    
                    const img = document.createElement('img');
                    img.src = e.target.result;
                    img.style.width = '100%';
                    img.style.height = '100%';
                    img.style.objectFit = 'cover';
                    img.style.borderRadius = '4px';
                    
                    div.appendChild(img);
                    preview.appendChild(div);
                }
                
                reader.readAsDataURL(file);
            });
        }
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

        // Fetch vacation data
        fetch(`/admin/vacations/${id}/edit`)
            .then(response => response.json())
            .then(data => {                // Helper function to safely set form values
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
                    'id', 'title', 'slug', 'location', 'city', 'country',
                    'region', 'latitude', 'longitude', 'best_travel_times',
                    'surroundings_description', 'airport_distance', 'water_distance',
                    'shopping_distance', 'travel_included', 'travel_options',
                    'amenities', 'target_fish', 'accommodation_description', 'living_area',
                    'bedroom_count', 'bed_count', 'max_persons', 'min_rental_days',
                    'basic_fishing_description', 'boat_description', 'catering_info', 'package_price_per_person',
                    'accommodation_price', 'boat_rental_price', 'guiding_price',
                    'additional_services', 'included_services', 'equipment'
                ];

                // Set values for all fields
                fields.forEach(field => setFieldValue(field, data[field]));

                // Handle checkboxes
                setFieldValue('pets_allowed', data.pets_allowed);
                setFieldValue('smoking_allowed', data.smoking_allowed);
                setFieldValue('disability_friendly', data.disability_friendly);// Handle JSON fields properly

                const jsonFields = ['best_travel_times', 'travel_options'];
                jsonFields.forEach(field => {
                    const fieldInput = form.querySelector(`[name="${field}"]`);
                    if (fieldInput && data[field]) {
                        // Parse the JSON string if it's not already an array
                        const value = typeof data[field] === 'string' ? 
                            JSON.parse(data[field]) : data[field];
                        fieldInput.value = JSON.stringify(value);
                    }
                });

                // Clear existing image preview
                const imagePreview = document.getElementById('imagePreview');
                if (imagePreview) {
                    imagePreview.innerHTML = '';
                } 

                if (data.images && data.images.length) {
                    data.images.forEach(image => {
                        // Create preview for existing images
                        const div = document.createElement('div');
                        div.style.width = '100px';
                        div.style.height = '100px';
                        div.style.position = 'relative';
                        
                        const img = document.createElement('img');
                        img.src = image.url; // Adjust based on your image URL structure
                        img.style.width = '100%';
                        img.style.height = '100%';
                        img.style.objectFit = 'cover';
                        img.style.borderRadius = '4px';
                        
                        div.appendChild(img);
                        imagePreview.appendChild(div);
                    });
                }

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
                            card.querySelector('textarea[name$="[description]"]').value = item.description;
                            card.querySelector('input[name$="[capacity]"]').value = item.capacity;
                            
                            // Parse dynamic fields
                            const dynamicFields = typeof item.dynamic_fields === 'string' 
                                ? JSON.parse(item.dynamic_fields) 
                                : item.dynamic_fields;
                            
                            console.log('Dynamic fields for', type, ':', dynamicFields); // Debug log

                            // Set type-specific fields
                            if (type === 'accommodations') {
                                if (card.querySelector('input[name$="[living_area]"]')) {
                                    card.querySelector('input[name$="[living_area]"]').value = dynamicFields.living_area || '';
                                }
                                if (card.querySelector('input[name$="[bed_count]"]')) {
                                    card.querySelector('input[name$="[bed_count]"]').value = dynamicFields.bed_count || '';
                                }
                                if (card.querySelector('input[name$="[facilities]"]')) {
                                    card.querySelector('input[name$="[facilities]"]').value = dynamicFields.facilities || '';
                                }
                            }
                            
                            if (type === 'boats') {
                                if (card.querySelector('input[name$="[facilities]"]')) {
                                    card.querySelector('input[name$="[facilities]"]').value = dynamicFields.facilities || '';
                                }
                            }

                            // Create and populate price inputs
                            if (dynamicFields && dynamicFields.prices) {
                                createPriceInputs(card, item.capacity, type.slice(0, -1), index);
                                
                                const priceInputs = card.querySelectorAll('.individual-price');
                                dynamicFields.prices.forEach((price, i) => {
                                    if (priceInputs[i]) {
                                        priceInputs[i].value = price;
                                    }
                                });
                                
                                // Update total price
                                updateTotalPrice(card);
                            }
                        });
                    }
                });
            })
            .catch(error => {
                console.error('Error fetching vacation data:', error);
                alert('Error loading vacation data. Please try again.');
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
    });

    // Add this to your editVacation function
    function loadDynamicItems(data) {
        ['accommodation', 'boat', 'package', 'guiding'].forEach(type => {
            const container = document.getElementById(`${type}-items`);
            if (container) {
                container.innerHTML = '';
                
                if (data[`${type}s`] && Array.isArray(data[`${type}s`])) {
                    data[`${type}s`].forEach((item, index) => {
                        container.insertAdjacentHTML('beforeend', getItemTemplate(type, index));
                        
                        // Fill in the values
                        const card = container.lastElementChild;
                        card.querySelector('textarea[name$="[description]"]').value = item.description;
                        card.querySelector('input[name$="[capacity]"]').value = item.capacity;
                        
                        // Parse dynamic_fields
                        const dynamicFields = JSON.parse(item.dynamic_fields || '{}');
                        
                        // Fill in type-specific fields
                        if (type === 'accommodation') {
                            if (card.querySelector('input[name$="[living_area]"]')) {
                                card.querySelector('input[name$="[living_area]"]').value = dynamicFields.living_area || '';
                            }
                            if (card.querySelector('input[name$="[bed_count]"]')) {
                                card.querySelector('input[name$="[bed_count]"]').value = dynamicFields.bed_count || '';
                            }
                            if (card.querySelector('input[name$="[facilities]"]')) {
                                card.querySelector('input[name$="[facilities]"]').value = dynamicFields.facilities || '';
                            }
                        }
                        
                        if (type === 'boat') {
                            if (card.querySelector('input[name$="[facilities]"]')) {
                                card.querySelector('input[name$="[facilities]"]').value = dynamicFields.facilities || '';
                            }
                        }

                        // Create price inputs
                        if (dynamicFields.prices) {
                            createPriceInputs(card, item.capacity, type, index);
                            const priceInputs = card.querySelectorAll('.individual-price');
                            dynamicFields.prices.forEach((price, i) => {
                                if (priceInputs[i]) {
                                    priceInputs[i].value = price;
                                }
                            });
                            updateTotalPrice(card);
                        }
                    });
                }
            }
        });
    }
</script>
@endsection
