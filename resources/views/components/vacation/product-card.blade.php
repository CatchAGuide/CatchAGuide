@props(['card', 'layout' => 'grid', 'variant' => 'default'])

@php
    use Illuminate\Support\Str;

    $isRow = $layout === 'row' || ($card['layout'] ?? 'grid') === 'row';
    $isCompact = $variant === 'compact';
    $pillar = $card['type'] ?? 'trip';

    $compactChips = [];
    if ($isCompact) {
        foreach (array_slice($card['traits'] ?? [], 0, 2) as $trait) {
            $compactChips[] = Str::limit($trait['value'], 28);
        }
        if (! empty($card['duration_pill'])) {
            $compactChips[] = $card['duration_pill'];
        }
        foreach (array_slice($card['addon_pills'] ?? [], 0, 1) as $pill) {
            $compactChips[] = $pill;
        }
        if (count($compactChips) < 3) {
            foreach (array_slice($card['facilities'] ?? [], 0, 3 - count($compactChips)) as $facility) {
                $compactChips[] = Str::limit($facility, 22);
            }
        }
        $compactChips = array_slice(array_unique($compactChips), 0, 3);
    }
@endphp

<article
    class="vacation-product-card vacation-product-card--{{ $card['badge_class'] ?? $pillar }}{{ $isRow ? ' vacation-product-card--row' : '' }}{{ $isCompact ? ' vacation-product-card--compact' : '' }}"
    data-analytics-vacation-card
    data-pillar="{{ $pillar }}"
>
    <div class="vacation-product-card__media">
        <x-vacation.card-gallery
            :images="$card['gallery_images'] ?? [$card['image'] ?? '']"
            :alt="$card['title']"
            :gallery-id="($card['type'] ?? 'listing') . '-' . ($card['id'] ?? uniqid())"
            :url="$card['url']"
        />
        <x-vacation.partials.image-pillar-badge
            :pillar="$pillar"
            :badge="$card['badge'] ?? null"
        />
    </div>

    <div class="vacation-product-card__body">
        <h3 class="vacation-product-card__title">
            <a href="{{ $card['url'] }}">{{ $card['title'] }}</a>
        </h3>

        @if(!empty($card['location']))
            <p class="vacation-product-card__location">
                <i class="fas fa-map-marker-alt" aria-hidden="true"></i>
                <span>{{ $card['location'] }}</span>
            </p>
        @endif

        @if($isCompact)
            @if(!empty($compactChips))
                <div class="vacation-product-card__chips">
                    @foreach($compactChips as $chip)
                        <span class="vacation-product-card__chip">{{ $chip }}</span>
                    @endforeach
                </div>
            @endif

            @if(!empty($card['trust']['rating']))
                <p class="vacation-product-card__trust vacation-product-card__trust--compact">
                    <i class="fas fa-star" aria-hidden="true"></i>
                    {{ number_format((float) $card['trust']['rating'], 1) }}★
                    @if(!empty($card['trust']['count']))
                        <span>({{ $card['trust']['count'] }})</span>
                    @endif
                </p>
            @endif
        @else
            @if(!empty($card['feature_badges']))
                <div class="vacation-product-card__features">
                    @foreach($card['feature_badges'] as $badge)
                        <span class="vacation-product-card__feature">
                            <i class="fas {{ $badge['icon'] }}" aria-hidden="true"></i>
                            {{ $badge['label'] }}
                        </span>
                    @endforeach
                </div>
            @endif

            @if(!empty($card['traits']))
                <ul class="vacation-product-card__traits">
                    @foreach($card['traits'] as $trait)
                        <li class="vacation-product-card__trait">
                            <span class="vacation-product-card__trait-label">{{ $trait['label'] }}:</span>
                            <span class="vacation-product-card__trait-value">{{ $trait['value'] }}</span>
                        </li>
                    @endforeach
                </ul>
            @elseif(!empty($card['meta_line']))
                <p class="vacation-product-card__meta">{{ $card['meta_line'] }}</p>
            @endif

            @if(!empty($card['facilities']))
                <div class="vacation-product-card__included">
                    <span class="vacation-product-card__included-label">{{ __('vacations.included_label') }}:</span>
                    @foreach($card['facilities'] as $facility)
                        <span class="vacation-product-card__included-item">
                            <i class="fas fa-check" aria-hidden="true"></i>
                            {{ $facility }}
                        </span>
                    @endforeach
                </div>
            @endif

            @if(!empty($card['duration_pill']) || !empty($card['addon_pills']))
                <div class="vacation-product-card__pills">
                    @if(!empty($card['duration_pill']))
                        <span class="vacation-product-card__pill vacation-product-card__pill--duration">{{ $card['duration_pill'] }}</span>
                    @endif
                    @foreach($card['addon_pills'] ?? [] as $pill)
                        <span class="vacation-product-card__pill vacation-product-card__pill--addon">{{ $pill }}</span>
                    @endforeach
                </div>
            @endif

            @if(!empty($card['trust']['label']))
                <p class="vacation-product-card__trust">
                    <i class="fas fa-star" aria-hidden="true"></i>
                    {{ $card['trust']['label'] }}
                </p>
            @endif
        @endif

        <div class="vacation-product-card__footer">
            @if($isCompact ? !empty($card['compact_price_label'] ?? $card['price_label']) : !empty($card['price_label']))
                <p class="vacation-product-card__price">
                    {{ $isCompact ? ($card['compact_price_label'] ?? $card['price_label']) : $card['price_label'] }}
                </p>
            @endif
            <a href="{{ $card['url'] }}" class="vacation-product-card__cta vacation-product-card__cta--{{ $card['cta_class'] ?? $pillar }}">
                {{ $card['cta'] }}
            </a>
        </div>
    </div>
</article>
