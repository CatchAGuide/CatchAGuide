<?php

namespace Tests\Feature\Destination;

use App\Domain\CategoryPage\CategoryPageEntityType;
use App\Domain\CategoryPage\CategoryPageScope;
use App\Enums\GuideStatus;
use App\Models\CategoryEntity;
use App\Models\Faq;
use App\Models\FishingType;
use App\Models\Guiding;
use App\Models\Language;
use App\Models\User;
use App\Repositories\Vacation\VacationDestinationRepository;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\URL;
use Mockery;
use Tests\TestCase;

class DestinationHubPageTest extends TestCase
{
    use DatabaseTransactions;

    protected function setUp(): void
    {
        parent::setUp();

        config(['app.url' => 'http://localhost']);
        URL::forceRootUrl('http://localhost');
        Cache::forget('guiding_category_availability_v1');

        $this->withoutMiddleware([
            \Illuminate\Routing\Middleware\ThrottleRequests::class,
            \App\Http\Middleware\DDoSProtectionMiddleware::class,
        ]);
    }

    private function createTour(string $country, ?string $countryIso = null): Guiding
    {
        $user = User::factory()->create([
            'is_guide' => 1,
            'guide_status' => GuideStatus::VERIFIED,
        ]);

        $guiding = new Guiding();
        $guiding->forceFill([
            'title' => 'Test Tour '.uniqid(),
            'slug' => 'test-tour-'.uniqid(),
            'location' => 'Somewhere',
            'status' => 1,
            'max_guests' => 4,
            'duration' => 4,
            'fishing_type_id' => FishingType::query()->value('id'),
            'user_id' => $user->id,
            'country' => $country,
            'country_iso' => $countryIso,
        ])->save();

        return $guiding;
    }

    private function hubGridRow(CategoryEntity $country, int $trips, int $camps): array
    {
        return [
            'destination' => $country,
            'slug' => $country->slug,
            'name' => $country->name,
            'sub_title' => null,
            'camps' => $camps,
            'trips' => $trips,
            'thumbnail_path' => null,
            'countrycode' => $country->countrycode,
        ];
    }

    private function mockHubGrid(array $rows): void
    {
        $mock = Mockery::mock(VacationDestinationRepository::class);
        $mock->shouldReceive('countriesForHubGrid')->andReturn(collect($rows));
        $this->app->instance(VacationDestinationRepository::class, $mock);
    }

    public function test_destination_index_falls_back_to_lang_title(): void
    {
        Language::query()
            ->where('type', CategoryPageEntityType::DESTINATION_HUB)
            ->where('source_id', CategoryPageEntityType::DESTINATION_HUB_SOURCE_ID)
            ->delete();

        $response = $this->get(route('destination'));

        $response->assertOk();
        $response->assertSee(__('destination.title'), false);
        $response->assertSee(__('destination.header_sub_title'), false);
        $response->assertSee('cag-site-nav', false);
        $response->assertSee('cag-site-nav-shell', false);
        $response->assertSee('cag-site-nav--overlay', false);
        $response->assertSee('data-category-header-shell', false);
        $response->assertSee('offers-page-header__hero', false);
        $response->assertSee('categoryHeroSearchPlace', false);
        $response->assertSee('data-category-header-search', false);
        $response->assertSee(__('destination.breadcrumb'), false);
        $response->assertDontSee('navbar-custom short-header long-header', false);
    }

    public function test_destination_index_renders_cms_hub_content(): void
    {
        $locale = app()->getLocale();

        Language::query()->create([
            'source_id' => CategoryPageEntityType::DESTINATION_HUB_SOURCE_ID,
            'type' => CategoryPageEntityType::DESTINATION_HUB,
            'scope' => CategoryPageScope::GLOBAL,
            'language' => $locale,
            'title' => 'CMS Fishing Tours Across Europe',
            'sub_title' => 'CMS destination subtitle',
            'introduction' => 'CMS destination introduction copy.',
            'content' => '<p>CMS destination body content.</p>',
            'faq_title' => 'CMS Destination FAQ',
        ]);

        Faq::query()->create([
            'source_id' => CategoryPageEntityType::DESTINATION_HUB_SOURCE_ID,
            'page' => CategoryPageEntityType::DESTINATION_HUB,
            'scope' => CategoryPageScope::GLOBAL,
            'language' => $locale,
            'question' => 'CMS hub FAQ question?',
            'answer' => 'CMS hub FAQ answer.',
        ]);

        $response = $this->get(route('destination'));

        $response->assertOk();
        $response->assertSee('CMS Fishing Tours Across Europe', false);
        $response->assertSee('CMS destination subtitle', false);
        $response->assertSee('CMS destination introduction copy.', false);
        $response->assertSee('CMS destination body content.', false);
        $response->assertSee('CMS Destination FAQ', false);
        $response->assertSee('CMS hub FAQ question?', false);
        $response->assertSee('CMS hub FAQ answer.', false);
        $response->assertDontSee(__('destination.title'), false);
        $response->assertSee('data-category-header-shell', false);
        $response->assertDontSee('navbar-custom short-header long-header', false);
    }

    public function test_destination_index_lowercases_flag_url_regardless_of_stored_case(): void
    {
        $country = CategoryEntity::query()->create([
            'type' => 'country',
            'name' => 'Argentina Test',
            'slug' => 'argentina-test-'.uniqid(),
            'countrycode' => 'AR',
        ]);

        $this->mockHubGrid([$this->hubGridRow($country, trips: 1, camps: 0)]);

        $response = $this->get(route('destination'));

        $response->assertOk();
        $response->assertSee('flags/ar.svg', false);
        $response->assertDontSee('flags/AR.svg', false);
    }

    public function test_destination_index_excludes_countries_with_no_listings(): void
    {
        $withVacations = CategoryEntity::query()->create([
            'type' => 'country',
            'name' => 'Vacation Country',
            'slug' => 'vacation-country-'.uniqid(),
            'countrycode' => 'VC',
        ]);
        $withoutListings = CategoryEntity::query()->create([
            'type' => 'country',
            'name' => 'Empty Country',
            'slug' => 'empty-country-'.uniqid(),
            'countrycode' => 'EC',
        ]);

        $this->mockHubGrid([$this->hubGridRow($withVacations, trips: 1, camps: 0)]);

        $response = $this->get(route('destination'));

        $response->assertOk();
        $response->assertSee(route('destination.country', $withVacations->slug, false), false);
        $response->assertDontSee(route('destination.country', $withoutListings->slug, false), false);
    }

    public function test_destination_index_includes_country_with_tours_only(): void
    {
        $country = CategoryEntity::query()->create([
            'type' => 'country',
            'name' => 'Tours Only Country',
            'slug' => 'tours-only-country-'.uniqid(),
            'countrycode' => 'TC',
        ]);

        $this->createTour($country->slug, $country->countrycode);
        Cache::forget('guiding_category_availability_v1');
        $this->mockHubGrid([]);

        $response = $this->get(route('destination'));

        $response->assertOk();
        $response->assertSee(route('destination.country', $country->slug, false), false);
    }
}
