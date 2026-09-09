<?php

namespace Tests\Unit\Services\Media;

use App\Services\Media\ListingGalleryImageProcessor;
use App\Services\Media\MediaTrashService;
use Illuminate\Http\Request;
use Tests\TestCase;

class MediaTrashServiceTest extends TestCase
{
    public function test_trash_path_uses_entity_folder_and_date(): void
    {
        $service = app(MediaTrashService::class);
        $when = new \DateTimeImmutable('2026-08-18 15:00:00');

        $this->assertSame(
            '_trash/guidings/42/2026-08-18/a.webp',
            $service->trashRelativePath('guidings/42/a.webp', $when)
        );
    }

    public function test_legacy_flat_paths_use_zero_entity_id(): void
    {
        $service = app(MediaTrashService::class);
        $when = new \DateTimeImmutable('2026-08-18');

        $this->assertSame(
            '_trash/guidings-images/0/2026-08-18/a.webp',
            $service->trashRelativePath('guidings-images/a.webp', $when)
        );
    }

    public function test_expired_trash_paths_are_detected_from_date_segment(): void
    {
        $service = app(MediaTrashService::class);
        $cutoff = new \DateTimeImmutable('2026-08-10');

        $this->assertTrue($service->isExpiredTrashPath('_trash/guidings/42/2026-08-01/a.webp', $cutoff));
        $this->assertFalse($service->isExpiredTrashPath('_trash/guidings/42/2026-08-18/a.webp', $cutoff));
        $this->assertFalse($service->isExpiredTrashPath('_trash/guidings/42/broken/a.webp', $cutoff));
    }

    public function test_unsynced_listing_update_does_not_queue_deletes(): void
    {
        $processor = app(ListingGalleryImageProcessor::class);
        $request = Request::create('/test', 'POST', [
            'is_update' => '1',
            'existing_images' => json_encode(['guidings/1/a.webp', 'guidings/1/b.webp']),
            'image_list' => '',
        ]);

        $result = $processor->process($request, 'guiding', 'tour-slug', 1);

        $this->assertNotNull($result);
        $this->assertSame(['guidings/1/a.webp', 'guidings/1/b.webp'], $result['gallery_images']);
        $this->assertSame([], $processor->takePendingDeletes());
    }

    public function test_basename_image_list_keeps_entity_folder_paths(): void
    {
        $processor = app(ListingGalleryImageProcessor::class);
        $request = Request::create('/test', 'POST', [
            'is_update' => '1',
            'existing_images' => json_encode(['guidings/1/photo.webp']),
            'image_list' => json_encode(['guidings-images/photo.webp']),
        ]);

        $result = $processor->process($request, 'guiding', 'tour-slug', 1);

        $this->assertNotNull($result);
        $this->assertSame(['guidings/1/photo.webp'], $result['gallery_images']);
        $this->assertSame([], $processor->takePendingDeletes());
    }
}
