<div class="ga-page">
    {{-- Header (spec OV-01 / OV-02) --}}
    <header class="ga-page__header">
        <div>
            <h1 class="ga-page__title">{{ $greeting }}</h1>
            <p class="ga-page__subtitle">
                {{ $today }}
                · <a href="{{ route('guide.area.bookings') }}" @class(['ga-urgent' => $openCount > 0, 'ga-plain-link' => true])>{{ trans_choice('guide_area.overview.requests_waiting', $openCount, ['count' => $openCount]) }}</a>
                · <a href="{{ route('guide.area.todos') }}" class="ga-plain-link">{{ trans_choice('guide_area.overview.open_todos', $score['open'], ['count' => $score['open']]) }}</a>
            </p>
        </div>
        <div class="ga-page__actions">
            <a href="{{ route('guide.area.calendar') }}" class="ga-btn ga-btn--dark"><i class="far fa-calendar" aria-hidden="true"></i> {{ __('guide_area.overview.manage_calendar') }}</a>
            <a href="{{ route('profile.newguiding') }}" class="ga-btn ga-btn--dark"><i class="fas fa-plus" aria-hidden="true"></i> {{ __('guide_area.overview.new_tour') }}</a>
        </div>
    </header>

    {{-- Waiting for you (spec OV-03…OV-05) --}}
    <section class="ga-card">
        <div class="ga-card__head">
            <h2 class="ga-card__title">{{ __('guide_area.overview.waiting') }} <span class="ga-count ga-count--warning">{{ $requests->count() + $vouchers->count() }}</span></h2>
            <a href="{{ route('guide.area.bookings') }}" class="ga-card__link">{{ __('guide_area.overview.all_bookings') }} <i class="fas fa-arrow-right" aria-hidden="true"></i></a>
        </div>
        @if ($requests->isEmpty() && $vouchers->isEmpty())
            <p class="ga-empty ga-empty--inline"><i class="fas fa-check-circle" aria-hidden="true"></i> {{ __('guide_area.overview.caught_up') }}</p>
        @else
            <ul class="ga-action-list">
                @foreach ($requests as $item)
                    <li @class(['ga-action-row', 'ga-action-row--highlight' => $loop->first && $item['reply_urgent']]) wire:key="ov-req-{{ $item['id'] }}">
                        <span class="ga-action-row__icon ga-action-row__icon--warning" aria-hidden="true"><i class="fas fa-inbox"></i></span>
                        <div class="ga-action-row__body">
                            <p class="ga-eyebrow ga-eyebrow--warning">{{ __('guide_area.overview.new_request') }} · #{{ $item['id'] }}
                                @if ($item['reply_label']) · <span @class(['ga-urgent' => $item['reply_urgent']])><i class="far fa-clock" aria-hidden="true"></i> {{ $item['reply_label'] }}</span>@endif
                            </p>
                            <p class="ga-action-row__title">{{ $item['tour']['title'] }}</p>
                            <p class="ga-action-row__meta">{{ $item['date_label'] }} · {{ trans_choice('guide_area.overview.anglers', $item['guests'], ['count' => $item['guests']]) }} · <strong>{{ $item['price_label'] }}</strong></p>
                            <div class="ga-action-row__buttons">
                                <button type="button" class="ga-btn ga-btn--success ga-btn--sm" wire:click="accept({{ $item['id'] }})" wire:loading.attr="disabled">{{ __('guide_area.bookings.accept') }}</button>
                                <a href="{{ route('guide.area.bookings', ['booking' => $item['id'], 'decline' => 1]) }}" class="ga-btn ga-btn--outline ga-btn--sm">{{ __('guide_area.bookings.decline') }}</a>
                            </div>
                        </div>
                    </li>
                @endforeach
                @foreach ($vouchers as $order)
                    <li class="ga-action-row" wire:key="ov-voucher-{{ $order['id'] }}">
                        <span class="ga-action-row__icon ga-action-row__icon--info" aria-hidden="true"><i class="fas fa-ticket-alt"></i></span>
                        <div class="ga-action-row__body">
                            <p class="ga-eyebrow ga-eyebrow--info">{{ __('guide_area.bookings.voucher_order') }} · {{ $order['public_id'] ?? '#'.$order['id'] }}</p>
                            <p class="ga-action-row__title">{{ __('guide_area.overview.voucher_title', ['tour' => $order['tour_title'], 'price' => $order['price_label']]) }}</p>
                            <p class="ga-action-row__meta">
                                {{ $order['buyer']['name'] }} · {{ __('guide_area.bookings.ordered_on', ['date' => $order['created_label']]) }}
                                @if ($order['buyer']['email']) · <a href="mailto:{{ $order['buyer']['email'] }}">{{ $order['buyer']['email'] }}</a>@endif
                                @if ($order['buyer']['phone']) · <a href="tel:{{ preg_replace('/[^0-9+]/', '', $order['buyer']['phone']) }}">{{ $order['buyer']['phone'] }}</a>@endif
                                · {{ __('guide_area.overview.voucher_hint') }}
                            </p>
                            @if ($order['pdf_url'])
                                <div class="ga-action-row__buttons">
                                    <a href="{{ $order['pdf_url'] }}" class="ga-btn ga-btn--dark ga-btn--sm">{{ __('guide_area.overview.voucher_pdf') }}</a>
                                </div>
                            @endif
                        </div>
                    </li>
                @endforeach
            </ul>
        @endif
    </section>

    {{-- Upcoming tours (spec OV-06) --}}
    <section class="ga-card">
        <div class="ga-card__head">
            <h2 class="ga-card__title">{{ __('guide_area.overview.upcoming') }}</h2>
            <a href="{{ route('guide.area.bookings', ['status' => 'confirmed']) }}" class="ga-card__link">{{ __('guide_area.overview.all_bookings') }} <i class="fas fa-arrow-right" aria-hidden="true"></i></a>
        </div>
        @forelse ($upcoming as $item)
            <div class="ga-upcoming" wire:key="ov-up-{{ $item['id'] }}">
                @if ($item['date_short'])
                    <span @class(['ga-date-chip', 'ga-date-chip--dark' => $loop->first])>
                        <span>{{ $item['date_short']['weekday'] }}</span><strong>{{ $item['date_short']['day'] }}</strong><span>{{ $item['date_short']['month'] }}</span>
                    </span>
                @endif
                <div>
                    <p class="ga-upcoming__tour">{{ $item['tour']['title'] }}</p>
                    <p class="ga-upcoming__meta">{{ trans_choice('guide_area.overview.anglers', $item['guests'], ['count' => $item['guests']]) }}</p>
                    <p class="ga-upcoming__guest">
                        <strong>{{ $item['guest']['name'] }}</strong>
                        @if ($item['guest']['phone']) <a href="tel:{{ preg_replace('/[^0-9+]/', '', $item['guest']['phone']) }}">{{ $item['guest']['phone'] }}</a>@endif
                        @if ($item['guest']['email']) <a href="mailto:{{ $item['guest']['email'] }}">{{ $item['guest']['email'] }}</a>@endif
                    </p>
                </div>
            </div>
        @empty
            <p class="ga-empty ga-empty--inline">{{ __('guide_area.overview.no_upcoming') }}</p>
        @endforelse
    </section>

    {{-- To-dos (spec OV-07) --}}
    <section class="ga-card">
        <div class="ga-card__head">
            <h2 class="ga-card__title">{{ __('guide_area.todos.title') }} <span class="ga-count">{{ $score['open'] }}</span></h2>
            <a href="{{ route('guide.area.todos') }}" class="ga-card__link">{{ __('guide_area.overview.all_todos') }} <i class="fas fa-arrow-right" aria-hidden="true"></i></a>
        </div>
        <div class="ga-progress ga-progress--labelled" role="progressbar" aria-valuemin="0" aria-valuemax="100" aria-valuenow="{{ $score['score'] }}">
            <span class="ga-progress__bar" style="width: {{ $score['score'] }}%"></span>
        </div>
        <p class="ga-help">{{ __('guide_area.todos.score', ['score' => $score['score']]) }}</p>
        @if ($topTodos === [])
            <p class="ga-empty ga-empty--inline">{{ __('guide_area.todos.all_done') }}</p>
        @else
            <ul class="ga-link-list">
                @foreach ($topTodos as $todo)
                    <li>
                        <a href="{{ $todo['fix_url'] }}" class="ga-link-list__item">
                            <span>
                                <span class="ga-eyebrow">{{ __('guide_area.todos.sections.'.$todo['section'].'.title') }}@if ($todo['context']) · {{ $todo['context'] }}@endif</span>
                                <span class="ga-link-list__title">{{ $todo['label'] }}</span>
                                @if ($todo['hint'])<span class="ga-link-list__hint">{{ $todo['hint'] }}</span>@endif
                            </span>
                            <i class="fas fa-arrow-right" aria-hidden="true"></i>
                        </a>
                    </li>
                @endforeach
            </ul>
        @endif
    </section>

    {{-- My fishing tours (spec OV-08) --}}
    <section class="ga-card">
        <div class="ga-card__head">
            <h2 class="ga-card__title">{{ __('guide_area.nav.tours') }}</h2>
            <a href="{{ route('guide.area.tours') }}" class="ga-card__link">{{ __('guide_area.overview.all_tours') }} <i class="fas fa-arrow-right" aria-hidden="true"></i></a>
        </div>
        <div class="ga-stat-grid ga-stat-grid--2">
            <div class="ga-stat ga-stat--positive"><span class="ga-stat__label">{{ __('guide_area.overview.live') }}</span><span class="ga-stat__value">{{ $liveTours }}</span></div>
            <div class="ga-stat"><span class="ga-stat__label">{{ __('guide_area.overview.total') }}</span><span class="ga-stat__value">{{ $totalTours }}</span></div>
        </div>
        <div class="ga-progress ga-progress--success" role="progressbar" aria-valuemin="0" aria-valuemax="{{ max(1, $totalTours) }}" aria-valuenow="{{ $liveTours }}" aria-label="{{ __('guide_area.overview.live_of', ['live' => $liveTours, 'total' => $totalTours]) }}">
            <span class="ga-progress__bar" style="width: {{ $totalTours ? round($liveTours / $totalTours * 100) : 0 }}%"></span>
        </div>
    </section>

    {{-- Latest review (spec OV-09) --}}
    <section class="ga-card">
        <div class="ga-card__head">
            <h2 class="ga-card__title">{{ __('guide_area.overview.latest_review') }}</h2>
            <a href="{{ route('guide.area.reviews') }}" class="ga-card__link">{{ __('guide_area.overview.all_reviews') }} <i class="fas fa-arrow-right" aria-hidden="true"></i></a>
        </div>
        @if ($latest)
            <span class="ga-score-pill">{{ number_format($latest['score'], 1) }}<small>/10</small></span>
            @if ($latest['comment'] !== '')
                <p class="ga-quote">“{{ str($latest['comment'])->limit(220) }}”</p>
            @endif
            <p class="ga-muted ga-small">{{ $latest['guest'] }}@if ($latest['tour']) · {{ $latest['tour'] }}@endif @if ($latest['date']) · {{ $latest['date'] }}@endif</p>
        @else
            <p class="ga-empty ga-empty--inline">{{ __('guide_area.overview.no_reviews') }}</p>
        @endif
    </section>
</div>
