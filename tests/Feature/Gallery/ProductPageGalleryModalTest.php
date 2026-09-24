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
                cta-url="{{ route('checkout') }}"
                cta-method="POST"
                :cta-fields="['guiding_id' => 12, 'person' => 1, 'selected_date' => '']"
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
        $this->assertStringContainsString('offers-gallery-modal__cta-form', $html);
        $this->assertStringContainsString('name="guiding_id"', $html);
        $this->assertStringContainsString('name="person"', $html);
        $this->assertStringContainsString('type="submit"', $html);
        $this->assertStringNotContainsString('href="#book-now"', $html);
        $this->assertStringNotContainsString('id="galleryModal"', $html);
        $this->assertStringNotContainsString('data-bs-target="#galleryModal"', $html);
    }

    public function test_gallery_modal_scss_sizes_post_cta_form_on_mobile(): void
    {
        $scss = (string) file_get_contents(
            dirname(__DIR__, 3).DIRECTORY_SEPARATOR
            .'resources'.DIRECTORY_SEPARATOR.'sass'.DIRECTORY_SEPARATOR
            .'components'.DIRECTORY_SEPARATOR.'_listing-gallery-modal.scss'
        );

        $this->assertStringContainsString('&__cta-form', $scss);
        $this->assertStringContainsString('&__cta,', $scss);
        $this->assertStringContainsString('&__cta-form {', $scss);
        $this->assertStringContainsString('max-width: 58%', $scss);
        $this->assertStringContainsString('.offers-gallery-modal__cta {', $scss);
        $this->assertStringContainsString('width: 100%', $scss);
    }
}
