<?php

namespace Tests\Feature\Translation;

use App\Jobs\TranslateListingJob;
use App\Models\Camp;
use App\Models\FishingType;
use App\Models\Guiding;
use App\Models\User;
use App\Models\Vacation;
use App\Services\Translation\ListingTranslationService;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Queue;
use Tests\TestCase;

/**
 * Creating/updating a translatable listing must queue a TranslateListingJob automatically
 * (Model::saved() closures registered in AppServiceProvider::registerAutomaticTranslationDispatch())
 * instead of relying on someone remembering to run the manual artisan translate commands. The job
 * itself decides whether an API call is actually needed via the content-hash "needs update"
 * check. Dispatch is additionally debounced per listing (services.translation.dispatch_cooldown_seconds)
 * so a burst of rapid saves on the same listing can't flood the job queue or the free translation
 * engine.
 */
class AutomaticTranslationDispatchTest extends TestCase
{
    use DatabaseTransactions;

    private function createCamp(array $overrides = []): Camp
    {
        $user = User::factory()->create();

        $camp = new Camp();
        $camp->forceFill(array_merge([
            'title' => 'Test Camp '.uniqid(),
            'description_camp' => 'Camp desc',
            'description_area' => 'Area desc',
            'description_fishing' => 'Fishing desc',
            'location' => 'Somewhere',
            'status' => 'active',
            'user_id' => $user->id,
        ], $overrides))->save();

        return $camp;
    }

    private function createGuiding(array $overrides = []): Guiding
    {
        $user = User::factory()->create();

        $guiding = new Guiding();
        $guiding->forceFill(array_merge([
            'title' => 'Test Guiding '.uniqid(),
            'location' => 'Somewhere',
            'status' => 1,
            'max_guests' => 4,
            'duration' => 4,
            'price' => 99.0,
            'fishing_type_id' => FishingType::query()->value('id'),
            'user_id' => $user->id,
        ], $overrides))->save();

        return $guiding;
    }

    private function createVacation(array $overrides = []): Vacation
    {
        $vacation = new Vacation();
        $vacation->forceFill(array_merge([
            'title' => 'Test Vacation '.uniqid(),
            'slug' => 'test-vacation-'.uniqid(),
            'location' => 'Somewhere',
            'city' => 'Somewhere',
            'country' => 'Germany',
            'latitude' => '0',
            'longitude' => '0',
            'region' => 'Bavaria',
            'best_travel_times' => 'Summer',
            'surroundings_description' => 'Lakeside surroundings',
            'target_fish' => ['Pike'],
            'status' => true,
        ], $overrides))->save();

        return $vacation;
    }

    public function test_creating_a_camp_dispatches_a_translation_job(): void
    {
        Queue::fake();

        $camp = $this->createCamp();

        Queue::assertPushed(TranslateListingJob::class, function (TranslateListingJob $job) use ($camp) {
            return $job->listingType === ListingTranslationService::TYPE_CAMP
                && $job->listingId === $camp->id;
        });
    }

    public function test_updating_a_camp_dispatches_another_translation_job(): void
    {
        $camp = $this->createCamp();

        // Simulate that this update happens outside the debounce cooldown window from the
        // create() save above (a real second edit, not a rapid resave).
        Cache::forget("translate-dispatch:".ListingTranslationService::TYPE_CAMP.":{$camp->id}");

        Queue::fake();

        $camp->description_camp = 'Updated description';
        $camp->save();

        Queue::assertPushed(TranslateListingJob::class, 1);
    }

    public function test_rapid_saves_on_the_same_listing_within_the_cooldown_only_dispatch_once(): void
    {
        $camp = $this->createCamp();

        Queue::fake();

        // Three saves in quick succession (e.g. an autosave flurry, or someone hammering the
        // edit form) must collapse into a single queued job while the debounce is active.
        for ($i = 0; $i < 3; $i++) {
            $camp->description_camp = "Updated description {$i}";
            $camp->save();
        }

        Queue::assertPushed(TranslateListingJob::class, 0);
    }

    public function test_dispatch_is_delayed_by_the_cooldown_so_a_later_save_in_the_window_is_captured(): void
    {
        config(['services.translation.dispatch_cooldown_seconds' => 90]);
        Queue::fake();

        $this->createCamp();

        // The job re-reads the listing fresh from the DB when it runs rather than carrying a
        // field snapshot, so delaying it by the full cooldown guarantees any second save that
        // lands within that window (see test above) is already committed by the time it runs.
        Queue::assertPushed(TranslateListingJob::class, function (TranslateListingJob $job) {
            return $job->delay === 90;
        });
    }

    public function test_dispatch_resumes_once_the_cooldown_expires(): void
    {
        config(['services.translation.dispatch_cooldown_seconds' => 0]);

        $camp = $this->createCamp();

        Queue::fake();

        $camp->description_camp = 'Updated after cooldown disabled';
        $camp->save();

        Queue::assertPushed(TranslateListingJob::class, 1);
    }

    public function test_creating_a_guiding_dispatches_a_translation_job(): void
    {
        Queue::fake();

        $guiding = $this->createGuiding();

        Queue::assertPushed(TranslateListingJob::class, function (TranslateListingJob $job) use ($guiding) {
            return $job->listingType === 'guiding' && $job->listingId === $guiding->id;
        });
    }

    public function test_creating_a_vacation_dispatches_a_translation_job(): void
    {
        Queue::fake();

        $vacation = $this->createVacation();

        Queue::assertPushed(TranslateListingJob::class, function (TranslateListingJob $job) use ($vacation) {
            return $job->listingType === 'vacation' && $job->listingId === $vacation->id;
        });
    }

    public function test_dispatch_is_skipped_when_auto_translate_is_disabled(): void
    {
        config(['services.translation.auto_translate' => false]);
        Queue::fake();

        $this->createCamp();

        Queue::assertNotPushed(TranslateListingJob::class);
    }
}
