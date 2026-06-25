@if(! empty($markers))
    <div class="modal fade" id="vacationCountryMapModal" tabindex="-1" aria-labelledby="vacationCountryMapModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-xl vacation-country-map-modal">
            <div class="modal-content">
                <div class="modal-header">
                    <h2 class="modal-title fs-5" id="vacationCountryMapModalLabel">{{ __('vacations.map_modal_title') }}</h2>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div id="vacationCountryMap" class="modal-body vacation-country-map-modal__canvas"></div>
            </div>
        </div>
    </div>
@endif
