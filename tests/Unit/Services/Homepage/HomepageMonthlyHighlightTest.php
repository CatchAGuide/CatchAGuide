<?php

namespace Tests\Unit\Services\Homepage;

use App\Models\CategoryPage;
use App\Models\Country;
use App\Models\MonthlyHighlight;
use App\Services\Homepage\HomepageLandingService;
use Illuminate\Support\Facades\Cache;
use ReflectionMethod;
use Tests\TestCase;

class HomepageMonthlyHighlightTest extends TestCase
{
    public function test_season_module_uses_active_monthly_highlight(): void
    {
        $month = (int) now()->month;
        $country = Country::query()->first();
        $targetPage = CategoryPage::query()->where('type', 'Targets')->first();
        if (! $country || ! $targetPage) {
            $this->markTestSkipped('Need country and target category page.');
        }

        Cache::flush();

        $existing = MonthlyHighlight::query()->where('month', $month)->first();
        $payload = [
            'month' => $month,
            'title_en' => 'Highlight title EN',
            'title_de' => 'Highlight title DE',
            'subtitle_en' => 'Highlight sub EN',
            'subtitle_de' => 'Highlight sub DE',
            'items' => [
                ['type' => 'country', 'id' => $country->id],
                ['type' => 'target', 'id' => $targetPage->id],
            ],
            'is_active' => true,
        ];

        if ($existing) {
            $existing->update($payload);
            $highlight = $existing->fresh();
        } else {
            $highlight = MonthlyHighlight::query()->create($payload);
        }

        $service = app(HomepageLandingService::class);
        $method = new ReflectionMethod(HomepageLandingService::class, 'seasonModule');
        $method->setAccessible(true);
        $season = $method->invoke($service, 'en');

        $this->assertSame('Highlight title EN', $season['title']);
        $this->assertSame('Highlight sub EN', $season['text']);
        $this->assertLessThanOrEqual(3, $season['species']->count());
        $this->assertTrue($season['species']->contains(fn ($card) => ($card['name'] ?? null) === $country->name));
        $this->assertTrue($season['species']->every(fn ($card) => ! str_contains((string) ($card['name'] ?? ''), 'fishing tours')));

        if (! $existing) {
            $highlight->delete();
        } else {
            // leave restored state alone; test DB may keep highlight
        }
    }
}
