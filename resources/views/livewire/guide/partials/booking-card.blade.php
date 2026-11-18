{{-- One tour booking card (Guide Area › Bookings, spec BK-05…BK-08). $item from GuideBookingPresenter. --}}
@php
    $isOpen = $item['status'] === 'open';
    $expanded = $booking === $item['id'] || $declining === $item['id'] || $cancelling === $item['id'];
@endphp
<li id="booking-{{ $item['id'] }}" wire:key="booking-{{ $item['id'] }}"
    @class(['ga-booking', 'ga-booking--open' => $isOpen, 'is-focused' => $booking === $item['id']])
    x-data="{ details: @js($expanded) }">
    <div class="ga-booking__head">
        <div class="ga-booking__badges">
            <span class="ga-id">#{{ $item['id'] }}</span>
            <span class="ga-status ga-status--{{ $item['status_tone'] }}">{{ $item['status_label'] }}</span>
        </div>
        @if ($item['reply_label'])
            <span @class(['ga-booking__reply', 'ga-urgent' => $item['reply_urgent']])><i class="far fa-clock" aria-hidden="true"></i> {{ $item['reply_label'] }}</span>
        @endif
    </div>

    <h3 class="ga-booking__title">{{ $item['tour']['title'] }}</h3>
    @if ($item['tour']['location'] !== '')
        <p class="ga-booking__location"><i class="fas fa-map-marker-alt" aria-hidden="true"></i> {{ $item['tour']['location'] }}</p>
    @endif

    <dl class="ga-facts">
        <div><dt>{{ __('guide_area.bookings.tour_date') }}</dt><dd>{{ $item['date_label'] }}</dd></div>
        <div><dt>{{ __('guide_area.bookings.guests') }}</dt><dd>{{ trans_choice('guide_area.bookings.people', $item['guests'], ['count' => $item['guests']]) }}</dd></div>
        <div><dt>{{ __('guide_area.bookings.price') }}</dt><dd>{{ $item['price_label'] }}</dd></div>
        <div><dt>{{ __('guide_area.bookings.requested_on') }}</dt><dd>{{ $item['requested_label'] ?? '—' }}</dd></div>
    </dl>

    <p class="ga-booking__guest">
        <strong>{{ $item['guest']['name'] }}</strong>
        @if ($item['guest']['phone'] || $item['guest']['email'])
            @if ($item['guest']['phone']) · <a href="tel:{{ preg_replace('/[^0-9+]/', '', $item['guest']['phone']) }}">{{ $item['guest']['phone'] }}</a>@endif
            @if ($item['guest']['email']) · <a href="mailto:{{ $item['guest']['email'] }}">{{ $item['guest']['email'] }}</a>@endif
        @elseif ($isOpen)
            <span class="ga-muted">{{ __('guide_area.bookings.contact_after_accept') }}</span>
        @endif
    </p>

    @if ($item['status'] === 'declined' && $item['alternative_labels'] !== [])
        <p class="ga-booking__note">{{ __('guide_area.bookings.suggested', ['dates' => implode(', ', $item['alternative_labels'])]) }}</p>
    @endif
    @if ($item['cancel_reason_label'])
        <p class="ga-booking__note">{{ __('guide_area.bookings.cancel_reason_shown', ['reason' => $item['cancel_reason_label']]) }}</p>
    @endif

    <button type="button" class="ga-disclosure" @click="details = !details" :aria-expanded="details.toString()">
        <span x-text="details ? @js(__('guide_area.bookings.hide_details')) : @js(__('guide_area.bookings.show_details'))">{{ $expanded ? __('guide_area.bookings.hide_details') : __('guide_area.bookings.show_details') }}</span>
        <i class="fas fa-caret-down" :class="{ 'fa-rotate-180': details }" aria-hidden="true"></i>
    </button>

    <div class="ga-details" x-show="details" @if (! $expanded) x-cloak @endif>
        <div class="ga-details__col">
            <p class="ga-details__head">{{ __('guide_area.bookings.d_tour') }}</p>
            <dl>
            <div><dt>{{ __('guide_area.bookings.d_tour_name') }}</dt><dd>
                @if ($item['tour']['url'])<a href="{{ $item['tour']['url'] }}" target="_blank" rel="noopener">{{ $item['tour']['title'] }} <i class="fas fa-external-link-alt" aria-hidden="true"></i></a>@else{{ $item['tour']['title'] }}@endif
            </dd></div>
            <div><dt>{{ __('guide_area.bookings.d_location') }}</dt><dd>{{ $item['tour']['location'] ?: '—' }}</dd></div>
            <div><dt>{{ __('guide_area.bookings.d_meeting_point') }}</dt><dd>{{ $item['tour']['meeting_point'] ?: '—' }}</dd></div>
            <div><dt>{{ __('guide_area.bookings.d_duration') }}</dt><dd>{{ $item['tour']['duration'] ?? '—' }}</dd></div>
            <div><dt>{{ __('guide_area.bookings.d_type') }}</dt><dd>{{ $item['tour']['type'] ?? '—' }}</dd></div>
        </dl></div>
        <div class="ga-details__col">
            <p class="ga-details__head">{{ __('guide_area.bookings.d_reservation') }}</p>
            <dl>
            <div><dt>{{ __('guide_area.bookings.d_booking_id') }}</dt><dd>#{{ $item['id'] }}</dd></div>
            <div><dt>{{ __('guide_area.bookings.requested_on') }}</dt><dd>{{ $item['requested_label'] ?? '—' }}</dd></div>
            <div><dt>{{ __('guide_area.bookings.tour_date') }}</dt><dd>{{ $item['date_label'] }}</dd></div>
            <div><dt>{{ __('guide_area.bookings.guests') }}</dt><dd>{{ trans_choice('guide_area.bookings.people', $item['guests'], ['count' => $item['guests']]) }}</dd></div>
            <div><dt>{{ __('guide_area.bookings.d_status') }}</dt><dd>{{ $item['status_label'] }}</dd></div>
        </dl></div>
        <div class="ga-details__col">
            <p class="ga-details__head">{{ __('guide_area.bookings.d_guest') }}</p>
            <dl>
            <div><dt>{{ __('guide_area.bookings.d_name') }}</dt><dd>{{ $item['guest']['name'] }}</dd></div>
            <div><dt>{{ __('guide_area.bookings.d_email') }}</dt><dd>{{ $item['guest']['email'] ?? __('guide_area.bookings.after_accept') }}</dd></div>
            <div><dt>{{ __('guide_area.bookings.d_phone') }}</dt><dd>{{ $item['guest']['phone'] ?? __('guide_area.bookings.after_accept') }}</dd></div>
        </dl></div>
        <div class="ga-details__col">
            <p class="ga-details__head">{{ __('guide_area.bookings.d_price') }}</p>
            <dl>
            <div><dt>{{ __('guide_area.bookings.d_total') }}</dt><dd class="ga-amount">{{ $item['price_label'] }}</dd></div>
            <div><dt>{{ $item['commission_percent'] !== null ? __('guide_area.bookings.d_commission_pct', ['percent' => $item['commission_percent']]) : __('guide_area.bookings.d_commission') }}</dt><dd class="ga-amount">− {{ $item['commission_label'] }}</dd></div>
            <div><dt>{{ __('guide_area.bookings.d_you_receive') }}</dt><dd class="ga-positive ga-amount">{{ $item['guide_amount_label'] }}</dd></div>
        </dl></div>
    </div>

    {{-- Decline & suggest another date (spec BK-06; same rules as the emailed form) --}}
    @if ($declining === $item['id'])
        <form class="ga-inline-form" wire:submit="decline">
            <p class="ga-inline-form__title">{{ __('guide_area.bookings.decline_title') }}</p>
            <div class="ga-grid-3">
                @foreach (range(0, \App\Http\Livewire\Guide\Bookings::DECLINE_DATES - 1) as $i)
                    <label class="ga-field">
                        <span class="ga-label">{{ __('guide_area.bookings.alternative', ['n' => $i + 1]) }} @if ($i > 0)<span class="ga-optional">{{ __('guide_area.common.optional') }}</span>@endif</span>
                        <input type="date" class="ga-input" min="{{ $minDate }}" wire:model="declineDates.{{ $i }}">
                    </label>
                @endforeach
            </div>
            @error('declineDates') <p class="ga-error">{{ $message }}</p> @enderror
            @error('declineDates.*') <p class="ga-error">{{ $message }}</p> @enderror
            <label class="ga-field">
                <span class="ga-label">{{ __('guide_area.bookings.decline_message') }}</span>
                <textarea class="ga-textarea" rows="3" wire:model="declineMessage" placeholder="{{ __('guide_area.bookings.decline_placeholder') }}"></textarea>
                <span class="ga-help">{{ __('guide_area.bookings.decline_help', ['min' => \App\Http\Requests\RejectionRequest::MIN_MESSAGE]) }}</span>
            </label>
            @error('declineMessage') <p class="ga-error">{{ $message }}</p> @enderror
            <div class="ga-inline-form__actions">
                <button type="submit" class="ga-btn ga-btn--dark" wire:loading.attr="disabled">{{ __('guide_area.bookings.decline_submit') }}</button>
                <button type="button" class="ga-btn ga-btn--outline" wire:click="closeForms">{{ __('guide_area.common.cancel') }}</button>
            </div>
        </form>
    @endif

    {{-- Cancel a confirmed booking with a reason (spec BK-07) --}}
    @if ($cancelling === $item['id'])
        <form class="ga-inline-form ga-inline-form--danger" wire:submit="cancel">
            <p class="ga-inline-form__title">{{ __('guide_area.bookings.cancel_title') }}</p>
            <p class="ga-help">{{ __('guide_area.bookings.cancel_help') }}</p>
            <label class="ga-field">
                <span class="ga-label">{{ __('guide_area.bookings.cancel_reason') }}</span>
                <select class="ga-input" wire:model="cancelReason">
                    <option value="">{{ __('guide_area.bookings.cancel_reason_pick') }}</option>
                    @foreach ($cancelReasons as $reason)
                        <option value="{{ $reason }}">{{ __('guide_area.bookings.cancel_reasons.'.$reason) }}</option>
                    @endforeach
                </select>
            </label>
            @error('cancelReason') <p class="ga-error">{{ $message }}</p> @enderror
            <div class="ga-inline-form__actions">
                <button type="submit" class="ga-btn ga-btn--danger" wire:loading.attr="disabled">{{ __('guide_area.bookings.cancel_submit') }}</button>
                <button type="button" class="ga-btn ga-btn--outline" wire:click="closeForms">{{ __('guide_area.bookings.keep_booking') }}</button>
            </div>
        </form>
    @endif

    @if (($item['can_accept'] && $declining !== $item['id']) || ($item['status'] === 'confirmed' && ($cancelling !== $item['id'] || $item['guest']['phone'] || $item['guest']['email'])))
        <div class="ga-booking__actions">
            @if ($item['can_accept'] && $declining !== $item['id'])
                <button type="button" class="ga-btn ga-btn--success" wire:click="accept({{ $item['id'] }})" wire:loading.attr="disabled">{{ __('guide_area.bookings.accept') }}</button>
                <button type="button" class="ga-btn ga-btn--outline" wire:click="openDecline({{ $item['id'] }})">{{ __('guide_area.bookings.decline') }}</button>
            @elseif ($item['status'] === 'confirmed')
                @if ($item['guest']['phone'])
                    <a href="tel:{{ preg_replace('/[^0-9+]/', '', $item['guest']['phone']) }}" class="ga-btn ga-btn--outline"><i class="fas fa-phone" aria-hidden="true"></i> {{ __('guide_area.bookings.call') }}</a>
                @endif
                @if ($item['guest']['email'])
                    <a href="mailto:{{ $item['guest']['email'] }}" class="ga-btn ga-btn--outline"><i class="far fa-envelope" aria-hidden="true"></i> {{ __('guide_area.bookings.send_email') }}</a>
                @endif
                @if ($item['can_cancel'] && $cancelling !== $item['id'])
                    <button type="button" class="ga-btn ga-btn--danger-outline ga-booking__cancel" wire:click="openCancel({{ $item['id'] }})">
                        <i class="far fa-times-circle" aria-hidden="true"></i> {{ __('guide_area.bookings.cancel_booking') }}
                    </button>
                @endif
            @endif
        </div>
    @endif
</li>
