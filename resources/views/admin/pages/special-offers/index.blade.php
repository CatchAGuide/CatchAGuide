@extends('admin.layouts.app')

@section('title', 'Special Offers')

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
                                <a href="{{ route('admin.special-offers.create') }}" class="btn btn-primary">
                                    <i class="fa fa-plus"></i> Create Special Offer
                                </a>
                                @isset($listingStats)
                                    @include('admin.partials.listing-stats-cards', ['stats' => $listingStats])
                                @endisset
                            </div>
                        </div>
                        <div class="card-body table-responsive">
                            @if($specialOffers->count() > 0)
                                <table id="special-offers-datatable" class="table align-middle mb-0 admin-listing-datatable">
                                    <thead>
                                        <tr>
                                            <th class="wd-8p border-bottom-0">ID</th>
                                            <th class="wd-22p border-bottom-0">Offer</th>
                                            <th class="wd-8p border-bottom-0 text-center">Images</th>
                                            <th class="wd-10p border-bottom-0">Status</th>
                                            <th class="wd-18p border-bottom-0">Created By</th>
                                            <th class="wd-12p border-bottom-0">Created At</th>
                                            <th class="wd-18p border-bottom-0">Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($specialOffers as $specialOffer)
                                            <tr>
                                                <td>{{ $specialOffer->id }}</td>
                                                <td>
                                                    <div class="d-flex flex-column">
                                                        <div class="fw-bold">{{ $specialOffer->title ?: 'Untitled' }}</div>
                                                        <div class="text-info">{{ $specialOffer->location ?: 'No location' }}</div>
                                                    </div>
                                                </td>
                                                <td class="text-center">
                                                    <x-admin.listing-image-count
                                                        :thumbnail-path="$specialOffer->thumbnail_path"
                                                        :gallery="$specialOffer->gallery_images"
                                                    />
                                                </td>
                                                <td>
                                                    <span class="badge admin-listing-status-badge bg-{{ $specialOffer->status === 'active' ? 'success' : ($specialOffer->status === 'draft' ? 'warning' : 'secondary') }}">
                                                        {{ ucfirst($specialOffer->status) }}
                                                    </span>
                                                </td>
                                                <td>
                                                    <x-admin.owner-cell :user="$specialOffer->user" />
                                                </td>
                                                <td>{{ $specialOffer->created_at->format('M d, Y') }}</td>
                                                <td class="text-center">
                                                    <div class="btn-group">
                                                        <a href="{{ route('admin.special-offers.show', $specialOffer->id) }}"
                                                           class="btn btn-sm btn-primary"
                                                           title="View">
                                                            <i class="fa fa-search"></i>
                                                        </a>
                                                        <a href="{{ route('admin.special-offers.edit', $specialOffer->id) }}"
                                                           class="btn btn-sm btn-secondary"
                                                           title="Edit">
                                                            <i class="fa fa-pen"></i>
                                                        </a>
                                                        <form action="{{ route('admin.special-offers.destroy', $specialOffer->id) }}"
                                                              method="POST"
                                                              class="d-inline"
                                                              onsubmit="return confirm('Are you sure you want to delete this special offer?')">
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
                                    <i class="fa fa-gift d-block"></i>
                                    <h5 class="text-muted">No special offers found</h5>
                                    <p class="mb-3">Get started by creating your first special offer.</p>
                                    <a href="{{ route('admin.special-offers.create') }}" class="btn btn-primary">
                                        <i class="fa fa-plus"></i> Create First Special Offer
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
    @include('admin.partials.listing-datatable-script', ['tableId' => 'special-offers-datatable', 'actionsColumn' => 6])
@endsection
