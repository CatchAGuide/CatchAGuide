@extends('admin.layouts.app')

@section('title', __('admin.voucher_orders.title'))

@section('custom_style')
<style>
    #voucher-orders-table thead th {
        font-weight: 600;
        font-size: 0.75rem;
        text-transform: uppercase;
        letter-spacing: 0.04em;
        color: #6c757d;
        border-bottom: 1px solid #dee2e6;
        padding: 0.85rem 0.75rem;
        white-space: nowrap;
    }
    #voucher-orders-table tbody td {
        padding: 0.85rem 0.75rem;
        vertical-align: middle;
        border-bottom: 1px solid #eee;
        font-size: 0.9rem;
    }
    #voucher-orders-table tbody tr:hover { background-color: #f8f9fa; }

    .vo-muted { font-size: 0.8rem; color: #6c757d; }
    .vo-ellipsis { display: block; max-width: 220px; overflow: hidden; text-overflow: ellipsis; white-space: nowrap; }
    .vo-code { font-family: SFMono-Regular, Menlo, Consolas, monospace; font-size: 0.85rem; }
    .vo-kind { font-size: 0.7rem; font-weight: 600; padding: 0.15rem 0.45rem; border-radius: 999px; display: inline-block; }
    .vo-kind--order { background: #e7f1ff; color: #1d4ed8; }
    .vo-kind--request { background: #fde8e4; color: #b4412e; }
    /* Request status button, same colours as the other request lists. */
    .js-status-dropdown-btn.btn-status-open { background-color: #0d6efd; border-color: #0d6efd; color: #fff; }
    .js-status-dropdown-btn.btn-status-open:hover { background-color: #0b5ed7; border-color: #0a58ca; color: #fff; }
    .js-status-dropdown-btn.btn-status-in_process { background-color: #ffc107; border-color: #ffc107; color: #000; }
    .js-status-dropdown-btn.btn-status-in_process:hover { background-color: #e0a800; border-color: #d39e00; color: #000; }
    .js-status-dropdown-btn.btn-status-done { background-color: #198754; border-color: #198754; color: #fff; }
    .js-status-dropdown-btn.btn-status-done:hover { background-color: #157347; border-color: #146c43; color: #fff; }
    .vo-status-cell { min-width: 9.5rem; }
    .vo-badge { font-size: 0.65rem; font-weight: 600; padding: 0.1rem 0.35rem; border-radius: 3px; display: inline-block; background: #fef3c7; color: #92400e; }
    .meta-pill { display:inline-flex; align-items:center; gap:.35rem; padding:.2rem .5rem; border:1px solid #dee2e6; border-radius:999px; font-size:.8rem; color:#495057; background:#fff; }
    .meta-pill i { color:#6c757d; }

    /* Mobile: stacked cards instead of a wide horizontal-scroll table. */
    @media (max-width: 767.98px) {
        #voucher-orders-table thead { position: absolute; width: 1px; height: 1px; overflow: hidden; clip: rect(0, 0, 0, 0); border: 0; }
        #voucher-orders-table, #voucher-orders-table tbody, #voucher-orders-table tr { display: block; width: 100%; }
        #voucher-orders-table tr {
            margin-bottom: 1rem;
            border: 1px solid #e9ecef;
            border-radius: 0.5rem;
            padding: 0.5rem 0.75rem;
            box-shadow: 0 1px 3px rgba(15, 23, 42, 0.06);
        }
        #voucher-orders-table td {
            display: flex;
            justify-content: space-between;
            gap: 0.75rem;
            padding: 0.5rem 0.1rem;
            border-bottom: 1px solid #f1f3f5;
            text-align: right;
        }
        #voucher-orders-table tr td:last-child { border-bottom: none; }
        #voucher-orders-table td::before {
            content: attr(data-label);
            flex-shrink: 0;
            font-weight: 600;
            font-size: 0.72rem;
            text-transform: uppercase;
            letter-spacing: 0.04em;
            color: #6c757d;
            text-align: left;
        }
        #voucher-orders-table td.vo-empty::before { display: none; }
        .vo-ellipsis { max-width: 60vw; }
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

        <p class="text-muted mb-3">{{ __('admin.voucher_orders.intro') }}</p>

        <div class="row row-sm mb-3">
            <div class="col-12">
                <div class="d-flex flex-wrap gap-3 align-items-center justify-content-between">
                    <div class="d-flex flex-wrap gap-2">
                        <div class="btn-group btn-group-sm" role="group" aria-label="{{ __('admin.voucher_orders.col_kind') }}">
                            @foreach(['' => 'filter_all', 'order' => 'filter_orders', 'request' => 'filter_requests'] as $value => $label)
                                <a href="{{ route('admin.voucher-orders.index', array_filter(['kind' => $value, 'q' => $search])) }}"
                                   class="btn {{ ($kind?->value ?? '') === $value ? 'btn-primary' : 'btn-outline-primary' }}">{{ __('admin.voucher_orders.'.$label) }}</a>
                            @endforeach
                        </div>
                        @if($kind?->value === 'request')
                            {{-- Second click on the active status clears it. --}}
                            <div class="btn-group btn-group-sm" role="group" aria-label="{{ __('admin.voucher_orders.col_status') }}">
                                @foreach($statusOptions as $value => $label)
                                    <a href="{{ route('admin.voucher-orders.index', array_filter(['kind' => 'request', 'status' => $status === $value ? null : $value, 'q' => $search])) }}"
                                       class="btn {{ $status === $value ? 'btn-secondary' : 'btn-outline-secondary' }}">{{ $label }}</a>
                                @endforeach
                            </div>
                        @endif
                    </div>
                    <div class="d-flex flex-wrap gap-3">
                        <span class="meta-pill"><i class="fa fa-gift"></i> {{ __('admin.voucher_orders.total') }}: <strong>{{ (int) $totals->orders }}</strong></span>
                        <span class="meta-pill"><i class="fa fa-euro-sign"></i> {{ __('admin.voucher_orders.volume') }}: <strong>{{ number_format((float) $totals->volume, 2, ',', '.') }} €</strong></span>
                        <span class="meta-pill"><i class="fa fa-percent"></i> {{ __('admin.voucher_orders.commission') }}: <strong>{{ number_format((float) $totals->commission, 2, ',', '.') }} €</strong></span>
                    </div>
                    <form method="GET" action="{{ route('admin.voucher-orders.index') }}" class="d-flex gap-2 ms-auto">
                        @if($kind)
                            <input type="hidden" name="kind" value="{{ $kind->value }}">
                        @endif
                        @if($status)
                            <input type="hidden" name="status" value="{{ $status }}">
                        @endif
                        <input type="search" name="q" value="{{ $search }}" class="form-control form-control-sm" placeholder="{{ __('admin.voucher_orders.search_placeholder') }}" aria-label="{{ __('admin.voucher_orders.search') }}">
                        <button type="submit" class="btn btn-sm btn-primary">{{ __('admin.voucher_orders.search') }}</button>
                        @if($search !== '')
                            <a href="{{ route('admin.voucher-orders.index', array_filter(['kind' => $kind?->value, 'status' => $status])) }}" class="btn btn-sm btn-outline-secondary">{{ __('admin.voucher_orders.reset') }}</a>
                        @endif
                    </form>
                </div>
            </div>
        </div>

        <div class="row row-sm">
            <div class="col-12">
                <div class="card shadow-sm border-0">
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-hover mb-0" id="voucher-orders-table">
                                <thead>
                                    <tr>
                                        <th>{{ __('admin.voucher_orders.col_code') }}</th>
                                        <th>{{ __('admin.voucher_orders.col_kind') }}</th>
                                        <th>{{ __('admin.voucher_orders.col_status') }}</th>
                                        <th>{{ __('admin.voucher_orders.col_buyer') }}</th>
                                        <th>{{ __('admin.voucher_orders.col_contact') }}</th>
                                        <th>{{ __('admin.voucher_orders.col_tour') }}</th>
                                        <th>{{ __('admin.voucher_orders.col_guide') }}</th>
                                        <th>{{ __('admin.voucher_orders.col_guests') }}</th>
                                        <th class="text-end">{{ __('admin.voucher_orders.col_price') }}</th>
                                        <th class="text-end">{{ __('admin.voucher_orders.col_commission') }}</th>
                                        <th>{{ __('admin.voucher_orders.col_valid_until') }}</th>
                                        <th>{{ __('admin.voucher_orders.col_ordered') }}</th>
                                        <th>{{ __('admin.voucher_orders.col_commission_paid') }}</th>
                                        <th class="text-end">{{ __('admin.voucher_orders.col_actions') }}</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($orders as $order)
                                        @php
                                            $isRequest = $order->isRequest();
                                            $commissionPaid = $order->financeItem?->paid_status === 'paid';
                                        @endphp
                                        <tr>
                                            <td data-label="{{ __('admin.voucher_orders.col_code') }}">
                                                <span class="vo-code fw-semibold">{{ $order->voucher_code }}</span>
                                                <div class="vo-muted">#{{ $order->id }}</div>
                                            </td>
                                            <td data-label="{{ __('admin.voucher_orders.col_kind') }}">
                                                <span class="vo-kind vo-kind--{{ $order->kind->value }}">{{ __('admin.voucher_orders.kind_'.$order->kind->value) }}</span>
                                            </td>
                                            <td data-label="{{ __('admin.voucher_orders.col_status') }}" class="vo-status-cell">
                                                @if($isRequest)
                                                    @include('admin.partials.request-status-dropdown', [
                                                        'item' => $order,
                                                        'updateUrl' => route('admin.voucher-orders.update-status', $order),
                                                        'historyUrl' => route('admin.voucher-orders.status-history', $order),
                                                        'summary' => $statusHistory->summary($order),
                                                    ])
                                                @else
                                                    <span class="vo-muted">&mdash;</span>
                                                @endif
                                            </td>
                                            <td data-label="{{ __('admin.voucher_orders.col_buyer') }}">
                                                <span class="vo-ellipsis">{{ $order->buyerName() ?: '—' }}</span>
                                                @if($order->recipient_names)
                                                    <span class="vo-muted vo-ellipsis" title="{{ $order->recipient_names }}">{{ __('admin.voucher_orders.recipients', ['names' => $order->recipient_names]) }}</span>
                                                @endif
                                            </td>
                                            <td data-label="{{ __('admin.voucher_orders.col_contact') }}">
                                                <a href="mailto:{{ $order->buyer_email }}" class="vo-ellipsis" title="{{ $order->buyer_email }}">{{ $order->buyer_email }}</a>
                                                <span class="vo-muted">{{ $order->buyerPhone() ?: '—' }}</span>
                                            </td>
                                            <td data-label="{{ __('admin.voucher_orders.col_tour') }}">
                                                @if($isRequest)
                                                    <span class="vo-ellipsis">{{ __('admin.voucher_orders.request_tour') }}</span>
                                                    <span class="vo-muted vo-ellipsis" title="{{ $order->requestDestination() }}">{{ $order->requestDestination() ?: __('admin.voucher_orders.request_open') }}</span>
                                                @else
                                                    <a href="{{ route('admin.guidings.edit', $order->guiding_id) }}" class="vo-ellipsis" title="{{ $order->tour_title_snapshot }}">{{ $order->tour_title_snapshot }}</a>
                                                    <span class="vo-badge">{{ __('admin.voucher_orders.design_'.($order->design_type?->value ?? 'cag_standard')) }}</span>
                                                @endif
                                            </td>
                                            <td data-label="{{ __('admin.voucher_orders.col_guide') }}">
                                                @if($order->guide)
                                                    <a href="{{ route('admin.guides.edit', $order->guide) }}" class="vo-ellipsis">{{ $order->guide->full_name }}</a>
                                                @else
                                                    —
                                                @endif
                                            </td>
                                            <td data-label="{{ __('admin.voucher_orders.col_guests') }}">{{ $order->number_of_guests ?? '—' }}</td>
                                            <td data-label="{{ __('admin.voucher_orders.col_price') }}" class="text-end text-nowrap">{{ number_format((float) $order->price_total, 2, ',', '.') }} {{ $order->currency }}</td>
                                            <td data-label="{{ __('admin.voucher_orders.col_commission') }}" class="text-end text-nowrap">
                                                @if($isRequest)
                                                    —
                                                @else
                                                    {{ number_format((float) $order->commission_amount, 2, ',', '.') }} {{ $order->currency }}
                                                    <div class="vo-muted">{{ rtrim(rtrim(number_format((float) $order->commission_rate * 100, 2, ',', '.'), '0'), ',') }} %</div>
                                                @endif
                                            </td>
                                            <td data-label="{{ __('admin.voucher_orders.col_valid_until') }}" class="text-nowrap">{{ $order->valid_until?->format('Y-m-d') ?? '—' }}</td>
                                            <td data-label="{{ __('admin.voucher_orders.col_ordered') }}" class="text-nowrap vo-muted">{{ $order->created_at?->format('M j, Y g:i A') }}</td>
                                            <td data-label="{{ __('admin.voucher_orders.col_commission_paid') }}">
                                                @if($isRequest)
                                                    —
                                                @else
                                                    <span class="badge {{ $commissionPaid ? 'bg-success' : 'bg-secondary' }}">{{ $commissionPaid ? __('admin.voucher_orders.paid') : __('admin.voucher_orders.unpaid') }}</span>
                                                @endif
                                            </td>
                                            <td data-label="{{ __('admin.voucher_orders.col_actions') }}" class="text-end text-nowrap">
                                                <div class="btn-group btn-group-sm">
                                                    <button type="button"
                                                            class="btn {{ $order->admin_comment ? 'btn-warning' : 'btn-outline-secondary' }} js-voucher-comment"
                                                            data-url="{{ route('admin.voucher-orders.comment.show', $order) }}"
                                                            title="{{ __('admin.voucher_orders.comment') }}"
                                                            aria-label="{{ __('admin.voucher_orders.comment') }}">
                                                        <i class="fa fa-sticky-note"></i>
                                                    </button>
                                                    @if($order->personal_message)
                                                        <button type="button"
                                                                class="btn btn-outline-info"
                                                                data-bs-toggle="popover"
                                                                data-bs-trigger="focus"
                                                                data-bs-placement="left"
                                                                data-bs-content="{{ $order->personal_message }}"
                                                                title="{{ __('admin.voucher_orders.view_message') }}"
                                                                aria-label="{{ __('admin.voucher_orders.view_message') }}">
                                                            <i class="fa fa-comment"></i>
                                                        </button>
                                                    @endif
                                                    @if($order->design_type?->generatesPdf())
                                                        <a href="{{ route('admin.voucher-orders.pdf', $order) }}"
                                                           class="btn btn-outline-primary"
                                                           title="{{ __('admin.voucher_orders.download_pdf') }}"
                                                           aria-label="{{ __('admin.voucher_orders.download_pdf') }}">
                                                            <i class="fa fa-file-pdf"></i>
                                                        </a>
                                                    @endif
                                                </div>
                                            </td>
                                        </tr>
                                    @empty
                                        <tr><td colspan="14" class="vo-empty text-center text-muted py-4">{{ __('admin.voucher_orders.empty') }}</td></tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>

                        <div class="mt-3">
                            {{ $orders->links() }}
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="voucherCommentModal" tabindex="-1" aria-labelledby="voucherCommentModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="voucherCommentModalLabel">{{ __('admin.voucher_orders.comment') }}</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="{{ __('admin.voucher_orders.comment_close') }}"></button>
            </div>
            <div class="modal-body">
                <label for="voucher-comment-text" class="form-label">{{ __('admin.voucher_orders.comment_label') }}</label>
                <textarea class="form-control" id="voucher-comment-text" rows="6" placeholder="{{ __('admin.voucher_orders.comment_placeholder') }}"></textarea>
                <p class="form-text text-muted small mb-0">{{ __('admin.voucher_orders.comment_hint') }}</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-light" data-bs-dismiss="modal">{{ __('admin.voucher_orders.comment_close') }}</button>
                <button type="button" class="btn btn-primary" id="voucher-comment-save">{{ __('admin.voucher_orders.comment_save') }}</button>
            </div>
        </div>
    </div>
</div>
@include('admin.partials.booking-status-history')
@endsection

@section('js_after')
<script>
    document.querySelectorAll('#voucher-orders-table [data-bs-toggle="popover"]').forEach(function (el) {
        new bootstrap.Popover(el);
    });

    (function () {
        var modalEl = document.getElementById('voucherCommentModal');
        var textEl = document.getElementById('voucher-comment-text');
        var text = {{ Js::from([
            'load_failed' => __('admin.voucher_orders.comment_load_failed'),
            'save_failed' => __('admin.voucher_orders.comment_save_failed'),
        ]) }};
        var current = null;

        document.querySelectorAll('.js-voucher-comment').forEach(function (btn) {
            btn.addEventListener('click', function () {
                fetch(btn.dataset.url, { headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' } })
                    .then(function (r) { if (!r.ok) throw new Error(r.status); return r.json(); })
                    .then(function (data) {
                        current = btn;
                        textEl.value = data.admin_comment || '';
                        bootstrap.Modal.getOrCreateInstance(modalEl).show();
                    })
                    .catch(function () { alert(text.load_failed); });
            });
        });

        document.getElementById('voucher-comment-save').addEventListener('click', function () {
            if (!current) return;
            var btn = current;
            var token = document.querySelector('meta[name="csrf-token"]');
            fetch(btn.dataset.url, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest',
                    'X-CSRF-TOKEN': token ? token.getAttribute('content') : ''
                },
                body: JSON.stringify({ admin_comment: textEl.value })
            })
                .then(function (r) { if (!r.ok) throw new Error(r.status); return r.json(); })
                .then(function (data) {
                    if (!data || !data.success) throw new Error('failed');
                    var hasComment = textEl.value.trim() !== '';
                    btn.classList.toggle('btn-warning', hasComment);
                    btn.classList.toggle('btn-outline-secondary', !hasComment);
                    bootstrap.Modal.getOrCreateInstance(modalEl).hide();
                })
                .catch(function () { alert(text.save_failed); });
        });
    })();
</script>
@endsection
