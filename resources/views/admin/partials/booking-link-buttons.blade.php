{{--
    Guest booking link actions for one row: send the guest the link to their booking, or turn the
    link off and send a new one. Needs $type (tour|camp|trip|voucher), $id and $email (the
    booking's email; nothing renders without it). Include once with 'part' => 'form' outside the
    row's btn-group, and once without it inside the group (forms can't nest in a btn-group).
--}}
@php
    $blKey = 'booking-link-'.$type.'-'.$id;
@endphp
@if (filled($email))
    @if (($part ?? 'button') === 'form')
        <form id="{{ $blKey }}-send" method="POST" action="{{ route('admin.booking-links.send', ['type' => $type, 'id' => $id]) }}" class="d-none">@csrf</form>
        <form id="{{ $blKey }}-revoke" method="POST" action="{{ route('admin.booking-links.revoke', ['type' => $type, 'id' => $id]) }}" class="d-none">@csrf</form>
    @else
        <button type="submit" form="{{ $blKey }}-send" class="btn btn-outline-secondary"
                title="{{ __('guest_bookings.admin.send') }}" aria-label="{{ __('guest_bookings.admin.send') }}"
                onclick="return confirm(@js(__('guest_bookings.admin.confirm_send', ['email' => $email])));">
            <i class="fa fa-link"></i>
        </button>
        <button type="submit" form="{{ $blKey }}-revoke" class="btn btn-outline-secondary"
                title="{{ __('guest_bookings.admin.revoke') }}" aria-label="{{ __('guest_bookings.admin.revoke') }}"
                onclick="return confirm(@js(__('guest_bookings.admin.confirm_revoke', ['email' => $email])));">
            <i class="fa fa-unlink"></i>
        </button>
    @endif
@endif
