<?php

namespace Tests\Unit\Jobs;

use App\Jobs\TranslateListingJob;
use App\Models\Camp;
use App\Models\Language;
use App\Models\User;
use App\Services\Translation\ListingTranslationService;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

/**
 * The whole point of gating automatic translation behind TranslateListingJob is cost control: a
 * save that didn't change any translatable field must not trigger an API call. These tests run
 * the job for real (no Queue::fake()) but never let it reach the translation engine, by seeding a
 * Language row whose content hash already matches — proving the "needs update" short-circuit
 * fires before any network call would be attempted.
 */
class TranslateListingJobTest extends TestCase
{
    use DatabaseTransactions;

    private ListingTranslationService $translationService;

    protected function setUp(): void
    {
        parent::setUp();

        $this->translationService = new ListingTranslationService();
    }

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

    public function test_job_is_a_no_op_when_the_stored_translation_hash_already_matches(): void
    {
        $camp = $this->createCamp();

        $fields = $this->translationService->getTranslatableFields($camp, ListingTranslationService::TYPE_CAMP);

        $language = Language::create([
            'source_id' => $camp->id,
            'type' => 'camps',
            'language' => 'en',
            'title' => $camp->title,
            'json_data' => ['title' => $camp->title],
            'content' => md5(serialize($fields)),
        ]);

        $updatedAtBefore = $language->updated_at;

        // Runs the job synchronously, in-process. If this reached the translation engine it
        // would attempt a real network call and either hang or throw in this test environment —
        // it must not, because needsTranslationUpdate() should short-circuit first.
        (new TranslateListingJob(ListingTranslationService::TYPE_CAMP, $camp->id))->handle(
            $this->translationService,
            app(\App\Services\Translation\GuidingTranslationService::class),
            app(\App\Services\Translation\VacationTranslationService::class),
        );

        $language->refresh();

        $this->assertTrue($updatedAtBefore->equalTo($language->updated_at));
    }

    public function test_job_does_nothing_when_the_listing_no_longer_exists(): void
    {
        $this->expectNotToPerformAssertions();

        (new TranslateListingJob(ListingTranslationService::TYPE_CAMP, 0))->handle(
            $this->translationService,
            app(\App\Services\Translation\GuidingTranslationService::class),
            app(\App\Services\Translation\VacationTranslationService::class),
        );
    }
}
