{{-- Calendar keeps the established checkout calendar markup/classes (calendar-*), styled by
     the shared checkout-calendar mixins, including the animated selected-day state. --}}
<section class="tc-card tc-calendar calendar-container" x-ref="field_date" aria-labelledby="tc-calendar-title">
    <h2 class="tc-card__title" id="tc-calendar-title">
        @if (! empty($title))
            {{ $title }}
        @else
            <span class="tc-only-mobile">{{ __('checkout.tour.date_and_guests') }}</span>
            <span class="tc-only-desktop">{{ __('checkout.tour.date') }}</span>
        @endif
    </h2>
    @if (! empty($note))
        <p class="tc-calendar__note tc-muted">{{ $note }}</p>
    @endif

    <div class="calendar-header">
        <button type="button" class="calendar-nav" @click="prevMonth()" :disabled="!canGoPrev">← {{ __('checkout.calendar_prev') }}</button>
        <div class="calendar-month" x-text="monthLabel" aria-live="polite"></div>
        <button type="button" class="calendar-nav" @click="nextMonth()">{{ __('checkout.calendar_next') }} →</button>
    </div>

    <div class="calendar-weekdays" aria-hidden="true">
        @foreach (['sunday', 'monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday'] as $weekday)
            <div class="weekday">{{ __('checkout.calendar_'.$weekday) }}</div>
        @endforeach
    </div>

    <div class="calendar-days">
        <template x-for="blank in monthGrid.leadingBlanks" :key="'blank-' + blank">
            <span class="calendar-day-empty" aria-hidden="true"></span>
        </template>
        <template x-for="day in monthGrid.days" :key="day.iso">
            <button
                type="button"
                class="calendar-day"
                :class="{ selected: isSelected(day.iso), past: day.past, blocked: day.blocked }"
                :disabled="!day.available"
                :aria-pressed="isSelected(day.iso).toString()"
                :aria-label="day.iso"
                @click="selectDate(day.iso)"
                x-text="day.day"
            ></button>
        </template>
    </div>

    @if ($showSelected ?? true)
        <p class="tc-calendar__selected" x-show="!errors.date">
            {{ __('checkout.tour.selected') }} <span x-text="selectedLabel"></span>
        </p>
    @endif
    @if (! empty($afterPartial))
        @include($afterPartial)
    @endif
    <p class="tc-field__error" x-show="errors.date" x-text="errors.date" role="alert" x-cloak></p>
</section>
