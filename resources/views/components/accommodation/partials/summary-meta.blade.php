@php
    use App\Presenters\Vacation\CampAttachmentChipPresenter;

    $personsChip = CampAttachmentChipPresenter::personsValue(
        $accommodation['max_occupancy'] ?? ($accommodation['occupancy_label'] ?? null)
    );
    $bathroomValue = $accommodation['number_of_bathrooms']
        ?? ($accommodation['bathroom_count'] ?? ($accommodation['bathrooms'] ?? null));
    $bathroomChip = null;
    if ($bathroomValue !== null && $bathroomValue !== '' && strtolower(trim((string) $bathroomValue)) !== 'keine angabe') {
        $bathroomChip = is_numeric($bathroomValue) ? (string) (int) $bathroomValue : trim((string) $bathroomValue);
    }
    $areaChip = CampAttachmentChipPresenter::areaValue(
        $accommodation['living_area_sqm'] ?? ($accommodation['living_area_value'] ?? null)
    );
    $bedroomsChip = CampAttachmentChipPresenter::bedroomsValue(
        $accommodation['number_of_bedrooms'] ?? null
    );
    $bedChips = CampAttachmentChipPresenter::bedChips(
        $accommodation['bed_items'] ?? [],
        $accommodation['bed_summary'] ?? ($bedSummary ?? null)
    );

    $distanceValue = function ($raw) {
        if ($raw === null || $raw === '') {
            return null;
        }
        if (is_numeric($raw)) {
            return $raw.' m';
        }

        return translate($raw) ?: $raw;
    };
    $waterDistance = $distanceValue($accommodation['distances']['to_water_m'] ?? null);
    $jettyDistance = $distanceValue($accommodation['distances']['to_berth_m'] ?? null);
    $parkingDistance = $distanceValue($accommodation['distances']['to_parking_m'] ?? null);
@endphp

<div class="accommodation-card__stats">
    @if($personsChip)
        <x-vacation.attachment-chip type="persons" :value="$personsChip" />
    @endif
    @if($bathroomChip)
        <x-vacation.attachment-chip type="bath" :value="$bathroomChip" />
    @endif
    @if($areaChip)
        <x-vacation.attachment-chip type="area" :value="$areaChip" />
    @endif
    @if($bedroomsChip)
        <x-vacation.attachment-chip type="bedrooms" :value="$bedroomsChip" />
    @endif
</div>

@if(count($bedChips) > 0)
    <div class="accommodation-card__beds">
        @foreach($bedChips as $bedChip)
            <x-vacation.attachment-chip type="bed" :value="$bedChip['value']" />
        @endforeach
    </div>
@endif

<div class="accommodation-card__distance-row">
    <div class="accommodation-card__distance-group">
        @if($waterDistance)
            <x-vacation.attachment-chip type="water" :label="__('vacations.label_water')" :value="$waterDistance" />
        @endif
        @if($jettyDistance)
            <x-vacation.attachment-chip type="jetty" :label="__('vacations.label_jetty')" :value="$jettyDistance" />
        @endif
        @if($parkingDistance)
            <x-vacation.attachment-chip type="parking" :label="__('vacations.label_parking')" :value="$parkingDistance" />
        @endif
    </div>
</div>
