@if(! empty($markers))
    @php
        $listingMarkers = collect($markers)->map(function ($item) {
            $title = $item['title'] ?? '';
            $url = $item['url'] ?? '#';

            return [
                'lat' => (float) ($item['lat'] ?? 0),
                'lng' => (float) ($item['lng'] ?? 0),
                'title' => $title,
                'variant' => 'primary',
                'popupHtml' => '<div class="vacation-country-map-popup"><a href="'
                    . e($url)
                    . '" class="vacation-country-map-popup__title">'
                    . e($title)
                    . '</a></div>',
            ];
        })->filter(fn ($m) => !empty($m['lat']) && !empty($m['lng']))->values()->all();
    @endphp
    @if(! empty($listingMarkers))
    <div class="modal fade" id="vacationCountryMapModal" tabindex="-1" aria-labelledby="vacationCountryMapModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-xl vacation-country-map-modal">
            <div class="modal-content">
                <div class="modal-header">
                    <h2 class="modal-title fs-5" id="vacationCountryMapModalLabel">{{ __('vacations.map_modal_title') }}</h2>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body vacation-country-map-modal__canvas p-0">
                    <x-maps.listing
                        :markers="$listingMarkers"
                        layout="modal"
                        modal-id="vacationCountryMapModal"
                        map-id="vacationCountryMap"
                        height="100%"
                        instance-key="vacation-country"
                        :cluster="true"
                        :show-gray-nearby="false"
                        :single-zoom="10"
                        :default-zoom="6"
                        :lazy-modal="true"
                        :updatable="false"
                    />
                </div>
            </div>
        </div>
    </div>
    @endif
@endif
