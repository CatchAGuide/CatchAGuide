@php
    use App\Presenters\Vacation\CampAttachmentChipPresenter;

    $personsChip = CampAttachmentChipPresenter::personsValue($maxPersons ?? null);
    $durationChip = $durationLabel ? (translate($durationLabel) ?: $durationLabel) : null;
    $tourChip = $tourType ? (translate($tourType) ?: $tourType) : null;
@endphp

<div class="guiding-card__spec-row">
    @if($durationChip)
        <x-vacation.attachment-chip type="duration" :value="$durationChip" />
    @endif
    @if($personsChip)
        <x-vacation.attachment-chip type="persons" :value="$personsChip" />
    @endif
    @if($tourChip)
        <x-vacation.attachment-chip type="tour" :value="$tourChip" />
    @endif
</div>
