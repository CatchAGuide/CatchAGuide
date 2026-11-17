<?php

namespace Tests\Unit\Guide\Area;

use App\Rules\Iban;
use App\Services\Guide\Area\GuideLanguageCatalog;
use Tests\TestCase;

class GuideLanguageCatalogTest extends TestCase
{
    public function test_parses_english_german_and_unknown_language_names(): void
    {
        $catalog = new GuideLanguageCatalog;

        $this->assertSame(['de', 'en'], $catalog->parse('Deutsch, Englisch'));
        $this->assertSame(['de', 'en', 'nl'], $catalog->parse('German / english und Niederländisch'));
        $this->assertSame(['de', 'Plattdeutsch'], $catalog->parse('Deutsch; Plattdeutsch; deutsch'));
        $this->assertSame([], $catalog->parse(null));
    }

    public function test_formats_codes_as_english_names_public_pages_understand(): void
    {
        $catalog = new GuideLanguageCatalog;

        $this->assertSame('German, English, Plattdeutsch', $catalog->format(['de', 'en', 'Plattdeutsch', 'de']));
        // Round trip keeps the selection.
        $this->assertSame(['de', 'en', 'Plattdeutsch'], $catalog->parse($catalog->format(['de', 'en', 'Plattdeutsch'])));
    }

    public function test_iban_checksum(): void
    {
        $this->assertTrue(Iban::isValid('DE89 3704 0044 0532 0130 00'));
        $this->assertTrue(Iban::isValid('nl91abna0417164300'));
        $this->assertFalse(Iban::isValid('DE89 3704 0044 0532 0130 01'));
        $this->assertFalse(Iban::isValid('not an iban'));
        $this->assertSame('DE89 3704 0044 0532 0130 00', Iban::format('de89370400440532013000'));
    }
}
