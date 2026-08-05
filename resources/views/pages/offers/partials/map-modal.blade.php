@if(! empty($markers))
    @php
        $listingMarkers = collect($markers)->map(function ($item) {
            $lat = (float) ($item['lat'] ?? 0);
            $lng = (float) ($item['lng'] ?? 0);
            if (empty($lat) || empty($lng)) {
                return null;
            }

            $pillar = $item['pillar'] ?? ($item['variant'] ?? 'tour');
            if ($pillar === 'guiding') {
                $pillar = 'tour';
            }
            if (! in_array($pillar, ['tour', 'trip', 'camp', 'primary', 'gray'], true)) {
                $pillar = 'tour';
            }

            $variant = $item['variant'] ?? $pillar;
            if ($variant === 'guiding' || ($variant === 'primary' && $pillar === 'tour')) {
                $variant = 'tour';
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
                'pillar' => $pillar,
                'variant' => $variant,
            ];
        })->filter()->values()->all();
    @endphp
    @if(! empty($listingMarkers))
        <x-maps.listing-modal
            modal-id="offersCatalogMapModal"
            :title="__('offers.map_modal_title')"
            :result-count="count($listingMarkers)"
            map-id="offersCatalogMap"
            :markers="$listingMarkers"
            instance-key="offers-catalog"
            :cluster="true"
            :show-gray-nearby="true"
            :single-zoom="10"
            :default-zoom="6"
            :lazy-modal="true"
            :updatable="false"
            :interactive-preview="true"
        />
    @endif
@endif
