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

        <p class="text-muted">{{ $tab === 'open' ? __('admin.vacation_reviews.open_subtitle') : __('admin.vacation_reviews.subtitle') }}</p>

        <div class="d-flex flex-wrap gap-2 align-items-center mb-3">
            <ul class="nav nav-pills me-2">
                <li class="nav-item">
                    <a href="{{ route('admin.vacation-reviews.index', array_filter(['type' => $type])) }}" class="nav-link {{ $tab === 'reviews' ? 'active' : '' }}">
                        {{ __('admin.vacation_reviews.tab_reviews') }} <span class="badge bg-light text-dark ms-1">{{ number_format($stats['total']) }}</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a href="{{ route('admin.vacation-reviews.index', array_filter(['tab' => 'open', 'type' => $type])) }}" class="nav-link {{ $tab === 'open' ? 'active' : '' }}">
                        {{ __('admin.vacation_reviews.tab_open') }} <span class="badge bg-light text-dark ms-1">{{ number_format($stats['open']) }}</span>
                    </a>
                </li>
            </ul>
            @if($tab === 'reviews')
                <span class="badge bg-light text-dark border">{{ __('admin.vacation_reviews.stat_avg_score') }}: <strong>{{ $stats['avg_score'] }}</strong></span>
            @endif
            <div class="btn-group btn-group-sm ms-auto" role="group" aria-label="{{ __('admin.vacation_reviews.filter_type') }}">
                @foreach([null => __('admin.vacation_reviews.type_all'), 'camp' => __('admin.vacation_reviews.type_camp'), 'trip' => __('admin.vacation_reviews.type_trip')] as $typeKey => $typeLabel)
                    <a href="{{ route('admin.vacation-reviews.index', array_filter(['tab' => $tab === 'open' ? 'open' : null, 'type' => $typeKey ?: null])) }}"
                       class="btn {{ ($type ?? '') === ($typeKey ?: '') ? 'btn-primary' : 'btn-outline-primary' }}">{{ $typeLabel }}</a>
                @endforeach
            </div>
            <button type="button" class="btn btn-sm btn-success" data-bs-toggle="modal" data-bs-target="#vacationReviewModal">
                <i class="fa fa-plus"></i> {{ __('admin.vacation_reviews.add') }}
            </button>
        </div>

        <div class="card shadow-sm border-0">
            <div class="card-body">
                <div class="table-responsive">
                    @if($tab === 'open')
                        <table class="table table-hover align-middle mb-0">
                            <thead>
                                <tr>
                                    <th>{{ __('admin.vacation_reviews.th_booking') }}</th>
                                    <th>{{ __('admin.vacation_reviews.th_listing') }}</th>
                                    <th>{{ __('admin.vacation_reviews.th_guest') }}</th>
                                    <th>{{ __('admin.vacation_reviews.th_stay') }}</th>
                                    <th>{{ __('admin.vacation_reviews.th_request') }}</th>
                                    <th class="text-end">{{ __('admin.vacation_reviews.th_actions') }}</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($awaiting as $row)
                                    @php
                                        $booking = $row['booking'];
                                        $isTrip = $row['type'] === 'trip';
                                        $requestRoute = $isTrip ? 'admin.trip-bookings.review-request' : 'admin.camp-vacation-bookings.review-request';
                                        $lastSent = optional($booking->review_requested_at)->format('M j, Y g:i A');
                                    @endphp
                                    <tr>
                                        <td class="text-nowrap">
                                            <span class="badge {{ $isTrip ? 'bg-info' : 'bg-success' }}">{{ $isTrip ? __('admin.vacation_reviews.type_trip') : __('admin.vacation_reviews.type_camp') }}</span>
                                            <a href="{{ route($isTrip ? 'admin.trip-bookings.index' : 'admin.camp-vacation-bookings.index') }}" class="fw-semibold">#{{ $booking->id }}</a>
                                        </td>
                                        <td>{{ $row['title'] ?? '—' }}</td>
                                        <td>
                                            <div>{{ $booking->name ?: '—' }}</div>
                                            <div class="text-muted small">{{ $booking->email ?: __('admin.vacation_reviews.no_email') }}</div>
                                        </td>
                                        <td class="text-nowrap">{{ optional($booking->preferred_date)->format('M j, Y') ?? '—' }}</td>
                                        <td class="text-nowrap small">{{ $lastSent ?? __('admin.vacation_reviews.not_requested') }}</td>
                                        <td class="text-end text-nowrap">
                                            @if(!empty($booking->email))
                                                <form method="POST" action="{{ route($requestRoute, $booking) }}" class="d-inline"
                                                      onsubmit="return confirm(@js($lastSent
                                                          ? __('admin.vacation_reviews.confirm_resend', ['email' => $booking->email, 'date' => $lastSent])
                                                          : __('admin.vacation_reviews.confirm_send', ['email' => $booking->email])));">
                                                    @csrf
                                                    <input type="hidden" name="return" value="reviews">
                                                    <button type="submit" class="btn btn-sm btn-outline-warning"
                                                            title="{{ $lastSent ? __('admin.vacation_reviews.btn_resend', ['date' => $lastSent]) : __('admin.vacation_reviews.btn_send') }}">
                                                        <i class="fa {{ $lastSent ? 'fa-star-half-alt' : 'fa-star' }}"></i>
                                                        {{ $lastSent ? __('admin.vacation_reviews.btn_resend', ['date' => $lastSent]) : __('admin.vacation_reviews.btn_send') }}
                                                    </button>
                                                </form>
                                            @endif
                                            <button type="button" class="btn btn-sm btn-outline-secondary"
                                                    data-bs-toggle="modal" data-bs-target="#vacationReviewModal"
                                                    data-booking-type="{{ $row['type'] }}"
                                                    data-booking-id="{{ $booking->id }}"
                                                    data-booking-label="{{ __('admin.vacation_reviews.create_for_booking', [
                                                        'type' => $isTrip ? __('admin.vacation_reviews.type_trip') : __('admin.vacation_reviews.type_camp'),
                                                        'id' => $booking->id,
                                                        'title' => $row['title'] ?? '—',
                                                    ]) }}"
                                                    data-booking-contact="{{ $booking->name }} · {{ $booking->email }}"
                                                    data-guest-name="{{ $booking->first_name ?: strtok((string) $booking->name, ' ') }}"
                                                    data-language="{{ $booking->customerLocale() }}">
                                                <i class="fa fa-pen"></i> {{ __('admin.vacation_reviews.btn_enter') }}
                                            </button>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="6" class="text-center py-5 text-muted">{{ __('admin.vacation_reviews.open_empty') }}</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    @else
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
                                        $listing = $listings[$review->listing_type.':'.$review->listing_id] ?? null;
                                        $frontUrl = $listing['url'] ?? null;
                                    @endphp
                                    <tr id="vacation-review-{{ $review->id }}" class="{{ $highlight === $review->id ? 'table-warning' : '' }}">
                                        <td class="fw-semibold">#{{ $review->id }}</td>
                                        <td class="text-nowrap">{{ optional($review->created_at)->format('M j, Y') }}</td>
                                        <td>
                                            <span class="badge {{ $isTrip ? 'bg-info' : 'bg-success' }}">{{ $isTrip ? __('admin.vacation_reviews.type_trip') : __('admin.vacation_reviews.type_camp') }} #{{ $review->listing_id }}</span>
                                            <div>
                                                {{ $listing['title'] ?? '—' }}
                                                @if($frontUrl)
                                                    <a href="{{ $frontUrl }}" target="_blank" rel="noopener" title="{{ __('admin.vacation_reviews.view_on_site') }}"><i class="fa fa-external-link-alt"></i></a>
                                                @endif
                                            </div>
                                        </td>
                                        <td>
                                            <div>{{ $review->guest_name ?: ($booking?->name ?: '—') }}</div>
                                            <div class="text-muted small">{{ $booking?->email }}</div>
                                            @if($review->is_manual)
                                                <span class="badge bg-secondary">{{ __('admin.vacation_reviews.manual_badge') }}</span>
                                            @endif
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
                                            @else
                                                —
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
                    @endif
                </div>
                @if($reviews)
                    <div class="mt-3">{{ $reviews->links() }}</div>
                @endif
            </div>
        </div>
    </div>
</div>

@include('admin.pages.vacation-reviews.partials.review-modal')
@endsection
