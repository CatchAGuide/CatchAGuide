<?php

namespace Tests\Unit\Services\Homepage;

use App\Enums\GuideStatus;
use App\Models\CategoryEntity;
use App\Models\FishingType;
use App\Models\Guiding;
use App\Models\Target;
use App\Models\User;
use App\Services\Homepage\HomepageMixedOfferSelector;
use App\Services\Offers\OfferCatalogPageService;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Mockery;
use Tests\TestCase;

class HomepageMixedOfferSelectorTest extends TestCase
{
    use DatabaseTransactions;

    private function createTour(string $createdAt): Guiding
    {
        $user = User::factory()->create([
            'is_guide' => 1,
            'guide_status' => GuideStatus::VERIFIED,
        ]);

        $guiding = new Guiding();
        $guiding->forceFill([
            'title' => 'Homepage Tour '.uniqid(),
            'slug' => 'homepage-tour-'.uniqid(),
            'location' => 'Somewhere',
            'status' => 1,
            'max_guests' => 4,
            'duration' => 4,
            'fishing_type_id' => FishingType::query()->value('id'),
            'user_id' => $user->id,
        ])->save();

        DB::table('guidings')
            ->where('id', $guiding->id)
            ->update(['created_at' => $createdAt]);

        return $guiding->fresh();
    }

    public function test_by_module_orders_tours_newest_first_like_camps_and_trips(): void
    {
        Cache::flush();

        $older = $this->createTour(now()->addDays(2)->toDateTimeString());
        $newer = $this->createTour(now()->addDays(3)->toDateTimeString());

        $this->assertTrue($newer->created_at->greaterThan($older->created_at));

        $tours = app(HomepageMixedOfferSelector::class)->byModule(8)['tour'];
        $ids = $tours->pluck('id')->map(fn ($id) => (int) $id)->all();

        $this->assertSame(
            [$newer->id, $older->id],
            array_values(array_intersect($ids, [$newer->id, $older->id])),
        );
    }

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

    public function test_destination_region_queries_use_the_tours_catalog_geo_scope(): void
    {
        // Region pages used to match the listings' free-text region column against the page's
        // name, which found nothing for most regions (Rheindelta, Småland, Algarve...), so the
        // page showed no offers and the inventory gate noindexed it. They must scope offers
        // the way /guidings/{country}/{region} does: by the region's stored centroid.
        $country = CategoryEntity::countries()->make(['name' => 'Niederlande', 'slug' => 'niederlande', 'countrycode' => 'NL']);
        $region = CategoryEntity::regions()->make([
            'name' => 'Rheindelta',
            'slug' => 'rheindelta',
            'filters' => ['placeLat' => '51.7', 'placeLng' => '4.3', 'region' => 'Zuid-Holland'],
        ]);

        $query = Mockery::mock(Builder::class);
        $query->shouldReceive('count')->andReturn(5, 2, 1);
        $catalog = Mockery::mock(OfferCatalogPageService::class);
        $catalog->shouldReceive('listingQueries')
            ->once()
            ->withArgs(fn (array $input) => $input['placeLat'] === 51.7
                && $input['placeLng'] === 4.3
                && $input['region'] === 'Zuid-Holland'
                && $input['place_types'] === ['administrative_area_level_1']
                && ! isset($input['type']))
            ->andReturn(['tour' => $query, 'trip' => $query, 'camp' => $query]);
        $this->app->instance(OfferCatalogPageService::class, $catalog);

        $this->assertSame(8, app(HomepageMixedOfferSelector::class)->countForDestination($country, $region));
    }
}
