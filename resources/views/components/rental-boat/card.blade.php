<div class="rental-boat-card" data-rental-boat-card>
    @php
        $galleryImages = $boat['gallery_images'] ?? [];
        $galleryCount = is_array($galleryImages) ? count($galleryImages) : 0;

        $price = $boat['price'] ?? [];
        $priceType = is_array($price) && isset($price['type']) ? $price['type'] : 'per_day';
        $priceAmount = is_array($price) && isset($price['amount']) ? (float) $price['amount'] : 0;
        $priceTypeMap = [
            'per_day' => __('per day'),
            'per_hour' => __('per hour'),
            'per_week' => __('per week'),
            'per_night' => __('per night'),
        ];
        $displayPriceType = $priceTypeMap[$priceType] ?? $priceType;

        $defaultInclusives = [
            __('Safety briefing'),
            __('Anchor'),
            __('Signal horn'),
            __('First aid kit'),
            __('Life vests'),
        ];

        $inclusiveItems = collect(is_array($boat['inclusives'] ?? null) ? $boat['inclusives'] : [])
            ->map(function ($item) {
                return is_array($item) ? ($item['name'] ?? ($item['value'] ?? json_encode($item))) : $item;
            })
            ->filter(fn ($value) => filled($value))
            ->values()
            ->toArray();

        if (count($inclusiveItems) === 0) {
            $inclusiveItems = $defaultInclusives;
        }

        $extraItems = collect(is_array($boat['extras'] ?? null) ? $boat['extras'] : [])
            ->map(function ($item) {
                return is_array($item) ? ($item['name'] ?? ($item['value'] ?? json_encode($item))) : $item;
            })
            ->filter(fn ($value) => filled($value))
            ->values()
            ->toArray();

        $boatInfo = $boat['boat_info'] ?? [];

        $licenseRequirement = null;
        if (!empty($boat['requirements']) && is_array($boat['requirements'])) {
            foreach ($boat['requirements'] as $requirement) {
                $value = is_array($requirement) ? ($requirement['name'] ?? ($requirement['value'] ?? null)) : $requirement;
                if ($value && (stripos($value, 'license') !== false || stripos($value, 'führerschein') !== false)) {
                    $licenseRequirement = $value;
                    break;
                }
            }
        }

        $specs = [];
        $specs[] = [
            'label' => __('Motor'),
            'value' => data_get($boat, 'power') ?? data_get($boat, 'engine') ?? ($boatInfo['power'] ?? ($boatInfo['engine'] ?? '–')),
        ];
        if ($licenseRequirement) {
            $specs[] = [
                'label' => __('License'),
                'value' => $licenseRequirement,
            ];
        }
        $specs[] = [
            'label' => __('Capacity'),
            'value' => data_get($boat, 'seats') ? data_get($boat, 'seats') . ' ' . __('persons') : '–',
        ];
    @endphp

    <div class="rental-boat-card__grid">
        <div class="rental-boat-card__media">
            <div class="rental-boat-card__gallery" data-gallery-images='@json($galleryImages)'>
                <img
                    src="{{ $boat['thumbnail_path'] ?? 'https://images.unsplash.com/photo-1520440229-84f3865cf003?q=80&w=1600&auto=format&fit=crop' }}"
                    alt="{{ $boat['title'] ?? 'Boat' }}"
                    data-gallery-image
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
                <div>
                    <h3 class="rental-boat-card__title">{{ $boat['title'] ?? __('Boat Title') }}</h3>
                    @if(!empty($boat['type']))
                        <div class="rental-boat-card__type">{{ $boat['type'] }}</div>
                    @endif
                </div>
                @if(!empty($boat['description']))
                    <p class="rental-boat-card__description">{{ $boat['description'] }}</p>
                @endif
            </div>

            @if(count($specs) > 0)
                <div class="rental-boat-card__spec-grid">
                    @foreach($specs as $spec)
                        <div class="rental-boat-card__spec">
                            <div class="rental-boat-card__spec-label">{{ $spec['label'] }}</div>
                            <div class="rental-boat-card__spec-value">{{ $spec['value'] }}</div>
                        </div>
                    @endforeach
                </div>
            @endif

            <div class="rental-boat-card__included">
                <div class="rental-boat-card__included-title">{{ __('Included in the price') }}</div>
                <div class="rental-boat-card__included-chips">
                    @foreach(array_slice($inclusiveItems, 0, 6) as $inclusive)
                        <span class="rental-boat-card__included-chip">
                            <svg class="rental-boat-card__check-icon" width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3">
                                <polyline points="20 6 9 17 4 12" />
                            </svg>
                            <span>{{ $inclusive }}</span>
                        </span>
                    @endforeach
                </div>
            </div>
        </div>

        <div class="rental-boat-card__actions">
            <div class="rental-boat-card__price">
                <div class="rental-boat-card__price-type">{{ $displayPriceType }}</div>
                <div class="rental-boat-card__price-amount">€{{ number_format($priceAmount, 2) }}</div>
            </div>
            <button class="rental-boat-card__select-btn">
                {{ __('Select Accommodation') }}
            </button>
            <button class="rental-boat-card__expand-btn rental-boat-card__expand-btn--secondary" data-toggle-btn>
                <span data-toggle-text>{{ __('Show More') }}</span>
                <span data-toggle-icon>▼</span>
            </button>
        </div>

        <div class="rental-boat-card__info-matrix" data-expanded-only>
            <div class="rental-boat-card__info-box rental-boat-card__info-box--extras">
                <div class="rental-boat-card__info-box-title">{{ __('Payable Extras') }}</div>
                <div class="rental-boat-card__info-box-content">
                    @if(count($extraItems) > 0)
                        <ul class="rental-boat-card__info-list">
                            @foreach($extraItems as $extra)
                                <li>{{ $extra }}</li>
                            @endforeach
                        </ul>
                    @else
                        <p class="rental-boat-card__info-empty">{{ __('No extras available') }}</p>
                    @endif
                </div>
            </div>

            <div class="rental-boat-card__info-box rental-boat-card__info-box--info">
                <div class="rental-boat-card__info-box-title">{{ __('Boat Information') }}</div>
                <div class="rental-boat-card__info-box-content">
                    <ul class="rental-boat-card__info-list">
                        @foreach([
                            __('Seats') => $boatInfo['seats'] ?? data_get($boat, 'seats'),
                            __('Length') => isset($boatInfo['length_m']) ? $boatInfo['length_m'] . ' m' : (data_get($boat, 'length_m') ? data_get($boat, 'length_m') . ' m' : null),
                            __('Width') => isset($boatInfo['width_m']) ? $boatInfo['width_m'] . ' m' : (data_get($boat, 'width_m') ? data_get($boat, 'width_m') . ' m' : null),
                            __('Year built') => $boatInfo['year_built'] ?? data_get($boat, 'year_built'),
                            __('Manufacturer') => $boatInfo['manufacturer'] ?? data_get($boat, 'manufacturer'),
                            __('Engine') => $boatInfo['engine'] ?? data_get($boat, 'engine'),
                            __('Power') => $boatInfo['power'] ?? data_get($boat, 'power'),
                            __('Top speed') => isset($boatInfo['max_speed_kmh']) ? $boatInfo['max_speed_kmh'] . ' km/h' : (data_get($boat, 'max_speed_kmh') ? data_get($boat, 'max_speed_kmh') . ' km/h' : null),
                        ] as $label => $value)
                            @if(filled($value))
                                <li>
                                    <span>{{ $label }}:</span>
                                    <strong>{{ $value }}</strong>
                                </li>
                            @endif
                        @endforeach
                    </ul>
                </div>
            </div>

            <div class="rental-boat-card__info-box rental-boat-card__info-box--requirements">
                <div class="rental-boat-card__info-box-title">{{ __('Requirements') }}</div>
                <div class="rental-boat-card__info-box-content">
                    @php
                        $requirementItems = collect(is_array($boat['requirements'] ?? null) ? $boat['requirements'] : [])
                            ->map(function ($item) {
                                return is_array($item) ? ($item['name'] ?? ($item['value'] ?? json_encode($item))) : $item;
                            })
                            ->filter(fn ($value) => filled($value))
                            ->values()
                            ->toArray();
                    @endphp

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
</div>

@once
<script src="{{ asset('js/rental-boat-card.js') }}"></script>
@endonce
