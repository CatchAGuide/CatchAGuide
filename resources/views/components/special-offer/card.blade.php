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
        $accommodations = $specialOffer['accommodations'] ?? [];
        $rentalBoats = $specialOffer['rental_boats'] ?? [];
        $guidings = $specialOffer['guidings'] ?? [];
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

                <div class="special-offer-card__anchor-points">
                    @if(count($accommodations) > 0)
                        <div class="special-offer-card__anchor-category" data-category-type="accommodation">
                            <span class="special-offer-card__anchor-category-label">{{ __('vacations.accommodation') }}</span>
                            <div class="special-offer-card__anchor-buttons">
                                @foreach($accommodations as $index => $accommodation)
                                    <a href="#accommodation-{{ $accommodation['id'] }}" 
                                       class="special-offer-card__anchor-box special-offer-card__anchor-box--accommodation {{ $index >= 3 ? 'special-offer-card__anchor-box--hidden' : '' }}" 
                                       data-anchor-type="accommodation"
                                       data-anchor-id="{{ $accommodation['id'] }}"
                                       data-anchor-scroll>
                                        <span class="special-offer-card__anchor-box-text">{{ translate($accommodation['title']) ?? '{Title}' }}</span>
                                    </a>
                                @endforeach
                                @if(count($accommodations) > 3)
                                    <button type="button" class="special-offer-card__anchor-toggle" data-toggle-category="accommodation" aria-label="{{ __('vacations.show_more') }}">
                                        <span class="special-offer-card__anchor-toggle-text">...</span>
                                    </button>
                                @endif
                            </div>
                        </div>
                    @endif

                    @if(count($rentalBoats) > 0)
                        <div class="special-offer-card__anchor-category" data-category-type="boat">
                            <span class="special-offer-card__anchor-category-label">{{ __('vacations.rental_boat') }}</span>
                            <div class="special-offer-card__anchor-buttons">
                                @foreach($rentalBoats as $index => $boat)
                                    <a href="#rental-boat-{{ $boat['id'] }}" 
                                       class="special-offer-card__anchor-box special-offer-card__anchor-box--boat {{ $index >= 3 ? 'special-offer-card__anchor-box--hidden' : '' }}" 
                                       data-anchor-type="boat"
                                       data-anchor-id="{{ $boat['id'] }}"
                                       data-anchor-scroll>
                                        <span class="special-offer-card__anchor-box-text">{{ translate($boat['title']) ?? '{Title}' }}</span>
                                    </a>
                                @endforeach
                                @if(count($rentalBoats) > 3)
                                    <button type="button" class="special-offer-card__anchor-toggle" data-toggle-category="boat" aria-label="{{ __('vacations.show_more') }}">
                                        <span class="special-offer-card__anchor-toggle-text">...</span>
                                    </button>
                                @endif
                            </div>
                        </div>
                    @endif

                    @if(count($guidings) > 0)
                        <div class="special-offer-card__anchor-category" data-category-type="guiding">
                            <span class="special-offer-card__anchor-category-label">{{ __('vacations.guidings') }}</span>
                            <div class="special-offer-card__anchor-buttons">
                                @foreach($guidings as $index => $guiding)
                                    <a href="#guiding-{{ $guiding['id'] }}" 
                                       class="special-offer-card__anchor-box special-offer-card__anchor-box--guiding {{ $index >= 3 ? 'special-offer-card__anchor-box--hidden' : '' }}" 
                                       data-anchor-type="guiding"
                                       data-anchor-id="{{ $guiding['id'] }}"
                                       data-anchor-scroll>
                                        <span class="special-offer-card__anchor-box-text">{{ translate($guiding['title']) ?? '{Title}' }}</span>
                                    </a>
                                @endforeach
                                @if(count($guidings) > 3)
                                    <button type="button" class="special-offer-card__anchor-toggle" data-toggle-category="guiding" aria-label="{{ __('vacations.show_more') }}">
                                        <span class="special-offer-card__anchor-toggle-text">...</span>
                                    </button>
                                @endif
                            </div>
                        </div>
                    @endif
                </div>
            </div>

            @if(count($whatsIncluded) > 0 || count($pricingExtras) > 0)
                <div class="special-offer-card__media-extras" data-expanded-only>
                    @if(count($whatsIncluded) > 0)
                        <div class="special-offer-card__panel special-offer-card__panel--inclusives">
                            <div class="special-offer-card__panel-title">{{ __('vacations.included_services') }}</div>
                            <div class="special-offer-card__inclusive-extras">
                                @foreach($whatsIncluded as $item)
                                    <span class="special-offer-card__inclusive-chip">✔ {{ translate($item) }}</span>
                                @endforeach
                            </div>
                        </div>
                    @endif

                    @if(count($pricingExtras) > 0)
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
                    @endif
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

            <div class="special-offer-card__anchor-points">
                @if(count($accommodations) > 0)
                    <div class="special-offer-card__anchor-category" data-category-type="accommodation">
                        <span class="special-offer-card__anchor-category-label">{{ __('vacations.accommodation') }}</span>
                        <div class="special-offer-card__anchor-buttons">
                            @foreach($accommodations as $index => $accommodation)
                                <a href="#accommodation-{{ $accommodation['id'] }}" 
                                   class="special-offer-card__anchor-box special-offer-card__anchor-box--accommodation {{ $index >= 3 ? 'special-offer-card__anchor-box--hidden' : '' }}" 
                                   data-anchor-type="accommodation"
                                   data-anchor-id="{{ $accommodation['id'] }}"
                                   data-anchor-scroll>
                                    <span class="special-offer-card__anchor-box-text">{{ translate($accommodation['title']) ?? '{Title}' }}</span>
                                </a>
                            @endforeach
                            @if(count($accommodations) > 3)
                                <button type="button" class="special-offer-card__anchor-toggle" data-toggle-category="accommodation" aria-label="{{ __('vacations.show_more') }}">
                                    <span class="special-offer-card__anchor-toggle-text">...</span>
                                </button>
                            @endif
                        </div>
                    </div>
                @endif

                @if(count($rentalBoats) > 0)
                    <div class="special-offer-card__anchor-category" data-category-type="boat">
                        <span class="special-offer-card__anchor-category-label">{{ __('vacations.rental_boat') }}</span>
                        <div class="special-offer-card__anchor-buttons">
                            @foreach($rentalBoats as $index => $boat)
                                <a href="#rental-boat-{{ $boat['id'] }}" 
                                   class="special-offer-card__anchor-box special-offer-card__anchor-box--boat {{ $index >= 3 ? 'special-offer-card__anchor-box--hidden' : '' }}" 
                                   data-anchor-type="boat"
                                   data-anchor-id="{{ $boat['id'] }}"
                                   data-anchor-scroll>
                                    <span class="special-offer-card__anchor-box-text">{{ translate($boat['title']) ?? '{Title}' }}</span>
                                </a>
                            @endforeach
                            @if(count($rentalBoats) > 3)
                                <button type="button" class="special-offer-card__anchor-toggle" data-toggle-category="boat" aria-label="{{ __('vacations.show_more') }}">
                                    <span class="special-offer-card__anchor-toggle-text">...</span>
                                </button>
                            @endif
                        </div>
                    </div>
                @endif

                @if(count($guidings) > 0)
                    <div class="special-offer-card__anchor-category" data-category-type="guiding">
                        <span class="special-offer-card__anchor-category-label">{{ __('vacations.guidings') }}</span>
                        <div class="special-offer-card__anchor-buttons">
                            @foreach($guidings as $index => $guiding)
                                <a href="#guiding-{{ $guiding['id'] }}" 
                                   class="special-offer-card__anchor-box special-offer-card__anchor-box--guiding {{ $index >= 3 ? 'special-offer-card__anchor-box--hidden' : '' }}" 
                                   data-anchor-type="guiding"
                                   data-anchor-id="{{ $guiding['id'] }}"
                                   data-anchor-scroll>
                                    <span class="special-offer-card__anchor-box-text">{{ translate($guiding['title']) ?? '{Title}' }}</span>
                                </a>
                            @endforeach
                            @if(count($guidings) > 3)
                                <button type="button" class="special-offer-card__anchor-toggle" data-toggle-category="guiding" aria-label="{{ __('vacations.show_more') }}">
                                    <span class="special-offer-card__anchor-toggle-text">...</span>
                                </button>
                            @endif
                        </div>
                    </div>
                @endif
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
                    @php
                        $accPersons = $chipPresenter::personsValue($acc['max_occupancy'] ?? null);
                        $accBathroomRaw = $acc['number_of_bathrooms'] ?? ($acc['bathroom_count'] ?? ($acc['bathrooms'] ?? null));
                        $accBathroom = $chipPresenter::bedroomsValue($accBathroomRaw);
                        $accArea = $chipPresenter::areaValue($acc['living_area_sqm'] ?? null);
                        $accBedrooms = $chipPresenter::bedroomsValue($acc['number_of_bedrooms'] ?? null);
                        $accBedChips = $chipPresenter::bedChips($acc['bed_items'] ?? [], $acc['bed_summary'] ?? null);
                        $accWater = $acc['distances']['to_water_m'] ?? null;
                        $accParking = $acc['distances']['to_parking_m'] ?? null;
                    @endphp
                    <div class="special-offer-card__component-card special-offer-card__component-card--accommodation" id="accommodation-{{ $acc['id'] }}">
                        <h4 class="special-offer-card__component-title">{{ translate($acc['title']) ?? '' }}</h4>
                        <div class="special-offer-card__component-subtitle">{{ translated_catalog_label(['id' => $acc['accommodation_type_id'] ?? null, 'name' => $acc['accommodation_type'] ?? '']) }}</div>

                        <div class="special-offer-card__component-badges">
                            @if($accPersons)
                                <x-vacation.attachment-chip type="persons" :value="$accPersons" />
                            @endif
                            @if($accBathroom)
                                <x-vacation.attachment-chip type="bath" :value="$accBathroom" />
                            @endif
                            @if($accArea)
                                <x-vacation.attachment-chip type="area" :value="$accArea" />
                            @endif
                            @if($accBedrooms)
                                <x-vacation.attachment-chip type="bedrooms" :value="$accBedrooms" />
                            @endif
                            @foreach($accBedChips as $bedChip)
                                <x-vacation.attachment-chip type="bed" :value="$bedChip['value']" />
                            @endforeach
                            @if(!empty($accWater))
                                <x-vacation.attachment-chip
                                    type="water"
                                    :label="__('vacations.label_water')"
                                    :value="is_numeric($accWater) ? $accWater.' m' : (translate($accWater) ?: $accWater)"
                                />
                            @endif
                            @if(!empty($accParking))
                                <x-vacation.attachment-chip
                                    type="parking"
                                    :label="__('vacations.label_parking')"
                                    :value="is_numeric($accParking) ? $accParking.' m' : (translate($accParking) ?: $accParking)"
                                />
                            @endif
                        </div>
                    </div>
                @endforeach

                @foreach($rentalBoatsFull as $boat)
                    @php
                        $nestedBoatChips = $chipPresenter::boatChips($boat['specs'] ?? []);
                    @endphp
                    <div class="special-offer-card__component-card special-offer-card__component-card--boat" id="rental-boat-{{ $boat['id'] }}">
                        <h4 class="special-offer-card__component-title">{{ translate($boat['title'] ?? '') }}</h4>
                        <div class="special-offer-card__component-subtitle">{{ translated_catalog_label(['id' => $boat['type_id'] ?? null, 'name' => $boat['type'] ?? '']) }}</div>

                        @if(count($nestedBoatChips) > 0)
                            <div class="special-offer-card__component-badges">
                                @foreach($nestedBoatChips as $chip)
                                    <x-vacation.attachment-chip
                                        :type="$chip['type']"
                                        :value="$chip['value']"
                                        :label="$chip['label']"
                                    />
                                @endforeach
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
                        $nestedWaterChips = $chipPresenter::waterTypeChips($guiding['water_types'] ?? []);
                    @endphp
                    <div class="special-offer-card__component-card special-offer-card__component-card--guiding" id="guiding-{{ $guiding['id'] }}">
                        <h4 class="special-offer-card__component-title">{{ translate($guiding['title']) ?? '' }}</h4>
                        @if(!empty($guiding['guiding_info']['art']))
                            <div class="special-offer-card__component-subtitle">{{ $guiding['guiding_info']['art'] ?? '' }}</div>
                        @endif

                        <div class="special-offer-card__component-badges">
                            @if(!empty($nestedDuration))
                                <x-vacation.attachment-chip type="duration" :value="$nestedDuration" />
                            @endif
                            @if($nestedPersons)
                                <x-vacation.attachment-chip type="persons" :value="$nestedPersons" />
                            @endif
                            @foreach($nestedWaterChips as $waterChip)
                                <x-vacation.attachment-chip type="water-type" :value="$waterChip['value']" />
                            @endforeach
                        </div>
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




