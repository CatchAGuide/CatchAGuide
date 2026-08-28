@php
    $type = $type ?? ($card['type'] ?? 'tour');
    $placeholder = asset('images/placeholder_guide.jpg');
    $image = $card['image'] ?? null;
    if (! filled($image) && ! empty($card['gallery_images'][0])) {
        $image = $card['gallery_images'][0];
    }
    $image = $image ?: $placeholder;
@endphp
<article
    class="cag-home-offer cag-home-offer--{{ $type }} cag-reveal__item"
    role="listitem"
    style="--reveal-i: {{ (int) ($revealIndex ?? 0) }}"
    data-home-analytics="homepage_mixed_offer_click"
    data-product-type="{{ $type }}"
>
    <a href="{{ $card['url'] }}" class="cag-home-offer__media cag-home-ph">
        <img
            src="{{ $image }}"
            alt="{{ $card['title'] }}"
            loading="lazy"
            width="400"
            height="260"
            onerror="this.onerror=null;this.src='{{ $placeholder }}';"
        >
        <span class="cag-home-offer__badge cag-home-offer__badge--{{ $type }}">{{ $card['badge'] ?? __('homepage.offer_type_' . $type) }}</span>
        @if(!empty($card['requested_count']))
            {{-- <span class="cag-home-offer__requested">{{ __('homepage.landing_requested_badge', ['count' => $card['requested_count']]) }}</span> --}}
        @endif
        @if(!empty($card['is_new']) && empty($card['rating']))
            <span class="cag-home-offer__new-badge">{{ __('homepage.landing_card_new') }}</span>
        @endif
        @if(!empty($card['rating']))
            <span class="cag-home-offer__rating-badge d-flex d-md-none">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                    <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/>
                </svg>
                {{ $card['rating'] }}
                @if(!empty($card['review_count']))
                    <span class="cag-home-offer__rating-badge-count">({{ $card['review_count'] }})</span>
                @endif
            </span>
        @endif
    </a>
    <div class="cag-home-offer__body">
        <h3 class="cag-home-offer__title">
            <a href="{{ $card['url'] }}">{{ $card['title'] }}</a>
        </h3>
        @if(!empty($card['location']))
            <p class="cag-home-offer__location">
                @include('pages.home.partials.cag-icon', ['name' => 'pin', 'size' => 12, 'iconClass' => 'cag-home-offer__pin'])
                {{ $card['location'] }}
            </p>
        @endif
        @if(!empty($card['meta']))
            <p class="cag-home-offer__meta">{{ $card['meta'] }}</p>
        @endif
        @if(!empty($card['rating']))
            <p class="cag-home-offer__rating">★ {{ $card['rating'] }} <span>({{ $card['review_count'] }})</span></p>
        @endif
        <div class="cag-home-offer__footer">
            @if(!empty($card['price_amount']))
                <div class="cag-home-offer__price">
                    <span class="cag-home-offer__price-from">{{ __('vacations.from_label') }}</span>
                    <span class="cag-home-offer__price-line">
                        <strong>{{ $card['price_amount'] }}</strong>
                        @if(!empty($card['price_unit']))
                            <small>/ {{ $card['price_unit'] }}</small>
                        @endif
                    </span>
                </div>
            @endif
            <a href="{{ $card['url'] }}" class="cag-home-btn cag-home-btn--coral cag-home-btn--sm">
                <span class="d-none d-md-inline">{{ __('homepage.offer_details') }}</span>
                <span class="d-md-none">{{ __('homepage.offer_details_short') }}</span>
            </a>
        </div>
    </div>
</article>
