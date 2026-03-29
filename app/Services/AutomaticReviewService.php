<?php

namespace App\Services;

use App\Models\Booking;
use App\Models\Review;
use Carbon\Carbon;
use Carbon\CarbonInterface;
use Illuminate\Support\Facades\DB;

class AutomaticReviewService
{
    /**
     * Eligible for an automatic review only after this many full days have passed since the fishing date.
     */
    public const DAYS_AFTER_FISHING_DATE = 10;

    /** All automatic reviews use 10 for each category; grand total is therefore 10. */
    private const AUTOMATIC_CATEGORY_SCORE = 10;

    private const EARLIEST_FISHING_DATE = '2024-01-01';

    /**
     * Default comment (English); displayed via translate() in views.
     */
    public static function defaultComment(): string
    {
        return 'Successfully completed fishing tour with :name';
    }

    /**
     * Comment text for storage; :name is replaced when firstname is available (member or guest).
     */
    private static function commentForBooking(Booking $booking): string
    {
        $booking->loadMissing('user');
        $name = trim((string) optional($booking->user)->firstname);
        if ($name === '') {
            return 'Successfully completed fishing tour';
        }

        return str_replace(':name', $name, self::defaultComment());
    }

    /**
     * @return array{overall_score: float, guide_score: float, region_water_score: float}
     */
    private static function automaticCategoryScores(): array
    {
        $s = (float) self::AUTOMATIC_CATEGORY_SCORE;

        return [
            'overall_score' => $s,
            'guide_score' => $s,
            'region_water_score' => $s,
        ];
    }

    public function isEligible(Booking $booking, CarbonInterface $now): bool
    {
        if ($booking->status !== 'accepted') {
            return false;
        }

        if ($booking->is_reviewed) {
            return false;
        }

        if ($booking->review()->exists()) {
            return false;
        }

        $fishingDate = $booking->getBookingDate();
        if ($fishingDate === null) {
            return false;
        }

        if ($fishingDate->lt(self::EARLIEST_FISHING_DATE)) {
            return false;
        }

        return $fishingDate->copy()->addDays(self::DAYS_AFTER_FISHING_DATE)->lte($now);
    }

    /**
     * Preview data for console dry-run (same eligibility + scores as would be stored).
     *
     * @return array{
     *     booking_id: int,
     *     guiding_id: int,
     *     tour_name: string,
     *     fishing_date: string,
     *     overall_score: float,
     *     guide_score: float,
     *     region_water_score: float,
     *     grandtotal_score: float
     * }|null
     */
    public function previewEligibleBooking(Booking $booking, CarbonInterface $now): ?array
    {
        if (!$this->isEligible($booking, $now)) {
            return null;
        }

        $scores = self::automaticCategoryScores();
        $grandtotal = round(
            ($scores['overall_score'] + $scores['guide_score'] + $scores['region_water_score']) / 3,
            1
        );

        return [
            'booking_id' => (int) $booking->id,
            'guiding_id' => (int) $booking->guiding_id,
            'tour_name' => (string) ($booking->guiding->title ?? '—'),
            'fishing_date' => $booking->getFormattedBookingDate('Y-m-d') ?? '—',
            'overall_score' => $scores['overall_score'],
            'guide_score' => $scores['guide_score'],
            'region_water_score' => $scores['region_water_score'],
            'grandtotal_score' => $grandtotal,
        ];
    }

    public function ensureAutomaticReview(Booking $booking): bool
    {
        if (!$this->isEligible($booking, Carbon::now())) {
            return false;
        }

        $fishingDate = $booking->getBookingDate();
        if ($fishingDate === null) {
            return false;
        }

        return (bool) DB::transaction(function () use ($booking, $fishingDate) {
            $scores = self::automaticCategoryScores();

            $review = Review::create([
                'comment' => self::commentForBooking($booking),
                'overall_score' => $scores['overall_score'],
                'guide_score' => $scores['guide_score'],
                'region_water_score' => $scores['region_water_score'],
                'user_id' => $booking->user_id,
                'guide_id' => $booking->guiding->user_id,
                'booking_id' => $booking->id,
                'guiding_id' => $booking->guiding_id,
                'is_automatic' => true,
            ]);

            $review->created_at = $fishingDate;
            $review->updated_at = $fishingDate;
            $review->saveQuietly();

            $booking->is_reviewed = true;
            $booking->save();

            return true;
        });
    }
}
