@extends('admin.layouts.app')

@section('title', __('accommodations.title'))

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="page-header">
                <div class="page-title">
                    <h1>{{ __('accommodations.title') }}</h1>
                    <p class="text-muted">Manage your accommodations</p>
                </div>
                <div class="page-actions">
                    <a href="{{ route('admin.accommodations.create') }}" class="btn btn-primary">
                        <i class="fas fa-plus"></i> {{ __('accommodations.create') }}
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

            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">All Accommodations</h3>
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
                    @if($accommodations->count() > 0)
                        <table class="table table-hover text-nowrap">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Title</th>
                                    <th>Location</th>
                                    <th>Type</th>
                                    <th>Max Occupancy</th>
                                    <th>Price/Night</th>
                                    <th>Status</th>
                                    <th>Owner</th>
                                    <th>Created</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($accommodations as $accommodation)
                                    <tr>
                                        <td>{{ $accommodation->id }}</td>
                                        <td>
                                            <div class="accommodation-title">
                                                <strong>{{ $accommodation->title }}</strong>
                                                @if($accommodation->thumbnail_path)
                                                    <img src="{{ Storage::url($accommodation->thumbnail_path) }}" 
                                                         alt="Thumbnail" class="accommodation-thumbnail">
                                                @endif
                                            </div>
                                        </td>
                                        <td>
                                            <div class="location-info">
                                                <div>{{ $accommodation->city }}, {{ $accommodation->country }}</div>
                                                <small class="text-muted">{{ $accommodation->location }}</small>
                                            </div>
                                        </td>
                                        <td>
                                            <span class="badge badge-info">
                                                {{ __('accommodations.options.accommodation_types.' . $accommodation->accommodation_type) }}
                                            </span>
                                        </td>
                                        <td>{{ $accommodation->max_occupancy ?? 'N/A' }}</td>
                                        <td>
                                            @if($accommodation->price_per_night)
                                                <strong>{{ number_format($accommodation->price_per_night, 2) }} {{ $accommodation->currency }}</strong>
                                            @else
                                                <span class="text-muted">N/A</span>
                                            @endif
                                        </td>
                                        <td>
                                            <span class="badge badge-{{ $accommodation->status === 'active' ? 'success' : 'secondary' }}">
                                                {{ __('accommodations.options.statuses.' . $accommodation->status) }}
                                            </span>
                                        </td>
                                        <td>{{ $accommodation->user->name ?? 'N/A' }}</td>
                                        <td>{{ $accommodation->created_at->format('M d, Y') }}</td>
                                        <td>
                                            <div class="btn-group" role="group">
                                                <a href="{{ route('admin.accommodations.show', $accommodation) }}" 
                                                   class="btn btn-sm btn-info" title="View">
                                                    <i class="fas fa-eye"></i>
                                                </a>
                                                <a href="{{ route('admin.accommodations.edit', $accommodation) }}" 
                                                   class="btn btn-sm btn-warning" title="Edit">
                                                    <i class="fas fa-edit"></i>
                                                </a>
                                                <form action="{{ route('admin.accommodations.destroy', $accommodation) }}" 
                                                      method="POST" class="d-inline" 
                                                      onsubmit="return confirm('{{ __('accommodations.confirm_delete') }}')">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="btn btn-sm btn-danger" title="Delete">
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
                        <div class="empty-state">
                            <div class="empty-state-icon">
                                <i class="fas fa-home"></i>
                            </div>
                            <h3>No Accommodations Found</h3>
                            <p>Get started by creating your first accommodation.</p>
                            <a href="{{ route('admin.accommodations.create') }}" class="btn btn-primary">
                                <i class="fas fa-plus"></i> Create Accommodation
                            </a>
                        </div>
                    @endif
                </div>

                @if($accommodations->hasPages())
                    <div class="card-footer">
                        {{ $accommodations->links() }}
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection

@push('styles')
<style>
.page-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 30px;
    padding-bottom: 20px;
    border-bottom: 1px solid #e9ecef;
}

.page-title h1 {
    margin: 0;
    color: #2c3e50;
    font-size: 2rem;
    font-weight: 600;
}

.page-title .text-muted {
    margin: 5px 0 0 0;
    font-size: 1rem;
}

.page-actions .btn {
    padding: 10px 20px;
    font-weight: 600;
}

.accommodation-title {
    display: flex;
    align-items: center;
    gap: 10px;
}

.accommodation-thumbnail {
    width: 40px;
    height: 30px;
    object-fit: cover;
    border-radius: 4px;
}

.location-info {
    line-height: 1.4;
}

.empty-state {
    text-align: center;
    padding: 60px 20px;
    color: #6c757d;
}

.empty-state-icon {
    font-size: 4rem;
    margin-bottom: 20px;
    color: #dee2e6;
}

.empty-state h3 {
    margin-bottom: 10px;
    color: #495057;
}

.empty-state p {
    margin-bottom: 30px;
    font-size: 1.1rem;
}

.btn-group .btn {
    margin-right: 2px;
}

.btn-group .btn:last-child {
    margin-right: 0;
}

.table th {
    background-color: #f8f9fa;
    font-weight: 600;
    color: #495057;
    border-top: none;
}

.table td {
    vertical-align: middle;
}

.badge {
    font-size: 0.75rem;
    padding: 0.375rem 0.75rem;
}

@media (max-width: 768px) {
    .page-header {
        flex-direction: column;
        align-items: flex-start;
        gap: 15px;
    }
    
    .table-responsive {
        font-size: 0.875rem;
    }
    
    .btn-group {
        flex-direction: column;
        gap: 2px;
    }
    
    .btn-group .btn {
        margin-right: 0;
        margin-bottom: 2px;
    }
}
</style>
@endpush
