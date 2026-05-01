@extends('admin.layouts.app')

@section('title', 'Strategy • Content coverage')

@section('content')
<div class="container-fluid px-4 pt-4">
    <div class="d-flex justify-content-between align-items-start mb-3">
        <div>
            <h1 class="h3 mb-0">Content coverage</h1>
            <div class="text-muted small">Listings missing key content (thumbnail, gallery, description/details, pricing, country)</div>
        </div>
        <div class="d-flex gap-2">
            <a href="{{ route('admin.strategy.index') }}" class="btn btn-light btn-sm">Strategy</a>
            <a href="{{ route('admin.listings.consolidated.index') }}" class="btn btn-outline-primary btn-sm">All listings</a>
        </div>
    </div>

    <div class="card shadow-sm mb-3">
        <div class="card-body">
            <form method="GET" class="row g-2 align-items-end">
                <div class="col-12 col-lg-6">
                    <label class="form-label mb-1 small">Countries (comma-separated, optional)</label>
                    <input name="countries" class="form-control form-control-sm" placeholder="DE, NL, SE" value="{{ $countryInput }}">
                </div>
                <div class="col-6 col-lg-2">
                    <label class="form-label mb-1 small">Min gallery images</label>
                    <input name="min_gallery" type="number" min="0" class="form-control form-control-sm" value="{{ $minGallery }}">
                </div>
                <div class="col-6 col-lg-2">
                    <div class="form-check mt-4">
                        <input class="form-check-input" type="checkbox" name="only_missing" value="1" id="only_missing" {{ $onlyMissing ? 'checked' : '' }}>
                        <label class="form-check-label small" for="only_missing">Only show missing</label>
                    </div>
                </div>
                <div class="col-12 col-lg-2 d-flex gap-2">
                    <button class="btn btn-primary btn-sm">Apply</button>
                    <a href="{{ route('admin.strategy.content-coverage') }}" class="btn btn-light btn-sm">Reset</a>
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
                            <th>Type</th>
                            <th class="text-end">ID</th>
                            <th>Title</th>
                            <th>Country</th>
                            <th>Status</th>
                            <th class="text-end">Gallery</th>
                            <th class="text-end">Price (low)</th>
                            <th>Missing</th>
                            <th class="text-end">Fix</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($items as $i)
                            <tr>
                                <td class="text-uppercase small">{{ $i['type'] }}</td>
                                <td class="text-end">{{ $i['id'] }}</td>
                                <td class="fw-semibold">{{ $i['title'] }}</td>
                                <td>{{ $i['country'] ?: '—' }}</td>
                                <td>{{ $i['status'] ?: '—' }}</td>
                                <td class="text-end">{{ $i['gallery_count'] }}</td>
                                <td class="text-end">
                                    {{ $i['price_low'] === null ? '—' : ('€' . number_format($i['price_low'], 2)) }}
                                </td>
                                <td>
                                    @if(($i['missing_count'] ?? 0) > 0)
                                        @foreach($i['missing'] as $m)
                                            <span class="badge bg-warning text-dark me-1">{{ $m }}</span>
                                        @endforeach
                                    @else
                                        <span class="badge bg-success">OK</span>
                                    @endif
                                </td>
                                <td class="text-end">
                                    <a href="{{ $i['edit_url'] }}" class="btn btn-outline-primary btn-sm">Edit</a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="9" class="text-center text-muted py-4">No items.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection

