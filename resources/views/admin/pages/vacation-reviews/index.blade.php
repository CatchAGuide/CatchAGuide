@extends('admin.layouts.app')

@section('title', __('admin.vacation_reviews.page_title'))

@section('content')
<div class="side-app">
    <div class="main-container container-fluid">
        <div class="page-header">
            <h1 class="page-title">{{ __('admin.vacation_reviews.page_title') }}</h1>
            <div>
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="{{ route('admin.index') }}">Admin</a></li>
                    <li class="breadcrumb-item active" aria-current="page">{{ __('admin.vacation_reviews.breadcrumb') }}</li>
                </ol>
            </div>
        </div>

        <p class="text-muted">{{ __('admin.vacation_reviews.subtitle') }}</p>

        <div class="d-flex flex-wrap gap-2 align-items-center mb-3">
            <span class="badge bg-light text-dark border">{{ __('admin.vacation_reviews.stat_total') }}: <strong>{{ number_format($stats['total']) }}</strong></span>
            <span class="badge bg-light text-dark border">{{ __('admin.vacation_reviews.stat_avg_score') }}: <strong>{{ $stats['avg_score'] }}</strong></span>
            <div class="btn-group btn-group-sm ms-auto" role="group" aria-label="{{ __('admin.vacation_reviews.filter_type') }}">
                <a href="{{ route('admin.vacation-reviews.index') }}" class="btn {{ $type === null ? 'btn-primary' : 'btn-outline-primary' }}">{{ __('admin.vacation_reviews.type_all') }}</a>
                <a href="{{ route('admin.vacation-reviews.index', ['type' => 'camp']) }}" class="btn {{ $type === 'camp' ? 'btn-primary' : 'btn-outline-primary' }}">{{ __('admin.vacation_reviews.type_camp') }}</a>
                <a href="{{ route('admin.vacation-reviews.index', ['type' => 'trip']) }}" class="btn {{ $type === 'trip' ? 'btn-primary' : 'btn-outline-primary' }}">{{ __('admin.vacation_reviews.type_trip') }}</a>
            </div>
        </div>

        <div class="card shadow-sm border-0">
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead>
                            <tr>
                                <th>{{ __('admin.vacation_reviews.th_id') }}</th>
                                <th>{{ __('admin.vacation_reviews.th_date') }}</th>
                                <th>{{ __('admin.vacation_reviews.th_listing') }}</th>
                                <th>{{ __('admin.vacation_reviews.th_guest') }}</th>
                                <th>{{ __('admin.vacation_reviews.th_scores') }}</th>
                                <th>{{ __('admin.vacation_reviews.th_comment') }}</th>
                                <th class="text-end">{{ __('admin.vacation_reviews.th_booking') }}</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($reviews as $review)
                                @php
                                    $booking = $review->reviewable;
                                    $isTrip = $review->listing_type === 'trip';
                                    $frontUrl = $booking?->getSourceFrontUrl();
                                @endphp
                                <tr id="vacation-review-{{ $review->id }}" class="{{ $highlight === $review->id ? 'table-warning' : '' }}">
                                    <td class="fw-semibold">#{{ $review->id }}</td>
                                    <td class="text-nowrap">{{ optional($review->created_at)->format('M j, Y') }}</td>
                                    <td>
                                        <span class="badge {{ $isTrip ? 'bg-info' : 'bg-success' }}">{{ $isTrip ? __('admin.vacation_reviews.type_trip') : __('admin.vacation_reviews.type_camp') }} #{{ $review->listing_id }}</span>
                                        <div>
                                            {{ $booking?->getSourceTitle() ?? '—' }}
                                            @if($frontUrl)
                                                <a href="{{ $frontUrl }}" target="_blank" rel="noopener" title="{{ __('admin.vacation_reviews.view_on_site') }}"><i class="fa fa-external-link-alt"></i></a>
                                            @endif
                                        </div>
                                    </td>
                                    <td>
                                        <div>{{ $booking?->name ?: '—' }}</div>
                                        <div class="text-muted small">{{ $booking?->email }}</div>
                                    </td>
                                    <td class="text-nowrap">
                                        <span class="badge bg-warning text-dark fs-6">{{ number_format($review->grandtotal_score, 1) }}</span>
                                        <div class="small text-muted mt-1">
                                            {{ __('admin.vacation_reviews.score_overall') }}: {{ $review->overall_score }}<br>
                                            {{ __('admin.vacation_reviews.score_accommodation') }}: {{ $review->accommodation_score }}<br>
                                            {{ __('admin.vacation_reviews.score_fishing') }}: {{ $review->fishing_score }}<br>
                                            {{ __('admin.vacation_reviews.score_value') }}: {{ $review->value_score }}
                                        </div>
                                    </td>
                                    <td style="min-width: 260px; white-space: pre-line;">{{ $review->comment }}</td>
                                    <td class="text-end">
                                        @if($booking)
                                            <a href="{{ route($isTrip ? 'admin.trip-bookings.index' : 'admin.camp-vacation-bookings.index') }}" class="btn btn-sm btn-outline-secondary">#{{ $booking->id }}</a>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="7" class="text-center py-5 text-muted">{{ __('admin.vacation_reviews.empty') }}</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                <div class="mt-3">{{ $reviews->links() }}</div>
            </div>
        </div>
    </div>
</div>
@endsection
