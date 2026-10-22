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
                    <div class="d-flex flex-wrap gap-3">
                        <span class="meta-pill"><i class="fa fa-gift"></i> {{ __('admin.voucher_orders.total') }}: <strong>{{ (int) $totals->orders }}</strong></span>
                        <span class="meta-pill"><i class="fa fa-euro-sign"></i> {{ __('admin.voucher_orders.volume') }}: <strong>{{ number_format((float) $totals->volume, 2, ',', '.') }} €</strong></span>
                        <span class="meta-pill"><i class="fa fa-percent"></i> {{ __('admin.voucher_orders.commission') }}: <strong>{{ number_format((float) $totals->commission, 2, ',', '.') }} €</strong></span>
                    </div>
                    <form method="GET" action="{{ route('admin.voucher-orders.index') }}" class="d-flex gap-2 ms-auto">
                        <input type="search" name="q" value="{{ $search }}" class="form-control form-control-sm" placeholder="{{ __('admin.voucher_orders.search_placeholder') }}" aria-label="{{ __('admin.voucher_orders.search') }}">
                        <button type="submit" class="btn btn-sm btn-primary">{{ __('admin.voucher_orders.search') }}</button>
                        @if($search !== '')
                            <a href="{{ route('admin.voucher-orders.index') }}" class="btn btn-sm btn-outline-secondary">{{ __('admin.voucher_orders.reset') }}</a>
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
                                            $commissionPaid = $order->financeItem?->paid_status === 'paid';
                                        @endphp
                                        <tr>
                                            <td data-label="{{ __('admin.voucher_orders.col_code') }}">
                                                <span class="vo-code fw-semibold">{{ $order->voucher_code }}</span>
                                                <div class="vo-muted">#{{ $order->id }}</div>
                                            </td>
                                            <td data-label="{{ __('admin.voucher_orders.col_buyer') }}">
                                                <span class="vo-ellipsis">{{ $order->buyerName() ?: '—' }}</span>
                                                <span class="vo-muted vo-ellipsis" title="{{ $order->recipient_names }}">{{ __('admin.voucher_orders.recipients', ['names' => $order->recipient_names]) }}</span>
                                            </td>
                                            <td data-label="{{ __('admin.voucher_orders.col_contact') }}">
                                                <a href="mailto:{{ $order->buyer_email }}" class="vo-ellipsis" title="{{ $order->buyer_email }}">{{ $order->buyer_email }}</a>
                                                <span class="vo-muted">{{ $order->buyerPhone() ?: '—' }}</span>
                                            </td>
                                            <td data-label="{{ __('admin.voucher_orders.col_tour') }}">
                                                <a href="{{ route('admin.guidings.edit', $order->guiding_id) }}" class="vo-ellipsis" title="{{ $order->tour_title_snapshot }}">{{ $order->tour_title_snapshot }}</a>
                                                <span class="vo-badge">{{ __('admin.voucher_orders.design_'.($order->design_type?->value ?? 'cag_standard')) }}</span>
                                            </td>
                                            <td data-label="{{ __('admin.voucher_orders.col_guide') }}">
                                                @if($order->guide)
                                                    <a href="{{ route('admin.guides.edit', $order->guide) }}" class="vo-ellipsis">{{ $order->guide->full_name }}</a>
                                                @else
                                                    —
                                                @endif
                                            </td>
                                            <td data-label="{{ __('admin.voucher_orders.col_guests') }}">{{ $order->number_of_guests }}</td>
                                            <td data-label="{{ __('admin.voucher_orders.col_price') }}" class="text-end text-nowrap">{{ number_format((float) $order->price_total, 2, ',', '.') }} {{ $order->currency }}</td>
                                            <td data-label="{{ __('admin.voucher_orders.col_commission') }}" class="text-end text-nowrap">
                                                {{ number_format((float) $order->commission_amount, 2, ',', '.') }} {{ $order->currency }}
                                                <div class="vo-muted">{{ rtrim(rtrim(number_format((float) $order->commission_rate * 100, 2, ',', '.'), '0'), ',') }} %</div>
                                            </td>
                                            <td data-label="{{ __('admin.voucher_orders.col_valid_until') }}" class="text-nowrap">{{ $order->valid_until?->format('Y-m-d') ?? '—' }}</td>
                                            <td data-label="{{ __('admin.voucher_orders.col_ordered') }}" class="text-nowrap vo-muted">{{ $order->created_at?->format('M j, Y g:i A') }}</td>
                                            <td data-label="{{ __('admin.voucher_orders.col_commission_paid') }}">
                                                <span class="badge {{ $commissionPaid ? 'bg-success' : 'bg-secondary' }}">{{ $commissionPaid ? __('admin.voucher_orders.paid') : __('admin.voucher_orders.unpaid') }}</span>
                                            </td>
                                            <td data-label="{{ __('admin.voucher_orders.col_actions') }}" class="text-end text-nowrap">
                                                <div class="btn-group btn-group-sm">
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
                                        <tr><td colspan="12" class="vo-empty text-center text-muted py-4">{{ __('admin.voucher_orders.empty') }}</td></tr>
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
@endsection

@section('js_after')
<script>
    document.querySelectorAll('#voucher-orders-table [data-bs-toggle="popover"]').forEach(function (el) {
        new bootstrap.Popover(el);
    });
</script>
@endsection
