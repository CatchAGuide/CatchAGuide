<?php

namespace Tests\Unit\Translation;

use App\Jobs\TranslateListingJob;
use App\Models\Guiding;
use App\Models\Language;
use App\Models\User;
use App\Services\Translation\GuidingTranslationService;
use App\Services\Translation\ListingTranslationService;
use App\Services\Translation\Support\FishingCopyGoogleTranslator;
use App\Services\Translation\VacationTranslationService;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

/**
 * guidings.language defaults to "de" and the EN/DE heuristics can't see any other language, so a
 * tour written in Dutch was stored as "de": catchaguide.de served the Dutch original as German and
 * the English translation was made from the wrong source ("roofvistrip" -> "roof trip").
 */
class GuidingSourceLanguageCorrectionTest extends TestCase
{
    use DatabaseTransactions;

    private const DUTCH_TITLE = 'De ultieme roofvistrip in Nederland, met ervaren gids en professionele boot';

    private const DUTCH_DESCRIPTION = 'Ervaar de spanning van het roofvissen vanaf de boot! Stap aan boord voor een avontuurlijke roofvistrip.';

    protected function setUp(): void
    {
        parent::setUp();

        // The saved() observer would otherwise queue a real TranslateListingJob for every guiding.
        config(['services.translation.auto_translate' => false]);
    }

    /**
     * Stands in for the free Google endpoint: fixed detection, translations tagged with their
     * from/to pair, and a count of detection calls.
     */
    private function fakeTranslator(?string $detected): FishingCopyGoogleTranslator
    {
        return new class($detected) extends FishingCopyGoogleTranslator
        {
            public int $detectCalls = 0;

            /** @var list<string> */
            public array $sourceLanguages = [];

            public function __construct(private ?string $detected) {}

            public function detectLanguage(string $text): ?string
            {
                $this->detectCalls++;

                return $this->detected;
            }

            public function batchTranslate(array $fields, string $toLanguage, string $fromLanguage): array
            {
                $this->sourceLanguages[] = $fromLanguage;

                return array_map(fn (string $text) => "[{$fromLanguage}->{$toLanguage}] {$text}", $fields);
            }
        };
    }

    private function dutchGuiding(string $storedLanguage = 'de'): Guiding
    {
        $guiding = new Guiding();
        $guiding->forceFill([
            'title' => self::DUTCH_TITLE.' '.uniqid(),
            'description' => self::DUTCH_DESCRIPTION,
            'language' => $storedLanguage,
            'user_id' => User::factory()->create()->id,
            'location' => 'Willemstad, Nederland',
            'max_guests' => 3,
            'duration' => 6,
            'fishing_type_id' => 1,
        ])->save();

        return $guiding->fresh();
    }

    private function storeTranslation(Guiding $guiding, string $language, string $title, ?string $hash = 'stale'): Language
    {
        return Language::create([
            'source_id' => $guiding->id,
            'type' => 'guidings',
            'language' => $language,
            'title' => $title,
            'json_data' => ['title' => $title],
            'content' => $hash,
        ]);
    }

    private function runJob(Guiding $guiding, GuidingTranslationService $service): void
    {
        (new TranslateListingJob('guiding', $guiding->id))->handle(
            new ListingTranslationService(),
            $service,
            app(VacationTranslationService::class),
        );
    }

    public function test_dutch_tour_stored_as_german_is_corrected_and_its_translations_marked_stale(): void
    {
        $guiding = $this->dutchGuiding();
        $translation = $this->storeTranslation($guiding, 'en', 'The ultimate roof trip', 'current-hash');
        $service = new GuidingTranslationService($this->fakeTranslator('nl'));

        $this->assertTrue($service->correctSourceLanguage($guiding));

        $this->assertSame('nl', $guiding->fresh()->language);
        $this->assertNull($translation->fresh()->content);
    }

    public function test_detection_runs_once_per_content_version(): void
    {
        $guiding = $this->dutchGuiding('nl');
        $translator = $this->fakeTranslator('nl');
        $service = new GuidingTranslationService($translator);

        $this->assertFalse($service->correctSourceLanguage($guiding));
        $this->assertFalse($service->correctSourceLanguage($guiding->fresh()));
        $this->assertSame(1, $translator->detectCalls);

        $guiding->update(['description' => self::DUTCH_DESCRIPTION.' Nieuwe tekst.']);
        $service->correctSourceLanguage($guiding->fresh());
        $this->assertSame(2, $translator->detectCalls);
    }

    public function test_a_language_set_by_hand_is_kept_until_the_content_changes(): void
    {
        $guiding = $this->dutchGuiding();
        $translator = $this->fakeTranslator('de');
        $service = new GuidingTranslationService($translator);

        $service->changeSourceLanguage($guiding, 'nl');

        $this->assertFalse($service->correctSourceLanguage($guiding->fresh()));
        $this->assertSame('nl', $guiding->fresh()->language);
        $this->assertSame(0, $translator->detectCalls);
    }

    public function test_failed_detection_leaves_the_language_alone(): void
    {
        $guiding = $this->dutchGuiding();
        $service = new GuidingTranslationService($this->fakeTranslator(null));

        $this->assertFalse($service->correctSourceLanguage($guiding));
        $this->assertSame('de', $guiding->fresh()->language);
    }

    public function test_job_translates_a_dutch_tour_into_german_and_english_from_dutch(): void
    {
        $guiding = $this->dutchGuiding();
        $this->storeTranslation($guiding, 'en', 'The ultimate roof trip', 'current-hash');
        $translator = $this->fakeTranslator('nl');

        $this->runJob($guiding, new GuidingTranslationService($translator));

        $this->assertSame('nl', $guiding->fresh()->language);
        $this->assertSame(['nl', 'nl'], $translator->sourceLanguages);

        foreach (['de', 'en'] as $target) {
            $rows = Language::where(['source_id' => $guiding->id, 'type' => 'guidings', 'language' => $target])->get();
            $this->assertCount(1, $rows, "one {$target} translation row");
            $this->assertStringStartsWith("[nl->{$target}] De ultieme roofvistrip", $rows->first()->title);
        }
    }

    public function test_retranslating_collapses_duplicate_rows_left_by_title_changes(): void
    {
        $guiding = $this->dutchGuiding('nl');
        $this->storeTranslation($guiding, 'en', 'Old title');
        $this->storeTranslation($guiding, 'en', 'Older title');

        $this->runJob($guiding, new GuidingTranslationService($this->fakeTranslator('nl')));

        $rows = Language::where(['source_id' => $guiding->id, 'type' => 'guidings', 'language' => 'en'])->get();
        $this->assertCount(1, $rows);
        $this->assertStringStartsWith('[nl->en] ', $rows->first()->title);
    }
}
