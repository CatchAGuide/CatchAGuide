{{--
    "View your booking" button for guest emails (plan "Guest Booking Access", Part 1).
    $bookable: Booking | CampVacationBooking | TripBooking | VoucherOrder.
    $layout: 'classic' (older div-based mails, default) or 'row' (table mails: renders a <tr>).
    Or, instead of $bookable: $gbLinkUrl, $gbLinkLabel and $gbLinkHint ("Find my booking" sign-in link).
    Renders nothing for unsaved samples (admin previews) or bookings without an email.
--}}
@use('App\Services\GuestBooking\BookingAccessTokenService')
@use('App\Models\VoucherOrder')
@php
    $gbUrl = $gbLinkUrl ?? (isset($bookable) ? app(BookingAccessTokenService::class)->urlFor($bookable) : null);
    $gbLabel = $gbLinkLabel ?? __(($bookable ?? null) instanceof VoucherOrder ? 'guest_bookings.mail.button_voucher' : 'guest_bookings.mail.button');
    $gbHint = $gbLinkHint ?? __('guest_bookings.mail.hint');
    $gbFont = "font-family:'DM Sans',Arial,Helvetica,sans-serif;";
@endphp
@if ($gbUrl)
    @if (($layout ?? 'classic') === 'row')
<tr><td class="px" align="center" style="padding:24px 32px 0;">
<table role="presentation" cellpadding="0" cellspacing="0" border="0" align="center"><tr><td align="center" bgcolor="#313041" style="background:#313041;border-radius:10px;">
<a href="{{ $gbUrl }}" target="_blank" style="display:inline-block;min-width:220px;height:46px;line-height:46px;padding:0 28px;box-sizing:border-box;{{ $gbFont }}font-size:15px;font-weight:700;color:#FFFFFF;text-decoration:none;border-radius:10px;text-align:center;">{{ $gbLabel }}</a>
</td></tr></table>
<p class="t-muted" style="margin:10px 0 0;{{ $gbFont }}font-size:13px;line-height:1.5;color:#5A6478;text-align:center;">{{ $gbHint }}</p>
</td></tr>
    @else
<table role="presentation" cellpadding="0" cellspacing="0" border="0" align="center" style="margin:24px auto 8px;">
    <tr><td bgcolor="#313041" style="background:#313041;">
        <a href="{{ $gbUrl }}" target="_blank" style="display:inline-block;padding:10px 20px;color:#ffffff !important;text-decoration:none;font-size:15px;font-weight:bold;">{{ $gbLabel }}</a>
    </td></tr>
</table>
<p style="margin:0 0 16px;font-size:13px;color:#787780;text-align:center;">{{ $gbHint }}</p>
    @endif
@endif
