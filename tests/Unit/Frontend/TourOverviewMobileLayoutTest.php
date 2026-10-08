<?php

namespace Tests\Unit\Frontend;

use PHPUnit\Framework\TestCase;

class TourOverviewMobileLayoutTest extends TestCase
{
    private function projectPath(string $relative): string
    {
        return dirname(__DIR__, 3).DIRECTORY_SEPARATOR.str_replace('/', DIRECTORY_SEPARATOR, $relative);
    }

    public function test_tour_page_uses_full_width_overview_fact_cards(): void
    {
        $blade = (string) file_get_contents($this->projectPath('resources/views/pages/guidings/newIndex.blade.php'));

        $this->assertStringContainsString('tour-overview', $blade);
        $this->assertStringContainsString('tour-overview__facts', $blade);
        $this->assertStringContainsString('tour-overview__fact', $blade);
        $this->assertStringContainsString('tour-overview__header', $blade);
        $this->assertStringContainsString('tour-overview__pills', $blade);
        $this->assertStringContainsString('tour-overview__languages', $blade);
        $this->assertStringContainsString("fa-clock", $blade);
        $this->assertStringContainsString("fa-map-marker-alt", $blade);
        $this->assertStringContainsString("fa-language", $blade);

        $this->assertStringNotContainsString('description-item col-12 col-md-6', $blade);
        $this->assertStringNotContainsString('class="row description-item-row"', $blade);
        $this->assertStringNotContainsString("badge border border-secondary text-secondary me-1", $blade);
    }

    public function test_starting_time_falls_back_to_default_text_when_not_set(): void
    {
        $blade = (string) file_get_contents($this->projectPath('resources/views/pages/guidings/newIndex.blade.php'));

        $this->assertStringContainsString("@lang('guidings.Starting_Time_Default')", $blade);
        $this->assertStringNotContainsString('@if ($guiding->desc_starting_time || $guiding->desc_departure_time)', $blade);
        // Selected departure-time pills must not suppress the default when the text is empty.
        $this->assertStringNotContainsString('@elseif(!$departureTimes)', $blade);
        $this->assertMatchesRegularExpression(
            '/@if\(\$hasStartingTimeText\).*?@else\s*<div class="tour-overview__body">\s*@lang\(\'guidings\.Starting_Time_Default\'\)/s',
            $blade
        );

        foreach (['en', 'de'] as $locale) {
            $lang = require $this->projectPath("resources/lang/{$locale}/guidings.php");
            $this->assertNotEmpty($lang['Starting_Time_Default'] ?? null, "Missing {$locale} Starting_Time_Default");
        }
    }

    public function test_meeting_point_falls_back_to_default_text_when_not_set(): void
    {
        $blade = (string) file_get_contents($this->projectPath('resources/views/pages/guidings/newIndex.blade.php'));

        $this->assertStringNotContainsString('@if ($guiding->desc_meeting_point)', $blade);
        $this->assertMatchesRegularExpression(
            '/@if\(\$hasMeetingPointText\).*?@else\s*@lang\(\'guidings\.Meeting_Point_Default\'\)/s',
            $blade
        );

        foreach (['en', 'de'] as $locale) {
            $lang = require $this->projectPath("resources/lang/{$locale}/guidings.php");
            $this->assertNotEmpty($lang['Meeting_Point_Default'] ?? null, "Missing {$locale} Meeting_Point_Default");
        }
    }

    public function test_overview_see_more_uses_translated_labels(): void
    {
        $blade = (string) file_get_contents($this->projectPath('resources/views/pages/guidings/newIndex.blade.php'));

        $this->assertStringContainsString("__('guidings.See_More')", $blade);
        $this->assertStringContainsString("__('guidings.Show_Less')", $blade);
        $this->assertStringNotContainsString('toggle.textContent = "See More";', $blade);
        $this->assertStringNotContainsString('toggle.textContent === "See Less"', $blade);
    }

    public function test_booking_column_stays_beside_the_description_column(): void
    {
        $blade = (string) file_get_contents($this->projectPath('resources/views/pages/guidings/newIndex.blade.php'));
        $start = strpos($blade, '<div class="guidings-descriptions">');
        $book = strpos($blade, '<div id="book-now" class="guidings-book">');
        $this->assertNotFalse($start);
        $this->assertNotFalse($book);
        $this->assertGreaterThan($start, $book);

        $slice = substr($blade, $start, $book - $start);
        $depth = preg_match_all('/<div\b/', $slice) - preg_match_all('/<\/div>/', $slice);

        // The description column must be closed before the booking column opens,
        // otherwise the sidebar is nested inside the left column and shrinks on desktop.
        $this->assertSame(0, $depth);
    }

    public function test_overview_scss_stacks_facts_on_mobile_and_drops_side_by_side_flex(): void
    {
        $scss = (string) file_get_contents($this->projectPath('resources/sass/page/guiding.scss'));

        $this->assertStringContainsString('.tour-overview', $scss);
        $this->assertStringContainsString('&__facts', $scss);
        $this->assertStringContainsString('grid-template-columns: minmax(0, 1fr);', $scss);
        $this->assertStringContainsString('grid-template-columns: repeat(2, minmax(0, 1fr));', $scss);
        $this->assertStringContainsString('&__fact', $scss);
        $this->assertStringContainsString('background: #f7f8fb;', $scss);

        $this->assertNotFalse(preg_match(
            '/\.description-container \{(?P<body>.*?)\n  \.title-right-container/s',
            $scss,
            $match
        ));
        $descriptionBody = $match['body'];

        $this->assertStringNotContainsString('flex: 1;', $descriptionBody);
        $this->assertStringNotContainsString('padding: 0 16px;', $descriptionBody);
    }
}
