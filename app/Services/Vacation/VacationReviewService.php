<?php

namespace App\Services\Vacation;

use App\Mail\Guest\VacationReviewRequestMail;
use App\Models\Camp;
use App\Models\CampVacationBooking;
use App\Models\Trip;
use App\Models\TripBooking;
use App\Models\Vacation;
use App\Models\VacationReview;
use App\Services\Sales\SalesLinks;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
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

    /** Guest comments shown on a product page; the averages still cover every review. */
    public const PAGE_REVIEW_LIMIT = 30;

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

    /**
     * A review an admin enters by hand (Admin › Reviews › Add camp/trip review), e.g. feedback
     * a guest gave by phone. With a booking it counts as that booking's review; without one it
     * is attached to the listing only.
     *
     * @param  array{listing_type: string, listing_id: int, guest_name: string, reviewed_at: string, language: string, overall_score: int, accommodation_score: int, fishing_score: int, value_score: int, comment: string}  $data
     */
    public function createManual(array $data, CampVacationBooking|TripBooking|null $booking = null): VacationReview
    {
        $review = new VacationReview([
            'listing_type' => $booking ? (string) $booking->source_type : $data['listing_type'],
            'listing_id' => $booking ? (int) $booking->source_id : (int) $data['listing_id'],
            'user_id' => $booking?->user_id,
            'guest_name' => $data['guest_name'],
            'overall_score' => (int) $data['overall_score'],
            'accommodation_score' => (int) $data['accommodation_score'],
            'fishing_score' => (int) $data['fishing_score'],
            'value_score' => (int) $data['value_score'],
            'comment' => (string) $data['comment'],
            'language' => $data['language'],
            'is_manual' => true,
        ]);

        if ($booking) {
            $review->reviewable()->associate($booking);
        }

        // The date the guest gave the feedback, shown on the product page.
        $review->created_at = Carbon::parse($data['reviewed_at'])->setTimeFrom(now());
        $review->save();

        return $review;
    }

    /**
     * Averages over all reviews of a camp or trip plus its newest comments, for the product page.
     *
     * @return array{count: int, averages: array{grandtotal: float, overall: float, accommodation: float, fishing: float, value: float}, reviews: Collection<int, VacationReview>}|null
     */
    public function listingReviews(string $listingType, int $listingId): ?array
    {
        $totals = VacationReview::query()
            ->forListing($listingType, $listingId)
            ->selectRaw('COUNT(*) as review_count, AVG(grandtotal_score) as grandtotal, AVG(overall_score) as overall, AVG(accommodation_score) as accommodation, AVG(fishing_score) as fishing, AVG(value_score) as value_score')
            ->toBase()
            ->first();

        $count = (int) ($totals->review_count ?? 0);
        if ($count === 0) {
            return null;
        }

        return [
            'count' => $count,
            'averages' => [
                'grandtotal' => (float) $totals->grandtotal,
                'overall' => (float) $totals->overall,
                'accommodation' => (float) $totals->accommodation,
                'fishing' => (float) $totals->fishing,
                'value' => (float) $totals->value_score,
            ],
            'reviews' => VacationReview::query()
                ->forListing($listingType, $listingId)
                ->with('reviewable')
                ->newestFirst()
                ->limit(self::PAGE_REVIEW_LIMIT)
                ->get(),
        ];
    }

    /**
     * Done camp and trip requests the guest hasn't reviewed yet, newest first, for the
     * "Open for review" list in Admin › Reviews. Listing titles are loaded in one query per
     * listing type.
     *
     * @return Collection<int, array{type: string, booking: CampVacationBooking|TripBooking, title: ?string}>
     */
    public function awaitingReview(?string $type = null, int $limit = 100): Collection
    {
        $query = fn (string $model) => $model::query()
            ->where('status', $model::STATUS_DONE)
            ->whereDoesntHave('review')
            ->orderByDesc('updated_at')
            ->limit($limit)
            ->get();

        $bookings = collect()
            ->merge($type === self::TYPE_TRIP ? [] : $query(CampVacationBooking::class)->all())
            ->merge($type === self::TYPE_CAMP ? [] : $query(TripBooking::class)->all())
            ->sortByDesc(fn ($booking) => $booking->updated_at?->getTimestamp() ?? 0)
            ->take($limit)
            ->values();

        $listings = $this->listingLinks($bookings->map(fn ($booking) => [
            'type' => strtolower((string) $booking->source_type),
            'id' => (int) $booking->source_id,
        ]));

        return $bookings->map(fn ($booking) => [
            'type' => $this->typeOf($booking),
            'booking' => $booking,
            'title' => $listings[strtolower((string) $booking->source_type).':'.$booking->source_id]['title'] ?? null,
        ]);
    }

    public function findBooking(string $type, int $id): CampVacationBooking|TripBooking|null
    {
        return match ($type) {
            self::TYPE_CAMP => CampVacationBooking::query()->find($id),
            self::TYPE_TRIP => TripBooking::query()->find($id),
            default => null,
        };
    }

    /**
     * A booking the admin review modal opens for: it must exist and have no review yet.
     *
     * @return array{type: string, booking: CampVacationBooking|TripBooking, title: ?string}|null
     */
    public function bookingOpenForReview(?string $type, ?int $id): ?array
    {
        $booking = $type && $id ? $this->findBooking($type, $id) : null;

        if (! $booking || $booking->review) {
            return null;
        }

        return ['type' => $this->typeOf($booking), 'booking' => $booking, 'title' => $booking->getSourceTitle()];
    }

    public function listingExists(string $type, int $id): bool
    {
        return match ($type) {
            self::TYPE_CAMP => Camp::query()->whereKey($id)->exists(),
            self::TYPE_TRIP => Trip::query()->whereKey($id)->exists(),
            default => false,
        };
    }

    /**
     * Active camps and trips an admin can attach a manual review to.
     *
     * @return array{camp: Collection<int, Camp>, trip: Collection<int, Trip>}
     */
    public function manualListingOptions(): array
    {
        return [
            self::TYPE_CAMP => Camp::query()->whereIn('status', ['active', 'draft'])->orderBy('title')->get(['id', 'title']),
            self::TYPE_TRIP => Trip::query()->whereIn('status', ['active', 'draft'])->orderBy('title')->get(['id', 'title']),
        ];
    }

    /**
     * Title and public URL per listing, one query per listing type.
     *
     * @param  Collection<int, array{type: string, id: int}>  $sources
     * @return array<string, array{title: string, url: ?string}>  keyed "type:id"
     */
    public function listingLinks(Collection $sources): array
    {
        $models = [
            CampVacationBooking::SOURCE_CAMP => [Camp::class, 'vacations.camps.show'],
            CampVacationBooking::SOURCE_VACATION => [Vacation::class, 'vacations.camps.show'],
            TripBooking::SOURCE_TRIP => [Trip::class, 'vacations.trips.show'],
        ];

        $links = [];
        foreach ($sources->groupBy('type') as $sourceType => $group) {
            [$model, $route] = $models[$sourceType] ?? [null, null];
            if ($model === null) {
                continue;
            }

            $model::query()
                ->whereIn('id', $group->pluck('id')->unique()->all())
                ->get(['id', 'title', 'slug'])
                ->each(function ($listing) use (&$links, $sourceType, $route) {
                    $links[$sourceType.':'.$listing->id] = [
                        'title' => (string) $listing->title,
                        'url' => $listing->slug ? route($route, $listing->slug) : null,
                    ];
                });
        }

        return $links;
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
