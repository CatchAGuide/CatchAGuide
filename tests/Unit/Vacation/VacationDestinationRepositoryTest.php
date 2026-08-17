<?php

namespace Tests\Unit\Vacation;

use App\Domain\CategoryPage\CategoryPageEntityType;
use App\Domain\CategoryPage\CategoryPageScope;
use App\Models\Country;
use App\Models\Language;
use App\Repositories\Vacation\VacationDestinationRepository;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class VacationDestinationRepositoryTest extends TestCase
{
    use DatabaseTransactions;

    public function test_hub_grid_includes_countries_without_vacation_listings(): void
    {
        $marker = 'test-hub-rail-'.uniqid();

        Country::query()->create([
            'name' => 'Zedonia',
            'slug' => $marker,
            'countrycode' => '',
            'thumbnail_path' => 'assets/images/'.$marker.'.jpg',
        ]);

        $grid = app(VacationDestinationRepository::class)->countriesForHubGrid();

        $row = $grid->first(fn (array $item) => $item['slug'] === $marker);

        $this->assertNotNull($row);
        $this->assertSame(0, $row['camps']);
        $this->assertSame(0, $row['trips']);
    }

    public function test_search_dropdown_uses_vacations_category_pages_not_tours(): void
    {
        $marker = 'test-vac-dropdown-'.uniqid();

        $vacationCountry = Country::query()->create([
            'name' => 'Vacationland',
            'slug' => $marker.'-vacations',
            'countrycode' => 'V7',
            'thumbnail_path' => 'assets/images/'.$marker.'-vacations.jpg',
        ]);

        $toursCountry = Country::query()->create([
            'name' => 'Toursland',
            'slug' => $marker.'-tours',
            'countrycode' => 'T7',
            'thumbnail_path' => 'assets/images/'.$marker.'-tours.jpg',
        ]);

        Language::query()->create([
            'source_id' => (string) $vacationCountry->id,
            'type' => CategoryPageEntityType::GEO_COUNTRY,
            'scope' => CategoryPageScope::VACATIONS,
            'language' => 'en',
            'title' => 'Fishing vacation in Vacationland',
            'sub_title' => 'Vacation subtitle',
            'introduction' => 'Vacation intro',
            'content' => 'Vacation body',
            'faq_title' => '',
        ]);

        Language::query()->create([
            'source_id' => (string) $toursCountry->id,
            'type' => CategoryPageEntityType::GEO_COUNTRY,
            'scope' => CategoryPageScope::TOURS,
            'language' => 'en',
            'title' => 'Fishing in Toursland – the best tours, waters & seasons',
            'sub_title' => 'All information about fishing in Toursland',
            'introduction' => 'Tours intro',
            'content' => 'Tours body',
            'faq_title' => '',
        ]);

        $options = app(VacationDestinationRepository::class)->countriesForSearch();
        $slugs = $options->pluck('slug');

        $this->assertTrue($slugs->contains($vacationCountry->slug));
        $this->assertFalse($slugs->contains($toursCountry->slug));
    }
}
