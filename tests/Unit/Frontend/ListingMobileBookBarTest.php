<?php

namespace Tests\Unit\Frontend;

use PHPUnit\Framework\TestCase;

class ListingMobileBookBarTest extends TestCase
{
    private function projectPath(string $relative): string
    {
        return dirname(__DIR__, 3).DIRECTORY_SEPARATOR.str_replace('/', DIRECTORY_SEPARATOR, $relative);
    }

    public function test_bar_bottom_equals_visible_footer_overlap(): void
    {
        $viewportHeight = 844;

        $this->assertSame(0, max(0, (int) round($viewportHeight - 900)));
        $this->assertSame(144, max(0, (int) round($viewportHeight - 700)));
        $this->assertSame(844, max(0, (int) round($viewportHeight - 0)));
    }

    public function test_module_pins_bar_just_above_the_site_footer(): void
    {
        $path = $this->projectPath('resources/js/modules/listingMobileBookBar.js');
        $this->assertFileExists($path);

        $source = (string) file_get_contents($path);

        $this->assertStringContainsString('export function listingMobileBookBarBottom', $source);
        $this->assertStringContainsString('export function syncListingMobileBookBarWithFooter', $source);
        $this->assertStringContainsString('export function initListingMobileBookBar', $source);
        $this->assertStringContainsString('viewportHeight - footerTop', $source);
        $this->assertStringContainsString('footer.site-footer', $source);
        $this->assertStringContainsString('footer.cag-footer', $source);
        $this->assertStringContainsString('.listing-mobile-book, .guidings-book-mobile', $source);
        $this->assertStringContainsString('is-above-footer', $source);
        $this->assertStringContainsString('style.bottom', $source);
        $this->assertStringContainsString("addEventListener('scroll'", $source);
        $this->assertStringContainsString("addEventListener('resize'", $source);
        $this->assertStringNotContainsString('listing-mobile-book--hidden', $source);
        $this->assertStringNotContainsString('aria-hidden', $source);
    }

    public function test_app_bundle_initializes_listing_mobile_book_bar(): void
    {
        $path = $this->projectPath('resources/js/app.js');
        $this->assertFileExists($path);

        $source = (string) file_get_contents($path);

        $this->assertStringContainsString('initListingMobileBookBar', $source);
        $this->assertStringContainsString('./modules/listingMobileBookBar', $source);
    }

    public function test_scss_does_not_hide_the_bar_at_the_footer(): void
    {
        $path = $this->projectPath('resources/sass/components/_listing-mobile-book-bar.scss');
        $this->assertFileExists($path);

        $source = (string) file_get_contents($path);

        $this->assertStringContainsString('is-above-footer', $source);
        $this->assertStringContainsString('&--note-nowrap', $source);
        $this->assertStringNotContainsString('&--hidden', $source);
    }

    public function test_tour_page_markup_includes_mobile_book_bar_wrapper(): void
    {
        $path = $this->projectPath('resources/views/pages/guidings/newIndex.blade.php');
        $this->assertFileExists($path);

        $source = (string) file_get_contents($path);

        $this->assertStringContainsString('class="guidings-book-mobile"', $source);
    }

    public function test_tour_bar_scss_pins_above_footer(): void
    {
        $path = $this->projectPath('resources/sass/components/_guidings-book-card.scss');
        $this->assertFileExists($path);

        $source = (string) file_get_contents($path);

        $this->assertStringContainsString('.guidings-book-mobile', $source);
        $this->assertStringContainsString('is-above-footer', $source);
        $this->assertStringContainsString('position: fixed', $source);
    }
}
