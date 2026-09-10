<?php

namespace Tests\Unit\Services\Media;

use App\Services\Media\ListingGalleryDeduplicator;
use Tests\TestCase;

class ListingGalleryDeduplicatorTest extends TestCase
{
    public function test_exact_path_duplicates_are_removed(): void
    {
        $deduper = app(ListingGalleryDeduplicator::class);

        $result = $deduper->dedupe([
            'camps/28/a.webp',
            'camps/28/a.webp',
            'camps/28/b.webp',
        ], 'camps/28/a.webp');

        $this->assertSame(['camps/28/a.webp', 'camps/28/b.webp'], $result['gallery']);
        $this->assertSame(['camps/28/a.webp'], $result['removed']);
        $this->assertTrue($result['changed']);
        $this->assertSame('camps/28/a.webp', $result['thumbnail']);
    }

    public function test_mixed_gallery_subdir_and_entity_root_prefers_root_set(): void
    {
        $deduper = app(ListingGalleryDeduplicator::class);

        $legacy = [
            'camps/28/gallery/a.webp',
            'camps/28/gallery/b.webp',
            'camps/28/gallery/c.webp',
        ];
        $preferred = [
            'camps/28/a_28.webp',
            'camps/28/b_28.webp',
            'camps/28/c_28.webp',
        ];

        $result = $deduper->dedupe(array_merge($legacy, $preferred), 'camps/28/gallery/a.webp');

        $this->assertSame($preferred, $result['gallery']);
        $this->assertSame($legacy, $result['removed']);
        $this->assertSame('camps/28/a_28.webp', $result['thumbnail']);
    }

    public function test_multiple_resave_batches_keep_last_matching_count(): void
    {
        $deduper = app(ListingGalleryDeduplicator::class);

        $legacy = [
            'camps/12/gallery/1.webp',
            'camps/12/gallery/2.webp',
        ];
        $batchOne = [
            'camps/12/old1_12.webp',
            'camps/12/old2_12.webp',
        ];
        $batchTwo = [
            'camps/12/new1_12.webp',
            'camps/12/new2_12.webp',
        ];

        $result = $deduper->dedupe(array_merge($legacy, $batchOne, $batchTwo), $legacy[0]);

        $this->assertSame($batchTwo, $result['gallery']);
        $this->assertCount(4, $result['removed']);
    }

    public function test_single_legacy_path_does_not_collapse_preferred_set_to_one(): void
    {
        $deduper = app(ListingGalleryDeduplicator::class);

        $legacy = ['camps/23/gallery/only.webp'];
        $preferred = [
            'camps/23/a_23.webp',
            'camps/23/b_23.webp',
            'camps/23/c_23.webp',
        ];

        $result = $deduper->dedupe(array_merge($legacy, $preferred), $legacy[0]);

        $this->assertSame($preferred, $result['gallery']);
        $this->assertSame($legacy, $result['removed']);
    }
}
