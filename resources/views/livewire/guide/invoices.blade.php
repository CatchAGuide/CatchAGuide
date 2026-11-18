<div class="ga-page">
    <header class="ga-page__header">
        <div>
            <h1 class="ga-page__title">{{ __('guide_area.invoices.title') }}</h1>
            <p class="ga-page__subtitle">{{ __('guide_area.invoices.subtitle') }}</p>
        </div>
        @if (count($years) > 1)
            <div class="ga-tabs" role="tablist" aria-label="{{ __('guide_area.invoices.year') }}">
                @foreach ($years as $y)
                    <button type="button" role="tab" class="ga-tabs__tab {{ $y === $selectedYear ? 'is-active' : '' }}" aria-selected="{{ $y === $selectedYear ? 'true' : 'false' }}" wire:click="$set('year', {{ $y }})">{{ $y }}</button>
                @endforeach
            </div>
        @endif
    </header>

    <section class="ga-card">
        <h2 class="ga-card__title">{{ __('guide_area.invoices.earnings_in', ['year' => $selectedYear]) }}</h2>

        {{-- Earnings tiles (spec IN-02) --}}
        <div class="ga-stat-grid ga-stat-grid--3">
            <div class="ga-stat">
                <span class="ga-stat__label">{{ __('guide_area.invoices.booking_value') }}</span>
                <span class="ga-stat__value">{{ $money($data['value']) }}</span>
                <span class="ga-stat__hint">{{ trans_choice('guide_area.invoices.bookings_via', $data['bookings'] + $data['vouchers'], ['count' => $data['bookings'] + $data['vouchers']]) }}</span>
            </div>
            <div class="ga-stat">
                <span class="ga-stat__label">{{ __('guide_area.invoices.commission') }}</span>
                <span class="ga-stat__value">{{ $money($data['commission']) }}</span>
                <span class="ga-stat__hint">
                    @if ($data['open_commission'] > 0)
                        @if ($data['open_due'] && $data['open_due']->isPast())
                            <span class="ga-urgent">{{ __('guide_area.invoices.overdue_amount', ['amount' => $money($data['open_commission']), 'date' => $data['open_due']->translatedFormat('j M Y')]) }}</span>
                        @else
                            {{ __('guide_area.invoices.open_amount', ['amount' => $money($data['open_commission'])]) }}@if ($data['open_due']) · {{ __('guide_area.invoices.due', ['date' => $data['open_due']->translatedFormat('j M')]) }}@endif
                        @endif
                    @else
                        {{ __('guide_area.invoices.nothing_open') }}
                    @endif
                </span>
            </div>
            <div class="ga-stat ga-stat--positive">
                <span class="ga-stat__label">{{ __('guide_area.invoices.your_earnings') }}</span>
                <span class="ga-stat__value">{{ $money($data['earnings']) }}</span>
                <span class="ga-stat__hint">{{ __('guide_area.invoices.after_commission') }}</span>
            </div>
        </div>

        {{-- Request tiles (spec IN-03) --}}
        <div class="ga-stat-grid ga-stat-grid--4">
            <div class="ga-stat ga-stat--plain"><span class="ga-stat__value">{{ $data['requests'] }}</span><span class="ga-stat__label">{{ __('guide_area.invoices.requests') }}</span><span class="ga-stat__hint">{{ __('guide_area.invoices.received') }}</span></div>
            <div class="ga-stat ga-stat--plain"><span class="ga-stat__value">{{ $data['accepted'] }}</span><span class="ga-stat__label">{{ __('guide_area.invoices.accepted') }}</span><span class="ga-stat__hint">{{ $data['acceptance_rate'] !== null ? __('guide_area.invoices.acceptance_rate', ['rate' => $data['acceptance_rate']]) : '—' }}</span></div>
            <div class="ga-stat ga-stat--plain"><span class="ga-stat__value">{{ $data['declined'] }}</span><span class="ga-stat__label">{{ __('guide_area.invoices.declined') }}</span><span class="ga-stat__hint">{{ __('guide_area.invoices.by_you') }}</span></div>
            <div class="ga-stat ga-stat--plain"><span class="ga-stat__value">{{ $data['expired'] }}</span><span class="ga-stat__label">{{ __('guide_area.invoices.expired') }}</span><span class="ga-stat__hint">{{ __('guide_area.invoices.no_reply') }}</span></div>
        </div>

        {{-- Monthly chart (spec IN-04): plain CSS bars, hover/focus shows the month's figures --}}
        <div class="ga-chart" x-data="{ active: null }">
            <div class="ga-chart__head">
                <h3 class="ga-chart__title">{{ __('guide_area.invoices.per_month') }}</h3>
                <p class="ga-chart__readout" aria-live="polite">
                    @foreach ($data['months'] as $m)
                        <span x-show="active === {{ $m['month'] }}" x-cloak>
                            {{ $monthNames[$m['month'] - 1] }} {{ $selectedYear }} · {{ $money($m['value']) }} · {{ trans_choice('guide_area.invoices.n_bookings', $m['bookings'], ['count' => $m['bookings']]) }} · {{ __('guide_area.invoices.commission_short', ['amount' => $money($m['commission'])]) }}
                        </span>
                    @endforeach
                    <span x-show="active === null">{{ __('guide_area.invoices.hover_hint') }}</span>
                </p>
            </div>
            <div class="ga-chart__plot" role="img" aria-label="{{ __('guide_area.invoices.per_month') }}">
                @foreach ($data['months'] as $m)
                    <button type="button" class="ga-chart__col" :class="{ 'is-active': active === {{ $m['month'] }} }"
                            @mouseenter="active = {{ $m['month'] }}" @mouseleave="active = null" @focus="active = {{ $m['month'] }}" @blur="active = null"
                            aria-label="{{ $monthNames[$m['month'] - 1] }}: {{ $money($m['value']) }}">
                        <span class="ga-chart__bar" style="height: {{ $m['value'] > 0 ? max(2, round($m['value'] / $max * 100)) : 0 }}%"></span>
                        <span class="ga-chart__month">{{ $monthNames[$m['month'] - 1] }}</span>
                    </button>
                @endforeach
            </div>
        </div>
    </section>

    {{-- Commission invoices (spec IN-05; today one emailed invoice per booking) --}}
    <section class="ga-card">
        <div class="ga-card__head">
            <div>
                <h2 class="ga-card__title">{{ __('guide_area.invoices.invoices_in', ['year' => $selectedYear]) }}</h2>
                <p class="ga-card__subtitle">{{ __('guide_area.invoices.invoices_note') }}</p>
            </div>
        </div>

        @if ($invoices->isEmpty())
            <p class="ga-empty ga-empty--inline">{{ __('guide_area.invoices.no_invoices') }}</p>
        @else
            <div class="ga-table-wrap">
                <table class="ga-table">
                    <thead>
                        <tr>
                            <th scope="col">{{ __('guide_area.invoices.th_invoice') }}</th>
                            <th scope="col">{{ __('guide_area.invoices.th_tour') }}</th>
                            <th scope="col">{{ __('guide_area.invoices.th_sent') }}</th>
                            <th scope="col" class="is-num">{{ __('guide_area.invoices.th_value') }}</th>
                            <th scope="col" class="is-num">{{ __('guide_area.invoices.th_commission') }}</th>
                            <th scope="col">{{ __('guide_area.invoices.th_status') }}</th>
                            <th scope="col"><span class="ga-visually-hidden">{{ __('guide_area.invoices.view') }}</span></th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($invoices as $invoice)
                            <tr @class(['is-open' => $invoice['status'] !== 'paid'])>
                                <td><code>{{ $invoice['number'] }}</code></td>
                                <td>
                                    <a href="{{ route('guide.area.bookings', ['booking' => $invoice['booking_id']]) }}">{{ $invoice['tour'] ?: '#'.$invoice['booking_id'] }}</a>
                                    @if ($invoice['tour_date'])<br><span class="ga-muted">{{ $invoice['tour_date'] }}</span>@endif
                                </td>
                                <td>{{ $invoice['sent'] }}</td>
                                <td class="is-num">{{ $invoice['value'] }}</td>
                                <td class="is-num"><strong>{{ $invoice['commission'] }}</strong></td>
                                <td>
                                    <span class="ga-status ga-status--{{ ['paid' => 'success', 'open' => 'warning', 'overdue' => 'danger'][$invoice['status']] }}">
                                        {{ __('guide_area.invoices.status.'.$invoice['status']) }}@if ($invoice['status'] !== 'paid' && $invoice['due']) · {{ __('guide_area.invoices.due', ['date' => $invoice['due']]) }}@endif
                                    </span>
                                </td>
                                <td class="is-num"><a href="{{ route('guide.area.invoices.show', $invoice['id']) }}" class="ga-btn ga-btn--outline ga-btn--sm" target="_blank" rel="noopener">{{ __('guide_area.invoices.view') }}</a></td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </section>
</div>
