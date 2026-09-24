<?php

namespace Tests\Unit\Sitemap;

use App\Enums\GuideStatus;
use App\Models\Camp;
use App\Models\FishingType;
use App\Models\Guiding;
use App\Models\User;
use App\Services\Sitemap\SitemapListingFreshness;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Carbon;
use Tests\TestCase;

/**
 * A facet page's lastmod must move when a listing in it changes — the newest updated_at among
 * the listings that page shows, per country/species/method (tours) and per country (vacations).
 */
class SitemapListingFreshnessTest extends TestCase
{
    use DatabaseTransactions;

    private function tour(string $country, array $targets, array $methods, string $updatedAt, string $guideStatus = GuideStatus::VERIFIED): Guiding
    {
        $user = User::factory()->create(['is_guide' => 1, 'guide_status' => $guideStatus]);
        $guiding = new Guiding();
        $guiding->forceFill([
            'title' => 'Frische Tour '.uniqid(),
            'slug' => 'frische-tour-'.uniqid(),
            'location' => 'Irgendwo',
            'country' => $country,
            'status' => 1,
            'max_guests' => 2,
            'duration' => 4,
            'target_fish' => json_encode($targets),
            'fishing_methods' => json_encode($methods),
            'fishing_type_id' => FishingType::query()->value('id'),
            'user_id' => $user->id,
        ])->save();
        Guiding::query()->whereKey($guiding->id)->update(['updated_at' => $updatedAt]);

        return $guiding;
    }

    public function test_tour_facets_take_the_newest_visible_tour_in_their_set(): void
    {
        $country = 'frischland-'.uniqid();
        $this->tour($country, [990001], [990101], '2031-01-05 10:00:00');
        $this->tour($country, [990001], [], '2031-03-01 08:00:00');
        // Hidden tours don't count — they aren't on the page.
        $this->tour($country, [990001], [990101], '2032-01-01 00:00:00', GuideStatus::PENDING);

        $freshness = app(SitemapListingFreshness::class);

        $this->assertSame('2031-03-01', Carbon::parse($freshness->tourCountry($country))->toDateString());
        $this->assertSame('2031-03-01', Carbon::parse($freshness->tourTarget(990001))->toDateString());
        $this->assertSame('2031-01-05', Carbon::parse($freshness->tourMethod(990101))->toDateString());
        $this->assertNull($freshness->tourMethod(990199));
    }

    public function test_vacation_country_is_split_by_pillar(): void
    {
        $country = 'Frischland '.uniqid();
        $camp = new Camp();
        $camp->forceFill([
            'title' => 'Frisches Camp',
            'description_camp' => 'x', 'description_area' => 'x', 'description_fishing' => 'x',
            'location' => 'Irgendwo',
            'country' => $country,
            'status' => 'active',
            'user_id' => User::factory()->create()->id,
        ])->save();
        Camp::query()->whereKey($camp->id)->update(['updated_at' => '2031-02-02 12:00:00']);

        $freshness = app(SitemapListingFreshness::class);
        $slug = str_replace(' ', '-', mb_strtolower($country));

        $this->assertSame('2031-02-02', Carbon::parse($freshness->vacationCountry($slug))->toDateString());
        $this->assertSame('2031-02-02', Carbon::parse($freshness->vacationCountry($slug, 'camps'))->toDateString());
        $this->assertNull($freshness->vacationCountry($slug, 'trips'));
    }
}
