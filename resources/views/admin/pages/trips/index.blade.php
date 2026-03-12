@extends('admin.layouts.app')

@section('title', __('trips.title') ?? 'Trips')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="page-header d-flex justify-content-between align-items-center">
                <div class="page-title">
                    <h1>Trips</h1>
                    <p class="text-muted">All-Inclusive Fishing Trips</p>
                </div>
                <div class="page-actions">
                    <a href="{{ route('admin.trips.create') }}" class="btn btn-primary">
                        <i class="fas fa-plus"></i> {{ __('trips.create') }}
                    </a>
                </div>
            </div>

            @if(session('success'))
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    {{ session('success') }}
                    <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
            @endif

            @if(session('error'))
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    {{ session('error') }}
                    <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
            @endif

            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h3 class="card-title">All Trips</h3>
                    <div class="card-tools d-flex">
                        <div class="mr-2">
                            <input type="text" name="filter_location" class="form-control form-control-sm" placeholder="Filter by location">
                        </div>
                        <div class="mr-2">
                            <input type="text" name="filter_species" class="form-control form-control-sm" placeholder="Filter by species">
                        </div>
                        <div class="mr-2">
                            <input type="number" name="filter_price_min" class="form-control form-control-sm" placeholder="Min price">
                        </div>
                        <div class="mr-2">
                            <input type="number" name="filter_price_max" class="form-control form-control-sm" placeholder="Max price">
                        </div>
                        <div class="input-group input-group-sm" style="width: 180px;">
                            <input type="text" name="table_search" class="form-control float-right" placeholder="Search table">
                            <div class="input-group-append">
                                <button type="button" class="btn btn-default">
                                    <i class="fas fa-search"></i>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="card-body table-responsive p-0">
                    @if($trips->count() > 0)
                        <table class="table table-hover text-nowrap" id="tripsTable">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Image</th>
                                    <th>Title</th>
                                    <th>Location</th>
                                    <th>Duration</th>
                                    <th>Price from</th>
                                    <th>Status</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($trips as $trip)
                                    <tr>
                                        <td>{{ $trip->id }}</td>
                                        <td>
                                            @if($trip->thumbnail_path)
                                                <img src="{{ asset('storage/' . $trip->thumbnail_path) }}"
                                                     alt="{{ $trip->title }}"
                                                     class="img-thumbnail"
                                                     style="width: 50px; height: 50px; object-fit: cover;">
                                            @else
                                                <div class="bg-light d-flex align-items-center justify-content-center"
                                                     style="width: 50px; height: 50px;">
                                                    <i class="fas fa-suitcase-rolling text-muted"></i>
                                                </div>
                                            @endif
                                        </td>
                                        <td>
                                            <div class="text-truncate" style="max-width: 220px;" title="{{ $trip->title }}">
                                                {{ $trip->title ?: 'Untitled' }}
                                            </div>
                                        </td>
                                        <td>
                                            <div class="text-truncate" style="max-width: 180px;" title="{{ $trip->location }}">
                                                {{ $trip->location ?: 'No location' }}
                                            </div>
                                        </td>
                                        <td>
                                            @php
                                                $nights = $trip->duration_nights;
                                                $days = $trip->duration_days;
                                            @endphp
                                            @if($nights || $days)
                                                {{ $nights ? $nights . ' nights' : '' }}
                                                @if($nights && $days) /
                                                @endif
                                                {{ $days ? $days . ' days' : '' }}
                                            @else
                                                <span class="text-muted">n/a</span>
                                            @endif
                                        </td>
                                        <td>
                                            @if($trip->price_per_person)
                                                € {{ number_format($trip->price_per_person, 2) }}
                                            @else
                                                <span class="text-muted">n/a</span>
                                            @endif
                                        </td>
                                        <td>
                                            <span class="badge badge-{{ $trip->status === 'active' ? 'success' : ($trip->status === 'draft' ? 'warning' : 'secondary') }}">
                                                {{ ucfirst($trip->status) }}
                                            </span>
                                        </td>
                                        <td>
                                            <div class="btn-group" role="group">
                                                <a href="{{ route('admin.trips.show', $trip->id) }}"
                                                   class="btn btn-sm btn-info"
                                                   title="View">
                                                    <i class="fas fa-eye"></i>
                                                </a>
                                                <a href="{{ route('admin.trips.edit', $trip->id) }}"
                                                   class="btn btn-sm btn-warning"
                                                   title="Edit">
                                                    <i class="fas fa-edit"></i>
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
                                                        <i class="fas fa-trash"></i>
                                                    </button>
                                                </form>
                                            </div>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    @else
                        <div class="text-center py-5">
                            <i class="fas fa-suitcase-rolling fa-3x text-muted mb-3"></i>
                            <h5 class="text-muted">No trips found</h5>
                            <p class="text-muted">Get started by creating your first all-inclusive trip.</p>
                            <a href="{{ route('admin.trips.create') }}" class="btn btn-primary">
                                <i class="fas fa-plus"></i> Create First Trip
                            </a>
                        </div>
                    @endif
                </div>

                @if($trips->hasPages())
                    <div class="card-footer">
                        <div class="d-flex justify-content-center">
                            {{ $trips->links() }}
                        </div>
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    $(document).ready(function () {
        function applyFilters() {
            var searchValue = $('input[name="table_search"]').val().toLowerCase();
            var locationFilter = $('input[name="filter_location"]').val().toLowerCase();
            var speciesFilter = $('input[name="filter_species"]').val().toLowerCase();
            var priceMin = parseFloat($('input[name="filter_price_min"]').val()) || null;
            var priceMax = parseFloat($('input[name="filter_price_max"]').val()) || null;

            $('#tripsTable tbody tr').each(function () {
                var rowText = $(this).text().toLowerCase();
                var locationText = $(this).find('td:nth-child(4)').text().toLowerCase();
                var priceText = $(this).find('td:nth-child(6)').text().replace(/[^\d.,]/g, '').replace(',', '.');
                var price = parseFloat(priceText) || null;

                var matchesSearch = !searchValue || rowText.indexOf(searchValue) > -1;
                var matchesLocation = !locationFilter || locationText.indexOf(locationFilter) > -1;
                var matchesPriceMin = (priceMin === null) || (price !== null && price >= priceMin);
                var matchesPriceMax = (priceMax === null) || (price !== null && price <= priceMax);

                var matchesSpecies = true;
                if (speciesFilter) {
                    matchesSpecies = rowText.indexOf(speciesFilter) > -1;
                }

                $(this).toggle(matchesSearch && matchesLocation && matchesSpecies && matchesPriceMin && matchesPriceMax);
            });
        }

        $('input[name="table_search"], input[name="filter_location"], input[name="filter_species"], input[name="filter_price_min"], input[name="filter_price_max"]').on('keyup change', applyFilters);
    });
</script>
@endpush

