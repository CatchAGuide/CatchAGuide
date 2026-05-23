@extends('admin.layouts.app')

@section('title', 'Guide requests')

@section('content')
    <div class="side-app">
        <div class="main-container container-fluid">
            <div class="page-header">
                <h1 class="page-title">@yield('title')</h1>
            </div>

            @if(session('message'))
                <div class="alert alert-success">{{ session('message') }}</div>
            @endif

            <div class="row row-sm">
                <div class="col-lg-12">
                    <div class="card">
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table table-bordered">
                                    <thead>
                                    <tr>
                                        <th>ID</th>
                                        <th>Name</th>
                                        <th>Email</th>
                                        <th>Type</th>
                                        <th>Submitted</th>
                                        <th>SLA</th>
                                        <th class="text-center">Actions</th>
                                    </tr>
                                    </thead>
                                    <tbody>
                                    @forelse($pendingRequests as $request)
                                        @php $guide = $request->user; @endphp
                                        <tr class="{{ $request->submitted_at->lt(now()->subHours(24)) ? 'table-warning' : '' }}">
                                            <td>{{ $request->id }}</td>
                                            <td>{{ trim($guide->firstname . ' ' . $guide->lastname) }}</td>
                                            <td>{{ $guide->email }}</td>
                                            <td>{{ $guide->guide_type ?? 'private' }}</td>
                                            <td>{{ $request->submitted_at->format('Y-m-d H:i') }}</td>
                                            <td>
                                                @if($request->submitted_at->lt(now()->subHours(24)))
                                                    <span class="badge bg-danger">Over 24h</span>
                                                @else
                                                    <span class="badge bg-success">OK</span>
                                                @endif
                                            </td>
                                            <td class="text-center">
                                                <form action="{{ route('admin.guide-requests.approve', $request) }}" method="POST" class="d-inline">
                                                    @csrf
                                                    <button type="submit" class="btn btn-sm btn-success" title="Approve"><i class="fa fa-check"></i></button>
                                                </form>
                                                <button type="button"
                                                    class="btn btn-sm btn-danger"
                                                    title="Reject"
                                                    data-bs-toggle="modal"
                                                    data-bs-target="#rejectGuideRequestModal"
                                                    data-action="{{ route('admin.guide-requests.reject', $request) }}"
                                                    data-guide-name="{{ trim($guide->firstname . ' ' . $guide->lastname) }}">
                                                    <i class="fa fa-times"></i>
                                                </button>
                                            </td>
                                        </tr>
                                    @empty
                                        <tr><td colspan="7" class="text-center text-muted">No pending requests in queue.</td></tr>
                                    @endforelse

                                    @foreach($legacyPending as $guide)
                                        <tr class="table-secondary">
                                            <td>—</td>
                                            <td>{{ trim($guide->firstname . ' ' . $guide->lastname) }}</td>
                                            <td>{{ $guide->email }}</td>
                                            <td>{{ $guide->guide_type ?? 'private' }}</td>
                                            <td>{{ $guide->guide_submitted_at?->format('Y-m-d H:i') ?? '—' }}</td>
                                            <td><span class="badge bg-secondary">Legacy</span></td>
                                            <td class="text-center">
                                                <a href="{{ route('admin.guides.change-status', $guide) }}" class="btn btn-sm btn-success" title="Approve"><i class="fa fa-check"></i></a>
                                            </td>
                                        </tr>
                                    @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Modal must live outside the table — modals inside tbody break focus and backdrop clicks --}}
    <div class="modal fade" id="rejectGuideRequestModal" tabindex="-1" aria-labelledby="rejectGuideRequestModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <form id="rejectGuideRequestForm" method="POST" action="">
                    @csrf
                    <div class="modal-header">
                        <h5 class="modal-title" id="rejectGuideRequestModalLabel">Reject application</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <p class="text-muted mb-3" id="rejectGuideRequestSubtitle"></p>
                        <div class="mb-3">
                            <label for="rejection_reason" class="form-label">Reason (optional)</label>
                            <textarea name="rejection_reason" id="rejection_reason" class="form-control" rows="3"
                                placeholder="Reason for the guide (optional)"></textarea>
                        </div>
                        <div class="mb-0">
                            <label for="internal_notes" class="form-label">Internal notes</label>
                            <textarea name="internal_notes" id="internal_notes" class="form-control" rows="3"
                                placeholder="Admin-only notes"></textarea>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-danger">Reject</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection

@section('js_after')
<script>
document.addEventListener('DOMContentLoaded', function () {
    const modalEl = document.getElementById('rejectGuideRequestModal');
    if (!modalEl) return;

    modalEl.addEventListener('show.bs.modal', function (event) {
        const button = event.relatedTarget;
        if (!button) return;

        const form = document.getElementById('rejectGuideRequestForm');
        const action = button.getAttribute('data-action');
        if (action) {
            form.setAttribute('action', action);
        }

        const name = button.getAttribute('data-guide-name') || 'this guide';
        document.getElementById('rejectGuideRequestSubtitle').textContent =
            'Reject the guide application for ' + name + '?';

        document.getElementById('rejection_reason').value = '';
        document.getElementById('internal_notes').value = '';
    });
});
</script>
@endsection
