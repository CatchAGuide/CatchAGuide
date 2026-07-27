@extends('admin.layouts.app')

@section('title', 'Email Logs')

@section('content')
    <div class="side-app">

        <!-- CONTAINER -->
        <div class="main-container container-fluid">

            <!-- PAGE-HEADER -->
            <div class="page-header">
                <h1 class="page-title">Email Logs</h1>
                <div>
                    <ol class="breadcrumb">
                        <li class="breadcrumb-item"><a href="#">System</a></li>
                        <li class="breadcrumb-item"><a href="#">Admin</a></li>
                        <li class="breadcrumb-item active" aria-current="page">Email Logs</li>
                    </ol>
                </div>
            </div>
            <!-- PAGE-HEADER END -->

            <!-- Row -->
            <div class="row row-sm">
                <div class="col-lg-12">
                    <div class="card">
                        <div class="card-header">
                            <h3 class="card-title">Email Logs</h3>
                        </div>
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table table-bordered table-striped text-nowrap border-bottom" id="email-logs-datatable">
                                    <thead>
                                        <tr>
                                            <th width="5%" class="border-bottom-0">ID</th>
                                            <th width="15%" class="border-bottom-0">Email</th>
                                            <th width="20%" class="border-bottom-0">Subject</th>
                                            <th width="10%" class="border-bottom-0">Type</th>
                                            <th width="8%" class="border-bottom-0 text-center">Language</th>
                                            <th width="10%" class="border-bottom-0">Target</th>
                                            <th width="12%" class="border-bottom-0">Created At</th>
                                            <th width="8%" class="border-bottom-0 text-center">Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @forelse($emailLogs as $log)
                                            <tr>
                                                <td>{{ $log->id }}</td>
                                                <td>{{ $log->email }}</td>
                                                <td>{{ $log->subject }}</td>
                                                <td>{{ $log->type }}</td>
                                                <td class="text-center" data-order="{{ $log->normalized_language }}">
                                                    @if($log->language_flag_code)
                                                        <i class="fi fi-{{ $log->language_flag_code }}" title="{{ strtoupper($log->normalized_language) }}"></i>
                                                    @else
                                                        <span>{{ $log->language ?: '—' }}</span>
                                                    @endif
                                                </td>
                                                <td>{{ $log->target }}</td>
                                                <td data-order="{{ optional($log->created_at)->timestamp }}">
                                                    {{ optional($log->created_at)->format('F j, Y g:i A') }}
                                                </td>
                                                <td class="text-center">
                                                    <button
                                                        type="button"
                                                        class="btn btn-sm btn-info js-view-email-log"
                                                        data-url="{{ route('admin.email-logs.show', $log) }}"
                                                        title="View email template"
                                                    >
                                                        <i class="fa fa-eye"></i>
                                                    </button>
                                                </td>
                                            </tr>
                                        @empty
                                            <tr>
                                                <td colspan="8" class="text-center">No email logs found</td>
                                            </tr>
                                        @endforelse
                                    </tbody>
                                </table>
                                @if(method_exists($emailLogs, 'links'))
                                    {{ $emailLogs->links() }}
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <!-- End Row -->
        </div>
        <!-- CONTAINER CLOSED -->

    </div>

    <div class="modal fade" id="emailLogPreviewModal" tabindex="-1" aria-labelledby="emailLogPreviewModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-xl modal-dialog-scrollable">
            <div class="modal-content">
                <div class="modal-header">
                    <div>
                        <h5 class="modal-title" id="emailLogPreviewModalLabel">Sent email</h5>
                        <div class="small text-muted" id="emailLogPreviewMeta"></div>
                    </div>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body p-0">
                    <div id="emailLogPreviewRerenderBanner" class="alert alert-info mb-0 rounded-0 d-none">
                        Showing a live re-render with the real booking/recipient data from this log (exact HTML was not stored when this email was sent). New emails store the exact sent body.
                    </div>
                    <div id="emailLogPreviewLoading" class="text-center py-5">
                        <div class="spinner-border text-primary" role="status"></div>
                        <p class="mt-2 text-muted mb-0">Loading email…</p>
                    </div>
                    <div id="emailLogPreviewError" class="alert alert-danger m-3 d-none"></div>
                    <iframe id="emailLogPreviewFrame" title="Email preview" style="width:100%; min-height:70vh; border:0;" class="d-none"></iframe>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('js_after')
<script>
    $(function () {
        var $table = $('#email-logs-datatable');
        if ($table.length && $.fn.DataTable) {
            $table.DataTable({
                language: {
                    url: "//cdn.datatables.net/plug-ins/1.13.7/i18n/de-DE.json"
                },
                pagingType: "full_numbers",
                responsive: true,
                autoWidth: false,
                order: [[6, 'desc']],
                columnDefs: [
                    { targets: 0, type: 'num' },
                    { targets: 6, type: 'num' },
                    { targets: -1, orderable: false }
                ]
            });
        }

        var $modal = $('#emailLogPreviewModal');
        var $meta = $('#emailLogPreviewMeta');
        var $banner = $('#emailLogPreviewRerenderBanner');
        var $loading = $('#emailLogPreviewLoading');
        var $error = $('#emailLogPreviewError');
        var $frame = $('#emailLogPreviewFrame');

        function resetPreview() {
            $meta.text('');
            $banner.addClass('d-none');
            $error.addClass('d-none').text('');
            $loading.removeClass('d-none');
            $frame.addClass('d-none').removeAttr('srcdoc').removeAttr('src');
        }

        $(document).on('click', '.js-view-email-log', function () {
            var url = $(this).data('url');
            resetPreview();

            var modal = bootstrap.Modal.getOrCreateInstance($modal[0]);
            modal.show();

            fetch(url, {
                headers: {
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest'
                }
            })
                .then(function (response) {
                    return response.json().then(function (data) {
                        return { ok: response.ok, data: data };
                    });
                })
                .then(function (result) {
                    var data = result.data || {};
                    $loading.addClass('d-none');

                    var metaParts = [];
                    if (data.subject) metaParts.push(data.subject);
                    if (data.email) metaParts.push(data.email);
                    if (data.type) metaParts.push(data.type);
                    if (data.created_at) metaParts.push(data.created_at);
                    if (data.source === 'stored') metaParts.push('exact sent body');
                    if (data.source === 'rerendered') metaParts.push('live re-render');
                    $meta.text(metaParts.join(' · '));
                    $('#emailLogPreviewModalLabel').text(data.subject || 'Sent email');

                    if (data.html) {
                        if (data.source === 'rerendered') {
                            $banner.removeClass('d-none');
                        }
                        $frame.removeClass('d-none').attr('srcdoc', data.html);
                        return;
                    }

                    $error.removeClass('d-none').text(
                        data.error || 'Could not reconstruct this email with the real booking data.'
                    );
                })
                .catch(function () {
                    $loading.addClass('d-none');
                    $error.removeClass('d-none').text('Could not load this email log. Please try again.');
                });
        });

        $modal.on('hidden.bs.modal', function () {
            resetPreview();
        });
    });
</script>
@endpush
