@extends('admin.layouts.app')

@section('title', __('trips.title') ?? 'Trips')

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

            @if(session('success'))
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    {{ session('success') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            @endif

            @if(session('error'))
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    {{ session('error') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            @endif

            <div class="row">
                <div class="col-lg-12">
                    <div class="card">
                        <div class="card-header">
                            <div class="d-flex justify-content-between align-items-center w-100 flex-wrap gap-2">
                                <a href="{{ route('admin.trips.create') }}" class="btn btn-primary">
                                    <i class="fa fa-plus"></i> {{ __('trips.create') }}
                                </a>
                                @isset($listingStats)
                                    @include('admin.partials.listing-stats-cards', ['stats' => $listingStats])
                                @endisset
                            </div>
                        </div>
                        <div class="card-body table-responsive">
                            @if($trips->count() > 0)
                                <table id="trips-datatable" class="table align-middle mb-0 admin-listing-datatable">
                                    <thead>
                                        <tr>
                                            <th class="wd-8p border-bottom-0">ID</th>
                                            <th class="wd-22p border-bottom-0">Trip</th>
                                            <th class="wd-12p border-bottom-0">Duration</th>
                                            <th class="wd-10p border-bottom-0">Price from</th>
                                            <th class="wd-8p border-bottom-0 text-center">Images</th>
                                            <th class="wd-10p border-bottom-0">Status</th>
                                            <th class="wd-18p border-bottom-0">Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($trips as $trip)
                                            @php
                                                $nights = $trip->duration_nights;
                                                $days = $trip->duration_days;
                                            @endphp
                                            <tr>
                                                <td>{{ $trip->id }}</td>
                                                <td>
                                                    <div class="d-flex flex-column">
                                                        <div class="fw-bold">{{ $trip->title ?: 'Untitled' }}</div>
                                                        <div class="text-info">{{ $trip->location ?: 'No location' }}</div>
                                                    </div>
                                                </td>
                                                <td>
                                                    @if($nights || $days)
                                                        {{ $nights ? $nights . ' nights' : '' }}
                                                        @if($nights && $days) / @endif
                                                        {{ $days ? $days . ' days' : '' }}
                                                    @else
                                                        <span class="text-muted">n/a</span>
                                                    @endif
                                                </td>
                                                <td data-order="{{ $trip->price_per_person ?? 0 }}">
                                                    @if($trip->price_per_person)
                                                        <span class="fw-bold">€{{ number_format($trip->price_per_person, 2) }}</span>
                                                    @else
                                                        <span class="text-muted">n/a</span>
                                                    @endif
                                                </td>
                                                <td class="text-center">
                                                    <x-admin.listing-image-count
                                                        :thumbnail-path="$trip->thumbnail_path"
                                                        :gallery="$trip->gallery_images"
                                                    />
                                                </td>
                                                <td>
                                                    <span class="badge admin-listing-status-badge bg-{{ $trip->status === 'active' ? 'success' : ($trip->status === 'draft' ? 'warning' : 'secondary') }}">
                                                        {{ ucfirst($trip->status) }}
                                                    </span>
                                                </td>
                                                <td class="text-center">
                                                    <div class="btn-group">
                                                        <a href="{{ route('vacations.trips.show', $trip->slug) }}"
                                                           class="btn btn-sm btn-outline-primary"
                                                           title="View product page"
                                                           target="_blank"
                                                           rel="noopener">
                                                            <i class="fa fa-external-link-alt"></i>
                                                        </a>
                                                        <a href="{{ route('admin.trips.edit', $trip->id) }}"
                                                           class="btn btn-sm btn-secondary"
                                                           title="Edit">
                                                            <i class="fa fa-pen"></i>
                                                        </a>
                                                        <form action="{{ route('admin.trips.destroy', $trip->id) }}"
                                                              method="POST"
                                                              class="d-inline"
                                                              onsubmit="return confirm('Are you sure you want to delete this trip?')">
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
                            @else
                                <div class="admin-listing-empty-state">
                                    <i class="fa fa-suitcase-rolling d-block"></i>
                                    <h5 class="text-muted">No trips found</h5>
                                    <p class="mb-3">Get started by creating your first all-inclusive trip.</p>
                                    <a href="{{ route('admin.trips.create') }}" class="btn btn-primary">
                                        <i class="fa fa-plus"></i> Create First Trip
                                    </a>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('js_after')
    @include('admin.partials.listing-datatable-script', ['tableId' => 'trips-datatable', 'actionsColumn' => 6])
@endsection
