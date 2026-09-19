<?php

namespace Tests\Unit\Frontend;

use PHPUnit\Framework\TestCase;

class GuidingsBookingWidgetScriptTest extends TestCase
{
    public function test_script_updates_price_and_keeps_cta_arrow_on_date_change(): void
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
            . 'guidings-booking-widget-script.blade.php';

        $this->assertFileExists($path);

        $source = (string) file_get_contents($path);

        $this->assertStringContainsString('data-guidings-book-delta', $source);
        $this->assertStringContainsString('data-guidings-book-person', $source);
        $this->assertStringContainsString('data-guidings-book-price', $source);
        $this->assertStringContainsString('data-guidings-book-breakdown', $source);
        $this->assertStringContainsString('data-guidings-book-per-person', $source);
        $this->assertStringContainsString('breakdownEl.hidden', $source);
        $this->assertStringContainsString('data-guidings-book-cta-text', $source);
        $this->assertStringContainsString("addEventListener('dateSelected'", $source);
        $this->assertStringContainsString("addEventListener('dateDeselected'", $source);
        $this->assertStringContainsString('ctaText.textContent', $source);
        $this->assertStringNotContainsString('reserveButton.textContent', $source);
    }
}
