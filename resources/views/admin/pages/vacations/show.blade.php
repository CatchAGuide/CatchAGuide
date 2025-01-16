@extends('admin.layouts.app')

@section('title', 'Booking Details')

@section('custom_style')
<style>
    .detail-card {
        background-color: #fff;
        border-radius: 8px;
        box-shadow: 0 2px 4px rgba(0,0,0,.05);
        margin-bottom: 1.5rem;
    }

    .detail-card .card-header {
        background-color: #f8f9fa;
        border-bottom: 1px solid rgba(0,0,0,.125);
        padding: 1rem;
    }

    .detail-card .card-body {
        padding: 1.25rem;
    }

    .detail-label {
        font-weight: 600;
        color: #495057;
        margin-bottom: 0.25rem;
    }

    .detail-value {
        color: #212529;
        margin-bottom: 1rem;
    }

    .status-badge {
        padding: 0.5em 1em;
        border-radius: 4px;
        font-weight: 500;
    }

    .price-summary {
        background-color: #f8f9fa;
        padding: 1rem;
        border-radius: 4px;
    }
</style>
@endsection

@section('content')
<div class="side-app">
    <div class="main-container container-fluid">
        <!-- Page Header -->
        <div class="page-header">
            <h1 class="page-title">Booking Details #{{ $booking->id }}</h1>
            <div>
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="{{ route('admin.vacations.bookings') }}">Bookings</a></li>
                    <li class="breadcrumb-item active" aria-current="page">Booking Details</li>
                </ol>
            </div>
        </div>

        <div class="row">
            <!-- Guest Information -->
            <div class="col-lg-6">
                <div class="detail-card">
                    <div class="card-header">
                        <h4 class="mb-0">Guest Information</h4>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="detail-label">Full Name</div>
                                <div class="detail-value">{{ $booking->title }} {{ $booking->name }} {{ $booking->surname }}</div>
                            </div>
                            <div class="col-md-6">
                                <div class="detail-label">Email</div>
                                <div class="detail-value">{{ $booking->email }}</div>
                            </div>
                            <div class="col-md-6">
                                <div class="detail-label">Phone</div>
                                <div class="detail-value">{{ $booking->phone_country_code }} {{ $booking->phone }}</div>
                            </div>
                            <div class="col-md-6">
                                <div class="detail-label">Address</div>
                                <div class="detail-value">
                                    {{ $booking->street }}<br>
                                    {{ $booking->post_code }} {{ $booking->city }}<br>
                                    {{ $booking->country }}
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Booking Details -->
            <div class="col-lg-6">
                <div class="detail-card">
                    <div class="card-header">
                        <h4 class="mb-0">Booking Information</h4>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="detail-label">Booking Period</div>
                                <div class="detail-value">
                                    {{ $booking->start_date->format('d.m.Y') }} - {{ $booking->end_date->format('d.m.Y') }}
                                    <br>
                                    <small class="text-muted">Duration: {{ $booking->duration }} days</small>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="detail-label">Number of Persons</div>
                                <div class="detail-value">{{ $booking->number_of_persons }}</div>
                            </div>
                            <div class="col-md-6">
                                <div class="detail-label">Booking Type</div>
                                <div class="detail-value">{{ ucfirst($booking->booking_type) }}</div>
                            </div>
                            <div class="col-md-6">
                                <div class="detail-label">Status</div>
                                <div class="detail-value">
                                    <span class="status-badge bg-{{ $booking->status === 'confirmed' ? 'success' : ($booking->status === 'pending' ? 'warning' : 'danger') }}">
                                        {{ ucfirst($booking->status) }}
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Booked Services -->
            <div class="col-12">
                <div class="detail-card">
                    <div class="card-header">
                        <h4 class="mb-0">Booked Services</h4>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            @if($booking->package)
                            <div class="col-md-6 mb-4">
                                <div class="detail-label">Package</div>
                                <div class="detail-value">
                                    <strong>{{ $booking->package->title }}</strong><br>
                                    {{ $booking->package->description }}
                                </div>
                            </div>
                            @endif

                            @if($booking->accommodation)
                            <div class="col-md-6 mb-4">
                                <div class="detail-label">Accommodation</div>
                                <div class="detail-value">
                                    <strong>{{ $booking->accommodation->title }}</strong><br>
                                    {{ $booking->accommodation->description }}
                                </div>
                            </div>
                            @endif

                            @if($booking->boat)
                            <div class="col-md-6 mb-4">
                                <div class="detail-label">Boat</div>
                                <div class="detail-value">
                                    <strong>{{ $booking->boat->title }}</strong><br>
                                    {{ $booking->boat->description }}
                                </div>
                            </div>
                            @endif

                            @if($booking->guiding)
                            <div class="col-md-6 mb-4">
                                <div class="detail-label">Guiding</div>
                                <div class="detail-value">
                                    <strong>{{ $booking->guiding->title }}</strong><br>
                                    {{ $booking->guiding->description }}
                                </div>
                            </div>
                            @endif
                        </div>

                        @if($booking->extra_offers)
                        <div class="mt-3">
                            <div class="detail-label">Extra Services</div>
                            <div class="detail-value">
                                <ul class="list-unstyled">
                                    @foreach($booking->extra_offers as $extra)
                                    <li>• {{ $extra['description'] }} - {{ $extra['price'] }}€</li>
                                    @endforeach
                                </ul>
                            </div>
                        </div>
                        @endif
                    </div>
                </div>
            </div>

            <!-- Additional Information -->
            <div class="col-md-6">
                <div class="detail-card">
                    <div class="card-header">
                        <h4 class="mb-0">Additional Information</h4>
                    </div>
                    <div class="card-body">
                        <div class="detail-label">Comments</div>
                        <div class="detail-value">{{ $booking->comments ?: 'No comments provided' }}</div>

                        <div class="detail-label">Pets</div>
                        <div class="detail-value">{{ $booking->has_pets ? 'Yes' : 'No' }}</div>
                    </div>
                </div>
            </div>

            <!-- Price Summary -->
            <div class="col-md-6">
                <div class="detail-card">
                    <div class="card-header">
                        <h4 class="mb-0">Price Summary</h4>
                    </div>
                    <div class="card-body">
                        <div class="price-summary">
                            <h3 class="mb-0">Total Price: {{ number_format($booking->total_price, 2) }}€</h3>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Action Buttons -->
        <div class="row mt-4">
            <div class="col-12">
                <a href="{{ route('admin.vacations.bookings') }}" class="btn btn-secondary">
                    <i class="fas fa-arrow-left me-2"></i>Back to Bookings
                </a>
            </div>
        </div>
    </div>
</div>
@endsection
