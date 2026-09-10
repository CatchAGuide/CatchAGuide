<?php

namespace Tests\Unit\Services\Media;

use App\Services\Media\ListingGalleryDeduplicator;
use App\Services\Media\ListingGalleryRetention;
use Tests\TestCase;

class ListingGalleryDeduplicatorTest extends TestCase
{
    private function deduper(?callable $exists = null): ListingGalleryDeduplicator
    {
        return new ListingGalleryDeduplicator(
            new ListingGalleryRetention,
            $exists ? \Closure::fromCallable($exists) : static fn () => true,
        );
    }

    public function test_exact_path_duplicates_are_removed(): void
    {
        $result = $this->deduper()->dedupe([
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

        $result = $this->deduper()->dedupe(array_merge($legacy, $preferred), 'camps/28/gallery/a.webp');

        $this->assertSame($preferred, $result['gallery']);
        $this->assertSame($legacy, $result['removed']);
        $this->assertSame('camps/28/a_28.webp', $result['thumbnail']);
    }

    public function test_multiple_resave_batches_keep_last_matching_count(): void
    {
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

        $result = $this->deduper()->dedupe(array_merge($legacy, $batchOne, $batchTwo), $legacy[0]);

        $this->assertSame($batchTwo, $result['gallery']);
        $this->assertCount(4, $result['removed']);
    }

    public function test_single_legacy_path_does_not_collapse_preferred_set_to_one(): void
    {
        $legacy = ['camps/23/gallery/only.webp'];
        $preferred = [
            'camps/23/a_23.webp',
            'camps/23/b_23.webp',
            'camps/23/c_23.webp',
        ];

        $result = $this->deduper()->dedupe(array_merge($legacy, $preferred), $legacy[0]);

        $this->assertSame($preferred, $result['gallery']);
        $this->assertSame($legacy, $result['removed']);
    }

    public function test_keeps_legacy_family_when_preferred_paths_are_missing(): void
    {
        $legacy = [
            'camps/28/gallery/a.webp',
            'camps/28/gallery/b.webp',
        ];
        $preferred = [
            'camps/28/a_28.webp',
            'camps/28/b_28.webp',
        ];

        $exists = static fn (string $path): bool => str_contains($path, '/gallery/');
        $result = $this->deduper($exists)->dedupe(array_merge($legacy, $preferred), $legacy[0]);

        $this->assertSame($legacy, $result['gallery']);
        $this->assertSame($preferred, $result['removed']);
        $this->assertSame('camps/28/gallery/a.webp', $result['thumbnail']);
    }

    public function test_does_not_collapse_when_neither_family_exists(): void
    {
        $paths = [
            'camps/28/gallery/a.webp',
            'camps/28/a_28.webp',
        ];

        $result = $this->deduper(static fn () => false)->dedupe($paths, $paths[0]);

        $this->assertSame($paths, $result['gallery']);
        $this->assertSame([], $result['removed']);
        $this->assertFalse($result['changed']);
    }
}
