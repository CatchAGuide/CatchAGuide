@props([
    'row',
    'href' => null,
    'clone' => false,
])

@php
    $name = $row['name'] ?? '';
    $url = $href ?? route('vacations.country', $row['slug']);
    $showMeta = array_key_exists('trips', $row) || array_key_exists('camps', $row);
    $metaLabel = $showMeta
        ? __('vacations.hub_country_trips_camps', [
            'trips' => (int) ($row['trips'] ?? 0),
            'camps' => (int) ($row['camps'] ?? 0),
        ])
        : null;
    $flagIso = strtolower((string) ($row['countrycode'] ?? ''));
    if ($flagIso === 'uk') {
        $flagIso = 'gb';
    }
@endphp

<a
    href="{{ $url }}"
    class="vacation-country-rail__tile"
    role="listitem"
    @if($clone) aria-hidden="true" tabindex="-1" @endif
    data-analytics-vacation-country="{{ $row['slug'] }}"
>
    @if(!empty($row['thumbnail_path']))
        <img
            src="{{ media_url($row['thumbnail_path']) }}"
            alt="{{ $clone ? '' : $name }}"
            class="vacation-country-rail__img"
            loading="lazy"
            width="320"
            height="240"
        >
    @else
        <span class="vacation-country-rail__placeholder" aria-hidden="true">
            <i class="fas fa-map-marked-alt"></i>
        </span>
    @endif

    <span class="vacation-country-rail__fade"></span>
    <span class="vacation-country-rail__meta">
        <span class="vacation-country-rail__name">
            @if($flagIso !== '')
                <span class="fi fi-{{ $flagIso }} vacation-country-rail__flag" aria-hidden="true"></span>
            @endif
            {{ $name }}
        </span>
        @if($metaLabel)
            {{-- <span class="vacation-country-rail__count">{{ $metaLabel }}</span> --}}
        @endif
    </span>
</a>
