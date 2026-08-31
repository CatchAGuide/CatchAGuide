<?php

namespace Tests\Unit\Models;

use App\Models\CategoryPage;
use Tests\TestCase;

class CategoryPageThumbnailTest extends TestCase
{
    public function test_thumbnail_resolves_via_media_url_not_raw_local_path(): void
    {
        $page = new CategoryPage([
            'thumbnail_path' => 'category/targets/example-target.webp',
        ]);

        $url = $page->getThumbnailPath();

        // Legacy helper returned "/category/targets/..." which 404s in the browser.
        $this->assertNotSame('/category/targets/example-target.webp', $url);
        $this->assertStringContainsString('category/targets/example-target.webp', $url);

        $cdnBase = rtrim((string) config('filesystems.disks.'.config('media_storage.disk', 'do_spaces').'.url'), '/');
        if ($cdnBase !== '' && (bool) config('media_storage.url_skip_exists', true)) {
            $this->assertStringStartsWith($cdnBase, $url);
        }
    }

    public function test_empty_thumbnail_falls_back_to_placeholder(): void
    {
        $page = new CategoryPage(['thumbnail_path' => null]);

        $this->assertStringContainsString('300x300.png', $page->getThumbnailPath());
    }
}
