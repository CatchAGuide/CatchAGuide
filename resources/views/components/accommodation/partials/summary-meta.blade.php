@php
    use App\Presenters\Vacation\CampAttachmentChipPresenter;

    $guestsRaw = $accommodation['max_occupancy'] ?? ($accommodation['occupancy_label'] ?? null);
    $personsChip = null;
    if ($guestsRaw !== null && $guestsRaw !== '') {
        $personsChip = (is_numeric($guestsRaw) ? (string) (int) $guestsRaw : trim((string) $guestsRaw))
            .' '.__('vacations.chip_guests');
    }
    $bedroomsChip = CampAttachmentChipPresenter::bedroomsValue(
        $accommodation['number_of_bedrooms'] ?? null
    );
    if ($bedroomsChip !== null) {
        $bedroomsChip .= ' '.__('vacations.chip_bedrooms');
    }
    $bedChips = CampAttachmentChipPresenter::bedChips(
        $accommodation['bed_items'] ?? [],
        $accommodation['bed_summary'] ?? ($bedSummary ?? null)
    );
    // "(5) Single bed" -> "5 Single bed" for a single comma-separated summary line.
    $bedSummaryLine = implode(', ', array_map(
        fn ($chip) => preg_replace('/^\((\d+)\)\s*/', '$1 ', $chip['value']),
        $bedChips
    ));

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

<div class="accommodation-card__hard-facts">
    <div class="accommodation-card__stats accommodation-card__stats--flat">
        @if($personsChip)
            <x-vacation.attachment-chip type="persons" :value="$personsChip" class="attachment-chip--flat" />
        @endif
        @if($bedroomsChip)
            <x-vacation.attachment-chip type="bedrooms" :value="$bedroomsChip" class="attachment-chip--flat" />
        @endif
    </div>
</div>

@if($bedSummaryLine !== '')
    <p class="accommodation-card__bed-summary">
        <span class="accommodation-card__bed-summary-label">{{ __('vacations.chip_bedrooms') }}:</span>
        {{ $bedSummaryLine }}
    </p>
@endif

<div class="accommodation-card__distance-row">
    <div class="accommodation-card__distance-group">
        @if($waterDistance)
            <x-vacation.attachment-chip type="water" :label="__('vacations.label_water')" :value="$waterDistance" :show-icon="false" class="attachment-chip--plain-pill" />
        @endif
        @if($jettyDistance)
            <x-vacation.attachment-chip type="jetty" :label="__('vacations.label_jetty')" :value="$jettyDistance" :show-icon="false" class="attachment-chip--plain-pill" />
        @endif
        @if($parkingDistance)
            <x-vacation.attachment-chip type="parking" :label="__('vacations.label_parking')" :value="$parkingDistance" :show-icon="false" class="attachment-chip--plain-pill" />
        @endif
    </div>
</div>
