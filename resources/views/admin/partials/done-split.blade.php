{{--
    "3 booked · 1 no reply · 75% booked" next to the Done stat of a request list.
    Styles live in admin.partials.booking-status-history.

    @param \Illuminate\Support\Collection $requests
--}}
@php $split = \App\Enums\DoneReason::breakdown($requests); @endphp
<span class="done-split">
    <span title="{{ \App\Enums\DoneReason::Booked->hint() }}"><i class="fa fa-check-circle" aria-hidden="true"></i> {{ __('admin.done_reason.count_booked', ['count' => $split['booked']]) }}</span>
    <span title="{{ \App\Enums\DoneReason::NoReply->hint() }}"><i class="fa fa-comment-slash" aria-hidden="true"></i> {{ __('admin.done_reason.count_no_reply', ['count' => $split['no_reply']]) }}</span>
    @if($split['rate'] !== null)
        <span title="{{ __('admin.done_reason.rate_hint') }}"><strong>{{ __('admin.done_reason.rate', ['rate' => $split['rate']]) }}</strong></span>
    @endif
</span>
