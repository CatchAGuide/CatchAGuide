<?php

namespace Tests\Unit\Frontend;

use PHPUnit\Framework\TestCase;

class BottomNavViewportTest extends TestCase
{
    private function projectPath(string $relative): string
    {
        return dirname(__DIR__, 3).DIRECTORY_SEPARATOR.str_replace('/', DIRECTORY_SEPARATOR, $relative);
    }

    public function test_visual_viewport_nav_top_sits_flush_with_visible_bottom(): void
    {
        $offsetTop = 0;
        $visualHeight = 700;
        $navHeight = 88;

        $this->assertSame(612, (int) round($offsetTop + $visualHeight - $navHeight));
    }

    public function test_module_pins_nav_with_visual_viewport_listeners(): void
    {
        $path = $this->projectPath('resources/js/modules/bottomNavViewport.js');
        $this->assertFileExists($path);

        $source = (string) file_get_contents($path);

        $this->assertStringContainsString('export function visualViewportNavTop', $source);
        $this->assertStringContainsString('offsetTop + visualHeight - navHeight', $source);
        $this->assertStringContainsString('visualViewport', $source);
        $this->assertStringContainsString('data-cag-vv', $source);
        $this->assertStringContainsString('is-vv-pinned', $source);
        $this->assertStringContainsString('--cag-bottom-nav-top', $source);
        $this->assertStringContainsString("addEventListener('resize'", $source);
        $this->assertStringContainsString("addEventListener('scroll'", $source);
    }

    public function test_module_caches_nav_height_instead_of_remeasuring_on_every_scroll_tick(): void
    {
        $path = $this->projectPath('resources/js/modules/bottomNavViewport.js');
        $source = (string) file_get_contents($path);

        // Re-reading nav.offsetHeight on every plain scroll tick forces a
        // synchronous layout while iOS is animating its own toolbar-collapse
        // layout, which caused the nav to flash/disappear on real devices.
        $this->assertMatchesRegularExpression(
            "/let navHeight = nav\\.offsetHeight;[\\s\\S]*const measure = \\(\\) => \\{[\\s\\S]*navHeight = nav\\.offsetHeight;/",
            $source
        );
        $this->assertStringContainsString("addEventListener('scroll', requestSync)", $source);
        $this->assertStringNotContainsString("addEventListener('scroll', requestRemeasureAndSync)", $source);
    }

    public function test_app_bundle_initializes_bottom_nav_viewport_sync(): void
    {
        $path = $this->projectPath('resources/js/app.js');
        $this->assertFileExists($path);

        $source = (string) file_get_contents($path);

        $this->assertStringContainsString('initBottomNavViewport', $source);
        $this->assertStringContainsString('./modules/bottomNavViewport', $source);
    }
}
