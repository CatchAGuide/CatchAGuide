<?php

namespace Tests\Unit\Services\Media;

use App\Services\Media\ListingGalleryImageProcessor;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class ListingGalleryImageProcessorDedupeGuardTest extends TestCase
{
    public function test_update_skips_reuploads_already_retained_by_basename(): void
    {
        Storage::fake('public');
        config(['media_storage.disk' => 'public', 'media_storage.local_disk' => 'public']);

        $processor = app(ListingGalleryImageProcessor::class);
        $existing = ['camps/28/keep.webp', 'camps/28/also.webp'];
        $upload = UploadedFile::fake()->image('keep.webp');

        $request = Request::create('/test', 'POST', [
            'is_update' => '1',
            'existing_images' => json_encode($existing),
            'image_list' => json_encode($existing),
            'thumbnail_path' => 'camps/28/keep.webp',
        ], files: [
            'title_image' => [$upload],
        ]);

        $result = $processor->process($request, 'camp', 'camp-slug', 28);

        $this->assertNotNull($result);
        $this->assertSame($existing, $result['gallery_images']);
        $this->assertSame([], $processor->takePendingDeletes());
    }

    public function test_update_allows_only_new_slots_from_synced_image_list(): void
    {
        Storage::fake('public');
        config(['media_storage.disk' => 'public', 'media_storage.local_disk' => 'public']);

        $processor = app(ListingGalleryImageProcessor::class);
        $existing = ['camps/28/a.webp', 'camps/28/b.webp'];
        $uploads = [
            UploadedFile::fake()->image('brand-new.jpg'),
            UploadedFile::fake()->image('surplus.jpg'),
        ];

        $request = Request::create('/test', 'POST', [
            'is_update' => '1',
            'existing_images' => json_encode($existing),
            // One extra slot for a real new image; surplus.jpg is not listed.
            'image_list' => json_encode([...$existing, 'brand-new.jpg']),
            'thumbnail_path' => 'camps/28/a.webp',
        ], files: [
            'title_image' => $uploads,
        ]);

        $result = $processor->process($request, 'camp', 'camp-slug', 28);

        $this->assertNotNull($result);
        $this->assertCount(3, $result['gallery_images']);
        $this->assertSame($existing[0], $result['gallery_images'][0]);
        $this->assertSame($existing[1], $result['gallery_images'][1]);
        $this->assertStringContainsString('camps/28/', $result['gallery_images'][2]);
        $this->assertStringEndsWith('.webp', $result['gallery_images'][2]);
    }
}
