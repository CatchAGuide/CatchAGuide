<div class="rental-boat-card" id="rental-boat-{{ $boat['id'] ?? '' }}" data-rental-boat-card data-rental-boat-id="{{ $boat['id'] ?? '' }}">
    @php
        $boatThumbnail = $boat['thumbnail_path']
            ?? 'https://images.unsplash.com/photo-1520440229-84f3865cf003?q=80&w=1600&auto=format&fit=crop';
        $galleryImages = array_values(array_unique(array_filter(
            array_merge([$boatThumbnail], $boat['gallery_images'] ?? [])
        )));
        $galleryCount = count($galleryImages);
        $boatGalleryId = 'rental-boat-card-'.($boat['id'] ?? uniqid());
        $price = $boat['price'] ?? [];
        $priceAmount = (float) ($price['amount'] ?? 0);
        $displayPriceType = $price['display_type'] ?? __('rental_boats.per_day');
        $inclusiveItems = $boat['inclusives'] ?? [];
        $extraItems = $boat['extras'] ?? [];
        $requirementItems = $boat['requirements'] ?? [];
        $specs = $boat['specs'] ?? [];
        $boatInfoList = $boat['boat_info'] ?? [];
        $boatModalTitle = translate($boat['title'] ?? null) ?? ($boat['title'] ?? 'Boat');
        $boatModalSpecs = array_values(array_filter([
            isset($specs['length']) ? $specs['length'] : null,
            isset($specs['capacity']) ? $specs['capacity'] : null,
            isset($specs['engine']) ? $specs['engine'] : null,
        ]));
        $boatPriceDisplay = '€'.number_format($priceAmount, 2);
    @endphp

    <div class="rental-boat-card__grid">
        <div class="rental-boat-card__media">
            <div class="rental-boat-card__gallery" data-vacation-gallery="{{ $boatGalleryId }}" data-gallery-images='@json($galleryImages)'>
                <img
                    src="{{ $boatThumbnail }}"
                    alt="{{ $boat['title'] ?? 'Boat' }}"
                    loading="lazy"
                    decoding="async"
                    data-vacation-gallery-image
                    data-vacation-open-modal
                    style="cursor: pointer;"
                />

                @if($galleryCount > 1)
                    <div>
                        <button
                            type="button"
                            aria-label="{{ __('vacations.gallery_prev') }}"
                            class="rental-boat-gallery__nav-btn rental-boat-gallery__nav-btn--prev"
                            data-prev-image
                        >
                            ‹
                        </button>
                        <button
                            type="button"
                            aria-label="{{ __('vacations.gallery_next') }}"
                            class="rental-boat-gallery__nav-btn rental-boat-gallery__nav-btn--next"
                            data-next-image
                        >
                            ›
                        </button>
                        <div class="rental-boat-gallery__counter" data-image-counter>1/{{ $galleryCount }}</div>
                    </div>
                @endif
            </div>

            {{-- Title and Summary right after gallery - Mobile version --}}
            <div class="rental-boat-card__title-after-gallery rental-boat-card__title-after-gallery--mobile">
                <div class="rental-boat-card__summary-header">
                    <h3 class="rental-boat-card__title">{{ translate($boat['title']) ?? __('Boat Title') }}</h3>
                    @if(!empty($boat['type']))
                        <div class="rental-boat-card__category">{{ $boat['type'] }}</div>
                    @endif
                </div>

                @if(count($specs) > 0)
                    <div class="rental-boat-card__spec-row">
                        @foreach($specs as $spec)
                            <span class="rental-boat-card__spec-item">
                                <span class="rental-boat-card__spec-label">{{ $spec['label'] }}:</span>
                                <span class="rental-boat-card__spec-value">{{ $spec['value'] }}</span>
                            </span>
                        @endforeach
                    </div>
                @endif

                @if (count($inclusiveItems) > 0 )
                    <div class="rental-boat-card__included">
                        <div class="rental-boat-card__included-title">{{ __('vacations.included_in_price') }}</div>
                        <div class="rental-boat-card__included-chips">
                            @foreach($inclusiveItems as $inclusive)
                                <span class="rental-boat-card__included-chip">
                                    ✅ {{ translate($inclusive) }}
                                </span>
                            @endforeach
                        </div>
                    </div>
                @endif
            </div>

            @if(count($extraItems) > 0)
                <div class="rental-boat-card__extras-inclusives" data-expanded-only>
                    <div class="rental-boat-card__info-box">
                        <div class="rental-boat-card__info-box-title">{{ __('vacations.payable_extras') }}</div>
                        <div class="rental-boat-card__info-box-content">
                            <ul class="rental-boat-card__info-list">
                                @foreach($extraItems as $extra)
                                    <li>{{ translate($extra) }}</li>
                                @endforeach
                            </ul>
                        </div>
                    </div>
                </div>
            @endif
        </div>

        <div class="rental-boat-card__content">
            <div class="rental-boat-card__content-header">
                {{-- Summary section - Desktop: middle column, Mobile: hidden (uses title-after-gallery instead) --}}
                <div class="rental-boat-card__summary">
                    <div class="rental-boat-card__summary-header">
                        <h3 class="rental-boat-card__title">{{ translate($boat['title']) ?? __('Boat Title') }}</h3>
                        @if(!empty($boat['type']))
                            <div class="rental-boat-card__category">{{ translate($boat['type']) }}</div>
                        @endif
                    </div>

                    @if(count($specs) > 0)
                        <div class="rental-boat-card__spec-row">
                            @foreach($specs as $spec)
                                <span class="rental-boat-card__spec-item">
                                    <span class="rental-boat-card__spec-label">{{ translate($spec['label']) }}:</span>
                                    <span class="rental-boat-card__spec-value">{{ translate($spec['value']) }}</span>
                                </span>
                            @endforeach
                        </div>
                    @endif

                    @if (count($inclusiveItems) > 0 )
                        <div class="rental-boat-card__included">
                            <div class="rental-boat-card__included-title">{{ __('vacations.included_in_price') }}</div>
                            <div class="rental-boat-card__included-chips">
                                @foreach($inclusiveItems as $inclusive)
                                    <span class="rental-boat-card__included-chip">
                                        ✅ {{ translate($inclusive) }}
                                    </span>
                                @endforeach
                            </div>
                        </div>
                    @endif
                </div>

                <div class="rental-boat-card__actions">
                    <div class="rental-boat-card__actions-column">
                        <div class="rental-boat-card__price">
                            <div class="rental-boat-card__price-type">{{ __('rental_boats.per_day') }}</div>
                            <div class="rental-boat-card__price-amount">€{{ number_format($priceAmount, 2) }}</div>
                        </div>
                        {{-- <button class="rental-boat-card__select-btn">
                            {{ __('Select Boat') }}
                        </button> --}}
                        <button class="rental-boat-card__expand-btn rental-boat-card__expand-btn--secondary" data-toggle-btn data-label-more="{{ __('vacations.show_more') }}" data-label-less="{{ __('vacations.show_less') }}">
                            <span data-toggle-text>{{ __('vacations.show_more') }}</span>
                            <span data-toggle-icon>▼</span>
                        </button>
                    </div>
                </div>
            </div>

            <div class="rental-boat-card__info-matrix" data-expanded-only>
                <div class="rental-boat-card__info-box">
                    <div class="rental-boat-card__info-box-title">{{ __('vacations.boat_information') }}</div>
                    <div class="rental-boat-card__info-box-content">
                        @if(count($boatInfoList) > 0)
                            <ul class="rental-boat-card__info-list">
                                @foreach($boatInfoList as $info)
                                    <li>
                                        <span>{{ translate($info['name']) }}:</span>
                                        <strong>{{ translate($info['value']) }}</strong>
                                    </li>
                                @endforeach
                            </ul>
                        @else
                            <p class="rental-boat-card__info-empty">{{ __('vacations.no_boat_information') }}</p>
                        @endif
                    </div>
                </div>

                <div class="rental-boat-card__info-box">
                    <div class="rental-boat-card__info-box-title">{{ __('guidings.Requirements') }}</div>
                    <div class="rental-boat-card__info-box-content">
                        @if(count($requirementItems) > 0)
                            <ul class="rental-boat-card__info-list">
                                @foreach($requirementItems as $requirement)
                                    <li>{{ translate($requirement) }}</li>
                                @endforeach
                            </ul>
                        @else
                            <p class="rental-boat-card__info-empty">{{ __('vacations.no_special_requirements') }}</p>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Rental Boat Gallery Modal -->
    <x-gallery.modal
        :id="$boatGalleryId"
        :images="$galleryImages"
        :title="$boatModalTitle"
        type="tour"
        :badge="__('vacations.rental_boat')"
        :specs="$boatModalSpecs"
        :price-prefix="$displayPriceType"
        :price-display="$boatPriceDisplay"
    />
</div>

@once
<script src="{{ asset('js/rental-boat-card.js') }}"></script>
@endonce
