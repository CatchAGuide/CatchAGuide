<div class="ga-page">
    <header class="ga-page__header">
        <div>
            <h1 class="ga-page__title">{{ __('guide_area.vouchers.title') }}</h1>
            <p class="ga-page__subtitle">{{ __('guide_area.vouchers.subtitle') }}</p>
        </div>
        <a href="{{ route('guide.area.vouchers.settings') }}" class="ga-btn ga-btn--outline">
            <i class="fas fa-cog" aria-hidden="true"></i> {{ __('guide_area.vouchers.settings_link') }}
        </a>
    </header>

    <div class="ga-tiles">
        @foreach ($filters as $key)
            <button type="button" class="ga-tile {{ $filter === $key ? 'is-active' : '' }}" wire:click="setFilter('{{ $key }}')"
                    aria-pressed="{{ $filter === $key ? 'true' : 'false' }}">
                <span class="ga-tile__value">{{ $counts[$key] }}</span>
                <span class="ga-tile__label">{{ __('guide_area.vouchers.filters.'.$key) }}</span>
            </button>
        @endforeach
    </div>

    <div class="ga-toolbar ga-toolbar--end">
        <label class="ga-search">
            <span class="ga-visually-hidden">{{ __('guide_area.vouchers.search') }}</span>
            <i class="fas fa-search" aria-hidden="true"></i>
            <input type="search" class="ga-input" wire:model.live.debounce.400ms="search" placeholder="{{ __('guide_area.vouchers.search') }}">
        </label>
    </div>

    @if ($orders->isEmpty())
        <div class="ga-empty">
            <i class="fas fa-gift" aria-hidden="true"></i>
            <p>{{ $search !== '' ? __('guide_area.vouchers.empty_search') : __('guide_area.vouchers.empty') }}</p>
        </div>
    @else
        <ul class="ga-voucher-list">
            @foreach ($orders as $order)
                <li class="ga-voucher" wire:key="voucher-{{ $order['id'] }}">
                    <div class="ga-voucher__main">
                        <div class="ga-voucher__head">
                            <span class="ga-voucher__id">{{ $order['public_id'] ?? '#'.$order['id'] }}</span>
                            <span class="ga-voucher__date">{{ $order['created_label'] }}</span>
                        </div>
                        <h2 class="ga-voucher__title">{{ $order['tour_title'] }}</h2>
                        <p class="ga-voucher__meta">
                            {{ trans_choice('guide_area.vouchers.guests', $order['number_of_guests'], ['count' => $order['number_of_guests']]) }}
                            @if ($order['price_label'])
                                · {{ $order['price_label'] }}
                            @endif
                            @if ($order['valid_until_label'])
                                · {{ __('guide_area.vouchers.valid_until', ['date' => $order['valid_until_label']]) }}
                            @endif
                        </p>
                        <dl class="ga-voucher__people">
                            <div>
                                <dt>{{ __('guide_area.vouchers.buyer') }}</dt>
                                <dd>
                                    {{ $order['buyer']['name'] }}
                                    @if ($order['buyer']['email']) · <a href="mailto:{{ $order['buyer']['email'] }}">{{ $order['buyer']['email'] }}</a>@endif
                                    @if ($order['buyer']['phone']) · <a href="tel:{{ preg_replace('/[^0-9+]/', '', $order['buyer']['phone']) }}">{{ $order['buyer']['phone'] }}</a>@endif
                                </dd>
                            </div>
                            @if ($order['recipient_names'])
                                <div>
                                    <dt>{{ __('guide_area.vouchers.recipient') }}</dt>
                                    <dd>{{ $order['recipient_names'] }}</dd>
                                </div>
                            @endif
                            <div>
                                <dt>{{ __('guide_area.vouchers.code') }}</dt>
                                <dd><code>{{ $order['voucher_code'] }}</code></dd>
                            </div>
                        </dl>
                    </div>
                    <div class="ga-voucher__actions">
                        <label class="ga-switch">
                            <input type="checkbox" class="ga-switch__input" role="switch" @checked($order['is_paid']) wire:click="togglePaid({{ $order['id'] }})">
                            <span class="ga-switch__track" aria-hidden="true"></span>
                            <span class="ga-switch__label">{{ __('guide_area.vouchers.paid') }}</span>
                        </label>
                        <label class="ga-switch">
                            <input type="checkbox" class="ga-switch__input" role="switch" @checked($order['is_sent']) wire:click="toggleSent({{ $order['id'] }})">
                            <span class="ga-switch__track" aria-hidden="true"></span>
                            <span class="ga-switch__label">{{ __('guide_area.vouchers.sent') }}</span>
                        </label>
                        @if ($order['pdf_url'])
                            <a href="{{ $order['pdf_url'] }}" class="ga-btn ga-btn--outline ga-btn--sm">
                                <i class="fas fa-file-pdf" aria-hidden="true"></i> {{ __('guide_area.vouchers.pdf') }}
                            </a>
                        @endif
                    </div>
                </li>
            @endforeach
        </ul>

        {{ $orders->links('livewire.guide.partials.pager') }}
    @endif
</div>
