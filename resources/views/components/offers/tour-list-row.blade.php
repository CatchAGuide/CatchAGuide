@props(['card'])

@php
    use Illuminate\Support\Str;

    $galleryImages = array_values(array_filter($card['gallery_images'] ?? [$card['image'] ?? '']));
    $galleryFull = array_map(function ($img) {
        return str_starts_with((string) $img, 'http') ? $img : media_url($img);
    }, $galleryImages);
    $galleryCount = count($galleryFull);
    $galleryId = 'offer-tour-'.($card['id'] ?? uniqid());
    $included = $card['listing_included'] ?? [];
    $includedExtra = (int) ($card['listing_included_extra'] ?? 0);
    $fishTags = $card['target_fish_tags'] ?? [];
@endphp

<article
    class="offers-tour-list-row guiding-list-item offers-list-row--tour"
    data-offer-type="tour"
    data-analytics-offer-card
>
    <div class="row m-0 mb-3">
        <div class="col-md-12">
            <div class="row p-2 border shadow-sm bg-white rounded guiding-card-wrapper offers-tour-list-row__card">
                <div class="col-12 col-md-4 mt-1 p-0 position-relative">
                    <div
                        class="offers-tour-list-row__gallery"
                        data-vacation-gallery="{{ $galleryId }}"
                        data-gallery-images='@json($galleryFull)'
                    >
                        @if($galleryCount > 0)
                            <img
                                src="{{ $galleryFull[0] }}"
                                alt="{{ $card['title'] }}"
                                class="offers-tour-list-row__img"
                                data-vacation-gallery-image
                                data-vacation-open-modal
                                loading="lazy"
                            >
                            @if($galleryCount > 1)
                                <div class="offers-tour-list-row__counter" data-vacation-image-counter>1/{{ $galleryCount }}</div>
                            @endif
                        @else
                            <img src="{{ asset('images/placeholder_guide.jpg') }}" alt="" class="offers-tour-list-row__img" loading="lazy">
                        @endif
                        <span class="offers-list-row__badge offers-list-row__badge--tour">{{ $card['badge'] ?? __('offers.badge_tour') }}</span>
                    </div>
                </div>

                <div class="guiding-item-desc col-12 col-md-8 p-2 px-md-3 pt-md-2">
                    <div class="guidings-item">
                        <div class="guidings-item-title">
                            <h3 class="fw-bolder text-truncate h5 mb-1">
                                <a href="{{ $card['url'] }}" class="text-decoration-none text-dark">{{ Str::limit($card['title'], 70) }}</a>
                            </h3>
                            @if(!empty($card['location']))
                                <span class="truncate"><i class="fas fa-map-marker-alt me-2"></i>{{ $card['location'] }}</span>
                            @endif
                        </div>
                        @if(!empty($card['rating']))
                            <div class="ave-reviews-row">
                                <div class="ratings-score">
                                    <span class="rating-value">{{ number_format((float) $card['rating'], 1) }}</span>
                                </div>
                                <span class="mb-1">({{ (int) ($card['review_count'] ?? 0) }} reviews)</span>
                            </div>
                        @endif
                    </div>

                    @if(!empty($fishTags))
                        <div class="gc-mob-fish-tags d-flex d-md-none mb-2">
                            @foreach(array_slice($fishTags, 0, 4) as $fishName)
                                <span class="gc-mob-fish-tag">{{ $fishName }}</span>
                            @endforeach
                        </div>
                    @endif

                    <div class="guidings-item-icon">
                        @if(!empty($card['duration_label']))
                            <div class="guidings-icon-container">
                                <img src="{{ asset('assets/images/icons/clock-new.svg') }}" height="20" width="20" alt="" />
                                <div>{{ $card['duration_label'] }}</div>
                            </div>
                        @endif
                        @if(!empty($card['guests_label']))
                            <div class="guidings-icon-container">
                                <img src="{{ asset('assets/images/icons/user-new.svg') }}" height="20" width="20" alt="" />
                                <div>{{ $card['guests_label'] }}</div>
                            </div>
                        @endif
                        @if(!empty($card['water_label']))
                            <div class="guidings-icon-container">
                                <img src="{{ asset('assets/images/icons/pelagic.png') }}" height="20" width="20" alt="" />
                                <div>{{ $card['water_label'] }}</div>
                            </div>
                        @endif
                        @if(!empty($card['boat_label']))
                            <div class="guidings-icon-container">
                                <img src="{{ asset('assets/images/icons/fishing-tool-new.svg') }}" height="20" width="20" alt="" />
                                <div>{{ $card['boat_label'] }}</div>
                            </div>
                        @endif
                    </div>

                    <div class="inclusions-price">
                        <div class="guidings-inclusions-container">
                            @if(!empty($included))
                                <div class="guidings-included">
                                    <strong>{{ $card['whats_included_title'] ?? __('guidings.Whats_Included') }}</strong>
                                    <div class="inclusions-list">
                                        @foreach($included as $item)
                                            <span class="inclusion-item"><i class="fa fa-check"></i>{{ $item }}</span>
                                        @endforeach
                                        @if($includedExtra > 0)
                                            <span class="inclusion-item">+{{ $includedExtra }} more</span>
                                        @endif
                                    </div>
                                </div>
                            @endif
                        </div>
                        <div class="guiding-item-price">
                            @if(!empty($card['listing_price_display']))
                                <h5 class="mr-1 fw-bold text-end mb-2">
                                    <span class="p-1">{{ $card['listing_price_prefix'] ?? __('message.from') }} {{ $card['listing_price_display'] }} {{ $card['listing_price_suffix'] ?? 'p.P.' }}</span>
                                </h5>
                            @endif
                            <div class="text-end">
                                <a href="{{ $card['url'] }}" class="btn btn-sm offers-list-row__cta offers-list-row__cta--tour">
                                    {{ $card['listing_cta'] ?? __('offers.cta_tour') }}
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
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
</article>
