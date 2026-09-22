<?php

namespace Tests\Unit\Frontend;

use PHPUnit\Framework\TestCase;

class TourPanelsTabsAccordionTest extends TestCase
{
    private function projectPath(string $relative): string
    {
        return dirname(__DIR__, 3).DIRECTORY_SEPARATOR.str_replace('/', DIRECTORY_SEPARATOR, $relative);
    }

    public function test_tour_page_uses_shared_panel_partials_for_tabs_and_accordion(): void
    {
        $blade = (string) file_get_contents($this->projectPath('resources/views/pages/guidings/newIndex.blade.php'));

        $this->assertStringContainsString('tour-panels__tabs', $blade);
        $this->assertStringContainsString('tour-panels__accordion', $blade);
        $this->assertStringContainsString("id=\"guidings-accordion\"", $blade);
        $this->assertStringContainsString("id=\"guiding-tab\"", $blade);
        $this->assertStringContainsString("id=\"guidings-tabs\"", $blade);

        $this->assertSame(2, substr_count($blade, "pages.guidings.partials.tour-panels.fishing"));
        $this->assertSame(2, substr_count($blade, "pages.guidings.partials.tour-panels.inclusions"));
        $this->assertSame(2, substr_count($blade, "pages.guidings.partials.tour-panels.boat"));
        $this->assertSame(2, substr_count($blade, "pages.guidings.partials.tour-panels.additional"));

        $this->assertStringContainsString('$hasNamedPricingExtras', $blade);
        $this->assertStringContainsString("filled(trim((string) (\$extra['name'] ?? '')))", $blade);
        $this->assertStringNotContainsString('class="row card tab-card', $blade);
        $this->assertStringNotContainsString('No information specified', $blade);
    }

    public function test_panel_partials_use_scannable_chip_and_checklist_markup(): void
    {
        $fishing = (string) file_get_contents($this->projectPath('resources/views/pages/guidings/partials/tour-panels/fishing.blade.php'));
        $inclusions = (string) file_get_contents($this->projectPath('resources/views/pages/guidings/partials/tour-panels/inclusions.blade.php'));
        $boat = (string) file_get_contents($this->projectPath('resources/views/pages/guidings/partials/tour-panels/boat.blade.php'));
        $additional = (string) file_get_contents($this->projectPath('resources/views/pages/guidings/partials/tour-panels/additional.blade.php'));

        $this->assertStringContainsString('tour-panel__chips', $fishing);
        $this->assertStringContainsString('tour-panel__chip', $fishing);
        $this->assertStringContainsString('tour-panel__checklist', $inclusions);
        $this->assertStringContainsString('tour-panel__callout', $inclusions);
        $this->assertStringContainsString("\$pricingExtras->isNotEmpty()", $inclusions);
        $this->assertStringContainsString("filled(trim((string) (\$extra['name'] ?? '')))", $inclusions);
        $this->assertStringContainsString('tour-panel__specs', $boat);
        $this->assertStringContainsString('tour-panel__kv-list', $additional);
        $this->assertStringContainsString("@lang('guidings.No_information_specified')", $additional);
    }

    public function test_scss_shows_tabs_on_desktop_and_accordion_on_mobile(): void
    {
        $scss = (string) file_get_contents($this->projectPath('resources/sass/page/guiding.scss'));

        $this->assertStringContainsString('.tour-panels', $scss);
        $this->assertStringContainsString('.tour-panel', $scss);
        $this->assertStringContainsString('&__chips', $scss);
        $this->assertStringContainsString('&__checklist', $scss);
        $this->assertStringContainsString('grid-template-columns: repeat(auto-fit, minmax(12.5rem, 1fr));', $scss);

        $this->assertStringContainsString('&__accordion', $scss);
        $this->assertStringContainsString('.tour-panels__tabs', $scss);

        $this->assertNotFalse(preg_match(
            '/#guidings-accordion,\s*\n\s*\.tour-panels__accordion \{\s*\n\s*display: none;/s',
            $scss
        ));

        $this->assertNotFalse(preg_match(
            '/#guidings-tabs,\s*\n\s*#guiding-tab,\s*\n\s*\.tour-panels__tabs \{\s*\n\s*display: none;/s',
            $scss
        ));
    }

    public function test_no_information_translation_exists_in_both_locales(): void
    {
        $en = include $this->projectPath('resources/lang/en/guidings.php');
        $de = include $this->projectPath('resources/lang/de/guidings.php');

        $this->assertIsArray($en);
        $this->assertIsArray($de);
        $this->assertArrayHasKey('No_information_specified', $en);
        $this->assertArrayHasKey('No_information_specified', $de);
        $this->assertNotSame('', $en['No_information_specified']);
        $this->assertNotSame('', $de['No_information_specified']);
    }
}
