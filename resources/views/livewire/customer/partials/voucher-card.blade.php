{{-- A tour the user bought as a gift voucher. $item from CustomerBookingPresenter::voucher(). --}}
<li class="ga-booking" wire:key="c-voucher-{{ $item['id'] }}">
    <div class="ga-booking__head">
        <div class="ga-booking__badges">
            <span class="ga-id">{{ $item['reference'] }}</span>
            <span class="ga-status ga-status--{{ $item['status_tone'] }}">{{ $item['status_label'] }}</span>
        </div>
    </div>

    <h3 class="ga-booking__title">
        @if ($item['url'])<a href="{{ $item['url'] }}" class="ga-plain-link">{{ $item['title'] }}</a>@else{{ $item['title'] }}@endif
    </h3>

    <dl class="ga-facts">
        <div><dt>{{ __('guide_area.bookings.guests') }}</dt><dd>{{ trans_choice('guide_area.bookings.people', $item['guests'], ['count' => $item['guests']]) }}</dd></div>
        <div><dt>{{ __('guide_area.bookings.price') }}</dt><dd>{{ $item['price_label'] }}</dd></div>
        <div><dt>{{ __('guide_area.customer.bookings.ordered_on') }}</dt><dd>{{ $item['ordered_label'] ?? '—' }}</dd></div>
        <div><dt>{{ __('guide_area.customer.bookings.valid_until') }}</dt><dd>{{ $item['valid_until_label'] ?? '—' }}</dd></div>
    </dl>

    <p class="ga-booking__guest">
        <span class="ga-muted">{{ __('guide_area.customer.bookings.guide') }}:</span> <strong>{{ $item['guide_name'] }}</strong>
        @if ($item['recipients'] !== '') · <span class="ga-muted">{{ __('guide_area.customer.bookings.for') }}:</span> {{ $item['recipients'] }}@endif
    </p>
    @if ($item['code'])
        <p class="ga-booking__note">{{ __('guide_area.customer.bookings.voucher_code', ['code' => $item['code']]) }}</p>
    @endif
    @if (! empty($item['guest_url']))
        <div class="ga-booking__actions">
            <a href="{{ $item['guest_url'] }}" class="ga-btn ga-btn--outline ga-btn--sm" rel="nofollow">{{ __('guest_bookings.find.open_booking') }}</a>
        </div>
    @endif
</li>
