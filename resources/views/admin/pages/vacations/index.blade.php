@extends('admin.layouts.app')

@section('title', 'Alle Vacations')

@section('custom_style')
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@yaireo/tagify/dist/tagify.css">
<!-- Add Google Maps API script in the head -->
<script src="https://maps.googleapis.com/maps/api/js?key={{ env('GOOGLE_MAP_API_KEY') }}&libraries=places" async defer></script>
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
                                                <a href="{{ route('admin.vacations.edit', $vacation) }}" class="btn btn-sm btn-secondary"><i class="fa fa-pencil"></i></a>
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

    @include('admin.pages.vacations.modals.add')
    @include('admin.pages.vacations.modals.edit')
@endsection

@section('js_after')
<script src="https://unpkg.com/@yaireo/tagify"></script>

<script>
    // Define initAutocomplete function before loading Google Maps API
    function initAutocomplete() {
        console.log('Initializing autocomplete...'); // Debug log
        const locationInputs = document.querySelectorAll('#location, #edit_location');
        
        locationInputs.forEach(locationInput => {
            if (locationInput) {
                console.log('Setting up autocomplete for:', locationInput.id); // Debug log
                
                const autocomplete = new google.maps.places.Autocomplete(locationInput, {
                    types: ['(cities)'],
                    fields: ['address_components', 'geometry', 'formatted_address']
                });

                console.log('Autocomplete instance created'); // Debug log

                autocomplete.addListener('place_changed', function() {
                    console.log('Place changed event fired'); // Debug log
                    const place = autocomplete.getPlace();
                    const modal = locationInput.closest('.modal');
                    
                    console.log('Selected place:', place); // Debug log

                    if (!place.geometry) {
                        window.alert("No details available for input: '" + place.name + "'");
                        return;
                    }

                    // Set latitude and longitude
                    modal.querySelector('input[name="latitude"]').value = place.geometry.location.lat();
                    modal.querySelector('input[name="longitude"]').value = place.geometry.location.lng();

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
                    modal.querySelector('input[name="city"]').value = city;
                    modal.querySelector('input[name="country"]').value = country;
                    modal.querySelector('input[name="region"]').value = region;
                });
            }
        });
    }

    // Initialize everything else
    document.addEventListener('DOMContentLoaded', function() {
        // Check if Google Maps API is loaded
        if (typeof google === 'undefined') {
            console.error('Google Maps API not loaded!');
            return;
        }
        
        // Initialize autocomplete
        initAutocomplete();
        
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
            if (e.target.matches('#addVacationModal, #editVacationModal')) {
                const slugInput = e.target.querySelector('input[name="slug"]');
                if (slugInput) {
                    slugInput.setAttribute('readonly', true);
                }
            }
        });

        // Initialize DataTable
        let vacationtable = new DataTable('#vacationtable');
    });

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
        if (event.target.id === 'addVacationModal' || event.target.id === 'editVacationModal') {
            initAutocomplete();
        }
    });
</script>
@endsection
