@extends('admin.layouts.app')

@section('title', 'Rental Boat Details')

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
                            <div class="card-options">
                                <a href="{{ route('admin.rental-boats.edit', $rentalBoat) }}" class="btn btn-primary btn-sm">
                                    <i class="fa fa-pen"></i> Edit
                                </a>
                            </div>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-6">
                                    <h5>Basic Information</h5>
                                    <table class="table table-borderless">
                                        <tr>
                                            <td><strong>Title:</strong></td>
                                            <td>{{ $rentalBoat->title }}</td>
                                        </tr>
                                        <tr>
                                            <td><strong>Boat Type:</strong></td>
                                            <td><span class="badge bg-info">{{ ucfirst(str_replace('_', ' ', $rentalBoat->boat_type)) }}</span></td>
                                        </tr>
                                        <tr>
                                            <td><strong>Location:</strong></td>
                                            <td>{{ $rentalBoat->location }}</td>
                                        </tr>
                                        <tr>
                                            <td><strong>City:</strong></td>
                                            <td>{{ $rentalBoat->city }}, {{ $rentalBoat->country }}</td>
                                        </tr>
                                        <tr>
                                            <td><strong>Region:</strong></td>
                                            <td>{{ $rentalBoat->region }}</td>
                                        </tr>
                                        <tr>
                                            <td><strong>Status:</strong></td>
                                            <td>
                                                @if($rentalBoat->status === 'active')
                                                    <span class="badge bg-success">Active</span>
                                                @else
                                                    <span class="badge bg-danger">Inactive</span>
                                                @endif
                                            </td>
                                        </tr>
                                    </table>
                                </div>
                                <div class="col-md-6">
                                    <h5>Pricing Information</h5>
                                    <table class="table table-borderless">
                                        <tr>
                                            <td><strong>Price Type:</strong></td>
                                            <td>{{ ucfirst(str_replace('_', ' ', $rentalBoat->price_type)) }}</td>
                                        </tr>
                                        <tr>
                                            <td><strong>Base Price:</strong></td>
                                            <td>
                                                @if(isset($rentalBoat->prices['base_price']))
                                                    €{{ number_format($rentalBoat->prices['base_price'], 2) }}
                                                @else
                                                    Not set
                                                @endif
                                            </td>
                                        </tr>
                                    </table>
                                </div>
                            </div>

                            <div class="row mt-4">
                                <div class="col-12">
                                    <h5>Description</h5>
                                    <p>{{ $rentalBoat->desc_of_boat }}</p>
                                </div>
                            </div>

                            @if($rentalBoat->requirements)
                            <div class="row mt-4">
                                <div class="col-12">
                                    <h5>Requirements</h5>
                                    <p>{{ $rentalBoat->requirements }}</p>
                                </div>
                            </div>
                            @endif

                            @if($rentalBoat->boat_information)
                            <div class="row mt-4">
                                <div class="col-12">
                                    <h5>Boat Information</h5>
                                    <div class="row">
                                        @foreach($rentalBoat->boat_information as $key => $value)
                                        <div class="col-md-4 mb-2">
                                            <strong>{{ ucfirst(str_replace('_', ' ', $key)) }}:</strong>
                                            <span>{{ $value }}</span>
                                        </div>
                                        @endforeach
                                    </div>
                                </div>
                            </div>
                            @endif

                            @if($rentalBoat->boat_extras && count($rentalBoat->boat_extras) > 0)
                            <div class="row mt-4">
                                <div class="col-12">
                                    <h5>Boat Extras</h5>
                                    <div class="d-flex flex-wrap">
                                        @foreach($rentalBoat->boat_extras as $extra)
                                        <span class="badge bg-secondary me-2 mb-2">{{ $extra }}</span>
                                        @endforeach
                                    </div>
                                </div>
                            </div>
                            @endif

                            @if($rentalBoat->inclusions && count($rentalBoat->inclusions) > 0)
                            <div class="row mt-4">
                                <div class="col-12">
                                    <h5>Inclusions</h5>
                                    <div class="d-flex flex-wrap">
                                        @foreach($rentalBoat->inclusions as $inclusion)
                                        <span class="badge bg-success me-2 mb-2">{{ $inclusion }}</span>
                                        @endforeach
                                    </div>
                                </div>
                            </div>
                            @endif

                            <div class="row mt-4">
                                <div class="col-12">
                                    <h5>Owner Information</h5>
                                    <table class="table table-borderless">
                                        <tr>
                                            <td><strong>Name:</strong></td>
                                            <td>
                                                <a href="{{ route('admin.guides.edit', $rentalBoat->user->id) }}" class="text-decoration-none">
                                                    {{ $rentalBoat->user->full_name ?? $rentalBoat->user->name }}
                                                </a>
                                            </td>
                                        </tr>
                                        <tr>
                                            <td><strong>Email:</strong></td>
                                            <td>{{ $rentalBoat->user->email }}</td>
                                        </tr>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <!-- End Row -->
        </div>
        <!-- CONTAINER CLOSED -->
    </div>
@endsection
