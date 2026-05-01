@extends('admin.layouts.app')

@section('title', 'Strategy')

@section('content')
<div class="container-fluid px-4 pt-4">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <div>
            <h1 class="h3 mb-0">Strategy</h1>
            <div class="text-muted small">Internal strategy tools (DB-driven, no external integrations required)</div>
        </div>
    </div>

    <div class="row g-3 mb-3">
        <div class="col-12 col-md-6 col-xl-3">
            <div class="card h-100 shadow-sm">
                <div class="card-body">
                    <div class="text-muted small">Active supply</div>
                    <div class="h2 mb-1">{{ ($guidingsActive ?? 0) + ($tripsActive ?? 0) + ($campsActive ?? 0) + ($accommodationsActive ?? 0) }}</div>
                    <div class="small text-muted">
                        Guidings: <b>{{ $guidingsActive ?? 0 }}</b> · Trips: <b>{{ $tripsActive ?? 0 }}</b><br>
                        Camps: <b>{{ $campsActive ?? 0 }}</b> · Accommodations: <b>{{ $accommodationsActive ?? 0 }}</b>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-12 col-md-6 col-xl-3">
            <div class="card h-100 shadow-sm">
                <div class="card-body">
                    <div class="text-muted small">Content tasks (sum of missing)</div>
                    <div class="h2 mb-2">{{ $contentTasks ?? 0 }}</div>
                    <a href="{{ route('admin.strategy.content-coverage') }}" class="btn btn-outline-primary btn-sm">Open content coverage</a>
                </div>
            </div>
        </div>
        <div class="col-12 col-md-6 col-xl-3">
            <div class="card h-100 shadow-sm">
                <div class="card-body">
                    <div class="text-muted small">Missing thumbnails (active)</div>
                    <div class="h2 mb-1">
                        {{ ($missing['guidings']['thumbnail'] ?? 0) + ($missing['trips']['thumbnail'] ?? 0) + ($missing['camps']['thumbnail'] ?? 0) + ($missing['accommodations']['thumbnail'] ?? 0) }}
                    </div>
                    <div class="small text-muted">
                        Guidings: <b>{{ $missing['guidings']['thumbnail'] ?? 0 }}</b> · Trips: <b>{{ $missing['trips']['thumbnail'] ?? 0 }}</b><br>
                        Camps: <b>{{ $missing['camps']['thumbnail'] ?? 0 }}</b> · Accom: <b>{{ $missing['accommodations']['thumbnail'] ?? 0 }}</b>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-12 col-md-6 col-xl-3">
            <div class="card h-100 shadow-sm">
                <div class="card-body">
                    <div class="text-muted small">Missing gallery (empty JSON) (active)</div>
                    <div class="h2 mb-1">
                        {{ ($missing['trips']['gallery_empty'] ?? 0) + ($missing['camps']['gallery_empty'] ?? 0) + ($missing['accommodations']['gallery_empty'] ?? 0) }}
                    </div>
                    <div class="small text-muted">
                        Trips: <b>{{ $missing['trips']['gallery_empty'] ?? 0 }}</b> · Camps: <b>{{ $missing['camps']['gallery_empty'] ?? 0 }}</b> · Accom: <b>{{ $missing['accommodations']['gallery_empty'] ?? 0 }}</b>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row g-3">
        <div class="col-12 col-lg-4">
            <div class="card h-100 shadow-sm">
                <div class="card-body">
                    <h5 class="card-title mb-2">Supply gaps</h5>
                    <div class="text-muted small mb-3">
                        Country-level supply coverage across guidings, trips, camps, and accommodations.
                    </div>
                    <a href="{{ route('admin.strategy.supply-gaps') }}" class="btn btn-primary btn-sm">Open supply gaps</a>
                </div>
            </div>
        </div>
        <div class="col-12 col-lg-4">
            <div class="card h-100 shadow-sm">
                <div class="card-body">
                    <h5 class="card-title mb-2">Content coverage</h5>
                    <div class="text-muted small mb-3">
                        Finds listings missing thumbnails, galleries, descriptions, pricing, or country metadata.
                    </div>
                    <a href="{{ route('admin.strategy.content-coverage') }}" class="btn btn-primary btn-sm">Open content coverage</a>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

