<?php

namespace Tests\Unit\Guiding;

use App\Models\FishingFrom;
use App\Models\Guiding;
use Tests\TestCase;

class GuidingShoreOrBoatLabelTest extends TestCase
{
    public function test_boat_flag_is_used_instead_of_private_tour_type(): void
    {
        app()->setLocale('en');

        $guiding = new Guiding([
            'is_boat' => 1,
            'fishing_from_id' => null,
            'tour_type' => 'private',
        ]);
        $guiding->setRelation('fishingFrom', null);

        $this->assertSame(__('guidings.boat'), $guiding->shoreOrBoatLabel());
    }

    public function test_shore_is_used_when_not_a_boat(): void
    {
        app()->setLocale('en');

        $guiding = new Guiding([
            'is_boat' => 0,
            'tour_type' => 'private',
        ]);
        $guiding->setRelation('fishingFrom', null);

        $this->assertSame(__('guidings.shore'), $guiding->shoreOrBoatLabel());
    }

    public function test_fishing_from_name_wins_when_it_is_not_a_privacy_label(): void
    {
        app()->setLocale('en');

        $guiding = new Guiding([
            'is_boat' => 1,
            'tour_type' => 'private',
        ]);
        $from = new FishingFrom();
        $from->setRawAttributes([
            'id' => 1,
            'name' => 'Boot',
            'name_en' => 'Boat',
        ]);
        $guiding->setRelation('fishingFrom', $from);

        $this->assertSame('Boat', $guiding->shoreOrBoatLabel());
    }
}
