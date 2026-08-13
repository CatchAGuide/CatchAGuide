<?php

namespace Tests\Unit\Services\Homepage;

use App\Domain\Vacation\CountrySlug;
use App\Models\CategoryPage;
use App\Models\Country;
use App\Models\MonthlyHighlight;
use App\Models\Target;
use App\Services\Homepage\HomepageLandingService;
use Illuminate\Support\Facades\Cache;
use ReflectionMethod;
use Tests\TestCase;

class HomepageMonthlyHighlightTest extends TestCase
{
    public function test_season_module_uses_active_monthly_highlight_pairs(): void
    {
        $month = (int) now()->month;
        $country = Country::query()->first();
        $targetPage = CategoryPage::query()->where('type', 'Targets')->first();
        if (! $country || ! $targetPage) {
            $this->markTestSkipped('Need country and target category page.');
        }

        Cache::flush();

        $existing = MonthlyHighlight::query()->where('month', $month)->first();
        $original = $existing?->only([
            'month',
            'title_en',
            'title_de',
            'subtitle_en',
            'subtitle_de',
            'items',
            'is_active',
        ]);
        $payload = [
            'month' => $month,
            'title_en' => 'Highlight title EN',
            'title_de' => 'Highlight title DE',
            'subtitle_en' => 'Highlight sub EN',
            'subtitle_de' => 'Highlight sub DE',
            'items' => [
                [
                    'type' => MonthlyHighlight::ITEM_TYPE_PAIR,
                    'country_id' => $country->id,
                    'target_id' => $targetPage->id,
                ],
            ],
            'is_active' => true,
        ];

        if ($existing) {
            $existing->update($payload);
            $highlight = $existing->fresh();
        } else {
            $highlight = MonthlyHighlight::query()->create($payload);
        }

        try {
            $service = app(HomepageLandingService::class);
            $method = new ReflectionMethod(HomepageLandingService::class, 'seasonModule');
            $method->setAccessible(true);
            $season = $method->invoke($service, 'en');

            $target = Target::query()->find($targetPage->source_id);
            $fishName = $target?->name ?? $targetPage->name;

            $this->assertSame('Highlight title EN', $season['title']);
            $this->assertSame('Highlight sub EN', $season['text']);
            $this->assertLessThanOrEqual(3, $season['species']->count());
            $this->assertTrue($season['species']->contains(fn ($card) => ($card['fish'] ?? null) === $fishName));
            $this->assertTrue($season['species']->contains(fn ($card) => filled($card['country'] ?? null)));
            $this->assertTrue($season['species']->every(fn ($card) => ! str_contains((string) ($card['name'] ?? ''), 'fishing tours')));

            $pair = $season['species']->first(fn ($card) => ($card['fish'] ?? null) === $fishName);
            $this->assertIsArray($pair);
            $url = (string) $pair['url'];
            $this->assertStringContainsString('/offers', $url);
            parse_str(parse_url($url, PHP_URL_QUERY) ?? '', $query);
            $this->assertSame(CountrySlug::canonicalize($country->slug), $query['country'] ?? null);
            $this->assertNotEmpty($query['place'] ?? null);
            $species = array_map('strval', (array) ($query['species'] ?? []));
            $expectedSpecies = $target?->id ?? $targetPage->source_id;
            $this->assertContains((string) $expectedSpecies, $species);
        } finally {
            if ($original) {
                $existing->update($original);
            } else {
                $highlight->delete();
            }
            Cache::flush();
        }
    }
}
