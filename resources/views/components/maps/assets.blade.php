{{-- Shared Leaflet maps assets + config (loaded once per page) --}}
@once
    @push('styles')
        <link rel="stylesheet" href="{{ mix('css/maps.css') }}">
    @endpush
    @push('js_push')
        <script>
            window.CAG_MAPS_CONFIG = window.CAG_MAPS_CONFIG || {
                tileUrl: @json(config('services.maps.tile_url')),
                attribution: @json(config('services.maps.attribution')),
                defaultCenter: {
                    lat: {{ (float) config('services.maps.default_center.lat') }},
                    lng: {{ (float) config('services.maps.default_center.lng') }}
                },
                defaultZoom: {{ (int) config('services.maps.default_zoom') }},
                googleMapsApiKey: @json(config('services.google_maps.api_key'))
            };
        </script>
        <script src="{{ mix('js/maps.js') }}" defer></script>
    @endpush
@endonce
