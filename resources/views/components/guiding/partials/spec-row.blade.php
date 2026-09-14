@php
    use App\Presenters\Vacation\CampAttachmentChipPresenter;

    $personsChip = CampAttachmentChipPresenter::personsValue($maxPersons ?? null);
    $durationChip = $durationLabel ?: null;
    $tourChip = CampAttachmentChipPresenter::fishingFromChipValue($tourType ?? null);
    $waterTypeChips = CampAttachmentChipPresenter::waterTypeChips($waterTypes ?? []);
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
    @foreach($waterTypeChips as $waterChip)
        <x-vacation.attachment-chip type="water-type" :value="$waterChip['value']" />
    @endforeach
</div>
