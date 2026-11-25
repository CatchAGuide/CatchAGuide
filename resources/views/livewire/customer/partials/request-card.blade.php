{{-- A camp or trip request. $item from CustomerBookingPresenter::camp() / ::trip(). --}}
<li class="ga-booking" wire:key="c-{{ $item['type'] }}-{{ $item['id'] }}">
    <div class="ga-booking__head">
        <div class="ga-booking__badges">
            <span class="ga-id">#{{ $item['id'] }}</span>
            <span class="ga-status ga-status--{{ $item['status_tone'] }}">{{ $item['status_label'] }}</span>
        </div>
    </div>

    <h3 class="ga-booking__title">
        @if ($item['url'])<a href="{{ $item['url'] }}" class="ga-plain-link">{{ $item['title'] }}</a>@else{{ $item['title'] }}@endif
    </h3>
    @if ($item['location'] !== '')
        <p class="ga-booking__location"><i class="fas fa-map-marker-alt" aria-hidden="true"></i> {{ $item['location'] }}</p>
    @endif

    <dl class="ga-facts">
        <div><dt>{{ __('guide_area.customer.bookings.wish_date') }}</dt><dd>{{ $item['date_label'] }}</dd></div>
        <div><dt>{{ __('guide_area.bookings.guests') }}</dt><dd>{{ trans_choice('guide_area.bookings.people', $item['guests'], ['count' => $item['guests']]) }}</dd></div>
        @if ($item['nights'])
            <div><dt>{{ __('guide_area.customer.bookings.nights') }}</dt><dd>{{ $item['nights'] }}</dd></div>
        @endif
        <div><dt>{{ __('guide_area.customer.bookings.estimate') }}</dt><dd>{{ $item['price_label'] ?? '—' }}</dd></div>
        <div><dt>{{ __('guide_area.bookings.requested_on') }}</dt><dd>{{ $item['requested_label'] ?? '—' }}</dd></div>
    </dl>

    @if ($item['includes'] !== [])
        <p class="ga-booking__guest"><span class="ga-muted">{{ __('guide_area.customer.bookings.includes') }}:</span> {{ implode(' · ', $item['includes']) }}</p>
    @endif
    @if ($item['message'] !== '')
        <p class="ga-booking__note">{{ __('guide_area.customer.bookings.your_message') }} “{{ str($item['message'])->limit(300) }}”</p>
    @endif

    @if ($item['rate_url'] || ! empty($item['guest_url']))
        <div class="ga-booking__actions">
            @if ($item['rate_url'])
                <a href="{{ $item['rate_url'] }}" class="ga-btn ga-btn--dark ga-btn--sm"><i class="fas fa-star" aria-hidden="true"></i> {{ __('guide_area.customer.bookings.rate') }}</a>
            @endif
            @if (! empty($item['guest_url']))
                <a href="{{ $item['guest_url'] }}" class="ga-btn ga-btn--outline ga-btn--sm" rel="nofollow">{{ __('guest_bookings.find.open_booking') }}</a>
            @endif
        </div>
    @endif
</li>
