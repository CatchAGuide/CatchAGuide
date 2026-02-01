@extends('admin.layouts.app')

@section('title', 'Guide Analytics')

@section('content')
<div class="side-app">
    <div class="main-container container-fluid">

        <!-- PAGE-HEADER -->
        <div class="page-header d-flex flex-wrap justify-content-between align-items-center gap-2">
            <div>
                <h1 class="page-title mb-1">Guide Analytics</h1>
                <p class="text-muted small mb-0">Monitor guide activity, guidings status, and deactivation trends</p>
            </div>
            <div>
                <ol class="breadcrumb mb-0">
                    <li class="breadcrumb-item"><a href="{{ route('admin.index') }}">Dashboard</a></li>
                    <li class="breadcrumb-item active" aria-current="page">Guide Analytics</li>
                </ol>
            </div>
        </div>
        <!-- PAGE-HEADER END -->

        <!-- KPI Summary Cards -->
        <div class="row g-3 mb-4">
            <div class="col-12 col-sm-6 col-xl-3">
                <div class="card border-0 shadow-sm h-100 overflow-hidden">
                    <div class="card-body d-flex align-items-center">
                        <div class="flex-shrink-0 bg-warning bg-opacity-15 rounded-3 p-3 me-3">
                            <i class="fe fe-alert-circle fa-2x text-warning"></i>
                        </div>
                        <div class="flex-grow-1 min-w-0">
                            <h6 class="text-uppercase text-muted small mb-1">Guides Needing Attention</h6>
                            <h3 class="mb-0 fw-bold text-warning">{{ $guidesWithoutActiveOrDraftCount }}</h3>
                            <small class="text-muted">No active/draft guidings</small>
                        </div>
                    </div>
                    <div class="card-footer bg-transparent border-0 py-2">
                        <a href="#guidesNeedingAttention" class="small text-warning text-decoration-none">
                            View list <i class="fe fe-arrow-right ms-1"></i>
                        </a>
                    </div>
                </div>
            </div>
            <div class="col-12 col-sm-6 col-xl-3">
                <div class="card border-0 shadow-sm h-100 overflow-hidden">
                    <div class="card-body d-flex align-items-center">
                        <div class="flex-shrink-0 bg-success bg-opacity-15 rounded-3 p-3 me-3">
                            <i class="fe fe-check-circle fa-2x text-success"></i>
                        </div>
                        <div class="flex-grow-1 min-w-0">
                            <h6 class="text-uppercase text-muted small mb-1">Guides with Active Tours</h6>
                            <h3 class="mb-0 fw-bold text-success">{{ $guidesWithActiveTours }}</h3>
                            <small class="text-muted">of {{ $totalGuides }} total guides</small>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-12 col-sm-6 col-xl-3">
                <div class="card border-0 shadow-sm h-100 overflow-hidden">
                    <div class="card-body d-flex align-items-center">
                        <div class="flex-shrink-0 bg-primary bg-opacity-15 rounded-3 p-3 me-3">
                            <i class="fe fe-users fa-2x text-primary"></i>
                        </div>
                        <div class="flex-grow-1 min-w-0">
                            <h6 class="text-uppercase text-muted small mb-1">Total Guides</h6>
                            <h3 class="mb-0 fw-bold text-primary">{{ $totalGuides }}</h3>
                            <small class="text-muted">Registered on platform</small>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-12 col-sm-6 col-xl-3">
                <div class="card border-0 shadow-sm h-100 overflow-hidden">
                    <div class="card-body d-flex align-items-center">
                        <div class="flex-shrink-0 bg-danger bg-opacity-15 rounded-3 p-3 me-3">
                            <i class="fe fe-x-circle fa-2x text-danger"></i>
                        </div>
                        <div class="flex-grow-1 min-w-0">
                            <h6 class="text-uppercase text-muted small mb-1">Deactivated Guidings</h6>
                            <h3 class="mb-0 fw-bold text-danger">{{ $totalDeactivatedGuidings }}</h3>
                            <small class="text-muted">Total across all guides</small>
                        </div>
                    </div>
                    <div class="card-footer bg-transparent border-0 py-2">
                        <a href="#deactivationTrends" class="small text-danger text-decoration-none">
                            View trend <i class="fe fe-arrow-right ms-1"></i>
                        </a>
                    </div>
                </div>
            </div>
        </div>

        <!-- Tabbed Content -->
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white border-bottom px-4 py-3">
                <ul class="nav nav-tabs card-header-tabs nav-fill" role="tablist">
                    <li class="nav-item">
                        <a class="nav-link active" data-bs-toggle="tab" href="#guidingsOverview" role="tab">
                            <i class="fa fa-bar-chart me-2"></i>Guidings Overview
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" data-bs-toggle="tab" href="#guidesNeedingAttention" role="tab">
                            <i class="fa fa-exclamation-circle me-2"></i>Guides Needing Attention
                            @if($guidesWithoutActiveOrDraftCount > 0)
                                <span class="badge bg-warning ms-2">{{ $guidesWithoutActiveOrDraftCount }}</span>
                            @endif
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" data-bs-toggle="tab" href="#deactivationTrends" role="tab">
                            <i class="fa fa-trending-down me-2"></i>Deactivation Trends
                        </a>
                    </li>
                </ul>
            </div>
            <div class="card-body p-4">
                <div class="tab-content">

                    <!-- Tab 1: Guidings Overview (default active) -->
                    <div class="tab-pane fade show active" id="guidingsOverview" role="tabpanel">
                        <div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4">
                            <p class="text-muted mb-0">Breakdown of guidings per guide: total, active, draft, and deactivated counts with last deactivation date.</p>
                            <div class="input-group input-group-sm" style="max-width: 280px;">
                                <span class="input-group-text bg-light"><i class="fa fa-search text-muted"></i></span>
                                <input type="text" id="guidingsSearch" class="form-control" placeholder="Search guides by name or email...">
                            </div>
                        </div>
                        <div class="table-responsive">
                            <table class="table table-hover align-middle" id="guidingsOverviewTable">
                                <thead class="table-light">
                                    <tr>
                                        <th>Guide</th>
                                        <th class="text-center">Total</th>
                                        <th class="text-center">Active</th>
                                        <th class="text-center">Draft</th>
                                        <th class="text-center">Deactivated</th>
                                        <th>Last Deactivated</th>
                                        <th class="text-end">Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($guidesWithGuidingsStats as $stat)
                                        <tr>
                                            <td>
                                                <div class="d-flex align-items-center gap-2">
                                                    <div class="rounded-circle bg-light d-flex align-items-center justify-content-center flex-shrink-0" style="width: 36px; height: 36px;">
                                                        <i class="fa fa-user text-muted small"></i>
                                                    </div>
                                                    <div>
                                                        <strong>{{ $stat['guide']->full_name }}</strong>
                                                        <br><small class="text-muted">{{ $stat['guide']->email }}</small>
                                                    </div>
                                                </div>
                                            </td>
                                            <td class="text-center"><span class="badge bg-primary rounded-pill">{{ $stat['total_guidings'] }}</span></td>
                                            <td class="text-center"><span class="badge bg-success rounded-pill">{{ $stat['active_count'] }}</span></td>
                                            <td class="text-center"><span class="badge bg-info rounded-pill">{{ $stat['draft_count'] }}</span></td>
                                            <td class="text-center">
                                                @if($stat['deactivated_count'] > 0)
                                                    <span class="badge bg-danger rounded-pill">{{ $stat['deactivated_count'] }}</span>
                                                @else
                                                    <span class="text-muted">—</span>
                                                @endif
                                            </td>
                                            <td>
                                                @if($stat['last_deactivation_date'])
                                                    <span class="text-muted">{{ $stat['last_deactivation_date']->format('M d, Y') }}</span>
                                                @else
                                                    <span class="text-muted">—</span>
                                                @endif
                                            </td>
                                            <td class="text-end">
                                                <div class="btn-group btn-group-sm">
                                                    <a href="{{ route('admin.guides.edit', $stat['guide']) }}" class="btn btn-primary btn-sm" title="Edit Guide"><i class="fas fa-pen"></i></a>
                                                    <a href="{{ route('admin.guides.show', $stat['guide']) }}" class="btn btn-secondary btn-sm" title="Profile"><i class="fas fa-user"></i></a>
                                                    @php
                                                        $guidingsData = $stat['all_guidings']->map(function($g) {
                                                            return ['id' => $g->id, 'title' => $g->title, 'status' => $g->status, 'updated_at' => $g->updated_at ? $g->updated_at->format('M d, Y') : null];
                                                        })->values();
                                                        $guidingsJsonB64 = base64_encode(json_encode($guidingsData));
                                                    @endphp
                                                    <button type="button" class="btn btn-info btn-sm text-white guidings-modal-btn" title="Guidings"
                                                            data-guide-name="{{ $stat['guide']->full_name }}"
                                                            data-guidings="{{ $guidingsJsonB64 }}"
                                                            data-bs-toggle="modal" data-bs-target="#guidingsModal">
                                                        <i class="fas fa-briefcase"></i>
                                                    </button>
                                                </div>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <!-- Tab 2: Guides Needing Attention -->
                    <div class="tab-pane fade" id="guidesNeedingAttention" role="tabpanel">
                        <div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4">
                            <div>
                                <p class="text-muted mb-2">Guides who have no active (1) or draft (2) guidings.</p>
                                <div class="btn-group btn-group-sm" role="group">
                                    <button type="button" class="btn btn-outline-primary active" data-filter="all">All</button>
                                    <button type="button" class="btn btn-outline-secondary" data-filter="none">No Guidings</button>
                                    <button type="button" class="btn btn-outline-secondary" data-filter="deactivated">All Deactivated</button>
                                </div>
                            </div>
                            <div class="input-group input-group-sm" style="max-width: 280px;">
                                <span class="input-group-text bg-light"><i class="fa fa-search text-muted"></i></span>
                                <input type="text" id="guidesNeedingAttentionSearch" class="form-control" placeholder="Search by name, email, phone, location...">
                            </div>
                        </div>

                        @if($guidesWithoutActiveOrDraftGuidings->isNotEmpty())
                            <div class="table-responsive">
                                <table class="table table-hover align-middle table-bordered" id="guidesNeedingAttentionTable">
                                    <thead class="table-light">
                                        <tr>
                                            <th style="width: 50px;">Image</th>
                                            <th>Name</th>
                                            <th>Email</th>
                                            <th>Phone</th>
                                            <th>Location</th>
                                            <th class="text-center">Total</th>
                                            <th class="text-center">Deactivated</th>
                                            <th class="text-end">Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($guidesWithoutActiveOrDraftGuidings as $guide)
                                            <tr data-filter-status="{{ $guide->guidings_count == 0 ? 'none' : 'deactivated' }}">
                                                <td>
                                                    @php $imgSrc = $guide->profil_image ? asset('images/' . $guide->profil_image) : asset('images/placeholder_guide.jpg'); @endphp
                                                    <img src="{{ $imgSrc }}" alt="{{ $guide->full_name }}" class="rounded-circle object-fit-cover" style="width: 40px; height: 40px;" onerror="this.onerror=null; this.src='{{ asset('images/placeholder_guide.jpg') }}';">
                                                </td>
                                                <td><strong>{{ $guide->full_name }}</strong></td>
                                                <td><a href="mailto:{{ $guide->email }}" class="text-decoration-none">{{ $guide->email }}</a></td>
                                                <td>{{ $guide->information?->phone ?? $guide->phone ?? '—' }}</td>
                                                <td>
                                                    @if($guide->information && ($guide->information->city || $guide->information->country))
                                                        {{ implode(', ', array_filter([$guide->information->city, $guide->information->country])) }}
                                                    @else
                                                        —
                                                    @endif
                                                </td>
                                                <td class="text-center">
                                                    @if($guide->guidings_count == 0)
                                                        <span class="badge bg-info">0</span>
                                                    @else
                                                        <span class="badge bg-secondary">{{ $guide->guidings_count }}</span>
                                                    @endif
                                                </td>
                                                <td class="text-center">
                                                    @if($guide->guidings_count > 0)
                                                        <span class="badge bg-danger">{{ $guide->guidings_count }} deactivated</span>
                                                    @else
                                                        <span class="text-muted">—</span>
                                                    @endif
                                                </td>
                                                <td class="text-end">
                                                    <div class="btn-group btn-group-sm">
                                                        <a href="{{ route('admin.guides.edit', $guide) }}" class="btn btn-primary btn-sm" title="Edit Guide"><i class="fas fa-pen"></i></a>
                                                        <a href="{{ route('admin.guides.show', $guide) }}" class="btn btn-secondary btn-sm" title="Profile"><i class="fas fa-user"></i></a>
                                                        @php
                                                            $guideGuidingsData = $guide->guidings->map(function($g) {
                                                                return ['id' => $g->id, 'title' => $g->title, 'status' => $g->status, 'updated_at' => $g->updated_at ? $g->updated_at->format('M d, Y') : null];
                                                            })->values();
                                                            $guideGuidingsJsonB64 = base64_encode(json_encode($guideGuidingsData));
                                                        @endphp
                                                        <button type="button" class="btn btn-info btn-sm text-white guidings-modal-btn" title="Guidings"
                                                                data-guide-name="{{ $guide->full_name }}"
                                                                data-guidings="{{ $guideGuidingsJsonB64 }}"
                                                                data-bs-toggle="modal" data-bs-target="#guidingsModal">
                                                            <i class="fas fa-briefcase"></i>
                                                        </button>
                                                    </div>
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        @else
                            <div class="text-center py-5">
                                <div class="rounded-circle bg-success bg-opacity-15 d-inline-flex align-items-center justify-content-center mb-3" style="width: 80px; height: 80px;">
                                    <i class="fa fa-check-circle fa-3x text-success"></i>
                                </div>
                                <h5 class="text-muted">All guides are in good standing</h5>
                                <p class="text-muted mb-0">Every guide has at least one active or draft guiding.</p>
                            </div>
                        @endif
                    </div>

                    <!-- Tab 3: Deactivation Trends -->
                    <div class="tab-pane fade" id="deactivationTrends" role="tabpanel">
                        <p class="text-muted mb-4">Monthly count of guidings that were deactivated. Based on updated_at when status changed to deactivated.</p>
                        @if($deactivationByMonth->isNotEmpty())
                            <div class="row align-items-end">
                                <div class="col-12 col-lg-8">
                                    <div style="height: 320px;">
                                        <canvas id="deactivationChart"></canvas>
                                    </div>
                                </div>
                                <div class="col-12 col-lg-4 mt-4 mt-lg-0">
                                    <div class="card bg-light border-0">
                                        <div class="card-body">
                                            <h6 class="text-uppercase text-muted small mb-3">Recent Deactivations</h6>
                                            <ul class="list-unstyled mb-0 deactivation-summary-list">
                                                @foreach($deactivationByMonth->take(6)->reverse() as $item)
                                                    <li class="d-flex justify-content-between py-2 border-bottom border-light">
                                                        <span>{{ $item['label'] }}</span>
                                                        <strong class="text-danger">{{ $item['count'] }}</strong>
                                                    </li>
                                                @endforeach
                                            </ul>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @else
                            <div class="text-center py-5">
                                <div class="rounded-circle bg-light d-inline-flex align-items-center justify-content-center mb-3" style="width: 80px; height: 80px;">
                                    <i class="fe fe-trending-down fa-3x text-muted"></i>
                                </div>
                                <h5 class="text-muted">No deactivation data</h5>
                                <p class="text-muted mb-0">No guidings have been deactivated yet.</p>
                            </div>
                        @endif
                    </div>

                </div>
            </div>
        </div>

        <!-- Guidings Modal -->
        <div class="modal fade" id="guidingsModal" tabindex="-1" aria-labelledby="guidingsModalLabel" aria-hidden="true">
            <div class="modal-dialog modal-lg modal-dialog-scrollable">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="guidingsModalLabel">
                            <i class="fas fa-briefcase me-2"></i>Guidings for <span id="guidingsModalGuideName"></span>
                        </h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <div id="guidingsModalContent">
                            <!-- Populated by JavaScript -->
                        </div>
                    </div>
                </div>
            </div>
        </div>

    </div>
</div>

<style>
.hover-shadow:hover { box-shadow: 0 2px 8px rgba(0,0,0,0.06); }
.deactivation-summary-list li:last-child { border-bottom: none !important; }
/* Ensure Font Awesome icons are visible in action buttons */
#guidingsOverviewTable .btn-group .fa,
#guidingsOverviewTable .btn-group .fas,
#guidesNeedingAttentionTable .btn-group .fa,
#guidesNeedingAttentionTable .btn-group .fas {
    display: inline-block;
    font-style: normal;
    font-variant: normal;
    text-rendering: auto;
    line-height: 1;
}
#guidingsOverviewTable .btn-primary .fa,
#guidingsOverviewTable .btn-primary .fas,
#guidesNeedingAttentionTable .btn-primary .fa,
#guidesNeedingAttentionTable .btn-primary .fas { color: #fff !important; }
#guidingsOverviewTable .btn-secondary .fa,
#guidingsOverviewTable .btn-secondary .fas,
#guidesNeedingAttentionTable .btn-secondary .fa,
#guidesNeedingAttentionTable .btn-secondary .fas { color: #fff !important; }
#guidingsOverviewTable .btn-info .fa,
#guidingsOverviewTable .btn-info .fas,
#guidesNeedingAttentionTable .btn-info .fa,
#guidesNeedingAttentionTable .btn-info .fas { color: #fff !important; }
</style>
@endsection

@push('js_after')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Guidings modal - populate when opened
    const guidingsModal = document.getElementById('guidingsModal');
    const guidingsEditBaseUrl = '{{ url("admin/guidings") }}';
    if (guidingsModal) {
        guidingsModal.addEventListener('show.bs.modal', function(event) {
            const button = event.relatedTarget;
            if (button && button.classList.contains('guidings-modal-btn')) {
                const guideName = button.dataset.guideName || '';
                try {
                    const guidingsEncoded = button.dataset.guidings || '';
                    const guidings = guidingsEncoded ? JSON.parse(atob(guidingsEncoded)) : [];
                document.getElementById('guidingsModalGuideName').textContent = guideName;
                const content = document.getElementById('guidingsModalContent');
                if (guidings.length === 0) {
                    content.innerHTML = '<p class="text-muted mb-0">No guidings created yet.</p>';
                } else {
                    content.innerHTML = guidings.map(function(g) {
                        var statusBadge = g.status === 1 ? '<span class="badge bg-success">Active</span>' :
                            g.status === 2 ? '<span class="badge bg-info">Draft</span>' :
                            '<span class="badge bg-danger">Deactivated</span>';
                        var editUrl = guidingsEditBaseUrl + '/' + g.id + '/edit';
                        return '<a href="' + editUrl + '" class="d-flex align-items-center justify-content-between p-2 rounded bg-light border text-decoration-none text-dark hover-shadow mb-2">' +
                            '<span class="small text-truncate flex-grow-1">#' + g.id + ' ' + (g.title || '').substring(0, 50) + (g.title && g.title.length > 50 ? '...' : '') + '</span>' +
                            '<span class="d-flex align-items-center gap-2 flex-shrink-0">' + statusBadge +
                            '<span class="badge bg-light text-muted small">' + (g.updated_at || '') + '</span></span></a>';
                    }).join('');
                }
                } catch (e) {
                    document.getElementById('guidingsModalContent').innerHTML = '<p class="text-danger mb-0">Error loading guidings data.</p>';
                }
            }
        });
    }

    // Filter and search for Guides Needing Attention table
    const guidesTable = document.getElementById('guidesNeedingAttentionTable');
    if (guidesTable) {
        const filterButtons = document.querySelectorAll('#guidesNeedingAttention [data-filter]');
        const searchInput = document.getElementById('guidesNeedingAttentionSearch');
        let currentFilter = 'all';
        let searchTerm = '';

        function applyFilters() {
            const rows = guidesTable.querySelectorAll('tbody tr');
            rows.forEach(row => {
                const status = row.dataset.filterStatus || '';
                const matchesFilter = currentFilter === 'all' || status === currentFilter;
                const matchesSearch = !searchTerm || row.textContent.toLowerCase().includes(searchTerm.toLowerCase());
                row.style.display = (matchesFilter && matchesSearch) ? '' : 'none';
            });
        }

        filterButtons.forEach(btn => {
            btn.addEventListener('click', function() {
                currentFilter = this.dataset.filter;
                filterButtons.forEach(b => {
                    b.classList.remove('active', 'btn-outline-primary');
                    b.classList.add('btn-outline-secondary');
                });
                this.classList.remove('btn-outline-secondary');
                this.classList.add('active', 'btn-outline-primary');
                applyFilters();
            });
        });

        if (searchInput) {
            searchInput.addEventListener('input', function() {
                searchTerm = this.value;
                applyFilters();
            });
        }
    }

    // Search filter for guidings overview table
    const searchInput = document.getElementById('guidingsSearch');
    if (searchInput) {
        searchInput.addEventListener('input', function() {
            const term = this.value.toLowerCase();
            document.querySelectorAll('#guidingsOverviewTable tbody tr').forEach(row => {
                row.style.display = row.textContent.toLowerCase().includes(term) ? '' : 'none';
            });
        });
    }

    // Chart
    const deactivationData = @json($deactivationByMonth ?? collect());
    const ctx = document.getElementById('deactivationChart');
    if (ctx && deactivationData.length > 0) {
        new Chart(ctx.getContext('2d'), {
            type: 'bar',
            data: {
                labels: deactivationData.map(d => d.label),
                datasets: [{
                    label: 'Deactivated Guidings',
                    data: deactivationData.map(d => d.count),
                    backgroundColor: 'rgba(220, 53, 69, 0.75)',
                    borderColor: 'rgb(220, 53, 69)',
                    borderWidth: 1,
                    borderRadius: 4,
                    borderSkipped: false
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: { display: false },
                    tooltip: {
                        backgroundColor: 'rgba(0,0,0,0.8)',
                        padding: 12,
                        titleFont: { size: 13 },
                        bodyFont: { size: 12 }
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: { stepSize: 1 },
                        grid: { color: 'rgba(0,0,0,0.05)' }
                    },
                    x: {
                        grid: { display: false }
                    }
                }
            }
        });
    }
});
</script>
@endpush
