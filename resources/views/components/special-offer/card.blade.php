<div class="special-offer-card" data-special-offer-card>
    @php
        $galleryImages = $specialOffer['gallery_images'] ?? [];
        $galleryTotal = $specialOffer['gallery_count'] ?? max(count($galleryImages), 1);
        $whatsIncluded = $specialOffer['whats_included'] ?? [];
        $accommodationNames = $specialOffer['accommodation_names'] ?? [];
        $boatNames = $specialOffer['boat_names'] ?? [];
        $guidingNames = $specialOffer['guiding_names'] ?? [];
        $price = $specialOffer['price'] ?? [];
        $priceAmount = (float) ($price['amount'] ?? 0);
        $currency = $price['currency'] ?? 'EUR';
        $priceType = $price['type'] ?? 'per_person';
        $location = $specialOffer['location'] ?? '';
        $city = $specialOffer['city'] ?? '';
        $region = $specialOffer['region'] ?? '';
        $country = $specialOffer['country'] ?? '';
        
        // Build details array
        $offerDetails = [];
        if (!empty($accommodationNames)) {
            $offerDetails[] = [
                'name' => __('Accommodation'),
                'value' => implode(', ', array_map('translate', $accommodationNames))
            ];
        }
        if (!empty($boatNames)) {
            $offerDetails[] = [
                'name' => __('Boat'),
                'value' => implode(', ', array_map('translate', $boatNames))
            ];
        }
        if (!empty($guidingNames)) {
            $offerDetails[] = [
                'name' => __('Guiding'),
                'value' => implode(', ', array_map('translate', $guidingNames))
            ];
        }
        
        // Price type translation
        $translatedPriceType = match($priceType) {
            'per_person' => __('Preis p.P.:'),
            'per_night' => __('Per Night'),
            'per_week' => __('Per Week'),
            default => ucfirst(str_replace('_', ' ', $priceType))
        };
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
        </div>

        <div class="special-offer-card__summary">
            <div class="special-offer-card__summary-header">
                <h3 class="special-offer-card__title">{{ translate($specialOffer['title']) ?? 'Special Offer' }}</h3>
            </div>

            @if(!empty($offerDetails))
                <div class="special-offer-card__details">
                    <ul class="special-offer-card__bullet-list">
                        @foreach($offerDetails as $detail)
                            <li>{{ translate($detail['name']) }}: <span class="font-medium">{{ translate($detail['value']) }}</span></li>
                        @endforeach
                    </ul>
                </div>
            @endif
        </div>

        <div class="special-offer-card__feature-grid" data-expanded-only>
            @if(count($whatsIncluded) > 0)
                <div class="special-offer-card__panel special-offer-card__panel--extras">
                    <div class="special-offer-card__panel-title">Inclusives</div>
                    <div class="special-offer-card__inclusive-extras">
                        @foreach($whatsIncluded as $item)
                            <span class="special-offer-card__inclusive-chip">✅ {{ translate($item) }}</span>
                        @endforeach
                    </div>
                </div>
            @endif
        </div>

        <div class="special-offer-card__actions">
            <div class="special-offer-card__actions-column">
                <div class="special-offer-card__pricing">
                    <div class="special-offer-card__price-type">{{ $translatedPriceType }}</div>
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




