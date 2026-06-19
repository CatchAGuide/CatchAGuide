@props(['row'])

@php
    $name = translate($row['name']);
    $subtitle = $row['sub_title'] ?? null;

    if (empty($subtitle) && (($row['trips'] ?? 0) > 0 || ($row['camps'] ?? 0) > 0)) {
        $subtitle = __('vacations.hub_country_trips_camps', [
            'trips' => $row['trips'] ?? 0,
            'camps' => $row['camps'] ?? 0,
        ]);
    }
@endphp

<a
    href="{{ route('vacations.country', $row['slug']) }}"
    class="vacation-country-slide"
    data-analytics-vacation-country="{{ $row['slug'] }}"
>
    <div class="vacation-country-slide__media">
        @if(!empty($row['thumbnail_path']))
            <img
                src="{{ media_url($row['thumbnail_path']) }}"
                alt="{{ $name }}"
                loading="lazy"
            >
        @else
            <div class="vacation-country-slide__placeholder" aria-hidden="true">
                <i class="fas fa-map-marked-alt"></i>
            </div>
        @endif

        <span class="vacation-country-slide__overlay" aria-hidden="true"></span>
    </div>

    <div class="vacation-country-slide__copy">
        <h3 class="vacation-country-slide__title">{{ $name }}</h3>

        @if($subtitle)
            <p class="vacation-country-slide__subtitle">{{ $subtitle }}</p>
        @endif
    </div>
</a>
