<?php

namespace Tests\Unit\Frontend;

use Tests\TestCase;

class RailTileStripePlaceholderTest extends TestCase
{
    public function test_country_and_fish_rail_tiles_have_no_stripe_overlay(): void
    {
        $scss = file_get_contents(resource_path('sass/page/_vacations-hub-extras.scss'));

        $this->assertNotFalse($scss);
        $this->assertStringNotContainsString(
            'repeating-linear-gradient(115deg',
            $scss,
            'Country and target-fish rail tiles must not use the diagonal stripe overlay.'
        );
    }

    public function test_homepage_country_and_species_tiles_do_not_use_stripe_placeholder_class(): void
    {
        $views = resource_path('views/pages/home/partials');

        $this->assertStringNotContainsString(
            'cag-home-ph',
            (string) file_get_contents($views.'/country-grid.blade.php')
        );
        $this->assertStringNotContainsString(
            'cag-home-ph',
            (string) file_get_contents($views.'/target-species.blade.php')
        );
        $this->assertStringNotContainsString(
            'cag-home-ph',
            (string) file_get_contents($views.'/season-module.blade.php')
        );
    }
}
