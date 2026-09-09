<?php

namespace Tests\Unit\Vacation;

use App\Domain\Vacation\CountrySlug;
use Tests\TestCase;

class CountrySlugStorageVariantsTest extends TestCase
{
    public function test_spain_variants_include_german_label(): void
    {
        $variants = array_map(
            fn (string $value) => mb_strtolower($value, 'UTF-8'),
            CountrySlug::storageVariants('spain')
        );

        $this->assertContains('spain', $variants);
        $this->assertContains('spanien', $variants);
    }

    public function test_spanien_variants_include_english_label(): void
    {
        $variants = array_map(
            fn (string $value) => mb_strtolower($value, 'UTF-8'),
            CountrySlug::storageVariants('spanien', 'ES')
        );

        $this->assertContains('spanien', $variants);
        $this->assertContains('spain', $variants);
    }
}
