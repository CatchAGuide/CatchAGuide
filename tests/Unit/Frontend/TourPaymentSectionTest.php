<?php

namespace Tests\Unit\Frontend;

use PHPUnit\Framework\TestCase;

class TourPaymentSectionTest extends TestCase
{
    private function projectPath(string $relative): string
    {
        return dirname(__DIR__, 3).DIRECTORY_SEPARATOR.str_replace('/', DIRECTORY_SEPARATOR, $relative);
    }

    public function test_tour_page_payment_section_uses_highlight_and_method_cards(): void
    {
        $blade = (string) file_get_contents($this->projectPath('resources/views/pages/guidings/newIndex.blade.php'));

        $this->assertStringContainsString('tour-payment', $blade);
        $this->assertStringContainsString('tour-payment__highlight', $blade);
        $this->assertStringContainsString('tour-payment__methods', $blade);
        $this->assertStringContainsString('tour-payment__method', $blade);
        $this->assertStringContainsString("@lang('booking.no_payment_now')", $blade);
        $this->assertStringContainsString('$paymentMethods->isNotEmpty()', $blade);
        $this->assertStringNotContainsString('payment-icon me-2', $blade);
    }

    public function test_tour_payment_scss_styles_method_grid(): void
    {
        $scss = (string) file_get_contents($this->projectPath('resources/sass/page/guiding.scss'));

        $this->assertStringContainsString('.tour-payment', $scss);
        $this->assertStringContainsString('&__highlight', $scss);
        $this->assertStringContainsString('&__methods', $scss);
        $this->assertStringContainsString('grid-template-columns: repeat(auto-fit, minmax(9.5rem, 1fr));', $scss);
    }
}
