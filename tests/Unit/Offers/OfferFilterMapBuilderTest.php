<?php

namespace Tests\Unit\Offers;

use App\Services\Offers\OfferFilterMapBuilder;
use ReflectionMethod;
use Tests\TestCase;

class OfferFilterMapBuilderTest extends TestCase
{
    public function test_resolve_species_keeps_catalog_ids_and_custom_names(): void
    {
        $builder = new OfferFilterMapBuilder;
        $method = new ReflectionMethod(OfferFilterMapBuilder::class, 'resolveSpecies');
        $method->setAccessible(true);

        $knownIds = [5, 8];
        $nameToId = [
            'hecht' => 5,
            'pike' => 5,
            'zander' => 8,
        ];

        $resolved = $method->invoke(
            $builder,
            json_encode([5, 'Pike', 'Thunfisch', ['id' => 99, 'value' => 'Amberjack'], ['value' => 'Zander']]),
            $knownIds,
            $nameToId
        );

        sort($resolved['ids']);

        $this->assertSame([5, 8], $resolved['ids']);
        $this->assertSame([
            'thunfisch' => 'Thunfisch',
            'amberjack' => 'Amberjack',
        ], $resolved['customs']);
    }

    public function test_resolve_species_ignores_unknown_numeric_ids_without_name(): void
    {
        $builder = new OfferFilterMapBuilder;
        $method = new ReflectionMethod(OfferFilterMapBuilder::class, 'resolveSpecies');
        $method->setAccessible(true);

        $resolved = $method->invoke($builder, [99, 5], [5], []);

        $this->assertSame([5], $resolved['ids']);
        $this->assertSame([], $resolved['customs']);
    }
}
