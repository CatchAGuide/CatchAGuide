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

    $metaItems = array_values(array_filter([
        $card['duration_label'] ?? $card['duration_pill'] ?? null,
        $card['guests_label'] ?? null,
        $card['boat_label'] ?? null,
        $card['water_label'] ?? null,
    ]));

    $badge = $card['badge'] ?? __('offers.badge_'.$type);
    $cta = $card['listing_cta'] ?? $card['cta'] ?? __('offers.see_details');
    $pricePrefix = $card['listing_price_prefix'] ?? __('vacations.starting_from_label');
    $priceDisplay = $card['listing_price_display'] ?? null;
    $priceSuffix = $card['listing_price_suffix'] ?? '';
    $verified = $type !== 'tour' || ! empty($card['verified']);
    if ($type === 'tour') {
        $verified = false;
    }
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
                    <span class="offers-card__counter" data-vacation-image-counter>1/{{ $galleryCount }}</span>
                @endif
            @else
                <img src="{{ asset('images/placeholder_guide.jpg') }}" alt="" class="offers-card__img" loading="lazy">
            @endif

            <span class="offers-card__badge offers-card__badge--{{ $type }}">{{ $badge }}</span>
        </div>
    </div>

    <div class="offers-card__body">
        <div class="offers-card__top">
            <div class="offers-card__headline">
                <h3 class="offers-card__title">
                    <a href="{{ $card['url'] }}">{{ Str::limit($card['title'], 72) }}</a>
                </h3>
                @if(!empty($card['location']))
                    <p class="offers-card__location">
                        <i class="fas fa-map-marker-alt" aria-hidden="true"></i>
                        <span>{{ $card['location'] }}</span>
                    </p>
                @endif
            </div>

            <div class="offers-card__status">
                @if(!empty($card['rating']))
                    <div class="offers-card__rating">
                        <span class="offers-card__rating-score">{{ number_format((float) $card['rating'], 1) }}</span>
                        @if(!empty($card['review_count']))
                            <span class="offers-card__rating-count">({{ (int) $card['review_count'] }})</span>
                        @endif
                    </div>
                @elseif($verified)
                    <span class="offers-card__verified">
                        <i class="fas fa-check-circle" aria-hidden="true"></i>
                        {{ __('vacations.verified_short') }}
                    </span>
                @endif
            </div>
        </div>

        @if($type === 'tour' && !empty($metaItems))
            <ul class="offers-card__meta">
                @foreach($metaItems as $item)
                    <li>{{ $item }}</li>
                @endforeach
            </ul>
        @elseif(!empty($visibleFish))
            <div class="offers-card__tags">
                @foreach($visibleFish as $tag)
                    <span class="offers-card__tag">{{ $tag }}</span>
                @endforeach
                @if($fishExtra > 0)
                    <span class="offers-card__tag offers-card__tag--more">+{{ $fishExtra }}</span>
                @endif
            </div>
        @endif

        @if(!empty($included))
            <ul class="offers-card__included">
                @foreach($included as $item)
                    <li>
                        <i class="fas fa-check" aria-hidden="true"></i>
                        <span>{{ Str::limit($item, 64) }}</span>
                    </li>
                @endforeach
                @if($includedExtra > 0)
                    <li class="offers-card__included-more">+{{ $includedExtra }} {{ __('vacations.more') }}</li>
                @endif
            </ul>
        @endif

        <div class="offers-card__footer">
            <div class="offers-card__price">
                @if($priceDisplay)
                    <span class="offers-card__price-prefix">{{ $pricePrefix }}</span>
                    <span class="offers-card__price-value">
                        {{ $priceDisplay }}
                        @if($priceSuffix)
                            <span class="offers-card__price-suffix">{{ $priceSuffix }}</span>
                        @endif
                    </span>
                @endif
            </div>
            <a href="{{ $card['url'] }}" class="offers-card__cta offers-card__cta--{{ $type }}">
                {{ __('offers.see_details') }}
            </a>
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
</article>
