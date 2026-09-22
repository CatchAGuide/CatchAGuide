<?php

namespace Tests\Unit\Frontend;

use PHPUnit\Framework\TestCase;

class GuidingCalendarDesktopOverflowTest extends TestCase
{
    private function projectPath(string $relative): string
    {
        return dirname(__DIR__, 3).DIRECTORY_SEPARATOR.str_replace('/', DIRECTORY_SEPARATOR, $relative);
    }

    public function test_desktop_two_month_calendar_fits_inside_the_card(): void
    {
        $scss = (string) file_get_contents($this->projectPath('resources/sass/page/guiding.scss'));

        $this->assertNotFalse(preg_match(
            '/\.litepicker \.container__months\.columns-2 \{(?P<body>.*?)\n      \.month-item \{/s',
            $scss,
            $columnsMatch
        ));
        $columnsBody = $columnsMatch['body'];

        $this->assertStringContainsString('box-sizing: border-box;', $columnsBody);
        $this->assertStringContainsString('padding: 8px 12px 12px;', $columnsBody);
        $this->assertStringContainsString('width: 100%;', $columnsBody);
        $this->assertStringContainsString('max-width: 100%;', $columnsBody);
        $this->assertStringNotContainsString('min-width: 280px;', $columnsBody);
        $this->assertStringNotContainsString('flex-shrink: 0;', $columnsBody);

        $this->assertNotFalse(preg_match(
            '/\.litepicker \.container__months\.columns-2 \{.*?\n      \.month-item \{(?P<body>.*?)\n        \.container__days \{/s',
            $scss,
            $monthMatch
        ));
        $monthBody = $monthMatch['body'];

        $this->assertStringContainsString('min-width: 0;', $monthBody);
        $this->assertStringContainsString('box-sizing: border-box;', $monthBody);
        $this->assertStringContainsString('flex: 1 1 calc(50% - 5px);', $monthBody);
        $this->assertStringNotContainsString('min-width: 280px;', $monthBody);
        $this->assertStringNotContainsString('flex-shrink: 0;', $monthBody);
    }

    public function test_mobile_single_month_calendar_rules_are_unchanged(): void
    {
        $scss = (string) file_get_contents($this->projectPath('resources/sass/page/guiding.scss'));
        $blade = (string) file_get_contents($this->projectPath('resources/views/pages/guidings/newIndex.blade.php'));

        $this->assertStringContainsString('.litepicker .container__months:not(.columns-2) {', $scss);
        $this->assertStringContainsString('max-width: 600px;', $scss);

        $this->assertStringContainsString('@media (max-width: 767px)', $blade);
        $this->assertStringContainsString(
            '#lite-datepicker .litepicker .container__months:not(.columns-2) .month-item .container__days',
            $blade
        );
        $this->assertStringContainsString('min-height: 40px !important;', $blade);
        $this->assertStringContainsString('return window.innerWidth < 768 ? 1 : 2;', $blade);
    }

    public function test_desktop_page_overrides_keep_days_inside_the_card(): void
    {
        $blade = (string) file_get_contents($this->projectPath('resources/views/pages/guidings/newIndex.blade.php'));

        $this->assertNotFalse(preg_match(
            '/\/\* Desktop: two column view \(768px and up\).*?@media \(min-width: 768px\) \{(?P<body>.*?)\n        \}/s',
            $blade,
            $match
        ));
        $desktopBody = $match['body'];

        $this->assertStringContainsString('box-sizing: border-box !important;', $desktopBody);
        $this->assertStringContainsString('min-width: 0 !important;', $desktopBody);
        $this->assertStringContainsString('margin: 0 !important;', $desktopBody);
        $this->assertStringContainsString('padding: 8px 12px 12px;', $desktopBody);
        $this->assertStringNotContainsString('min-width: 280px', $desktopBody);
    }
}
