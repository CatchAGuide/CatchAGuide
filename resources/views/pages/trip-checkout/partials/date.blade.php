{{--
    Trips with open departures: a dropdown of those dates. Year-round trips and trips whose
    departures have passed or are full: the guest's preferred travel window.
--}}
@php
    $icon = 'pages.modern-checkout.partials.icon';
    $departures = $checkout->departures();
    $selected = collect($departures)->firstWhere('date', $checkout->departureDate());
@endphp
<section class="cc-section" aria-labelledby="cc-date-title">
    <h2 class="cc-section__title" id="cc-date-title">{{ __('checkout.trip.date_title') }}</h2>

    @if ($checkout->usesFixedDates())
        <div class="cc-field" x-ref="field_date">
            <div class="cc-departures" @click.outside="datesOpen = false" @keydown.escape.prevent.stop="closeDates()">
                <button
                    type="button"
                    class="cc-departures__trigger"
                    x-ref="datesTrigger"
                    :class="{ 'is-open': datesOpen, 'is-invalid': errors.date }"
                    aria-haspopup="listbox"
                    :aria-expanded="datesOpen.toString()"
                    aria-labelledby="cc-date-title cc-date-value"
                    aria-describedby="cc-date-error"
                    @click="toggleDates()"
                    @keydown.arrow-down.prevent="openDates()"
                >
                    <span
                        id="cc-date-value"
                        @class(['cc-departures__value', 'cc-mono' => $selected, 'is-placeholder' => ! $selected])
                        :class="{ 'cc-mono': selected, 'is-placeholder': !selected }"
                        x-text="selected ? selected.label : i18n.chooseDate"
                    >{{ $selected['label'] ?? __('checkout.trip.choose_date') }}</span>
                    @include($icon, ['name' => 'chevron-down', 'size' => 20, 'stroke' => 2, 'class' => 'cc-departures__chevron'])
                </button>

                <div
                    class="cc-departures__list"
                    role="listbox"
                    aria-label="{{ __('checkout.trip.dates_label') }}"
                    x-ref="datesList"
                    x-show="datesOpen"
                    x-cloak
                    @keydown.arrow-down.prevent="moveDateFocus(1)"
                    @keydown.arrow-up.prevent="moveDateFocus(-1)"
                >
                    @foreach ($departures as $departure)
                        <button
                            type="button"
                            role="option"
                            class="cc-departures__option"
                            data-date="{{ $departure['date'] }}"
                            :class="{ 'is-selected': departureDate === $el.dataset.date }"
                            :aria-selected="(departureDate === $el.dataset.date).toString()"
                            @click="pickDate($el.dataset.date)"
                        >
                            <span class="cc-departures__radio" aria-hidden="true"></span>
                            <span class="cc-departures__label cc-mono">{{ $departure['label'] }}</span>
                            @if ($departure['spotsLabel'] !== '')
                                <span class="cc-departures__badge">{{ $departure['spotsLabel'] }}</span>
                            @endif
                        </button>
                    @endforeach
                </div>
            </div>
            <p class="cc-field__error" id="cc-date-error" x-show="errors.date" x-text="errors.date" role="alert" x-cloak></p>
        </div>
    @else
        <div class="cc-wish" x-ref="field_wish">
            <p class="cc-wish__intro">
                @include($icon, ['name' => 'calendar', 'size' => 16, 'stroke' => 2])
                <span>{{ __('checkout.trip.no_fixed_dates') }}</span>
            </p>

            <div class="cc-range">
                @foreach (['wishStart' => 'wish_start', 'wishEnd' => 'wish_end'] as $field => $label)
                    <div class="cc-field" @click="openPicker('{{ $field }}')">
                        <label class="cc-field__label" for="cc-{{ $field }}">{{ __('checkout.trip.'.$label) }}</label>
                        <div class="cc-date is-empty" :class="{ 'is-empty': !{{ $field }}, 'is-invalid': wishInvalid('{{ $field }}') }">
                            <input
                                id="cc-{{ $field }}"
                                class="cc-input cc-date__input"
                                x-ref="{{ $field }}"
                                type="date"
                                name="{{ $label }}"
                                min="{{ $checkout->minDate() }}"
                                max="{{ $checkout->maxDate() }}"
                                @if ($field === 'wishEnd') :min="wishStart || '{{ $checkout->minDate() }}'" @endif
                                x-model="{{ $field }}"
                                @change="clearError('wish')"
                                :aria-invalid="wishInvalid('{{ $field }}').toString()"
                                aria-describedby="cc-wish-error"
                            >
                            <span class="cc-date__hint" aria-hidden="true">{{ __('checkout.trip.date_placeholder') }}</span>
                            <span class="cc-date__glyph" aria-hidden="true">
                                @include($icon, ['name' => 'calendar', 'size' => 20, 'stroke' => 1.8])
                            </span>
                        </div>
                    </div>
                @endforeach
            </div>
            <p class="cc-field__error" id="cc-wish-error" x-show="errors.wish" x-text="errors.wish" role="alert" x-cloak></p>

            @if ($checkout->duration() !== '')
                <p class="cc-wish__duration">{{ __('checkout.trip.duration', ['duration' => $checkout->duration()]) }}</p>
            @endif
        </div>
    @endif
</section>
