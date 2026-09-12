<?php

namespace Tests\Feature\Gallery;

use Illuminate\Support\Facades\Blade;
use Tests\TestCase;

class ListingGalleryModalComponentTest extends TestCase
{
    public function test_gallery_modal_renders_dock_with_listing_context(): void
    {
        $html = Blade::render(
            '<x-gallery.modal
                :id="$id"
                :images="$images"
                :title="$title"
                type="tour"
                :badge="$badge"
                :location="$location"
                :rating="$rating"
                :review-count="$reviewCount"
                :specs="$specs"
                :price-prefix="$pricePrefix"
                :price-display="$priceDisplay"
                :cta-url="$ctaUrl"
                :cta-label="$ctaLabel"
            />',
            [
                'id' => 'test-gallery-1',
                'images' => [
                    'https://example.com/one.jpg',
                    'https://example.com/two.jpg',
                ],
                'title' => 'Docked Tour Title',
                'badge' => 'Fishing tour',
                'location' => 'Lake Test',
                'rating' => 4.8,
                'reviewCount' => 12,
                'specs' => ['4 hours', 'Max 2'],
                'pricePrefix' => 'from',
                'priceDisplay' => '120€',
                'ctaUrl' => 'https://example.com/tour',
                'ctaLabel' => 'See details',
            ]
        );

        $this->assertStringContainsString('offers-gallery-modal', $html);
        $this->assertStringContainsString('offers-gallery-modal__dock', $html);
        $this->assertStringContainsString('Docked Tour Title', $html);
        $this->assertStringContainsString('Lake Test', $html);
        $this->assertStringContainsString('data-vacation-modal="test-gallery-1"', $html);
        $this->assertStringContainsString('data-offers-gallery-stage', $html);
        $this->assertStringContainsString('data-offers-gallery-modal-image', $html);
        $this->assertStringContainsString('See details', $html);
        $this->assertStringContainsString('120€', $html);
        $this->assertStringNotContainsString('vacation-gallery-modal__counter', $html);
        $this->assertStringContainsString('offers-gallery-modal__counter', $html);
    }

    public function test_gallery_modal_omits_cta_when_url_missing(): void
    {
        $html = Blade::render(
            '<x-gallery.modal
                id="no-cta"
                :images="[\'https://example.com/one.jpg\']"
                title="Title Only"
                type="camp"
            />'
        );

        $this->assertStringContainsString('offers-gallery-modal__dock', $html);
        $this->assertStringContainsString('Title Only', $html);
        $this->assertStringNotContainsString('offers-gallery-modal__cta', $html);
    }
}
