<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Rating;
use App\Models\Review;
use App\Models\Guiding;
use App\Models\Booking;

class MigrateReviews extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'migrate:reviews';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Migrate reviews from old database to new database';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $ratings = Rating::all();
        $this->info('Starting review migration...');
        $migrated = 0;
        $skipped = 0;

        foreach ($ratings as $rating) {
            $rate = $rating->rating * 2;

            $guidings = Guiding::where('user_id', $rating->guide_id)->first();
            if (!$guidings) {
                $this->warn("No guiding found for guide ID: {$rating->guide_id}");
                $skipped++;
                continue;
            }

            $bookings = Booking::where('guiding_id', $guidings->id)->first();
            if (!$bookings) {
                $this->warn("No booking found for guiding ID: {$guidings->id}");
                $skipped++;
                continue;
            }

            // Check if review already exists
            $existingReview = Review::where([
                'user_id' => $rating->user_id,
                'guide_id' => $rating->guide_id,
                'guiding_id' => $guidings->id,
                'booking_id' => $bookings->id
            ])->first();

            if ($existingReview) {
                $this->line("Review already exists for user {$rating->user_id} and guide {$rating->guide_id}. Skipping.");
                $skipped++;
                continue;
            }

            $review = new Review();
            $review->comment = $rating->description;
            $review->overall_score = $rate;
            $review->guide_score = $rate;
            $review->region_water_score = $rate;
            $review->user_id = $rating->user_id;
            $review->guide_id = $rating->guide_id;
            $review->guiding_id = $guidings->id;
            $review->booking_id = $bookings->id;
            $review->save();
            
            $migrated++;
        }

        $this->info("Migration completed: {$migrated} reviews migrated, {$skipped} skipped.");
    }
} 