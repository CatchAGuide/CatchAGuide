@extends('admin.layouts.app')

@section('title', 'Special Offers')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="page-header">
                <div class="page-title">
                    <h1>Special Offers</h1>
                    <p class="text-muted">Manage your special offers</p>
                </div>
                <div class="page-actions">
                    <a href="{{ route('admin.special-offers.create') }}" class="btn btn-primary">
                        <i class="fas fa-plus"></i> Create Special Offer
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
                <div class="card-header">
                    <h3 class="card-title">All Special Offers</h3>
                    <div class="card-tools">
                        <div class="input-group input-group-sm" style="width: 150px;">
                            <input type="text" name="table_search" class="form-control float-right" placeholder="Search">
                            <div class="input-group-append">
                                <button type="submit" class="btn btn-default">
                                    <i class="fas fa-search"></i>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="card-body table-responsive p-0">
                    @if($specialOffers->count() > 0)
                        <table class="table table-hover text-nowrap">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Image</th>
                                    <th>Title</th>
                                    <th>Location</th>
                                    <th>Status</th>
                                    <th>Created By</th>
                                    <th>Created At</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($specialOffers as $specialOffer)
                                    <tr>
                                        <td>{{ $specialOffer->id }}</td>
                                        <td>
                                            @if($specialOffer->thumbnail_path)
                                                <img src="{{ asset('storage/' . $specialOffer->thumbnail_path) }}" 
                                                     alt="{{ $specialOffer->title }}" 
                                                     class="img-thumbnail" 
                                                     style="width: 50px; height: 50px; object-fit: cover;">
                                            @else
                                                <div class="bg-light d-flex align-items-center justify-content-center" 
                                                     style="width: 50px; height: 50px;">
                                                    <i class="fas fa-image text-muted"></i>
                                                </div>
                                            @endif
                                        </td>
                                        <td>
                                            <div class="text-truncate" style="max-width: 200px;" title="{{ $specialOffer->title }}">
                                                {{ $specialOffer->title ?: 'Untitled' }}
                                            </div>
                                        </td>
                                        <td>
                                            <div class="text-truncate" style="max-width: 150px;" title="{{ $specialOffer->location }}">
                                                {{ $specialOffer->location ?: 'No location' }}
                                            </div>
                                        </td>
                                        <td>
                                            <span class="badge badge-{{ $specialOffer->status === 'active' ? 'success' : ($specialOffer->status === 'draft' ? 'warning' : 'secondary') }}">
                                                {{ ucfirst($specialOffer->status) }}
                                            </span>
                                        </td>
                                        <td>{{ $specialOffer->user->name ?? 'Unknown' }}</td>
                                        <td>{{ $specialOffer->created_at->format('M d, Y') }}</td>
                                        <td>
                                            <div class="btn-group" role="group">
                                                <a href="{{ route('admin.special-offers.show', $specialOffer->id) }}" 
                                                   class="btn btn-sm btn-info" 
                                                   title="View">
                                                    <i class="fas fa-eye"></i>
                                                </a>
                                                <a href="{{ route('admin.special-offers.edit', $specialOffer->id) }}" 
                                                   class="btn btn-sm btn-warning" 
                                                   title="Edit">
                                                    <i class="fas fa-edit"></i>
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
                            <i class="fas fa-gift fa-3x text-muted mb-3"></i>
                            <h5 class="text-muted">No special offers found</h5>
                            <p class="text-muted">Get started by creating your first special offer.</p>
                            <a href="{{ route('admin.special-offers.create') }}" class="btn btn-primary">
                                <i class="fas fa-plus"></i> Create First Special Offer
                            </a>
                        </div>
                    @endif
                </div>

                @if($specialOffers->hasPages())
                    <div class="card-footer">
                        <div class="d-flex justify-content-center">
                            {{ $specialOffers->links() }}
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
$(document).ready(function() {
    // Search functionality
    $('input[name="table_search"]').on('keyup', function() {
        var value = $(this).val().toLowerCase();
        $('table tbody tr').filter(function() {
            $(this).toggle($(this).text().toLowerCase().indexOf(value) > -1);
        });
    });
});
</script>
@endpush

