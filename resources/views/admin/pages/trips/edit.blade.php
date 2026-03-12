@extends('admin.layouts.app')

@section('title', __('trips.edit'))

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="page-header">
                <div class="page-title">
                    <h1>{{ __('trips.edit') }}</h1>
                    <p class="text-muted">Edit trip: {{ $formData['title'] ?: 'Untitled' }}</p>
                </div>
                <div class="page-actions">
                    <a href="{{ route('admin.trips.index') }}" class="btn btn-secondary">
                        <i class="fas fa-arrow-left"></i> {{ __('trips.back_to_list') }}
                    </a>
                    <a href="{{ route('admin.trips.show', $formData['id']) }}" class="btn btn-info">
                        <i class="fas fa-eye"></i> {{ __('trips.view') }}
                    </a>
                </div>
            </div>

            <div class="row">
                <div class="col-12">
                    @include('components.trip-form', [
                        'formData'  => $formData,
                        'formAction'=> route('admin.trips.update', $formData['id']),
                        'targetRedirect' => $targetRedirect,
                        'targetSpecies'  => $targetSpecies ?? [],
                        'includedPreset' => $includedPreset ?? [],
                        'excludedPreset' => $excludedPreset ?? [],
                        'availabilityStatusOptions' => $availabilityStatusOptions ?? [],
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

