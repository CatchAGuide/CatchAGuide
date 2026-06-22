@php
    $pillar = $pillar ?? ($card['type'] ?? 'trip');
    $allSliderTags = vacation_fish_tags($card['slider_tags'] ?? []);
    $sliderTags = array_slice($allSliderTags, 0, 2);
    $tagsExtra = max(0, count($allSliderTags) - count($sliderTags));
    $availability = array_map(
        fn ($item) => vacation_availability_item($item),
        $card['slider_availability'] ?? []
    );
    $galleryId = ($pillar) . '-' . ($card['id'] ?? uniqid());
@endphp

<article
    class="vacation-slider-card vacation-slider-card--{{ $pillar }}"
    data-analytics-vacation-card
    data-pillar="{{ $pillar }}"
>
    <div class="vacation-slider-card__media">
        <x-vacation.card-gallery
            :images="$card['gallery_images'] ?? [$card['image'] ?? '']"
            :alt="$card['title']"
            :gallery-id="$galleryId"
            :url="$card['url']"
            :show-nav="false"
            gallery-class="vacation-slider-card__gallery"
        />
        @if(!empty($card['badge']))
            <span class="vacation-slider-card__badge">{{ $card['badge'] }}</span>
        @endif
    </div>

    <div class="vacation-slider-card__body">
        <h3 class="vacation-slider-card__title">
            <a href="{{ $card['url'] }}">{{ $card['title'] }}</a>
        </h3>

        @if(!empty($card['location']))
            <p class="vacation-slider-card__location">
                <i class="fas fa-map-marker-alt" aria-hidden="true"></i>
                <span>{{ $card['location'] }}</span>
            </p>
        @endif

        @if(!empty($sliderTags))
            <div class="vacation-slider-card__tags-row">
                <img
                    src="{{ asset('assets/images/icons/fish.png') }}"
                    width="14"
                    height="14"
                    alt=""
                    class="vacation-slider-card__tags-icon"
                />
                <div class="vacation-slider-card__tags">
                    @foreach($sliderTags as $tag)
                        <span class="vacation-slider-card__tag">{{ $tag }}</span>
                    @endforeach
                    @if($tagsExtra > 0)
                        <span class="vacation-slider-card__tag vacation-slider-card__tag--more">+{{ $tagsExtra }}</span>
                    @endif
                </div>
            </div>
        @endif

        @if($pillar === 'trip' && !empty($card['duration_pill']))
            <div class="vacation-slider-card__meta">
                <span class="vacation-slider-card__meta-pill">
                    {{ __('vacations.duration_chip', ['value' => $card['duration_pill']]) }}
                </span>
            </div>
        @endif

        @if($pillar === 'camp' && !empty($availability))
            <div class="vacation-slider-card__available">
                <div class="vacation-slider-card__available-heading">
                    <img
                        src="{{ asset('assets/images/icons/check.png') }}"
                        width="14"
                        height="14"
                        alt=""
                        class="vacation-slider-card__available-icon"
                    />
                    <span class="vacation-slider-card__available-label">{{ __('vacations.availability_label') }}:</span>
                </div>
                <div class="vacation-slider-card__available-items">
                    @foreach($availability as $item)
                        <span class="vacation-slider-card__available-item">
                            <img
                                src="{{ asset($item['icon']) }}"
                                width="14"
                                height="14"
                                alt=""
                                class="vacation-slider-card__available-item-icon"
                            />
                            <span>{{ $item['label'] }}</span>
                        </span>
                    @endforeach
                </div>
            </div>
        @endif

        <div class="vacation-slider-card__footer">
            @if(!empty($card['price_amount']))
                <div class="vacation-slider-card__price">
                    <span class="vacation-slider-card__price-from">{{ __('vacations.from_label') }}</span>
                    <span class="vacation-slider-card__price-line">
                        <strong>{{ $card['price_amount'] }}</strong>
                        @if(!empty($card['price_unit']))
                            <span class="vacation-slider-card__price-unit">/ {{ $card['price_unit'] }}</span>
                        @endif
                    </span>
                </div>
            @endif

            <a
                href="{{ $card['url'] }}"
                class="vacation-slider-card__cta vacation-slider-card__cta--{{ $card['cta_class'] ?? $pillar }}"
            >
                {{ $card['slider_cta'] ?? $card['cta'] }}
            </a>
        </div>
    </div>
</article>
