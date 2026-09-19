<?php

namespace Tests\Unit\Frontend;

use PHPUnit\Framework\TestCase;

class OffersPersonsStepperScriptTest extends TestCase
{
    public function test_stepper_script_opens_guest_popup_above_the_count(): void
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
            . 'offers-persons-stepper-script.blade.php';

        $this->assertFileExists($path);

        $source = (string) file_get_contents($path);

        $this->assertStringContainsString('data-offers-persons-popup-toggle', $source);
        $this->assertStringContainsString('data-offers-persons-popup-panel', $source);
        $this->assertStringContainsString('setPersonPopupOpen', $source);
        $this->assertStringContainsString('closePersonPopups', $source);
        $this->assertStringContainsString("event.key === 'Escape'", $source);
        $this->assertStringContainsString('aria-expanded', $source);
        $this->assertStringContainsString('panel.hidden = !open', $source);
    }
}
