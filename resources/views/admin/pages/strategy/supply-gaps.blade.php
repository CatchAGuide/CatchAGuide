@extends('admin.layouts.app')

@section('title', 'Strategy • Supply gaps')

@section('content')
<div class="container-fluid px-4 pt-4">
    <div class="d-flex justify-content-between align-items-start mb-3">
        <div>
            <h1 class="h3 mb-0">Supply gaps</h1>
            <div class="text-muted small">Active supply by country (guidings, trips, camps, accommodations)</div>
        </div>
        <div class="d-flex gap-2">
            <a href="{{ route('admin.strategy.index') }}" class="btn btn-light btn-sm">Strategy</a>
            <a href="{{ route('admin.listings.consolidated.index') }}" class="btn btn-outline-primary btn-sm">All listings</a>
        </div>
    </div>

    <div class="card shadow-sm mb-3">
        <div class="card-body">
            <form method="GET" class="row g-2 align-items-end">
                <div class="col-12 col-lg-8">
                    <label class="form-label mb-1 small">Countries (comma-separated, optional)</label>
                    <input name="countries" class="form-control form-control-sm" placeholder="DE, NL, SE" value="{{ $countryInput }}">
                </div>
                <div class="col-12 col-lg-4 d-flex gap-2">
                    <button class="btn btn-primary btn-sm">Apply</button>
                    <a href="{{ route('admin.strategy.supply-gaps') }}" class="btn btn-light btn-sm">Reset</a>
                </div>
            </form>
        </div>
    </div>

    <div class="card shadow-sm">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover table-striped mb-0 align-middle">
                    <thead class="table-light">
                        <tr>
                            <th>Country</th>
                            <th class="text-end">Guidings (active)</th>
                            <th class="text-end">Trips (active)</th>
                            <th class="text-end">Camps (active)</th>
                            <th class="text-end">Accommodations (active)</th>
                            <th class="text-end">Total (active)</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($rows as $r)
                            <tr>
                                <td class="fw-semibold">{{ $r['country'] }}</td>
                                <td class="text-end">{{ $r['guidings_active'] }}</td>
                                <td class="text-end">{{ $r['trips_active'] }}</td>
                                <td class="text-end">{{ $r['camps_active'] }}</td>
                                <td class="text-end">{{ $r['accommodations_active'] }}</td>
                                <td class="text-end fw-semibold">{{ $r['total_active'] }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="text-center text-muted py-4">No data.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection

