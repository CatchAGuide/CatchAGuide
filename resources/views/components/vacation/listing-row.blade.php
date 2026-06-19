@props(['card'])

@php
    $galleryImages = array_values(array_filter($card['gallery_images'] ?? [$card['image'] ?? '']));
    $galleryFull = array_map(function ($img) {
        return str_starts_with((string) $img, 'http') ? $img : media_url($img);
    }, $galleryImages);
    $galleryCount = count($galleryImages);
    $galleryId = ($card['type'] ?? 'listing') . '-' . ($card['id'] ?? uniqid());

    $targetFishTags = $card['target_fish_tags'] ?? [];
    if (empty($targetFishTags) && ! empty($card['traits'])) {
        foreach ($card['traits'] as $trait) {
            if (($trait['label'] ?? '') === __('vacations.target_fish')) {
                $targetFishTags = array_map('trim', explode(',', $trait['value'] ?? ''));
                break;
            }
        }
    }

    $durationTrait = null;
    $capacityTrait = null;
    foreach ($card['traits'] ?? [] as $trait) {
        $label = $trait['label'] ?? '';
        if ($label === __('vacations.duration_label')) {
            $durationTrait = $trait['value'] ?? null;
        }
        if ($label === __('vacations.capacity_label')) {
            $capacityTrait = $trait['value'] ?? null;
        }
    }

    $facilities = $card['facilities'] ?? [];
    $facilitiesExtra = (int) ($card['facilities_extra'] ?? 0);
    $imageBadge = $card['image_badge'] ?? null;
    $priceSuffix = $card['listing_price_suffix'] ?? ($card['type'] === 'trip' ? __('vacations.per_person') : __('vacations.per_day_label'));
    $pillar = $card['type'] ?? 'camp';
    $pillarIcon = $pillar === 'trip' ? 'fa-suitcase-rolling' : 'fa-campground';
    $pillarLabel = $pillar === 'trip' ? __('vacations.pillar_index_trips_title') : __('vacations.pillar_index_camps_title');
    $targetFishSummary = ! empty($targetFishTags) ? implode(', ', $targetFishTags) : null;
    $mobileMethod = null;
    foreach ($card['traits'] ?? [] as $trait) {
        if (($trait['label'] ?? '') === __('vacations.method')) {
            $mobileMethod = $trait['value'] ?? null;
            break;
        }
    }
@endphp

<div class="vacation-list-card guiding-list-item vacation-list-card--{{ $pillar }}">
    <div class="vacation-list-card__inner border shadow-sm bg-white rounded overflow-hidden">
        <div class="vacation-list-card__media">
            <span
                class="vacation-list-card__pillar-icon vacation-list-card__pillar-icon--{{ $pillar }}"
                title="{{ $pillarLabel }}"
                aria-label="{{ $pillarLabel }}"
            >
                <i class="fas {{ $pillarIcon }}" aria-hidden="true"></i>
            </span>
            <div
                class="vacation-card__gallery"
                data-vacation-gallery="{{ $galleryId }}"
                data-gallery-images='@json($galleryFull)'
            >
                @if($imageBadge === 'top')
                    <span class="vacation-list-card__badge vacation-list-card__badge--top">
                        <i class="fas fa-star" aria-hidden="true"></i>
                        @lang('vacations.top_rated_badge')
                    </span>
                @elseif($imageBadge === 'limited')
                    <span class="vacation-list-card__badge vacation-list-card__badge--limited">
                        <i class="fas fa-bolt" aria-hidden="true"></i>
                        @lang('vacations.limited_avail_badge')
                    </span>
                @elseif($imageBadge === 'trip' && ! empty($card['badge']))
                    <span class="vacation-list-card__badge vacation-list-card__badge--limited">
                        {{ $card['badge'] }}
                    </span>
                @endif

                @if($galleryCount > 0)
                    <img
                        src="{{ $galleryImages[0] }}"
                        alt="{{ $card['title'] }}"
                        data-vacation-gallery-image
                        data-vacation-open-modal
                        class="vacation-list-card__img"
                        loading="lazy"
                    />
                    @if($galleryCount > 1)
                        <button type="button" aria-label="{{ __('vacations.gallery_prev') }}" class="vacation-gallery__nav-btn vacation-gallery__nav-btn--prev" data-vacation-prev-image>‹</button>
                        <button type="button" aria-label="{{ __('vacations.gallery_next') }}" class="vacation-gallery__nav-btn vacation-gallery__nav-btn--next" data-vacation-next-image>›</button>
                        <div class="vacation-gallery__counter" data-vacation-image-counter>1/{{ $galleryCount }}</div>
                    @endif
                @else
                    <img src="{{ asset('images/placeholder_guide.jpg') }}" alt="{{ $card['title'] }}" class="vacation-list-card__img" loading="lazy" />
                @endif
            </div>
        </div>

        <div class="vacation-list-card__body">
            <a href="{{ $card['url'] }}" class="vacation-list-card__link">
                <h3 class="vacation-list-card__title">{{ \Illuminate\Support\Str::limit($card['title'], 65) }}</h3>

                @if(!empty($card['location']))
                    <p class="vacation-list-card__location">
                        <i class="fas fa-map-marker-alt me-2" aria-hidden="true"></i>{{ $card['location'] }}
                    </p>
                @endif

                @foreach($card['traits'] ?? [] as $trait)
                    @php
                        $traitLabel = $trait['label'] ?? '';
                        $isDuration = $traitLabel === __('vacations.duration_label');
                        $isCapacity = $traitLabel === __('vacations.capacity_label');
                    @endphp
                    @if(! $isDuration && ! $isCapacity)
                        <div class="vacation-card-trait d-none d-md-flex">
                            <img src="{{ asset('assets/images/icons/' . ($traitLabel === __('vacations.method') ? 'fishing' : 'fish') . '.png') }}" height="16" width="16" alt="" />
                            <div class="vacation-card-trait__text">
                                <span class="vacation-card-trait__label">{{ $traitLabel }}:</span>
                                {{ $trait['value'] }}
                            </div>
                        </div>
                    @endif
                @endforeach

                @if($durationTrait || $capacityTrait || !empty($card['duration_pill']))
                    <div class="vacation-card-stats d-none d-md-flex">
                        @if($durationTrait || !empty($card['duration_pill']))
                            <div class="vacation-card-stat">
                                <span class="vacation-card-stat__label">@lang('vacations.duration_label')</span>
                                <span class="vacation-card-stat__value">
                                    <i class="far fa-clock" aria-hidden="true"></i>
                                    {{ $durationTrait ?? $card['duration_pill'] }}
                                </span>
                            </div>
                        @endif
                        @if($capacityTrait)
                            <div class="vacation-card-stat">
                                <span class="vacation-card-stat__label">@lang('vacations.capacity_label')</span>
                                <span class="vacation-card-stat__value">
                                    <i class="fas fa-user-friends" aria-hidden="true"></i>
                                    {{ $capacityTrait }}
                                </span>
                            </div>
                        @endif
                    </div>
                @endif

                @if($targetFishSummary)
                    <div class="vacation-card-trait vacation-card-trait--mobile d-md-none">
                        <img src="{{ asset('assets/images/icons/fish.png') }}" height="16" width="16" alt="" />
                        <div class="vacation-card-trait__text">
                            <span class="vacation-card-trait__label">@lang('vacations.target_fish'):</span>
                            <span class="vacation-card-trait__value">{{ \Illuminate\Support\Str::limit($targetFishSummary, 90) }}</span>
                        </div>
                    </div>
                @endif

                @if($mobileMethod)
                    <div class="vacation-card-trait vacation-card-trait--mobile d-md-none">
                        <img src="{{ asset('assets/images/icons/fishing.png') }}" height="16" width="16" alt="" />
                        <div class="vacation-card-trait__text">
                            <span class="vacation-card-trait__label">@lang('vacations.method'):</span>
                            <span class="vacation-card-trait__value">{{ \Illuminate\Support\Str::limit($mobileMethod, 60) }}</span>
                        </div>
                    </div>
                @endif

                @if($durationTrait || $capacityTrait || !empty($card['duration_pill']))
                    <div class="vacation-card-stats vacation-card-stats--mobile d-md-none">
                        @if($durationTrait || !empty($card['duration_pill']))
                            <div class="vacation-card-stat">
                                <span class="vacation-card-stat__label">@lang('vacations.duration_label')</span>
                                <span class="vacation-card-stat__value">
                                    <i class="far fa-clock" aria-hidden="true"></i>
                                    {{ $durationTrait ?? $card['duration_pill'] }}
                                </span>
                            </div>
                        @endif
                        @if($capacityTrait)
                            <div class="vacation-card-stat">
                                <span class="vacation-card-stat__label">@lang('vacations.capacity_label')</span>
                                <span class="vacation-card-stat__value">
                                    <i class="fas fa-user-friends" aria-hidden="true"></i>
                                    {{ $capacityTrait }}
                                </span>
                            </div>
                        @endif
                    </div>
                @endif

                @if(!empty($card['feature_badges']))
                    <div class="vacation-list-card__features d-md-none">
                        @foreach($card['feature_badges'] as $badge)
                            <span class="vacation-list-card__feature vacation-list-card__feature--{{ $loop->first ? 'boat' : 'guide' }}">
                                <i class="fas {{ $badge['icon'] }}" aria-hidden="true"></i>
                                <span>{{ $badge['label'] }}</span>
                            </span>
                        @endforeach
                    </div>
                @endif

                @if(!empty($facilities))
                    <div class="vacations-amenities-container d-md-none">
                        <ul class="vacation-list-card__amenities">
                            @foreach(array_slice($facilities, 0, 3) as $facility)
                                <li class="vacation-list-card__amenity">
                                    <i class="fa fa-check text-success" aria-hidden="true"></i>
                                    <span>{{ $facility }}</span>
                                </li>
                            @endforeach
                        </ul>
                    </div>

                    <div class="vacation-card-included d-none d-md-flex">
                        <span class="vacation-card-included__label">@lang('vacations.included_label'):</span>
                        @foreach($facilities as $facility)
                            <span class="vacation-card-included__item">
                                <i class="fa fa-check" aria-hidden="true"></i>
                                {{ $facility }}
                            </span>
                        @endforeach
                        @if($facilitiesExtra > 0)
                            <span class="vacation-card-included__more">+{{ $facilitiesExtra }} @lang('vacations.more')</span>
                        @endif
                    </div>
                @endif
            </a>

            <div class="vacation-list-card__footer">
                @if(($card['price'] ?? null) !== null)
                    <div class="vacation-list-card__price-block">
                        <span class="vacation-list-card__price-from-label d-none d-md-inline">@lang('vacations.from_label')</span>
                        <span class="vacation-list-card__price">€{{ two($card['price']) }}</span>
                        <span class="vacation-list-card__price-label">{{ $priceSuffix }}</span>
                    </div>
                @endif

                <div class="vacation-list-card__footer-actions">
                    <a href="{{ $card['url'] }}" class="vacation-list-card__btn-book">{{ $card['cta'] }} &rarr;</a>
                    <a href="{{ $card['url'] }}" class="vacation-list-card__btn-details d-none d-md-inline-flex">@lang('vacations.view_details') &rarr;</a>
                    <a href="{{ $card['url'] }}" class="vacation-list-card__btn-info d-md-none" aria-label="@lang('vacations.details')">
                        <i class="fas fa-info" aria-hidden="true"></i>
                    </a>
                </div>
                <div class="vacation-list-card__trust d-none d-md-flex">
                    <span class="vacation-list-card__trust-item">
                        <i class="fas fa-check-circle" aria-hidden="true"></i>
                        @lang('vacations.free_cancellation')
                    </span>
                    <span class="vacation-list-card__trust-item">
                        <i class="fas fa-check-circle" aria-hidden="true"></i>
                        @lang('vacations.verified_operator')
                    </span>
                </div>

                <div class="vacation-list-card__no-fees d-none d-md-block">@lang('vacations.no_booking_fees')</div>
            </div>

            @if(!empty($card['destination_id']))
                <form action="{{ $card['url'] }}" method="GET" style="display: none;">
                    @php session(['vacation_destination_id' => $card['destination_id']]); @endphp
                </form>
            @endif
        </div>
    </div>

    @if($galleryCount > 1)
        <div class="vacation-gallery-modal" data-vacation-modal="{{ $galleryId }}">
            <div class="vacation-gallery-modal__content">
                <button type="button" class="vacation-gallery-modal__close" aria-label="{{ __('vacations.gallery_close') }}">&times;</button>
                <button type="button" class="vacation-gallery-modal__prev" aria-label="{{ __('vacations.gallery_prev') }}">&#10094;</button>
                <button type="button" class="vacation-gallery-modal__next" aria-label="{{ __('vacations.gallery_next') }}">&#10095;</button>
                <img class="vacation-gallery-modal__image" src="" alt="{{ $card['title'] }}">
                <div class="vacation-gallery-modal__counter">
                    <span class="vacation-gallery-modal__current">1</span> / <span class="vacation-gallery-modal__total">{{ $galleryCount }}</span>
                </div>
            </div>
        </div>
    @endif
</div>
