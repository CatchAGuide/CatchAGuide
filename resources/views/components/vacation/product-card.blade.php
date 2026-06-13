@props(['card', 'layout' => 'grid'])

@php
    $isRow = $layout === 'row' || ($card['layout'] ?? 'grid') === 'row';
    $pillar = $card['type'] ?? 'trip';
@endphp

<article
    class="vacation-product-card vacation-product-card--{{ $card['badge_class'] ?? $pillar }}{{ $isRow ? ' vacation-product-card--row' : '' }}"
    data-analytics-vacation-card
    data-pillar="{{ $pillar }}"
>
    <a href="{{ $card['url'] }}" class="vacation-product-card__media-link" aria-label="{{ $card['title'] }}">
        <div class="vacation-product-card__media">
            <img src="{{ $card['image'] }}" alt="{{ $card['title'] }}" loading="lazy">
            <span class="vacation-product-card__badge">{{ $card['badge'] }}</span>
        </div>
    </a>

    <div class="vacation-product-card__body">
        <h3 class="vacation-product-card__title">
            <a href="{{ $card['url'] }}">{{ $card['title'] }}</a>
        </h3>

        @if(!empty($card['location']))
            <p class="vacation-product-card__location">
                <i class="fas fa-map-marker-alt" aria-hidden="true"></i>
                {{ $card['location'] }}
            </p>
        @endif

        @if(!empty($card['meta_line']))
            <p class="vacation-product-card__meta">{{ $card['meta_line'] }}</p>
        @endif

        <div class="vacation-product-card__pills">
            @if(!empty($card['duration_pill']))
                <span class="vacation-product-card__pill vacation-product-card__pill--duration">{{ $card['duration_pill'] }}</span>
            @endif
            @foreach($card['addon_pills'] ?? [] as $pill)
                <span class="vacation-product-card__pill vacation-product-card__pill--addon">{{ $pill }}</span>
            @endforeach
        </div>

        @if(!empty($card['trust']['label']))
            <p class="vacation-product-card__trust">
                <i class="fas fa-star" aria-hidden="true"></i>
                {{ $card['trust']['label'] }}
            </p>
        @endif

        <div class="vacation-product-card__footer">
            @if(!empty($card['price_label']))
                <p class="vacation-product-card__price">{{ $card['price_label'] }}</p>
            @endif
            <a href="{{ $card['url'] }}" class="vacation-product-card__cta vacation-product-card__cta--{{ $card['cta_class'] ?? $pillar }}">
                {{ $card['cta'] }}
            </a>
        </div>
    </div>
</article>
