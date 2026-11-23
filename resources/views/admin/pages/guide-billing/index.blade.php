@extends('admin.layouts.app')
@use('App\Enums\AccountVerificationStatus')
@use('App\Support\CountryList')

@section('title', __('guide_area.account.admin.title'))

@section('content')
<div class="side-app">
    <div class="main-container container-fluid">
        <div class="page-header">
            <h1 class="page-title">{{ __('guide_area.account.admin.title') }}</h1>
            <span class="badge bg-warning text-dark">{{ __('guide_area.account.admin.pending_count', ['count' => $pendingCount]) }}</span>
        </div>

        @if (session('message'))
            <div class="alert alert-success">{{ session('message') }}</div>
        @endif
        @if ($errors->any())
            <div class="alert alert-danger">@foreach ($errors->all() as $error)<div>{{ $error }}</div>@endforeach</div>
        @endif

        <div class="card">
            <div class="card-header">
                <div class="btn-group" role="group">
                    @foreach (['pending', 'verified', 'rejected', 'all'] as $filter)
                        <a href="{{ route('admin.guide-billing.index', ['status' => $filter]) }}"
                           @class(['btn btn-sm', 'btn-primary' => $status === $filter, 'btn-outline-primary' => $status !== $filter])>
                            {{ __('guide_area.account.admin.filter.'.$filter) }}
                        </a>
                    @endforeach
                </div>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-bordered mb-0">
                        <thead>
                        <tr>
                            <th>{{ __('guide_area.account.admin.th_guide') }}</th>
                            <th>{{ __('guide_area.account.admin.th_type') }}</th>
                            <th>{{ __('guide_area.account.admin.th_legal') }}</th>
                            <th>{{ __('guide_area.account.admin.th_address') }}</th>
                            <th>{{ __('guide_area.account.fields.vat_id') }}</th>
                            <th>{{ __('guide_area.account.admin.th_status') }}</th>
                            <th>{{ __('guide_area.account.admin.th_actions') }}</th>
                        </tr>
                        </thead>
                        <tbody>
                        @forelse ($guides as $guide)
                            @php($info = $guide->information)
                            @php($company = $guide->guide_type === 'company')
                            <tr>
                                <td>
                                    {{ trim($guide->firstname.' '.$guide->lastname) }}<br>
                                    <small class="text-muted">{{ $guide->email }}</small><br>
                                    <small class="text-muted">{{ $guide->phone_country_code }} {{ $guide->phone }}</small>
                                </td>
                                <td>
                                    {{ __('guide_area.account.types.'.($company ? 'company' : 'private')) }}
                                    @if ($guide->account_type_changed_at)
                                        <br><small class="text-warning">{{ __('guide_area.account.admin.type_changed_on', ['date' => $guide->account_type_changed_at->format('d.m.Y')]) }}</small>
                                    @endif
                                </td>
                                <td>
                                    @if ($company)
                                        {{ $info?->company_name ?: '—' }}<br>
                                        <small class="text-muted">{{ $info?->legal_form ?: '—' }}</small>
                                    @else
                                        {{ __('guide_area.account.fields.birthday') }}: {{ $info?->birthday?->format('d.m.Y') ?? '—' }}
                                    @endif
                                </td>
                                <td>
                                    {{ trim(($info?->address ?? '').' '.($info?->address_number ?? '')) ?: '—' }}<br>
                                    <small class="text-muted">{{ trim(($info?->postal ?? '').' '.($info?->city ?? '')) }} {{ CountryList::name(CountryList::normalize($info?->country)) ?? $info?->country }}</small>
                                </td>
                                <td>{{ $guide->tax_id ?: ($info?->vat_not_registered ? __('guide_area.account.vat_not_registered') : '—') }}</td>
                                <td>
                                    {{ __($guide->account_verification_status->labelKey()) }}
                                    @if ($guide->account_rejection_reason)
                                        <br><small class="text-muted">{{ $guide->account_rejection_reason }}</small>
                                    @endif
                                    <br><small class="text-muted">{{ $guide->updated_at->format('d.m.Y H:i') }}</small>
                                </td>
                                <td class="text-nowrap">
                                    @if ($guide->account_verification_status !== AccountVerificationStatus::Verified)
                                        <form method="post" action="{{ route('admin.guide-billing.approve', $guide) }}" class="d-inline">
                                            @csrf
                                            <button type="submit" class="btn btn-sm btn-success">{{ __('guide_area.account.admin.approve') }}</button>
                                        </form>
                                    @endif
                                    @if ($guide->account_verification_status !== AccountVerificationStatus::Rejected)
                                        <details class="d-inline-block ms-1">
                                            <summary class="btn btn-sm btn-outline-danger">{{ __('guide_area.account.admin.reject') }}</summary>
                                            <form method="post" action="{{ route('admin.guide-billing.reject', $guide) }}" class="mt-2" style="min-width: 260px;">
                                                @csrf
                                                <textarea name="rejection_reason" class="form-control form-control-sm mb-2" rows="2" maxlength="1000" required placeholder="{{ __('guide_area.admin.reject_reason_placeholder') }}"></textarea>
                                                <button type="submit" class="btn btn-sm btn-danger">{{ __('guide_area.admin.reject_submit') }}</button>
                                            </form>
                                        </details>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="7" class="text-center text-muted">{{ __('guide_area.account.admin.empty') }}</td></tr>
                        @endforelse
                        </tbody>
                    </table>
                </div>
                <div class="mt-3">{{ $guides->links() }}</div>
            </div>
        </div>
    </div>
</div>
@endsection
