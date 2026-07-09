@extends('admin.layouts.app')

@section('title', __('accommodations.title'))

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

            <div class="row">
                <div class="col-lg-12">
                    <div class="card">
                        <div class="card-header">
                            <div class="d-flex justify-content-between align-items-center w-100 flex-wrap gap-2">
                                <a href="{{ route('admin.accommodations.create') }}" class="btn btn-primary">
                                    <i class="fa fa-plus"></i> {{ __('accommodations.create') }}
                                </a>
                                @isset($listingStats)
                                    @include('admin.partials.listing-stats-cards', ['stats' => $listingStats])
                                @endisset
                            </div>
                        </div>
                        <div class="card-body table-responsive">
                            @if($accommodations->count() > 0)
                                <table id="accommodations-datatable" class="table align-middle mb-0 admin-listing-datatable">
                                    <thead>
                                        <tr>
                                            <th class="wd-8p border-bottom-0">ID</th>
                                            <th class="wd-20p border-bottom-0">Accommodation</th>
                                            <th class="wd-12p border-bottom-0">Type</th>
                                            <th class="wd-15p border-bottom-0">Details</th>
                                            <th class="wd-8p border-bottom-0 text-center">Images</th>
                                            <th class="wd-10p border-bottom-0">Status</th>
                                            <th class="wd-12p border-bottom-0">Created</th>
                                            <th class="wd-18p border-bottom-0">Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($accommodations as $accommodation)
                                            @php
                                                $amenitiesCount = is_array($accommodation->amenities) ? count($accommodation->amenities) : 0;
                                                $detailsCount = is_array($accommodation->accommodation_details) ? count($accommodation->accommodation_details) : 0;
                                            @endphp
                                            <tr>
                                                <td>{{ $accommodation->id }}</td>
                                                <td>
                                                    <div class="d-flex flex-column">
                                                        <div class="fw-bold">{{ $accommodation->title }}</div>
                                                        @if($accommodation->location)
                                                            <div class="text-info">{{ $accommodation->location }}</div>
                                                        @endif
                                                        @if($accommodation->city && $accommodation->country)
                                                            <small class="text-muted">{{ $accommodation->city }}, {{ $accommodation->country }}</small>
                                                        @endif
                                                    </div>
                                                </td>
                                                <td>
                                                    @if($accommodation->accommodationType)
                                                        <span class="badge bg-info admin-listing-status-badge">{{ $accommodation->accommodationType->name }}</span>
                                                    @else
                                                        <span class="text-muted">Not set</span>
                                                    @endif
                                                </td>
                                                <td>
                                                    <div class="admin-listing-meta">
                                                        @if($detailsCount > 0)
                                                            <small><i class="fa fa-info-circle"></i> {{ $detailsCount }} detail(s)</small>
                                                        @endif
                                                        @if($amenitiesCount > 0)
                                                            <small><i class="fa fa-check-circle"></i> {{ $amenitiesCount }} amenity/ies</small>
                                                        @endif
                                                        @if($detailsCount === 0 && $amenitiesCount === 0)
                                                            <span class="text-muted">—</span>
                                                        @endif
                                                    </div>
                                                </td>
                                                <td class="text-center">
                                                    <x-admin.listing-image-count
                                                        :thumbnail-path="$accommodation->thumbnail_path"
                                                        :gallery="$accommodation->gallery_images"
                                                    />
                                                </td>
                                                <td>
                                                    <span class="badge admin-listing-status-badge bg-{{ $accommodation->status === 'active' ? 'success' : ($accommodation->status === 'draft' ? 'warning' : 'secondary') }}">
                                                        {{ ucfirst($accommodation->status) }}
                                                    </span>
                                                </td>
                                                <td>{{ $accommodation->created_at->format('M d, Y') }}</td>
                                                <td class="text-center">
                                                    <div class="btn-group">
                                                        <a href="{{ route('admin.accommodations.show', $accommodation) }}"
                                                           class="btn btn-sm btn-primary"
                                                           title="View">
                                                            <i class="fa fa-search"></i>
                                                        </a>
                                                        <a href="{{ route('admin.accommodations.edit', $accommodation) }}"
                                                           class="btn btn-sm btn-secondary"
                                                           title="Edit">
                                                            <i class="fa fa-pen"></i>
                                                        </a>
                                                        <form action="{{ route('admin.accommodations.destroy', $accommodation) }}"
                                                              method="POST"
                                                              class="d-inline"
                                                              onsubmit="return confirm('{{ __('accommodations.confirm_delete') }}')">
                                                            @csrf
                                                            @method('DELETE')
                                                            <button type="submit" class="btn btn-sm btn-danger" title="Delete">
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
                                    <i class="fa fa-home d-block"></i>
                                    <h5 class="text-muted">No Accommodations Found</h5>
                                    <p class="mb-3">Get started by creating your first accommodation.</p>
                                    <a href="{{ route('admin.accommodations.create') }}" class="btn btn-primary">
                                        <i class="fa fa-plus"></i> Create Accommodation
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
    @include('admin.partials.listing-datatable-script', ['tableId' => 'accommodations-datatable', 'actionsColumn' => 7])
@endsection
