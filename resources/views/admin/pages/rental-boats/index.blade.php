@extends('admin.layouts.app')

@section('title', 'All Rental Boats')

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
                            <a href="{{ route('admin.rental-boats.create') }}" class="btn btn-primary">
                                <i class="fa fa-plus"></i> Add Rental Boat
                            </a>
                        </div>
                        <div class="card-body table-responsive">
                            <table id="rental-boats-datatable" class="table">
                                <thead>
                                    <tr>
                                        <th class="wd-15p border-bottom-0">ID</th>
                                        <th class="wd-20p border-bottom-0">Boat Title</th>
                                        <th class="wd-15p border-bottom-0">Boat Type</th>
                                        <th class="wd-15p border-bottom-0">Location</th>
                                        <th class="wd-15p border-bottom-0">Owner</th>
                                        <th class="wd-10p border-bottom-0">Status</th>
                                        <th class="wd-15p border-bottom-0">Price</th>
                                        <th class="wd-25p border-bottom-0">Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($rentalBoats as $rentalBoat)
                                    <tr>
                                        <td>{{ $rentalBoat->id }}</td>
                                        <td>
                                            <div class="d-flex flex-column">
                                                <div class="fw-bold">{{ $rentalBoat->title }}</div>
                                                <div class="text-muted small">{{ Str::limit($rentalBoat->desc_of_boat, 50) }}</div>
                                            </div>
                                        </td>
                                        <td>
                                            <span class="badge bg-info">{{ ucfirst(str_replace('_', ' ', $rentalBoat->boat_type)) }}</span>
                                        </td>
                                        <td>
                                            <div class="d-flex flex-column">
                                                <div class="fw-bold">{{ $rentalBoat->location }}</div>
                                                @if($rentalBoat->city)
                                                    <div class="text-muted small">{{ $rentalBoat->city }}, {{ $rentalBoat->country }}</div>
                                                @endif
                                            </div>
                                        </td>
                                        <td>
                                            <a href="{{ route('admin.guides.edit', $rentalBoat->user->id) }}" class="text-decoration-none">
                                                {{ $rentalBoat->user->full_name ?? $rentalBoat->user->name }}
                                            </a>
                                        </td>
                                        <td>
                                            @if($rentalBoat->status === 'active')
                                                <span class="badge bg-success">Active</span>
                                            @else
                                                <span class="badge bg-danger">Inactive</span>
                                            @endif
                                        </td>
                                        <td>
                                            @if(isset($rentalBoat->prices['base_price']))
                                                <div class="fw-bold">€{{ number_format($rentalBoat->prices['base_price'], 2) }}</div>
                                                <div class="text-muted small">{{ ucfirst(str_replace('_', ' ', $rentalBoat->price_type)) }}</div>
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
                                                    <i class="fa fa-eye"></i>
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
            <!-- End Row -->
        </div>
        <!-- CONTAINER CLOSED -->
    </div>
@endsection

@section('js_after')
<script>
    $(function(e) {
        $('#rental-boats-datatable').DataTable({
            order: [[0, 'desc']],
            columnDefs: [
                { orderable: false, targets: [7] } // Disable ordering on Actions column
            ]
        });
    });
</script>
@endsection
