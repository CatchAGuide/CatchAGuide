@extends('admin.layouts.app')

@section('title', __('camps.show'))

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="page-header">
                <div class="page-title">
                    <h1>{{ __('camps.show') }}</h1>
                    <p class="text-muted">{{ $camp->title ?: 'Untitled Camp' }}</p>
                </div>
                <div class="page-actions">
                    <a href="{{ route('admin.camps.index') }}" class="btn btn-secondary">
                        <i class="fas fa-arrow-left"></i> {{ __('camps.back_to_list') }}
                    </a>
                    <a href="{{ route('admin.camps.edit', $camp->id) }}" class="btn btn-warning">
                        <i class="fas fa-edit"></i> {{ __('camps.edit') }}
                    </a>
                </div>
            </div>

            <div class="row">
                <div class="col-md-8">
                    <div class="card">
                        <div class="card-header">
                            <h3 class="card-title">Camp Details</h3>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-6">
                                    <h5>Basic Information</h5>
                                    <table class="table table-borderless">
                                        <tr>
                                            <td><strong>Title:</strong></td>
                                            <td>{{ $camp->title ?: 'Not set' }}</td>
                                        </tr>
                                        <tr>
                                            <td><strong>Location:</strong></td>
                                            <td>{{ $camp->location ?: 'Not set' }}</td>
                                        </tr>
                                        <tr>
                                            <td><strong>Status:</strong></td>
                                            <td>
                                                <span class="badge badge-{{ $camp->status === 'active' ? 'success' : ($camp->status === 'draft' ? 'warning' : 'secondary') }}">
                                                    {{ ucfirst($camp->status) }}
                                                </span>
                                            </td>
                                        </tr>
                                        <tr>
                                            <td><strong>Created By:</strong></td>
                                            <td>{{ $camp->user->name ?? 'Unknown' }}</td>
                                        </tr>
                                        <tr>
                                            <td><strong>Created At:</strong></td>
                                            <td>{{ $camp->created_at->format('M d, Y H:i') }}</td>
                                        </tr>
                                        <tr>
                                            <td><strong>Updated At:</strong></td>
                                            <td>{{ $camp->updated_at->format('M d, Y H:i') }}</td>
                                        </tr>
                                    </table>
                                </div>
                                <div class="col-md-6">
                                    <h5>Location Details</h5>
                                    <table class="table table-borderless">
                                        <tr>
                                            <td><strong>Country:</strong></td>
                                            <td>{{ $camp->country ?: 'Not set' }}</td>
                                        </tr>
                                        <tr>
                                            <td><strong>City:</strong></td>
                                            <td>{{ $camp->city ?: 'Not set' }}</td>
                                        </tr>
                                        <tr>
                                            <td><strong>Region:</strong></td>
                                            <td>{{ $camp->region ?: 'Not set' }}</td>
                                        </tr>
                                        <tr>
                                            <td><strong>Coordinates:</strong></td>
                                            <td>
                                                @if($camp->latitude && $camp->longitude)
                                                    {{ $camp->latitude }}, {{ $camp->longitude }}
                                                @else
                                                    Not set
                                                @endif
                                            </td>
                                        </tr>
                                    </table>
                                </div>
                            </div>

                            <hr>

                            <div class="row">
                                <div class="col-12">
                                    <h5>Descriptions</h5>
                                    <div class="row">
                                        <div class="col-md-4">
                                            <h6>Camp Description</h6>
                                            <p class="text-muted">{{ $camp->description_camp ?: 'No description provided' }}</p>
                                        </div>
                                        <div class="col-md-4">
                                            <h6>Area Description</h6>
                                            <p class="text-muted">{{ $camp->description_area ?: 'No description provided' }}</p>
                                        </div>
                                        <div class="col-md-4">
                                            <h6>Fishing Description</h6>
                                            <p class="text-muted">{{ $camp->description_fishing ?: 'No description provided' }}</p>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <hr>

                            <div class="row">
                                <div class="col-12">
                                    <h5>Distances</h5>
                                    <div class="row">
                                        <div class="col-md-3">
                                            <strong>To Store:</strong><br>
                                            <span class="text-muted">{{ $camp->distance_to_store ?: 'Not specified' }}</span>
                                        </div>
                                        <div class="col-md-3">
                                            <strong>To Nearest Town:</strong><br>
                                            <span class="text-muted">{{ $camp->distance_to_nearest_town ?: 'Not specified' }}</span>
                                        </div>
                                        <div class="col-md-3">
                                            <strong>To Airport:</strong><br>
                                            <span class="text-muted">{{ $camp->distance_to_airport ?: 'Not specified' }}</span>
                                        </div>
                                        <div class="col-md-3">
                                            <strong>To Ferry Port:</strong><br>
                                            <span class="text-muted">{{ $camp->distance_to_ferry_port ?: 'Not specified' }}</span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-md-4">
                    <div class="card">
                        <div class="card-header">
                            <h3 class="card-title">Camp Images</h3>
                        </div>
                        <div class="card-body">
                            @if($camp->gallery_images && count($camp->gallery_images) > 0)
                                <div class="row">
                                    @foreach($camp->gallery_images as $index => $image)
                                        <div class="col-6 mb-3">
                                            <img src="{{ asset('storage/' . $image) }}" 
                                                 alt="Camp Image {{ $index + 1 }}" 
                                                 class="img-thumbnail w-100" 
                                                 style="height: 100px; object-fit: cover;">
                                            @if($index === 0)
                                                <small class="text-success">Primary Image</small>
                                            @endif
                                        </div>
                                    @endforeach
                                </div>
                            @else
                                <p class="text-muted text-center">No images uploaded</p>
                            @endif
                        </div>
                    </div>

                    <div class="card mt-3">
                        <div class="card-header">
                            <h3 class="card-title">Camp Facilities</h3>
                        </div>
                        <div class="card-body">
                            @if($camp->facilities && $camp->facilities->count() > 0)
                                <div class="d-flex flex-wrap">
                                    @foreach($camp->facilities as $facility)
                                        <span class="badge badge-primary mr-2 mb-2">{{ $facility->name }}</span>
                                    @endforeach
                                </div>
                            @else
                                <p class="text-muted">No facilities selected</p>
                            @endif
                        </div>
                    </div>

                    <div class="card mt-3">
                        <div class="card-header">
                            <h3 class="card-title">Related Services</h3>
                        </div>
                        <div class="card-body">
                            <h6>Accommodations ({{ $camp->accommodations->count() }})</h6>
                            @if($camp->accommodations->count() > 0)
                                <ul class="list-unstyled">
                                    @foreach($camp->accommodations as $accommodation)
                                        <li><a href="{{ route('admin.accommodations.show', $accommodation->id) }}">{{ $accommodation->title }}</a></li>
                                    @endforeach
                                </ul>
                            @else
                                <p class="text-muted small">No accommodations linked</p>
                            @endif

                            <h6>Rental Boats ({{ $camp->rentalBoats->count() }})</h6>
                            @if($camp->rentalBoats->count() > 0)
                                <ul class="list-unstyled">
                                    @foreach($camp->rentalBoats as $rentalBoat)
                                        <li><a href="{{ route('admin.rental-boats.show', $rentalBoat->id) }}">{{ $rentalBoat->title }}</a></li>
                                    @endforeach
                                </ul>
                            @else
                                <p class="text-muted small">No rental boats linked</p>
                            @endif

                            <h6>Guidings ({{ $camp->guidings->count() }})</h6>
                            @if($camp->guidings->count() > 0)
                                <ul class="list-unstyled">
                                    @foreach($camp->guidings as $guiding)
                                        <li><a href="{{ route('admin.guidings.show', $guiding->id) }}">{{ $guiding->title }}</a></li>
                                    @endforeach
                                </ul>
                            @else
                                <p class="text-muted small">No guidings linked</p>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
