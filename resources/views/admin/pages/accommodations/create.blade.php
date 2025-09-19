@extends('admin.layouts.app')

@section('title', __('accommodations.create'))

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="page-header">
                <div class="page-title">
                    <h1>{{ __('accommodations.create') }}</h1>
                    <p class="text-muted">Add a new accommodation to your listings</p>
                </div>
                <div class="page-actions">
                    <a href="{{ route('admin.accommodations.index') }}" class="btn btn-secondary">
                        <i class="fas fa-arrow-left"></i> Back to List
                    </a>
                </div>
            </div>

            @if($errors->any())
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <h5>Please fix the following errors:</h5>
                    <ul class="mb-0">
                        @foreach($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                    <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
            @endif

            <div class="row">
                <div class="col-12">
                    <div class="card">
                        <div class="card-header">
                            <h3 class="card-title">Accommodation Details</h3>
                        </div>
                        <div class="card-body">
                            @include('components.accommodation-form', ['accommodation' => new \App\Models\Accommodation()])
                        </div>
                    </div>
                </div>
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

.card {
    box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
    border: none;
}

.card-header {
    background: #f8f9fa;
    border-bottom: 1px solid #e9ecef;
    padding: 20px;
}

.card-header .card-title {
    margin: 0;
    color: #2c3e50;
    font-size: 1.3rem;
    font-weight: 600;
}

.card-body {
    padding: 30px;
}

@media (max-width: 768px) {
    .page-header {
        flex-direction: column;
        align-items: flex-start;
        gap: 15px;
    }
    
    .card-body {
        padding: 20px 15px;
    }
}
</style>
@endpush

@stack('js_push')
