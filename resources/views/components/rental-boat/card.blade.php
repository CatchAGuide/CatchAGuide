<div class="rental-boat-card" data-rental-boat-card>
    @php
        $galleryImages = $boat['gallery_images'] ?? [];
        $galleryCount = $boat['gallery_count'] ?? count($galleryImages);
        $price = $boat['price'] ?? [];
        $priceAmount = (float) ($price['amount'] ?? 0);
        $displayPriceType = $price['display_type'] ?? __('per day');
        $inclusiveItems = $boat['inclusives'] ?? [];
        $extraItems = $boat['extras'] ?? [];
        $requirementItems = $boat['requirements'] ?? [];
        $specs = $boat['specs'] ?? [];
        $boatInfoList = $boat['boat_info'] ?? [];
    @endphp

    <div class="rental-boat-card__grid">
        <div class="rental-boat-card__media">
            <div class="rental-boat-card__gallery" data-gallery-images='@json($galleryImages)'>
                <img
                    src="{{ $boat['thumbnail_path'] ?? 'https://images.unsplash.com/photo-1520440229-84f3865cf003?q=80&w=1600&auto=format&fit=crop' }}"
                    alt="{{ $boat['title'] ?? 'Boat' }}"
                    data-gallery-image
                    data-open-modal
                    style="cursor: pointer;"
                />

                @if($galleryCount > 1)
                    <div>
                        <button
                            type="button"
                            aria-label="{{ __('Previous image') }}"
                            class="rental-boat-gallery__nav-btn rental-boat-gallery__nav-btn--prev"
                            data-prev-image
                        >
                            ‹
                        </button>
                        <button
                            type="button"
                            aria-label="{{ __('Next image') }}"
                            class="rental-boat-gallery__nav-btn rental-boat-gallery__nav-btn--next"
                            data-next-image
                        >
                            ›
                        </button>
                        <div class="rental-boat-gallery__counter" data-image-counter>1/{{ $galleryCount }}</div>
                    </div>
                @endif
            </div>
        </div>

        <div class="rental-boat-card__summary">
            <div class="rental-boat-card__summary-header">
                <h3 class="rental-boat-card__title">{{ $boat['title'] ?? __('Boat Title') }}</h3>
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
                    <div class="rental-boat-card__included-title">{{ __('Included in the price') }}</div>
                    <div class="rental-boat-card__included-chips">
                        @foreach($inclusiveItems as $inclusive)
                            <span class="rental-boat-card__included-chip">
                                ✅ {{ $inclusive }}
                            </span>
                        @endforeach
                    </div>
                </div>
            @endif
        </div>

        <div class="rental-boat-card__actions">
            <div class="rental-boat-card__actions-column">
                <div class="rental-boat-card__price">
                    <div class="rental-boat-card__price-type">{{ $displayPriceType }}</div>
                    <div class="rental-boat-card__price-amount">€{{ number_format($priceAmount, 2) }}</div>
                </div>
                {{-- <button class="rental-boat-card__select-btn">
                    {{ __('Select Boat') }}
                </button> --}}
                <button class="rental-boat-card__expand-btn rental-boat-card__expand-btn--secondary" data-toggle-btn>
                    <span data-toggle-text>{{ __('Show More') }}</span>
                    <span data-toggle-icon>▼</span>
                </button>
            </div>
        </div>

        @if(count($extraItems) > 0)
            <div class="rental-boat-card__extras-inclusives" data-expanded-only>
                <div class="rental-boat-card__info-box">
                    <div class="rental-boat-card__info-box-title">{{ __('Payable Extras') }}</div>
                    <div class="rental-boat-card__info-box-content">
                        <ul class="rental-boat-card__info-list">
                            @foreach($extraItems as $extra)
                                <li>{{ $extra }}</li>
                            @endforeach
                        </ul>
                    </div>
                </div>
            </div>
        @endif

        <div class="rental-boat-card__info-matrix" data-expanded-only>
            <div class="rental-boat-card__info-box">
                <div class="rental-boat-card__info-box-title">{{ __('Boat Information') }}</div>
                <div class="rental-boat-card__info-box-content">
                    @if(count($boatInfoList) > 0)
                        <ul class="rental-boat-card__info-list">
                            @foreach($boatInfoList as $info)
                                <li>
                                    <span>{{ $info['name'] }}:</span>
                                    <strong>{{ $info['value'] }}</strong>
                                </li>
                            @endforeach
                        </ul>
                    @else
                        <p class="rental-boat-card__info-empty">{{ __('No boat information available') }}</p>
                    @endif
                </div>
            </div>

            <div class="rental-boat-card__info-box">
                <div class="rental-boat-card__info-box-title">{{ __('Requirements') }}</div>
                <div class="rental-boat-card__info-box-content">
                    @if(count($requirementItems) > 0)
                        <ul class="rental-boat-card__info-list">
                            @foreach($requirementItems as $requirement)
                                <li>{{ $requirement }}</li>
                            @endforeach
                        </ul>
                    @else
                        <p class="rental-boat-card__info-empty">{{ __('No special requirements') }}</p>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <!-- Rental Boat Gallery Modal -->
    <div class="rental-boat-gallery-modal" data-rental-boat-modal>
        <div class="rental-boat-gallery-modal__content">
            <button class="rental-boat-gallery-modal__close">&times;</button>
            <button class="rental-boat-gallery-modal__prev">&#10094;</button>
            <button class="rental-boat-gallery-modal__next">&#10095;</button>
            <img class="rental-boat-gallery-modal__image" src="" alt="{{ $boat['title'] ?? 'Boat' }}">
            <div class="rental-boat-gallery-modal__counter">
                <span class="rental-boat-gallery-modal__current">1</span> / <span class="rental-boat-gallery-modal__total">{{ $galleryCount }}</span>
            </div>
        </div>
    </div>
</div>

@once
<script src="{{ asset('js/rental-boat-card.js') }}"></script>
@endonce
