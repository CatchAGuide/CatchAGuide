@extends('admin.layouts.app')

@section('title', $accommodation->title)

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="page-header">
                <div class="page-title">
                    <h1>{{ $accommodation->title }}</h1>
                    <p class="text-muted">{{ $accommodation->city }}, {{ $accommodation->country }}</p>
                </div>
                <div class="page-actions">
                    <a href="{{ route('admin.accommodations.index') }}" class="btn btn-secondary">
                        <i class="fas fa-arrow-left"></i> Back to List
                    </a>
                    <a href="{{ route('admin.accommodations.edit', $accommodation) }}" class="btn btn-warning">
                        <i class="fas fa-edit"></i> Edit
                    </a>
                    <form action="{{ route('admin.accommodations.destroy', $accommodation) }}" 
                          method="POST" class="d-inline" 
                          onsubmit="return confirm('{{ __('accommodations.confirm_delete') }}')">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn btn-danger">
                            <i class="fas fa-trash"></i> Delete
                        </button>
                    </form>
                </div>
            </div>

            <div class="row">
                <!-- Main Content -->
                <div class="col-lg-8">
                    <!-- Basic Information -->
                    <div class="card mb-4">
                        <div class="card-header">
                            <h3 class="card-title">{{ __('accommodations.sections.basic_info') }}</h3>
                            <div class="card-tools">
                                <span class="badge badge-{{ $accommodation->status === 'active' ? 'success' : 'secondary' }}">
                                    {{ __('accommodations.options.statuses.' . $accommodation->status) }}
                                </span>
                            </div>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="info-item">
                                        <label>{{ __('accommodations.fields.title') }}</label>
                                        <p>{{ $accommodation->title }}</p>
                                    </div>
                                    <div class="info-item">
                                        <label>{{ __('accommodations.fields.accommodation_type') }}</label>
                                        <p>
                                            <span class="badge badge-info">
                                                {{ __('accommodations.options.accommodation_types.' . $accommodation->accommodation_type) }}
                                            </span>
                                        </p>
                                    </div>
                                    <div class="info-item">
                                        <label>{{ __('accommodations.fields.condition_or_style') }}</label>
                                        <p>{{ $accommodation->condition_or_style ?? 'N/A' }}</p>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="info-item">
                                        <label>{{ __('accommodations.fields.living_area_sqm') }}</label>
                                        <p>{{ $accommodation->living_area_sqm ? $accommodation->living_area_sqm . ' sqm' : 'N/A' }}</p>
                                    </div>
                                    <div class="info-item">
                                        <label>{{ __('accommodations.fields.max_occupancy') }}</label>
                                        <p>{{ $accommodation->max_occupancy ?? 'N/A' }}</p>
                                    </div>
                                    <div class="info-item">
                                        <label>{{ __('accommodations.fields.floor_layout') }}</label>
                                        <p>{{ $accommodation->floor_layout ?? 'N/A' }}</p>
                                    </div>
                                </div>
                            </div>
                            @if($accommodation->description)
                                <div class="info-item">
                                    <label>{{ __('accommodations.fields.description') }}</label>
                                    <p>{{ $accommodation->description }}</p>
                                </div>
                            @endif
                        </div>
                    </div>

                    <!-- Location Information -->
                    <div class="card mb-4">
                        <div class="card-header">
                            <h3 class="card-title">{{ __('accommodations.sections.location_info') }}</h3>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="info-item">
                                        <label>{{ __('accommodations.fields.location') }}</label>
                                        <p>{{ $accommodation->location }}</p>
                                    </div>
                                    <div class="info-item">
                                        <label>{{ __('accommodations.fields.city') }}</label>
                                        <p>{{ $accommodation->city }}</p>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="info-item">
                                        <label>{{ __('accommodations.fields.country') }}</label>
                                        <p>{{ $accommodation->country }}</p>
                                    </div>
                                    <div class="info-item">
                                        <label>{{ __('accommodations.fields.region') }}</label>
                                        <p>{{ $accommodation->region }}</p>
                                    </div>
                                </div>
                            </div>
                            @if($accommodation->lat && $accommodation->lng)
                                <div class="info-item">
                                    <label>Coordinates</label>
                                    <p>{{ $accommodation->lat }}, {{ $accommodation->lng }}</p>
                                </div>
                            @endif
                            @if($accommodation->location_description)
                                <div class="info-item">
                                    <label>{{ __('accommodations.fields.location_description') }}</label>
                                    <p>{{ $accommodation->location_description }}</p>
                                </div>
                            @endif
                        </div>
                    </div>

                    <!-- Accommodation Details -->
                    <div class="card mb-4">
                        <div class="card-header">
                            <h3 class="card-title">{{ __('accommodations.sections.accommodation_details') }}</h3>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="info-item">
                                        <label>{{ __('accommodations.fields.number_of_bedrooms') }}</label>
                                        <p>{{ $accommodation->number_of_bedrooms ?? 'N/A' }}</p>
                                    </div>
                                    <div class="info-item">
                                        <label>{{ __('accommodations.fields.number_of_beds') }}</label>
                                        <p>{{ $accommodation->number_of_beds ?? 'N/A' }}</p>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="info-item">
                                        <label>{{ __('accommodations.fields.bathroom') }}</label>
                                        <p>{{ $accommodation->bathroom ?? 'N/A' }}</p>
                                    </div>
                                    <div class="info-item">
                                        <label>{{ __('accommodations.fields.kitchen_type') }}</label>
                                        <p>
                                            @if($accommodation->kitchen_type)
                                                <span class="badge badge-secondary">
                                                    {{ __('accommodations.options.kitchen_types.' . $accommodation->kitchen_type) }}
                                                </span>
                                            @else
                                                N/A
                                            @endif
                                        </p>
                                    </div>
                                </div>
                            </div>
                            @if($accommodation->bed_types && count($accommodation->bed_types) > 0)
                                <div class="info-item">
                                    <label>{{ __('accommodations.fields.bed_types') }}</label>
                                    <div class="amenities-list">
                                        @foreach($accommodation->bed_types as $bedType)
                                            <span class="amenity-tag">
                                                {{ __('accommodations.options.bed_types.' . $bedType) }}
                                            </span>
                                        @endforeach
                                    </div>
                                </div>
                            @endif
                        </div>
                    </div>

                    <!-- Amenities -->
                    <div class="card mb-4">
                        <div class="card-header">
                            <h3 class="card-title">{{ __('accommodations.sections.amenities') }}</h3>
                        </div>
                        <div class="card-body">
                            <div class="amenities-grid">
                                @php
                                    $amenities = [
                                        'living_room' => __('accommodations.fields.living_room'),
                                        'dining_room_or_area' => __('accommodations.fields.dining_room_or_area'),
                                        'terrace' => __('accommodations.fields.terrace'),
                                        'garden' => __('accommodations.fields.garden'),
                                        'swimming_pool' => __('accommodations.fields.swimming_pool'),
                                        'wifi_or_internet' => __('accommodations.fields.wifi_or_internet'),
                                        'pets_allowed' => __('accommodations.fields.pets_allowed'),
                                        'smoking_allowed' => __('accommodations.fields.smoking_allowed'),
                                        'reception_available' => __('accommodations.fields.reception_available'),
                                    ];
                                @endphp
                                @foreach($amenities as $field => $label)
                                    @if($accommodation->$field)
                                        <div class="amenity-item">
                                            <i class="fas fa-check text-success"></i>
                                            <span>{{ $label }}</span>
                                        </div>
                                    @endif
                                @endforeach
                            </div>
                        </div>
                    </div>

                    <!-- Kitchen Equipment -->
                    <div class="card mb-4">
                        <div class="card-header">
                            <h3 class="card-title">{{ __('accommodations.sections.kitchen_equipment') }}</h3>
                        </div>
                        <div class="card-body">
                            <div class="amenities-grid">
                                @php
                                    $kitchenEquipment = [
                                        'refrigerator_freezer' => __('accommodations.fields.refrigerator_freezer'),
                                        'oven' => __('accommodations.fields.oven'),
                                        'stove_or_ceramic_hob' => __('accommodations.fields.stove_or_ceramic_hob'),
                                        'microwave' => __('accommodations.fields.microwave'),
                                        'dishwasher' => __('accommodations.fields.dishwasher'),
                                        'coffee_machine' => __('accommodations.fields.coffee_machine'),
                                        'cookware_and_dishes' => __('accommodations.fields.cookware_and_dishes'),
                                    ];
                                @endphp
                                @foreach($kitchenEquipment as $field => $label)
                                    @if($accommodation->$field)
                                        <div class="amenity-item">
                                            <i class="fas fa-check text-success"></i>
                                            <span>{{ $label }}</span>
                                        </div>
                                    @endif
                                @endforeach
                            </div>
                        </div>
                    </div>

                    <!-- Laundry Facilities -->
                    <div class="card mb-4">
                        <div class="card-header">
                            <h3 class="card-title">{{ __('accommodations.sections.laundry_facilities') }}</h3>
                        </div>
                        <div class="card-body">
                            <div class="amenities-grid">
                                @php
                                    $laundryFacilities = [
                                        'washing_machine' => __('accommodations.fields.washing_machine'),
                                        'dryer' => __('accommodations.fields.dryer'),
                                        'separate_laundry_room' => __('accommodations.fields.separate_laundry_room'),
                                        'freezer_room' => __('accommodations.fields.freezer_room'),
                                        'filleting_house' => __('accommodations.fields.filleting_house'),
                                    ];
                                @endphp
                                @foreach($laundryFacilities as $field => $label)
                                    @if($accommodation->$field)
                                        <div class="amenity-item">
                                            <i class="fas fa-check text-success"></i>
                                            <span>{{ $label }}</span>
                                        </div>
                                    @endif
                                @endforeach
                            </div>
                        </div>
                    </div>

                    <!-- Policies -->
                    <div class="card mb-4">
                        <div class="card-header">
                            <h3 class="card-title">{{ __('accommodations.sections.policies') }}</h3>
                        </div>
                        <div class="card-body">
                            <div class="amenities-grid">
                                @php
                                    $policies = [
                                        'bed_linen_included' => __('accommodations.fields.bed_linen_included'),
                                        'utilities_included' => __('accommodations.fields.utilities_included'),
                                    ];
                                @endphp
                                @foreach($policies as $field => $label)
                                    @if($accommodation->$field)
                                        <div class="amenity-item">
                                            <i class="fas fa-check text-success"></i>
                                            <span>{{ $label }}</span>
                                        </div>
                                    @endif
                                @endforeach
                            </div>
                        </div>
                    </div>

                    <!-- Location Distances -->
                    <div class="card mb-4">
                        <div class="card-header">
                            <h3 class="card-title">{{ __('accommodations.sections.location_distances') }}</h3>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="info-item">
                                        <label>{{ __('accommodations.fields.distance_to_water_m') }}</label>
                                        <p>{{ $accommodation->distance_to_water_m ? $accommodation->distance_to_water_m . ' m' : 'N/A' }}</p>
                                    </div>
                                    <div class="info-item">
                                        <label>{{ __('accommodations.fields.distance_to_boat_berth_m') }}</label>
                                        <p>{{ $accommodation->distance_to_boat_berth_m ? $accommodation->distance_to_boat_berth_m . ' m' : 'N/A' }}</p>
                                    </div>
                                    <div class="info-item">
                                        <label>{{ __('accommodations.fields.distance_to_shop_km') }}</label>
                                        <p>{{ $accommodation->distance_to_shop_km ? $accommodation->distance_to_shop_km . ' km' : 'N/A' }}</p>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="info-item">
                                        <label>{{ __('accommodations.fields.distance_to_parking_m') }}</label>
                                        <p>{{ $accommodation->distance_to_parking_m ? $accommodation->distance_to_parking_m . ' m' : 'N/A' }}</p>
                                    </div>
                                    <div class="info-item">
                                        <label>{{ __('accommodations.fields.distance_to_nearest_town_km') }}</label>
                                        <p>{{ $accommodation->distance_to_nearest_town_km ? $accommodation->distance_to_nearest_town_km . ' km' : 'N/A' }}</p>
                                    </div>
                                    <div class="info-item">
                                        <label>{{ __('accommodations.fields.distance_to_airport_km') }}</label>
                                        <p>{{ $accommodation->distance_to_airport_km ? $accommodation->distance_to_airport_km . ' km' : 'N/A' }}</p>
                                    </div>
                                </div>
                            </div>
                            <div class="info-item">
                                <label>{{ __('accommodations.fields.distance_to_ferry_port_km') }}</label>
                                <p>{{ $accommodation->distance_to_ferry_port_km ? $accommodation->distance_to_ferry_port_km . ' km' : 'N/A' }}</p>
                            </div>
                        </div>
                    </div>

                    <!-- Pricing -->
                    <div class="card mb-4">
                        <div class="card-header">
                            <h3 class="card-title">{{ __('accommodations.sections.pricing') }}</h3>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="info-item">
                                        <label>{{ __('accommodations.fields.price_per_night') }}</label>
                                        <p>
                                            @if($accommodation->price_per_night)
                                                <strong>{{ number_format($accommodation->price_per_night, 2) }} {{ $accommodation->currency }}</strong>
                                            @else
                                                N/A
                                            @endif
                                        </p>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="info-item">
                                        <label>{{ __('accommodations.fields.price_per_week') }}</label>
                                        <p>
                                            @if($accommodation->price_per_week)
                                                <strong>{{ number_format($accommodation->price_per_week, 2) }} {{ $accommodation->currency }}</strong>
                                            @else
                                                N/A
                                            @endif
                                        </p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Rental Terms -->
                    <div class="card mb-4">
                        <div class="card-header">
                            <h3 class="card-title">{{ __('accommodations.sections.rental_terms') }}</h3>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="info-item">
                                        <label>{{ __('accommodations.fields.changeover_day') }}</label>
                                        <p>
                                            @if($accommodation->changeover_day)
                                                {{ __('accommodations.options.changeover_days.' . $accommodation->changeover_day) }}
                                            @else
                                                N/A
                                            @endif
                                        </p>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="info-item">
                                        <label>{{ __('accommodations.fields.minimum_stay_nights') }}</label>
                                        <p>{{ $accommodation->minimum_stay_nights ?? 'N/A' }}</p>
                                    </div>
                                </div>
                            </div>
                            @if($accommodation->rental_includes && count($accommodation->rental_includes) > 0)
                                <div class="info-item">
                                    <label>{{ __('accommodations.fields.rental_includes') }}</label>
                                    <div class="amenities-list">
                                        @foreach($accommodation->rental_includes as $include)
                                            <span class="amenity-tag">{{ ucfirst($include) }}</span>
                                        @endforeach
                                    </div>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>

                <!-- Sidebar -->
                <div class="col-lg-4">
                    <!-- Images -->
                    @if($accommodation->thumbnail_path || ($accommodation->gallery_images && count($accommodation->gallery_images) > 0))
                        <div class="card mb-4">
                            <div class="card-header">
                                <h3 class="card-title">Images</h3>
                            </div>
                            <div class="card-body">
                                @if($accommodation->thumbnail_path)
                                    <div class="thumbnail-preview mb-3">
                                        <img src="{{ Storage::url($accommodation->thumbnail_path) }}" 
                                             alt="Thumbnail" class="img-fluid rounded">
                                    </div>
                                @endif
                                
                                @if($accommodation->gallery_images && count($accommodation->gallery_images) > 0)
                                    <div class="gallery-preview">
                                        <h5>Gallery Images</h5>
                                        <div class="gallery-grid">
                                            @foreach($accommodation->gallery_images as $image)
                                                <img src="{{ Storage::url($image) }}" 
                                                     alt="Gallery image" class="img-fluid rounded">
                                            @endforeach
                                        </div>
                                    </div>
                                @endif
                            </div>
                        </div>
                    @endif

                    <!-- Owner Information -->
                    <div class="card mb-4">
                        <div class="card-header">
                            <h3 class="card-title">Owner Information</h3>
                        </div>
                        <div class="card-body">
                            <div class="info-item">
                                <label>Owner</label>
                                <p>{{ $accommodation->user->name ?? 'N/A' }}</p>
                            </div>
                            <div class="info-item">
                                <label>Email</label>
                                <p>{{ $accommodation->user->email ?? 'N/A' }}</p>
                            </div>
                        </div>
                    </div>

                    <!-- Quick Stats -->
                    <div class="card mb-4">
                        <div class="card-header">
                            <h3 class="card-title">Quick Stats</h3>
                        </div>
                        <div class="card-body">
                            <div class="info-item">
                                <label>Created</label>
                                <p>{{ $accommodation->created_at->format('M d, Y') }}</p>
                            </div>
                            <div class="info-item">
                                <label>Last Updated</label>
                                <p>{{ $accommodation->updated_at->format('M d, Y') }}</p>
                            </div>
                            <div class="info-item">
                                <label>ID</label>
                                <p>{{ $accommodation->id }}</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('styles')
<style>
.page-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 30px;
    padding-bottom: 20px;
    border-bottom: 1px solid #e9ecef;
}

.page-title h1 {
    margin: 0;
    color: #2c3e50;
    font-size: 2rem;
    font-weight: 600;
}

.page-title .text-muted {
    margin: 5px 0 0 0;
    font-size: 1rem;
}

.page-actions {
    display: flex;
    gap: 10px;
}

.page-actions .btn {
    padding: 10px 20px;
    font-weight: 600;
}

.card {
    box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
    border: none;
    margin-bottom: 20px;
}

.card-header {
    background: #f8f9fa;
    border-bottom: 1px solid #e9ecef;
    padding: 20px;
    display: flex;
    justify-content: space-between;
    align-items: center;
}

.card-header .card-title {
    margin: 0;
    color: #2c3e50;
    font-size: 1.3rem;
    font-weight: 600;
}

.card-body {
    padding: 25px;
}

.info-item {
    margin-bottom: 20px;
}

.info-item label {
    display: block;
    font-weight: 600;
    color: #2c3e50;
    margin-bottom: 5px;
    font-size: 0.9rem;
}

.info-item p {
    margin: 0;
    color: #495057;
    font-size: 1rem;
}

.amenities-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
    gap: 15px;
}

.amenity-item {
    display: flex;
    align-items: center;
    gap: 8px;
    padding: 8px 12px;
    background: #f8f9fa;
    border-radius: 6px;
    font-size: 0.9rem;
}

.amenities-list {
    display: flex;
    flex-wrap: wrap;
    gap: 8px;
}

.amenity-tag {
    background: #e3f2fd;
    color: #1976d2;
    padding: 4px 8px;
    border-radius: 12px;
    font-size: 0.8rem;
    font-weight: 500;
}

.badge {
    font-size: 0.75rem;
    padding: 0.375rem 0.75rem;
}

.thumbnail-preview img {
    max-width: 100%;
    height: auto;
}

.gallery-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(100px, 1fr));
    gap: 10px;
}

.gallery-grid img {
    width: 100%;
    height: 80px;
    object-fit: cover;
    border-radius: 4px;
}

@media (max-width: 768px) {
    .page-header {
        flex-direction: column;
        align-items: flex-start;
        gap: 15px;
    }
    
    .page-actions {
        flex-direction: column;
        width: 100%;
    }
    
    .page-actions .btn {
        width: 100%;
    }
    
    .card-header {
        flex-direction: column;
        align-items: flex-start;
        gap: 10px;
    }
    
    .amenities-grid {
        grid-template-columns: 1fr;
    }
}
</style>
@endpush
