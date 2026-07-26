@if(! empty($markers))
    @php
        $listingMarkers = collect($markers)->map(function ($item) {
            $lat = (float) ($item['lat'] ?? 0);
            $lng = (float) ($item['lng'] ?? 0);
            if (empty($lat) || empty($lng)) {
                return null;
            }

            $pillar = $item['pillar'] ?? ($item['variant'] ?? 'primary');
            if (! in_array($pillar, ['trip', 'camp', 'primary', 'gray'], true)) {
                $pillar = 'primary';
            }

            return [
                'id' => $item['id'] ?? null,
                'lat' => $lat,
                'lng' => $lng,
                'title' => $item['title'] ?? '',
                'url' => $item['url'] ?? '#',
                'location' => $item['location'] ?? '',
                'image' => $item['image'] ?? '',
                'price' => $item['price'] ?? null,
                'priceLabel' => $item['priceLabel'] ?? null,
                'badge' => $item['badge'] ?? null,
                'cta' => $item['cta'] ?? null,
                'pillar' => $item['pillar'] ?? null,
                'variant' => $item['variant'] ?? ($pillar === 'trip' || $pillar === 'camp' ? $pillar : 'primary'),
            ];
        })->filter()->values()->all();
    @endphp
    @if(! empty($listingMarkers))
        <x-maps.listing-modal
            modal-id="vacationCountryMapModal"
            :title="__('vacations.map_modal_title')"
            :result-count="count($listingMarkers)"
            map-id="vacationCountryMap"
            :markers="$listingMarkers"
            instance-key="vacation-country"
            :cluster="true"
            :show-gray-nearby="false"
            :single-zoom="10"
            :default-zoom="6"
            :lazy-modal="true"
            :updatable="false"
            :interactive-preview="true"
        />
    @endif
@endif
