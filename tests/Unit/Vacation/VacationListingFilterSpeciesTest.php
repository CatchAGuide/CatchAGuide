<?php

namespace Tests\Unit\Vacation;

use App\Domain\Vacation\VacationListingFilter;
use Tests\TestCase;

class VacationListingFilterSpeciesTest extends TestCase
{
    public function test_normalizes_species_ids_and_legacy_names(): void
    {
        [$ids, $names] = VacationListingFilter::normalizeSpecies(['12', 'Pike', '', null, '7']);

        $this->assertSame([12, 7], $ids);
        $this->assertSame(['Pike'], $names);
    }

    public function test_from_request_parses_species_checkboxes(): void
    {
        $filter = VacationListingFilter::fromRequest([
            'pillar' => 'trips',
            'species' => ['3', '9'],
        ]);

        $this->assertSame([3, 9], $filter->speciesIds);
        $this->assertSame([], $filter->speciesNames);
        $this->assertNull($filter->species());
    }

    public function test_from_request_keeps_legacy_species_name(): void
    {
        $filter = VacationListingFilter::fromRequest([
            'species' => 'Hecht',
        ]);

        $this->assertSame([], $filter->speciesIds);
        $this->assertSame(['Hecht'], $filter->speciesNames);
        $this->assertSame('Hecht', $filter->species());
    }
}
