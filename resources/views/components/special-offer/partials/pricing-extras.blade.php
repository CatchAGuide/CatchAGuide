<div class="special-offer-card__panel special-offer-card__panel--pricing-extras">
    <div class="special-offer-card__panel-title">{{ __('vacations.pricing_extras') }}</div>
    <div class="special-offer-card__pricing-extras-list">
        @foreach($pricingExtras as $extra)
            <div class="special-offer-card__pricing-extra-item">
                <span class="special-offer-card__pricing-extra-name">{{ translate($extra['name'] ?? '') }}</span>
                <span class="special-offer-card__pricing-extra-price">
                    {{ $currency === 'EUR' ? '€' : $currency }}{{ number_format((float)($extra['price'] ?? 0), 2, ',', '.') }}
                </span>
            </div>
        @endforeach
    </div>
</div>
