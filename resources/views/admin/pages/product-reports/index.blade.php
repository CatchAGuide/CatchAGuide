@extends('admin.layouts.app')

@section('title', __('message.product-reports'))

@section('custom_style')
<style>
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
    .cr-card .card-body { padding: 1.25rem 1.5rem; }
    #product-reports-datatable { border-collapse: collapse; }
    #product-reports-datatable thead th { font-weight: 600; font-size: 0.75rem; text-transform: uppercase; letter-spacing: 0.04em; color: #6c757d; border-bottom: 1px solid #dee2e6; padding: 0.85rem 1rem; white-space: nowrap; }
    #product-reports-datatable tbody td { padding: 0.85rem 1rem; vertical-align: middle; border-bottom: 1px solid #eee; font-size: 0.9rem; }
    #product-reports-datatable tbody tr:hover { background-color: #f8f9fa; }
    .cr-row-contact { display: block; max-width: 100%; overflow: hidden; text-overflow: ellipsis; white-space: nowrap; }
    .cr-created { white-space: nowrap; font-size: 0.85rem; color: #6c757d; }
    .cr-source__none { color: #adb5bd; font-style: italic; font-size: 0.85rem; }
</style>
@endsection

@section('content')
<div class="side-app">
    <div class="main-container container-fluid">
        <div class="page-header">
            <h1 class="page-title">{{ __('message.product-reports') }}</h1>
            <div>
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="{{ route('admin.product-reports.index') }}">Admin</a></li>
                    <li class="breadcrumb-item active" aria-current="page">{{ __('message.product-reports') }}</li>
                </ol>
            </div>
        </div>

        @php
            $statsOpen = $productReports->where('status', 'open')->count();
            $statsInProcess = $productReports->where('status', 'in_process')->count();
            $statsDone = $productReports->where('status', 'done')->count();
        @endphp
        <div class="cr-stats">
            <div class="cr-stat">
                <div class="cr-stat__value">{{ $productReports->count() }}</div>
                <div class="cr-stat__label">Total</div>
            </div>
            <div class="cr-stat">
                <div class="cr-stat__value">{{ $statsOpen }}</div>
                <div class="cr-stat__label">{{ \App\Models\ProductReport::statusOptions()['open'] ?? 'Open' }}</div>
            </div>
            <div class="cr-stat">
                <div class="cr-stat__value">{{ $statsInProcess }}</div>
                <div class="cr-stat__label">{{ \App\Models\ProductReport::statusOptions()['in_process'] ?? 'In Process' }}</div>
            </div>
            <div class="cr-stat">
                <div class="cr-stat__value">{{ $statsDone }}</div>
                <div class="cr-stat__label">{{ \App\Models\ProductReport::statusOptions()['done'] ?? 'Done' }}</div>
            </div>
        </div>

        <div class="row row-sm">
            <div class="col-12">
                <div class="card shadow-sm border-0 cr-card">
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-hover mb-0" id="product-reports-datatable">
                                <thead>
                                    <tr>
                                        <th width="4%">ID</th>
                                        <th width="12%">Name</th>
                                        <th width="14%">Email</th>
                                        <th width="12%">Reason</th>
                                        <th width="20%">Source / URL</th>
                                        <th width="12%">Created</th>
                                        <th width="10%">Status</th>
                                        <th width="10%" class="text-end">Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($productReports as $report)
                                        @php $rowStatus = $report->status ?? 'open'; @endphp
                                        <tr data-status="{{ $rowStatus }}">
                                            <td class="fw-semibold">#{{ $report->id }}</td>
                                            <td><span class="cr-row-contact" title="{{ e($report->name) }}">{{ $report->name ?: '—' }}</span></td>
                                            <td><span class="cr-row-contact" title="{{ e($report->email) }}">{{ $report->email ?: '—' }}</span></td>
                                            <td>{{ $report->reason_label }}</td>
                                            <td>
                                                @php
                                                    $sourceLabel = $report->getSourceLabel();
                                                    $frontUrl = $report->getSourceFrontUrl();
                                                    $adminUrl = $report->getSourceAdminUrl();
                                                @endphp
                                                @if($sourceLabel)
                                                    <div class="mb-1">{{ \Illuminate\Support\Str::limit($sourceLabel, 48) }}</div>
                                                @elseif($report->reported_url)
                                                    <span class="cr-source__none">General report</span>
                                                @else
                                                    <span class="cr-source__none">—</span>
                                                @endif
                                                <div class="d-flex gap-2 mt-1">
                                                    @if($frontUrl)
                                                        <a href="{{ $frontUrl }}" target="_blank" rel="noopener" title="View on site"><i class="fa fa-external-link-alt"></i></a>
                                                    @endif
                                                    @if($adminUrl)
                                                        <a href="{{ $adminUrl }}" target="_blank" rel="noopener" title="Open in admin"><i class="fa fa-edit"></i></a>
                                                    @endif
                                                </div>
                                            </td>
                                            <td data-order="{{ optional($report->created_at)->timestamp }}" class="cr-created">
                                                {{ optional($report->created_at)->format('M j, Y g:i A') }}
                                            </td>
                                            <td>
                                                <div class="dropdown">
                                                    <button type="button" class="btn btn-sm dropdown-toggle js-status-dropdown-btn btn-status-{{ $rowStatus }}" data-bs-toggle="dropdown" data-id="{{ $report->id }}" data-url="{{ route('admin.product-reports.update-status', $report) }}" data-status="{{ $rowStatus }}" data-options='@json(\App\Models\ProductReport::statusOptions())' aria-expanded="false">
                                                        <span class="js-status-btn-text">{{ \App\Models\ProductReport::statusOptions()[$rowStatus] ?? $rowStatus }}</span>
                                                        <i class="fa fa-caret-down ms-1"></i>
                                                    </button>
                                                    <ul class="dropdown-menu dropdown-menu-end">
                                                        @foreach(\App\Models\ProductReport::statusOptions() as $value => $label)
                                                            <li>
                                                                <a class="dropdown-item js-status-option {{ $rowStatus === $value ? 'active' : '' }}" href="#" data-status="{{ $value }}">{{ $label }}</a>
                                                            </li>
                                                        @endforeach
                                                    </ul>
                                                </div>
                                            </td>
                                            <td class="pe-3 text-end">
                                                <div class="btn-group btn-group-sm">
                                                    <button type="button" class="btn btn-outline-info js-view-message" data-message="{{ e($report->description) }}" data-bs-toggle="modal" data-bs-target="#messageModal" title="View description"><i class="fa fa-eye"></i></button>
                                                    <button type="button"
                                                            class="btn {{ $report->admin_comment ? 'btn-warning' : 'btn-outline-secondary' }}"
                                                            onclick="showProductReportCommentModal({{ $report->id }})"
                                                            title="Admin comment (internal)">
                                                        <i class="fa fa-comment"></i>
                                                    </button>
                                                </div>
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="8" class="text-center py-5 text-muted">
                                                <i class="fa fa-inbox fa-2x mb-2 d-block"></i>
                                                No product reports found.
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
                <h5 class="modal-title" id="messageModalLabel">Report description</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div id="messageModalContent" style="white-space: pre-wrap; word-break: break-word;"></div>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="productReportCommentModal" tabindex="-1" aria-labelledby="productReportCommentModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="productReportCommentModalLabel">Comment</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <input type="hidden" id="product-report-comment-id" value="">
                <label for="product-report-comment-text" class="form-label">Internal notes (plain text)</label>
                <textarea class="form-control" id="product-report-comment-text" rows="6" placeholder="Internal notes for the team…"></textarea>
                <p class="form-text text-muted small mb-0">Not sent to guests.</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-light" data-bs-dismiss="modal">Close</button>
                <button type="button" class="btn btn-primary" onclick="saveProductReportComment()">Save</button>
            </div>
        </div>
    </div>
</div>
@endsection

@section('js_after')
<script>
    function productReportCommentUrl(id) {
        return `{{ url('/admin/product-reports') }}/${id}/comment`;
    }

    function showProductReportCommentModal(id) {
        fetch(productReportCommentUrl(id), {
            headers: {
                'Accept': 'application/json',
                'X-Requested-With': 'XMLHttpRequest'
            }
        })
        .then(function (r) { return r.json(); })
        .then(function (data) {
            document.getElementById('product-report-comment-id').value = data.id;
            document.getElementById('product-report-comment-text').value = data.admin_comment || '';
            new bootstrap.Modal(document.getElementById('productReportCommentModal')).show();
        })
        .catch(function () { alert('Failed to load comment.'); });
    }

    function saveProductReportComment() {
        var id = document.getElementById('product-report-comment-id').value;
        var text = document.getElementById('product-report-comment-text').value;
        var token = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
        fetch(productReportCommentUrl(id), {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'X-Requested-With': 'XMLHttpRequest',
                'X-CSRF-TOKEN': token
            },
            body: JSON.stringify({ admin_comment: text })
        })
        .then(function (r) {
            if (!r.ok) return r.text().then(function (t) { throw new Error(t || r.status); });
            return r.json();
        })
        .then(function (data) {
            if (data && data.success) {
                var modalEl = document.getElementById('productReportCommentModal');
                var inst = bootstrap.Modal.getInstance(modalEl);
                if (inst) inst.hide();
                window.location.reload();
            }
        })
        .catch(function () { alert('Failed to save comment.'); });
    }

    $(function () {
        var $table = $('#product-reports-datatable');
        if ($table.length && $.fn.DataTable) {
            $table.DataTable({
                order: [[5, 'desc']],
                pageLength: 25
            });
        }

        $(document).on('click', '.js-view-message', function () {
            document.getElementById('messageModalContent').textContent = $(this).data('message') || '';
        });

        $(document).on('click', '.js-status-option', function (e) {
            e.preventDefault();
            var $option = $(this);
            var $btn = $option.closest('.dropdown').find('.js-status-dropdown-btn');
            var status = $option.data('status');
            var url = $btn.data('url');
            var token = $('meta[name="csrf-token"]').attr('content');

            fetch(url, {
                method: 'PATCH',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest',
                    'X-CSRF-TOKEN': token
                },
                body: JSON.stringify({ status: status })
            })
            .then(function (r) { return r.json(); })
            .then(function (data) {
                if (!data || !data.success) {
                    throw new Error('Status update failed');
                }
                $btn
                    .removeClass('btn-status-open btn-status-in_process btn-status-done')
                    .addClass('btn-status-' + data.status)
                    .attr('data-status', data.status);
                $btn.find('.js-status-btn-text').text(data.status_label || data.status);
                $option.closest('.dropdown-menu').find('.js-status-option').removeClass('active');
                $option.addClass('active');
            })
            .catch(function () { alert('Failed to update status.'); });
        });
    });
</script>
@endsection
