@extends('admin.layouts.app')

@section('title', 'Create Rental Boat')

@section('content')
    <div class="side-app">

        <!-- CONTAINER -->
        <div class="main-container container-fluid">

            <!-- PAGE-HEADER -->
            <div class="page-header">
                <h1 class="page-title">@yield('title')</h1>
                <div>
                    <ol class="breadcrumb">
                        <li class="breadcrumb-item"><a href="#">Administration</a></li>
                        <li class="breadcrumb-item"><a href="{{ route('admin.rental-boats.index') }}">Rental Boats</a></li>
                        <li class="breadcrumb-item active" aria-current="page">@yield('title')</li>
                    </ol>
                </div>
            </div>
            <!-- PAGE-HEADER END -->

            <!-- Row -->
            <div class="row">
                <div class="col-lg-12">
                    <div class="card">
                        <div class="card-header">
                            <h3 class="card-title">Rental Boat Information</h3>
                        </div>
                        <div class="card-body">
                            <x-rental-boat-form 
                                :form-data="$formData" 
                                :rental-boat-types="$rentalBoatTypes"
                                :boat-extras="$boatExtras"
                                :inclusions="$inclusions"
                                :guiding-boat-descriptions="$guiding_boat_descriptions"
                                form-action="{{ route('admin.rental-boats.store') }}"
                                target-redirect="{{ $targetRedirect }}" />
                        </div>
                    </div>
                </div>
            </div>
            <!-- End Row -->
        </div>
        <!-- CONTAINER CLOSED -->
    </div>
@endsection
