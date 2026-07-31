<div class="guiding-card" id="guiding-{{ $guiding['id'] ?? '' }}" data-guiding-card data-guiding-id="{{ $guiding['id'] ?? '' }}">
    @php
        $guidingThumbnail = $guiding['thumbnail_path']
            ?? 'https://images.unsplash.com/photo-1474843148229-3163319fcc00?q=80&w=1600&auto=format&fit=crop';
        // The thumbnail leads the gallery so the counter and the arrows match what is on screen
        $guidingGalleryImages = array_values(array_unique(array_filter(
            array_merge([$guidingThumbnail], $guiding['gallery_images'] ?? [])
        )));
        $guidingGalleryCount = count($guidingGalleryImages);
        $durationLabel = $guiding['duration_label'] ?? ($guiding['duration_hours'] ?? null);
        $maxPersons = $guiding['max_persons'] ?? null;
        $tourType = $guiding['type'] ?? null;
        $priceAmount = (float) ($guiding['price']['amount'] ?? 0);
        $displayPriceType = $guiding['price']['display_type'] ?? __('per tour');
    @endphp

    <div class="guiding-card__grid">
        <div class="guiding-card__media">
            <div class="guiding-card__gallery" data-gallery-images='@json($guidingGalleryImages)'>
                <img
                    src="{{ $guidingThumbnail }}"
                    alt="{{ $guiding['title'] ?? 'Guiding' }}"
                    loading="lazy"
                    decoding="async"
                    data-gallery-image
                    data-open-modal
                    style="cursor: pointer;"
                />

                @if($guidingGalleryCount > 1)
                <div>
                    <button
                        type="button"
                        aria-label="{{ __('Previous image') }}"
                        class="guiding-gallery__nav-btn guiding-gallery__nav-btn--prev"
                        data-prev-image
                    >
                        ‹
                    </button>
                    <button
                        type="button"
                        aria-label="{{ __('Next image') }}"
                        class="guiding-gallery__nav-btn guiding-gallery__nav-btn--next"
                        data-next-image
                    >
                        ›
                    </button>
                    <div class="guiding-gallery__counter" data-image-counter>1/{{ $guidingGalleryCount }}</div>
                </div>
                @endif
            </div>

            {{-- Title and Summary right after gallery - Mobile version --}}
            <div class="guiding-card__title-after-gallery guiding-card__title-after-gallery--mobile">
                <div class="guiding-card__summary-header">
                    <h3 class="guiding-card__title">{{ translate($guiding['title']) ?? 'Guiding Title' }}</h3>
                    <p class="guiding-card__description">{{ translate($guiding['description']) ?? 'Description' }}</p>
                </div>

                <div class="guiding-card__spec-row">
                    @if($durationLabel)
                    <span class="guiding-card__spec-item">
                        <svg class="guiding-card__spec-icon" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <circle cx="12" cy="12" r="10"/>
                            <polyline points="12 6 12 12 16 14"/>
                        </svg>
                        <span>{{ translate($durationLabel) }}</span>
                    </span>
                    @endif
                    @if($maxPersons)
                    <span class="guiding-card__spec-item">
                        <svg class="guiding-card__spec-icon" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/>
                            <circle cx="12" cy="7" r="4"/>
                        </svg>
                        <span>{{ $maxPersons }} {{ __('Pers') }}</span>
                    </span>
                    @endif
                    @if($tourType)
                    <span class="guiding-card__spec-item">
                        <svg class="guiding-card__spec-icon" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M2 6s1.5-2 5-2 5 2 5 2 1.5-2 5-2 5 2 5 2v14s-1.5-2-5-2-5 2-5 2-1.5-2-5-2-5 2-5 2V6z"/>
                        </svg>
                        <span>{{ translate($tourType) }}</span>
                    </span>
                    @endif
                </div>
            </div>

            {{-- Inclusives panel appears after gallery (expanded only) --}}
            <div class="guiding-card__left-panels" data-expanded-only>
                <div class="guiding-card__panel">
                    <div class="guiding-card__panel-title">{{ __('Included in the price') }}</div>
                    <div class="guiding-card__chip-row">
                        @if(!empty($guiding['inclusives']) && is_array($guiding['inclusives']))
                            @foreach($guiding['inclusives'] as $inclusive)
                                <span class="guiding-card__inclusive-chip">
                                    <svg class="guiding-card__check-icon" width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3">
                                        <polyline points="20 6 9 17 4 12"/>
                                    </svg>
                                    <span>{{ translate($inclusive['name']) ?? $inclusive }}</span>
                                </span>
                            @endforeach
                        @else
                            <p class="guiding-card__empty">{{ __('No inclusives listed') }}</p>
                        @endif
                    </div>
                </div>
            </div>
        </div>

        {{-- Summary section - Desktop: middle column, Mobile: hidden (uses title-after-gallery instead) --}}
        <div class="guiding-card__summary">
            <div class="guiding-card__summary-header">
                <h3 class="guiding-card__title">{{ translate($guiding['title']) ?? 'Guiding Title' }}</h3>
                <p class="guiding-card__description">{{ translate($guiding['description']) ?? 'Description' }}</p>
            </div>

            <div class="guiding-card__spec-row">
                @if($durationLabel)
                <span class="guiding-card__spec-item">
                    <svg class="guiding-card__spec-icon" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <circle cx="12" cy="12" r="10"/>
                        <polyline points="12 6 12 12 16 14"/>
                    </svg>
                    <span>{{ translate($durationLabel) }}</span>
                </span>
                @endif
                @if($maxPersons)
                <span class="guiding-card__spec-item">
                    <svg class="guiding-card__spec-icon" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/>
                        <circle cx="12" cy="7" r="4"/>
                    </svg>
                    <span>{{ $maxPersons }} {{ __('Pers') }}</span>
                </span>
                @endif
                @if($tourType)
                <span class="guiding-card__spec-item">
                    <svg class="guiding-card__spec-icon" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M2 6s1.5-2 5-2 5 2 5 2 1.5-2 5-2 5 2 5 2v14s-1.5-2-5-2-5 2-5 2-1.5-2-5-2-5 2-5 2V6z"/>
                    </svg>
                    <span>{{ translate($tourType) }}</span>
                </span>
                @endif
            </div>
        </div>

        <div class="guiding-card__actions">
            <div class="guiding-card__actions-column">
                <div class="guiding-card__pricing">
                    <div class="guiding-card__price-type">{{ $displayPriceType }}</div>
                    <div class="guiding-card__price-amount">€{{ number_format($priceAmount, 2) }}</div>
                </div>
                {{-- <button class="guiding-card__select-btn">
                    {{ __('Select This Guiding') }}
                </button> --}}
                <button class="guiding-card__expand-btn guiding-card__expand-btn--secondary" data-toggle-btn>
                    <span data-toggle-text>{{ __('Show More') }}</span>
                    <span data-toggle-icon>▼</span>
                </button>
            </div>
        </div>

        <div class="guiding-card__info-matrix" data-expanded-only>
            <div class="guiding-card__info-box">
                <div class="guiding-card__info-box-title">{{ __('Guiding Information') }}</div>
                <div class="guiding-card__info-box-content">
                    @if(!empty($guiding['guiding_info']))
                        <ul class="guiding-card__info-list">
                            @if(!empty($guiding['guiding_info']['art']))
                                <li><span>{{ __('Type') }}:</span> <strong>{{ translate($guiding['guiding_info']['art']) }}</strong></li>
                            @endif
                            @if(!empty($guiding['guiding_info']['dauer']))
                                <li><span>{{ __('Duration') }}:</span> <strong>{{ translate($guiding['guiding_info']['dauer']) }}</strong></li>
                            @endif
                            @if(!empty($guiding['guiding_info']['max_personen']))
                                <li><span>{{ __('Max Persons') }}:</span> <strong>{{ $guiding['guiding_info']['max_personen'] }}</strong></li>
                            @endif
                            @if(!empty($guiding['guiding_info']['gewaesser']))
                                <li><span>{{ __('Water') }}:</span> <strong>{{ translate($guiding['guiding_info']['gewaesser']) }}</strong></li>
                            @endif
                        </ul>
                    @else
                        <p class="guiding-card__empty">{{ __('No guiding details available') }}</p>
                    @endif
                </div>
            </div>

            <div class="guiding-card__info-box">
                <div class="guiding-card__info-box-title">{{ __('Target Fish') }}</div>
                <div class="guiding-card__info-box-content">
                    @if(!empty($guiding['target_fish']) && is_array($guiding['target_fish']))
                        <div class="guiding-card__chip-row">
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
                        <p class="guiding-card__empty">{{ __('No target fish specified') }}</p>
                    @endif
                </div>

                @if(!empty($guiding['methods']) && is_array($guiding['methods']) && count($guiding['methods']) > 0)
                    <div class="guiding-card__info-box-title">{{ __('Fishing Methods') }}</div>
                    <div class="guiding-card__info-box-content">
                        <div class="guiding-card__chip-row">
                            @foreach($guiding['methods'] as $method)
                                <span class="guiding-card__method-chip">{{ translate($method['name']) ?? $method }}</span>
                            @endforeach
                        </div>
                    </div>
                @endif
            </div>

            <div class="guiding-card__info-box">
                <div class="guiding-card__info-box-title">{{ __('Location & Schedule') }}</div>
                <div class="guiding-card__info-box-content">
                    @if(!empty($guiding['meeting_point']))
                        <ul class="guiding-card__info-list">
                            <li><span>{{ __('Meeting Point') }}:</span> <strong>{{ $guiding['meeting_point'] }}</strong></li>
                        </ul>
                    @endif

                    @if(!empty($guiding['start_times']) && is_array($guiding['start_times']))
                        <div class="guiding-card__chip-row">
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
                    @endif

                    @if(empty($guiding['meeting_point']) && empty($guiding['start_times']))
                        <p class="guiding-card__empty">{{ __('No schedule details available') }}</p>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <!-- Guiding Gallery Modal -->
    <div class="guiding-gallery-modal" data-guiding-modal>
        <div class="guiding-gallery-modal__content">
            <button class="guiding-gallery-modal__close">&times;</button>
            <button class="guiding-gallery-modal__prev">&#10094;</button>
            <button class="guiding-gallery-modal__next">&#10095;</button>
            <img class="guiding-gallery-modal__image" src="" alt="{{ translate($guiding['title']) ?? 'Guiding' }}">
            <div class="guiding-gallery-modal__counter">
                <span class="guiding-gallery-modal__current">1</span> / <span class="guiding-gallery-modal__total">{{ max($guidingGalleryCount, 1) }}</span>
            </div>
        </div>
    </div>
</div>

@once
<script src="{{ asset('js/guiding-card.js') }}"></script>
@endonce
