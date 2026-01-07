@extends('admin.layouts.app')

@section('title', 'Edit Special Offer')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="page-header">
                <div class="page-title">
                    <h1>Edit Special Offer</h1>
                    <p class="text-muted">Edit special offer: {{ $formData['title'] ?: 'Untitled' }}</p>
                </div>
                <div class="page-actions">
                    <a href="{{ route('admin.special-offers.index') }}" class="btn btn-secondary">
                        <i class="fas fa-arrow-left"></i> Back to List
                    </a>
                    <a href="{{ route('admin.special-offers.show', $formData['id']) }}" class="btn btn-info">
                        <i class="fas fa-eye"></i> View
                    </a>
                </div>
            </div>

            <div class="row">
                <div class="col-12">
                    @include('components.special-offer-form', [
                        'formData' => $formData,
                        'accommodations' => $accommodations ?? [],
                        'rentalBoats' => $rentalBoats ?? [],
                        'guidings' => $guidings ?? [],
                        'formAction' => route('admin.special-offers.update', $formData['id']),
                        'targetRedirect' => $targetRedirect
                    ])
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script src="https://maps.googleapis.com/maps/api/js?key={{ config('services.google.maps_key') }}&libraries=places"></script>
<script src="{{ asset('js/select2.min.js') }}"></script>
@endpush

@push('styles')
<link rel="stylesheet" href="{{ asset('css/select2.min.css') }}">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/cropperjs/1.5.12/cropper.min.css">
@endpush

