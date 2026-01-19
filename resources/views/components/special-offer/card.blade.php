<div class="special-offer-card" data-special-offer-card>
    @php
        $galleryImages = $specialOffer['gallery_images'] ?? [];
        $galleryTotal = $specialOffer['gallery_count'] ?? max(count($galleryImages), 1);
        $whatsIncluded = $specialOffer['whats_included'] ?? [];
        $pricingExtras = $specialOffer['pricing_extras'] ?? [];
        $accommodations = $specialOffer['accommodations'] ?? [];
        $rentalBoats = $specialOffer['rental_boats'] ?? [];
        $guidings = $specialOffer['guidings'] ?? [];
        $price = $specialOffer['price'] ?? [];
        $priceAmount = (float) ($price['amount'] ?? 0);
        $currency = $price['currency'] ?? 'EUR';
    @endphp

    <div class="special-offer-card__grid">
        <div class="special-offer-card__media">
            <div class="special-offer-gallery" data-gallery-images='@json($galleryImages)'>
                <img src="{{ $specialOffer['thumbnail_path'] ?? 'https://images.unsplash.com/photo-1474843148229-3163319fcc00?q=80&w=1600&auto=format&fit=crop' }}" alt="{{ $specialOffer['title'] ?? 'Special Offer' }}" data-gallery-image data-open-modal style="cursor: pointer;" />

                <div>
                    <button
                        type="button"
                        aria-label="Previous image"
                        class="special-offer-gallery__nav-btn special-offer-gallery__nav-btn--prev"
                        data-prev-image
                    >
                        ‹
                    </button>
                    <button
                        type="button"
                        aria-label="Next image"
                        class="special-offer-gallery__nav-btn special-offer-gallery__nav-btn--next"
                        data-next-image
                    >
                        ›
                    </button>
                    <div class="special-offer-gallery__counter" data-image-counter>
                        1/{{ $galleryTotal }}
                    </div>
                </div>
            </div>

            @if(count($whatsIncluded) > 0 || count($pricingExtras) > 0)
                <div class="special-offer-card__media-extras" data-expanded-only>
                    @if(count($whatsIncluded) > 0)
                        <div class="special-offer-card__panel special-offer-card__panel--inclusives">
                            <div class="special-offer-card__panel-title">INCLUSIVES</div>
                            <div class="special-offer-card__inclusive-extras">
                                @foreach($whatsIncluded as $item)
                                    <span class="special-offer-card__inclusive-chip">✔ {{ translate($item) }}</span>
                                @endforeach
                            </div>
                        </div>
                    @endif

                    @if(count($pricingExtras) > 0)
                        <div class="special-offer-card__panel special-offer-card__panel--pricing-extras">
                            <div class="special-offer-card__panel-title">PRICING EXTRAS</div>
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
                    @endif
                </div>
            @endif
        </div>

        <div class="special-offer-card__summary">
            <div class="special-offer-card__summary-header">
                <h3 class="special-offer-card__title">{{ translate($specialOffer['title']) ?? 'Special Offer' }}</h3>
            </div>

            <div class="special-offer-card__anchor-points">
                @if(count($accommodations) > 0)
                    <div class="special-offer-card__anchor-category" data-category-type="accommodation">
                        <span class="special-offer-card__anchor-category-label">{{ __('Accommodation') }}</span>
                        <div class="special-offer-card__anchor-buttons">
                            @foreach($accommodations as $index => $accommodation)
                                <a href="#accommodation-{{ $accommodation['id'] }}" 
                                   class="special-offer-card__anchor-box special-offer-card__anchor-box--accommodation {{ $index >= 3 ? 'special-offer-card__anchor-box--hidden' : '' }}" 
                                   data-anchor-type="accommodation"
                                   data-anchor-id="{{ $accommodation['id'] }}"
                                   data-anchor-scroll>
                                    <span class="special-offer-card__anchor-box-text">{{ $accommodation['title'] ?? '{Title}' }}</span>
                                </a>
                            @endforeach
                            @if(count($accommodations) > 3)
                                <button type="button" class="special-offer-card__anchor-toggle" data-toggle-category="accommodation" aria-label="Show more">
                                    <span class="special-offer-card__anchor-toggle-text">...</span>
                                </button>
                            @endif
                        </div>
                    </div>
                @endif

                @if(count($rentalBoats) > 0)
                    <div class="special-offer-card__anchor-category" data-category-type="boat">
                        <span class="special-offer-card__anchor-category-label">{{ __('Rental Boat') }}</span>
                        <div class="special-offer-card__anchor-buttons">
                            @foreach($rentalBoats as $index => $boat)
                                <a href="#rental-boat-{{ $boat['id'] }}" 
                                   class="special-offer-card__anchor-box special-offer-card__anchor-box--boat {{ $index >= 3 ? 'special-offer-card__anchor-box--hidden' : '' }}" 
                                   data-anchor-type="boat"
                                   data-anchor-id="{{ $boat['id'] }}"
                                   data-anchor-scroll>
                                    <span class="special-offer-card__anchor-box-text">{{ $boat['title'] ?? '{Title}' }}</span>
                                </a>
                            @endforeach
                            @if(count($rentalBoats) > 3)
                                <button type="button" class="special-offer-card__anchor-toggle" data-toggle-category="boat" aria-label="Show more">
                                    <span class="special-offer-card__anchor-toggle-text">...</span>
                                </button>
                            @endif
                        </div>
                    </div>
                @endif

                @if(count($guidings) > 0)
                    <div class="special-offer-card__anchor-category" data-category-type="guiding">
                        <span class="special-offer-card__anchor-category-label">Guidings</span>
                        <div class="special-offer-card__anchor-buttons">
                            @foreach($guidings as $index => $guiding)
                                <a href="#guiding-{{ $guiding['id'] }}" 
                                   class="special-offer-card__anchor-box special-offer-card__anchor-box--guiding {{ $index >= 3 ? 'special-offer-card__anchor-box--hidden' : '' }}" 
                                   data-anchor-type="guiding"
                                   data-anchor-id="{{ $guiding['id'] }}"
                                   data-anchor-scroll>
                                    <span class="special-offer-card__anchor-box-text">{{ $guiding['title'] ?? '{Title}' }}</span>
                                </a>
                            @endforeach
                            @if(count($guidings) > 3)
                                <button type="button" class="special-offer-card__anchor-toggle" data-toggle-category="guiding" aria-label="Show more">
                                    <span class="special-offer-card__anchor-toggle-text">...</span>
                                </button>
                            @endif
                        </div>
                    </div>
                @endif
            </div>
        </div>

        @php
            $accommodationsFull = $specialOffer['accommodations_full'] ?? [];
            $rentalBoatsFull = $specialOffer['rental_boats_full'] ?? [];
            $guidingsFull = $specialOffer['guidings_full'] ?? [];
            $allComponents = array_merge($accommodationsFull, $rentalBoatsFull, $guidingsFull);
        @endphp

        @if(count($allComponents) > 0)
            <div class="special-offer-card__component-cards" data-expanded-only>
                @foreach($accommodationsFull as $acc)
                    <div class="special-offer-card__component-card special-offer-card__component-card--accommodation" id="accommodation-{{ $acc['id'] }}">
                        <h4 class="special-offer-card__component-title">{{ $acc['title'] ?? '' }}</h4>
                        <div class="special-offer-card__component-subtitle">{{ translate($acc['accommodation_type'] ?? '') }}</div>
                        
                        @if(!empty($acc['living_area_sqm']))
                            <div class="special-offer-card__component-badge">
                                <span class="special-offer-card__component-badge-icon">📐</span>
                                <span>{{ $acc['living_area_sqm'] }} qm</span>
                            </div>
                        @endif

                        @if(!empty($acc['bed_summary']))
                            <div class="special-offer-card__component-text">
                                Schlafzimmer: {{ translate($acc['bed_summary']) }}
                            </div>
                        @endif

                        <div class="special-offer-card__component-badges">
                            @if(!empty($acc['distances']['to_water_m']))
                                <div class="special-offer-card__component-badge">
                                    <span class="special-offer-card__component-badge-icon">🌊</span>
                                    <span>Water: {{ is_numeric($acc['distances']['to_water_m']) ? $acc['distances']['to_water_m'] . 'm' : translate($acc['distances']['to_water_m']) }}</span>
                                </div>
                            @endif
                            @if(!empty($acc['distances']['to_parking_m']))
                                <div class="special-offer-card__component-badge">
                                    <span class="special-offer-card__component-badge-icon">🚗</span>
                                    <span>Parking: {{ is_numeric($acc['distances']['to_parking_m']) ? $acc['distances']['to_parking_m'] . 'm' : translate($acc['distances']['to_parking_m']) }}</span>
                                </div>
                            @endif
                        </div>
                    </div>
                @endforeach

                @foreach($rentalBoatsFull as $boat)
                    <div class="special-offer-card__component-card special-offer-card__component-card--boat" id="rental-boat-{{ $boat['id'] }}">
                        <h4 class="special-offer-card__component-title">{{ $boat['title'] ?? '' }}</h4>
                        <div class="special-offer-card__component-subtitle">{{ translate($boat['type'] ?? '') }}</div>
                        
                        @if(!empty($boat['specs']))
                            @foreach($boat['specs'] as $spec)
                                <div class="special-offer-card__component-badge">
                                    <span>{{ translate($spec['label']) }}: {{ translate($spec['value']) }}</span>
                                </div>
                            @endforeach
                        @endif
                    </div>
                @endforeach

                @foreach($guidingsFull as $guiding)
                    <div class="special-offer-card__component-card special-offer-card__component-card--guiding" id="guiding-{{ $guiding['id'] }}">
                        <h4 class="special-offer-card__component-title">{{ $guiding['title'] ?? '' }}</h4>
                        @if(!empty($guiding['guiding_info']['art']))
                            <div class="special-offer-card__component-subtitle">{{ translate($guiding['guiding_info']['art'] ?? '') }}</div>
                        @endif
                        
                        @if(!empty($guiding['guiding_info']))
                            <div class="special-offer-card__component-badges">
                                @if(!empty($guiding['guiding_info']['dauer']))
                                    <div class="special-offer-card__component-badge">
                                        <span>Duration: {{ translate($guiding['guiding_info']['dauer']) }}</span>
                                    </div>
                                @endif
                                @if(!empty($guiding['guiding_info']['max_personen']))
                                    <div class="special-offer-card__component-badge">
                                        <span>Max Persons: {{ translate($guiding['guiding_info']['max_personen']) }}</span>
                                    </div>
                                @endif
                            </div>
                        @endif
                    </div>
                @endforeach
            </div>
        @endif


        <div class="special-offer-card__actions">
            <div class="special-offer-card__actions-column">
                <div class="special-offer-card__price-label">{{ 'per person' }}</div>
                <div class="special-offer-card__pricing">
                    <div class="special-offer-card__price-amount">{{ $currency === 'EUR' ? '€' : $currency }}{{ number_format($priceAmount, 2, ',', '.') }}</div>
                </div>
                <button class="special-offer-card__expand-btn special-offer-card__expand-btn--secondary" data-toggle-btn>
                    <span data-toggle-text>{{ __('Show More') }}</span>
                    <span data-toggle-icon>▼</span>
                </button>
            </div>
        </div>
    </div>

    <!-- Special Offer Gallery Modal -->
    <div class="special-offer-gallery-modal" data-special-offer-modal>
        <div class="special-offer-gallery-modal__content">
            <button class="special-offer-gallery-modal__close">&times;</button>
            <button class="special-offer-gallery-modal__prev">&#10094;</button>
            <button class="special-offer-gallery-modal__next">&#10095;</button>
            <img class="special-offer-gallery-modal__image" src="" alt="{{ $specialOffer['title'] ?? 'Special Offer' }}">
            <div class="special-offer-gallery-modal__counter">
                <span class="special-offer-gallery-modal__current">1</span> / <span class="special-offer-gallery-modal__total">{{ $galleryTotal }}</span>
            </div>
        </div>
    </div>
</div>

@once
<script src="{{ asset('js/special-offer-card.js') }}"></script>
@endonce




