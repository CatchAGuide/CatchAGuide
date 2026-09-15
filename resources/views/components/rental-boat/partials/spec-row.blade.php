@php
    use App\Presenters\Vacation\CampAttachmentChipPresenter;

    $boatChips = CampAttachmentChipPresenter::boatChips($specs ?? []);
@endphp

@if(count($boatChips) > 0)
    <div class="rental-boat-card__spec-row">
        @foreach($boatChips as $chip)
            <x-vacation.attachment-chip
                :type="$chip['type']"
                :value="$chip['value']"
                :label="$chip['label']"
                class="attachment-chip--flat"
            />
        @endforeach
    </div>
@endif
