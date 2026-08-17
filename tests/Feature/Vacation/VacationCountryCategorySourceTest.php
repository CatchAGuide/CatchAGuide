<?php

namespace Tests\Feature\Vacation;

use App\Domain\CategoryPage\CategoryPageEntityType;
use App\Domain\CategoryPage\CategoryPageScope;
use App\Models\Country;
use App\Models\CountryTranslation;
use App\Models\Language;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\URL;
use Tests\TestCase;

class VacationCountryCategorySourceTest extends TestCase
{
    use DatabaseTransactions;

    protected function setUp(): void
    {
        parent::setUp();

        config(['app.url' => 'http://localhost']);
        URL::forceRootUrl('http://localhost');
        app()->setLocale('en');

        $this->withoutMiddleware([
            \Illuminate\Routing\Middleware\ThrottleRequests::class,
            \App\Http\Middleware\DDoSProtectionMiddleware::class,
        ]);
    }

    public function test_country_page_uses_vacations_category_title_not_tours(): void
    {
        $slug = 'test-vac-cms-'.uniqid();
        $country = Country::query()->create([
            'name' => 'Vacationland',
            'slug' => $slug,
            'countrycode' => '',
        ]);

        CountryTranslation::query()->create([
            'country_id' => $country->id,
            'language' => 'en',
            'title' => 'Fishing in Vacationland – the best tours, waters & seasons',
            'sub_title' => 'All information about fishing in Vacationland',
            'introduction' => 'Tours intro',
            'content' => 'Tours body',
            'faq_title' => '',
        ]);

        Language::query()->create([
            'source_id' => (string) $country->id,
            'type' => CategoryPageEntityType::GEO_COUNTRY,
            'scope' => CategoryPageScope::VACATIONS,
            'language' => 'en',
            'title' => 'Fishing vacation in Vacationland',
            'sub_title' => 'Camps and trips in Vacationland',
            'introduction' => 'Vacation intro',
            'content' => 'Vacation body',
            'faq_title' => '',
        ]);

        $response = $this->get(route('vacations.country', $slug));

        $response->assertOk();
        $response->assertSeeText('Fishing vacation in Vacationland');
        $response->assertSeeText('Camps and trips in Vacationland');
        $response->assertDontSeeText('the best tours, waters & seasons');
        $response->assertSee('value="'.$slug.'"', false);
    }

    public function test_country_page_without_vacations_cms_does_not_fall_back_to_tours_title(): void
    {
        $slug = 'test-vac-legacy-'.uniqid();
        $country = Country::query()->create([
            'name' => 'Toursonlyland',
            'slug' => $slug,
            'countrycode' => '',
        ]);

        CountryTranslation::query()->create([
            'country_id' => $country->id,
            'language' => 'en',
            'title' => 'Fishing in Toursonlyland – the best tours, waters & seasons',
            'sub_title' => 'All information about fishing in Toursonlyland',
            'introduction' => 'Tours intro',
            'content' => 'Tours body',
            'faq_title' => '',
        ]);

        $response = $this->get(route('vacations.country', $slug));

        $response->assertOk();
        $response->assertDontSeeText('the best tours, waters & seasons');
        $response->assertSeeText('Fishing vacation in');
        $response->assertSeeText('Toursonlyland');
    }
}
