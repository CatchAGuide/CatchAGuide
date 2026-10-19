<?php

namespace App\Services\Vacation;

use App\Mail\Guest\VacationReviewRequestMail;
use App\Models\CampVacationBooking;
use App\Models\TripBooking;
use App\Models\VacationReview;
use App\Services\Sales\SalesLinks;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;

/**
 * Review process for camp and trip bookings: an admin sends the guest a review request email,
 * the guest opens /ratings/{camp|trip}/{token} and submits a VacationReview.
 */
class VacationReviewService
{
    public const TYPE_CAMP = 'camp';
    public const TYPE_TRIP = 'trip';

    /** A second click within this many seconds is treated as a double-submit, not a resend. */
    private const RESEND_GUARD_SECONDS = 120;

    public function __construct(private readonly SalesLinks $links) {}

    /**
     * Email the guest a review request. Returns false when the booking can't be reviewed yet
     * (not done, no email, already reviewed) or a request went out moments ago.
     */
    public function sendRequest(CampVacationBooking|TripBooking $booking): bool
    {
        if (! $booking->canRequestReview()) {
            return false;
        }

        if ($booking->review_requested_at && $booking->review_requested_at->gt(now()->subSeconds(self::RESEND_GUARD_SECONDS))) {
            return false;
        }

        $booking->review_token ??= Str::random(48);
        $booking->save();

        $locale = $booking->customerLocale();

        Mail::to($booking->email, $booking->name ?: null)
            ->locale($locale)
            ->send(new VacationReviewRequestMail($booking, $this->reviewUrl($booking)));

        $booking->forceFill(['review_requested_at' => now()])->save();

        return true;
    }

    /**
     * Absolute review link on the domain of the guest's language.
     */
    public function reviewUrl(CampVacationBooking|TripBooking $booking): string
    {
        $path = route('ratings.vacation.show', [
            'type' => $this->typeOf($booking),
            'token' => (string) $booking->review_token,
        ], false);

        return $this->links->absolute($path, $booking->customerLocale());
    }

    public function findByToken(string $type, string $token): CampVacationBooking|TripBooking|null
    {
        $query = match ($type) {
            self::TYPE_CAMP => CampVacationBooking::query(),
            self::TYPE_TRIP => TripBooking::query(),
            default => null,
        };

        if ($query === null || $token === '') {
            return null;
        }

        return $query->where('review_token', $token)->first();
    }

    /**
     * @param  array{overall_score: int, accommodation_score: int, fishing_score: int, value_score: int, comment: string}  $data
     */
    public function submit(CampVacationBooking|TripBooking $booking, array $data): VacationReview
    {
        return $booking->review()->create([
            'listing_type' => (string) $booking->source_type,
            'listing_id' => (int) $booking->source_id,
            'user_id' => $booking->user_id,
            'overall_score' => (int) $data['overall_score'],
            'accommodation_score' => (int) $data['accommodation_score'],
            'fishing_score' => (int) $data['fishing_score'],
            'value_score' => (int) $data['value_score'],
            'comment' => (string) $data['comment'],
            'language' => app()->getLocale(),
        ]);
    }

    public function typeOf(CampVacationBooking|TripBooking $booking): string
    {
        return $booking instanceof TripBooking ? self::TYPE_TRIP : self::TYPE_CAMP;
    }

    /**
     * What the guest booked, in the current locale, for the review page and email.
     *
     * @return array{title: string, location: string, dates: ?string, persons: ?int, url: ?string}
     */
    public function summary(CampVacationBooking|TripBooking $booking): array
    {
        return [
            'title' => (string) ($booking->getSourceTitle() ?? ''),
            'location' => (string) ($booking->getSourceLocation() ?? ''),
            'dates' => $this->dates($booking),
            'persons' => $booking->number_of_persons ?: null,
            'url' => $booking->getSourceFrontUrl(),
        ];
    }

    /**
     * Camps: arrival to departure (arrival + nights). Trips: the departure or travel window.
     */
    private function dates(CampVacationBooking|TripBooking $booking): ?string
    {
        $from = $booking->preferred_date;
        if ($from === null) {
            return null;
        }

        $to = $booking instanceof TripBooking
            ? $booking->preferred_date_to
            : ($booking->nights ? $from->copy()->addDays((int) $booking->nights) : null);

        $text = $this->formatDate($from);

        return $to && ! $to->isSameDay($from) ? $text.' – '.$this->formatDate($to) : $text;
    }

    private function formatDate(Carbon $date): string
    {
        $locale = app()->getLocale();

        return $date->copy()->locale($locale)->translatedFormat($locale === 'de' ? 'd.m.Y' : 'M j, Y');
    }
}
