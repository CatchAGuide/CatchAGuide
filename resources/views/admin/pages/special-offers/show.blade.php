@extends('admin.layouts.app')

@section('title', 'View Special Offer')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="page-header">
                <div class="page-title">
                    <h1>Special Offer Details</h1>
                    <p class="text-muted">{{ $specialOffer->title ?: 'Untitled Special Offer' }}</p>
                </div>
                <div class="page-actions">
                    <a href="{{ route('admin.special-offers.index') }}" class="btn btn-secondary">
                        <i class="fas fa-arrow-left"></i> Back to List
                    </a>
                    <a href="{{ route('admin.special-offers.edit', $specialOffer->id) }}" class="btn btn-warning">
                        <i class="fas fa-edit"></i> Edit
                    </a>
                </div>
            </div>

            <div class="row">
                <div class="col-md-8">
                    <div class="card">
                        <div class="card-header">
                            <h3 class="card-title">Special Offer Details</h3>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-6">
                                    <h5>Basic Information</h5>
                                    <table class="table table-borderless">
                                        <tr>
                                            <td><strong>Title:</strong></td>
                                            <td>{{ $specialOffer->title ?: 'Not set' }}</td>
                                        </tr>
                                        <tr>
                                            <td><strong>Location:</strong></td>
                                            <td>{{ $specialOffer->location ?: 'Not set' }}</td>
                                        </tr>
                                        <tr>
                                            <td><strong>Status:</strong></td>
                                            <td>
                                                <span class="badge badge-{{ $specialOffer->status === 'active' ? 'success' : ($specialOffer->status === 'draft' ? 'warning' : 'secondary') }}">
                                                    {{ ucfirst($specialOffer->status) }}
                                                </span>
                                            </td>
                                        </tr>
                                        <tr>
                                            <td><strong>Price Type:</strong></td>
                                            <td>{{ $specialOffer->price_type ?: 'Not set' }}</td>
                                        </tr>
                                        <tr>
                                            <td><strong>Currency:</strong></td>
                                            <td>{{ $specialOffer->currency ?: 'EUR' }}</td>
                                        </tr>
                                        <tr>
                                            <td><strong>Created By:</strong></td>
                                            <td>{{ $specialOffer->user->name ?? 'Unknown' }}</td>
                                        </tr>
                                    </table>
                                </div>
                                <div class="col-md-6">
                                    <h5>Location Details</h5>
                                    <table class="table table-borderless">
                                        <tr>
                                            <td><strong>Country:</strong></td>
                                            <td>{{ $specialOffer->country ?: 'Not set' }}</td>
                                        </tr>
                                        <tr>
                                            <td><strong>City:</strong></td>
                                            <td>{{ $specialOffer->city ?: 'Not set' }}</td>
                                        </tr>
                                        <tr>
                                            <td><strong>Region:</strong></td>
                                            <td>{{ $specialOffer->region ?: 'Not set' }}</td>
                                        </tr>
                                    </table>
                                </div>
                            </div>

                            <hr>

                            <div class="row">
                                <div class="col-12">
                                    <h5>What's Included</h5>
                                    @if($specialOffer->whats_included && is_array($specialOffer->whats_included) && count($specialOffer->whats_included) > 0)
                                        <div class="d-flex flex-wrap">
                                            @foreach($specialOffer->whats_included as $item)
                                                <span class="badge badge-primary mr-2 mb-2">{{ $item }}</span>
                                            @endforeach
                                        </div>
                                    @else
                                        <p class="text-muted">No items specified</p>
                                    @endif
                                </div>
                            </div>

                            <hr>

                            <div class="row">
                                <div class="col-12">
                                    <h5>Pricing</h5>
                                    @if($specialOffer->pricing && is_array($specialOffer->pricing))
                                        <pre>{{ json_encode($specialOffer->pricing, JSON_PRETTY_PRINT) }}</pre>
                                    @else
                                        <p class="text-muted">No pricing information</p>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-md-4">
                    <div class="card">
                        <div class="card-header">
                            <h3 class="card-title">Special Offer Images</h3>
                        </div>
                        <div class="card-body">
                            @if($specialOffer->gallery_images && count($specialOffer->gallery_images) > 0)
                                <div class="row">
                                    @foreach($specialOffer->gallery_images as $index => $image)
                                        <div class="col-6 mb-3">
                                            <img src="{{ asset('storage/' . $image) }}" 
                                                 alt="Special Offer Image {{ $index + 1 }}" 
                                                 class="img-thumbnail w-100" 
                                                 style="height: 100px; object-fit: cover;">
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
                            <h3 class="card-title">Related Services</h3>
                        </div>
                        <div class="card-body">
                            <h6>Accommodations ({{ $specialOffer->accommodations->count() }})</h6>
                            @if($specialOffer->accommodations->count() > 0)
                                <ul class="list-unstyled">
                                    @foreach($specialOffer->accommodations as $accommodation)
                                        <li><a href="{{ route('admin.accommodations.show', $accommodation->id) }}">{{ $accommodation->title }}</a></li>
                                    @endforeach
                                </ul>
                            @else
                                <p class="text-muted small">No accommodations linked</p>
                            @endif

                            <h6>Rental Boats ({{ $specialOffer->rentalBoats->count() }})</h6>
                            @if($specialOffer->rentalBoats->count() > 0)
                                <ul class="list-unstyled">
                                    @foreach($specialOffer->rentalBoats as $rentalBoat)
                                        <li><a href="{{ route('admin.rental-boats.show', $rentalBoat->id) }}">{{ $rentalBoat->title }}</a></li>
                                    @endforeach
                                </ul>
                            @else
                                <p class="text-muted small">No rental boats linked</p>
                            @endif

                            <h6>Guidings ({{ $specialOffer->guidings->count() }})</h6>
                            @if($specialOffer->guidings->count() > 0)
                                <ul class="list-unstyled">
                                    @foreach($specialOffer->guidings as $guiding)
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

