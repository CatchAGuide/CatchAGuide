@extends('admin.layouts.app')

@section('title', __('message.contact-request'))

@section('custom_style')
<style>
    /* Status dropdown button only – no row background tint */
    .js-status-dropdown-btn.btn-status-open { background-color: #0d6efd; border-color: #0d6efd; color: #fff; }
    .js-status-dropdown-btn.btn-status-open:hover { background-color: #0b5ed7; border-color: #0a58ca; color: #fff; }
    .js-status-dropdown-btn.btn-status-in_process { background-color: #ffc107; border-color: #ffc107; color: #000; }
    .js-status-dropdown-btn.btn-status-in_process:hover { background-color: #e0a800; border-color: #d39e00; color: #000; }
    .js-status-dropdown-btn.btn-status-done { background-color: #198754; border-color: #198754; color: #fff; }
    .js-status-dropdown-btn.btn-status-done:hover { background-color: #157347; border-color: #146c43; color: #fff; }

    .cr-stats { display: flex; flex-wrap: wrap; gap: 1.25rem; margin-bottom: 1.75rem; }
    .cr-stat { background: var(--bs-light, #f8f9fa); border-radius: 8px; padding: 1rem 1.5rem; min-width: 100px; }
    .cr-stat__value { font-size: 1.5rem; font-weight: 600; color: var(--bs-primary, #0d6efd); }
    .cr-stat__label { font-size: 0.8rem; color: #6c757d; text-transform: uppercase; letter-spacing: 0.02em; }

    /* Card padding */
    .cr-card .card-body { padding: 1.25rem 1.5rem; }

    /* Table: clean, uniform rows, more padding */
    #contact-requests-datatable { border-collapse: collapse; }
    #contact-requests-datatable thead th { font-weight: 600; font-size: 0.75rem; text-transform: uppercase; letter-spacing: 0.04em; color: #6c757d; border-bottom: 1px solid #dee2e6; padding: 0.85rem 1rem; white-space: nowrap; }
    #contact-requests-datatable tbody td { padding: 0.85rem 1rem; vertical-align: middle; border-bottom: 1px solid #eee; font-size: 0.9rem; }
    #contact-requests-datatable tbody td:has(.cr-source-cell) { padding: 0.75rem 1rem; }
    #contact-requests-datatable tbody tr:hover { background-color: #f8f9fa; }
    /* Source cell: circle thumbnail + title + location */
    .cr-source-cell { display: flex; align-items: center; gap: 0.75rem; min-height: 3rem; }
    .cr-source-cell__thumb { width: 40px; height: 40px; border-radius: 50%; flex-shrink: 0; background: #e9ecef; }
    .cr-source-cell__thumb--img { object-fit: cover; }
    .cr-source-cell__thumb--placeholder { min-width: 40px; }
    .cr-source-cell__thumb--hide { display: none !important; }
    .cr-source-cell__body { min-width: 0; flex: 1; }
    .cr-source-cell__badge { font-size: 0.65rem; font-weight: 600; padding: 0.1rem 0.35rem; border-radius: 3px; display: inline-block; margin-bottom: 0.2rem; }
    .cr-source-cell__badge--guiding { background: #e7f1ff; color: #0d6efd; }
    .cr-source-cell__badge--vacation { background: #e8f5e9; color: #2e7d32; }
    .cr-source-cell__badge--camp { background: #fff8e1; color: #f57c00; }
    .cr-source-cell__badge--trip { background: #ede7f6; color: #5e35b1; }
    .cr-source-cell__title { font-size: 0.9rem; font-weight: 600; color: #212529; display: block; overflow: hidden; text-overflow: ellipsis; white-space: nowrap; }
    .cr-source-cell__location { font-size: 0.8rem; color: #6c757d; display: block; overflow: hidden; text-overflow: ellipsis; white-space: nowrap; margin-top: 0.1rem; }
    .cr-source-cell__links { display: inline-flex; align-items: center; gap: 0.25rem; flex-shrink: 0; }
    .cr-source-cell__links a { width: 28px; height: 28px; padding: 0; display: inline-flex; align-items: center; justify-content: center; border-radius: 6px; font-size: 0.75rem; color: #495057; border: 1px solid #dee2e6; text-decoration: none; }
    .cr-source-cell__links a:hover { background: #e9ecef; color: #0d6efd; border-color: #adb5bd; }
    .cr-source__none { color: #adb5bd; font-style: italic; font-size: 0.85rem; }
    .cr-row-contact { display: block; max-width: 100%; overflow: hidden; text-overflow: ellipsis; white-space: nowrap; }
    .cr-created { white-space: nowrap; font-size: 0.85rem; color: #6c757d; }
</style>
@endsection

@section('content')
<div class="side-app">
    <div class="main-container container-fluid">
        <div class="page-header">
            <h1 class="page-title">{{ __('message.contact-request') }}</h1>
            <div>
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="{{ route('admin.contact-requests.index') }}">Admin</a></li>
                    <li class="breadcrumb-item active" aria-current="page">{{ __('message.contact-request') }}</li>
                </ol>
            </div>
        </div>

        @if($errors->any())
            <div class="alert alert-danger">
                <ul class="mb-0">
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        @php
            $statsOpen = $contactRequests->where('status', 'open')->count();
            $statsInProcess = $contactRequests->where('status', 'in_process')->count();
            $statsDone = $contactRequests->where('status', 'done')->count();
        @endphp
        <div class="cr-stats">
            <div class="cr-stat">
                <div class="cr-stat__value">{{ $contactRequests->count() }}</div>
                <div class="cr-stat__label">Total</div>
            </div>
            <div class="cr-stat">
                <div class="cr-stat__value">{{ $statsOpen }}</div>
                <div class="cr-stat__label">{{ \App\Models\ContactSubmission::statusOptions()['open'] ?? 'Open' }}</div>
            </div>
            <div class="cr-stat">
                <div class="cr-stat__value">{{ $statsInProcess }}</div>
                <div class="cr-stat__label">{{ \App\Models\ContactSubmission::statusOptions()['in_process'] ?? 'In Process' }}</div>
            </div>
            <div class="cr-stat">
                <div class="cr-stat__value">{{ $statsDone }}</div>
                <div class="cr-stat__label">{{ \App\Models\ContactSubmission::statusOptions()['done'] ?? 'Done' }}</div>
            </div>
        </div>

        <div class="row row-sm">
            <div class="col-12">
                <div class="card shadow-sm border-0 cr-card">
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-hover mb-0" id="contact-requests-datatable">
                                <thead>
                                    <tr>
                                        <th width="4%">ID</th>
                                        <th width="14%">Name</th>
                                        <th width="14%">Email</th>
                                        <th width="10%">Phone</th>
                                        <th width="24%">Source</th>
                                        <th width="12%">Created</th>
                                        <th width="10%">Status</th>
                                        <th width="10%" class="text-end">Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($contactRequests as $request)
                                        @php $rowStatus = $request->status ?? 'open'; @endphp
                                        <tr class="contact-request-row" data-status="{{ $rowStatus }}">
                                            <td class="fw-semibold">#{{ $request->id }}</td>
                                            <td><span class="cr-row-contact" title="{{ e($request->name) }}">{{ $request->name ?: '—' }}</span></td>
                                            <td><span class="cr-row-contact" title="{{ e($request->email) }}">{{ $request->email ?: '—' }}</span></td>
                                            <td><span class="cr-row-contact" title="{{ e($request->phone) }}">{{ $request->phone ?: '—' }}</span></td>
                                            <td>
                                                @if($request->source_type && $request->source_id)
                                                    @php
                                                        $sourceType = strtolower($request->source_type);
                                                        $sourceLabel = \App\Models\ContactSubmission::sourceTypeLabel($request->source_type);
                                                        $frontUrl = $request->getSourceFrontUrl();
                                                        $adminUrl = $request->getSourceAdminUrl();
                                                        $thumbUrl = $request->getSourceThumbnailUrl();
                                                        $sourceTitle = $request->getSourceTitle();
                                                        $sourceLocation = $request->getSourceLocation();
                                                    @endphp
                                                    <div class="cr-source-cell" title="{{ $request->getSourceLabel() }}">
                                                        @if($thumbUrl)
                                                            <img src="{{ $thumbUrl }}" alt="" class="cr-source-cell__thumb cr-source-cell__thumb--img" loading="lazy" onerror="this.style.display='none';this.nextElementSibling&&this.nextElementSibling.classList.remove('cr-source-cell__thumb--hide');">
                                                            <div class="cr-source-cell__thumb cr-source-cell__thumb--placeholder cr-source-cell__thumb--hide" style="display:flex;align-items:center;justify-content:center;color:#adb5bd;font-size:1rem;"><i class="fa fa-image"></i></div>
                                                        @else
                                                            <div class="cr-source-cell__thumb cr-source-cell__thumb--placeholder" style="display:flex;align-items:center;justify-content:center;color:#adb5bd;font-size:1rem;"><i class="fa fa-image"></i></div>
                                                        @endif
                                                        <div class="cr-source-cell__body">
                                                            <span class="cr-source-cell__badge cr-source-cell__badge--{{ $sourceType }}">{{ $sourceLabel }} #{{ $request->source_id }}</span>
                                                            @if($sourceTitle)
                                                                <span class="cr-source-cell__title">{{ \Illuminate\Support\Str::limit($sourceTitle, 32) }}</span>
                                                            @endif
                                                            @if($sourceLocation)
                                                                <span class="cr-source-cell__location">{{ \Illuminate\Support\Str::limit($sourceLocation, 36) }}</span>
                                                            @endif
                                                        </div>
                                                        <span class="cr-source-cell__links">
                                                            @if($frontUrl)
                                                                <a href="{{ $frontUrl }}" target="_blank" rel="noopener" title="View on site"><i class="fa fa-external-link-alt"></i></a>
                                                            @endif
                                                        </span>
                                                    </div>
                                                @else
                                                    <span class="cr-source__none">— General contact</span>
                                                @endif
                                            </td>
                                            <td data-order="{{ optional($request->created_at)->timestamp }}" class="cr-created">
                                                {{ optional($request->created_at)->format('M j, Y g:i A') }}
                                            </td>
                                            <td>
                                                <div class="dropdown">
                                                    <button type="button" class="btn btn-sm dropdown-toggle js-status-dropdown-btn btn-status-{{ $rowStatus }}" data-bs-toggle="dropdown" data-id="{{ $request->id }}" data-url="{{ route('admin.contact-requests.update-status', $request) }}" data-status="{{ $rowStatus }}" data-options='@json(\App\Models\ContactSubmission::statusOptions())' aria-expanded="false">
                                                        <span class="js-status-btn-text">{{ \App\Models\ContactSubmission::statusOptions()[$rowStatus] ?? $rowStatus }}</span>
                                                        <i class="fa fa-caret-down ms-1"></i>
                                                    </button>
                                                    <ul class="dropdown-menu dropdown-menu-end">
                                                        @foreach(\App\Models\ContactSubmission::statusOptions() as $value => $label)
                                                            <li>
                                                                <a class="dropdown-item js-status-option {{ $rowStatus === $value ? 'active' : '' }}" href="#" data-status="{{ $value }}">{{ $label }}</a>
                                                            </li>
                                                        @endforeach
                                                    </ul>
                                                </div>
                                            </td>
                                            <td class="pe-3 text-end">
                                                <div class="btn-group btn-group-sm">
                                                    <button type="button" class="btn btn-outline-info js-view-message" data-message="{{ e($request->description) }}" data-bs-toggle="modal" data-bs-target="#messageModal" title="View message"><i class="fa fa-eye"></i></button>
                                                    @if(!empty($request->email))
                                                        <button type="button" class="btn btn-outline-primary js-reply-btn" data-id="{{ $request->id }}" data-name="{{ e($request->name ?? '') }}" data-email="{{ e($request->email) }}" data-subject="{{ e('Re: Your contact request') }}" data-bs-toggle="modal" data-bs-target="#replyModal" title="Reply"><i class="fa fa-reply"></i></button>
                                                    @endif
                                                </div>
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="8" class="text-center py-5 text-muted">
                                                <i class="fa fa-inbox fa-2x mb-2 d-block"></i>
                                                No contact requests found.
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
                <h5 class="modal-title" id="messageModalLabel">Contact Message</h5>
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
            <form method="POST" action="{{ route('admin.contact-requests.reply') }}" id="replyForm">
                @csrf
                <input type="hidden" name="contact_submission_id" id="reply_contact_submission_id" value="{{ old('contact_submission_id') }}">
                <input type="hidden" name="recipient_display" id="reply_recipient_hidden" value="{{ old('recipient_display') }}">

                <div class="modal-header">
                    <h5 class="modal-title" id="replyModalLabel">Reply to Contact Request</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>

                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Recipient</label>
                        <input type="text" class="form-control" id="reply_recipient_display" value="{{ old('recipient_display') }}" readonly>
                    </div>

                    <div class="mb-3">
                        <label for="reply_subject" class="form-label">Subject</label>
                        <input type="text" class="form-control" id="reply_subject" name="subject" value="{{ old('subject', 'Re: Your contact request') }}" required>
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
@endsection

@section('js_after')
<script>
    $(function () {
        var $table = $('#contact-requests-datatable');
        if ($table.length && $.fn.DataTable) {
            $table.DataTable({
                order: [[5, 'desc']],
                pageLength: 25,
                language: {
                    url: "//cdn.datatables.net/plug-ins/1.13.7/i18n/de-DE.json"
                },
                columnDefs: [
                    { orderable: false, targets: 7 }
                ]
            });
        }

        if (typeof CKEDITOR !== 'undefined' && !CKEDITOR.instances.reply_body) {
            CKEDITOR.replace('reply_body', {
                height: 260
            });
        }

        $('.js-view-message').on('click', function () {
            const message = $(this).data('message') || 'No message provided.';
            $('#messageModalContent').text(message);
        });

        $(document).on('click', '.js-status-option', function (e) {
                e.preventDefault();
                var newStatus = $(this).data('status');
                var $dropdown = $(this).closest('.dropdown');
                var $btn = $dropdown.find('.js-status-dropdown-btn');
                var currentStatus = $btn.data('status');
                if (newStatus === currentStatus) {
                    var d = bootstrap.Dropdown.getOrCreateInstance($btn[0]); if (d) d.hide();
                    return;
                }
                var url = $btn.data('url');
                var options = $btn.data('options');
                var $row = $btn.closest('tr');
                $.ajax({
                    url: url,
                    method: 'PATCH',
                    data: { status: newStatus, _token: '{{ csrf_token() }}' },
                    headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json' },
                    success: function (res) {
                        var status = res.status || newStatus;
                        var label = (res.status_label) ? res.status_label : (options && options[status]) ? options[status] : status;
                        $btn.removeClass('btn-status-open btn-status-in_process btn-status-done').addClass('btn-status-' + status).data('status', status);
                        $btn.find('.js-status-btn-text').text(label);
                        $row.removeClass('contact-request-status-open contact-request-status-in_process contact-request-status-done').addClass('contact-request-status-' + status).attr('data-status', status);
                        $dropdown.find('.dropdown-item').removeClass('active').filter('[data-status="' + status + '"]').addClass('active');
                        $row.addClass('table-success');
                        setTimeout(function () { $row.removeClass('table-success'); }, 600);
                        var dd = bootstrap.Dropdown.getOrCreateInstance($btn[0]); if (dd) dd.hide();
                    },
                    error: function (xhr) {
                        var msg = (xhr.responseJSON && xhr.responseJSON.message) ? xhr.responseJSON.message : 'Failed to update status.';
                        alert(msg);
                    }
                });
            });

        $('.js-reply-btn').on('click', function () {
            const id = $(this).data('id');
            const name = $(this).data('name') || '';
            const email = $(this).data('email') || '';
            const subject = $(this).data('subject') || 'Re: Your contact request';
            const recipient = name ? `${name} <${email}>` : email;

            $('#reply_contact_submission_id').val(id);
            $('#reply_recipient_display').val(recipient);
            $('#reply_recipient_hidden').val(recipient);
            $('#reply_subject').val(subject);
        });

        $('#replyForm').on('submit', function () {
            if (typeof CKEDITOR !== 'undefined' && CKEDITOR.instances.reply_body) {
                CKEDITOR.instances.reply_body.updateElement();
            }
        });

        @if($errors->any() && old('contact_submission_id'))
            const previousReplyModal = new bootstrap.Modal(document.getElementById('replyModal'));
            previousReplyModal.show();
        @endif
    });
</script>
@endsection
