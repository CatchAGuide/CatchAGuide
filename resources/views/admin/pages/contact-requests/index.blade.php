@extends('admin.layouts.app')

@section('title', 'Contact Requests')

@section('custom_style')
<style>
    /* Contact request status row colors */
    #contact-requests-datatable tbody tr.contact-request-status-open { background-color: rgba(13, 110, 253, 0.06); }
    #contact-requests-datatable tbody tr.contact-request-status-in_process { background-color: rgba(255, 193, 7, 0.15); }
    #contact-requests-datatable tbody tr.contact-request-status-done { background-color: rgba(25, 135, 84, 0.08); }
    /* Status dropdown button - single button colored by status */
    .js-status-dropdown-btn.btn-status-open { background-color: #0d6efd; border-color: #0d6efd; color: #fff; }
    .js-status-dropdown-btn.btn-status-open:hover { background-color: #0b5ed7; border-color: #0a58ca; color: #fff; }
    .js-status-dropdown-btn.btn-status-in_process { background-color: #ffc107; border-color: #ffc107; color: #000; }
    .js-status-dropdown-btn.btn-status-in_process:hover { background-color: #e0a800; border-color: #d39e00; color: #000; }
    .js-status-dropdown-btn.btn-status-done { background-color: #198754; border-color: #198754; color: #fff; }
    .js-status-dropdown-btn.btn-status-done:hover { background-color: #157347; border-color: #146c43; color: #fff; }
</style>
@endsection

@section('content')
<div class="side-app">
    <div class="main-container container-fluid">
        <div class="page-header">
            <h1 class="page-title">Contact Requests</h1>
            <div>
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="#">System</a></li>
                    <li class="breadcrumb-item"><a href="#">Admin</a></li>
                    <li class="breadcrumb-item active" aria-current="page">Contact Requests</li>
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

        <div class="row row-sm">
            <div class="col-lg-12">
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">Contact Requests</h3>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-bordered table-striped text-nowrap border-bottom" id="contact-requests-datatable">
                                <thead>
                                    <tr>
                                        <th width="5%" class="border-bottom-0">ID</th>
                                        <th width="20%" class="border-bottom-0">Name</th>
                                        <th width="20%" class="border-bottom-0">Email</th>
                                        <th width="15%" class="border-bottom-0">Phone</th>
                                        <th width="15%" class="border-bottom-0">Source Type</th>
                                        <th width="15%" class="border-bottom-0">Created At</th>
                                        <th width="12%" class="border-bottom-0">Status</th>
                                        <th width="10%" class="border-bottom-0">Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($contactRequests as $request)
                                        @php $rowStatus = $request->status ?? 'open'; @endphp
                                        <tr class="contact-request-row contact-request-status-{{ $rowStatus }}" data-status="{{ $rowStatus }}">
                                            <td>{{ $request->id }}</td>
                                            <td>{{ $request->name ?: '-' }}</td>
                                            <td>{{ $request->email ?: '-' }}</td>
                                            <td>{{ $request->phone ?: '-' }}</td>
                                            <td>{{ $request->source_type ?: '-' }}</td>
                                            <td data-order="{{ optional($request->created_at)->timestamp }}">
                                                {{ optional($request->created_at)->format('F j, Y g:i A') }}
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
                                            <td>
                                                <button
                                                    type="button"
                                                    class="btn btn-sm btn-info js-view-message"
                                                    data-message="{{ e($request->description) }}"
                                                    data-bs-toggle="modal"
                                                    data-bs-target="#messageModal"
                                                >
                                                    <i class="fa fa-eye"></i>
                                                </button>

                                                @if(!empty($request->email))
                                                    <button
                                                        type="button"
                                                        class="btn btn-sm btn-primary js-reply-btn"
                                                        data-id="{{ $request->id }}"
                                                        data-name="{{ e($request->name ?? '') }}"
                                                        data-email="{{ e($request->email) }}"
                                                        data-subject="{{ e('Re: Your contact request') }}"
                                                        data-bs-toggle="modal"
                                                        data-bs-target="#replyModal"
                                                    >
                                                        <i class="fa fa-reply"></i>
                                                    </button>
                                                @endif
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td></td><td></td><td></td><td></td><td></td><td></td><td></td>
                                            <td class="text-center text-muted">No contact requests found</td>
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
            if (CKEDITOR.instances.reply_body) {
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