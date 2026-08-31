@props(['tile'])

@php
    use App\Domain\Vacation\Pillar;

    $modifier = $tile->pillar === Pillar::Trip ? 'trip' : 'camp';
    $marketingKeywords = __($tile->pillar->marketingKeywordsKey());
    $icon = $modifier === 'trip' ? 'fa-compass' : 'fa-campground';
    $ctaKey = $modifier === 'trip' ? 'vacations.pillar_trips_cta' : 'vacations.pillar_camps_cta';
@endphp

<a
    href="{{ $tile->url }}"
    class="vacation-pillar-tile vacation-pillar-tile--{{ $modifier }}"
    data-analytics-vacation-pillar-tile
    data-pillar="{{ $modifier }}"
>
    <span class="vacation-pillar-tile__decor" aria-hidden="true"></span>

    <div class="vacation-pillar-tile__header">
        <span class="vacation-pillar-tile__icon" aria-hidden="true">
            <i class="fas {{ $icon }}"></i>
        </span>
        <h2 class="vacation-pillar-tile__title">{{ $tile->title }}</h2>
    </div>

    <p class="vacation-pillar-tile__desc">{{ $tile->description }}</p>

    @if(is_array($marketingKeywords) && count($marketingKeywords) > 0)
        <div class="vacation-pillar-tile__badges">
            @foreach($marketingKeywords as $keyword)
                <span class="vacation-pillar-tile__badge">{{ $keyword }}</span>
            @endforeach
        </div>
    @endif

    <div class="vacation-pillar-tile__footer">
        <span class="vacation-pillar-tile__cta">
            {{ __($ctaKey) }}
            <i class="fas fa-arrow-right" aria-hidden="true"></i>
        </span>
    </div>
</a>
