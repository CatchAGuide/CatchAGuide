<div class="accommodation-card" data-accommodation-card>
    @php
        $galleryImages = $accommodation['gallery_images'] ?? [];
        $galleryTotal = $accommodation['gallery_total'] ?? max(count($galleryImages), 1);
        $stats = $accommodation['stats'] ?? [];
        $bedSummary = $accommodation['bed_summary'] ?? 'Keine Angaben zur Bettenanzahl';
        $occupancyLabel = $accommodation['occupancy_label'] ?? null;
        $bathroomChipValue = $accommodation['bathroom_count'] ?? ($accommodation['bathrooms'] ?? null);
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
    @endphp

    <div class="accommodation-card__grid">
        <div class="accommodation-card__media">
            <div class="accommodation-gallery" data-gallery-images='@json($galleryImages)'>
                <img src="{{ $accommodation['thumbnail_path'] ?? 'https://images.unsplash.com/photo-1519710164239-da123dc03ef4?q=80&w=1600&auto=format&fit=crop' }}" alt="{{ $accommodation['title'] ?? 'Apartment' }}" data-gallery-image />

                <div>
                    <button
                        type="button"
                        aria-label="Previous image"
                        class="accommodation-gallery__nav-btn accommodation-gallery__nav-btn--prev"
                        data-prev-image
                    >
                        ‹
                    </button>
                    <button
                        type="button"
                        aria-label="Next image"
                        class="accommodation-gallery__nav-btn accommodation-gallery__nav-btn--next"
                        data-next-image
                    >
                        ›
                    </button>
                    <div class="accommodation-gallery__counter" data-image-counter>
                        1/{{ $galleryTotal }}
                    </div>
                </div>
            </div>

            <div class="accommodation-card__left-panels" data-expanded-only>
                <div class="accommodation-card__panel">
                    <div class="accommodation-card__panel-title">Details</div>
                    <ul class="accommodation-card__bullet-list">
                        <li>Floor(s): <span class="font-medium">{{ $accommodation['floors'] ?? 'EG' }}</span></li>
                        <li>Built/Renovated: <span class="font-medium">{{ $accommodation['year_or_renovated'] ?? 'Renovated 2023' }}</span></li>
                        <li>Living room: <span class="font-medium">{{ isset($accommodation['living_room']) ? ($accommodation['living_room'] ? 'Yes' : 'No') : 'No data' }}</span></li>
                        <li>Dining room: <span class="font-medium">{{ isset($accommodation['dining_room']) ? ($accommodation['dining_room'] ? 'Yes' : 'No') : 'No data' }}</span></li>
                    </ul>
                </div>

                @if(!empty($accommodation['policies']) || !empty($accommodation['changeover_day']) || !empty($accommodation['minimum_stay_nights']))
                    <div class="accommodation-card__panel">
                        <div class="accommodation-card__panel-title">Policies</div>
                        @if(!empty($accommodation['policies']))
                            <ul class="accommodation-card__bullet-list">
                                @if(isset($accommodation['policies']['pets_allowed']))
                                    <li>{{ $accommodation['policies']['pets_allowed'] ? '✅' : '❌' }} Pets allowed</li>
                                @endif
                                @if(isset($accommodation['policies']['smoking_allowed']))
                                    <li>{{ $accommodation['policies']['smoking_allowed'] ? '✅' : '❌' }} Smoking allowed</li>
                                @endif
                                @if(isset($accommodation['policies']['children_allowed']))
                                    <li>{{ $accommodation['policies']['children_allowed'] ? '✅' : '❌' }} Children allowed</li>
                                @endif
                                @if(!empty($accommodation['policies']['quiet_hours']))
                                    <li>🔇 Quiet hours: {{ $accommodation['policies']['quiet_hours'] }}</li>
                                @endif
                            </ul>
                        @endif

                        @if(!empty($accommodation['changeover_day']) || !empty($accommodation['minimum_stay_nights']))
                            <div class="accommodation-card__panel-subtitle">Conditions</div>
                            <ul class="accommodation-card__bullet-list">
                                @if(!empty($accommodation['changeover_day']))
                                    <li>📅 Changeover day: <strong>{{ $accommodation['changeover_day'] }}</strong></li>
                                @endif
                                @if(!empty($accommodation['minimum_stay_nights']))
                                    <li>🌙 Minimum stay: <strong>{{ $accommodation['minimum_stay_nights'] }} nights</strong></li>
                                @endif
                            </ul>
                        @endif
                    </div>
                @endif
            </div>
        </div>

        <div class="accommodation-card__summary">
            <div class="accommodation-card__summary-header">
                <h3 class="accommodation-card__title">{{ $accommodation['title'] ?? 'Apartment Title' }}</h3>
                <div class="accommodation-card__type">{{ $accommodation['accommodation_type'] ?? 'Apartment / Holiday Home' }}</div>
            </div>

            <div class="accommodation-card__stats">
                <span class="accommodation-card__stat">
                    <span class="accommodation-card__stat-icon">👥</span>
                    <span>{{ $stats['occupancy'] ?? 'Keine Angabe' }}</span>
                </span>
                <span class="accommodation-card__stat">
                    <span class="accommodation-card__stat-icon">🛁</span>
                    <span>{{ $stats['bathrooms'] ?? 'Keine Angabe' }}</span>
                </span>
                <span class="accommodation-card__stat">
                    <span class="accommodation-card__stat-icon">📐</span>
                    <span>{{ $stats['living_area'] ?? 'Keine Angabe' }}</span>
                </span>
            </div>

            <div class="accommodation-card__beds">
                <span class="accommodation-card__beds-label">Schlafzimmer:</span>
                <span class="accommodation-card__beds-value">{{ $bedSummary }}</span>
            </div>

            <div class="accommodation-card__distance-row">
                <div class="accommodation-card__distance-group">
                    @if(!empty($accommodation['distances']['to_water_m']))
                        <span class="accommodation-card__distance-chip">
                            🌊 Water: <span>{{ is_numeric($accommodation['distances']['to_water_m']) ? $accommodation['distances']['to_water_m'] . ' m' : $accommodation['distances']['to_water_m'] }}</span>
                        </span>
                    @endif
                    @if(!empty($accommodation['distances']['to_berth_m']))
                        <span class="accommodation-card__distance-chip">
                            ⚓ Jetty: <span>{{ is_numeric($accommodation['distances']['to_berth_m']) ? $accommodation['distances']['to_berth_m'] . ' m' : $accommodation['distances']['to_berth_m'] }}</span>
                        </span>
                    @endif
                    @if(!empty($accommodation['distances']['to_parking_m']))
                        <span class="accommodation-card__distance-chip">
                            🚗 Parking: <span>{{ is_numeric($accommodation['distances']['to_parking_m']) ? $accommodation['distances']['to_parking_m'] . ' m' : $accommodation['distances']['to_parking_m'] }}</span>
                        </span>
                    @endif
                </div>
            </div>

        </div>

        <div class="accommodation-card__feature-grid" data-expanded-only>
            <div class="accommodation-card__panel">
                <div class="accommodation-card__panel-title">Amenities</div>
                <ul class="accommodation-card__chip-list">
                    @if(isset($accommodation['amenities']) && is_array($accommodation['amenities']) && count($accommodation['amenities']) > 0)
                        @foreach($accommodation['amenities'] as $amenity)
                            <li class="accommodation-card__chip">{{ is_array($amenity) ? ($amenity['value'] ?? $amenity['name'] ?? '') : $amenity }}</li>
                        @endforeach
                    @else
                        <li class="accommodation-card__chip">WiFi</li>
                        <li class="accommodation-card__chip">Fishing room</li>
                        <li class="accommodation-card__chip">Filleting station</li>
                        <li class="accommodation-card__chip">Fish freezer</li>
                        <li class="accommodation-card__chip">BBQ Area</li>
                        <li class="accommodation-card__chip">Parking spaces</li>
                        <li class="accommodation-card__chip">TV</li>
                        <li class="accommodation-card__chip">Terrace</li>
                        <li class="accommodation-card__chip">Keybox</li>
                        <li class="accommodation-card__chip">Heating</li>
                    @endif
                </ul>
            </div>

            <div class="accommodation-card__panel">
                <div class="accommodation-card__panel-title">Kitchen Equipment</div>
                @if(!empty($accommodation['kitchen']))
                    <ul class="accommodation-card__bullet-list">
                        @if(!empty($accommodation['kitchen']['refrigerator_freezer']))
                            <li>✅ Refrigerator/Freezer</li>
                        @endif
                        @if(!empty($accommodation['kitchen']['oven']))
                            <li>✅ Oven</li>
                        @endif
                        @if(!empty($accommodation['kitchen']['stove']))
                            <li>✅ Stove</li>
                        @endif
                        @if(!empty($accommodation['kitchen']['microwave']))
                            <li>✅ Microwave</li>
                        @endif
                        @if(!empty($accommodation['kitchen']['dishwasher']))
                            <li>✅ Dishwasher</li>
                        @endif
                        @if(!empty($accommodation['kitchen']['coffee_machine']))
                            <li>☕ Coffee machine: {{ $accommodation['kitchen']['coffee_machine'] }}</li>
                        @endif
                        @if(!empty($accommodation['kitchen']['kettle']))
                            <li>✅ Kettle</li>
                        @endif
                        @if(!empty($accommodation['kitchen']['toaster']))
                            <li>✅ Toaster</li>
                        @endif
                    </ul>
                @else
                    <p class="accommodation-card__empty">No kitchen details available</p>
                @endif
            </div>

            <div class="accommodation-card__panel">
                <div class="accommodation-card__panel-title">Bathroom Equipment</div>
                @if(!empty($accommodation['bathroom_laundry']))
                    <ul class="accommodation-card__bullet-list">
                        @if(!empty($accommodation['bathroom_laundry']['toilet']))
                            <li>🚽 Toilet: {{ $accommodation['bathroom_laundry']['toilet'] }}</li>
                        @endif
                        @if(!empty($accommodation['bathroom_laundry']['shower']))
                            <li>🚿 Shower: {{ $accommodation['bathroom_laundry']['shower'] }}</li>
                        @endif
                        @if(!empty($accommodation['bathroom_laundry']['washbasin']))
                            <li>🚰 Washbasin: {{ $accommodation['bathroom_laundry']['washbasin'] }}</li>
                        @endif
                        @if(!empty($accommodation['bathroom_laundry']['washing_machine']))
                            <li>✅ Washing machine</li>
                        @endif
                        @if(!empty($accommodation['bathroom_laundry']['dryer']))
                            <li>✅ Dryer</li>
                        @endif
                        @if(!empty($accommodation['bathroom_laundry']['iron_board']))
                            <li>✅ Iron & board</li>
                        @endif
                    </ul>
                @else
                    <p class="accommodation-card__empty">No bathroom details available</p>
                @endif
            </div>

            @if(!empty($accommodation['extras_inclusives']['inclusives']) || !empty($accommodation['extras_inclusives']['extras']))
                <div class="accommodation-card__panel accommodation-card__panel--extras">
                    <div class="accommodation-card__panel-columns">
                        @if(!empty($accommodation['extras_inclusives']['inclusives']))
                            <div>
                                <div class="accommodation-card__panel-title">Inclusives</div>
                                <div class="accommodation-card__inclusive-extras">
                                    @foreach($accommodation['extras_inclusives']['inclusives'] as $inclusive)
                                        <span class="accommodation-card__inclusive-chip">✅ {{ is_array($inclusive) ? ($inclusive['name'] ?? $inclusive['value'] ?? json_encode($inclusive)) : $inclusive }}</span>
                                    @endforeach
                                </div>
                            </div>
                        @endif

                        @if(!empty($accommodation['extras_inclusives']['extras']))
                            <div>
                                <div class="accommodation-card__panel-title">Extras</div>
                                <div class="accommodation-card__inclusive-extras">
                                    @foreach($accommodation['extras_inclusives']['extras'] as $extra)
                                        <span class="accommodation-card__inclusive-chip">✅ {{ is_array($extra) ? ($extra['name'] ?? $extra['value'] ?? json_encode($extra)) : $extra }}</span>
                                    @endforeach
                                </div>
                            </div>
                        @endif
                    </div>
                </div>
            @endif
        </div>

        <div class="accommodation-card__actions">
            <div class="accommodation-card__actions-column">
                <div class="accommodation-card__pricing">
                    <div class="accommodation-card__price-type">per night</div>
                    <div class="accommodation-card__price-amount">€{{ number_format($accommodation['price']['amount'] ?? 110, 2) }}</div>
                </div>
                <button class="accommodation-card__select-btn">
                    Select Accommodation
                </button>
                <button class="accommodation-card__expand-btn accommodation-card__expand-btn--secondary" data-toggle-btn>
                    <span data-toggle-text>Show More</span>
                    <span data-toggle-icon>▼</span>
                </button>
            </div>
        </div>
    </div>
</div>

@once
<script src="{{ asset('js/accommodation-card.js') }}"></script>
@endonce
