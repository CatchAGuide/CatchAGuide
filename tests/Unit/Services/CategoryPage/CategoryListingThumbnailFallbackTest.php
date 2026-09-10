<?php

namespace Tests\Unit\Services\CategoryPage;

use App\Enums\GuideStatus;
use App\Models\Camp;
use App\Models\CategoryEntity;
use App\Models\CategoryPage;
use App\Models\FishingType;
use App\Models\Guiding;
use App\Models\Method;
use App\Models\Target;
use App\Models\User;
use App\Services\CategoryPage\CategoryListingThumbnailFallback;
use App\Services\CategoryPage\FavoriteTargetSpeciesResolver;
use App\Services\Homepage\HomepageCountrySelector;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Cache;
use Tests\TestCase;

class CategoryListingThumbnailFallbackTest extends TestCase
{
    use DatabaseTransactions;

    protected function setUp(): void
    {
        parent::setUp();

        Cache::forget(CategoryListingThumbnailFallback::CACHE_KEY);
        Cache::forget('guiding_category_availability_v1');
    }

    public function test_country_without_cms_image_uses_tour_thumbnail(): void
    {
        $marker = 'fallback-country-'.uniqid();
        $tourPath = 'guidings/9001/'.$marker.'.webp';

        CategoryEntity::countries()->create([
            'type' => 'country',
            'name' => $marker,
            'slug' => $marker,
            'countrycode' => '',
            'thumbnail_path' => null,
        ]);

        $this->createTour([
            'country' => $marker,
            'thumbnail_path' => $tourPath,
        ]);

        Cache::forget(CategoryListingThumbnailFallback::CACHE_KEY);

        $url = app(CategoryListingThumbnailFallback::class)->url(
            null,
            CategoryListingThumbnailFallback::KIND_COUNTRY,
            $marker,
        );

        $this->assertStringContainsString($tourPath, $url);
        $this->assertStringNotContainsString('300x300.png', $url);
    }

    public function test_country_cms_image_wins_over_tour_thumbnail(): void
    {
        $marker = 'fallback-own-'.uniqid();
        $ownPath = 'category/'.$marker.'.webp';
        $tourPath = 'guidings/9002/'.$marker.'.webp';

        $this->createTour([
            'country' => $marker,
            'thumbnail_path' => $tourPath,
        ]);

        Cache::forget(CategoryListingThumbnailFallback::CACHE_KEY);

        $url = app(CategoryListingThumbnailFallback::class)->url(
            $ownPath,
            CategoryListingThumbnailFallback::KIND_COUNTRY,
            $marker,
        );

        $this->assertStringContainsString($ownPath, $url);
        $this->assertStringNotContainsString($tourPath, $url);
    }

    public function test_target_and_method_without_cms_image_use_tour_thumbnail(): void
    {
        $marker = 'fallback-facet-'.uniqid();

        $target = new Target();
        $target->forceFill([
            'name' => $marker.'-fish',
            'name_en' => $marker.'-fish',
        ])->save();

        $method = new Method();
        $method->forceFill([
            'name' => $marker.'-method',
            'name_en' => $marker.'-method',
        ])->save();

        $targetPath = 'guidings/9003/'.$marker.'-fish.webp';
        $methodPath = 'guidings/9004/'.$marker.'-method.webp';

        $this->createTour([
            'country' => $marker,
            'target_fish' => json_encode([$target->id]),
            'fishing_methods' => json_encode([$method->id]),
            'thumbnail_path' => $targetPath,
        ]);
        $this->createTour([
            'country' => $marker.'-b',
            'fishing_methods' => json_encode([$method->id]),
            'thumbnail_path' => $methodPath,
        ]);

        Cache::forget(CategoryListingThumbnailFallback::CACHE_KEY);
        $fallback = app(CategoryListingThumbnailFallback::class);

        $this->assertStringContainsString(
            $targetPath,
            $fallback->url(null, CategoryListingThumbnailFallback::KIND_TARGET, $target->id),
        );
        $this->assertStringContainsString(
            $methodPath,
            $fallback->url(null, CategoryListingThumbnailFallback::KIND_METHOD, $method->id),
        );
    }

    public function test_country_without_tour_image_uses_camp_thumbnail(): void
    {
        $marker = 'fallback-camp-'.uniqid();
        $campPath = 'camps/9005/'.$marker.'.webp';
        $user = User::factory()->create();

        Camp::query()->create([
            'title' => 'Camp '.$marker,
            'description_camp' => 'desc',
            'description_area' => 'desc',
            'description_fishing' => 'desc',
            'location' => 'Somewhere',
            'country' => $marker,
            'status' => 'active',
            'user_id' => $user->id,
            'thumbnail_path' => $campPath,
        ]);

        Cache::forget(CategoryListingThumbnailFallback::CACHE_KEY);

        $url = app(CategoryListingThumbnailFallback::class)->url(
            null,
            CategoryListingThumbnailFallback::KIND_COUNTRY,
            $marker,
        );

        $this->assertStringContainsString($campPath, $url);
    }

    public function test_homepage_featured_country_uses_tour_image_when_cms_thumb_is_empty(): void
    {
        Cache::flush();

        $marker = 'fallback-rail-'.uniqid();
        $tourPath = 'guidings/9006/'.$marker.'.webp';

        CategoryEntity::countries()->create([
            'type' => 'country',
            'name' => $marker,
            'slug' => $marker,
            'countrycode' => '',
            'thumbnail_path' => null,
        ]);

        $this->createTour([
            'country' => $marker,
            'thumbnail_path' => $tourPath,
        ]);

        $row = app(HomepageCountrySelector::class)->featured()
            ->first(fn (array $row) => $row['slug'] === $marker);

        $this->assertNotNull($row);
        $this->assertStringContainsString($tourPath, $row['thumbnail']);
    }

    public function test_favorite_target_resolver_uses_tour_image_when_page_has_no_thumb(): void
    {
        Cache::flush();

        $marker = 'fallback-species-'.uniqid();
        $tourPath = 'guidings/9007/'.$marker.'.webp';

        $target = new Target();
        $target->forceFill([
            'name' => $marker,
            'name_en' => $marker,
        ])->save();

        CategoryPage::query()->create([
            'name' => $marker,
            'type' => 'Targets',
            'slug' => $marker,
            'source_id' => (string) $target->id,
            'is_favorite' => true,
            'thumbnail_path' => null,
        ]);

        $this->createTour([
            'target_fish' => json_encode([$target->id]),
            'thumbnail_path' => $tourPath,
        ]);

        $card = app(FavoriteTargetSpeciesResolver::class)
            ->resolve(20, [$target->id])
            ->first(fn (array $row) => $row['slug'] === $marker);

        $this->assertNotNull($card);
        $this->assertStringContainsString($tourPath, (string) $card['thumbnail']);
    }

    /**
     * @param  array<string, mixed>  $overrides
     */
    private function createTour(array $overrides = []): Guiding
    {
        $user = User::factory()->create([
            'is_guide' => 1,
            'guide_status' => GuideStatus::VERIFIED,
        ]);

        $guiding = new Guiding();
        $guiding->forceFill(array_merge([
            'title' => 'Fallback Tour '.uniqid(),
            'slug' => 'fallback-tour-'.uniqid(),
            'location' => 'Somewhere',
            'status' => 1,
            'max_guests' => 4,
            'duration' => 4,
            'fishing_type_id' => FishingType::query()->value('id'),
            'user_id' => $user->id,
        ], $overrides))->save();

        return $guiding;
    }
}
