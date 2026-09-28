<?php

namespace App\Domain\Checkout\ViewModels;

use App\Http\Requests\RejectionRequest;
use App\Models\Booking;
use App\Presenters\Offers\TourCardPresenter;
use App\Services\Checkout\BlockedDateRanges;
use Carbon\CarbonImmutable;

/**
 * Page data for the guide's "decline request" form (/booking-reject/{token}): the request
 * summary plus the boot config for resources/js/checkout/booking-reject.js, which reuses the
 * checkout calendar to pick 1..MAX_DATES alternative dates.
 */
final class BookingRejectViewModel
{
    public function __construct(
        private readonly Booking $booking,
        private readonly TourCardPresenter $presenter,
    ) {}

    /**
     * @return array{title: string, image: string, location: string, duration: string|null, url: string}
     */
    public function product(): array
    {
        return $this->presenter->presentCheckoutSummary($this->booking->guiding);
    }

    /**
     * What was requested, as the guide needs it to decide. Only the customer's first name:
     * the guide already has the full contact details in the request email.
     *
     * @return array{date: string|null, guests: int, customer: string, total: string}
     */
    public function request(): array
    {
        $customer = $this->booking->user;

        return [
            'date' => $this->requestedDate()
                ? CarbonImmutable::parse($this->requestedDate())->locale(app()->getLocale())->isoFormat('dd, LL')
                : null,
            'guests' => (int) $this->booking->count_of_users,
            'customer' => trim((string) ($customer?->firstname ?? '')),
            'total' => number_format((float) $this->booking->price, (float) $this->booking->price == (int) $this->booking->price ? 0 : 2, ',', '.').' €',
        ];
    }

    public function maxDates(): int
    {
        return RejectionRequest::MAX_DATES;
    }

    public function minMessage(): int
    {
        return RejectionRequest::MIN_MESSAGE;
    }

    public function maxMessage(): int
    {
        return RejectionRequest::MAX_MESSAGE;
    }

    public function submitUrl(): string
    {
        return route('booking.rejection', $this->booking->token);
    }

    /**
     * @return array<string, mixed>
     */
    public function clientConfig(): array
    {
        $blocked = $this->booking->guiding->getBlockedEvents();
        if ($this->requestedDate()) {
            // The requested day can't be offered as an alternative to itself.
            $blocked[] = ['from' => $this->requestedDate(), 'due' => $this->requestedDate()];
        }

        return [
            'minDate' => CarbonImmutable::tomorrow()->format('Y-m-d'),
            'blocked' => BlockedDateRanges::merge($blocked),
            'startDate' => $this->requestedDate(),
            'maxDates' => RejectionRequest::MAX_DATES,
            'minMessage' => RejectionRequest::MIN_MESSAGE,
            'maxMessage' => RejectionRequest::MAX_MESSAGE,
            'locale' => app()->getLocale(),
            'submitUrl' => $this->submitUrl(),
            'i18n' => [
                'months' => array_map(fn (string $month) => __('checkout.calendar_'.$month), [
                    'january', 'february', 'march', 'april', 'may', 'june',
                    'july', 'august', 'september', 'october', 'november', 'december',
                ]),
                'noDates' => __('checkout.reject.no_dates'),
                'removeDate' => __('checkout.reject.remove_date'),
                'datesCount' => __('checkout.reject.dates_count'),
                'chars' => __('checkout.reject.chars'),
                'errors' => [
                    'datesRequired' => __('checkout.reject.errors.dates_required'),
                    'datesMax' => __('checkout.reject.errors.dates_max', ['max' => RejectionRequest::MAX_DATES]),
                    'message' => __('checkout.reject.errors.message_min', ['min' => RejectionRequest::MIN_MESSAGE]),
                    'tooManyRequests' => __('checkout.too_many_requests'),
                    'unexpected' => __('checkout.unexpected_error'),
                ],
            ],
        ];
    }

    private function requestedDate(): ?string
    {
        $date = substr((string) $this->booking->book_date, 0, 10);

        return preg_match('/^\d{4}-\d{2}-\d{2}$/', $date) ? $date : null;
    }
}
