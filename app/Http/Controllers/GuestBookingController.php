<?php

namespace App\Http\Controllers;

use App\Models\BookingAccessToken;
use App\Models\VoucherOrder;
use App\Presenters\Booking\GuestBookingPresenter;
use App\Services\GuestBooking\BookingAccessTokenService;
use App\Services\GuestBooking\GuestBookingCalendar;
use App\Services\GuestBooking\GuestBookingSession;
use App\Services\Voucher\VoucherPdfGenerator;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Response;
use Illuminate\Support\Str;

/**
 * The per-booking guest link (plan "Guest Booking Access", Part 1). /b/{token} from an email
 * is exchanged for a session entry and redirected to /your-booking/{id}, so the token never
 * reaches the page's address bar, analytics or a referrer. Read-only: no cancelling, no
 * changes (Q6); the guest writes to the guide or support.
 */
class GuestBookingController extends Controller
{
    private const PRIVATE_HEADERS = [
        'Referrer-Policy' => 'no-referrer',
        'Cache-Control' => 'no-store, private',
        'X-Robots-Tag' => 'noindex, nofollow',
    ];

    public function __construct(
        private readonly BookingAccessTokenService $tokens,
        private readonly GuestBookingSession $session,
        private readonly GuestBookingPresenter $presenter,
    ) {}

    public function enter(string $token): Response|RedirectResponse
    {
        $access = $this->tokens->find($token);
        if ($access === null) {
            return $this->unavailable(404);
        }

        if ($this->session->grant($access)) {
            $this->tokens->markOpened($access);
        }

        return redirect()->route('guest-booking.show', ['access' => $access->id], 303)->withHeaders(self::PRIVATE_HEADERS);
    }

    public function show(BookingAccessToken $access): Response
    {
        $bookable = $this->bookable($access);
        if ($bookable === null) {
            return $this->unavailable(403);
        }

        return response()
            ->view('guest-booking.show', $this->presenter->present($bookable) + ['access' => $access])
            ->withHeaders(self::PRIVATE_HEADERS);
    }

    public function calendar(BookingAccessToken $access, GuestBookingCalendar $calendar): Response
    {
        $bookable = $this->bookable($access);
        $dates = $bookable ? $this->presenter->calendarDate($bookable) : null;
        if ($dates === null) {
            return $this->unavailable(404);
        }

        $item = $this->presenter->present($bookable)['item'];
        $ics = $calendar->ics(
            uid: $access->bookable_type->value.'-'.$access->bookable_id,
            title: $item['title'],
            from: $dates['from'],
            to: $dates['to'],
            location: (string) ($item['location'] ?? ''),
            description: __('guest_bookings.calendar_description', ['guide' => $item['guide']['name'] ?? '']),
            url: route('guest-booking.show', ['access' => $access->id]),
        );

        return response($ics, 200, self::PRIVATE_HEADERS + [
            'Content-Type' => 'text/calendar; charset=utf-8',
            'Content-Disposition' => 'attachment; filename="'.(Str::slug($item['title']) ?: 'booking').'.ics"',
        ]);
    }

    public function voucherPdf(BookingAccessToken $access, VoucherPdfGenerator $pdfs): Response
    {
        $order = $this->bookable($access);
        if (! $order instanceof VoucherOrder
            || $this->presenter->isRedacted($order)
            || ! $this->presenter->voucherPdfAvailable($order)
            || ($contents = $pdfs->contents($order)) === null) {
            return $this->unavailable(404);
        }

        return response($contents, 200, self::PRIVATE_HEADERS + [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'attachment; filename="'.$pdfs->fileName($order).'"',
        ]);
    }

    /** The booking behind a link this session opened, or null. */
    private function bookable(BookingAccessToken $access): ?Model
    {
        if (! $this->session->allows($access)) {
            return null;
        }

        return $this->presenter->load($access->bookable_type, (int) $access->bookable_id);
    }

    private function unavailable(int $status): Response
    {
        return response()->view('guest-booking.unavailable', [], $status)->withHeaders(self::PRIVATE_HEADERS);
    }
}
