<div class="accommodation-card" id="accommodation-{{ $accommodation['id'] ?? '' }}" data-accommodation-card data-accommodation-id="{{ $accommodation['id'] ?? '' }}">
    @php
        $accommodationThumbnail = $accommodation['thumbnail_path']
            ?? 'https://images.unsplash.com/photo-1519710164239-da123dc03ef4?q=80&w=1600&auto=format&fit=crop';
        $galleryImages = array_values(array_unique(array_filter(
            array_merge([$accommodationThumbnail], $accommodation['gallery_images'] ?? [])
        )));
        $galleryTotal = count($galleryImages);
        $accommodationGalleryId = 'accommodation-card-'.($accommodation['id'] ?? uniqid());
        $stats = $accommodation['stats'] ?? [];
        $bedSummary = $accommodation['bed_summary'] ?? '';
        $occupancyLabel = $accommodation['occupancy_label'] ?? null;
        $bathroomChipValue = $accommodation['number_of_bathrooms'] ?? ($accommodation['bathroom_count'] ?? ($accommodation['bathrooms'] ?? null));
        $livingAreaChipValue = $accommodation['living_area_value'] ?? ($accommodation['living_area_sqm'] ?? null);
        $bathroomChipDisplay = is_numeric($bathroomChipValue)
            ? $bathroomChipValue
            : ((is_string($bathroomChipValue) && trim($bathroomChipValue) !== '' && strtolower(trim($bathroomChipValue)) !== 'keine angabe')
                ? $bathroomChipValue
                : '–');
        $livingAreaChipDisplay = is_numeric($livingAreaChipValue)
            ? $livingAreaChipValue
            : ((is_string($livingAreaChipValue) && trim($livingAreaChipValue) !== '' && strtolower(trim($livingAreaChipValue)) !== 'keine angabe')
                ? $livingAreaChipValue
                : '–');
        $bathroomChipSuffix = is_numeric($bathroomChipDisplay)
            ? ((int) $bathroomChipDisplay === 1 ? ' Bath' : ' Baths')
            : '';
        $livingAreaChipSuffix = is_numeric($livingAreaChipDisplay) ? ' m²' : '';
        $accommodationModalTitle = translate($accommodation['title'] ?? null) ?? ($accommodation['title'] ?? 'Apartment');
        $accommodationModalSpecs = array_values(array_filter([
            $occupancyLabel,
            is_numeric($bathroomChipDisplay) ? ($bathroomChipDisplay.$bathroomChipSuffix) : null,
            is_numeric($livingAreaChipDisplay) ? ($livingAreaChipDisplay.$livingAreaChipSuffix) : null,
        ]));
        $accommodationPriceDisplay = '€'.number_format((float) ($accommodation['price']['amount'] ?? 0), 2);
    @endphp

    <div class="accommodation-card__grid">
        <div class="accommodation-card__media">
            <div class="accommodation-gallery" data-vacation-gallery="{{ $accommodationGalleryId }}" data-gallery-images='@json($galleryImages)'>
                <img src="{{ $accommodationThumbnail }}" alt="{{ $accommodation['title'] ?? 'Apartment' }}" loading="lazy" decoding="async" data-vacation-gallery-image data-vacation-open-modal style="cursor: pointer;" />

                @if($galleryTotal > 1)
                <div>
                    <button
                        type="button"
                        aria-label="{{ __('vacations.gallery_prev') }}"
                        class="accommodation-gallery__nav-btn accommodation-gallery__nav-btn--prev"
                        data-prev-image
                    >
                        ‹
                    </button>
                    <button
                        type="button"
                        aria-label="{{ __('vacations.gallery_next') }}"
                        class="accommodation-gallery__nav-btn accommodation-gallery__nav-btn--next"
                        data-next-image
                    >
                        ›
                    </button>
                    <div class="accommodation-gallery__counter" data-image-counter>
                        1/{{ $galleryTotal }}
                    </div>
                </div>
                @endif
            </div>

            {{-- Title and Summary right after gallery - Mobile version --}}
            <div class="accommodation-card__title-after-gallery accommodation-card__title-after-gallery--mobile">
                <div class="accommodation-card__summary-header">
                    <h3 class="accommodation-card__title">{{ translate($accommodation['title']) ?? 'Apartment Title' }}</h3>
                    <div class="accommodation-card__type">{{ translated_catalog_label(['id' => $accommodation['accommodation_type_id'] ?? null, 'name' => $accommodation['accommodation_type'] ?? __('vacations.accommodation')]) }}</div>
                </div>

                @include('components.accommodation.partials.summary-meta')
            </div>

            {{-- Details panel appears after gallery (desktop expanded only) --}}
            <div class="accommodation-card__left-panels" data-expanded-only>
                <div class="accommodation-card__panel">
                    <div class="accommodation-card__panel-title">{{ __('vacations.details') }}</div>
                    <ul class="accommodation-card__bullet-list">
                        @foreach($accommodation['accommodation_details'] as $detail)
                            <li>{{ translated_catalog_label($detail) }}: <span class="font-medium">{{ is_numeric($detail['value'] ?? null) ? $detail['value'] : translate($detail['value'] ?? '') }}</span></li>
                        @endforeach
                    </ul>
                </div>

                @if(!empty($accommodation['policies']))
                    <div class="accommodation-card__panel">
                        <div class="accommodation-card__panel-title">{{ __('accommodations.policies') }}</div>
                        @if(!empty($accommodation['policies']))
                            <ul class="accommodation-card__bullet-list">
                                @foreach ($accommodation['policies'] as $policy)
                                    <li>{{ translated_catalog_label($policy) }}@if(filled($policy['value'] ?? null)): {{ is_numeric($policy['value']) ? $policy['value'] : translate($policy['value']) }}@endif</li>
                                @endforeach
                            </ul>
                        @endif
                    </div>
                @endif
            </div>
        </div>

        <div class="accommodation-card__content">
            <div class="accommodation-card__content-header">
                {{-- Summary section - Desktop: right column, Mobile: hidden (uses title-after-gallery instead) --}}
                <div class="accommodation-card__summary">
                    <div class="accommodation-card__summary-header">
                        <h3 class="accommodation-card__title">{{ translate($accommodation['title']) ?? 'Apartment Title' }}</h3>
                        <div class="accommodation-card__type">{{ translated_catalog_label(['id' => $accommodation['accommodation_type_id'] ?? null, 'name' => $accommodation['accommodation_type'] ?? __('vacations.accommodation')]) }}</div>
                    </div>

                    @include('components.accommodation.partials.summary-meta')

                </div>

                <div class="accommodation-card__actions">
                    <div class="accommodation-card__actions-column">
                        <div class="accommodation-card__pricing">
                            @php
                                $priceType = $accommodation['price']['type'] ?? 'per_night';
                                $translatedPriceType = match($priceType) {
                                    'per_person' => __('vacations.per_person'),
                                    'per_night' => __('accommodations.per_night'),
                                    default => ucfirst(str_replace('_', ' ', $priceType))
                                };
                            @endphp
                            {{-- <div class="accommodation-card__price-type">{{ $translatedPriceType }}</div> --}}
                            <div class="accommodation-card__price-type">{{ __('rental_boats.per_day') }}</div>
                            <div class="accommodation-card__price-amount">€{{ number_format($accommodation['price']['amount'] ?? 0, 2) }}</div>
                        </div>
                        {{-- <button class="accommodation-card__select-btn">
                            Select Accommodation
                        </button> --}}
                        <button class="attachment-expand-btn accommodation-card__expand-btn accommodation-card__expand-btn--secondary" data-toggle-btn data-label-more="{{ __('vacations.show_more') }}" data-label-less="{{ __('vacations.show_less') }}">
                            <span data-toggle-text>{{ __('vacations.show_more') }}</span>
                            <span data-toggle-icon>▼</span>
                        </button>
                    </div>
                </div>
            </div>

            <div class="accommodation-card__feature-grid" data-expanded-only>
            {{-- Mobile-only Details panel (appears before Amenities on mobile) --}}
            <div class="accommodation-card__panel accommodation-card__panel--mobile-only accommodation-card__panel--mobile-details">
                <div class="accommodation-card__panel-title">{{ __('vacations.details') }}</div>
                <ul class="accommodation-card__bullet-list">
                    @foreach($accommodation['accommodation_details'] as $detail)
                        <li>{{ translated_catalog_label($detail) }}: <span class="font-medium">{{ is_numeric($detail['value'] ?? null) ? $detail['value'] : translate($detail['value'] ?? '') }}</span></li>
                    @endforeach
                </ul>
            </div>

            <div class="accommodation-card__panel">
                <div class="accommodation-card__panel-title">{{ __('vacations.amenities') }}</div>
                <ul class="accommodation-card__chip-list">
                    @if(isset($accommodation['amenities']) && is_array($accommodation['amenities']) && count($accommodation['amenities']) > 0)
                        @foreach($accommodation['amenities'] as $amenity)
                            <li class="accommodation-card__chip">{{ translated_catalog_label($amenity) }}</li>
                        @endforeach
                    @endif
                </ul>
            </div>

            {{-- Mobile-only Policies panel (appears after Amenities on mobile) --}}
            @if(!empty($accommodation['policies']))
                <div class="accommodation-card__panel accommodation-card__panel--mobile-only accommodation-card__panel--mobile-policies">
                    <div class="accommodation-card__panel-title">{{ __('accommodations.policies') }}</div>
                    <ul class="accommodation-card__bullet-list">
                        @foreach ($accommodation['policies'] as $policy)
                            <li>{{ translated_catalog_label($policy) }}@if(filled($policy['value'] ?? null)): {{ is_numeric($policy['value']) ? $policy['value'] : translate($policy['value']) }}@endif</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <div class="accommodation-card__panel">
                <div class="accommodation-card__panel-title">{{ __('vacations.kitchen_equipment') }}</div>
                @if(!empty($accommodation['kitchen']))
                    <ul class="accommodation-card__bullet-list">
                        @foreach($accommodation['kitchen'] as $kitchen)
                            <li class="accommodation-card__chip">{{ translated_catalog_label($kitchen) }}</li>
                        @endforeach
                    </ul>
                @else
                    <p class="accommodation-card__empty">{{ __('vacations.no_kitchen_details') }}</p>
                @endif
            </div>

            <div class="accommodation-card__panel">
                <div class="accommodation-card__panel-title">{{ __('vacations.bathroom_equipment') }}</div>
                @if(!empty($accommodation['bathroom_laundry']))
                    <ul class="accommodation-card__bullet-list">
                        @foreach($accommodation['bathroom_laundry'] as $bathroom_laundry)
                            <li class="accommodation-card__chip">{{ translated_catalog_label($bathroom_laundry) }}</li>
                        @endforeach
                    </ul>
                @else
                    <p class="accommodation-card__empty">{{ __('vacations.no_bathroom_details') }}</p>
                @endif
            </div>

            @if(!empty($accommodation['extras_inclusives']['inclusives']) || !empty($accommodation['extras_inclusives']['extras']))
                <div class="accommodation-card__panel accommodation-card__panel--extras">
                    <div class="accommodation-card__panel-columns">
                        @if(!empty($accommodation['extras_inclusives']['inclusives']))
                            <div>
                                <div class="accommodation-card__panel-title">{{ __('vacations.included_services') }}</div>
                                <div class="accommodation-card__inclusive-extras">
                                    @foreach($accommodation['extras_inclusives']['inclusives'] as $inclusive)
                                        <span class="accommodation-card__inclusive-chip">
                                            <svg class="accommodation-card__check-icon" width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" aria-hidden="true">
                                                <polyline points="20 6 9 17 4 12"/>
                                            </svg>
                                            <span>{{ translated_catalog_label($inclusive) }}</span>
                                        </span>
                                    @endforeach
                                </div>
                            </div>
                        @endif

                        @if(!empty($accommodation['extras_inclusives']['extras']))
                            <div>
                                <div class="accommodation-card__panel-title">{{ __('vacations.excluded') }}</div>
                                <div class="accommodation-card__inclusive-extras">
                                    @foreach($accommodation['extras_inclusives']['extras'] as $extra)
                                        <span class="accommodation-card__inclusive-chip accommodation-card__inclusive-chip--extra" title="{{ __('vacations.extra_addon_hint') }}">
                                            <svg class="accommodation-card__extra-icon" width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" aria-hidden="true">
                                                <line x1="18" y1="6" x2="6" y2="18"/>
                                                <line x1="6" y1="6" x2="18" y2="18"/>
                                            </svg>
                                            <span>{{ translated_catalog_label($extra) }}</span>
                                        </span>
                                    @endforeach
                                </div>
                            </div>
                        @endif
                    </div>
                </div>
            @endif
        </div>
        </div>
    </div>

    <!-- Accommodation Gallery Modal -->
    <x-gallery.modal
        :id="$accommodationGalleryId"
        :images="$galleryImages"
        :title="$accommodationModalTitle"
        type="camp"
        :badge="__('vacations.accommodation')"
        :specs="$accommodationModalSpecs"
        :price-prefix="__('rental_boats.per_day')"
        :price-display="$accommodationPriceDisplay"
    />
</div>

@once
<script src="{{ asset('js/accommodation-card.js') }}"></script>
@endonce
