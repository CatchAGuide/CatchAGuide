@extends('admin.layouts.app')

@section('title', __('camps.edit'))

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="page-header">
                <div class="page-title">
                    <h1>{{ __('camps.edit') }}</h1>
                    <p class="text-muted">Edit camp: {{ $formData['title'] ?: 'Untitled' }}</p>
                </div>
                <div class="page-actions">
                    <a href="{{ route('admin.camps.index') }}" class="btn btn-secondary">
                        <i class="fas fa-arrow-left"></i> {{ __('camps.back_to_list') }}
                    </a>
                    <a href="{{ route('admin.camps.show', $formData['id']) }}" class="btn btn-info">
                        <i class="fas fa-eye"></i> {{ __('camps.view') }}
                    </a>
                </div>
            </div>

            <div class="row">
                <div class="col-12">
                    @include('components.camp-form', [
                        'formData' => $formData,
                        'accommodations' => $accommodations ?? [],
                        'rentalBoats' => $rentalBoats ?? [],
                        'guidings' => $guidings ?? [],
                        'specialOffers' => $specialOffers ?? [],
                        'campFacilities' => $campFacilities ?? [],
                        'targetFish' => $targetFish ?? [],
                        'formAction' => route('admin.camps.update', $formData['id']),
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
