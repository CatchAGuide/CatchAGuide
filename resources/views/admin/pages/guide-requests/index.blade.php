@extends('admin.layouts.app')

@section('title', 'Guide requests')

@php
    $emptyPlaceholder = '—';

    $buildGuidePayload = function ($guide, $request = null) use ($emptyPlaceholder) {
        $info = $guide->information;
        $val = static fn ($value) => filled($value) ? (string) $value : null;

        $fullName = trim(($guide->firstname ?? '') . ' ' . ($guide->lastname ?? ''));

        $street = trim(($info->address ?? '') . ' ' . ($info->address_number ?? ''));
        $cityLine = trim(trim(($info->postal ?? '') . ' ' . ($info->city ?? '')));

        $birthday = null;
        if ($info && filled($info->birthday)) {
            try {
                $birthday = \Carbon\Carbon::parse($info->birthday)->format('Y-m-d');
            } catch (\Throwable $e) {
                $birthday = (string) $info->birthday;
            }
        }

        $phone = null;
        if ($info && (filled($info->phone) || filled($info->phone_country_code))) {
            $phone = trim((string) ($info->phone_country_code ?? '') . ' ' . (string) ($info->phone ?? ''));
        } elseif (filled($guide->phone) || filled($guide->phone_country_code)) {
            $phone = trim((string) ($guide->phone_country_code ?? '') . ' ' . (string) ($guide->phone ?? ''));
        }

        return [
            'name' => $val($fullName),
            'email' => $val($guide->email),
            'guide_type' => $val($guide->guide_type ?? 'private'),
            'phone' => $val($phone),
            'birthday' => $birthday,
            'address' => $val($street),
            'postal_city' => $val($cityLine),
            'country' => $val($info->country ?? null),
            'tax_id' => $val($guide->taxId ?? ($info->taxId ?? null)),
            'tax_number' => $val($info->tax_number ?? null),
            'company_name' => $val($info->company_name ?? null),
            'legal_form' => $val($info->legal_form ?? null),
            'submitted_at' => $request && $request->submitted_at
                ? $request->submitted_at->format('Y-m-d H:i')
                : ($guide->guide_submitted_at?->format('Y-m-d H:i')),
            'request_id' => $request?->id,
        ];
    };
@endphp

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
                                        @php
                                            $guide = $request->user;
                                            $payload = $buildGuidePayload($guide, $request);
                                            $displayName = $payload['name'] ?? $emptyPlaceholder;
                                        @endphp
                                        <tr class="{{ $request->submitted_at->lt(now()->subHours(24)) ? 'table-warning' : '' }}">
                                            <td>{{ $request->id }}</td>
                                            <td>{{ $displayName }}</td>
                                            <td>{{ $payload['email'] ?? $emptyPlaceholder }}</td>
                                            <td>{{ $payload['guide_type'] ?? $emptyPlaceholder }}</td>
                                            <td>{{ $payload['submitted_at'] ?? $emptyPlaceholder }}</td>
                                            <td>
                                                @if($request->submitted_at->lt(now()->subHours(24)))
                                                    <span class="badge bg-danger">Over 24h</span>
                                                @else
                                                    <span class="badge bg-success">OK</span>
                                                @endif
                                            </td>
                                            <td class="text-center">
                                                <button type="button"
                                                    class="btn btn-sm btn-success"
                                                    title="Review &amp; approve"
                                                    data-bs-toggle="modal"
                                                    data-bs-target="#approveGuideRequestModal"
                                                    data-action="{{ route('admin.guide-requests.approve', $request) }}"
                                                    data-method="POST"
                                                    data-guide='@json($payload)'>
                                                    <i class="fa fa-check"></i>
                                                </button>
                                                <button type="button"
                                                    class="btn btn-sm btn-danger"
                                                    title="Reject"
                                                    data-bs-toggle="modal"
                                                    data-bs-target="#rejectGuideRequestModal"
                                                    data-action="{{ route('admin.guide-requests.reject', $request) }}"
                                                    data-guide-name="{{ $displayName }}">
                                                    <i class="fa fa-times"></i>
                                                </button>
                                            </td>
                                        </tr>
                                    @empty
                                        <tr><td colspan="7" class="text-center text-muted">No pending requests in queue.</td></tr>
                                    @endforelse

                                    @foreach($legacyPending as $guide)
                                        @php
                                            $payload = $buildGuidePayload($guide);
                                            $displayName = $payload['name'] ?? $emptyPlaceholder;
                                        @endphp
                                        <tr class="table-secondary">
                                            <td>—</td>
                                            <td>{{ $displayName }}</td>
                                            <td>{{ $payload['email'] ?? $emptyPlaceholder }}</td>
                                            <td>{{ $payload['guide_type'] ?? $emptyPlaceholder }}</td>
                                            <td>{{ $payload['submitted_at'] ?? $emptyPlaceholder }}</td>
                                            <td><span class="badge bg-secondary">Legacy</span></td>
                                            <td class="text-center">
                                                <button type="button"
                                                    class="btn btn-sm btn-success"
                                                    title="Review &amp; approve"
                                                    data-bs-toggle="modal"
                                                    data-bs-target="#approveGuideRequestModal"
                                                    data-action="{{ route('admin.guides.change-status', $guide) }}"
                                                    data-method="GET"
                                                    data-guide='@json($payload)'>
                                                    <i class="fa fa-check"></i>
                                                </button>
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

    {{-- Approve review modal: shows guide details before confirming the approval --}}
    <div class="modal fade" id="approveGuideRequestModal" tabindex="-1" aria-labelledby="approveGuideRequestModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="approveGuideRequestModalLabel">Review guide application</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <p class="text-muted mb-3" id="approveGuideRequestSubtitle">
                        Please review the applicant's details before approving.
                    </p>

                    <div class="row g-3">
                        <div class="col-md-6">
                            <div class="fw-semibold text-muted small">Full name</div>
                            <div data-guide-field="name">{{ $emptyPlaceholder }}</div>
                        </div>
                        <div class="col-md-6">
                            <div class="fw-semibold text-muted small">Email</div>
                            <div data-guide-field="email" class="text-break">{{ $emptyPlaceholder }}</div>
                        </div>

                        <div class="col-md-6">
                            <div class="fw-semibold text-muted small">Guide type</div>
                            <div data-guide-field="guide_type" class="text-capitalize">{{ $emptyPlaceholder }}</div>
                        </div>
                        <div class="col-md-6">
                            <div class="fw-semibold text-muted small">Phone</div>
                            <div data-guide-field="phone">{{ $emptyPlaceholder }}</div>
                        </div>

                        <div class="col-md-6">
                            <div class="fw-semibold text-muted small">Birthday</div>
                            <div data-guide-field="birthday">{{ $emptyPlaceholder }}</div>
                        </div>
                        <div class="col-md-6">
                            <div class="fw-semibold text-muted small">Submitted</div>
                            <div data-guide-field="submitted_at">{{ $emptyPlaceholder }}</div>
                        </div>

                        <div class="col-12">
                            <hr class="my-1">
                            <div class="fw-semibold text-muted text-uppercase small mb-2">Address</div>
                        </div>
                        <div class="col-md-6">
                            <div class="fw-semibold text-muted small">Street</div>
                            <div data-guide-field="address">{{ $emptyPlaceholder }}</div>
                        </div>
                        <div class="col-md-4">
                            <div class="fw-semibold text-muted small">Postal &amp; city</div>
                            <div data-guide-field="postal_city">{{ $emptyPlaceholder }}</div>
                        </div>
                        <div class="col-md-2">
                            <div class="fw-semibold text-muted small">Country</div>
                            <div data-guide-field="country">{{ $emptyPlaceholder }}</div>
                        </div>

                        <div class="col-12 approve-company-section" style="display:none">
                            <hr class="my-1">
                            <div class="fw-semibold text-muted text-uppercase small mb-2">Company</div>
                        </div>
                        <div class="col-md-6 approve-company-section" style="display:none">
                            <div class="fw-semibold text-muted small">Company name</div>
                            <div data-guide-field="company_name">{{ $emptyPlaceholder }}</div>
                        </div>
                        <div class="col-md-6 approve-company-section" style="display:none">
                            <div class="fw-semibold text-muted small">Legal form</div>
                            <div data-guide-field="legal_form">{{ $emptyPlaceholder }}</div>
                        </div>

                        <div class="col-12">
                            <hr class="my-1">
                            <div class="fw-semibold text-muted text-uppercase small mb-2">Tax</div>
                        </div>
                        <div class="col-md-6">
                            <div class="fw-semibold text-muted small">VAT / Tax ID</div>
                            <div data-guide-field="tax_id">{{ $emptyPlaceholder }}</div>
                        </div>
                        <div class="col-md-6">
                            <div class="fw-semibold text-muted small">Tax number</div>
                            <div data-guide-field="tax_number">{{ $emptyPlaceholder }}</div>
                        </div>
                    </div>

                    <div class="alert alert-info mt-4 mb-0 small">
                        Approving this application will mark the user as a verified guide. Any draft tours that already
                        contain complete information will be published automatically.
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <form id="approveGuideRequestForm" method="POST" action="" class="d-inline">
                        @csrf
                        <button type="submit" class="btn btn-success" id="approveGuideRequestSubmit">
                            <i class="fa fa-check me-1"></i> Approve guide
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    {{-- Reject modal --}}
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
    const EMPTY_PLACEHOLDER = '—';

    const rejectModalEl = document.getElementById('rejectGuideRequestModal');
    if (rejectModalEl) {
        rejectModalEl.addEventListener('show.bs.modal', function (event) {
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
    }

    const approveModalEl = document.getElementById('approveGuideRequestModal');
    if (!approveModalEl) return;

    const approveForm = document.getElementById('approveGuideRequestForm');
    const approveSubtitle = document.getElementById('approveGuideRequestSubtitle');
    const approveSubmit = document.getElementById('approveGuideRequestSubmit');
    const companySections = approveModalEl.querySelectorAll('.approve-company-section');

    let pendingAction = null;
    let pendingMethod = 'POST';

    function setFieldValues(payload) {
        approveModalEl.querySelectorAll('[data-guide-field]').forEach(function (el) {
            const key = el.getAttribute('data-guide-field');
            const value = payload && payload[key] != null && String(payload[key]).trim() !== ''
                ? String(payload[key])
                : EMPTY_PLACEHOLDER;
            el.textContent = value;
            el.classList.toggle('text-muted', value === EMPTY_PLACEHOLDER);
        });

        const isCompany = (payload && payload.guide_type || '').toLowerCase() === 'company';
        companySections.forEach(function (section) {
            section.style.display = isCompany ? '' : 'none';
        });

        const displayName = payload && payload.name ? payload.name : 'this applicant';
        approveSubtitle.textContent = 'Review the details below for ' + displayName + ' before approving.';
    }

    approveModalEl.addEventListener('show.bs.modal', function (event) {
        const button = event.relatedTarget;
        if (!button) return;

        pendingAction = button.getAttribute('data-action') || null;
        pendingMethod = (button.getAttribute('data-method') || 'POST').toUpperCase();

        let payload = {};
        try {
            const raw = button.getAttribute('data-guide');
            if (raw) payload = JSON.parse(raw);
        } catch (e) {
            payload = {};
        }

        setFieldValues(payload);

        if (pendingMethod === 'POST') {
            approveForm.setAttribute('action', pendingAction || '');
        } else {
            approveForm.setAttribute('action', '');
        }
    });

    approveForm.addEventListener('submit', function (event) {
        if (!pendingAction) return;

        if (pendingMethod === 'GET') {
            event.preventDefault();
            approveSubmit.disabled = true;
            window.location.href = pendingAction;
            return;
        }

        approveSubmit.disabled = true;
    });
});
</script>
@endsection
