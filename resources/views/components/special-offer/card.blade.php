<div class="special-offer-card" data-special-offer-card>
    @php
        use App\Presenters\Vacation\CampAttachmentChipPresenter;

        $offerThumbnail = $specialOffer['thumbnail_path']
            ?? 'https://images.unsplash.com/photo-1474843148229-3163319fcc00?q=80&w=1600&auto=format&fit=crop';
        $galleryImages = array_values(array_unique(array_filter(
            array_merge([$offerThumbnail], $specialOffer['gallery_images'] ?? [])
        )));
        $galleryTotal = count($galleryImages);
        $specialOfferGalleryId = 'special-offer-card-'.($specialOffer['id'] ?? uniqid());
        $whatsIncluded = $specialOffer['whats_included'] ?? [];
        $pricingExtras = $specialOffer['pricing_extras'] ?? [];
        $price = $specialOffer['price'] ?? [];
        $priceAmount = (float) ($price['amount'] ?? 0);
        $currency = $price['currency'] ?? 'EUR';
        $specialOfferModalTitle = translate($specialOffer['title'] ?? null) ?: __('vacations.special_offer_singular');
        $specialOfferPriceDisplay = ($currency === 'EUR' ? '€' : $currency).number_format($priceAmount, 2, ',', '.');
        $chipPresenter = CampAttachmentChipPresenter::class;
    @endphp

    <div class="special-offer-card__grid">
        <div class="special-offer-card__media">
            <div class="special-offer-gallery" data-vacation-gallery="{{ $specialOfferGalleryId }}" data-gallery-images='@json($galleryImages)'>
                <img src="{{ $offerThumbnail }}" alt="{{ translate($specialOffer['title'] ?? '') ?: __('vacations.special_offer_singular') }}" loading="lazy" decoding="async" data-vacation-gallery-image data-vacation-open-modal style="cursor: pointer;" />

                @if($galleryTotal > 1)
                <div>
                    <button
                        type="button"
                        aria-label="{{ __('vacations.gallery_prev') }}"
                        class="special-offer-gallery__nav-btn special-offer-gallery__nav-btn--prev"
                        data-prev-image
                    >
                        ‹
                    </button>
                    <button
                        type="button"
                        aria-label="{{ __('vacations.gallery_next') }}"
                        class="special-offer-gallery__nav-btn special-offer-gallery__nav-btn--next"
                        data-next-image
                    >
                        ›
                    </button>
                    <div class="special-offer-gallery__counter" data-image-counter>
                        1/{{ $galleryTotal }}
                    </div>
                </div>
                @endif
            </div>

            {{-- Title and Summary right after gallery - Mobile version --}}
            <div class="special-offer-card__title-after-gallery special-offer-card__title-after-gallery--mobile">
                <div class="special-offer-card__summary-header">
                    <h3 class="special-offer-card__title">{{ translate($specialOffer['title']) ?? __('vacations.special_offer_singular') }}</h3>
                </div>
            </div>

            @if(count($whatsIncluded) > 0)
                <div class="special-offer-card__media-extras">
                    <div class="special-offer-card__panel special-offer-card__panel--inclusives">
                        <div class="special-offer-card__panel-title">{{ __('vacations.included_services') }}</div>
                        <div class="special-offer-card__inclusive-extras">
                            @foreach($whatsIncluded as $item)
                                <span class="special-offer-card__inclusive-chip">✔ {{ translate($item) }}</span>
                            @endforeach
                        </div>
                    </div>
                </div>
            @endif

            @if(count($pricingExtras) > 0)
                <div class="special-offer-card__media-extras" data-expanded-only>
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
                </div>
            @endif
        </div>

        <div class="special-offer-card__content">
            <div class="special-offer-card__content-header">
                {{-- Summary section - Desktop: middle column, Mobile: hidden (uses title-after-gallery instead) --}}
                <div class="special-offer-card__summary">
                    <div class="special-offer-card__summary-header">
                        <h3 class="special-offer-card__title">{{ translate($specialOffer['title']) ?? __('vacations.special_offer_singular') }}</h3>
                    </div>
                </div>

                <div class="special-offer-card__actions">
                    <div class="special-offer-card__actions-column">
                        <div class="special-offer-card__pricing">
                            <div class="special-offer-card__price-label">{{ __('vacations.per_person') }}</div>
                            <div class="special-offer-card__price-amount">{{ $currency === 'EUR' ? '€' : $currency }}{{ number_format($priceAmount, 2, ',', '.') }}</div>
                        </div>
                        <button class="attachment-expand-btn special-offer-card__expand-btn special-offer-card__expand-btn--secondary" data-toggle-btn data-label-more="{{ __('vacations.show_more') }}" data-label-less="{{ __('vacations.show_less') }}">
                            <span data-toggle-text>{{ __('vacations.show_more') }}</span>
                            <span data-toggle-icon>▼</span>
                        </button>
                    </div>
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
                        <h4 class="special-offer-card__component-title">{{ translate($acc['title']) ?? '' }}</h4>
                        <div class="special-offer-card__component-subtitle">{{ translated_catalog_label(['id' => $acc['accommodation_type_id'] ?? null, 'name' => $acc['accommodation_type'] ?? '']) }}</div>

                        @if(!empty($acc['accommodation_details']))
                            <div class="special-offer-card__panel special-offer-card__panel--facts">
                                <div class="special-offer-card__panel-title">{{ __('vacations.details') }}</div>
                                <ul class="special-offer-card__fact-list">
                                    @foreach($acc['accommodation_details'] as $detail)
                                        <li class="special-offer-card__fact-row">
                                            <span class="special-offer-card__fact-label">{{ translated_catalog_label($detail) }}</span>
                                            <span class="special-offer-card__fact-value">{{ is_numeric($detail['value'] ?? null) ? $detail['value'] : translate($detail['value'] ?? '') }}</span>
                                        </li>
                                    @endforeach
                                </ul>
                            </div>
                        @endif
                    </div>
                @endforeach

                @foreach($rentalBoatsFull as $boat)
                    <div class="special-offer-card__component-card special-offer-card__component-card--boat" id="rental-boat-{{ $boat['id'] }}">
                        <h4 class="special-offer-card__component-title">{{ translate($boat['title'] ?? '') }}</h4>
                        <div class="special-offer-card__component-subtitle">{{ translated_catalog_label(['id' => $boat['type_id'] ?? null, 'name' => $boat['type'] ?? '']) }}</div>

                        @if(!empty($boat['boat_info']))
                            <div class="special-offer-card__panel special-offer-card__panel--facts">
                                <div class="special-offer-card__panel-title">{{ __('vacations.boat_information') }}</div>
                                <ul class="special-offer-card__fact-list">
                                    @foreach($boat['boat_info'] as $info)
                                        <li class="special-offer-card__fact-row">
                                            <span class="special-offer-card__fact-label">{{ translated_catalog_label($info) }}</span>
                                            <span class="special-offer-card__fact-value">{{ is_numeric($info['value'] ?? null) ? $info['value'] : translate($info['value'] ?? '') }}</span>
                                        </li>
                                    @endforeach
                                </ul>
                            </div>
                        @endif
                    </div>
                @endforeach

                @foreach($guidingsFull as $guiding)
                    @php
                        $nestedDuration = $guiding['guiding_info']['dauer'] ?? ($guiding['duration_label'] ?? null);
                        $nestedPersons = $chipPresenter::personsValue(
                            $guiding['guiding_info']['max_personen'] ?? ($guiding['max_persons'] ?? null)
                        );
                        $nestedFishingType = $guiding['guiding_info']['art'] ?? null;
                        $nestedWaterChips = $chipPresenter::waterTypeChips($guiding['water_types'] ?? []);
                        $nestedWaterValue = count($nestedWaterChips) > 0
                            ? implode(' · ', array_column($nestedWaterChips, 'value'))
                            : (!empty($guiding['guiding_info']['gewaesser']) ? translate($guiding['guiding_info']['gewaesser']) : null);
                        $resolvedCatalogLabels = fn (array $items) => array_values(array_filter(
                            array_map(fn ($item) => translated_catalog_label($item), $items),
                            fn ($label) => $label !== '' && ! is_numeric($label)
                        ));
                        $nestedMethodsValue = implode(' · ', $resolvedCatalogLabels($guiding['methods'] ?? []));
                        $nestedTargetFishValue = implode(', ', $resolvedCatalogLabels($guiding['target_fish'] ?? []));
                        $guidingFacts = array_filter([
                            __('guidings.Duration') => $nestedDuration,
                            __('guidings.persons') => $nestedPersons,
                            __('guidings.Fishing_Type') => $nestedFishingType,
                            __('guidings.Water') => $nestedWaterValue,
                            __('vacations.fishing_methods') => $nestedMethodsValue ?: null,
                            __('guidings.Target_Fish') => $nestedTargetFishValue ?: null,
                        ]);
                    @endphp
                    <div class="special-offer-card__component-card special-offer-card__component-card--guiding" id="guiding-{{ $guiding['id'] }}">
                        <h4 class="special-offer-card__component-title">{{ translate($guiding['title']) ?? '' }}</h4>
                        @if(!empty($guiding['guiding_info']['art']))
                            <div class="special-offer-card__component-subtitle">{{ $guiding['guiding_info']['art'] ?? '' }}</div>
                        @endif

                        @if(count($guidingFacts) > 0)
                            <div class="special-offer-card__panel special-offer-card__panel--facts">
                                <div class="special-offer-card__panel-title">{{ __('vacations.guiding_information') }}</div>
                                <ul class="special-offer-card__fact-list">
                                    @foreach($guidingFacts as $factLabel => $factValue)
                                        <li class="special-offer-card__fact-row">
                                            <span class="special-offer-card__fact-label">{{ $factLabel }}</span>
                                            <span class="special-offer-card__fact-value">{{ $factValue }}</span>
                                        </li>
                                    @endforeach
                                </ul>
                            </div>
                        @endif
                    </div>
                @endforeach
            </div>
        @endif
        </div>
    </div>

    <!-- Special Offer Gallery Modal -->
    <x-gallery.modal
        :id="$specialOfferGalleryId"
        :images="$galleryImages"
        :title="$specialOfferModalTitle"
        type="tour"
        :badge="__('vacations.special_offer_singular')"
        :price-prefix="__('vacations.per_person')"
        :price-display="$specialOfferPriceDisplay"
    />
</div>

@once
<script src="{{ asset('js/special-offer-card.js') }}"></script>
@endonce




