<div class="rental-boat-card" data-rental-boat-card>
    <div class="rental-boat-card__grid">
        <!-- Left Column: Gallery (+ Inclusives & Extras when expanded) -->
        <div class="rental-boat-card__left-column">
            <!-- Gallery -->
            @php
                $boatGalleryImages = $boat['gallery_images'] ?? [];
                $boatGalleryCount = is_array($boatGalleryImages) ? count($boatGalleryImages) : 0;
            @endphp
            <div class="rental-boat-card__gallery" data-gallery-images='@json($boatGalleryImages)'>
                <img src="{{ $boat['thumbnail_path'] ?? 'https://images.unsplash.com/photo-1520440229-84f3865cf003?q=80&w=1600&auto=format&fit=crop' }}" alt="{{ $boat['title'] ?? 'Boat' }}" data-gallery-image />
                
                @if($boatGalleryCount > 0)
                <div>
                    <button 
                        type="button"
                        aria-label="Previous image" 
                        class="rental-boat-gallery__nav-btn rental-boat-gallery__nav-btn--prev"
                        data-prev-image
                    >
                        ‹
                    </button>
                    <button 
                        type="button"
                        aria-label="Next image" 
                        class="rental-boat-gallery__nav-btn rental-boat-gallery__nav-btn--next"
                        data-next-image
                    >
                        ›
                    </button>
                    <div class="rental-boat-gallery__counter" data-image-counter>1/{{ $boatGalleryCount }}</div>
                </div>
                @endif

                <!-- Info chips on gallery (visible on expand) -->
                <div class="rental-boat-gallery__info-chips" data-expanded-only>
                    <span class="rental-boat-gallery__info-chip">
                        👥 <span>{{ $boat['seats'] ?? '1' }}</span> Sitze
                    </span>
                    <span class="rental-boat-gallery__info-chip">
                        📏 <span>{{ $boat['length_m'] ?? '3.6' }}</span> m
                    </span>
                    <span class="rental-boat-gallery__info-chip">
                        ↔️ <span>{{ $boat['width_m'] ?? '0.9' }}</span> m
                    </span>
                </div>
            </div>
            
            <!-- Inclusives & Extras Section (only visible when expanded) -->
            <div class="rental-boat-card__detail-box" data-expanded-only>
                <div class="rental-boat-card__detail-box-title">Inklusive & Extras</div>
                
                <div style="margin-bottom: 12px;">
                    <div class="rental-boat-card__detail-box-subtitle">Inklusive</div>
                    <div class="rental-boat-card__inclusives-chips">
                        @if(!empty($boat['inclusives']) && is_array($boat['inclusives']) && count($boat['inclusives']) > 0)
                            @foreach($boat['inclusives'] as $inclusive)
                                <span class="rental-boat-card__inclusive-chip">
                                    <svg class="rental-boat-card__check-icon" width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3">
                                        <polyline points="20 6 9 17 4 12"/>
                                    </svg>
                                    <span>{{ is_array($inclusive) ? ($inclusive['name'] ?? ($inclusive['value'] ?? json_encode($inclusive))) : $inclusive }}</span>
                                </span>
                            @endforeach
                        @else
                            <span class="rental-boat-card__inclusive-chip">
                                <svg class="rental-boat-card__check-icon" width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3">
                                    <polyline points="20 6 9 17 4 12"/>
                                </svg>
                                <span>Sicherheitsunterweisung</span>
                            </span>
                            <span class="rental-boat-card__inclusive-chip">
                                <svg class="rental-boat-card__check-icon" width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3">
                                    <polyline points="20 6 9 17 4 12"/>
                                </svg>
                                <span>Anker</span>
                            </span>
                            <span class="rental-boat-card__inclusive-chip">
                                <svg class="rental-boat-card__check-icon" width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3">
                                    <polyline points="20 6 9 17 4 12"/>
                                </svg>
                                <span>Signalhorn</span>
                            </span>
                            <span class="rental-boat-card__inclusive-chip">
                                <svg class="rental-boat-card__check-icon" width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3">
                                    <polyline points="20 6 9 17 4 12"/>
                                </svg>
                                <span>Erste Hilfe Set</span>
                            </span>
                            <span class="rental-boat-card__inclusive-chip">
                                <svg class="rental-boat-card__check-icon" width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3">
                                    <polyline points="20 6 9 17 4 12"/>
                                </svg>
                                <span>Schwimmweste</span>
                            </span>
                        @endif
                    </div>
                </div>
                
                @if(!empty($boat['extras']) && is_array($boat['extras']) && count($boat['extras']) > 0)
                <div>
                    <div class="rental-boat-card__detail-box-subtitle">Extras</div>
                    <div class="rental-boat-card__inclusives-chips">
                        @foreach($boat['extras'] as $extra)
                            <span class="rental-boat-card__inclusive-chip">
                                <svg class="rental-boat-card__check-icon" width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3">
                                    <polyline points="20 6 9 17 4 12"/>
                                </svg>
                                <span>{{ is_array($extra) ? ($extra['name'] ?? ($extra['value'] ?? json_encode($extra))) : $extra }}</span>
                            </span>
                        @endforeach
                    </div>
                </div>
                @else
                <div>
                    <div class="rental-boat-card__detail-box-subtitle">Extras</div>
                    <div class="rental-boat-card__inclusives-chips">
                        <span class="rental-boat-card__inclusive-chip">
                            <svg class="rental-boat-card__check-icon" width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3">
                                <polyline points="20 6 9 17 4 12"/>
                            </svg>
                            <span>Dry Bag</span>
                        </span>
                    </div>
                </div>
                @endif
            </div>
        </div>

        <!-- Right Column: Content -->
        <div class="rental-boat-card__right-column">
            <!-- Header & Pricing -->
            <div class="rental-boat-card__header">
                <div class="rental-boat-card__info">
                    <h3 class="rental-boat-card__title">{{ $boat['title'] ?? 'Boat Title' }}</h3>
                    <div class="rental-boat-card__type">{{ $boat['type'] ?? 'Boat Type' }}</div>
                    <p class="rental-boat-card__description">{{ $boat['description'] ?? 'Description' }}</p>
                </div>
                <div class="rental-boat-card__pricing">
                    @php
                        $price = $boat['price'] ?? [];
                        $priceType = is_array($price) && isset($price['type']) ? $price['type'] : 'per_day';
                        $priceAmount = is_array($price) && isset($price['amount']) ? (float)$price['amount'] : 25;
                        $priceTypeMap = [
                            'per_day' => 'pro Tag',
                            'per_hour' => 'pro Stunde',
                            'per_week' => 'pro Woche',
                        ];
                        $displayPriceType = $priceTypeMap[$priceType] ?? $priceType;
                    @endphp
                    <div class="rental-boat-card__price-type">{{ $displayPriceType }}</div>
                    <div class="rental-boat-card__price-amount">€{{ number_format($priceAmount, 2) }}</div>
                    <button class="rental-boat-card__select-btn">
                        Dieses Boot übernehmen
                    </button>
                </div>
            </div>

            <!-- Information Pills -->
            <div class="rental-boat-card__info-pills">
                <span class="rental-boat-card__info-pill">
                    <svg class="rental-boat-card__pill-icon" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/>
                        <circle cx="12" cy="7" r="4"/>
                    </svg>
                    <span>{{ $boat['seats'] ?? '1' }}</span> Sitze
                </span>
                <span class="rental-boat-card__info-pill">
                    <svg class="rental-boat-card__pill-icon" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <line x1="5" y1="12" x2="19" y2="12"/>
                        <polyline points="12 5 19 12 12 19"/>
                    </svg>
                    <span>{{ $boat['length_m'] ?? '3.6' }}</span> m
                </span>
                <span class="rental-boat-card__info-pill">
                    <svg class="rental-boat-card__pill-icon" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <line x1="3" y1="12" x2="21" y2="12"/>
                        <line x1="3" y1="6" x2="21" y2="6"/>
                        <line x1="3" y1="18" x2="21" y2="18"/>
                    </svg>
                    <span>{{ $boat['width_m'] ?? '0.9' }}</span> m breit
                </span>
                
                <!-- Expand/Collapse Button (inline with pills) -->
                <button class="rental-boat-card__expand-btn" data-toggle-btn>
                    <span data-toggle-text>Show More</span>
                    <span data-toggle-icon>▼</span>
                </button>
            </div>

            <!-- Information Panels (only visible when expanded) -->
            <div class="rental-boat-card__info-matrix" data-expanded-only>
                <!-- Panel 1: Boat Information -->
                <div class="rental-boat-card__info-box">
                    <div class="rental-boat-card__info-box-title">Bootsinformationen</div>
                    <div class="rental-boat-card__info-box-content">
                        @if(!empty($boat['boat_info']))
                            <ul style="list-style: none; padding: 0; margin: 0; font-size: 13px; line-height: 1.8;">
                                @if(!empty($boat['boat_info']['seats']))
                                    <li>Sitzplätze: <strong>{{ $boat['boat_info']['seats'] }}</strong></li>
                                @endif
                                @if(!empty($boat['boat_info']['length_m']))
                                    <li>Länge: <strong>{{ $boat['boat_info']['length_m'] }} m</strong></li>
                                @endif
                                @if(!empty($boat['boat_info']['width_m']))
                                    <li>Breite: <strong>{{ $boat['boat_info']['width_m'] }} m</strong></li>
                                @endif
                                @if(!empty($boat['boat_info']['year_built']))
                                    <li>Baujahr: <strong>{{ $boat['boat_info']['year_built'] }}</strong></li>
                                @endif
                                @if(!empty($boat['boat_info']['manufacturer']))
                                    <li>Boot: <strong>{{ $boat['boat_info']['manufacturer'] }}</strong></li>
                                @endif
                                @if(!empty($boat['boat_info']['engine']))
                                    <li>Motor: <strong>{{ $boat['boat_info']['engine'] }}</strong></li>
                                @endif
                                @if(!empty($boat['boat_info']['power']))
                                    <li>Leistung: <strong>{{ $boat['boat_info']['power'] }}</strong></li>
                                @endif
                                @if(!empty($boat['boat_info']['max_speed_kmh']))
                                    <li>Vmax: <strong>{{ $boat['boat_info']['max_speed_kmh'] }} km/h</strong></li>
                                @endif
                            </ul>
                        @else
                            <ul style="list-style: none; padding: 0; margin: 0; font-size: 13px; line-height: 1.8;">
                                <li>Sitzplätze: <strong>{{ $boat['seats'] ?? '1' }}</strong></li>
                                <li>Länge: <strong>{{ $boat['length_m'] ?? '3.6' }} m</strong></li>
                                <li>Breite: <strong>{{ $boat['width_m'] ?? '0.9' }} m</strong></li>
                                <li>Baujahr: <strong>{{ $boat['year_built'] ?? '2022' }}</strong></li>
                                <li>Boot: <strong>{{ $boat['manufacturer'] ?? 'Hobie' }}</strong></li>
                                <li>Motor: <strong>{{ $boat['engine'] ?? '-' }}</strong></li>
                                <li>Leistung: <strong>{{ $boat['power'] ?? '-' }}</strong></li>
                                <li>Vmax: <strong>{{ $boat['max_speed_kmh'] ?? '8' }} km/h</strong></li>
                            </ul>
                        @endif
                    </div>
                </div>

                <!-- Panel 2: Equipment -->
                <div class="rental-boat-card__info-box">
                    <div class="rental-boat-card__info-box-title">Ausstattung</div>
                    <div class="rental-boat-card__info-box-content">
                        @if(!empty($boat['equipment']) && is_array($boat['equipment']) && count($boat['equipment']) > 0)
                            <div class="rental-boat-card__equipment-chips">
                                @foreach($boat['equipment'] as $equipment)
                                    <span class="rental-boat-card__equipment-chip">
                                        <svg class="rental-boat-card__chip-icon" width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                            <circle cx="12" cy="12" r="2"/>
                                            <path d="M12 1v6m0 6v6m-6-6h6m6 0h-6"/>
                                        </svg>
                                        <span>{{ is_array($equipment) ? ($equipment['name'] ?? ($equipment['value'] ?? json_encode($equipment))) : $equipment }}</span>
                                    </span>
                                @endforeach
                            </div>
                        @else
                            <div class="rental-boat-card__equipment-chips">
                                <span class="rental-boat-card__equipment-chip">
                                    <svg class="rental-boat-card__chip-icon" width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                        <circle cx="12" cy="12" r="2"/>
                                        <path d="M12 1v6m0 6v6m-6-6h6m6 0h-6"/>
                                    </svg>
                                    Rutenhalter
                                </span>
                                <span class="rental-boat-card__equipment-chip">
                                    <svg class="rental-boat-card__chip-icon" width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                        <path d="M12 2v20m0-4c-2 0-4-2-4-4s2-4 4-4m0 8c2 0 4-2 4-4s-2-4-4-4"/>
                                    </svg>
                                    Anker
                                </span>
                                <span class="rental-boat-card__equipment-chip">
                                    <svg class="rental-boat-card__chip-icon" width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                        <path d="M12 2L2 7l10 5 10-5-10-5z"/>
                                        <path d="M2 17l10 5 10-5M2 12l10 5 10-5"/>
                                    </svg>
                                    Signalhorn
                                </span>
                                <span class="rental-boat-card__equipment-chip">
                                    <svg class="rental-boat-card__chip-icon" width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                        <path d="M12 2L2 7l10 5 10-5-10-5z"/>
                                        <polyline points="12 22 12 12"/>
                                    </svg>
                                    Erste Hilfe
                                </span>
                                <span class="rental-boat-card__equipment-chip">
                                    <svg class="rental-boat-card__chip-icon" width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                        <circle cx="12" cy="12" r="10"/>
                                    </svg>
                                    Schwimmwesten
                                </span>
                                <span class="rental-boat-card__equipment-chip">
                                    <svg class="rental-boat-card__chip-icon" width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                        <line x1="12" y1="2" x2="12" y2="22"/>
                                        <polyline points="5 9 12 2 19 9"/>
                                    </svg>
                                    Ruder
                                </span>
                            </div>
                        @endif
                    </div>
                </div>

                <!-- Panel 3: Requirements -->
                <div class="rental-boat-card__info-box">
                    <div class="rental-boat-card__info-box-title">Anforderungen</div>
                    <div class="rental-boat-card__info-box-content">
                        @if(!empty($boat['requirements']) && is_array($boat['requirements']) && count($boat['requirements']) > 0)
                            <div class="rental-boat-card__requirements-chips">
                                @foreach($boat['requirements'] as $requirement)
                                    <span class="rental-boat-card__requirement-chip">{{ is_array($requirement) ? ($requirement['name'] ?? ($requirement['value'] ?? json_encode($requirement))) : $requirement }}</span>
                                @endforeach
                            </div>
                        @else
                            <div class="rental-boat-card__requirements-chips">
                                <span class="rental-boat-card__requirement-chip">Führerschein nicht nötig</span>
                                <span class="rental-boat-card__requirement-chip">Mindestalter 14</span>
                                <span class="rental-boat-card__requirement-chip">Ausweis mitbringen</span>
                                <span class="rental-boat-card__requirement-chip">Kaution nein</span>
                                <span class="rental-boat-card__requirement-chip">Sicherheitsunterweisung</span>
                                <span class="rental-boat-card__requirement-chip">Schwimmwestenpflicht</span>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@once
<script src="{{ asset('js/rental-boat-card.js') }}"></script>
@endonce
