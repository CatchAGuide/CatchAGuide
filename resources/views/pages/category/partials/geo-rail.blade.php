@php
    $items = collect($items ?? []);
    $title = $title ?? '';
    $subtitle = $subtitle ?? '';
    $railKey = $railKey ?? 'geo';
@endphp
@if($items->isNotEmpty())
<section class="cag-home-species cag-dest-geo-rail" data-species-spotlight data-geo-rail="{{ $railKey }}">
    <div class="cag-home-section__header cag-home-species__header">
        <div class="cag-home-species__heading">
            <h2 class="cag-home-section__title">{{ $title }}</h2>
            @if($subtitle !== '')
                <p class="cag-home-species__subtitle">{{ $subtitle }}</p>
            @endif
        </div>
    </div>

    <div class="cag-home-species__viewport" data-species-viewport>
        <div class="cag-home-species__rail" role="list">
            @foreach($items as $item)
                <a
                    href="{{ $item['url'] }}"
                    class="cag-home-species__card"
                    role="listitem"
                    style="--species-i: {{ $loop->index }}"
                >
                    <img
                        class="cag-home-species__img"
                        src="{{ $item['thumbnail'] }}"
                        alt="{{ $item['name'] }}"
                        loading="lazy"
                        draggable="false"
                        width="320"
                        height="240"
                    >
                    <span class="cag-home-species__fade" aria-hidden="true"></span>
                    <span class="cag-home-species__shine" aria-hidden="true"></span>
                    <span class="cag-home-species__meta">
                        <span class="cag-home-species__name">{{ $item['name'] }}</span>
                        <span class="cag-home-species__cta">
                            {{ __('destination.geo_explore') }}
                            <i class="fas fa-arrow-right" aria-hidden="true"></i>
                        </span>
                    </span>
                </a>
            @endforeach
        </div>
    </div>
</section>
@endif
