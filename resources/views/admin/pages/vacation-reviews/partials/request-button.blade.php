{{--
    Review request action for a camp/trip booking row. Needs $booking (CampVacationBooking|TripBooking
    with `review` loaded) and $url (its send-review-request route). Include it with 'part' => 'form'
    before the row's btn-group and without it inside the group, like the offer button's form.
--}}
@php
    $formId = 'review-request-' . $booking->id;
    $lastSent = optional($booking->review_requested_at)->format('M j, Y g:i A');
@endphp
@if(($part ?? 'button') === 'form')
    @if(!$booking->review && !empty($booking->email))
        <form id="{{ $formId }}" method="POST" action="{{ $url }}" class="d-none">@csrf</form>
    @endif
@elseif($booking->review)
    <a href="{{ route('admin.vacation-reviews.index', ['review' => $booking->review->id]) }}"
       class="btn btn-warning"
       title="{{ __('admin.vacation_reviews.btn_reviewed', ['score' => number_format($booking->review->grandtotal_score, 1)]) }}">
        <i class="fa fa-star"></i>
    </a>
@elseif(!empty($booking->email))
    <button type="submit"
            form="{{ $formId }}"
            class="btn btn-outline-warning"
            title="{{ $lastSent ? __('admin.vacation_reviews.btn_resend', ['date' => $lastSent]) : __('admin.vacation_reviews.btn_send') }}"
            onclick="return confirm(@js($lastSent
                ? __('admin.vacation_reviews.confirm_resend', ['email' => $booking->email, 'date' => $lastSent])
                : __('admin.vacation_reviews.confirm_send', ['email' => $booking->email])));">
        <i class="fa {{ $lastSent ? 'fa-star-half-alt' : 'fa-star' }}"></i>
    </button>
@endif
