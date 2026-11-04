<?php

namespace Tests\Feature\Translation;

use App\Models\Language;
use App\Models\Vacation;
use App\Services\Translation\Support\FishingCopyGoogleTranslator;
use App\Services\Translation\VacationTranslationService;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use RuntimeException;
use Tests\TestCase;

/**
 * Production log, 4 Nov 2026: "Vacation relations translation failed ... Column 'title' cannot
 * be null". The call that filled $translatedData had been commented out, so every package,
 * accommodation, boat, guiding and extra was written with a null title, the insert failed, and
 * the loop stopped at the first item.
 *
 * The free endpoint is rate limited (429), so these also pin how many calls a run makes.
 */
class VacationRelationTranslationTest extends TestCase
{
    use DatabaseTransactions;

    private int $calls = 0;

    public function test_components_are_translated_and_stored(): void
    {
        $this->fakeTranslator(fn (string $text) => 'EN: '.$text);
        [$vacation, $package, $extra] = $this->makeVacation();

        $this->assertTrue($this->service()->translateVacationRelations($vacation, 'en'));

        $this->assertSame('EN: Standard-Paket', $this->translation('vacation_package', $package->id)?->title);
        $this->assertSame('EN: Endreinigung', $this->translation('vacation_extra', $extra->id)?->title);
    }

    public function test_a_name_that_translates_to_itself_is_stored_and_not_requested_again(): void
    {
        $this->fakeTranslator(fn (string $text) => $text);
        [$vacation, $package] = $this->makeVacation();

        $this->assertTrue($this->service()->translateVacationRelations($vacation, 'en'));
        $this->assertSame('Standard-Paket', $this->translation('vacation_package', $package->id)?->title);

        $callsAfterFirstRun = $this->calls;
        $this->travel(1)->minutes();
        $this->service()->translateVacationRelations($vacation->fresh(), 'en');

        $this->assertSame($callsAfterFirstRun, $this->calls, 'Up-to-date items must not call Google again.');
    }

    public function test_a_failed_call_stores_nothing_and_stops_the_run(): void
    {
        $this->fakeTranslator(fn () => throw new RuntimeException('429 Too Many Requests'));
        [$vacation, $package, $extra] = $this->makeVacation();

        $this->assertFalse($this->service()->translateVacationRelations($vacation, 'en'));

        $this->assertSame(1, $this->calls, 'A rate-limited run must not keep calling for every item.');
        $this->assertNull($this->translation('vacation_package', $package->id));
        $this->assertNull($this->translation('vacation_extra', $extra->id));
    }

    private function service(): VacationTranslationService
    {
        return app(VacationTranslationService::class);
    }

    private function fakeTranslator(callable $translate): void
    {
        $calls = &$this->calls;

        $this->app->instance(FishingCopyGoogleTranslator::class, new class($translate, $calls) extends FishingCopyGoogleTranslator {
            private $fake;

            private int $count;

            public function __construct(callable $fake, int &$count)
            {
                $this->fake = $fake;
                $this->count = &$count;
            }

            public function translate(string $text, string $toLanguage, string $fromLanguage = 'auto'): string
            {
                $this->count++;

                return ($this->fake)($text);
            }
        });
    }

    /**
     * @return array{0: Vacation, 1: \App\Models\VacationPackage, 2: \App\Models\VacationExtra}
     */
    private function makeVacation(): array
    {
        $vacation = Vacation::withoutEvents(fn () => Vacation::query()->create([
            'title' => 'Test vacation',
            'slug' => 'test-vacation-'.uniqid(),
            'location' => 'Massaluca',
            'country' => 'Spanien',
            'latitude' => '41.2',
            'longitude' => '0.35',
            'best_travel_times' => 'Frühling',
            'surroundings_description' => '<p>Ebro</p>',
            'target_fish' => json_encode([]),
            'status' => 1,
            'language' => 'de',
        ]));

        $package = $vacation->packages()->create(['title' => 'Standard-Paket', 'description' => 'Unterkunft und Boot', 'capacity' => 2]);
        $extra = $vacation->extras()->create(['description' => 'Endreinigung', 'price' => 50, 'type' => 'overall']);

        return [$vacation->fresh(), $package, $extra];
    }

    private function translation(string $type, int $sourceId): ?Language
    {
        return Language::query()->where(['type' => $type, 'source_id' => $sourceId, 'language' => 'en'])->first();
    }
}
