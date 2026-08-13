<?php

namespace Tests\Unit\Vacation;

use App\Models\Country;
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
}
