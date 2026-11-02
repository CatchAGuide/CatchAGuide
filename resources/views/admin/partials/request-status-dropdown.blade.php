{{--
    Status menu + "changed by" line for an admin request list (camp/trip requests, contact requests,
    product reports). Requests with a done reason get "Done → Booked / No reply" instead of plain Done.
    Needs admin.partials.booking-status-history once on the page (modal, styles and the click handler).

    @param \App\Contracts\Booking\HasStatusHistoryContract $item
    @param string $updateUrl
    @param string $historyUrl
    @param string|null $summary
--}}
@php
    $status = (string) ($item->status ?: 'open');
    $withReasons = $item instanceof \App\Contracts\Booking\HasDoneReasonContract;
    $doneReason = $withReasons ? $item->doneReason() : null;
@endphp
<div class="dropdown">
    <button type="button"
            class="btn btn-sm dropdown-toggle js-status-dropdown-btn btn-status-{{ $status }} {{ $doneReason ? 'btn-done-'.$doneReason->value : '' }}"
            data-bs-toggle="dropdown"
            data-bs-boundary="viewport"
            data-url="{{ $updateUrl }}"
            data-status="{{ $status }}"
            data-done-reason="{{ $doneReason?->value }}"
            aria-expanded="false">
        <i class="fa js-status-btn-icon {{ $doneReason ? $doneReason->icon().' me-1' : 'd-none' }}" aria-hidden="true"></i><span class="js-status-btn-text">{{ $item->statusLabel() }}</span>
        <i class="fa fa-caret-down ms-1"></i>
    </button>
    <ul class="dropdown-menu dropdown-menu-end request-status-menu">
        @foreach($item::statusOptions() as $value => $label)
            @if($value === 'done' && $withReasons)
                <li><hr class="dropdown-divider"></li>
                <li><h6 class="dropdown-header">{{ __('admin.done_reason.heading') }}</h6></li>
                @foreach(\App\Enums\DoneReason::cases() as $reason)
                    <li>
                        <a class="dropdown-item js-status-option request-status-option request-status-option--{{ $reason->value }} {{ $doneReason === $reason ? 'active' : '' }}"
                           href="#"
                           data-status="done"
                           data-done-reason="{{ $reason->value }}"
                           data-icon="{{ $reason->icon() }}">
                            <i class="fa {{ $reason->icon() }} request-status-option__icon" aria-hidden="true"></i>
                            <span>
                                <span class="request-status-option__label">{{ $reason->label() }}</span>
                                <small class="request-status-option__hint">{{ $reason->hint() }}</small>
                            </span>
                        </a>
                    </li>
                @endforeach
            @else
                <li>
                    <a class="dropdown-item js-status-option request-status-option {{ $status === $value && ! $doneReason ? 'active' : '' }}"
                       href="#"
                       data-status="{{ $value }}"
                       data-done-reason="">
                        <span class="request-status-dot request-status-dot--{{ $value }}" aria-hidden="true"></span>
                        <span class="request-status-option__label">{{ $label }}</span>
                    </a>
                </li>
            @endif
        @endforeach
    </ul>
</div>
<button type="button"
        class="btn btn-link p-0 text-muted js-status-summary"
        data-url="{{ $historyUrl }}"
        data-bs-toggle="modal"
        data-bs-target="#statusHistoryModal"
        title="{{ __('admin.booking_status_history.button') }}">
    <i class="fa fa-stream me-1"></i><span class="js-status-summary-text">{{ $summary ?? __('admin.booking_status_history.button') }}</span>
</button>
