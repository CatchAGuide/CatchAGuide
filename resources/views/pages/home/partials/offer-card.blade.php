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
    <a href="{{ $card['url'] }}" class="cag-home-offer__media">
        <img
            src="{{ $image }}"
            alt="{{ $card['title'] }}"
            loading="lazy"
            width="400"
            height="260"
            onerror="this.onerror=null;this.src='{{ $placeholder }}';"
        >
        <span class="cag-home-offer__badge cag-home-offer__badge--{{ $type }}">{{ $card['badge'] ?? __('homepage.offer_type_' . $type) }}</span>
    </a>
    <div class="cag-home-offer__body">
        <h3 class="cag-home-offer__title">
            <a href="{{ $card['url'] }}">{{ $card['title'] }}</a>
        </h3>
        @if(!empty($card['location']))
            <p class="cag-home-offer__location">
                <i class="fas fa-map-marker-alt" aria-hidden="true"></i>
                {{ $card['location'] }}
            </p>
        @endif
        <div class="cag-home-offer__footer">
            @if(!empty($card['price_amount']))
                <div class="cag-home-offer__price">
                    <span>{{ __('vacations.from_label') }}</span>
                    <strong>{{ $card['price_amount'] }}</strong>
                    @if(!empty($card['price_unit']))
                        <small>/ {{ $card['price_unit'] }}</small>
                    @endif
                </div>
            @endif
            <a href="{{ $card['url'] }}" class="cag-home-btn cag-home-btn--coral cag-home-btn--sm">
                {{ __('homepage.offer_details') }}
            </a>
        </div>
    </div>
</article>
