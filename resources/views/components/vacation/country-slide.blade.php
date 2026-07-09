@props(['row', 'href' => null])

@php
    $name = translate($row['name']);
    $url = $href ?? route('vacations.country', $row['slug']);
    $flagCode = ! empty($row['countrycode']) ? strtolower($row['countrycode']) : null;
@endphp

<a
    href="{{ $url }}"
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
        <h3 class="vacation-country-slide__title">
            @if($flagCode)
                <img
                    class="vacation-country-slide__flag"
                    src="{{ asset('flags/' . $flagCode . '.svg') }}"
                    alt=""
                    width="22"
                    height="22"
                    loading="lazy"
                >
            @endif
            <span>{{ $name }}</span>
        </h3>
    </div>
</a>
