{{-- Accommodation + optional extras. Options are rendered by Alpine so their prices follow the party size. --}}
@php
    $icon = 'pages.modern-checkout.partials.icon';
    // key => [options list, label, "none" label, Alpine label formatter]
    $selects = array_filter([
        'boat' => $pricing->boats() !== [] ? ['boats', 'checkout.camp.boat', 'checkout.camp.no_boat', 'boatLabel'] : null,
        'tour' => $pricing->tours() !== [] ? ['tours', 'checkout.camp.tour', 'checkout.camp.no_tour', 'tourLabel'] : null,
        'special' => $pricing->specials() !== [] ? ['specials', 'checkout.camp.special', 'checkout.camp.no_special', 'specialLabel'] : null,
    ]);
@endphp

@if ($pricing->hasAccommodations() || $selects !== [])
<section class="cc-section" aria-labelledby="cc-extras-title">
    <h2 class="cc-section__title" id="cc-extras-title">{{ __('checkout.camp.extras_title') }}</h2>

    @if ($pricing->hasAccommodations())
        <div class="cc-field" x-ref="field_accommodation">
            <label class="cc-field__label" for="cc-accommodation">{{ __('checkout.camp.accommodation') }}</label>
            <div class="cc-select is-active" :class="{ 'is-invalid': errors.accommodation }">
                <select
                    id="cc-accommodation"
                    class="cc-select__input"
                    name="accommodation_id"
                    x-model="accommodationId"
                    @change="onAccommodationChange()"
                    aria-describedby="cc-accommodation-error"
                >
                    <template x-for="unit in options.accommodations" :key="unit.id">
                        <option :value="String(unit.id)" :selected="String(unit.id) === accommodationId" x-text="unitLabel(unit)"></option>
                    </template>
                </select>
                @include($icon, ['name' => 'chevron-down', 'size' => 18, 'stroke' => 2, 'class' => 'cc-select__chevron'])
            </div>
            <p class="cc-field__error" id="cc-accommodation-error" x-show="errors.accommodation" x-text="errors.accommodation" x-cloak></p>
            <div class="cc-warning" role="status" x-show="overCapacity" x-cloak>
                @include($icon, ['name' => 'info', 'size' => 18, 'stroke' => 2])
                <span x-text="overCapacityLabel"></span>
            </div>
        </div>
    @endif

    @foreach ($selects as $key => [$list, $label, $none, $formatter])
        <div class="cc-field" x-ref="field_{{ $key }}">
            <label class="cc-field__label" for="cc-{{ $key }}">{{ __($label) }}</label>
            <div class="cc-select" :class="{ 'is-active': {{ $key }}Id !== '', 'is-invalid': errors.{{ $key }} }">
                <select id="cc-{{ $key }}" class="cc-select__input" x-model="{{ $key }}Id" @change="clearError('{{ $key }}')">
                    <option value="">{{ __($none) }}</option>
                    <template x-for="option in options.{{ $list }}" :key="option.id">
                        <option :value="String(option.id)" :selected="String(option.id) === {{ $key }}Id" x-text="{{ $formatter }}(option)"></option>
                    </template>
                </select>
                @include($icon, ['name' => 'chevron-down', 'size' => 18, 'stroke' => 2, 'class' => 'cc-select__chevron'])
            </div>
            <p class="cc-field__error" x-show="errors.{{ $key }}" x-text="errors.{{ $key }}" x-cloak></p>
        </div>
    @endforeach
</section>
@endif
