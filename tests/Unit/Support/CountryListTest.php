<?php

namespace Tests\Unit\Support;

use App\Support\CountryList;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

class CountryListTest extends TestCase
{
    public static function storedValues(): array
    {
        return [
            ['DE', 'DE'],
            ['de', 'DE'],
            ['Deutschland', 'DE'],
            ['Germany', 'DE'],
            ['Italy ', 'IT'],
            ['Frankrike', 'FR'],
            ['Schweiz', 'CH'],
            ['Österreich', 'AT'],
            ['Danmark', 'DK'],
            ['USA', 'US'],
            ['United States', 'US'],
            ['Atlantis', null],
            ['', null],
        ];
    }

    #[DataProvider('storedValues')]
    public function test_normalizes_old_free_text_countries(string $stored, ?string $code): void
    {
        $this->assertSame($code, CountryList::normalize($stored));
    }

    public function test_options_are_real_countries_in_the_requested_language(): void
    {
        $options = CountryList::options('de');

        $this->assertSame('Deutschland', $options['DE']);
        $this->assertArrayNotHasKey('EU', $options);
        $this->assertArrayNotHasKey('ZZ', $options);
        $this->assertContains('SE', CountryList::codes());
    }
}
