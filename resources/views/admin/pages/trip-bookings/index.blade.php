@extends('admin.layouts.app')

@section('title', 'Trip Booking Requests')

@section('custom_style')
<style>
    /* Bootstrap dropdowns get clipped inside .table-responsive (overflow: auto). Allow overflow on larger screens. */
    @media (min-width: 992px) {
        .table-responsive { overflow: visible; }
    }
    .dropdown-menu { z-index: 2000; }

    .js-status-dropdown-btn.btn-status-open { background-color: #0d6efd; border-color: #0d6efd; color: #fff; }
    .js-status-dropdown-btn.btn-status-open:hover { background-color: #0b5ed7; border-color: #0a58ca; color: #fff; }
    .js-status-dropdown-btn.btn-status-in_process { background-color: #ffc107; border-color: #ffc107; color: #000; }
    .js-status-dropdown-btn.btn-status-in_process:hover { background-color: #e0a800; border-color: #d39e00; color: #000; }
    .js-status-dropdown-btn.btn-status-done { background-color: #198754; border-color: #198754; color: #fff; }
    .js-status-dropdown-btn.btn-status-done:hover { background-color: #157347; border-color: #146c43; color: #fff; }

    #trip-bookings-datatable thead th {
        font-weight: 600;
        font-size: 0.75rem;
        text-transform: uppercase;
        letter-spacing: 0.04em;
        color: #6c757d;
        border-bottom: 1px solid #dee2e6;
        padding: 0.85rem 1rem;
        white-space: nowrap;
    }
    #trip-bookings-datatable tbody td {
        padding: 0.85rem 1rem;
        vertical-align: middle;
        border-bottom: 1px solid #eee;
        font-size: 0.9rem;
    }
    #trip-bookings-datatable tbody tr:hover { background-color: #f8f9fa; }

    .cr-source-cell { display: flex; align-items: center; gap: 0.75rem; min-height: 3rem; }
    .cr-source-cell__thumb { width: 40px; height: 40px; border-radius: 50%; flex-shrink: 0; background: #e9ecef; }
    .cr-source-cell__thumb--img { object-fit: cover; }
    .cr-source-cell__body { min-width: 0; flex: 1; }
    .cr-source-cell__badge { font-size: 0.65rem; font-weight: 600; padding: 0.1rem 0.35rem; border-radius: 3px; display: inline-block; margin-bottom: 0.2rem; }
    .cr-source-cell__badge--trip { background: #eef2ff; color: #3730a3; }
    .cr-source-cell__title { font-size: 0.9rem; font-weight: 600; color: #212529; display: block; overflow: hidden; text-overflow: ellipsis; white-space: nowrap; }
    .cr-source-cell__location { font-size: 0.8rem; color: #6c757d; display: block; overflow: hidden; text-overflow: ellipsis; white-space: nowrap; margin-top: 0.1rem; }
    .cr-source-cell__links { display: inline-flex; align-items: center; gap: 0.25rem; flex-shrink: 0; }
    .cr-source-cell__links a { width: 28px; height: 28px; padding: 0; display: inline-flex; align-items: center; justify-content: center; border-radius: 6px; font-size: 0.75rem; color: #495057; border: 1px solid #dee2e6; text-decoration: none; }
    .cr-source-cell__links a:hover { background: #e9ecef; color: #0d6efd; border-color: #adb5bd; }

    .cr-created { white-space: nowrap; font-size: 0.85rem; color: #6c757d; }
    .cr-row-contact { display: block; max-width: 100%; overflow: hidden; text-overflow: ellipsis; white-space: nowrap; }
    .meta-pill { display:inline-flex; align-items:center; gap:.35rem; padding:.2rem .5rem; border:1px solid #dee2e6; border-radius:999px; font-size:.8rem; color:#495057; background:#fff; }
    .meta-pill i { color:#6c757d; }

    /* Dropdown polish for manual creation modal */
    #trip-dropdown {
        background: #ffffff;
        border-radius: 0.5rem;
        box-shadow: 0 10px 30px rgba(15, 23, 42, 0.18);
    }

    #trip-dropdown .guiding-card {
        border-radius: 0;
        border-left: none;
        border-right: none;
        box-shadow: none;
        cursor: pointer;
        transition: background-color 120ms ease;
    }

    #trip-dropdown #trip-dropdown-list .guiding-card:nth-child(odd) { background: #ffffff; }
    #trip-dropdown #trip-dropdown-list .guiding-card:nth-child(even) { background: #f8fafc; }
    #trip-dropdown #trip-dropdown-list .guiding-card:hover { background: #eef2ff; }

    #trip-dropdown .guiding-card:first-child {
        border-top-left-radius: 0.5rem;
        border-top-right-radius: 0.5rem;
    }

    #trip-dropdown .guiding-card:last-child {
        border-bottom-left-radius: 0.5rem;
        border-bottom-right-radius: 0.5rem;
        border-bottom: none;
    }
</style>
@endsection

@section('content')
<div class="side-app">
    <div class="main-container container-fluid">
        <div class="page-header">
            <h1 class="page-title">@yield('title')</h1>
            <div>
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="{{ route('admin.index') }}">Admin</a></li>
                    <li class="breadcrumb-item active" aria-current="page">@yield('title')</li>
                </ol>
            </div>
        </div>

        @php
            $statsOpen = $bookingRequests->where('status', 'open')->count();
            $statsInProcess = $bookingRequests->where('status', 'in_process')->count();
            $statsDone = $bookingRequests->where('status', 'done')->count();
        @endphp

        <div class="row row-sm mb-3">
            <div class="col-12">
                <div class="d-flex flex-wrap gap-3 align-items-center justify-content-between">
                    <div class="d-flex flex-wrap gap-3">
                        <span class="meta-pill"><i class="fa fa-inbox"></i> Total: <strong>{{ $bookingRequests->count() }}</strong></span>
                        <span class="meta-pill"><i class="fa fa-folder-open"></i> Open: <strong>{{ $statsOpen }}</strong></span>
                        <span class="meta-pill"><i class="fa fa-spinner"></i> In process: <strong>{{ $statsInProcess }}</strong></span>
                        <span class="meta-pill"><i class="fa fa-check-circle"></i> Done: <strong>{{ $statsDone }}</strong></span>
                    </div>
                    <div class="ms-auto">
                        <button type="button" class="btn btn-sm btn-primary" data-bs-toggle="modal" data-bs-target="#manualTripBookingModal">
                            <i class="fa fa-plus me-1"></i> Create request
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <div class="row row-sm">
            <div class="col-12">
                <div class="card shadow-sm border-0">
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-hover mb-0" id="trip-bookings-datatable">
                                <thead>
                                    <tr>
                                        <th width="6%">ID</th>
                                        <th width="18%">Guest</th>
                                        <th width="18%">Contact</th>
                                        <th width="26%">Trip</th>
                                        <th width="10%">Preferred date</th>
                                        <th width="8%">Persons</th>
                                        <th width="10%">Created</th>
                                        <th width="10%">Status</th>
                                        <th width="8%" class="text-end">Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($bookingRequests as $request)
                                        @php
                                            $rowStatus = $request->status ?? 'open';
                                            $frontUrl = $request->getSourceFrontUrl();
                                            $thumbUrl = $request->getSourceThumbnailUrl();
                                            $sourceTitle = $request->getSourceTitle();
                                            $sourceLocation = $request->getSourceLocation();
                                            $sourceLabel = \App\Models\TripBooking::sourceTypeLabel($request->source_type);
                                            $preferredDateStr = optional($request->preferred_date)->format('Y-m-d');
                                            $replySubject = 'Re: Booking request';
                                            if (!empty($sourceTitle)) {
                                                $replySubject .= ' – ' . $sourceTitle;
                                            }
                                            if (!empty($preferredDateStr)) {
                                                $replySubject .= ' (' . $preferredDateStr . ')';
                                            }
                                        @endphp
                                        <tr>
                                            <td class="fw-semibold">#{{ $request->id }}</td>
                                            <td><span class="cr-row-contact" title="{{ e($request->name) }}">{{ $request->name ?: '—' }}</span></td>
                                            <td>
                                                <div class="cr-row-contact" title="{{ e($request->email) }}">{{ $request->email ?: '—' }}</div>
                                                <div class="cr-row-contact text-muted" title="{{ e(($request->phone_country_code ?? '') . ' ' . ($request->phone ?? '')) }}">{{ trim(($request->phone_country_code ?? '') . ' ' . ($request->phone ?? '')) ?: '—' }}</div>
                                            </td>
                                            <td>
                                                <div class="cr-source-cell" title="{{ $request->getSourceLabel() }}">
                                                    @if($thumbUrl)
                                                        <img src="{{ $thumbUrl }}" alt="" class="cr-source-cell__thumb cr-source-cell__thumb--img" loading="lazy" onerror="this.style.display='none';">
                                                    @else
                                                        <div class="cr-source-cell__thumb" style="display:flex;align-items:center;justify-content:center;color:#adb5bd;font-size:1rem;"><i class="fa fa-image"></i></div>
                                                    @endif
                                                    <div class="cr-source-cell__body">
                                                        <span class="cr-source-cell__badge cr-source-cell__badge--trip">{{ $sourceLabel }} #{{ $request->source_id }}</span>
                                                        @if($sourceTitle)
                                                            <span class="cr-source-cell__title">{{ \Illuminate\Support\Str::limit($sourceTitle, 42) }}</span>
                                                        @endif
                                                        @if($sourceLocation)
                                                            <span class="cr-source-cell__location">{{ \Illuminate\Support\Str::limit($sourceLocation, 48) }}</span>
                                                        @endif
                                                    </div>
                                                    <span class="cr-source-cell__links">
                                                        @if($frontUrl)
                                                            <a href="{{ $frontUrl }}" target="_blank" rel="noopener" title="View on site"><i class="fa fa-external-link-alt"></i></a>
                                                        @endif
                                                    </span>
                                                </div>
                                            </td>
                                            <td>{{ optional($request->preferred_date)->format('Y-m-d') ?: '—' }}</td>
                                            <td>{{ $request->number_of_persons ?: '—' }}</td>
                                            <td data-order="{{ optional($request->created_at)->timestamp }}" class="cr-created">{{ optional($request->created_at)->format('M j, Y g:i A') }}</td>
                                            <td>
                                                <div class="dropdown">
                                                    <button type="button"
                                                            class="btn btn-sm dropdown-toggle js-status-dropdown-btn btn-status-{{ $rowStatus }}"
                                                            data-bs-toggle="dropdown"
                                                            data-url="{{ route('admin.trip-bookings.update-status', $request) }}"
                                                            data-status="{{ $rowStatus }}"
                                                            aria-expanded="false">
                                                        <span class="js-status-btn-text">{{ \App\Models\TripBooking::statusOptions()[$rowStatus] ?? $rowStatus }}</span>
                                                        <i class="fa fa-caret-down ms-1"></i>
                                                    </button>
                                                    <ul class="dropdown-menu dropdown-menu-end">
                                                        @foreach(\App\Models\TripBooking::statusOptions() as $value => $label)
                                                            <li>
                                                                <a class="dropdown-item js-status-option {{ $rowStatus === $value ? 'active' : '' }}"
                                                                   href="#"
                                                                   data-status="{{ $value }}">{{ $label }}</a>
                                                            </li>
                                                        @endforeach
                                                    </ul>
                                                </div>
                                            </td>
                                            <td class="text-end">
                                                <div class="btn-group btn-group-sm">
                                                    <button type="button"
                                                            class="btn btn-outline-info js-view-message"
                                                            data-message="{{ e($request->message) }}"
                                                            data-bs-toggle="modal"
                                                            data-bs-target="#messageModal"
                                                            title="View message">
                                                        <i class="fa fa-eye"></i>
                                                    </button>
                                                    @if(!empty($request->email))
                                                        <button type="button"
                                                                class="btn btn-outline-primary js-reply-btn"
                                                                data-id="{{ $request->id }}"
                                                                data-name="{{ e($request->name ?? '') }}"
                                                                data-email="{{ e($request->email) }}"
                                                                data-subject="{{ e($replySubject) }}"
                                                                data-bs-toggle="modal"
                                                                data-bs-target="#replyModal"
                                                                title="Reply">
                                                            <i class="fa fa-reply"></i>
                                                        </button>
                                                        <button type="button"
                                                                class="btn btn-outline-secondary js-history-btn"
                                                                data-url="{{ route('admin.trip-bookings.email-history', $request) }}"
                                                                data-bs-toggle="modal"
                                                                data-bs-target="#historyModal"
                                                                title="Email history">
                                                            <i class="fa fa-history"></i>
                                                        </button>
                                                    @endif
                                                </div>
                                            </td>
                                        </tr>
                                    @empty
                                        <tr><td colspan="9" class="text-center text-muted py-4">No trip booking requests yet.</td></tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Manual Trip Booking Request Modal -->
<div class="modal fade" id="manualTripBookingModal" tabindex="-1" aria-labelledby="manualTripBookingModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="manualTripBookingModalLabel">Create trip booking request</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form method="POST" action="{{ route('admin.trip-bookings.store') }}" onsubmit="return validateManualTripBookingForm()">
                @csrf
                <div class="modal-body">
                    <div class="row">
                        <div class="col-lg-7">
                            <div class="card border-0 shadow-sm mb-4 position-relative">
                                <div class="card-header border-0">
                                    <h5 class="mb-0">Trip</h5>
                                </div>
                                <div class="card-body">
                                    <div class="mb-3 position-relative">
                                        <input type="text"
                                               id="trip-search-input"
                                               class="form-control"
                                               placeholder="Select trip…"
                                               autocomplete="off">
                                        <input type="hidden" name="trip_id" id="trip-id-input">

                                        <div id="trip-dropdown"
                                             class="card position-absolute w-100 mt-1 d-none"
                                             style="z-index: 1055; max-height: 260px; overflow-y: auto;">
                                            <div id="trip-dropdown-list"></div>

                                            <div id="trip-dropdown-loading" class="text-center py-2 small text-muted d-none">
                                                Loading trips…
                                            </div>

                                            <div id="trip-dropdown-empty" class="text-center py-2 small text-muted d-none">
                                                No trips found.
                                            </div>
                                        </div>

                                        <div class="text-danger small mt-1 d-none" id="trip-error">
                                            Please select a trip.
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="card border-0 shadow-sm">
                                <div class="card-header border-0">
                                    <h5 class="mb-0">Request details</h5>
                                </div>
                                <div class="card-body">
                                    <div class="row">
                                        <div class="col-md-6 mb-3">
                                            <label class="form-label">Preferred date</label>
                                            <input type="date"
                                                   name="preferred_date"
                                                   class="form-control"
                                                   min="{{ now()->toDateString() }}"
                                                   max="{{ now()->copy()->addYears(2)->toDateString() }}"
                                                   required>
                                        </div>
                                        <div class="col-md-6 mb-3">
                                            <label class="form-label">Number of persons</label>
                                            <input type="number" name="number_of_persons" class="form-control" min="1" max="99" value="1" required>
                                        </div>
                                        <div class="col-md-6 mb-3">
                                            <label class="form-label">Initial status</label>
                                            <select name="status" class="form-control">
                                                <option value="open" selected>Open</option>
                                                <option value="in_process">In process</option>
                                                <option value="done">Done</option>
                                            </select>
                                        </div>
                                        <div class="col-12 mb-3">
                                            <label class="form-label">Message</label>
                                            <textarea name="message" rows="3" class="form-control" placeholder="Internal details / guest message (optional)"></textarea>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="col-lg-5">
                            <div class="card border-0 shadow-sm mb-4">
                                <div class="card-header border-0">
                                    <h5 class="mb-0">Guest details</h5>
                                </div>
                                <div class="card-body">
                                    <div class="mb-3">
                                        <label class="form-label">Name</label>
                                        <input type="text" name="name" class="form-control" required>
                                    </div>
                                    <div class="mb-3">
                                        <label class="form-label">Email</label>
                                        <input type="email" name="email" class="form-control">
                                    </div>
                                    <div class="row">
                                        <div class="col-md-4 mb-3">
                                            <label class="form-label">Country code</label>
                                            <input type="text" name="phone_country_code" class="form-control" value="+49">
                                        </div>
                                        <div class="col-md-8 mb-3">
                                            <label class="form-label">Phone</label>
                                            <input type="text" name="phone" class="form-control">
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="card border-0 shadow-sm">
                                <div class="card-header border-0">
                                    <h5 class="mb-0">How this works</h5>
                                </div>
                                <div class="card-body">
                                    <ul class="list-unstyled mb-0">
                                        <li class="mb-2">
                                            <strong>DB only:</strong>
                                            This creates a booking request record without triggering email flows.
                                        </li>
                                        <li class="mb-0">
                                            <strong>Source:</strong>
                                            The request is linked to the selected trip as <code>source_type=trip</code>.
                                        </li>
                                    </ul>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">
                        Create request
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Message Modal -->
<div class="modal fade" id="messageModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Message</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="js-message-text" style="white-space: pre-wrap;"></div>
            </div>
        </div>
    </div>
</div>

<!-- Reply Modal -->
<div class="modal fade" id="replyModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content">
            <form method="POST" action="{{ route('admin.trip-bookings.reply') }}" id="tripReplyForm">
                @csrf
                <input type="hidden" name="trip_booking_id" id="replyTripBookingId">
                <div class="modal-header">
                    <h5 class="modal-title">Reply</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">To</label>
                        <input type="text" class="form-control" id="replyTo" disabled>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Subject</label>
                        <input type="text" name="subject" class="form-control" id="replySubject" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Message</label>
                        <textarea name="body" class="form-control" id="trip_reply_body" rows="10" required></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Send</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- History Modal -->
<div class="modal fade" id="historyModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Email history</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="js-history-loading text-muted">Loading…</div>
                <div class="js-history-empty text-muted d-none">No emails yet.</div>
                <div class="js-history-list d-none"></div>
            </div>
        </div>
    </div>
</div>
@endsection

@section('js_after')
<script>
function validateManualTripBookingForm() {
    const tripId = document.getElementById('trip-id-input')?.value;
    const errorEl = document.getElementById('trip-error');
    if (!tripId) {
        if (errorEl) errorEl.classList.remove('d-none');
        return false;
    }
    if (errorEl) errorEl.classList.add('d-none');
    return true;
}

document.addEventListener('DOMContentLoaded', function () {
    // WYSIWYG (CKEditor 4 is loaded globally in admin layout)
    if (typeof CKEDITOR !== 'undefined' && !CKEDITOR.instances.trip_reply_body) {
        CKEDITOR.replace('trip_reply_body', { height: 260 });
    }

    // View message
    document.querySelectorAll('.js-view-message').forEach(function(btn) {
        btn.addEventListener('click', function() {
            var msg = btn.getAttribute('data-message') || '';
            var el = document.querySelector('#messageModal .js-message-text');
            if (el) el.textContent = msg;
        });
    });

    // Reply modal prefill
    document.querySelectorAll('.js-reply-btn').forEach(function(btn) {
        btn.addEventListener('click', function() {
            var id = btn.getAttribute('data-id');
            var email = btn.getAttribute('data-email') || '';
            var name = btn.getAttribute('data-name') || '';
            var subject = btn.getAttribute('data-subject') || 'Re: Booking request';
            var to = (name ? (name + ' <' + email + '>') : email);

            var idEl = document.getElementById('replyTripBookingId');
            var toEl = document.getElementById('replyTo');
            var subjectEl = document.getElementById('replySubject');
            if (idEl) idEl.value = id || '';
            if (toEl) toEl.value = to;
            if (subjectEl) subjectEl.value = subject;
        });
    });

    // Ensure editor content is posted
    var replyForm = document.getElementById('tripReplyForm');
    if (replyForm) {
        replyForm.addEventListener('submit', function () {
            if (typeof CKEDITOR !== 'undefined' && CKEDITOR.instances.trip_reply_body) {
                CKEDITOR.instances.trip_reply_body.updateElement();
            }
        });
    }

    // Status dropdown update (AJAX)
    document.querySelectorAll('.js-status-option').forEach(function(option) {
        option.addEventListener('click', function(e) {
            e.preventDefault();
            var status = option.getAttribute('data-status');
            var dropdown = option.closest('.dropdown');
            var btn = dropdown ? dropdown.querySelector('.js-status-dropdown-btn') : null;
            if (!btn) return;
            var url = btn.getAttribute('data-url');
            var token = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');

            fetch(url, {
                method: 'PATCH',
                headers: {
                    'Content-Type': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest',
                    'X-CSRF-TOKEN': token
                },
                body: JSON.stringify({ status: status })
            })
            .then(function(r){ return r.json(); })
            .then(function(data){
                if (!data || !data.success) return;
                btn.classList.remove('btn-status-open', 'btn-status-in_process', 'btn-status-done');
                btn.classList.add('btn-status-' + data.status);
                btn.setAttribute('data-status', data.status);
                var labelEl = btn.querySelector('.js-status-btn-text');
                if (labelEl) labelEl.textContent = data.status_label || data.status;
            })
            .catch(function(){});
        });
    });

    // History modal loader
    var historyModal = document.getElementById('historyModal');
    if (historyModal) {
        historyModal.addEventListener('show.bs.modal', function (event) {
            var trigger = event.relatedTarget;
            var url = trigger ? trigger.getAttribute('data-url') : null;
            var loading = historyModal.querySelector('.js-history-loading');
            var empty = historyModal.querySelector('.js-history-empty');
            var list = historyModal.querySelector('.js-history-list');
            if (loading) loading.classList.remove('d-none');
            if (empty) empty.classList.add('d-none');
            if (list) { list.classList.add('d-none'); list.innerHTML = ''; }
            if (!url) return;

            fetch(url, { headers: { 'X-Requested-With': 'XMLHttpRequest' } })
                .then(function(r){ return r.json(); })
                .then(function(data){
                    if (loading) loading.classList.add('d-none');
                    var items = (data && data.items) ? data.items : [];
                    if (!items.length) {
                        if (empty) empty.classList.remove('d-none');
                        return;
                    }
                    if (!list) return;
                    list.classList.remove('d-none');
                    items.forEach(function(item){
                        var wrapper = document.createElement('div');
                        wrapper.className = 'border rounded p-3 mb-2';
                        wrapper.innerHTML =
                            '<div class="d-flex justify-content-between gap-2">' +
                              '<div><strong>' + (item.subject || '') + '</strong><div class="text-muted small">' + (item.created_at || '') + '</div></div>' +
                              '<div class="text-muted small">' + (item.email || '') + '</div>' +
                            '</div>' +
                            (item.body_html ? ('<hr><div style="white-space: normal;">' + item.body_html + '</div>') : '');
                        list.appendChild(wrapper);
                    });
                })
                .catch(function(){
                    if (loading) loading.classList.add('d-none');
                    if (empty) empty.classList.remove('d-none');
                });
        });
    }
});

// Trip dropdown search + selection (lazy load + search + infinite scroll)
(function () {
    const searchInput = document.getElementById('trip-search-input');
    const tripIdInput = document.getElementById('trip-id-input');
    const dropdown = document.getElementById('trip-dropdown');
    const listEl = document.getElementById('trip-dropdown-list');
    const loadingEl = document.getElementById('trip-dropdown-loading');
    const emptyEl = document.getElementById('trip-dropdown-empty');
    if (!searchInput || !dropdown || !listEl || !tripIdInput) return;

    const apiUrl = @json(route('admin.trip-bookings.trips-search'));

    let currentTerm = '';
    let currentPage = 1;
    let nextPage = null;
    let isLoading = false;
    let debounceTimer = null;

    function openDropdown() {
        dropdown.classList.remove('d-none');
    }

    function closeDropdown() {
        dropdown.classList.add('d-none');
    }

    function setLoading(state) {
        isLoading = state;
        if (loadingEl) loadingEl.classList.toggle('d-none', !state);
    }

    function clearList() {
        listEl.innerHTML = '';
        if (emptyEl) emptyEl.classList.add('d-none');
    }

    function renderTripCard(item) {
        const div = document.createElement('div');
        div.className = 'guiding-card border-0 border-bottom';
        div.dataset.tripId = item.id;
        div.dataset.label = item.title;
        const placeholderUrl = @json(asset('images/placeholder_guide.jpg'));
        div.innerHTML = `
            <div class="card-body py-2 px-2 d-flex align-items-center">
                <div class="me-3">
                    <img src="${item.thumbnail_url}"
                         alt="Trip thumbnail"
                         class="rounded"
                         loading="lazy"
                         onerror="this.onerror=null;this.src='${placeholderUrl}';"
                         style="width: 40px; height: 40px; object-fit: cover;">
                </div>
                <div class="flex-grow-1">
                    <div class="fw-semibold">${item.title}</div>
                    <div class="text-muted small">
                        ID #${item.id}${item.location ? ' · ' + item.location : ''}
                    </div>
                </div>
            </div>
        `;

        div.addEventListener('click', function () {
            const id = this.dataset.tripId;
            const label = this.dataset.label || ('ID #' + id);
            tripIdInput.value = id;
            searchInput.value = label;

            const errorEl = document.getElementById('trip-error');
            if (errorEl) errorEl.classList.add('d-none');

            closeDropdown();
        });

        return div;
    }

    function loadTrips({ reset = false } = {}) {
        if (isLoading) return;

        if (reset) {
            currentPage = 1;
            nextPage = null;
            clearList();
        }

        setLoading(true);

        const params = new URLSearchParams();
        params.set('page', String(currentPage));
        params.set('per_page', '20');
        if (currentTerm) params.set('q', currentTerm);

        fetch(apiUrl + '?' + params.toString(), {
            headers: { 'Accept': 'application/json' },
        })
            .then(r => r.json())
            .then(json => {
                const data = Array.isArray(json.data) ? json.data : [];

                if (reset && data.length === 0) {
                    if (emptyEl) emptyEl.classList.remove('d-none');
                } else {
                    if (emptyEl) emptyEl.classList.add('d-none');
                }

                data.forEach(item => listEl.appendChild(renderTripCard(item)));

                nextPage = json.next_page || null;
                currentPage = json.current_page || currentPage;
            })
            .catch(() => {
                if (reset && emptyEl) {
                    emptyEl.textContent = 'Failed to load trips.';
                    emptyEl.classList.remove('d-none');
                }
            })
            .finally(() => setLoading(false));
    }

    function ensureInitialLoaded() {
        if (!listEl.childElementCount && !isLoading) {
            loadTrips({ reset: true });
        }
    }

    searchInput.addEventListener('focus', function () {
        openDropdown();
        ensureInitialLoaded();
    });
    searchInput.addEventListener('click', function () {
        openDropdown();
        ensureInitialLoaded();
    });

    searchInput.addEventListener('input', function () {
        currentTerm = this.value.toLowerCase().trim();
        if (debounceTimer) window.clearTimeout(debounceTimer);
        debounceTimer = window.setTimeout(() => loadTrips({ reset: true }), 250);
    });

    dropdown.addEventListener('scroll', function () {
        if (!nextPage || isLoading) return;
        const threshold = 40;
        if (dropdown.scrollTop + dropdown.clientHeight + threshold >= dropdown.scrollHeight) {
            currentPage = nextPage;
            loadTrips();
        }
    });

    document.addEventListener('click', function (e) {
        if (!dropdown.contains(e.target) && e.target !== searchInput) {
            closeDropdown();
        }
    });
})();

// Prevent double-submit (disable button after first submit)
document.addEventListener('DOMContentLoaded', function () {
    const form = document.querySelector('#manualTripBookingModal form');
    if (!form) return;
    form.addEventListener('submit', function () {
        const btn = form.querySelector('button[type="submit"]');
        if (!btn) return;
        btn.disabled = true;
        btn.innerHTML = '<i class="fa fa-spinner fa-spin me-1"></i> Creating…';
    });
});
</script>
@endsection

