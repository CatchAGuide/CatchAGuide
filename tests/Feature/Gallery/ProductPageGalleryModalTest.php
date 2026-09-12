<?php

namespace Tests\Feature\Gallery;

use Illuminate\Support\Facades\Blade;
use Tests\TestCase;

class ProductPageGalleryModalTest extends TestCase
{
    public function test_tour_product_gallery_snippet_renders_shared_dock_modal(): void
    {
        $html = Blade::render(
            <<<'BLADE'
            <div data-vacation-gallery="tour-detail-1" data-gallery-images='@json($images)'>
                <div data-gallery-index="0"></div>
            </div>
            <x-gallery.modal
                id="tour-detail-1"
                :images="$images"
                title="Tour Title"
                type="tour"
                badge="Fishing tour"
                location="Lake Test"
                :rating="4.5"
                :review-count="3"
                :specs="['4 hours', 'Max 2']"
                price-prefix="from"
                price-display="120€"
                cta-url="#book-now"
                cta-label="Book"
            />
            BLADE,
            [
                'images' => [
                    'https://example.com/one.jpg',
                    'https://example.com/two.jpg',
                ],
            ]
        );

        $this->assertStringContainsString('data-vacation-gallery="tour-detail-1"', $html);
        $this->assertStringContainsString('offers-gallery-modal__dock', $html);
        $this->assertStringContainsString('Tour Title', $html);
        $this->assertStringContainsString('#book-now', $html);
        $this->assertStringNotContainsString('id="galleryModal"', $html);
        $this->assertStringNotContainsString('data-bs-target="#galleryModal"', $html);
    }
}
