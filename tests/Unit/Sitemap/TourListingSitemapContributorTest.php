<?php

namespace Tests\Unit\Sitemap;

use App\Enums\GuideStatus;
use App\Models\FishingType;
use App\Models\Guiding;
use App\Models\User;
use App\Services\Sitemap\Contributors\TourListingSitemapContributor;
use App\Services\Sitemap\SitemapContext;
use App\Services\Sitemap\SitemapEntry;
use App\Services\Sitemap\SitemapPathEncoder;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Collection;
use Tests\TestCase;

class TourListingSitemapContributorTest extends TestCase
{
    use DatabaseTransactions;

    private function createTour(string $guideStatus): Guiding
    {
        $user = User::factory()->create(['is_guide' => 1, 'guide_status' => $guideStatus]);

        $guiding = new Guiding();
        $guiding->forceFill([
            'title' => 'Sitemap Tour '.uniqid(),
            'slug' => 'sitemap-tour-'.uniqid(),
            'location' => 'Somewhere',
            'country' => 'Schweden',
            'status' => 1,
            'max_guests' => 4,
            'duration' => 4,
            'fishing_type_id' => FishingType::query()->value('id'),
            'user_id' => $user->id,
        ])->save();

        return $guiding;
    }

    /**
     * @return Collection<string, SitemapEntry>
     */
    private function entries(): Collection
    {
        return (new TourListingSitemapContributor(new SitemapPathEncoder()))
            ->entries(new SitemapContext('https://www.catchaguide.com', 'en'))
            ->keyBy(fn (SitemapEntry $entry) => $entry->loc);
    }

    public function test_lists_publicly_visible_tours_under_offer_slug_with_their_own_lastmod(): void
    {
        $tour = $this->createTour(GuideStatus::VERIFIED);

        $entry = $this->entries()->get('https://www.catchaguide.com/guidings/offer/'.$tour->slug);

        $this->assertNotNull($entry);
        $this->assertSame($tour->fresh()->updated_at->toAtomString(), $entry->lastmod);
    }

    /**
     * A published tour whose guide isn't verified doesn't render publicly — submitting it is
     * what put more URLs in the old listing file than there were tours online.
     */
    public function test_excludes_tours_that_are_not_publicly_visible(): void
    {
        $tour = $this->createTour(GuideStatus::PENDING);

        $this->assertFalse($this->entries()->has('https://www.catchaguide.com/guidings/offer/'.$tour->slug));
    }
}
