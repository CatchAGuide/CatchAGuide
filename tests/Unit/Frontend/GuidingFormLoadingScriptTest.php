<?php

namespace Tests\Unit\Frontend;

use PHPUnit\Framework\TestCase;

class GuidingFormLoadingScriptTest extends TestCase
{
    private function projectPath(string $relative): string
    {
        return dirname(__DIR__, 3) . DIRECTORY_SEPARATOR . str_replace('/', DIRECTORY_SEPARATOR, $relative);
    }

    public function test_guiding_form_loading_helper_exposes_timeout_and_watchdog_apis(): void
    {
        $path = $this->projectPath('public/assets/js/guiding-form-loading.js');

        $this->assertFileExists($path);

        $source = file_get_contents($path);

        $this->assertStringContainsString('fetchWithTimeout', $source);
        $this->assertStringContainsString('armLoadingWatchdog', $source);
        $this->assertStringContainsString('clearLoadingWatchdog', $source);
        $this->assertStringContainsString('AbortController', $source);
        $this->assertStringContainsString('DEFAULT_FETCH_TIMEOUT_MS', $source);
        $this->assertStringContainsString('DEFAULT_WATCHDOG_MS', $source);
    }

    public function test_image_manager_caps_cropped_canvas_size_with_safe_defaults(): void
    {
        $path = $this->projectPath('public/assets/js/ImageManager.js');

        $this->assertFileExists($path);

        $source = file_get_contents($path);

        $this->assertStringContainsString('getCroppedCanvas({', $source);
        $this->assertStringContainsString('Number(this.compressionSettings.maxWidth) || 1920', $source);
        $this->assertStringContainsString('Number(this.compressionSettings.maxHeight) || 1080', $source);
        $this->assertStringContainsString('maxWidth: 1920', $source);
        $this->assertStringContainsString('maxHeight: 1080', $source);
        $this->assertStringContainsString('getCroppedImages(onlyUnsaved = false)', $source);
    }

    public function test_multi_step_form_script_loads_loading_helper(): void
    {
        $path = $this->projectPath('resources/views/pages/guidings/includes/scripts/multi-step-form-script.blade.php');

        $this->assertFileExists($path);

        $source = file_get_contents($path);

        $this->assertStringContainsString('guiding-form-loading.js', $source);
        $this->assertStringContainsString('formFetch(', $source);
        $this->assertStringContainsString('armLoadingWatchdog', $source);
    }

    public function test_shared_listing_forms_still_use_image_manager_api(): void
    {
        $sharedConsumers = [
            'resources/views/components/camp-form-scripts.blade.php',
            'resources/views/components/trip-form-scripts.blade.php',
            'resources/views/components/accommodation-form-scripts.blade.php',
            'resources/views/components/rental-boat-form-scripts.blade.php',
            'resources/views/components/special-offer-form-scripts.blade.php',
        ];

        foreach ($sharedConsumers as $relative) {
            $path = $this->projectPath($relative);
            $this->assertFileExists($path, "Missing consumer script: {$relative}");

            $source = file_get_contents($path);

            $this->assertStringContainsString(
                'assets/js/ImageManager.js',
                $source,
                "{$relative} should load ImageManager.js"
            );
            $this->assertStringContainsString(
                'getCroppedImages(',
                $source,
                "{$relative} should call getCroppedImages()"
            );
            $this->assertStringNotContainsString(
                'guiding-form-loading.js',
                $source,
                "{$relative} must not depend on guiding-only loading helper"
            );
        }

        $guiding = file_get_contents($this->projectPath(
            'resources/views/pages/guidings/includes/scripts/multi-step-form-script.blade.php'
        ));
        $this->assertStringContainsString('assets/js/ImageManager.js', $guiding);
        $this->assertStringContainsString('getCroppedImages(', $guiding);
        $this->assertStringContainsString('guiding-form-loading.js', $guiding);
    }
}