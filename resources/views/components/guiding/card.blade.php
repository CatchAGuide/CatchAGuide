<div class="guiding-card" id="guiding-{{ $guiding['id'] ?? '' }}" data-guiding-card data-guiding-id="{{ $guiding['id'] ?? '' }}">
    @php
        use App\Presenters\Vacation\CampAttachmentChipPresenter;

        $guidingThumbnail = $guiding['thumbnail_path']
            ?? 'https://images.unsplash.com/photo-1474843148229-3163319fcc00?q=80&w=1600&auto=format&fit=crop';
        // The thumbnail leads the gallery so the counter and the arrows match what is on screen
        $guidingGalleryImages = array_values(array_unique(array_filter(
            array_merge([$guidingThumbnail], $guiding['gallery_images'] ?? [])
        )));
        $guidingGalleryCount = count($guidingGalleryImages);
        $guidingGalleryId = 'guiding-card-'.($guiding['id'] ?? uniqid());
        $durationLabel = $guiding['duration_label'] ?? ($guiding['duration_hours'] ?? null);
        $maxPersons = $guiding['max_persons'] ?? null;
        $tourType = CampAttachmentChipPresenter::fishingFromChipValue($guiding['type'] ?? null);
        $waterTypes = $guiding['water_types'] ?? [];
        $priceAmount = (float) ($guiding['price']['amount'] ?? 0);
        $displayPriceType = $guiding['price']['display_type'] ?? __('vacations.per_tour');
        $guidingModalTitle = translate($guiding['title'] ?? null) ?? ($guiding['title'] ?? 'Guiding');
        $guidingPersonsChip = CampAttachmentChipPresenter::personsValue($maxPersons);
        $waterTypeChips = CampAttachmentChipPresenter::waterTypeChips($waterTypes);
        $guidingModalSpecs = array_values(array_filter(array_merge(
            [
                $durationLabel,
                $guidingPersonsChip,
                $tourType,
            ],
            array_column($waterTypeChips, 'value')
        )));
        $guidingPriceDisplay = '€'.number_format($priceAmount, 2);
    @endphp

    <div class="guiding-card__grid">
        <div class="guiding-card__media">
            <div class="guiding-card__gallery" data-vacation-gallery="{{ $guidingGalleryId }}" data-gallery-images='@json($guidingGalleryImages)'>
                <img
                    src="{{ $guidingThumbnail }}"
                    alt="{{ $guiding['title'] ?? 'Guiding' }}"
                    loading="lazy"
                    decoding="async"
                    data-vacation-gallery-image
                    data-vacation-open-modal
                    style="cursor: pointer;"
                />

                @if($guidingGalleryCount > 1)
                <div>
                    <button
                        type="button"
                        aria-label="{{ __('vacations.gallery_prev') }}"
                        class="guiding-gallery__nav-btn guiding-gallery__nav-btn--prev"
                        data-prev-image
                    >
                        ‹
                    </button>
                    <button
                        type="button"
                        aria-label="{{ __('vacations.gallery_next') }}"
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

                @include('components.guiding.partials.spec-row')
            </div>
        </div>

        <div class="guiding-card__content">
            <div class="guiding-card__content-header">
                {{-- Summary section - Desktop: middle column, Mobile: hidden (uses title-after-gallery instead) --}}
                <div class="guiding-card__summary">
                    <div class="guiding-card__summary-header">
                        <h3 class="guiding-card__title">{{ translate($guiding['title']) ?? 'Guiding Title' }}</h3>
                        <p class="guiding-card__description">{{ translate($guiding['description']) ?? 'Description' }}</p>
                    </div>

                    @include('components.guiding.partials.spec-row')
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
                        <button class="attachment-expand-btn guiding-card__expand-btn guiding-card__expand-btn--secondary" data-toggle-btn data-label-more="{{ __('vacations.show_more') }}" data-label-less="{{ __('vacations.show_less') }}">
                            <span data-toggle-text>{{ __('vacations.show_more') }}</span>
                            <span data-toggle-icon>▼</span>
                        </button>
                    </div>
                </div>
            </div>

        <div class="guiding-card__info-matrix" data-expanded-only>
            @php
                $factWaterValue = count($waterTypeChips) > 0
                    ? implode(' · ', array_column($waterTypeChips, 'value'))
                    : (!empty($guiding['guiding_info']['gewaesser']) ? translate($guiding['guiding_info']['gewaesser']) : null);
                // Catalog IDs that no longer resolve to a real name fall back to the
                // raw id (see translated_catalog_label()) — drop those instead of
                // showing a meaningless number.
                $resolvedCatalogLabels = fn (array $items) => array_values(array_filter(
                    array_map(fn ($item) => translated_catalog_label($item), $items),
                    fn ($label) => $label !== '' && ! is_numeric($label)
                ));
                $methodLabels = $resolvedCatalogLabels($guiding['methods'] ?? []);
                $factMethodsValue = count($methodLabels) > 0 ? implode(' · ', $methodLabels) : null;
                $targetFishLabels = $resolvedCatalogLabels($guiding['target_fish'] ?? []);
                $factTargetFishValue = count($targetFishLabels) > 0 ? implode(', ', $targetFishLabels) : null;
                $hasAnyFact = $durationLabel || $guidingPersonsChip || $tourType || $factWaterValue || $factMethodsValue || $factTargetFishValue;
            @endphp
            <div class="guiding-card__info-box guiding-card__info-box--facts">
                <div class="guiding-card__info-box-title">{{ __('vacations.guiding_information') }}</div>
                @if($hasAnyFact)
                    <div class="guiding-card__fact-grid">
                        @if($durationLabel)
                            <div class="guiding-card__fact-cell">
                                <div class="guiding-card__fact-label">
                                    <img src="{{ asset('assets/images/icons/clock-new.svg') }}" width="12" height="12" alt="">
                                    {{ __('guidings.Duration') }}
                                </div>
                                <div class="guiding-card__fact-value">{{ $durationLabel }}</div>
                            </div>
                        @endif
                        @if($guidingPersonsChip)
                            <div class="guiding-card__fact-cell">
                                <div class="guiding-card__fact-label">
                                    <img src="{{ asset('assets/images/icons/user-new.svg') }}" width="12" height="12" alt="">
                                    {{ __('guidings.persons') }}
                                </div>
                                <div class="guiding-card__fact-value">{{ $guidingPersonsChip }}</div>
                            </div>
                        @endif
                        @if($tourType)
                            <div class="guiding-card__fact-cell">
                                <div class="guiding-card__fact-label">
                                    <img src="{{ asset('assets/images/icons/fishing-tool-new.svg') }}" width="12" height="12" alt="">
                                    {{ __('guidings.Fishing_Type') }}
                                </div>
                                <div class="guiding-card__fact-value">{{ $tourType }}</div>
                            </div>
                        @endif
                        @if($factWaterValue)
                            <div class="guiding-card__fact-cell">
                                <div class="guiding-card__fact-label">
                                    <img src="{{ asset('assets/images/icons/water-waves.png') }}" width="12" height="12" alt="">
                                    {{ __('guidings.Water') }}
                                </div>
                                <div class="guiding-card__fact-value">{{ $factWaterValue }}</div>
                            </div>
                        @endif
                        @if($factMethodsValue)
                            <div class="guiding-card__fact-cell guiding-card__fact-cell--full">
                                <div class="guiding-card__fact-label">
                                    <img src="{{ asset('assets/images/icons/fishing-tool-new.svg') }}" width="12" height="12" alt="">
                                    {{ __('vacations.fishing_methods') }}
                                </div>
                                <div class="guiding-card__fact-value">{{ $factMethodsValue }}</div>
                            </div>
                        @endif
                        @if($factTargetFishValue)
                            <div class="guiding-card__fact-cell guiding-card__fact-cell--full">
                                <div class="guiding-card__fact-label">
                                    <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                        <path d="M6.5 12c.94-3.46 4.94-6 8.5-6 3.56 0 6.06 2.54 7 6-1 3.46-3.44 6-7 6s-7.56-2.54-8.5-6z"/>
                                        <path d="M18 5L22 9M18 19L22 15M6 9L2 5M6 15L2 19"/>
                                    </svg>
                                    {{ __('guidings.Target_Fish') }}
                                </div>
                                <div class="guiding-card__fact-value">{{ $factTargetFishValue }}</div>
                            </div>
                        @endif
                    </div>
                @else
                    <p class="guiding-card__empty">{{ __('vacations.no_guiding_details') }}</p>
                @endif
            </div>

            <div class="guiding-card__info-box">
                <div class="guiding-card__info-box-title">{{ __('vacations.included_in_price') }}</div>
                @if(!empty($guiding['inclusives']) && is_array($guiding['inclusives']))
                    <ul class="guiding-card__checklist">
                        @foreach($guiding['inclusives'] as $inclusive)
                            <li>
                                <svg class="guiding-card__check-icon" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3">
                                    <polyline points="20 6 9 17 4 12"/>
                                </svg>
                                <span>{{ translated_catalog_label($inclusive) }}</span>
                            </li>
                        @endforeach
                    </ul>
                @else
                    <p class="guiding-card__empty">{{ __('vacations.no_inclusives_listed') }}</p>
                @endif
            </div>

            <div class="guiding-card__info-box">
                <div class="guiding-card__info-box-content">
                    @php
                        $hasStartTimes = !empty($guiding['start_times']) && is_array($guiding['start_times']);
                        $startTimesValue = $hasStartTimes
                            ? implode(' · ', array_map(fn ($time) => translate(trim($time)), $guiding['start_times']))
                            : null;
                        $meetingPoint = trim((string) ($guiding['desc_meeting_point'] ?? ''));
                        $hasMeetingPoint = $meetingPoint !== '';
                    @endphp
                    @if($startTimesValue)
                        <div class="guiding-card__schedule-item">
                            <div class="guiding-card__schedule-label">{{ __('guidings.Starting_Time') }}</div>
                            <div class="guiding-card__schedule-value">{{ $startTimesValue }}</div>
                        </div>
                    @endif
                    @if($hasMeetingPoint)
                        <div class="guiding-card__schedule-item">
                            <div class="guiding-card__schedule-label">{{ __('guidings.Meeting_Point') }}</div>
                            <div class="guiding-card__schedule-value">{!! clean_html($meetingPoint) !!}</div>
                        </div>
                    @endif
                    @if(!$startTimesValue && !$hasMeetingPoint)
                        <p class="guiding-card__empty">{{ __('vacations.no_schedule_details') }}</p>
                    @endif
                </div>
            </div>
        </div>
        </div>
    </div>

    <!-- Guiding Gallery Modal -->
    <x-gallery.modal
        :id="$guidingGalleryId"
        :images="$guidingGalleryImages"
        :title="$guidingModalTitle"
        type="tour"
        :badge="__('offers.badge_tour')"
        :specs="$guidingModalSpecs"
        :price-prefix="$displayPriceType"
        :price-display="$guidingPriceDisplay"
    />
</div>

@once
<script src="{{ asset('js/guiding-card.js') }}"></script>
@endonce
