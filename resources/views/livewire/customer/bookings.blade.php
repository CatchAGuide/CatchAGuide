<div class="ga-page">
    <header class="ga-page__header">
        <div>
            <h1 class="ga-page__title">{{ __($guest ? 'guest_bookings.find.list_title' : 'guide_area.customer.bookings.title') }}</h1>
            <p class="ga-page__subtitle">{{ __($guest ? 'guest_bookings.find.list_subtitle' : 'guide_area.customer.bookings.subtitle') }}</p>
        </div>
        @if ($guest)
            <form method="POST" action="{{ route('guest-bookings.sign-out') }}" class="gb-page__signout">
                @csrf
                <button type="submit" class="ga-btn ga-btn--outline ga-btn--sm">{{ __('guest_bookings.find.sign_out') }}</button>
            </form>
        @endif
    </header>

    <div class="ga-tabs ga-tabs--underline" role="tablist" aria-label="{{ __('guide_area.customer.bookings.tabs_label') }}">
        @foreach ($tabs as $key)
            <button type="button" role="tab" class="ga-tabs__tab {{ $tab === $key ? 'is-active' : '' }}" aria-selected="{{ $tab === $key ? 'true' : 'false' }}" wire:click="setTab('{{ $key }}')">
                {{ __('guide_area.customer.bookings.tabs.'.$key) }}
                <span class="ga-tabs__count">{{ $tabCounts[$key] }}</span>
            </button>
        @endforeach
    </div>

    @if ($tab === 'tours')
        <div class="ga-tiles ga-tiles--5">
            @foreach ($filters as $key)
                <button type="button" @class(['ga-tile', 'is-active' => $status === $key, 'ga-tile--warning' => $key === 'open' && $counts['open'] > 0])
                        wire:click="setStatus('{{ $key }}')" aria-pressed="{{ $status === $key ? 'true' : 'false' }}">
                    <span class="ga-tile__value">{{ $counts[$key] }}</span>
                    <span class="ga-tile__label">{{ __('guide_area.customer.bookings.filters.'.$key) }}</span>
                </button>
            @endforeach
        </div>
    @else
        <p class="ga-help ga-customer-note"><i class="fas fa-info-circle" aria-hidden="true"></i> {{ __('guide_area.customer.bookings.hint.'.$tab) }}</p>
    @endif

    @if ($items->isEmpty())
        <div class="ga-empty">
            <i class="fas {{ $tab === 'vouchers' ? 'fa-ticket-alt' : 'fa-suitcase-rolling' }}" aria-hidden="true"></i>
            <p>{{ $tab === 'tours' && $status !== 'all' ? __('guide_area.customer.bookings.empty_filtered') : __('guide_area.customer.bookings.empty.'.$tab) }}</p>
            <a href="{{ $tab === 'camps' || $tab === 'trips' ? route('vacations.index') : route('guidings.index') }}" class="ga-btn ga-btn--outline ga-btn--sm">
                {{ $tab === 'camps' || $tab === 'trips' ? __('guide_area.customer.overview.find_vacation') : __('guide_area.customer.overview.find_tour') }}
            </a>
        </div>
    @else
        <ul class="ga-booking-list">
            @foreach ($items as $item)
                @if ($item['type'] === 'tour')
                    @include('livewire.customer.partials.tour-card', ['item' => $item, 'focused' => $booking === $item['id']])
                @elseif ($item['type'] === 'voucher')
                    @include('livewire.customer.partials.voucher-card', ['item' => $item])
                @else
                    @include('livewire.customer.partials.request-card', ['item' => $item])
                @endif
            @endforeach
        </ul>

        {{ $items->links('livewire.guide.partials.pager') }}
    @endif
</div>
