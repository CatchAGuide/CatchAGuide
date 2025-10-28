<div class="accommodation-card" data-accommodation-card>
    <div class="accommodation-card__grid">
        <!-- Left Column: Gallery (+ Details + Inclusives/Extras when expanded) -->
        <div class="accommodation-card__left-column">
            <!-- Gallery -->
            <div class="accommodation-gallery" data-gallery-images='@json($accommodation["gallery_images"] ?? [])'>
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
                    <div class="accommodation-gallery__counter" data-image-counter>1/{{ count($accommodation['gallery_images'] ?? []) }}</div>
                </div>

                <div class="accommodation-gallery__info-chips" data-expanded-only>
                    <span class="accommodation-gallery__info-chip">
                        👥 <span>{{ $accommodation['max_occupancy'] ?? '4' }}</span> Pers
                    </span>
                    <span class="accommodation-gallery__info-chip">
                        🛏️ <span>{{ $accommodation['number_of_bedrooms'] ?? '2' }}</span> BR
                    </span>
                    <span class="accommodation-gallery__info-chip">
                        🛁 <span>{{ $accommodation['bathrooms'] ?? '1' }}</span> Bath
                    </span>
                    <span class="accommodation-gallery__info-chip">
                        📐 <span>{{ $accommodation['living_area_sqm'] ?? '80' }}</span> m²
                    </span>
                </div>
            </div>

            <!-- Details Section (only visible when expanded) -->
            <div class="accommodation-card__detail-box" data-expanded-only>
                <div class="accommodation-card__detail-box-title">Details</div>
                <ul class="accommodation-card__detail-list">
                    <li>Floor(s): <span class="font-medium">{{ $accommodation['floors'] ?? 'EG' }}</span></li>
                    <li>Built/Renovated: <span class="font-medium">{{ $accommodation['year_or_renovated'] ?? 'Renovated 2023' }}</span></li>
                    <li>Living room: <span class="font-medium">{{ $accommodation['living_room'] ? 'Yes' : 'No' }}</span></li>
                    <li>Dining room: <span class="font-medium">{{ $accommodation['dining_room'] ? 'Yes' : 'No' }}</span></li>
                </ul>
            </div>

            <!-- Inclusives & Extras (only visible when expanded) -->
            <div class="accommodation-card__detail-box" data-expanded-only>
                <div class="accommodation-card__detail-box-title">Included & Extras</div>
                
                @if(!empty($accommodation['extras_inclusives']['inclusives']))
                <div>
                    <div class="accommodation-card__detail-box-title" style="font-size: 11px; margin-bottom: 2px;">Included</div>
                    <div class="accommodation-card__inclusive-extras">
                        @foreach($accommodation['extras_inclusives']['inclusives'] as $inclusive)
                            <span class="accommodation-card__inclusive-chip">✅ {{ is_array($inclusive) ? ($inclusive['name'] ?? $inclusive['value'] ?? json_encode($inclusive)) : $inclusive }}</span>
                        @endforeach
                    </div>
                </div>
                @endif

                @if(!empty($accommodation['extras_inclusives']['extras']))
                <div style="margin-top: 8px;">
                    <div class="accommodation-card__detail-box-title" style="font-size: 11px; margin-bottom: 2px;">Extras</div>
                    <div class="accommodation-card__inclusive-extras">
                        @foreach($accommodation['extras_inclusives']['extras'] as $extra)
                            <span class="accommodation-card__inclusive-chip">✅ {{ is_array($extra) ? ($extra['name'] ?? $extra['value'] ?? json_encode($extra)) : $extra }}</span>
                        @endforeach
                    </div>
                </div>
                @endif
            </div>
        </div>

        <!-- Right Column: Header, Price, Chips (+ Matrix, Amenities when expanded) -->
        <div class="accommodation-card__right-column">
            <!-- Header & Pricing -->
            <div class="accommodation-card__header">
                <div class="accommodation-card__info">
                    <h3 class="accommodation-card__title">{{ $accommodation['title'] ?? 'Apartment Title' }}</h3>
                    <div class="accommodation-card__type">{{ $accommodation['accommodation_type'] ?? 'Apartment / Holiday Home' }}</div>
                    <p class="accommodation-card__description">{{ $accommodation['description'] ?? 'Description' }}</p>
                </div>
                <div class="accommodation-card__pricing">
                    <div class="accommodation-card__price-type">per night</div>
                    <div class="accommodation-card__price-amount">€{{ number_format($accommodation['price']['amount'] ?? 110, 2) }}</div>
                    <button class="accommodation-card__select-btn">
                        Select Accommodation
                    </button>
                </div>
            </div>

            <!-- Distance Chips and Expand Button -->
            <div class="accommodation-card__distance-chips">
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
                
                <!-- Expand/Collapse Button (inline with chips) -->
                <button class="accommodation-card__expand-btn" data-toggle-btn>
                    <span data-toggle-text>Show More</span>
                    <span data-toggle-icon>▼</span>
                </button>
            </div>

            <!-- Info Matrix: 3 boxes top, 4th bottom full-width (only visible when expanded) -->
            <div class="accommodation-card__info-matrix" data-expanded-only>
                <!-- Box 1: Beds & Location -->
                <div class="accommodation-card__info-box">
                    <div class="accommodation-card__info-box-title">Beds & Location</div>
                    <div class="accommodation-card__info-box-content">
                        @if(!empty($accommodation['bed_config']))
                            @php
                                $bedTypes = [];
                                if (!empty($accommodation['bed_config']['single'])) $bedTypes[] = $accommodation['bed_config']['single'] . '× Single';
                                if (!empty($accommodation['bed_config']['double'])) $bedTypes[] = $accommodation['bed_config']['double'] . '× Double';
                                if (!empty($accommodation['bed_config']['bunk'])) $bedTypes[] = $accommodation['bed_config']['bunk'] . '× Bunk';
                                if (!empty($accommodation['bed_config']['sofabed'])) $bedTypes[] = $accommodation['bed_config']['sofabed'] . '× Sofa bed';
                                if (!empty($accommodation['bed_config']['child'])) $bedTypes[] = $accommodation['bed_config']['child'] . '× Child bed';
                                if (!empty($accommodation['bed_config']['folding'])) $bedTypes[] = $accommodation['bed_config']['folding'] . '× Folding bed';
                            @endphp
                            <div class="font-medium">{{ implode(' • ', $bedTypes) ?: 'Bed configuration not specified' }}</div>
                        @else
                            <div class="font-medium">Bed configuration not specified</div>
                        @endif
                        @if(!empty($accommodation['location_description']))
                            <div style="margin-top: 8px;">
                                <div class="accommodation-card__info-box-title">Location</div>
                                <p style="color: #334155; line-height: 1.4;">{{ $accommodation['location_description'] }}</p>
                            </div>
                        @endif
                    </div>
                </div>

                <!-- Box 2: Bath/Laundry -->
                <div class="accommodation-card__info-box">
                    <div class="accommodation-card__info-box-title">Bath/Laundry</div>
                    <div class="accommodation-card__info-box-content">
                        @if(!empty($accommodation['bathroom_laundry']))
                            <ul style="list-style: none; padding: 0; margin: 0; font-size: 13px; line-height: 1.8;">
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
                            <p style="color: #64748b; font-size: 13px;">No bathroom details available</p>
                        @endif
                    </div>
                </div>

                <!-- Box 3: Kitchen -->
                <div class="accommodation-card__info-box">
                    <div class="accommodation-card__info-box-title">Kitchen</div>
                    <div class="accommodation-card__info-box-content">
                        @if(!empty($accommodation['kitchen']))
                            <ul style="list-style: none; padding: 0; margin: 0; font-size: 13px; line-height: 1.8;">
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
                            <p style="color: #64748b; font-size: 13px;">No kitchen details available</p>
                        @endif
                    </div>
                </div>

                <!-- Box 4: Policies & Conditions (full width) -->
                <div class="accommodation-card__info-box">
                    @if(!empty($accommodation['policies']))
                        <div class="accommodation-card__info-box-title">Policies</div>
                        <div class="accommodation-card__info-box-content">
                            <ul style="list-style: none; padding: 0; margin: 0; font-size: 13px; line-height: 1.8;">
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
                        </div>
                    @endif
                    
                    @if(!empty($accommodation['changeover_day']) || !empty($accommodation['minimum_stay_nights']))
                        <div class="accommodation-card__info-box-title" style="margin-top: 8px;">Conditions</div>
                        <div class="accommodation-card__info-box-content">
                            <ul style="list-style: none; padding: 0; margin: 0; font-size: 13px; line-height: 1.8;">
                                @if(!empty($accommodation['changeover_day']))
                                    <li>📅 Changeover day: <strong>{{ $accommodation['changeover_day'] }}</strong></li>
                                @endif
                                @if(!empty($accommodation['minimum_stay_nights']))
                                    <li>🌙 Minimum stay: <strong>{{ $accommodation['minimum_stay_nights'] }} nights</strong></li>
                                @endif
                            </ul>
                        </div>
                    @endif
                </div>
            </div>

            <!-- Amenities (only visible when expanded) -->
            <div class="accommodation-card__amenities-section" data-expanded-only>
                <div class="accommodation-card__amenities-title">Amenities</div>
                <div class="accommodation-card__amenities-chips">
                    @if(isset($accommodation['amenities']) && is_array($accommodation['amenities']) && count($accommodation['amenities']) > 0)
                        @foreach($accommodation['amenities'] as $amenity)
                            <span class="accommodation-card__amenity-chip">{{ is_array($amenity) ? ($amenity['value'] ?? $amenity['name'] ?? '') : $amenity }}</span>
                        @endforeach
                    @else
                        <span class="accommodation-card__amenity-chip">WiFi</span>
                        <span class="accommodation-card__amenity-chip">Fishing room</span>
                        <span class="accommodation-card__amenity-chip">Filleting station</span>
                        <span class="accommodation-card__amenity-chip">Fish freezer</span>
                        <span class="accommodation-card__amenity-chip">BBQ Area</span>
                        <span class="accommodation-card__amenity-chip">Parking spaces</span>
                        <span class="accommodation-card__amenity-chip">TV</span>
                        <span class="accommodation-card__amenity-chip">Terrace</span>
                        <span class="accommodation-card__amenity-chip">Keybox</span>
                        <span class="accommodation-card__amenity-chip">Heating</span>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>

@once
<script src="{{ asset('js/accommodation-card.js') }}"></script>
@endonce
