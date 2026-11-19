<div class="ga-page">
    <header class="ga-page__header">
        <div>
            <h1 class="ga-page__title">{{ __('guide_area.calendar.title') }}</h1>
            <p class="ga-page__subtitle">{{ __('guide_area.calendar.subtitle') }}</p>
        </div>
        <button type="button" class="ga-btn ga-btn--dark" wire:click="openBlockForm">
            <i class="fas fa-ban" aria-hidden="true"></i> {{ __('guide_area.calendar.block_days') }}
        </button>
    </header>

    <div class="ga-cal-layout">
        <section class="ga-card ga-cal" aria-label="{{ $grid['label'] }}">
            <div class="ga-cal__nav">
                <button type="button" class="ga-btn ga-btn--outline ga-btn--sm" wire:click="previousMonth" aria-label="{{ __('guide_area.calendar.previous_month') }}"><i class="fas fa-chevron-left" aria-hidden="true"></i></button>
                <h2 class="ga-cal__month">{{ $grid['label'] }}</h2>
                <button type="button" class="ga-btn ga-btn--outline ga-btn--sm" wire:click="nextMonth" aria-label="{{ __('guide_area.calendar.next_month') }}"><i class="fas fa-chevron-right" aria-hidden="true"></i></button>
            </div>

            <div class="ga-cal__grid" role="grid">
                @foreach ($weekdays as $weekday)
                    <div class="ga-cal__weekday" role="columnheader">{{ $weekday }}</div>
                @endforeach
                @foreach ($grid['weeks'] as $week)
                    @foreach ($week as $cell)
                        @if ($cell === null)
                            <div class="ga-cal__cell ga-cal__cell--empty" aria-hidden="true"></div>
                        @else
                            @php
                                $booking = $cell['bookings'][0] ?? null;
                                $block = $cell['blocks'][0] ?? null;
                                $label = match ($cell['state']) {
                                    'booked' => __('guide_area.calendar.cell.booked', ['tour' => $booking['tour'] ?? '']),
                                    'pending' => __('guide_area.calendar.cell.request', ['tour' => $booking['tour'] ?? '']),
                                    'blocked' => $block && $block['source'] === 'ical' ? __('guide_area.calendar.cell.private') : ($block['note'] ?? __('guide_area.calendar.cell.blocked')),
                                    'closed' => __('guide_area.calendar.cell.closed'),
                                    'partial' => __('guide_area.calendar.cell.partial', ['count' => $cell['bookable_count'], 'total' => $cell['tour_count']]),
                                    default => null,
                                };
                            @endphp
                            <button type="button" wire:key="cal-{{ $cell['date'] }}" wire:click="select('{{ $cell['date'] }}')"
                                    @class(['ga-cal__cell', 'ga-cal__cell--'.$cell['state'], 'is-selected' => $cell['date'] === $day['date'], 'ga-cal__cell--today' => $cell['is_today']])
                                    aria-pressed="{{ $cell['date'] === $day['date'] ? 'true' : 'false' }}"
                                    aria-label="{{ $cell['date'] }} — {{ __('guide_area.calendar.states.'.$cell['state']) }}">
                                <span class="ga-cal__day">{{ $cell['day'] }}</span>
                                @if ($label)
                                    <span class="ga-cal__label">{{ $label }}</span>
                                @endif
                            </button>
                        @endif
                    @endforeach
                @endforeach
            </div>

            <ul class="ga-legend">
                @foreach (['available', 'partial', 'pending', 'booked', 'blocked'] as $state)
                    <li><span class="ga-legend__swatch ga-cal__cell--{{ $state }}" aria-hidden="true"></span>{{ __('guide_area.calendar.legend.'.$state) }}</li>
                @endforeach
            </ul>
        </section>

        <aside class="ga-cal-side">
            @if ($showBlockForm)
                <form class="ga-card ga-block-form" wire:submit="blockDays">
                    <h2 class="ga-card__title">{{ __('guide_area.calendar.block_days') }}</h2>
                    <div class="ga-segmented" role="radiogroup">
                        <label @class(['is-active' => $blockMode === 'single'])><input type="radio" value="single" wire:model.live="blockMode"> {{ __('guide_area.calendar.block.single') }}</label>
                        <label @class(['is-active' => $blockMode === 'period'])><input type="radio" value="period" wire:model.live="blockMode"> {{ __('guide_area.calendar.block.period') }}</label>
                    </div>
                    <div @class(['ga-grid-2' => $blockMode === 'period'])>
                        <label class="ga-field">
                            <span class="ga-label">{{ $blockMode === 'period' ? __('guide_area.calendar.block.from') : __('guide_area.calendar.block.date') }}</span>
                            <input type="date" class="ga-input" min="{{ $today }}" wire:model="blockFrom">
                            @error('blockFrom') <span class="ga-error">{{ $message }}</span> @enderror
                        </label>
                        @if ($blockMode === 'period')
                            <label class="ga-field">
                                <span class="ga-label">{{ __('guide_area.calendar.block.to') }}</span>
                                <input type="date" class="ga-input" min="{{ $today }}" wire:model="blockTo">
                                @error('blockTo') <span class="ga-error">{{ $message }}</span> @enderror
                            </label>
                        @endif
                    </div>
                    <label class="ga-field">
                        <span class="ga-label">{{ __('guide_area.calendar.block.applies_to') }}</span>
                        <select class="ga-input" wire:model="blockTour">
                            <option value="0">{{ __('guide_area.calendar.block.all_tours') }}</option>
                            @foreach ($tours as $tour)
                                <option value="{{ $tour->id }}">{{ $tour->title }}</option>
                            @endforeach
                        </select>
                    </label>
                    <label class="ga-field">
                        <span class="ga-label">{{ __('guide_area.calendar.block.note') }}</span>
                        <input type="text" class="ga-input" maxlength="255" wire:model="blockNote" placeholder="{{ __('guide_area.calendar.block.note_placeholder') }}">
                    </label>
                    <div class="ga-inline-form__actions">
                        <button type="submit" class="ga-btn ga-btn--dark" wire:loading.attr="disabled">{{ $blockMode === 'period' ? __('guide_area.calendar.block.submit_period') : __('guide_area.calendar.block.submit_single') }}</button>
                        <button type="button" class="ga-btn ga-btn--outline" wire:click="$set('showBlockForm', false)">{{ __('guide_area.common.cancel') }}</button>
                    </div>
                </form>
            @endif

            <section class="ga-card ga-day-panel" aria-live="polite">
                <p class="ga-eyebrow">{{ __('guide_area.calendar.selected_day') }}</p>
                <h2 class="ga-day-panel__date">{{ $selectedDate->translatedFormat('l, j F Y') }}</h2>
                <span class="ga-status ga-status--{{ ['available' => 'success', 'partial' => 'success', 'pending' => 'warning', 'booked' => 'neutral', 'blocked' => 'danger', 'closed' => 'danger', 'past' => 'neutral'][$day['state']] }}">
                    @if ($day['state'] === 'partial')
                        {{ __('guide_area.calendar.cell.partial', ['count' => $day['bookable_count'], 'total' => $day['tour_count']]) }}
                    @else
                        {{ __('guide_area.calendar.states.'.$day['state']) }}
                    @endif
                </span>

                @foreach ($day['bookings'] as $item)
                    <a class="ga-day-panel__item" href="{{ route('guide.area.bookings', ['booking' => $item['id']]) }}">
                        <i class="fas {{ $item['status'] === 'booked' ? 'fa-check-circle' : 'fa-hourglass-half' }}" aria-hidden="true"></i>
                        {{ $item['status'] === 'booked' ? __('guide_area.calendar.panel.booked', ['tour' => $item['tour'], 'guest' => $item['guest']]) : __('guide_area.calendar.panel.request', ['tour' => $item['tour']]) }}
                    </a>
                @endforeach
                @foreach ($day['blocks'] as $block)
                    <p class="ga-day-panel__item ga-day-panel__item--muted">
                        <i class="fas {{ $block['source'] === 'ical' ? 'fa-calendar-alt' : 'fa-ban' }}" aria-hidden="true"></i>
                        {{ $block['source'] === 'ical' ? __('guide_area.calendar.panel.private') : __('guide_area.calendar.panel.blocked_by_you') }}@if ($block['note']) · {{ $block['note'] }}@endif
                    </p>
                @endforeach

                @if ($day['state'] !== 'past')
                    @php $manualBlocks = collect($day['blocks'])->where('source', 'manual'); @endphp
                    @if (in_array($day['state'], ['booked', 'pending'], true))
                        <p class="ga-help">{{ __('guide_area.calendar.panel.day_taken') }}</p>
                    @elseif ($manualBlocks->isNotEmpty())
                        <button type="button" class="ga-btn ga-btn--outline ga-btn--block" wire:click="unblockSelectedDay">{{ __('guide_area.calendar.unblock_day') }}</button>
                    @elseif ($day['state'] === 'blocked')
                        <p class="ga-help">{{ __('guide_area.calendar.panel.private_hint') }}</p>
                    @else
                        <button type="button" class="ga-btn ga-btn--danger-outline ga-btn--block" wire:click="blockSelectedDay">{{ __('guide_area.calendar.block_day') }}</button>
                    @endif

                    @if ($day['tours'] !== [])
                        <p class="ga-day-panel__subhead">{{ __('guide_area.calendar.panel.tours') }}</p>
                        <ul class="ga-toggle-list">
                            @foreach ($day['tours'] as $tour)
                                @php $locked = in_array($day['state'], ['booked', 'pending', 'blocked'], true) || in_array($tour['reason'], ['rules', 'declined'], true); @endphp
                                <li class="ga-toggle-list__item" wire:key="day-tour-{{ $tour['id'] }}">
                                    <span class="ga-day-panel__tour">
                                        {{ $tour['title'] }}
                                        @if (in_array($tour['reason'], ['rules', 'declined'], true) && ! in_array($day['state'], ['booked', 'pending', 'blocked'], true))
                                            <small class="ga-muted">{{ __('guide_area.calendar.panel.closed_by_'.$tour['reason']) }}</small>
                                        @endif
                                    </span>
                                    <span @class(['ga-day-panel__state', 'is-off' => ! $tour['bookable']])>{{ $tour['bookable'] ? __('guide_area.calendar.panel.bookable') : __('guide_area.calendar.panel.not_bookable') }}</span>
                                    <label class="ga-switch">
                                        <input type="checkbox" class="ga-switch__input" role="switch" @checked($tour['bookable']) @disabled($locked)
                                               wire:click="setTour({{ $tour['id'] }}, {{ $tour['bookable'] ? 'false' : 'true' }})"
                                               aria-label="{{ $tour['title'] }}">
                                        <span class="ga-switch__track" aria-hidden="true"></span>
                                    </label>
                                </li>
                            @endforeach
                        </ul>
                        <p class="ga-help">{{ __('guide_area.calendar.panel.rules_hint') }}</p>
                    @endif
                @endif
            </section>
        </aside>
    </div>

    {{-- Calendar sync (spec CA-08 / CA-09) --}}
    <div class="ga-section-head" id="calendar-sync">
        <div>
            <h2 class="ga-section-head__title">{{ __('guide_area.calendar.sync.title') }}</h2>
            <p class="ga-page__subtitle">{{ __('guide_area.calendar.sync.subtitle') }}</p>
        </div>
    </div>
    <div class="ga-grid-2 ga-sync">
        <section class="ga-card">
            <p class="ga-eyebrow ga-eyebrow--info">{{ __('guide_area.calendar.sync.import') }}</p>
            <h3 class="ga-card__title">{{ __('guide_area.calendar.sync.import_title') }}</h3>
            <p class="ga-card__subtitle">{{ __('guide_area.calendar.sync.import_text') }}</p>

            @forelse ($imports as $feed)
                <div class="ga-connection" wire:key="feed-{{ $feed->id }}">
                    <span class="ga-action-row__icon" aria-hidden="true"><i class="far fa-calendar-alt"></i></span>
                    <div class="ga-connection__body">
                        <p class="ga-connection__name">{{ $feed->name }}</p>
                        <p class="ga-connection__meta">
                            {{ $feed->last_successful_sync_at ? __('guide_area.calendar.sync.last_sync', ['time' => $feed->last_successful_sync_at->diffForHumans()]) : __('guide_area.calendar.sync.never_synced') }}
                        </p>
                    </div>
                    @if ($feed->last_error)
                        <span class="ga-status ga-status--danger" title="{{ $feed->last_error }}">{{ __('guide_area.calendar.sync.error') }}</span>
                    @else
                        <span class="ga-status ga-status--success">{{ __('guide_area.calendar.sync.connected') }}</span>
                    @endif
                </div>
                <div class="ga-inline-form__actions ga-connection__actions">
                    <button type="button" class="ga-btn ga-btn--outline ga-btn--sm" wire:click="syncNow({{ $feed->id }})" wire:loading.attr="disabled"><i class="fas fa-sync-alt" aria-hidden="true"></i> {{ __('guide_area.calendar.sync.sync_now') }}</button>
                    <button type="button" class="ga-btn ga-btn--ghost ga-btn--sm ga-text-danger" wire:click="disconnect({{ $feed->id }})" wire:confirm="{{ __('guide_area.calendar.sync.disconnect_confirm') }}">{{ __('guide_area.calendar.sync.disconnect') }}</button>
                </div>
            @empty
                <p class="ga-help">{{ __('guide_area.calendar.sync.none') }}</p>
            @endforelse

            @if ($showImportForm)
                <form class="ga-inline-form" wire:submit="addImport">
                    <label class="ga-field">
                        <span class="ga-label">{{ __('guide_area.calendar.sync.url') }}</span>
                        <input type="url" class="ga-input" wire:model="importUrl" placeholder="https://calendar.google.com/calendar/ical/…/basic.ics">
                        <span class="ga-help">{{ __('guide_area.calendar.sync.url_help') }}</span>
                        @error('importUrl') <span class="ga-error">{{ $message }}</span> @enderror
                    </label>
                    <label class="ga-field">
                        <span class="ga-label">{{ __('guide_area.calendar.sync.name') }} <span class="ga-optional">{{ __('guide_area.common.optional') }}</span></span>
                        <input type="text" class="ga-input" maxlength="100" wire:model="importName" placeholder="{{ __('guide_area.calendar.sync.name_placeholder') }}">
                    </label>
                    <div class="ga-inline-form__actions">
                        <button type="submit" class="ga-btn ga-btn--dark" wire:loading.attr="disabled">{{ __('guide_area.calendar.sync.connect') }}</button>
                        <button type="button" class="ga-btn ga-btn--outline" wire:click="$set('showImportForm', false)">{{ __('guide_area.common.cancel') }}</button>
                    </div>
                </form>
            @else
                <button type="button" class="ga-btn ga-btn--outline ga-btn--sm" wire:click="$set('showImportForm', true)"><i class="fas fa-plus" aria-hidden="true"></i> {{ __('guide_area.calendar.sync.add') }}</button>
            @endif
            <p class="ga-help">{{ __('guide_area.calendar.sync.works_with') }}</p>
        </section>

        <section class="ga-card" x-data="{ copied: false }">
            <p class="ga-eyebrow ga-eyebrow--info">{{ __('guide_area.calendar.sync.export') }}</p>
            <h3 class="ga-card__title">{{ __('guide_area.calendar.sync.export_title') }}</h3>
            <p class="ga-card__subtitle">{{ __('guide_area.calendar.sync.export_text') }}</p>
            <label class="ga-label" for="ga-export-url">{{ __('guide_area.calendar.sync.your_link') }}</label>
            <div class="ga-copy">
                <input id="ga-export-url" type="text" class="ga-input ga-input--mono" readonly value="{{ $export->getFeedUrl() }}" x-ref="url" @focus="$el.select()">
                <button type="button" class="ga-btn ga-btn--dark" @click="navigator.clipboard.writeText($refs.url.value); copied = true; setTimeout(() => copied = false, 2000)">
                    <span x-show="!copied">{{ __('guide_area.calendar.sync.copy') }}</span><span x-show="copied" x-cloak>{{ __('guide_area.calendar.sync.copied') }}</span>
                </button>
            </div>
            <p class="ga-label">{{ __('guide_area.calendar.sync.include') }}</p>
            <ul class="ga-checklist">
                <li><label class="ga-checkline"><input type="checkbox" checked disabled> {{ __('guide_area.calendar.sync.include_confirmed') }}</label></li>
                <li><label class="ga-checkline"><input type="checkbox" @checked($exportSettings['include_pending']) wire:click="setExportOption('include_pending', {{ $exportSettings['include_pending'] ? 'false' : 'true' }})"> {{ __('guide_area.calendar.sync.include_pending') }}</label></li>
                <li><label class="ga-checkline"><input type="checkbox" @checked($exportSettings['include_blocks']) wire:click="setExportOption('include_blocks', {{ $exportSettings['include_blocks'] ? 'false' : 'true' }})"> {{ __('guide_area.calendar.sync.include_blocks') }}</label></li>
            </ul>
            <button type="button" class="ga-btn ga-btn--ghost ga-btn--sm" wire:click="regenerateExport" wire:confirm="{{ __('guide_area.calendar.sync.regenerate_confirm') }}">
                <i class="fas fa-redo" aria-hidden="true"></i> {{ __('guide_area.calendar.sync.regenerate') }}
            </button>
        </section>
    </div>
</div>
