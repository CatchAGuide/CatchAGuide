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
        console.log('Initializing autocomplete...'); // Debug log
        const locationInput = document.querySelector('#location');
        
        if (locationInput) {
            console.log('Setting up autocomplete for location input'); // Debug log
            
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

    // Reinitialize autocomplete when modal is shown
    document.querySelector('#addVacationModal').addEventListener('shown.bs.modal', function () {
        // Only initialize if Google Maps API is loaded
        if (window.google && window.google.maps) {
            initAutocomplete();
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

    // Add modal event listener to reinitialize autocomplete when modal opens
    document.addEventListener('shown.bs.modal', function(event) {
        if (event.target.id === 'addVacationModal') {
            initAutocomplete();
        }
    });

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
                setFieldValue('disability_friendly', data.disability_friendly);

                // Handle JSON fields properly
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

                // If there are existing images, display them
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
</script>
@endsection
