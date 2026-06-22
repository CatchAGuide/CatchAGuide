@props(['card'])

@php
    $galleryImages = array_values(array_filter($card['gallery_images'] ?? [$card['image'] ?? '']));
    $galleryFull = array_map(function ($img) {
        return str_starts_with((string) $img, 'http') ? $img : media_url($img);
    }, $galleryImages);
    $galleryCount = count($galleryImages);
    $galleryId = 'camp-' . ($card['id'] ?? uniqid());

    $targetFishTags = vacation_fish_tags($card['target_fish_tags'] ?? []);
    $visibleFishTags = array_slice($targetFishTags, 0, 3);
    $targetFishExtra = max(0, count($targetFishTags) - count($visibleFishTags));

    $facilities = $card['listing_facilities'] ?? array_map(
        fn (string $label) => ['label' => $label, 'icon' => vacation_camp_facility_icon($label)],
        $card['facilities'] ?? []
    );
    $availability = array_map(
        fn ($item) => vacation_availability_item($item),
        $card['listing_availability'] ?? ($card['slider_availability'] ?? [])
    );
    $priceSuffix = $card['listing_price_suffix'] ?? __('vacations.per_night_label');
@endphp

<article class="vacation-camp-list-card guiding-list-item" data-analytics-vacation-card data-pillar="camp">
    <div class="vacation-camp-list-card__inner">
        <div class="vacation-camp-list-card__media">
            <div
                class="vacation-camp-list-card__gallery"
                data-vacation-gallery="{{ $galleryId }}"
                data-gallery-images='@json($galleryFull)'
            >
                @if($galleryCount > 0)
                    <img
                        src="{{ $galleryImages[0] }}"
                        alt="{{ $card['title'] }}"
                        data-vacation-gallery-image
                        data-vacation-open-modal
                        class="vacation-camp-list-card__img"
                        loading="lazy"
                    />
                    @if($galleryCount > 1)
                        <div class="vacation-camp-list-card__counter" data-vacation-image-counter>1/{{ $galleryCount }}</div>
                    @endif
                @else
                    <img
                        src="{{ asset('images/placeholder_guide.jpg') }}"
                        alt="{{ $card['title'] }}"
                        class="vacation-camp-list-card__img"
                        loading="lazy"
                    />
                @endif

                <button
                    type="button"
                    class="vacation-camp-list-card__favorite"
                    aria-label="{{ __('vacations.add_to_favorites') }}"
                >
                    <i class="far fa-heart" aria-hidden="true"></i>
                </button>
            </div>
        </div>

        <div class="vacation-camp-list-card__content">
            <div class="vacation-camp-list-card__header">
                <div class="vacation-camp-list-card__headline">
                    <h3 class="vacation-camp-list-card__title">
                        <a href="{{ $card['url'] }}">{{ $card['title'] }}</a>
                    </h3>

                    @if(!empty($card['location']))
                        <p class="vacation-camp-list-card__location">
                            <i class="fas fa-map-marker-alt" aria-hidden="true"></i>
                            <span>{{ $card['location'] }}</span>
                        </p>
                    @endif
                </div>

                @if(($card['price'] ?? null) !== null)
                    <div class="vacation-camp-list-card__price">
                        <span class="vacation-camp-list-card__price-from">{{ __('vacations.from_label') }}</span>
                        <span class="vacation-camp-list-card__price-amount">€{{ two($card['price']) }}</span>
                        <span class="vacation-camp-list-card__price-unit">{{ $priceSuffix }}</span>
                    </div>
                @endif
            </div>

            @if(!empty($visibleFishTags))
                <div class="vacation-camp-list-card__section">
                    <div class="vacation-camp-list-card__section-heading">
                        <img
                            src="{{ asset('assets/images/icons/fish.png') }}"
                            width="16"
                            height="16"
                            alt=""
                            class="vacation-camp-list-card__section-icon"
                        />
                        <span class="vacation-camp-list-card__section-label">{{ __('vacations.target_fish') }}</span>
                    </div>
                    <div class="vacation-camp-list-card__tags">
                        @foreach($visibleFishTags as $tag)
                            <span class="vacation-camp-list-card__tag">{{ $tag }}</span>
                        @endforeach
                        @if($targetFishExtra > 0)
                            <span class="vacation-camp-list-card__tag vacation-camp-list-card__tag--more">+{{ $targetFishExtra }}</span>
                        @endif
                    </div>
                </div>
            @endif

            @if(!empty($facilities) || !empty($availability))
                <div class="vacation-camp-list-card__section">
                    <div class="vacation-camp-list-card__section-heading">
                        <img
                            src="{{ asset('assets/images/icons/check.png') }}"
                            width="16"
                            height="16"
                            alt=""
                            class="vacation-camp-list-card__section-icon"
                        />
                        <span class="vacation-camp-list-card__section-label">{{ __('vacations.availability_label') }}</span>
                    </div>

                    @if(!empty($facilities))
                        <div class="vacation-camp-list-card__amenities">
                            @foreach($facilities as $facility)
                                <span class="vacation-camp-list-card__amenity">
                                    <img
                                        src="{{ asset($facility['icon']) }}"
                                        width="16"
                                        height="16"
                                        alt=""
                                        class="vacation-camp-list-card__amenity-icon"
                                    />
                                    <span>{{ $facility['label'] }}</span>
                                </span>
                            @endforeach
                        </div>
                    @endif

                    @if(!empty($availability))
                        <div class="vacation-camp-list-card__availability">
                            @foreach($availability as $item)
                                <span class="vacation-camp-list-card__availability-item">
                                    <img
                                        src="{{ asset($item['icon']) }}"
                                        width="16"
                                        height="16"
                                        alt=""
                                        class="vacation-camp-list-card__availability-icon"
                                    />
                                    <span>{{ $item['label'] }}</span>
                                </span>
                            @endforeach
                        </div>
                    @endif
                </div>
            @endif

            <div class="vacation-camp-list-card__footer">
                <div class="vacation-camp-list-card__trust">
                    <span class="vacation-camp-list-card__trust-item">
                        <i class="fas fa-shield-alt" aria-hidden="true"></i>
                        {{ __('vacations.verified_host') }}
                    </span>
                    <span class="vacation-camp-list-card__trust-item">
                        <i class="fas fa-calendar-check" aria-hidden="true"></i>
                        {{ __('vacations.free_cancellation') }}
                    </span>
                </div>

                <a href="{{ $card['url'] }}" class="vacation-camp-list-card__cta">
                    {{ $card['cta'] }} <span aria-hidden="true">&rarr;</span>
                </a>
            </div>
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

    @if(!empty($card['destination_id']))
        <form action="{{ $card['url'] }}" method="GET" style="display: none;">
            @php session(['vacation_destination_id' => $card['destination_id']]); @endphp
        </form>
    @endif
</article>
