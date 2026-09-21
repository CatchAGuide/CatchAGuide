<?php

namespace Tests\Unit;

use Tests\TestCase;

class ListingPlaceLabelTest extends TestCase
{
    public function test_joins_city_region_and_country(): void
    {
        $this->assertSame(
            'Bograngen, Värmlands län, Schweden',
            listing_place_label(['Bograngen', 'Värmlands län', 'Schweden'])
        );
    }

    public function test_falls_back_to_location_when_city_is_empty(): void
    {
        $this->assertSame(
            'Po-Delta bei Adria, Italien',
            listing_place_label([
                '' ?: 'Po-Delta bei Adria, Italien',
                null,
                null,
            ])
        );

        $this->assertSame(
            'Baalensee Str. 8 Fürstenberg, Brandenburg',
            listing_place_label([
                '' ?: 'Baalensee Str. 8 Fürstenberg',
                'Brandenburg',
                '',
            ])
        );
    }

    public function test_skips_duplicates_case_insensitively(): void
    {
        $this->assertSame(
            'Sweden',
            listing_place_label(['Sweden', 'sweden', 'SWEDEN'])
        );
    }

    public function test_returns_empty_string_when_all_parts_are_blank(): void
    {
        $this->assertSame('', listing_place_label([null, '', '  ']));
    }
}
