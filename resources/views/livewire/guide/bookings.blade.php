<div class="ga-page">
    <header class="ga-page__header">
        <div>
            <h1 class="ga-page__title">{{ __('guide_area.bookings.title') }}</h1>
            <p class="ga-page__subtitle">{{ __('guide_area.bookings.subtitle') }}</p>
        </div>
    </header>

    {{-- New & open (spec BK-01) --}}
    <section class="ga-card ga-open" aria-labelledby="ga-open-title">
        <div class="ga-card__head">
            <h2 class="ga-card__title" id="ga-open-title">
                {{ __('guide_area.bookings.new_open') }}
                <span class="ga-count ga-count--warning">{{ $openRequests->count() + $openVouchers->count() }}</span>
            </h2>
            <span class="ga-card__aside">{{ __('guide_area.bookings.new_open_hint') }}</span>
        </div>

        @if ($openRequests->isEmpty() && $openVouchers->isEmpty())
            <p class="ga-empty ga-empty--inline"><i class="fas fa-check-circle" aria-hidden="true"></i> {{ __('guide_area.bookings.all_caught_up') }}</p>
        @else
            <ul class="ga-action-list">
                @foreach ($openRequests as $item)
                    <li class="ga-action-row" wire:key="open-booking-{{ $item['id'] }}">
                        <span class="ga-action-row__icon ga-action-row__icon--warning" aria-hidden="true"><i class="fas fa-fish"></i></span>
                        <div class="ga-action-row__body">
                            <p class="ga-eyebrow ga-eyebrow--warning">
                                {{ __('guide_area.bookings.tour_request') }} · #{{ $item['id'] }}
                                @if ($item['reply_label'])
                                    · <span @class(['ga-urgent' => $item['reply_urgent']])>{{ $item['reply_label'] }}</span>
                                @endif
                            </p>
                            <p class="ga-action-row__title">{{ $item['tour']['title'] }}</p>
                            <p class="ga-action-row__meta">{{ $item['date_label'] }} · {{ trans_choice('guide_area.bookings.people', $item['guests'], ['count' => $item['guests']]) }} · {{ $item['price_label'] }} · {{ $item['guest']['name'] }}</p>
                        </div>
                        <div class="ga-action-row__actions">
                            <button type="button" class="ga-btn ga-btn--success ga-btn--sm" wire:click="accept({{ $item['id'] }})" wire:loading.attr="disabled">{{ __('guide_area.bookings.accept') }}</button>
                            <button type="button" class="ga-btn ga-btn--outline ga-btn--sm" wire:click="focus({{ $item['id'] }}, true)">{{ __('guide_area.bookings.decline_short') }}</button>
                            <button type="button" class="ga-link" wire:click="focus({{ $item['id'] }})">{{ __('guide_area.bookings.details') }}</button>
                        </div>
                    </li>
                @endforeach
                @foreach ($openVouchers as $order)
                    <li class="ga-action-row" wire:key="open-voucher-{{ $order['id'] }}">
                        <span class="ga-action-row__icon ga-action-row__icon--info" aria-hidden="true"><i class="fas fa-ticket-alt"></i></span>
                        <div class="ga-action-row__body">
                            <p class="ga-eyebrow ga-eyebrow--info">
                                {{ __('guide_area.bookings.voucher_order') }} · {{ $order['public_id'] ?? '#'.$order['id'] }}
                                · {{ $order['is_paid'] ? __('guide_area.bookings.voucher_paid_not_sent') : __('guide_area.bookings.voucher_payment_pending') }}
                            </p>
                            <p class="ga-action-row__title">{{ __('guide_area.bookings.voucher_title', ['tour' => $order['tour_title']]) }}</p>
                            <p class="ga-action-row__meta">
                                {{ $order['price_label'] }} · {{ trans_choice('guide_area.bookings.people', $order['number_of_guests'], ['count' => $order['number_of_guests']]) }}
                                · {{ __('guide_area.bookings.ordered_on', ['date' => $order['created_label']]) }} · {{ $order['buyer']['name'] }}
                            </p>
                        </div>
                        <div class="ga-action-row__actions">
                            <label class="ga-check">
                                <input type="checkbox" @checked($order['is_paid']) wire:click="toggleVoucherPaid({{ $order['id'] }})">
                                <span>{{ __('guide_area.vouchers.paid') }}</span>
                            </label>
                            <label class="ga-check">
                                <input type="checkbox" @checked($order['is_sent']) wire:click="toggleVoucherSent({{ $order['id'] }})">
                                <span>{{ __('guide_area.vouchers.sent') }}</span>
                            </label>
                            <button type="button" class="ga-link" wire:click="setTab('vouchers')">{{ __('guide_area.bookings.details') }}</button>
                        </div>
                    </li>
                @endforeach
            </ul>
        @endif
    </section>

    {{-- Tabs (spec BK-02) --}}
    <div class="ga-tabs ga-tabs--underline" role="tablist" aria-label="{{ __('guide_area.bookings.tabs_label') }}">
        <button type="button" role="tab" class="ga-tabs__tab {{ $tab === 'tours' ? 'is-active' : '' }}" aria-selected="{{ $tab === 'tours' ? 'true' : 'false' }}" wire:click="setTab('tours')">
            {{ __('guide_area.bookings.tab_tours') }}
            @if ($counts['open'] > 0)
                <span class="ga-tabs__count ga-tabs__count--warning">{{ __('guide_area.bookings.n_open', ['count' => $counts['open']]) }}</span>
            @endif
        </button>
        <button type="button" role="tab" class="ga-tabs__tab {{ $tab === 'vouchers' ? 'is-active' : '' }}" aria-selected="{{ $tab === 'vouchers' ? 'true' : 'false' }}" wire:click="setTab('vouchers')">
            {{ __('guide_area.bookings.tab_vouchers') }}
            @if ($voucherCounts['todo'] > 0)
                <span class="ga-tabs__count ga-tabs__count--info">{{ __('guide_area.bookings.n_open', ['count' => $voucherCounts['todo']]) }}</span>
            @endif
        </button>
    </div>

    @if ($tab === 'vouchers')
        <livewire:guide.vouchers :embedded="true" :key="'bookings-vouchers'" />
    @else
        <section class="ga-card">
            <div class="ga-tiles ga-tiles--5">
                @foreach ($filters as $key)
                    <button type="button" @class(['ga-tile', 'is-active' => $status === $key, 'ga-tile--warning' => $key === 'open' && $counts['open'] > 0])
                            wire:click="setStatus('{{ $key }}')" aria-pressed="{{ $status === $key ? 'true' : 'false' }}">
                        <span class="ga-tile__value">{{ $counts[$key] }}</span>
                        <span class="ga-tile__label">{{ __('guide_area.bookings.filters.'.$key) }}</span>
                    </button>
                @endforeach
            </div>

            <div class="ga-toolbar">
                <label class="ga-search ga-search--grow">
                    <span class="ga-visually-hidden">{{ __('guide_area.bookings.search') }}</span>
                    <i class="fas fa-search" aria-hidden="true"></i>
                    <input type="search" class="ga-input" wire:model.live.debounce.400ms="search" placeholder="{{ __('guide_area.bookings.search') }}">
                </label>
                <label class="ga-inline-field">
                    <span>{{ __('guide_area.bookings.tour') }}</span>
                    <select class="ga-input" wire:model.live="tour">
                        <option value="0">{{ __('guide_area.bookings.all_tours') }}</option>
                        @foreach ($tours as $t)
                            <option value="{{ $t->id }}">{{ $t->title ?: __('guide_area.tours.untitled') }}</option>
                        @endforeach
                    </select>
                </label>
                <label class="ga-inline-field">
                    <span>{{ __('guide_area.bookings.period') }}</span>
                    <select class="ga-input" wire:model.live="period">
                        @foreach ($periods as $p)
                            <option value="{{ $p }}">{{ __('guide_area.bookings.periods.'.$p) }}</option>
                        @endforeach
                    </select>
                </label>
            </div>
        </section>

        <div class="ga-section-head">
            <h2 class="ga-section-head__title">{{ __('guide_area.bookings.tour_bookings') }}</h2>
            <span class="ga-section-head__aside">
                {{ trans_choice('guide_area.bookings.shown', $bookings->total(), ['count' => $bookings->total()]) }}
                @if ($status !== 'all') · {{ __('guide_area.bookings.filter_label', ['filter' => __('guide_area.bookings.filters.'.$status)]) }} @endif
            </span>
        </div>

        @if ($bookings->isEmpty())
            <div class="ga-empty">
                <i class="fas fa-inbox" aria-hidden="true"></i>
                <p>{{ $search !== '' || $status !== 'all' || $tour || $period !== 'all' ? __('guide_area.bookings.empty_filtered') : __('guide_area.bookings.empty') }}</p>
            </div>
        @else
            <ul class="ga-booking-list">
                @foreach ($bookings as $item)
                    @include('livewire.guide.partials.booking-card', ['item' => $item])
                @endforeach
            </ul>

            {{ $bookings->links('livewire.guide.partials.pager') }}
        @endif
    @endif
</div>
