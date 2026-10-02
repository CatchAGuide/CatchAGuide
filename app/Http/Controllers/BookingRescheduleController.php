<?php

namespace App\Http\Controllers;

use App\Domain\Checkout\ViewModels\TourCheckoutViewModel;
use App\Http\Requests\TourRescheduleRequest;
use App\Models\Booking;
use App\Models\User;
use App\Models\UserGuest;
use App\Presenters\Offers\TourCardPresenter;
use App\Services\Booking\Reschedule\RescheduleAlreadyUsedException;
use App\Services\Booking\Reschedule\RescheduleOffer;
use App\Services\Booking\Reschedule\RescheduleOfferResolver;
use App\Services\Booking\Reschedule\RescheduleSession;
use App\Services\Booking\Reschedule\TourRescheduleService;
use App\Services\Checkout\BookingConfirmationAccess;
use Carbon\CarbonImmutable;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Log;
use InvalidArgumentException;
use Throwable;

/**
 * Customer side of a declined tour request: pick one of the guide's suggested dates and send
 * the request again. Access stays link-based (no login needed), but the link's token is
 * exchanged for a session on first open and the page runs on a token-free URL.
 */
class BookingRescheduleController extends Controller
{
    private const PRIVATE_HEADERS = [
        'Referrer-Policy' => 'no-referrer',
        'Cache-Control' => 'no-store, private',
        'X-Robots-Tag' => 'noindex, nofollow',
    ];

    public function __construct(
        private readonly RescheduleOfferResolver $offers,
        private readonly RescheduleSession $session,
    ) {}

    /**
     * Emailed link: /booking/reschedule/{token}?date=Y-m-d. Every old email keeps working.
     */
    public function enter(Request $request, string $token): RedirectResponse
    {
        $offer = $this->offers->resolve($token);

        if ($offer->booking !== null) {
            $this->session->remember($token, $request->query('date'));
        } else {
            $this->session->forget();
        }

        return redirect()->route('booking.reschedule.show', status: 303)->withHeaders(self::PRIVATE_HEADERS);
    }

    public function show(TourCardPresenter $presenter): Response
    {
        $offer = $this->offers->resolve($this->session->token());

        if (! $offer->isAvailable()) {
            return $this->statePage($offer);
        }

        $booking = $offer->booking;

        $checkout = TourCheckoutViewModel::forReschedule(
            $booking->guiding,
            $presenter,
            (int) $booking->count_of_users,
            $this->session->preferredDate(),
            $offer->dates,
            $this->contactPrefill($booking),
            $this->originalExtraIndexes($booking),
        );

        return response()
            ->view('pages.modern-checkout.reschedule', [
                'checkout' => $checkout,
                'guideName' => $booking->guiding->user?->firstname,
                'guideMessage' => $booking->additional_information,
                'originalDate' => $booking->book_date
                    ? CarbonImmutable::parse($booking->book_date)->locale(app()->getLocale())->isoFormat('LL')
                    : null,
            ])
            ->withHeaders(self::PRIVATE_HEADERS);
    }

    public function store(
        TourRescheduleRequest $request,
        TourRescheduleService $reschedules,
        BookingConfirmationAccess $confirmations,
    ): JsonResponse {
        $offer = $request->offer();

        if (! $offer->isAvailable()) {
            return $this->unavailableResponse($offer);
        }

        $quote = $request->pricing()->quote((int) $request->validated('persons'), $request->extraIndexes());
        $date = (string) $request->validated('selected_date');

        try {
            $booking = $reschedules->submit($offer->booking, $quote, $date, $request->contact());
        } catch (RescheduleAlreadyUsedException) {
            return $this->unavailableResponse(new RescheduleOffer(RescheduleOffer::USED, $offer->booking));
        } catch (InvalidArgumentException) {
            return response()->json([
                'success' => false,
                'errors' => ['selected_date' => [__('checkout.tour.errors.date_unavailable')]],
            ], 422);
        } catch (Throwable $e) {
            Log::error('Tour reschedule failed', ['booking_id' => $offer->booking->id, 'error' => $e->getMessage()]);

            return response()->json(['success' => false, 'message' => __('checkout.booking_failed')], 500);
        }

        $this->session->forget();
        $confirmations->grant($booking);

        return response()->json([
            'success' => true,
            'redirect_url' => route('checkout.thank-you', [$booking]),
        ]);
    }

    /**
     * A friendly page with a way forward for every link that can't be used (any more).
     */
    private function statePage(RescheduleOffer $offer): Response
    {
        $guiding = $offer->booking?->guiding;
        $tourUrl = $offer->status !== RescheduleOffer::UNAVAILABLE && $guiding ? $guiding->publicShowUrl() : null;

        return response()
            ->view('pages.modern-checkout.reschedule-state', [
                'state' => $offer->status,
                'tourUrl' => $tourUrl,
                'tourTitle' => $tourUrl ? $guiding->title : null,
            ], $offer->status === RescheduleOffer::INVALID ? 404 : 200)
            ->withHeaders(self::PRIVATE_HEADERS);
    }

    private function unavailableResponse(RescheduleOffer $offer): JsonResponse
    {
        return response()->json([
            'success' => false,
            'message' => __('checkout.reschedule.errors.'.$offer->status),
        ], 409);
    }

    /**
     * Full contact from the original request, split into the checkout form fields.
     *
     * @return array{firstName: string, lastName: string, email: string, countryCode: string, phone: string}
     */
    private function contactPrefill(Booking $booking): array
    {
        $customer = $booking->user;
        $countryCode = $this->countryCode($booking, $customer);

        return [
            'firstName' => trim((string) ($customer->firstname ?? '')),
            'lastName' => trim((string) ($customer->lastname ?? '')),
            'email' => (string) ($booking->customerEmail() ?? ''),
            'countryCode' => $countryCode,
            'phone' => $this->nationalPhone($booking, $customer, $countryCode),
        ];
    }

    private function countryCode(Booking $booking, User|UserGuest|null $customer): string
    {
        $codes = array_keys((array) config('phone_country_codes', []));
        $stored = (string) ($customer->phone_country_code ?? $booking->phone_country_code ?? '');

        if (in_array($stored, $codes, true)) {
            return $stored;
        }

        $compactPhone = preg_replace('/\s+/', '', (string) $booking->phone) ?? '';
        usort($codes, fn (string $a, string $b) => strlen($b) <=> strlen($a));

        foreach ($codes as $code) {
            $compactCode = preg_replace('/\s+/', '', $code) ?? '';
            if ($compactCode !== '' && str_starts_with($compactPhone, $compactCode)) {
                return $code;
            }
        }

        return TourCheckoutViewModel::DEFAULT_COUNTRY_CODE;
    }

    private function nationalPhone(Booking $booking, User|UserGuest|null $customer, string $countryCode): string
    {
        $phone = trim((string) ($customer->phone ?? ''));
        if ($phone !== '') {
            return $phone;
        }

        $full = trim((string) $booking->phone);
        $compactCode = preg_replace('/\s+/', '', $countryCode) ?? '';
        $compactFull = preg_replace('/\s+/', '', $full) ?? '';

        if ($compactCode !== '' && str_starts_with($compactFull, $compactCode)) {
            return trim(substr($compactFull, strlen($compactCode)));
        }

        return $full;
    }

    /**
     * Extras the customer picked originally, so the new request starts from the same choice.
     *
     * @return list<int>
     */
    private function originalExtraIndexes(Booking $booking): array
    {
        if (! is_string($booking->extras) || $booking->extras === '') {
            return [];
        }

        $extras = @unserialize($booking->extras, ['allowed_classes' => false]);

        return is_array($extras)
            ? array_values(array_filter(array_map(fn ($extra) => is_array($extra) && isset($extra['extra_id']) ? (int) $extra['extra_id'] : null, $extras), 'is_int'))
            : [];
    }
}
