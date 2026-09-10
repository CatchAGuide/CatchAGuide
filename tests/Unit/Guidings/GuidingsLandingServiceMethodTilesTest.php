<?php

namespace Tests\Unit\Guidings;

use App\Models\CategoryPage;
use App\Models\Method;
use App\Presenters\Guiding\GuidingCardPresenter;
use App\Repositories\Guiding\GuidingCategoryAvailabilityRepository;
use App\Services\CategoryPage\CategoryListingThumbnailFallback;
use App\Services\CategoryPage\FavoriteTargetSpeciesResolver;
use App\Services\Guidings\GuidingsLandingService;
use App\Services\Homepage\HomepageCountrySelector;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Collection;
use Mockery;
use ReflectionMethod;
use Tests\TestCase;

class GuidingsLandingServiceMethodTilesTest extends TestCase
{
    use DatabaseTransactions;

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }

    public function test_method_listed_as_available_appears_without_a_pivot_row(): void
    {
        $marker = 'json-method-'.uniqid();
        $method = $this->createMethod($marker);
        $page = $this->createMethodPage($method, $marker, favorite: true);

        $tiles = $this->methodTiles([$method->id]);

        $tile = $tiles->first(fn (array $tile) => $tile['slug'] === $page->slug);

        $this->assertNotNull($tile);
        $this->assertSame($marker, $tile['name']);
        $this->assertSame(route('guidings.methods.show', ['slug' => $page->slug]), $tile['url']);
        $this->assertCount(1, $tiles);
    }

    public function test_method_without_tours_is_omitted(): void
    {
        $marker = 'empty-method-'.uniqid();
        $method = $this->createMethod($marker);
        $page = $this->createMethodPage($method, $marker, favorite: true);

        $tiles = $this->methodTiles([]);

        $this->assertNull($tiles->first(fn (array $tile) => $tile['slug'] === $page->slug));
        $this->assertTrue($tiles->isEmpty());
    }

    public function test_non_favorite_method_with_tours_fills_the_rail(): void
    {
        $favoriteMarker = 'aaa-fav-'.uniqid();
        $extraMarker = 'zzz-extra-'.uniqid();

        $favorite = $this->createMethod($favoriteMarker);
        $favoritePage = $this->createMethodPage($favorite, $favoriteMarker, favorite: true);

        $extra = $this->createMethod($extraMarker);
        $extraPage = $this->createMethodPage($extra, $extraMarker, favorite: false);

        $tiles = $this->methodTiles([$favorite->id, $extra->id]);

        $this->assertSame([
            $favoritePage->slug,
            $extraPage->slug,
        ], $tiles->pluck('slug')->all());
    }

    /**
     * @param  list<int>  $availableMethodIds
     */
    private function methodTiles(array $availableMethodIds): Collection
    {
        $availability = Mockery::mock(GuidingCategoryAvailabilityRepository::class);
        $availability->shouldReceive('methodIdsWithGuidings')->andReturn($availableMethodIds);

        $service = new GuidingsLandingService(
            Mockery::mock(HomepageCountrySelector::class),
            Mockery::mock(FavoriteTargetSpeciesResolver::class),
            new GuidingCardPresenter(),
            $availability,
            app(CategoryListingThumbnailFallback::class),
        );

        $method = new ReflectionMethod(GuidingsLandingService::class, 'methodTiles');
        $method->setAccessible(true);

        return $method->invoke($service, 'test-'.uniqid());
    }

    private function createMethod(string $name): Method
    {
        $method = new Method();
        $method->forceFill([
            'name' => $name,
            'name_en' => $name,
        ])->save();

        return $method;
    }

    private function createMethodPage(Method $method, string $marker, bool $favorite): CategoryPage
    {
        return CategoryPage::query()->create([
            'name' => $marker,
            'type' => 'Methods',
            'slug' => $marker,
            'source_id' => (string) $method->id,
            'is_favorite' => $favorite,
        ]);
    }
}
