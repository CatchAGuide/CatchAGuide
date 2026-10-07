@use('App\Enums\TourExtraUnit')
<div class="tc-pricing">
    <div class="tc-pricing__row">
        <span class="tc-pricing__label" x-text="baseLine"></span>
        <span class="tc-mono" x-text="money(basePrice)"></span>
    </div>

    @if ($checkout->extras() !== [])
        <p class="tc-pricing__extras-title">{{ __('checkout.tour.add_extras') }}</p>
        <div class="tc-pricing__extras">
            @foreach ($checkout->extras() as $extra)
                <button
                    type="button"
                    class="tc-extra"
                    role="checkbox"
                    :class="{ 'is-selected': isExtraSelected({{ (int) $extra['index'] }}) }"
                    :aria-checked="isExtraSelected({{ (int) $extra['index'] }}).toString()"
                    @click="toggleExtra({{ (int) $extra['index'] }})"
                >
                    <span class="tc-extra__box">@include('pages.modern-checkout.partials.icon', ['name' => 'check', 'size' => 12, 'stroke' => 2.6])</span>
                    @if ($extra['unit'] === TourExtraUnit::PerPerson->value)
                        <span class="tc-extra__label">{{ $extra['name'] }} × <span x-text="persons"></span> <span x-text="unitLabel"></span></span>
                    @else
                        <span class="tc-extra__label">{{ $extra['name'] }}</span>
                    @endif
                    <span class="tc-extra__price tc-mono" x-text="extraTotal({{ (int) $extra['index'] }})"></span>
                </button>
            @endforeach
        </div>
    @endif

    <hr class="tc-divider">

    <div class="tc-pricing__total">
        <span class="tc-pricing__total-label">{{ __('checkout.tour.total') }}</span>
        <span class="tc-pricing__total-value tc-mono" x-text="money(total)"></span>
    </div>
    <p class="tc-muted">{{ __('checkout.tour.pay_nothing_today') }}</p>
</div>
