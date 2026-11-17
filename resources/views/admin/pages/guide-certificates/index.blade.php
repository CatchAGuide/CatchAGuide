@extends('admin.layouts.app')

@section('title', __('guide_area.admin.title'))

@section('content')
<div class="side-app">
    <div class="main-container container-fluid">
        <div class="page-header">
            <h1 class="page-title">{{ __('guide_area.admin.title') }}</h1>
            <span class="badge bg-warning text-dark">{{ __('guide_area.admin.in_review_count', ['count' => $inReviewCount]) }}</span>
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
                    @foreach (['in_review', 'verified', 'rejected', 'all'] as $filter)
                        <a href="{{ route('admin.guide-certificates.index', ['status' => $filter]) }}"
                           @class(['btn btn-sm', 'btn-primary' => $status === $filter, 'btn-outline-primary' => $status !== $filter])>
                            {{ __('guide_area.admin.filter.'.$filter) }}
                        </a>
                    @endforeach
                </div>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-bordered text-nowrap mb-0">
                        <thead>
                        <tr>
                            <th>{{ __('guide_area.admin.th_guide') }}</th>
                            <th>{{ __('guide_area.admin.th_certificate') }}</th>
                            <th>{{ __('guide_area.admin.th_valid_until') }}</th>
                            <th>{{ __('guide_area.admin.th_uploaded') }}</th>
                            <th>{{ __('guide_area.admin.th_status') }}</th>
                            <th>{{ __('guide_area.admin.th_actions') }}</th>
                        </tr>
                        </thead>
                        <tbody>
                        @forelse ($certificates as $certificate)
                            <tr>
                                <td>
                                    {{ trim(($certificate->user->firstname ?? '').' '.($certificate->user->lastname ?? '')) }}<br>
                                    <small class="text-muted">{{ $certificate->user->email ?? '' }}</small>
                                </td>
                                <td>
                                    {{ $certificate->label() }}<br>
                                    <a href="{{ route('admin.guide-certificates.file', $certificate) }}" target="_blank" rel="noopener">
                                        <small>{{ $certificate->original_name }}</small>
                                    </a>
                                </td>
                                <td>{{ $certificate->valid_until?->format('d.m.Y') ?? '—' }}</td>
                                <td>{{ $certificate->updated_at->format('d.m.Y H:i') }}</td>
                                <td>
                                    {{ __($certificate->status->labelKey()) }}
                                    @if ($certificate->rejection_reason)
                                        <br><small class="text-muted">{{ $certificate->rejection_reason }}</small>
                                    @endif
                                </td>
                                <td>
                                    @if ($certificate->status !== \App\Enums\CertificateStatus::Verified)
                                        <form method="post" action="{{ route('admin.guide-certificates.verify', $certificate) }}" class="d-inline">
                                            @csrf
                                            <button type="submit" class="btn btn-sm btn-success">{{ __('guide_area.admin.verify') }}</button>
                                        </form>
                                    @endif
                                    @if ($certificate->status !== \App\Enums\CertificateStatus::Rejected)
                                        <details class="d-inline-block ms-1">
                                            <summary class="btn btn-sm btn-outline-danger">{{ __('guide_area.admin.reject') }}</summary>
                                            <form method="post" action="{{ route('admin.guide-certificates.reject', $certificate) }}" class="mt-2" style="min-width: 260px;">
                                                @csrf
                                                <textarea name="rejection_reason" class="form-control form-control-sm mb-2" rows="2" maxlength="1000" required placeholder="{{ __('guide_area.admin.reject_reason_placeholder') }}"></textarea>
                                                <button type="submit" class="btn btn-sm btn-danger">{{ __('guide_area.admin.reject_submit') }}</button>
                                            </form>
                                        </details>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="6" class="text-center text-muted">{{ __('guide_area.admin.empty') }}</td></tr>
                        @endforelse
                        </tbody>
                    </table>
                </div>
                <div class="mt-3">{{ $certificates->links() }}</div>
            </div>
        </div>
    </div>
</div>
@endsection
