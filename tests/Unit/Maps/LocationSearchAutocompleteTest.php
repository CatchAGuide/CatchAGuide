<?php

namespace Tests\Unit\Maps;

use Illuminate\Support\Facades\Blade;
use Tests\TestCase;

class LocationSearchAutocompleteTest extends TestCase
{
    public function test_location_input_defaults_to_unrestricted_places_types(): void
    {
        $html = Blade::render('<x-maps.location-input label="Standort" />');

        $this->assertStringContainsString('data-places-location', $html);
        $this->assertStringNotContainsString('data-places-types', $html);
        $this->assertStringNotContainsString('geocode', $html);
        $this->assertStringNotContainsString('(regions)', $html);
        $this->assertStringNotContainsString('(cities)', $html);
    }

    public function test_location_input_can_still_opt_into_places_types(): void
    {
        $html = Blade::render('<x-maps.location-input :types="[\'geocode\']" />');

        $this->assertStringContainsString('data-places-types', $html);
        $this->assertStringContainsString('geocode', $html);
    }

    public function test_listing_location_inputs_do_not_restrict_places_types(): void
    {
        $files = [
            resource_path('views/pages/guidings/multi-step-form.blade.php'),
            resource_path('views/components/camp-form.blade.php'),
            resource_path('views/components/trip-form.blade.php'),
            resource_path('views/components/accommodation-form.blade.php'),
            resource_path('views/components/rental-boat-form.blade.php'),
            resource_path('views/components/special-offer-form.blade.php'),
        ];

        foreach ($files as $path) {
            $this->assertFileExists($path);
            $source = file_get_contents($path);
            $this->assertDoesNotMatchRegularExpression(
                '/<x-maps\.location-input[\s\S]*?:types=/',
                $source,
                basename($path).' must not restrict Places Autocomplete types (navbar search is unrestricted).'
            );
        }
    }

    public function test_navbar_search_initializes_autocomplete_without_types(): void
    {
        $source = file_get_contents(resource_path('views/layouts/includes/scripts.blade.php'));

        $this->assertStringContainsString('MapsManager.initAutocomplete(config.input, callback);', $source);
        $this->assertStringNotContainsString("types: ['(regions)']", $source);
        $this->assertStringNotContainsString("types: ['geocode']", $source);
        $this->assertStringNotContainsString("types: ['(cities)']", $source);
    }

    public function test_places_service_treats_empty_types_as_unrestricted(): void
    {
        $source = file_get_contents(resource_path('js/maps/PlacesAutocompleteService.js'));

        $this->assertStringContainsString('normalizePlaceTypes', $source);
        $this->assertStringContainsString('Empty / missing means unrestricted', $source);
    }

    public function test_admin_ad_hoc_autocomplete_does_not_restrict_places_types(): void
    {
        $files = [
            resource_path('views/admin/pages/listings/consolidated.blade.php'),
            resource_path('views/admin/pages/vacations/index.blade.php'),
            resource_path('views/admin/pages/vacations/bookings.blade.php'),
        ];

        foreach ($files as $path) {
            $this->assertFileExists($path);
            $source = file_get_contents($path);
            $this->assertStringNotContainsString(
                "types: ['(regions)']",
                $source,
                basename($path).' should not restrict Places types to regions.'
            );
            $this->assertStringNotContainsString(
                "types: ['(cities)']",
                $source,
                basename($path).' should not restrict Places types to cities.'
            );
        }
    }
}
