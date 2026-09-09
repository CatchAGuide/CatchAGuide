<?php

namespace Tests\Unit\Services\Media;

use App\Services\Media\ListingGalleryRetention;
use Tests\TestCase;

class ListingGalleryRetentionTest extends TestCase
{
    private ListingGalleryRetention $retention;

    protected function setUp(): void
    {
        parent::setUp();
        $this->retention = new ListingGalleryRetention;
    }

    public function test_unsynced_empty_image_list_keeps_all_existing_images(): void
    {
        $existing = ['guidings/42/a.webp', 'guidings/42/b.webp'];

        $result = $this->retention->retain('', $existing);

        $this->assertFalse($result['image_list_synced']);
        $this->assertSame($existing, $result['kept']);
        $this->assertSame([], $result['to_delete']);
    }

    public function test_missing_image_list_keeps_all_existing_images(): void
    {
        $existing = ['guidings/42/a.webp'];

        $result = $this->retention->retain(null, $existing);

        $this->assertFalse($result['image_list_synced']);
        $this->assertSame($existing, $result['kept']);
        $this->assertSame([], $result['to_delete']);
    }

    public function test_invalid_json_keeps_all_existing_images(): void
    {
        $existing = ['guidings/42/a.webp'];

        $result = $this->retention->retain('{not-json', $existing);

        $this->assertFalse($result['image_list_synced']);
        $this->assertSame($existing, $result['kept']);
        $this->assertSame([], $result['to_delete']);
    }

    public function test_synced_empty_gallery_queues_existing_images_for_delete(): void
    {
        $existing = ['guidings/42/a.webp', 'guidings/42/b.webp'];

        $result = $this->retention->retain('[]', $existing);

        $this->assertTrue($result['image_list_synced']);
        $this->assertSame([], $result['kept']);
        $this->assertSame($existing, $result['to_delete']);
    }

    public function test_basename_mismatch_still_keeps_live_entity_path(): void
    {
        $existing = ['guidings/42/photo.webp'];

        $result = $this->retention->retain(json_encode(['guidings-images/photo.webp']), $existing);

        $this->assertTrue($result['image_list_synced']);
        $this->assertSame($existing, $result['kept']);
        $this->assertSame([], $result['to_delete']);
    }

    public function test_removed_image_is_queued_and_kept_images_remain(): void
    {
        $existing = ['guidings/42/keep.webp', 'guidings/42/drop.webp'];

        $result = $this->retention->retain(json_encode(['guidings/42/keep.webp']), $existing);

        $this->assertSame(['guidings/42/keep.webp'], $result['kept']);
        $this->assertSame(['guidings/42/drop.webp'], $result['to_delete']);
    }

    public function test_filter_skips_deletes_still_referenced_by_committed_gallery(): void
    {
        $queued = ['guidings/42/a.webp', 'guidings/42/b.webp', 'guidings/42/c.webp'];
        $committed = ['guidings/42/a.webp', 'guidings-images/b.webp'];

        $safe = $this->retention->filterDeletesAgainstCommitted($queued, $committed);

        $this->assertSame(['guidings/42/c.webp'], $safe);
    }
}
