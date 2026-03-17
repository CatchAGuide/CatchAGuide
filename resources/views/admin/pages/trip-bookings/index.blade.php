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
                <div class="d-flex flex-wrap gap-3">
                    <span class="meta-pill"><i class="fa fa-inbox"></i> Total: <strong>{{ $bookingRequests->count() }}</strong></span>
                    <span class="meta-pill"><i class="fa fa-folder-open"></i> Open: <strong>{{ $statsOpen }}</strong></span>
                    <span class="meta-pill"><i class="fa fa-spinner"></i> In process: <strong>{{ $statsInProcess }}</strong></span>
                    <span class="meta-pill"><i class="fa fa-check-circle"></i> Done: <strong>{{ $statsDone }}</strong></span>
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
</script>
@endsection

