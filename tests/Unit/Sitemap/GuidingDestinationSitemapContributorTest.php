<?php

namespace Tests\Unit\Sitemap;

use App\Enums\GuideStatus;
use App\Models\CategoryEntity;
use App\Models\FishingType;
use App\Models\Guiding;
use App\Models\User;
use App\Repositories\Guiding\GuidingCategoryAvailabilityRepository;
use App\Services\Sitemap\Contributors\GuidingDestinationSitemapContributor;
use App\Services\Sitemap\SitemapContext;
use App\Services\Sitemap\SitemapEntry;
use App\Services\Sitemap\SitemapPathEncoder;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Cache;
use Tests\TestCase;

class GuidingDestinationSitemapContributorTest extends TestCase
{
    use DatabaseTransactions;

    protected function setUp(): void
    {
        parent::setUp();

        Cache::forget('guiding_category_availability_v1');
    }

    private function contributor(): GuidingDestinationSitemapContributor
    {
        return new GuidingDestinationSitemapContributor(
            new SitemapPathEncoder(),
            app(GuidingCategoryAvailabilityRepository::class),
        );
    }

    private function createTour(string $country): Guiding
    {
        $user = User::factory()->create([
            'is_guide' => 1,
            'guide_status' => GuideStatus::VERIFIED,
        ]);

        $guiding = new Guiding();
        $guiding->forceFill([
            'title' => 'Sitemap Tour '.uniqid(),
            'slug' => 'sitemap-tour-'.uniqid(),
            'location' => 'Somewhere',
            'country' => $country,
            'status' => 1,
            'max_guests' => 4,
            'duration' => 4,
            'fishing_type_id' => FishingType::query()->value('id'),
            'user_id' => $user->id,
        ])->save();

        return $guiding;
    }

    public function test_includes_hub_and_countries_with_tours_excludes_countries_without(): void
    {
        $marker = 'sitemap-guiding-'.uniqid();
        $withTours = CategoryEntity::countries()->create([
            'type' => 'country',
            'name' => 'Sitemap Spain',
            'slug' => $marker,
            'countrycode' => '',
        ]);
        $withoutTours = CategoryEntity::countries()->create([
            'type' => 'country',
            'name' => 'Sitemap Empty',
            'slug' => 'sitemap-empty-'.uniqid(),
            'countrycode' => '',
        ]);

        $this->createTour($marker);
        Cache::forget('guiding_category_availability_v1');

        $locs = $this->contributor()
            ->entries(new SitemapContext('https://www.catchaguide.com', 'en'))
            ->map(fn (SitemapEntry $e) => $e->loc)
            ->all();

        $this->assertContains('https://www.catchaguide.com/guidings/countries', $locs);
        $this->assertContains('https://www.catchaguide.com/guidings/'.$withTours->slug, $locs);
        $this->assertNotContains('https://www.catchaguide.com/guidings/'.$withoutTours->slug, $locs);
    }

    /**
     * A capitalized/umlaut country slug must never appear in the sitemap as-is — only its
     * canonical lowercase form, matching GuidingDestinationController::show()'s 301 redirect
     * (see CLAUDE.md's "SEO / catalog page conventions").
     */
    public function test_lists_canonical_slug_not_raw_uppercase_umlaut_slug(): void
    {
        $marker = 'Österreich-'.uniqid();
        $country = CategoryEntity::countries()->create([
            'type' => 'country',
            'name' => 'Österreich',
            'slug' => $marker,
            'countrycode' => '',
        ]);

        $this->createTour($marker);
        Cache::forget('guiding_category_availability_v1');

        $locs = $this->contributor()
            ->entries(new SitemapContext('https://www.catchaguide.com', 'de'))
            ->map(fn (SitemapEntry $e) => $e->loc)
            ->all();

        $canonical = \App\Domain\Vacation\CountrySlug::canonicalize($country->slug);

        $this->assertContains('https://www.catchaguide.com/guidings/'.rawurlencode($canonical), $locs);
        $this->assertNotContains('https://www.catchaguide.com/guidings/'.rawurlencode($country->slug), $locs);
    }
}
