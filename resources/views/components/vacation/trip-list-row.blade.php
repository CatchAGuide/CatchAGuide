@props(['card'])

@php
    use Illuminate\Support\Str;

    $galleryImages = array_values(array_filter($card['gallery_images'] ?? [$card['image'] ?? '']));
    $galleryFull = array_map(function ($img) {
        return str_starts_with((string) $img, 'http') ? $img : media_url($img);
    }, $galleryImages);
    $galleryCount = count($galleryImages);
    $galleryId = 'trip-' . ($card['id'] ?? uniqid());

    $targetFishTags = vacation_fish_tags($card['target_fish_tags'] ?? []);
    $visibleFishTags = array_slice($targetFishTags, 0, 3);
    $targetFishExtra = (int) ($card['target_fish_tags_extra'] ?? max(0, count($targetFishTags) - count($visibleFishTags)));

    $included = $card['listing_included'] ?? ($card['facilities'] ?? []);
@endphp

<article class="vacation-trip-list-card guiding-list-item" data-analytics-vacation-card data-pillar="trip">
    <div class="vacation-trip-list-card__inner">
        <div class="vacation-trip-list-card__media">
            <div
                class="vacation-trip-list-card__gallery"
                data-vacation-gallery="{{ $galleryId }}"
                data-gallery-images='@json($galleryFull)'
            >
                @if($galleryCount > 0)
                    <img
                        src="{{ $galleryImages[0] }}"
                        alt="{{ $card['title'] }}"
                        data-vacation-gallery-image
                        data-vacation-open-modal
                        class="vacation-trip-list-card__img"
                        loading="lazy"
                    />
                    @if($galleryCount > 1)
                        <div class="vacation-trip-list-card__counter" data-vacation-image-counter>1/{{ $galleryCount }}</div>
                    @endif
                @else
                    <img
                        src="{{ asset('images/placeholder_guide.jpg') }}"
                        alt="{{ $card['title'] }}"
                        class="vacation-trip-list-card__img"
                        loading="lazy"
                    />
                @endif

                <x-vacation.partials.image-pillar-badge pillar="trip" :badge="$card['badge'] ?? null" />
            </div>
        </div>

        <div class="vacation-trip-list-card__main">
            <div class="vacation-trip-list-card__headline">
                <h3 class="vacation-trip-list-card__title">
                    <a href="{{ $card['url'] }}">{{ $card['title'] }}</a>
                </h3>

                @if(!empty($card['location']))
                    <p class="vacation-trip-list-card__location">
                        <i class="fas fa-map-marker-alt" aria-hidden="true"></i>
                        <span>{{ $card['location'] }}</span>
                    </p>
                @endif
            </div>

            @if(!empty($visibleFishTags))
                <div class="vacation-trip-list-card__tags">
                    @foreach($visibleFishTags as $tag)
                        <span class="vacation-trip-list-card__tag">{{ $tag }}</span>
                    @endforeach
                    @if($targetFishExtra > 0)
                        <a href="{{ $card['url'] }}" class="vacation-trip-list-card__more-link">
                            +{{ $targetFishExtra }} @lang('vacations.more')
                        </a>
                    @endif
                </div>
            @endif

            @if(!empty($included))
                <ul class="vacation-trip-list-card__included">
                    @foreach($included as $item)
                        <li class="vacation-trip-list-card__included-item" title="{{ $item }}">
                            <i class="fas fa-check-circle" aria-hidden="true"></i>
                            <span>{{ Str::limit($item, 72) }}</span>
                        </li>
                    @endforeach
                </ul>
            @endif

            @if(!empty($card['duration_pill']))
                <div class="vacation-trip-list-card__duration">
                    <i class="far fa-clock" aria-hidden="true"></i>
                    <span>
                        <span class="vacation-trip-list-card__duration-label">{{ __('vacations.duration_label') }}:</span>
                        {{ $card['duration_pill'] }}
                    </span>
                </div>
            @endif
        </div>

        <div class="vacation-trip-list-card__aside">
            @if(!empty($card['listing_price_display']))
                <div class="vacation-trip-list-card__price">
                    <span class="vacation-trip-list-card__price-prefix">{{ $card['listing_price_prefix'] ?? __('vacations.starting_from_label') }}</span>
                    <span class="vacation-trip-list-card__price-line">
                        <strong>{{ $card['listing_price_display'] }}</strong>
                        <span class="vacation-trip-list-card__price-suffix">{{ $card['listing_price_suffix'] ?? __('vacations.per_person_short') }}</span>
                    </span>
                </div>
            @endif

            <div class="vacation-trip-list-card__trust">
                <span class="vacation-trip-list-card__trust-item">
                    <i class="fas fa-check-circle" aria-hidden="true"></i>
                    {{ __('vacations.verified_short') }}
                </span>
                <span class="vacation-trip-list-card__trust-item">
                    <i class="fas fa-calendar-check" aria-hidden="true"></i>
                    {{ __('vacations.cancel_free') }}
                </span>
            </div>

            <a href="{{ $card['url'] }}" class="vacation-trip-list-card__cta">
                {{ __('vacations.see_more') }}
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
