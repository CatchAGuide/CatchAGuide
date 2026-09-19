<?php

namespace Tests\Unit\Frontend;

use PHPUnit\Framework\TestCase;

class MobileSearchSheetScriptTest extends TestCase
{
    public function test_script_opens_and_closes_the_sheet_by_id(): void
    {
        $path = dirname(__DIR__, 3)
            . DIRECTORY_SEPARATOR
            . 'resources'
            . DIRECTORY_SEPARATOR
            . 'views'
            . DIRECTORY_SEPARATOR
            . 'layouts'
            . DIRECTORY_SEPARATOR
            . 'partials'
            . DIRECTORY_SEPARATOR
            . 'mobile-search-sheet-script.blade.php';

        $this->assertFileExists($path);

        $source = (string) file_get_contents($path);

        $this->assertStringContainsString('@once', $source);
        $this->assertStringContainsString('data-mobile-search-chip', $source);
        $this->assertStringContainsString("input[name=\"place\"]", $source);
        $this->assertStringContainsString('data-mobile-search-chip-value', $source);
        $this->assertStringContainsString("select[name=\"country\"]", $source);
        $this->assertStringContainsString('placeLat', $source);
        $this->assertStringContainsString('data-mobile-search-sheet-close', $source);
        $this->assertStringContainsString('data-mobile-search-sheet-backdrop', $source);
        $this->assertStringContainsString('is-mobile-search-open', $source);
        $this->assertStringContainsString('__cagInitHeaderPlaces', $source);
        $this->assertStringContainsString("event.key === 'Escape'", $source);
        $this->assertStringContainsString('aria-hidden', $source);
        $this->assertStringContainsString('aria-expanded', $source);
    }
}
