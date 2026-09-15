@php
    use App\Presenters\Vacation\CampAttachmentChipPresenter;

    $personsChip = CampAttachmentChipPresenter::personsValue($maxPersons ?? null);
    $durationChip = $durationLabel ?: null;
    $tourChip = CampAttachmentChipPresenter::fishingFromChipValue($tourType ?? null);
@endphp

<div class="guiding-card__spec-row">
    @if($durationChip)
        <x-vacation.attachment-chip type="duration" :value="$durationChip" class="attachment-chip--flat" />
    @endif
    @if($personsChip)
        <x-vacation.attachment-chip type="persons" :value="$personsChip" class="attachment-chip--flat" />
    @endif
    @if($tourChip)
        <x-vacation.attachment-chip type="tour" :value="$tourChip" class="attachment-chip--flat" />
    @endif
</div>
