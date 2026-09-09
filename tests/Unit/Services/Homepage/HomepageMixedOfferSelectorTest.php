<?php

namespace Tests\Unit\Services\Homepage;

use App\Models\CategoryEntity;
use App\Models\Target;
use App\Services\Homepage\HomepageMixedOfferSelector;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Cache;
use Tests\TestCase;

class HomepageMixedOfferSelectorTest extends TestCase
{
    use DatabaseTransactions;

    public function test_mixed_returns_collection_with_expected_keys_when_data_exists(): void
    {
        Cache::flush();

        $selector = app(HomepageMixedOfferSelector::class);
        $mixed = $selector->mixed(6);

        $this->assertLessThanOrEqual(6, $mixed->count());

        if ($mixed->isEmpty()) {
            $this->markTestSkipped('No guidings/trips/camps available in test database.');
        }

        $types = $mixed->pluck('type')->unique()->values();
        $this->assertTrue($types->every(fn ($type) => in_array($type, ['tour', 'trip', 'camp'], true)));
        $this->assertTrue($mixed->every(fn ($row) => isset($row['url'], $row['title'], $row['type'])));
    }

    public function test_by_module_returns_separate_rails(): void
    {
        Cache::flush();

        $selector = app(HomepageMixedOfferSelector::class);
        $modules = $selector->byModule(3);

        $this->assertArrayHasKey('tour', $modules);
        $this->assertArrayHasKey('trip', $modules);
        $this->assertArrayHasKey('camp', $modules);

        foreach (['tour', 'camp', 'trip'] as $type) {
            $this->assertLessThanOrEqual(3, $modules[$type]->count());
            $this->assertTrue($modules[$type]->every(fn ($row) => ($row['type'] ?? null) === $type));
        }
    }

    public function test_by_module_for_destination_returns_separate_rails(): void
    {
        Cache::flush();

        $country = CategoryEntity::countries()->create([
            'type' => 'country',
            'name' => 'Selector Spain',
            'slug' => 'selector-spain-'.uniqid(),
            'countrycode' => 'ES',
        ]);

        $selector = app(HomepageMixedOfferSelector::class);
        $modules = $selector->byModuleForDestination($country, null, null, 2);

        $this->assertArrayHasKey('tour', $modules);
        $this->assertArrayHasKey('trip', $modules);
        $this->assertArrayHasKey('camp', $modules);

        foreach (['tour', 'camp', 'trip'] as $type) {
            $this->assertLessThanOrEqual(2, $modules[$type]->count());
            $this->assertTrue($modules[$type]->every(fn ($row) => ($row['type'] ?? null) === $type));
        }
    }

    public function test_by_module_for_target_fish_returns_separate_rails(): void
    {
        Cache::flush();

        $target = new Target();
        $target->forceFill([
            'name' => 'Selector Pike',
            'name_en' => 'Selector Pike',
        ])->save();

        $selector = app(HomepageMixedOfferSelector::class);
        $modules = $selector->byModuleForTargetFish((int) $target->id, 2);

        $this->assertArrayHasKey('tour', $modules);
        $this->assertArrayHasKey('trip', $modules);
        $this->assertArrayHasKey('camp', $modules);

        foreach (['tour', 'camp', 'trip'] as $type) {
            $this->assertLessThanOrEqual(2, $modules[$type]->count());
            $this->assertTrue($modules[$type]->every(fn ($row) => ($row['type'] ?? null) === $type));
        }
    }
}
