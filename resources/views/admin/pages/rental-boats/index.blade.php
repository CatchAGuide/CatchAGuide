@extends('admin.layouts.app')

@section('title', 'All Rental Boats')

@section('content')
    <div class="side-app">
        <div class="main-container container-fluid">
            <div class="page-header">
                <h1 class="page-title">@yield('title')</h1>
                <div>
                    <ol class="breadcrumb">
                        <li class="breadcrumb-item"><a href="#">Administration</a></li>
                        <li class="breadcrumb-item active" aria-current="page">@yield('title')</li>
                    </ol>
                </div>
            </div>

            <div class="row">
                <div class="col-lg-12">
                    <div class="card">
                        <div class="card-header">
                            <div class="d-flex justify-content-between align-items-center w-100 flex-wrap gap-2">
                                <a href="{{ route('admin.rental-boats.create') }}" class="btn btn-primary">
                                    <i class="fa fa-plus"></i> Add Rental Boat
                                </a>
                                @isset($listingStats)
                                    @include('admin.partials.listing-stats-cards', ['stats' => $listingStats])
                                @endisset
                            </div>
                        </div>
                        <div class="card-body table-responsive">
                            <table id="rental-boats-datatable" class="table align-middle mb-0 admin-listing-datatable">
                                <thead>
                                    <tr>
                                        <th class="wd-10p border-bottom-0">ID</th>
                                        <th class="wd-20p border-bottom-0">Boat</th>
                                        <th class="wd-12p border-bottom-0">Type</th>
                                        <th class="wd-15p border-bottom-0">Owner</th>
                                        <th class="wd-8p border-bottom-0 text-center">Images</th>
                                        <th class="wd-10p border-bottom-0">Status</th>
                                        <th class="wd-12p border-bottom-0">Price</th>
                                        <th class="wd-20p border-bottom-0">Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($rentalBoats as $rentalBoat)
                                        <tr>
                                            <td>{{ $rentalBoat->id }}</td>
                                            <td>
                                                <div class="d-flex flex-column">
                                                    <div class="fw-bold">{{ $rentalBoat->title }}</div>
                                                    <div class="text-info">{{ $rentalBoat->location }}</div>
                                                    @if($rentalBoat->city)
                                                        <small class="text-muted">{{ $rentalBoat->city }}@if($rentalBoat->country), {{ $rentalBoat->country }}@endif</small>
                                                    @endif
                                                </div>
                                            </td>
                                            <td>
                                                <span class="badge bg-info admin-listing-status-badge">{{ ucfirst(str_replace('_', ' ', $rentalBoat->boat_type)) }}</span>
                                            </td>
                                            <td>
                                                <x-admin.owner-cell :user="$rentalBoat->user" />
                                            </td>
                                            <td class="text-center">
                                                <x-admin.listing-image-count
                                                    :thumbnail-path="$rentalBoat->thumbnail_path"
                                                    :gallery="$rentalBoat->gallery_images"
                                                />
                                            </td>
                                            <td>
                                                @if($rentalBoat->status === 'active')
                                                    <span class="badge bg-success admin-listing-status-badge">Active</span>
                                                @else
                                                    <span class="badge bg-danger admin-listing-status-badge">Inactive</span>
                                                @endif
                                            </td>
                                            <td>
                                                @if(isset($rentalBoat->prices['base_price']))
                                                    <div class="fw-bold">€{{ number_format($rentalBoat->prices['base_price'], 2) }}</div>
                                                    <small class="text-muted">{{ ucfirst(str_replace('_', ' ', $rentalBoat->price_type)) }}</small>
                                                @else
                                                    <span class="text-muted">Not set</span>
                                                @endif
                                            </td>
                                            <td class="text-center">
                                                <div class="btn-group">
                                                    @if($rentalBoat->status === 'active')
                                                        <a href="{{ route('admin.rental-boats.change-status', $rentalBoat->id) }}"
                                                           title="Deactivate Rental Boat"
                                                           class="btn btn-sm btn-danger">
                                                            <i class="fa fa-times"></i>
                                                        </a>
                                                    @else
                                                        <a href="{{ route('admin.rental-boats.change-status', $rentalBoat->id) }}"
                                                           title="Activate Rental Boat"
                                                           class="btn btn-sm btn-success">
                                                            <i class="fa fa-check"></i>
                                                        </a>
                                                    @endif
                                                    <a href="{{ route('admin.rental-boats.edit', $rentalBoat) }}"
                                                       class="btn btn-sm btn-secondary"
                                                       title="Edit">
                                                        <i class="fa fa-pen"></i>
                                                    </a>
                                                    <a href="{{ route('admin.rental-boats.show', $rentalBoat) }}"
                                                       class="btn btn-sm btn-primary"
                                                       title="View Details">
                                                        <i class="fa fa-search"></i>
                                                    </a>
                                                    <form action="{{ route('admin.rental-boats.destroy', $rentalBoat) }}"
                                                          method="POST"
                                                          class="d-inline"
                                                          onsubmit="return confirm('Are you sure you want to delete this rental boat?')">
                                                        @csrf
                                                        @method('DELETE')
                                                        <button type="submit"
                                                                class="btn btn-sm btn-danger"
                                                                title="Delete">
                                                            <i class="fa fa-trash"></i>
                                                        </button>
                                                    </form>
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
        </div>
    </div>
@endsection

@section('js_after')
    @include('admin.partials.listing-datatable-script', ['tableId' => 'rental-boats-datatable', 'actionsColumn' => 7])
@endsection
