{{-- One tour booking as the guest sees it. $item from CustomerBookingPresenter::tour(). --}}
<li id="booking-{{ $item['id'] }}" wire:key="c-booking-{{ $item['id'] }}"
    @class(['ga-booking', 'ga-booking--open' => $item['status'] === 'open', 'is-focused' => $focused])
    x-data="{ details: @js($focused) }">
    <div class="ga-booking__head">
        <div class="ga-booking__badges">
            <span class="ga-id">#{{ $item['id'] }}</span>
            <span class="ga-status ga-status--{{ $item['status_tone'] }}">{{ $item['status_label'] }}</span>
        </div>
        @if ($item['answer_by'])
            <span class="ga-booking__reply"><i class="far fa-clock" aria-hidden="true"></i> {{ __('guide_area.customer.bookings.answer_by', ['date' => $item['answer_by']]) }}</span>
        @endif
    </div>

    <h3 class="ga-booking__title">
        @if ($item['url'])<a href="{{ $item['url'] }}" class="ga-plain-link">{{ $item['title'] }}</a>@else{{ $item['title'] }}@endif
    </h3>
    @if ($item['location'] !== '')
        <p class="ga-booking__location"><i class="fas fa-map-marker-alt" aria-hidden="true"></i> {{ $item['location'] }}</p>
    @endif

    <dl class="ga-facts">
        <div><dt>{{ __('guide_area.bookings.tour_date') }}</dt><dd>{{ $item['date_label'] }}</dd></div>
        <div><dt>{{ __('guide_area.bookings.guests') }}</dt><dd>{{ trans_choice('guide_area.bookings.people', $item['guests'], ['count' => $item['guests']]) }}</dd></div>
        <div><dt>{{ __('guide_area.bookings.price') }}</dt><dd>{{ $item['price_label'] }}</dd></div>
        <div><dt>{{ __('guide_area.bookings.requested_on') }}</dt><dd>{{ $item['requested_label'] ?? '—' }}</dd></div>
    </dl>

    <p class="ga-booking__guest">
        <span class="ga-muted">{{ __('guide_area.customer.bookings.guide') }}:</span>
        <strong>{{ $item['guide']['name'] }}</strong>
        @if ($item['guide']['phone']) · <a href="tel:{{ preg_replace('/[^0-9+]/', '', $item['guide']['phone']) }}">{{ $item['guide']['phone'] }}</a>@endif
        @if ($item['guide']['email']) · <a href="mailto:{{ $item['guide']['email'] }}">{{ $item['guide']['email'] }}</a>@endif
        @if ($item['contact_after_accept']) <span class="ga-muted">· {{ __('guide_area.customer.bookings.contact_after_accept') }}</span>@endif
    </p>

    @if ($item['status'] === 'declined')
        <div class="ga-booking__note">
            @if ($item['guide_message'] !== '')
                <p>{{ __('guide_area.customer.bookings.guide_wrote') }} “{{ $item['guide_message'] }}”</p>
            @endif
            @if ($item['alternative_labels'] !== [])
                <p>{{ __('guide_area.customer.bookings.suggested', ['dates' => implode(', ', $item['alternative_labels'])]) }}</p>
            @endif
        </div>
    @elseif ($item['status'] === 'expired')
        <p class="ga-booking__note">{{ __('guide_area.customer.bookings.expired_note') }}</p>
    @elseif ($item['cancel_reason_label'])
        <p class="ga-booking__note">{{ __('guide_area.customer.bookings.cancelled_by_guide', ['reason' => $item['cancel_reason_label']]) }}</p>
    @endif

    <div class="ga-booking__actions">
        @if ($item['reschedule_url'])
            <a href="{{ $item['reschedule_url'] }}" class="ga-btn ga-btn--dark ga-btn--sm">{{ __('guide_area.customer.bookings.pick_date') }}</a>
        @endif
        @if ($item['rate_url'])
            <a href="{{ $item['rate_url'] }}" class="ga-btn ga-btn--dark ga-btn--sm"><i class="fas fa-star" aria-hidden="true"></i> {{ __('guide_area.customer.bookings.rate') }}</a>
        @endif
        @if (in_array($item['status'], ['declined', 'expired', 'cancelled', 'completed'], true) && $item['url'])
            <a href="{{ $item['url'] }}" class="ga-btn ga-btn--outline ga-btn--sm">{{ __('guide_area.customer.bookings.book_again') }}</a>
        @endif
        <button type="button" class="ga-disclosure" @click="details = !details" :aria-expanded="details.toString()">
            <span x-text="details ? @js(__('guide_area.bookings.hide_details')) : @js(__('guide_area.bookings.show_details'))">{{ $focused ? __('guide_area.bookings.hide_details') : __('guide_area.bookings.show_details') }}</span>
            <i class="fas fa-caret-down" :class="{ 'fa-rotate-180': details }" aria-hidden="true"></i>
        </button>
    </div>

    <div class="ga-details" x-show="details" @if (! $focused) x-cloak @endif>
        <div class="ga-details__col">
            <p class="ga-details__head">{{ __('guide_area.bookings.d_tour') }}</p>
            <dl>
                <div><dt>{{ __('guide_area.bookings.d_location') }}</dt><dd>{{ $item['location'] ?: '—' }}</dd></div>
                <div><dt>{{ __('guide_area.bookings.d_meeting_point') }}</dt><dd>{{ $item['meeting_point'] ?: __('guide_area.customer.bookings.after_accept') }}</dd></div>
                <div><dt>{{ __('guide_area.bookings.d_duration') }}</dt><dd>{{ $item['duration'] ?? '—' }}</dd></div>
            </dl>
        </div>
        <div class="ga-details__col">
            <p class="ga-details__head">{{ __('guide_area.bookings.d_reservation') }}</p>
            <dl>
                <div><dt>{{ __('guide_area.bookings.d_booking_id') }}</dt><dd>#{{ $item['id'] }}</dd></div>
                <div><dt>{{ __('guide_area.bookings.tour_date') }}</dt><dd>{{ $item['date_label'] }}</dd></div>
                <div><dt>{{ __('guide_area.bookings.d_status') }}</dt><dd>{{ $item['status_label'] }}</dd></div>
            </dl>
        </div>
        <div class="ga-details__col">
            <p class="ga-details__head">{{ __('guide_area.customer.bookings.guide') }}</p>
            <dl>
                <div><dt>{{ __('guide_area.bookings.d_name') }}</dt><dd>{{ $item['guide']['name'] }}</dd></div>
                <div><dt>{{ __('guide_area.bookings.d_email') }}</dt><dd>{{ $item['guide']['email'] ?? __('guide_area.customer.bookings.after_accept') }}</dd></div>
                <div><dt>{{ __('guide_area.bookings.d_phone') }}</dt><dd>{{ $item['guide']['phone'] ?? __('guide_area.customer.bookings.after_accept') }}</dd></div>
            </dl>
        </div>
        <div class="ga-details__col">
            <p class="ga-details__head">{{ __('guide_area.bookings.d_price') }}</p>
            <dl>
                @foreach ($item['extras'] as $extra)
                    <div><dt>{{ $extra['name'] }}</dt><dd class="ga-amount">{{ $extra['price_label'] }}</dd></div>
                @endforeach
                <div><dt>{{ __('guide_area.bookings.d_total') }}</dt><dd class="ga-amount">{{ $item['price_label'] }}</dd></div>
            </dl>
        </div>
    </div>
</li>
