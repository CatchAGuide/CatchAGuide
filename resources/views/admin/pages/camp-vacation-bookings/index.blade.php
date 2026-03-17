@extends('admin.layouts.app')

@section('title', 'Camp/Vacation Booking Requests')

@section('custom_style')
<style>
    /* Bootstrap dropdowns get clipped inside .table-responsive (overflow: auto). Allow overflow on larger screens. */
    @media (min-width: 992px) {
        .table-responsive {
            overflow: visible;
        }
    }
    .dropdown-menu {
        z-index: 2000;
    }

    /* Reuse the same status button styles as contact requests */
    .js-status-dropdown-btn.btn-status-open { background-color: #0d6efd; border-color: #0d6efd; color: #fff; }
    .js-status-dropdown-btn.btn-status-open:hover { background-color: #0b5ed7; border-color: #0a58ca; color: #fff; }
    .js-status-dropdown-btn.btn-status-in_process { background-color: #ffc107; border-color: #ffc107; color: #000; }
    .js-status-dropdown-btn.btn-status-in_process:hover { background-color: #e0a800; border-color: #d39e00; color: #000; }
    .js-status-dropdown-btn.btn-status-done { background-color: #198754; border-color: #198754; color: #fff; }
    .js-status-dropdown-btn.btn-status-done:hover { background-color: #157347; border-color: #146c43; color: #fff; }

    #camp-vacation-bookings-datatable thead th {
        font-weight: 600;
        font-size: 0.75rem;
        text-transform: uppercase;
        letter-spacing: 0.04em;
        color: #6c757d;
        border-bottom: 1px solid #dee2e6;
        padding: 0.85rem 1rem;
        white-space: nowrap;
    }
    #camp-vacation-bookings-datatable tbody td {
        padding: 0.85rem 1rem;
        vertical-align: middle;
        border-bottom: 1px solid #eee;
        font-size: 0.9rem;
    }
    #camp-vacation-bookings-datatable tbody tr:hover { background-color: #f8f9fa; }

    .cr-source-cell { display: flex; align-items: center; gap: 0.75rem; min-height: 3rem; }
    .cr-source-cell__thumb { width: 40px; height: 40px; border-radius: 50%; flex-shrink: 0; background: #e9ecef; }
    .cr-source-cell__thumb--img { object-fit: cover; }
    .cr-source-cell__body { min-width: 0; flex: 1; }
    .cr-source-cell__badge { font-size: 0.65rem; font-weight: 600; padding: 0.1rem 0.35rem; border-radius: 3px; display: inline-block; margin-bottom: 0.2rem; }
    .cr-source-cell__badge--vacation { background: #e8f5e9; color: #2e7d32; }
    .cr-source-cell__badge--camp { background: #fff8e1; color: #f57c00; }
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
                            <table class="table table-hover mb-0" id="camp-vacation-bookings-datatable">
                                <thead>
                                    <tr>
                                        <th width="6%">ID</th>
                                        <th width="18%">Guest</th>
                                        <th width="18%">Contact</th>
                                        <th width="26%">Camp/Vacation</th>
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
                                            $sourceType = strtolower($request->source_type);
                                            $frontUrl = $request->getSourceFrontUrl();
                                            $thumbUrl = $request->getSourceThumbnailUrl();
                                            $sourceTitle = $request->getSourceTitle();
                                            $sourceLocation = $request->getSourceLocation();
                                            $sourceLabel = \App\Models\CampVacationBooking::sourceTypeLabel($request->source_type);
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
                                            <td>
                                                <span class="cr-row-contact" title="{{ e($request->name) }}">{{ $request->name ?: '—' }}</span>
                                            </td>
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
                                                        <span class="cr-source-cell__badge cr-source-cell__badge--{{ $sourceType }}">{{ $sourceLabel }} #{{ $request->source_id }}</span>
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
                                            <td data-order="{{ optional($request->created_at)->timestamp }}" class="cr-created">
                                                {{ optional($request->created_at)->format('M j, Y g:i A') }}
                                            </td>
                                            <td>
                                                <div class="dropdown">
                                                    <button type="button"
                                                            class="btn btn-sm dropdown-toggle js-status-dropdown-btn btn-status-{{ $rowStatus }}"
                                                            data-bs-toggle="dropdown"
                                                            data-url="{{ route('admin.camp-vacation-bookings.update-status', $request) }}"
                                                            data-status="{{ $rowStatus }}"
                                                            aria-expanded="false">
                                                        <span class="js-status-btn-text">{{ \App\Models\CampVacationBooking::statusOptions()[$rowStatus] ?? $rowStatus }}</span>
                                                        <i class="fa fa-caret-down ms-1"></i>
                                                    </button>
                                                    <ul class="dropdown-menu dropdown-menu-end">
                                                        @foreach(\App\Models\CampVacationBooking::statusOptions() as $value => $label)
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
                                                                data-url="{{ route('admin.camp-vacation-bookings.email-history', $request) }}"
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
                                        <tr>
                                            <td colspan="9" class="text-center py-5 text-muted">
                                                <i class="fa fa-inbox fa-2x mb-2 d-block"></i>
                                                No camp/vacation booking requests found.
                                            </td>
                                        </tr>
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

<div class="modal fade" id="messageModal" tabindex="-1" aria-labelledby="messageModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="messageModalLabel">Booking request message</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div id="messageModalContent" style="white-space: pre-wrap; word-break: break-word;"></div>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="replyModal" tabindex="-1" aria-labelledby="replyModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <form method="POST" action="{{ route('admin.camp-vacation-bookings.reply') }}" id="replyForm">
                @csrf
                <input type="hidden" name="camp_vacation_booking_id" id="reply_booking_id" value="{{ old('camp_vacation_booking_id') }}">
                <input type="hidden" name="recipient_display" id="reply_recipient_hidden" value="{{ old('recipient_display') }}">

                <div class="modal-header">
                    <h5 class="modal-title" id="replyModalLabel">Reply to booking request</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>

                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Recipient</label>
                        <input type="text" class="form-control" id="reply_recipient_display" value="{{ old('recipient_display') }}" readonly>
                    </div>

                    <div class="mb-3">
                        <label for="reply_subject" class="form-label">Subject</label>
                        <input type="text" class="form-control" id="reply_subject" name="subject" value="{{ old('subject', 'Re: Your booking request') }}" required>
                    </div>

                    <div class="mb-3">
                        <label for="reply_body" class="form-label">Message</label>
                        <textarea class="form-control" id="reply_body" name="body" rows="12" required>{{ old('body') }}</textarea>
                    </div>
                </div>

                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Send Email</button>
                </div>
            </form>
        </div>
    </div>
</div>

<div class="modal fade" id="historyModal" tabindex="-1" aria-labelledby="historyModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="historyModalLabel">Email history</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div id="historyLoading" class="text-muted">Loading…</div>
                <div id="historyEmpty" class="text-muted d-none">No emails sent yet.</div>
                <div id="historyList" class="d-none"></div>
            </div>
        </div>
    </div>
</div>
@endsection

@section('js_after')
<script>
    document.addEventListener('DOMContentLoaded', function () {
        // message modal
        document.querySelectorAll('.js-view-message').forEach(btn => {
            btn.addEventListener('click', function () {
                const msg = this.getAttribute('data-message') || '';
                const el = document.getElementById('messageModalContent');
                if (el) el.textContent = msg;
            });
        });

        // status updates (AJAX)
        document.querySelectorAll('#camp-vacation-bookings-datatable .js-status-option').forEach(option => {
            option.addEventListener('click', function (e) {
                e.preventDefault();
                const newStatus = this.getAttribute('data-status');
                const dropdown = this.closest('.dropdown');
                const btn = dropdown ? dropdown.querySelector('.js-status-dropdown-btn') : null;
                const url = btn ? btn.getAttribute('data-url') : null;
                if (!url || !btn) return;

                const tokenEl = document.querySelector('meta[name="csrf-token"]');
                const token = tokenEl ? tokenEl.getAttribute('content') : null;
                if (!token) return;

                fetch(url, {
                    method: 'PATCH',
                    headers: {
                        'X-CSRF-TOKEN': token,
                        'Content-Type': 'application/json',
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify({ status: newStatus })
                })
                .then(r => r.json())
                .then(data => {
                    if (!data.success) return;
                    btn.classList.remove('btn-status-open', 'btn-status-in_process', 'btn-status-done');
                    btn.classList.add('btn-status-' + data.status);
                    const label = btn.querySelector('.js-status-btn-text');
                    if (label) label.textContent = data.status_label || data.status;
                })
                .catch(() => {});
            });
        });

        // Reply modal + CKEditor
        let replyEditorInitialized = false;
        function ensureReplyEditor() {
            if (replyEditorInitialized) return;
            if (typeof CKEDITOR !== 'undefined') {
                CKEDITOR.replace('reply_body');
                replyEditorInitialized = true;
            }
        }

        document.querySelectorAll('.js-reply-btn').forEach(btn => {
            btn.addEventListener('click', function () {
                const id = this.getAttribute('data-id');
                const name = this.getAttribute('data-name') || '';
                const email = this.getAttribute('data-email') || '';
                const subject = this.getAttribute('data-subject') || 'Re: Your booking request';
                const recipient = (name ? name + ' ' : '') + '<' + email + '>';

                const idEl = document.getElementById('reply_booking_id');
                const recEl = document.getElementById('reply_recipient_display');
                const recHiddenEl = document.getElementById('reply_recipient_hidden');
                const subjEl = document.getElementById('reply_subject');

                if (idEl) idEl.value = id || '';
                if (recEl) recEl.value = recipient;
                if (recHiddenEl) recHiddenEl.value = recipient;
                if (subjEl) subjEl.value = subject;

                ensureReplyEditor();
                if (typeof CKEDITOR !== 'undefined' && CKEDITOR.instances.reply_body) {
                    CKEDITOR.instances.reply_body.setData('');
                } else {
                    const bodyEl = document.getElementById('reply_body');
                    if (bodyEl) bodyEl.value = '';
                }
            });
        });

        // History modal
        document.querySelectorAll('.js-history-btn').forEach(btn => {
            btn.addEventListener('click', function () {
                const url = this.getAttribute('data-url');
                const loading = document.getElementById('historyLoading');
                const empty = document.getElementById('historyEmpty');
                const list = document.getElementById('historyList');
                if (!loading || !empty || !list) return;

                loading.classList.remove('d-none');
                empty.classList.add('d-none');
                list.classList.add('d-none');
                list.innerHTML = '';

                fetch(url, { headers: { 'Accept': 'application/json' } })
                    .then(r => r.json())
                    .then(data => {
                        loading.classList.add('d-none');
                        const items = (data && data.items) ? data.items : [];
                        if (!items.length) {
                            empty.classList.remove('d-none');
                            return;
                        }
                        const html = items.map(item => {
                            const body = item.body_html ? item.body_html : '<em>No body saved.</em>';
                            return `
                                <div class="card mb-3">
                                    <div class="card-body">
                                        <div class="d-flex justify-content-between align-items-start gap-3">
                                            <div>
                                                <div class="fw-semibold">${item.subject || ''}</div>
                                                <div class="text-muted small">${item.created_at || ''} · ${item.email || ''}</div>
                                            </div>
                                            <span class="badge bg-light text-dark">#${item.id}</span>
                                        </div>
                                        <hr>
                                        <div>${body}</div>
                                    </div>
                                </div>
                            `;
                        }).join('');
                        list.innerHTML = html;
                        list.classList.remove('d-none');
                    })
                    .catch(() => {
                        loading.classList.add('d-none');
                        empty.textContent = 'Failed to load email history.';
                        empty.classList.remove('d-none');
                    });
            });
        });
    });
</script>
@endsection

