<div class="ga-page">
    <header class="ga-page__header">
        <div>
            <h1 class="ga-page__title">{{ $greeting }}</h1>
            <p class="ga-page__subtitle">
                {{ $today }}
                · <a href="{{ route('profile.bookings', ['status' => 'confirmed']) }}" class="ga-plain-link">{{ trans_choice('guide_area.customer.overview.upcoming_count', $upcomingCount, ['count' => $upcomingCount]) }}</a>
                @if ($waiting->isNotEmpty())
                    · <a href="{{ route('profile.bookings', ['status' => 'open']) }}" class="ga-plain-link">{{ trans_choice('guide_area.customer.overview.waiting_count', $waiting->count(), ['count' => $waiting->count()]) }}</a>
                @endif
            </p>
        </div>
        <div class="ga-page__actions">
            <a href="{{ route('guidings.index') }}" class="ga-btn ga-btn--dark"><i class="fas fa-search" aria-hidden="true"></i> {{ __('guide_area.customer.overview.find_tour') }}</a>
            <a href="{{ route('vacations.index') }}" class="ga-btn ga-btn--outline"><i class="fas fa-suitcase-rolling" aria-hidden="true"></i> {{ __('guide_area.customer.overview.find_vacation') }}</a>
        </div>
    </header>

    {{-- Next trips --}}
    <section class="ga-card">
        <div class="ga-card__head">
            <h2 class="ga-card__title">{{ __('guide_area.customer.overview.next_trips') }}</h2>
            <a href="{{ route('profile.bookings') }}" class="ga-card__link">{{ __('guide_area.customer.overview.all_bookings') }} <i class="fas fa-arrow-right" aria-hidden="true"></i></a>
        </div>
        @forelse ($upcoming as $item)
            <div class="ga-upcoming" wire:key="cu-up-{{ $item['id'] }}">
                @if ($item['date_short'])
                    <span @class(['ga-date-chip', 'ga-date-chip--dark' => $loop->first])>
                        <span>{{ $item['date_short']['weekday'] }}</span><strong>{{ $item['date_short']['day'] }}</strong><span>{{ $item['date_short']['month'] }}</span>
                    </span>
                @endif
                <div>
                    <p class="ga-upcoming__tour">{{ $item['title'] }}</p>
                    <p class="ga-upcoming__meta">
                        {{ trans_choice('guide_area.bookings.people', $item['guests'], ['count' => $item['guests']]) }}
                        @if ($item['location'] !== '') · {{ $item['location'] }}@endif
                    </p>
                    <p class="ga-upcoming__guest">
                        <strong>{{ $item['guide']['name'] }}</strong>
                        @if ($item['guide']['phone']) <a href="tel:{{ preg_replace('/[^0-9+]/', '', $item['guide']['phone']) }}">{{ $item['guide']['phone'] }}</a>@endif
                        @if ($item['guide']['email']) <a href="mailto:{{ $item['guide']['email'] }}">{{ $item['guide']['email'] }}</a>@endif
                    </p>
                </div>
            </div>
        @empty
            <div class="ga-empty ga-empty--inline">
                <p>{{ __('guide_area.customer.overview.no_trips') }}</p>
                <a href="{{ route('guidings.index') }}" class="ga-btn ga-btn--outline ga-btn--sm">{{ __('guide_area.customer.overview.find_tour') }}</a>
            </div>
        @endforelse
    </section>

    {{-- Waiting for an answer --}}
    @if ($waiting->isNotEmpty())
        <section class="ga-card">
            <div class="ga-card__head">
                <h2 class="ga-card__title">{{ __('guide_area.customer.overview.waiting') }} <span class="ga-count ga-count--warning">{{ $waiting->count() }}</span></h2>
            </div>
            <ul class="ga-action-list">
                @foreach ($waiting as $item)
                    <li class="ga-action-row" wire:key="cu-wait-{{ $item['type'] }}-{{ $item['id'] }}">
                        <span class="ga-action-row__icon ga-action-row__icon--warning" aria-hidden="true"><i class="fas {{ $item['type'] === 'tour' ? 'fa-fish' : ($item['type'] === 'camp' ? 'fa-campground' : 'fa-route') }}"></i></span>
                        <div class="ga-action-row__body">
                            <p class="ga-eyebrow ga-eyebrow--warning">{{ __('guide_area.customer.bookings.kind.'.$item['type']) }} · #{{ $item['id'] }} · {{ $item['status_label'] }}</p>
                            <p class="ga-action-row__title">{{ $item['title'] }}</p>
                            <p class="ga-action-row__meta">
                                {{ $item['date_label'] }} · {{ trans_choice('guide_area.bookings.people', $item['guests'], ['count' => $item['guests']]) }}
                                @if (! empty($item['answer_by'])) · {{ __('guide_area.customer.bookings.answer_by', ['date' => $item['answer_by']]) }}@endif
                            </p>
                        </div>
                        <div class="ga-action-row__actions">
                            <a href="{{ $item['type'] === 'tour' ? route('profile.bookings', ['booking' => $item['id']]) : route('profile.bookings', ['tab' => $item['type'].'s']) }}" class="ga-link">{{ __('guide_area.bookings.details') }}</a>
                        </div>
                    </li>
                @endforeach
            </ul>
        </section>
    @endif

    {{-- Trips to rate --}}
    @if ($toRate->isNotEmpty())
        <section class="ga-card">
            <div class="ga-card__head">
                <h2 class="ga-card__title">{{ __('guide_area.customer.overview.rate_title') }}</h2>
            </div>
            <ul class="ga-action-list">
                @foreach ($toRate as $item)
                    <li class="ga-action-row" wire:key="cu-rate-{{ $item['id'] }}">
                        <span class="ga-action-row__icon ga-action-row__icon--info" aria-hidden="true"><i class="fas fa-star"></i></span>
                        <div class="ga-action-row__body">
                            <p class="ga-action-row__title">{{ $item['title'] }}</p>
                            <p class="ga-action-row__meta">{{ $item['date_label'] }} · {{ $item['guide']['name'] }}</p>
                        </div>
                        @if ($item['rate_url'])
                            <div class="ga-action-row__actions">
                                <a href="{{ $item['rate_url'] }}" class="ga-btn ga-btn--dark ga-btn--sm">{{ __('guide_area.customer.bookings.rate') }}</a>
                            </div>
                        @endif
                    </li>
                @endforeach
            </ul>
        </section>
    @endif

    {{-- Bookings by type --}}
    <section class="ga-card">
        <div class="ga-card__head">
            <h2 class="ga-card__title">{{ __('guide_area.customer.nav.bookings') }}</h2>
            <a href="{{ route('profile.bookings') }}" class="ga-card__link">{{ __('guide_area.customer.overview.all_bookings') }} <i class="fas fa-arrow-right" aria-hidden="true"></i></a>
        </div>
        <div class="ga-tiles ga-tiles--4">
            @foreach ($tabCounts as $tab => $count)
                <a href="{{ route('profile.bookings', $tab === 'tours' ? [] : ['tab' => $tab]) }}" class="ga-tile">
                    <span class="ga-tile__value">{{ $count }}</span>
                    <span class="ga-tile__label">{{ __('guide_area.customer.bookings.tabs.'.$tab) }}</span>
                </a>
            @endforeach
        </div>
    </section>

    {{-- Favourites --}}
    <section class="ga-card">
        <div class="ga-card__head">
            <h2 class="ga-card__title">{{ __('guide_area.customer.nav.favourites') }} <span class="ga-count">{{ $favouriteCount }}</span></h2>
            @if ($favouriteCount > 0)
                <a href="{{ route('profile.favoriteguides') }}" class="ga-card__link">{{ __('guide_area.customer.overview.all_favourites') }} <i class="fas fa-arrow-right" aria-hidden="true"></i></a>
            @endif
        </div>
        @if ($favourites->isEmpty())
            <p class="ga-empty ga-empty--inline"><i class="far fa-heart" aria-hidden="true"></i> {{ __('guide_area.customer.favourites.empty_short') }}</p>
        @else
            <div class="ga-fav-grid ga-fav-grid--compact">
                @foreach ($favourites as $fav)
                    @include('livewire.customer.partials.favourite-card', ['fav' => $fav, 'removable' => false])
                @endforeach
            </div>
        @endif
    </section>

    @if ($becomeGuideUrl)
        <section class="ga-card ga-card--sand ga-become-guide">
            <div>
                <h2 class="ga-card__title">{{ __('guide_area.customer.become_guide.title') }}</h2>
                <p class="ga-card__subtitle">{{ __('guide_area.customer.become_guide.long') }}</p>
            </div>
            <a href="{{ $becomeGuideUrl }}" class="ga-btn ga-btn--coral become-guide-link"><i class="fas fa-fish" aria-hidden="true"></i> {{ __('guide_area.customer.become_guide.cta') }}</a>
        </section>
    @endif
</div>
