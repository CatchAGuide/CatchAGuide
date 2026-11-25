<?php

namespace Tests\Unit\Rules;

use App\Rules\VatId;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

class VatIdTest extends TestCase
{
    public static function validIds(): array
    {
        return [
            'Germany with spaces' => ['DE 123 456 789', 'DE'],
            'Austria' => ['ATU12345678', 'AT'],
            'Netherlands' => ['NL123456789B01', 'NL'],
            'Greece uses EL' => ['EL123456789', 'GR'],
            'France with letters' => ['FRAB123456789', 'FR'],
            'Swiss UID with dots' => ['CHE-123.456.789 MWST', 'CH'],
            'Norway' => ['NO123456789MVA', 'NO'],
            'Danish ID for a German guide' => ['DK12345678', 'DE'],
            'Plain tax number outside the EU' => ['123456789', 'US'],
        ];
    }

    public static function invalidIds(): array
    {
        return [
            'German ID too short' => ['DE12345', 'DE'],
            'EU guide without the prefix' => ['123456789', 'DE'],
            'Austria without the U' => ['AT12345678', 'AT'],
            'Dutch ID missing B' => ['NL123456789001', 'NL'],
            'Garbage' => ['XX', 'US'],
        ];
    }

    #[DataProvider('validIds')]
    public function test_accepts_valid_ids(string $id, string $country): void
    {
        $this->assertTrue(VatId::isValid($id, $country));
    }

    #[DataProvider('invalidIds')]
    public function test_rejects_invalid_ids(string $id, string $country): void
    {
        $this->assertFalse(VatId::isValid($id, $country));
    }

    public function test_normalize_strips_spacing_and_punctuation(): void
    {
        $this->assertSame('DE123456789', VatId::normalize(' de 123.456-789 '));
    }
}
