@props([
    'title' => '',
    'url' => '#',
    'location' => '',
    'image' => '',
    'price' => null,
    'showPrice' => true,
])

<div class="cag-map-popup__card">
    @if($image)
        <img class="cag-map-popup__image" src="{{ $image }}" alt="{{ $title }}">
    @endif
    <div class="cag-map-popup__body">
        <a class="text-decoration-none" id="guiding-link-redirection" href="{{ $url }}">
            <h5 class="cag-map-popup__title">{{ $title }}</h5>
        </a>
        @if($location)
            <div class="cag-map-popup__location">{{ $location }}</div>
        @endif
        @if($showPrice && $price !== null && $price !== '')
            <div class="cag-map-popup__price">
                <span class="fw-bold">ab {{ $price }}€</span> p.P.
            </div>
        @endif
    </div>
</div>
