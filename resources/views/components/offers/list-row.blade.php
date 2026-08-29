@props(['card'])

@php
    use Illuminate\Support\Str;

    $type = $card['type'] ?? 'tour';
    $galleryImages = array_values(array_filter($card['gallery_images'] ?? [$card['image'] ?? '']));
    $galleryFull = array_map(function ($img) {
        return str_starts_with((string) $img, 'http') ? $img : media_url($img);
    }, $galleryImages);
    $galleryCount = count($galleryFull);
    $galleryId = 'offer-'.$type.'-'.($card['id'] ?? uniqid());

    $fishTags = function_exists('vacation_fish_tags')
        ? vacation_fish_tags($card['target_fish_tags'] ?? [])
        : ($card['target_fish_tags'] ?? []);
    $visibleFish = array_slice($fishTags, 0, 3);
    $fishExtra = (int) ($card['target_fish_tags_extra'] ?? max(0, count($fishTags) - 3));

    $included = array_slice($card['listing_included'] ?? ($card['facilities'] ?? []), 0, 3);
    $includedExtra = (int) ($card['listing_included_extra'] ?? $card['facilities_extra'] ?? 0);
    $includedTitle = $card['whats_included_title'] ?? __('offers.included_heading');

    $duration = $card['duration_label'] ?? $card['duration_pill'] ?? null;
    $guests = $card['guests_label'] ?? null;
    $water = $card['water_label'] ?? null;
    $boat = $card['boat_label'] ?? null;
    $methods = $card['methods_label'] ?? null;
    $availability = $card['listing_availability'] ?? [];

    $specs = array_values(array_filter([
        $duration ? [
            'key' => 'duration',
            'icon' => asset('assets/images/icons/clock-new.svg'),
            'label' => $duration,
        ] : null,
        $guests ? [
            'key' => 'guests',
            'icon' => asset('assets/images/icons/user-new.svg'),
            'label' => $guests,
        ] : null,
        $water ? [
            'key' => 'water',
            'icon' => asset('assets/images/icons/pelagic.png'),
            'label' => $water,
        ] : null,
        $boat ? [
            'key' => 'boat',
            'icon' => asset('assets/images/icons/fishing-tool-new.svg'),
            'label' => $boat,
        ] : null,
        (! $boat && $methods) ? [
            'key' => 'methods',
            'icon' => asset('assets/images/icons/fishing-tool-new.svg'),
            'label' => $methods,
        ] : null,
    ]));

    $badge = $card['badge'] ?? __('offers.badge_'.$type);
    $imageBadge = $card['image_badge'] ?? null;
    $cta = $card['listing_cta'] ?? $card['cta'] ?? __('offers.see_details');
    $pricePrefix = $card['listing_price_prefix'] ?? __('vacations.starting_from_label');
    $priceDisplay = $card['listing_price_display'] ?? null;
    $priceSuffix = $card['listing_price_suffix'] ?? '';
    $priceNote = $card['listing_price_note'] ?? null;
    $rating = isset($card['rating']) ? (float) $card['rating'] : null;
    $reviewCount = (int) ($card['review_count'] ?? 0);
    $verified = ! empty($card['verified']) && ! in_array($type, ['trip', 'camp'], true);
@endphp

<article
    class="offers-card offers-card--{{ $type }}"
    data-offer-type="{{ $type }}"
    data-analytics-offer-card
>
    <div class="offers-card__media">
        <div
            class="offers-card__gallery"
            data-vacation-gallery="{{ $galleryId }}"
            data-gallery-images='@json($galleryFull)'
        >
            @if($galleryCount > 0)
                <img
                    src="{{ $galleryFull[0] }}"
                    alt="{{ $card['title'] }}"
                    class="offers-card__img"
                    data-vacation-gallery-image
                    data-vacation-open-modal
                    loading="lazy"
                >
                @if($galleryCount > 1)
                    <button
                        type="button"
                        class="offers-card__nav offers-card__nav--prev"
                        data-offers-gallery-prev
                        aria-label="{{ __('vacations.gallery_prev') }}"
                    >&#10094;</button>
                    <button
                        type="button"
                        class="offers-card__nav offers-card__nav--next"
                        data-offers-gallery-next
                        aria-label="{{ __('vacations.gallery_next') }}"
                    >&#10095;</button>
                    <span class="offers-card__counter" data-vacation-image-counter>1/{{ $galleryCount }}</span>
                @endif
            @else
                <img src="{{ asset('images/placeholder_guide.jpg') }}" alt="" class="offers-card__img" loading="lazy">
            @endif

            <span class="offers-card__badge offers-card__badge--{{ $type }}">{{ $badge }}</span>

            @if($imageBadge === 'top')
                <span class="offers-card__ribbon">
                    <i class="fas fa-star" aria-hidden="true"></i>
                    {{ __('vacations.top_rated_badge') }}
                </span>
            @elseif($imageBadge === 'limited')
                <span class="offers-card__ribbon offers-card__ribbon--alt">
                    <i class="fas fa-bolt" aria-hidden="true"></i>
                    {{ __('vacations.limited_avail_badge') }}
                </span>
            @endif

            @if($rating)
                <div
                    class="offers-card__rating-badge"
                    title="{{ trans_choice('offers.reviews_count', $reviewCount, ['count' => $reviewCount]) }}"
                    aria-label="{{ number_format($rating, 1) }}{{ $reviewCount > 0 ? ', '.trans_choice('offers.reviews_count', $reviewCount, ['count' => $reviewCount]) : '' }}"
                >
                    <i class="fas fa-star" aria-hidden="true"></i>
                    <span class="offers-card__rating-badge-value">{{ number_format($rating, 1) }}</span>
                    @if($reviewCount > 0)
                        <span class="offers-card__rating-badge-count">({{ $reviewCount }})</span>
                    @endif
                </div>
            @endif
        </div>
    </div>

    <div class="offers-card__main">
        <div class="offers-card__headline">
            <div class="offers-card__headline-row">
                <h3 class="offers-card__title">
                    <a href="{{ $card['url'] }}" title="{{ $card['title'] }}">{{ $card['title'] }}</a>
                </h3>
                @if($rating)
                    <div
                        class="offers-card__score"
                        title="{{ trans_choice('offers.reviews_count', $reviewCount, ['count' => $reviewCount]) }}"
                        aria-label="{{ number_format($rating, 1) }}{{ $reviewCount > 0 ? ', '.trans_choice('offers.reviews_count', $reviewCount, ['count' => $reviewCount]) : '' }}"
                    >
                        <span class="offers-card__score-value">{{ number_format($rating, 1) }}</span>
                        @if($reviewCount > 0)
                            <span class="offers-card__score-meta">({{ $reviewCount }})</span>
                        @endif
                    </div>
                @endif
            </div>
            <div class="offers-card__meta-row">
                @if(!empty($card['location']))
                    <p class="offers-card__location">
                        <i class="fas fa-map-marker-alt" aria-hidden="true"></i>
                        <span>{{ $card['location'] }}</span>
                    </p>
                @endif
                @if($verified)
                    <span class="offers-card__verified">
                        <i class="fas fa-check-circle" aria-hidden="true"></i>
                        {{ __('vacations.verified_short') }}
                    </span>
                @endif
            </div>
        </div>

        @if(!empty($specs))
            <ul class="offers-card__specs" data-offers-card-specs>
                @foreach($specs as $spec)
                    <li class="offers-card__spec offers-card__spec--{{ $spec['key'] }}">
                        <span class="offers-card__spec-icon" aria-hidden="true">
                            <img src="{{ $spec['icon'] }}" width="16" height="16" alt="">
                        </span>
                        <span class="offers-card__spec-label">{{ $spec['label'] }}</span>
                    </li>
                @endforeach
            </ul>
        @endif

        @if(!empty($visibleFish))
            <div class="offers-card__tags" data-offers-card-tags>
                @foreach($visibleFish as $tag)
                    <span class="offers-card__tag">{{ $tag }}</span>
                @endforeach
                @if($fishExtra > 0)
                    <a href="{{ $card['url'] }}" class="offers-card__tag offers-card__tag--more">
                        +{{ $fishExtra }} {{ __('vacations.more') }}
                    </a>
                @endif
            </div>
        @endif

        @if(!empty($included))
            <div class="offers-card__included-block">
                <p class="offers-card__section-label">{{ $includedTitle }}</p>
                <ul class="offers-card__included">
                    @foreach($included as $item)
                        <li>
                            <i class="fas fa-check-circle" aria-hidden="true"></i>
                            <span>{{ Str::limit($item, 36) }}</span>
                        </li>
                    @endforeach
                    @if($includedExtra > 0)
                        <li class="offers-card__included-more">
                            +{{ $includedExtra }} {{ __('vacations.more') }}
                        </li>
                    @endif
                </ul>
            </div>
        @endif

        @if(!empty($availability))
            <ul class="offers-card__availability" data-offers-card-availability>
                @foreach($availability as $item)
                    <li @class([
                        'offers-card__availability-item',
                        'is-available' => ! empty($item['available']),
                        'is-unavailable' => empty($item['available']),
                    ])>
                        <i
                            @class([
                                'fas',
                                'fa-check-circle' => ! empty($item['available']),
                                'fa-times-circle' => empty($item['available']),
                            ])
                            aria-hidden="true"
                        ></i>
                        <span>{{ $item['label'] ?? '' }}</span>
                    </li>
                @endforeach
            </ul>
        @endif
    </div>

    <aside class="offers-card__aside">
        @if($priceDisplay)
            <div class="offers-card__price{{ $priceNote ? ' offers-card__price--guest-total' : '' }}">
                @if($pricePrefix)
                    <span class="offers-card__price-prefix">{{ $pricePrefix }}</span>
                @endif
                <span class="offers-card__price-amount">{{ $priceDisplay }}</span>
                @if($priceSuffix)
                    <span class="offers-card__price-suffix">{{ $priceSuffix }}</span>
                @endif
                @if($priceNote)
                    <span class="offers-card__price-note">{{ $priceNote }}</span>
                @endif
            </div>
        @endif

        <a href="{{ $card['url'] }}" class="offers-card__cta offers-card__cta--{{ $type }}">
            {{ $cta }}
        </a>
    </aside>

    @if($galleryCount > 0)
        <div
            class="vacation-gallery-modal offers-gallery-modal offers-gallery-modal--{{ $type }}"
            data-vacation-modal="{{ $galleryId }}"
            role="dialog"
            aria-modal="true"
            aria-label="{{ $card['title'] }}"
        >
            <div class="offers-gallery-modal__shell">
                <div class="offers-gallery-modal__top">
                    <div class="offers-gallery-modal__top-left">
                        <span class="offers-gallery-modal__badge">{{ $badge }}</span>
                        @if($galleryCount > 1)
                            <span class="offers-gallery-modal__counter vacation-gallery-modal__counter">
                                <span class="vacation-gallery-modal__current">1</span>
                                <span aria-hidden="true">/</span>
                                <span class="vacation-gallery-modal__total">{{ $galleryCount }}</span>
                            </span>
                        @endif
                    </div>
                    <div class="offers-gallery-modal__top-right">
                        @if($galleryCount > 1)
                            <div class="offers-gallery-modal__top-nav">
                                <button
                                    type="button"
                                    class="offers-gallery-modal__chip-nav"
                                    data-offers-gallery-modal-prev
                                    aria-label="{{ __('vacations.gallery_prev') }}"
                                >&#10094;</button>
                                <button
                                    type="button"
                                    class="offers-gallery-modal__chip-nav"
                                    data-offers-gallery-modal-next
                                    aria-label="{{ __('vacations.gallery_next') }}"
                                >&#10095;</button>
                            </div>
                        @endif
                        <button
                            type="button"
                            class="offers-gallery-modal__close"
                            data-offers-gallery-modal-close
                            aria-label="{{ __('vacations.gallery_close') }}"
                        >&times;</button>
                    </div>
                </div>

                <div class="offers-gallery-modal__stage" data-offers-gallery-stage>
                    <div class="offers-gallery-modal__frame">
                        <div
                            class="offers-gallery-modal__loader"
                            data-offers-gallery-loader
                            hidden
                            aria-hidden="true"
                        >
                            <span class="offers-gallery-modal__spinner" aria-hidden="true"></span>
                            <span class="offers-gallery-modal__loader-text">{{ __('vacations.loading') }}</span>
                        </div>
                        <img
                            class="offers-gallery-modal__image is-ready"
                            data-offers-gallery-modal-image
                            src="{{ $galleryFull[0] }}"
                            alt="{{ $card['title'] }}"
                            draggable="false"
                            decoding="async"
                        >
                        @if($galleryCount > 1)
                            <button
                                type="button"
                                class="offers-gallery-modal__nav offers-gallery-modal__nav--prev"
                                data-offers-gallery-modal-prev
                                aria-label="{{ __('vacations.gallery_prev') }}"
                            >&#10094;</button>
                            <button
                                type="button"
                                class="offers-gallery-modal__nav offers-gallery-modal__nav--next"
                                data-offers-gallery-modal-next
                                aria-label="{{ __('vacations.gallery_next') }}"
                            >&#10095;</button>
                        @endif
                    </div>
                </div>

                <div class="offers-gallery-modal__dock">
                    <div class="offers-gallery-modal__info">
                        <h3 class="offers-gallery-modal__title">{{ $card['title'] }}</h3>
                        <div class="offers-gallery-modal__meta">
                            @if(!empty($card['location']))
                                <span class="offers-gallery-modal__location">
                                    <i class="fas fa-map-marker-alt" aria-hidden="true"></i>
                                    {{ $card['location'] }}
                                </span>
                            @endif
                            @if($rating)
                                <span
                                    class="offers-gallery-modal__score"
                                    title="{{ trans_choice('offers.reviews_count', $reviewCount, ['count' => $reviewCount]) }}"
                                >
                                    <span class="offers-gallery-modal__score-value">{{ number_format($rating, 1) }}</span>
                                    @if($reviewCount > 0)
                                        <span class="offers-gallery-modal__score-meta">({{ $reviewCount }})</span>
                                    @endif
                                </span>
                            @endif
                        </div>
                        @if(!empty($specs))
                            <ul class="offers-gallery-modal__specs">
                                @foreach(array_slice($specs, 0, 3) as $spec)
                                    <li>{{ $spec['label'] }}</li>
                                @endforeach
                            </ul>
                        @endif
                    </div>
                    <div class="offers-gallery-modal__actions">
                        @if($priceDisplay)
                            <div class="offers-gallery-modal__price{{ $priceNote ? ' offers-gallery-modal__price--guest-total' : '' }}">
                                @if($pricePrefix)
                                    <span class="offers-gallery-modal__price-prefix">{{ $pricePrefix }}</span>
                                @endif
                                <span class="offers-gallery-modal__price-amount">{{ $priceDisplay }}</span>
                                @if($priceSuffix)
                                    <span class="offers-gallery-modal__price-suffix">{{ $priceSuffix }}</span>
                                @endif
                                @if($priceNote)
                                    <span class="offers-gallery-modal__price-note">{{ $priceNote }}</span>
                                @endif
                            </div>
                        @endif
                        <a href="{{ $card['url'] }}" class="offers-gallery-modal__cta">
                            {{ $cta }}
                        </a>
                    </div>
                </div>
            </div>
        </div>
    @endif
</article>
