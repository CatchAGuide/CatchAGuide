@extends('admin.layouts.app')

@section('title', __('sales.list.heading'))

@section('custom_style')
<link href="{{ mix('css/admin-sales-builder.css') }}" rel="stylesheet">
@endsection

@section('content')
<div class="side-app">
    <div class="main-container container-fluid sb-list">
        <div class="page-header d-flex flex-wrap justify-content-between align-items-center gap-2">
            <div>
                <h1 class="page-title mb-1">{{ __('sales.list.heading') }}</h1>
                <p class="text-muted small mb-0">{{ __('sales.list.subheading') }}</p>
            </div>
            <a href="{{ route('admin.sales.offers.create') }}" class="btn btn-primary btn-sm"><i class="fe fe-plus me-1"></i>{{ __('sales.list.new') }}</a>
        </div>

        @if(session('sales_notice'))
            <div class="alert alert-success" role="status">{{ session('sales_notice') }}</div>
        @endif
        @if(session('sales_error'))
            <div class="alert alert-danger" role="alert">{{ session('sales_error') }}</div>
        @endif

        <form method="GET" class="card card-body sb-filters">
            <div class="row g-2 align-items-end">
                <div class="col-12 col-md-3">
                    <label class="form-label small mb-1" for="sales-q">{{ __('sales.list.search') }}</label>
                    <input id="sales-q" type="search" name="q" value="{{ $filters['q'] ?? '' }}" class="form-control form-control-sm" placeholder="{{ __('sales.list.search_placeholder') }}">
                </div>
                <div class="col-6 col-md-2">
                    <label class="form-label small mb-1" for="sales-status">{{ __('sales.list.status') }}</label>
                    <select id="sales-status" name="status" class="form-select form-select-sm">
                        <option value="">{{ __('sales.list.all') }}</option>
                        @foreach($statuses as $status)
                            <option value="{{ $status->value }}" @selected(($filters['status'] ?? '') === $status->value)>{{ $status->label() }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-6 col-md-2">
                    <label class="form-label small mb-1" for="sales-creator">{{ __('sales.list.creator') }}</label>
                    <select id="sales-creator" name="creator" class="form-select form-select-sm">
                        <option value="">{{ __('sales.list.all') }}</option>
                        @foreach($creators as $creator)
                            <option value="{{ $creator->id }}" @selected((string) ($filters['creator'] ?? '') === (string) $creator->id)>{{ $creator->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-6 col-md-2">
                    <label class="form-label small mb-1" for="sales-from">{{ __('sales.list.travel_from') }}</label>
                    <input id="sales-from" type="date" name="from" value="{{ $filters['from'] ?? '' }}" class="form-control form-control-sm">
                </div>
                <div class="col-6 col-md-2">
                    <label class="form-label small mb-1" for="sales-to">{{ __('sales.list.travel_to') }}</label>
                    <input id="sales-to" type="date" name="to" value="{{ $filters['to'] ?? '' }}" class="form-control form-control-sm">
                </div>
                <div class="col-12 col-md-1 d-flex gap-1">
                    <button class="btn btn-secondary btn-sm w-100">{{ __('sales.list.filter') }}</button>
                </div>
            </div>
        </form>

        <div class="card">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0 sb-table">
                    <thead>
                        <tr>
                            <th>{{ __('sales.list.number') }}</th>
                            <th>{{ __('sales.list.customer') }}</th>
                            <th>{{ __('sales.list.products') }}</th>
                            <th>{{ __('sales.list.period') }}</th>
                            <th class="text-end">{{ __('sales.list.total') }}</th>
                            <th>{{ __('sales.list.status') }}</th>
                            <th>{{ __('sales.list.last_sent') }}</th>
                            <th>{{ __('sales.list.created_by') }}</th>
                            <th class="text-end">{{ __('sales.list.actions') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($documents as $row)
                            @php($document = $row['document'])
                            <tr>
                                <td><a href="{{ route('admin.sales.offers.edit', $document) }}" class="sb-table__number">{{ $document->number }}</a></td>
                                <td>
                                    <div>{{ $row['customer'] }}</div>
                                    <div class="sb-table__muted">{{ $document->email }}</div>
                                </td>
                                <td class="sb-table__products" title="{{ $row['products'] }}">{{ $row['products'] }}</td>
                                <td>{{ $row['period'] }}</td>
                                <td class="text-end sb-table__amount">{{ $row['total'] }}</td>
                                <td>
                                    <span class="badge bg-{{ $document->status->badge() }}">{{ $document->status->label() }}</span>
                                    @if($document->hasUnseenAcceptance())
                                        <span class="badge bg-danger">{{ __('sales.list.new_acceptance') }}</span>
                                    @endif
                                </td>
                                <td>{{ $row['last_sent'] }}</td>
                                <td>{{ $document->creator?->name }}</td>
                                <td class="text-end">
                                    <form id="offer-duplicate-{{ $document->id }}" method="POST" action="{{ route('admin.sales.offers.duplicate', $document) }}" class="d-none">@csrf</form>
                                    @if($row['can_decline'])
                                        <form id="offer-decline-{{ $document->id }}" method="POST" action="{{ route('admin.sales.offers.decline', $document) }}" class="d-none">@csrf</form>
                                    @endif
                                    @if($row['can_cancel'])
                                        <form id="offer-cancel-{{ $document->id }}" method="POST" action="{{ route('admin.sales.offers.cancel', $document) }}" class="d-none"
                                              onsubmit="return confirm(@js(__('sales.list.cancel_confirm', ['number' => $document->number])))">@csrf</form>
                                    @endif
                                    <div class="btn-group btn-group-sm">
                                        <a href="{{ route('admin.sales.offers.edit', $document) }}" class="btn btn-outline-primary" data-bs-toggle="tooltip" title="{{ __('sales.list.open') }}" aria-label="{{ __('sales.list.open') }}"><i class="fas fa-pen"></i></a>
                                        <button type="button" class="btn btn-outline-secondary" data-copy="{{ $row['url'] }}" data-bs-toggle="tooltip" title="{{ __('sales.list.copy_link') }}" aria-label="{{ __('sales.list.copy_link') }}"><i class="fas fa-link"></i></button>
                                        <button type="submit" form="offer-duplicate-{{ $document->id }}" class="btn btn-outline-secondary" data-bs-toggle="tooltip" title="{{ __('sales.list.duplicate') }}" aria-label="{{ __('sales.list.duplicate') }}"><i class="fas fa-copy"></i></button>
                                        @if($row['can_decline'])
                                            <button type="submit" form="offer-decline-{{ $document->id }}" class="btn btn-outline-warning" data-bs-toggle="tooltip" title="{{ __('sales.list.decline') }}" aria-label="{{ __('sales.list.decline') }}"><i class="fas fa-thumbs-down"></i></button>
                                        @endif
                                        @if($row['can_cancel'])
                                            <button type="submit" form="offer-cancel-{{ $document->id }}" class="btn btn-outline-danger" data-bs-toggle="tooltip" title="{{ __('sales.list.cancel') }}" aria-label="{{ __('sales.list.cancel') }}"><i class="fas fa-ban"></i></button>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="9" class="text-center sb-table__muted py-4">{{ __('sales.list.empty') }}</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            @if($paginator->hasPages())
                <div class="card-footer">{{ $paginator->links() }}</div>
            @endif
        </div>
    </div>
</div>
@endsection

@push('js_after')
<script>
    document.querySelectorAll('[data-copy]').forEach((button) => {
        button.addEventListener('click', async () => {
            try {
                await navigator.clipboard.writeText(button.dataset.copy);
                const icon = button.innerHTML;
                button.innerHTML = '<i class="fas fa-check"></i>';
                setTimeout(() => { button.innerHTML = icon; }, 1500);
            } catch (error) {
                console.warn('Copy failed', error);
            }
        });
    });
</script>
@endpush
