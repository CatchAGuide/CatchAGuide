<?php

namespace Tests\Unit\Services\Admin;

use App\Services\Admin\ListingAttributeRegistry;
use Tests\TestCase;

class ListingAttributeRegistryTest extends TestCase
{
    public function test_groups_are_complete_and_ordered(): void
    {
        $this->assertSame(
            [
                ListingAttributeRegistry::GROUP_GUIDING,
                ListingAttributeRegistry::GROUP_ACCOMMODATION,
                ListingAttributeRegistry::GROUP_CAMP,
                ListingAttributeRegistry::GROUP_RENTAL_BOAT,
            ],
            ListingAttributeRegistry::groups()
        );
    }

    public function test_expected_slugs_are_registered(): void
    {
        $expected = [
            'levels',
            'fishing-types',
            'fishing-equipment',
            'fishing-from',
            'inclussions',
            'methods',
            'waters',
            'targets',
            'boat-extras',
            'guiding-boat-types',
            'guiding-boat-descriptions',
            'guiding-additional-informations',
            'guiding-recommendations',
            'guiding-requirements',
            'extras-prices',
            'accommodation-types',
            'facilities',
            'kitchen-equipment',
            'bathroom-amenities',
            'room-configurations',
            'accommodation-details',
            'accommodation-extras',
            'accommodation-inclusives',
            'accommodation-policies',
            'accommodation-rental-conditions',
            'camp-facilities',
            'rental-boat-requirements',
        ];

        $this->assertSame($expected, ListingAttributeRegistry::slugs());
    }

    public function test_every_type_belongs_to_a_known_group_and_has_label_key(): void
    {
        foreach (ListingAttributeRegistry::all() as $slug => $config) {
            $this->assertContains($config['group'], ListingAttributeRegistry::groups(), $slug);
            $this->assertNotEmpty($config['label_key'], $slug);
            $this->assertNotEmpty($config['fields'], $slug);
            $this->assertTrue(class_exists($config['model']), $slug);
        }
    }

    public function test_legacy_slugs_resolve(): void
    {
        $this->assertSame('targets', ListingAttributeRegistry::resolveLegacySlug('targets'));
        $this->assertSame('fishing-types', ListingAttributeRegistry::resolveLegacySlug('fishingtype'));
        $this->assertSame('fishing-equipment', ListingAttributeRegistry::resolveLegacySlug('equipment'));
        $this->assertSame('fishing-from', ListingAttributeRegistry::resolveLegacySlug('fishingfrom'));
        $this->assertNull(ListingAttributeRegistry::resolveLegacySlug('not-a-real-type'));
    }
}
