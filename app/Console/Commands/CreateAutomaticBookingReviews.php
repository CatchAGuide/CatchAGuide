<?php

namespace App\Console\Commands;

use App\Models\Booking;
use App\Services\AutomaticReviewService;
use Illuminate\Console\Command;
use Illuminate\Support\Str;

class CreateAutomaticBookingReviews extends Command
{
    protected $signature = 'bookings:create-automatic-reviews
                            {--dry-run : Only list eligible bookings}
                            {--booking= : Only process this booking ID}
                            {--guiding= : Only process bookings for this guiding (tour) ID}';

    protected $description = 'Create automatic reviews 10 days after the fishing date for accepted bookings without a guest review (since 2024)';

    public function handle(AutomaticReviewService $automaticReviews): int
    {
        $dryRun = (bool) $this->option('dry-run');

        $bookingOption = $this->option('booking');
        $guidingOption = $this->option('guiding');

        if (($bookingOption !== null && $bookingOption !== '') && ($guidingOption !== null && $guidingOption !== '')) {
            $this->error('Use only one of --booking or --guiding, not both.');

            return self::FAILURE;
        }

        if ($bookingOption !== null && $bookingOption !== '') {
            $onlyBookingId = (int) $bookingOption;
            if ($onlyBookingId < 1) {
                $this->error('Invalid --booking ID.');

                return self::FAILURE;
            }
        } else {
            $onlyBookingId = null;
        }

        if ($guidingOption !== null && $guidingOption !== '') {
            $onlyGuidingId = (int) $guidingOption;
            if ($onlyGuidingId < 1) {
                $this->error('Invalid --guiding ID.');

                return self::FAILURE;
            }
        } else {
            $onlyGuidingId = null;
        }

        $processed = 0;
        $tableRows = [];
        $now = now();

        $query = Booking::query()
            ->where('status', 'accepted')
            ->where('is_reviewed', false)
            ->whereNotNull('guiding_id')
            ->whereNotNull('user_id')
            ->whereDoesntHave('review')
            ->with(['guiding.user', 'calendar_schedule', 'blocked_event', 'user'])
            ->orderBy('id');

        if ($onlyBookingId !== null) {
            $query->where('id', $onlyBookingId);
        } elseif ($onlyGuidingId !== null) {
            $query->where('guiding_id', $onlyGuidingId);
        }

        $query->chunkById(100, function ($bookings) use ($automaticReviews, $dryRun, $now, &$processed, &$tableRows) {
            foreach ($bookings as $booking) {
                if (!$booking->guiding || !$booking->guiding->user_id) {
                    continue;
                }

                if ($dryRun) {
                    $preview = $automaticReviews->previewEligibleBooking($booking, $now);
                    if ($preview === null) {
                        continue;
                    }
                    $processed++;
                    $tableRows[] = [
                        $preview['booking_id'],
                        $preview['guiding_id'],
                        Str::limit($preview['tour_name'], 48),
                        $preview['fishing_date'],
                        sprintf(
                            '%s / %s / %s → %s',
                            $preview['overall_score'],
                            $preview['guide_score'],
                            $preview['region_water_score'],
                            $preview['grandtotal_score']
                        ),
                    ];

                    continue;
                }

                if ($automaticReviews->ensureAutomaticReview($booking)) {
                    $processed++;
                }
            }
        });

        if ($dryRun) {
            if ($tableRows !== []) {
                $this->table(
                    ['Booking ID', 'Tour ID', 'Tour name', 'Fishing date', 'Overall / Guide / Region → Total'],
                    $tableRows
                );
            }
            $this->info("Eligible bookings (dry run): {$processed}");

            return self::SUCCESS;
        }

        $this->info("Created {$processed} automatic review(s).");

        return self::SUCCESS;
    }
}
