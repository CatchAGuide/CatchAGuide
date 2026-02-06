<div class="guiding-card" id="guiding-{{ $guiding['id'] ?? '' }}" data-guiding-card data-guiding-id="{{ $guiding['id'] ?? '' }}">
    <div class="guiding-card__grid">
        <!-- Left Column: Gallery (+ Inclusives when expanded) -->
        <div class="guiding-card__left-column">
            <!-- Gallery -->
            @php
                $guidingGalleryImages = $guiding['gallery_images'] ?? [];
                $guidingGalleryCount = is_array($guidingGalleryImages) ? count($guidingGalleryImages) : 0;
            @endphp
            <div class="guiding-card__gallery" data-gallery-images='@json($guidingGalleryImages)'>
                <img src="{{ $guiding['thumbnail_path'] ?? 'https://images.unsplash.com/photo-1474843148229-3163319fcc00?q=80&w=1600&auto=format&fit=crop' }}" alt="{{ $guiding['title'] ?? 'Guiding' }}" data-gallery-image />
                
                @if($guidingGalleryCount > 0)
                <div>
                    <button 
                        type="button"
                        aria-label="Previous image" 
                        class="guiding-gallery__nav-btn guiding-gallery__nav-btn--prev"
                        data-prev-image
                    >
                        ‹
                    </button>
                    <button 
                        type="button"
                        aria-label="Next image" 
                        class="guiding-gallery__nav-btn guiding-gallery__nav-btn--next"
                        data-next-image
                    >
                        ›
                    </button>
                    <div class="guiding-gallery__counter" data-image-counter>1/{{ $guidingGalleryCount }}</div>
                </div>
                @endif

                <!-- Info chips on gallery (visible on expand) -->
                <div class="guiding-gallery__info-chips" data-expanded-only>
                    <span class="guiding-gallery__info-chip">
                        ⏱️ <span>{{ $guiding['duration_hours'] ?? '6' }}</span>h
                    </span>
                    <span class="guiding-gallery__info-chip">
                        👥 <span>{{ $guiding['max_persons'] ?? '3' }}</span> Pers
                    </span>
                    <span class="guiding-gallery__info-chip">
                        🎣 <span>{{ $guiding['type'] ?? 'Shore' }}</span>
                    </span>
                </div>
            </div>

            {{-- Title and Summary right after gallery - Mobile version --}}
            <div class="guiding-card__title-after-gallery guiding-card__title-after-gallery--mobile">
                <div class="guiding-card__header">
                    <div class="guiding-card__info">
                        <h3 class="guiding-card__title">{{ translate($guiding['title']) ?? 'Guiding Title' }}</h3>
                        <p class="guiding-card__description">{{ translate($guiding['description']) ?? 'Description' }}</p>
                    </div>
                </div>

                <!-- Information Pills -->
                <div class="guiding-card__info-pills">
                    <span class="guiding-card__info-pill">
                        <svg class="guiding-card__pill-icon" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <circle cx="12" cy="12" r="10"/>
                            <polyline points="12 6 12 12 16 14"/>
                        </svg>
                        <span>{{ $guiding['duration_hours'] ?? '6' }}</span> h
                    </span>
                    <span class="guiding-card__info-pill">
                        <svg class="guiding-card__pill-icon" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/>
                            <circle cx="12" cy="7" r="4"/>
                        </svg>
                        <span>{{ $guiding['max_persons'] ?? '3' }}</span> Pers
                    </span>
                    <span class="guiding-card__info-pill">
                        <svg class="guiding-card__pill-icon" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M2 6s1.5-2 5-2 5 2 5 2 1.5-2 5-2 5 2 5 2v14s-1.5-2-5-2-5 2-5 2-1.5-2-5-2-5 2-5 2V6z"/>
                        </svg>
                        <span>{{ $guiding['type'] ?? 'Shore' }}</span>
                    </span>
                </div>
            </div>
            
            <!-- Inclusives Section (only visible when expanded) -->
            <div class="guiding-card__detail-box" data-expanded-only>
                <div class="guiding-card__detail-box-title">Included in This Tour</div>
                <div class="guiding-card__inclusives-chips">
                    @if(!empty($guiding['inclusives']) && is_array($guiding['inclusives']) && count($guiding['inclusives']) > 0)
                        @foreach($guiding['inclusives'] as $inclusive)
                            <span class="guiding-card__inclusive-chip">
                                <svg class="guiding-card__check-icon" width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3">
                                    <polyline points="20 6 9 17 4 12"/>
                                </svg>
                                <span>{{ translate($inclusive['name']) ?? $inclusive }}</span>
                            </span>
                        @endforeach
                    @else
                        <span class="guiding-card__inclusive-chip">
                            <svg class="guiding-card__check-icon" width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3">
                                <polyline points="20 6 9 17 4 12"/>
                            </svg>
                            <span>Equipment Transfer</span>
                        </span>
                        <span class="guiding-card__inclusive-chip">
                            <svg class="guiding-card__check-icon" width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3">
                                <polyline points="20 6 9 17 4 12"/>
                            </svg>
                            <span>Fishing License</span>
                        </span>
                    @endif
                </div>
            </div>
        </div>

        <!-- Right Column: Content -->
        <div class="guiding-card__right-column">
            <!-- Header & Pricing -->
            <div class="guiding-card__header">
                <div class="guiding-card__info">
                    <h3 class="guiding-card__title">{{ translate($guiding['title']) ?? 'Guiding Title' }}</h3>
                    <p class="guiding-card__description">{{ translate($guiding['description']) ?? 'Description' }}</p>
                </div>
                <div class="guiding-card__pricing">
                    <div class="guiding-card__price-type">{{ translate($guiding['price']['type']) ?? 'per tour' }}</div>
                    <div class="guiding-card__price-amount">€{{ number_format($guiding['price']['amount'] ?? 260, 2) }}</div>
                    <button class="guiding-card__select-btn">
                        Select This Guiding
                    </button>
                </div>
            </div>

            <!-- Information Pills -->
            <div class="guiding-card__info-pills">
                <span class="guiding-card__info-pill">
                    <svg class="guiding-card__pill-icon" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <circle cx="12" cy="12" r="10"/>
                        <polyline points="12 6 12 12 16 14"/>
                    </svg>
                    <span>{{ translate($guiding['duration_hours']) ?? '6' }}</span> h
                </span>
                <span class="guiding-card__info-pill">
                    <svg class="guiding-card__pill-icon" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/>
                        <circle cx="12" cy="7" r="4"/>
                    </svg>
                    <span>{{ translate($guiding['max_persons']) ?? '3' }}</span> Pers
                </span>
                <span class="guiding-card__info-pill">
                    <svg class="guiding-card__pill-icon" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M2 6s1.5-2 5-2 5 2 5 2 1.5-2 5-2 5 2 5 2v14s-1.5-2-5-2-5 2-5 2-1.5-2-5-2-5 2-5 2V6z"/>
                    </svg>
                    <span>{{ translate($guiding['type']) ?? 'Shore' }}</span>
                </span>
                
                <!-- Expand/Collapse Button (inline with pills) -->
                <button class="guiding-card__expand-btn" data-toggle-btn>
                    <span data-toggle-text>Show More</span>
                    <span data-toggle-icon>▼</span>
                </button>
            </div>

            <!-- Info Matrix: 3 boxes (only visible when expanded) -->
            <div class="guiding-card__info-matrix" data-expanded-only>
                <!-- Box 1: Guiding Information -->
                <div class="guiding-card__info-box">
                    <div class="guiding-card__info-box-title">Guiding Information</div>
                    <div class="guiding-card__info-box-content">
                        @if(!empty($guiding['guiding_info']))
                            <ul style="list-style: none; padding: 0; margin: 0; font-size: 13px; line-height: 1.8;">
                                @if(!empty($guiding['guiding_info']['art']))
                                    <li>Type: <strong>{{ translate($guiding['guiding_info']['art']) }}</strong></li>
                                @endif
                                @if(!empty($guiding['guiding_info']['dauer']))
                                    <li>Duration: <strong>{{ translate($guiding['guiding_info']['dauer']) }}</strong></li>
                                @endif
                                @if(!empty($guiding['guiding_info']['max_personen']))
                                    <li>Max Persons: <strong>{{ translate($guiding['guiding_info']['max_personen']) }}</strong></li>
                                @endif
                                @if(!empty($guiding['guiding_info']['gewaesser']))
                                    <li>Water: <strong>{{ translate($guiding['guiding_info']['gewaesser']) }}</strong></li>
                                @endif
                            </ul>
                        @else
                            <p style="color: #64748b; font-size: 13px;">No guiding details available</p>
                        @endif
                    </div>
                </div>

                <!-- Box 2: Target Fish & Methods -->
                <div class="guiding-card__info-box">
                    <div class="guiding-card__info-box-title">Target Fish</div>
                    <div class="guiding-card__info-box-content">
                        @if(!empty($guiding['target_fish']) && is_array($guiding['target_fish']) && count($guiding['target_fish']) > 0)
                            <div class="guiding-card__target-fish-chips">
                                @foreach($guiding['target_fish'] as $fish)
                                    <span class="guiding-card__target-fish-chip">
                                        <svg class="guiding-card__chip-icon" width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                            <path d="M6.5 12c.94-3.46 4.94-6 8.5-6 3.56 0 6.06 2.54 7 6-1 3.46-3.44 6-7 6s-7.56-2.54-8.5-6z"/>
                                            <path d="M18 5L22 9M18 19L22 15M6 9L2 5M6 15L2 19"/>
                                        </svg>
                                        <span>{{ translate($fish['name']) ?? $fish }}</span>
                                    </span>
                                @endforeach
                            </div>
                        @else
                            <p style="color: #64748b; font-size: 13px;">No target fish specified</p>
                        @endif
                    </div>

                    @if(!empty($guiding['methods']) && is_array($guiding['methods']) && count($guiding['methods']) > 0)
                        <div class="guiding-card__info-box-title" style="margin-top: 12px;">Fishing Methods</div>
                        <div class="guiding-card__info-box-content">
                            <div class="guiding-card__methods-chips">
                                @foreach($guiding['methods'] as $method)
                                    <span class="guiding-card__method-chip">{{ translate($method['name']) ?? $method }}</span>
                                @endforeach
                            </div>
                        </div>
                    @endif
                </div>

                <!-- Box 3: Location & Meeting Point -->
                <div class="guiding-card__info-box">
                    <div class="guiding-card__info-box-title">Location & Schedule</div>
                    <div class="guiding-card__info-box-content">
                        @if(!empty($guiding['meeting_point']))
                            <div>Meeting Point: <strong>{{ $guiding['meeting_point'] }}</strong></div>
                        @endif
                        
                        @if(!empty($guiding['start_times']) && is_array($guiding['start_times']))
                            <div style="margin-top: 8px;">
                                <div class="guiding-card__info-box-title" style="font-size: 11px; margin-bottom: 4px;">Available Times</div>
                                <div class="guiding-card__start-times-chips">
                                    @foreach($guiding['start_times'] as $time)
                                        <span class="guiding-card__start-time-chip">
                                            @if(stripos($time, 'evening') !== false || stripos($time, 'abends') !== false)
                                                <svg class="guiding-card__chip-icon" width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                                    <circle cx="12" cy="12" r="5"/>
                                                    <line x1="12" y1="1" x2="12" y2="3"/>
                                                    <line x1="12" y1="21" x2="12" y2="23"/>
                                                    <line x1="4.22" y1="4.22" x2="5.64" y2="5.64"/>
                                                    <line x1="18.36" y1="18.36" x2="19.78" y2="19.78"/>
                                                    <line x1="1" y1="12" x2="3" y2="12"/>
                                                    <line x1="21" y1="12" x2="23" y2="12"/>
                                                    <line x1="4.22" y1="19.78" x2="5.64" y2="18.36"/>
                                                    <line x1="18.36" y1="5.64" x2="19.78" y2="4.22"/>
                                                </svg>
                                            @elseif(stripos($time, 'night') !== false || stripos($time, 'nachts') !== false)
                                                <svg class="guiding-card__chip-icon" width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                                    <path d="M21 12.79A9 9 0 1 1 11.21 3 7 7 0 0 0 21 12.79z"/>
                                                </svg>
                                            @else
                                                <svg class="guiding-card__chip-icon" width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                                    <circle cx="12" cy="12" r="10"/>
                                                    <polyline points="12 6 12 12 16 14"/>
                                                </svg>
                                            @endif
                                            <span>{{ trim($time) }}</span>
                                        </span>
                                    @endforeach
                                </div>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@once
<script src="{{ asset('js/guiding-card.js') }}"></script>
@endonce
