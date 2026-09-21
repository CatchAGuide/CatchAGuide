<?php

namespace Tests\Feature\Gallery;

use Tests\TestCase;

class TourProductGalleryTest extends TestCase
{
    public function test_tour_product_gallery_uses_vacation_mobile_carousel_only_on_mobile(): void
    {
        $source = (string) file_get_contents(resource_path('views/pages/guidings/newIndex.blade.php'));
        $styles = (string) file_get_contents(resource_path('sass/page/guiding.scss'));

        $this->assertStringContainsString('camp-gallery__mobile-carousel', $source);
        $this->assertStringContainsString('camp-gallery__mobile-carousel-scroll', $source);
        $this->assertStringContainsString('camp-gallery__mobile-carousel-item', $source);
        $this->assertStringContainsString('camp-gallery__counter', $source);
        $this->assertStringContainsString('$mobileCarouselImages = array_slice($overallImages, 1)', $source);
        $this->assertStringContainsString("__('guidings.gallery_image_alt'", $source);
        $this->assertStringNotContainsString('gallery-mobile', $source);

        $this->assertMatchesRegularExpression(
            '/\.guidings-gallery \{[\s\S]*?\.camp-gallery__mobile-carousel \{[\s\S]*?display:\s*none\s*!important;/',
            $styles
        );
        $this->assertMatchesRegularExpression(
            '/@media screen and \(max-width: 980px\) \{[\s\S]*?\.guidings-gallery \{[\s\S]*?\.right-images \{[\s\S]*?display:\s*none;/',
            $styles
        );
        $this->assertMatchesRegularExpression(
            '/@media screen and \(max-width: 980px\) \{[\s\S]*?\.camp-gallery__mobile-carousel \{[\s\S]*?display:\s*block\s*!important;/',
            $styles
        );
        $this->assertMatchesRegularExpression(
            '/@media screen and \(max-width: 980px\) \{[\s\S]*?\.camp-gallery__mobile-carousel \{[\s\S]*?padding-left:\s*0\s*!important;[\s\S]*?padding-right:\s*0\s*!important;/',
            $styles
        );
        $this->assertStringContainsString("padding-bottom: 62.5% !important", $styles);
    }

    public function test_gallery_image_alt_exists_in_both_locales(): void
    {
        $this->assertSame(':title — image :num', __('guidings.gallery_image_alt', ['title' => ':title', 'num' => ':num'], 'en'));
        $this->assertSame(':title — Bild :num', __('guidings.gallery_image_alt', ['title' => ':title', 'num' => ':num'], 'de'));
    }
}
