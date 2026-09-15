<?php

namespace Tests\Feature\Vacation;

use App\Models\Camp;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\URL;
use Tests\TestCase;

class CampGalleryTest extends TestCase
{
    use DatabaseTransactions;

    protected function setUp(): void
    {
        parent::setUp();

        config(['app.url' => 'http://cag.local']);
        URL::forceRootUrl('http://cag.local');
    }

    public function test_multi_image_camp_page_renders_a_bounded_mobile_gallery(): void
    {
        $camp = $this->makeCamp([
            'thumbnail_path' => '/images/placeholder.jpg',
            'gallery_images' => [
                '/images/placeholder.jpg',
                '/images/placeholder_guide.jpg',
                '/images/placeholder_guide.jpg',
            ],
        ]);

        $response = $this->get(route('vacations.camps.show', $camp->slug));

        $response->assertOk();
        $response->assertSee('camp-gallery__main', false);
        $response->assertSee('camp-gallery__mobile-carousel', false);
        $response->assertSee('camp-gallery__mobile-carousel-scroll', false);
        $response->assertSee('data-vacation-gallery="camp-detail-'.$camp->id.'"', false);
        $response->assertSee('data-gallery-index="0"', false);
        $response->assertSee('data-gallery-index="1"', false);
    }

    public function test_single_image_camp_page_does_not_render_the_mobile_thumb_strip(): void
    {
        $camp = $this->makeCamp([
            'thumbnail_path' => '/images/placeholder.jpg',
            'gallery_images' => ['/images/placeholder.jpg'],
        ]);

        $response = $this->get(route('vacations.camps.show', $camp->slug));

        $response->assertOk();
        $response->assertSee('camp-gallery__main', false);
        $response->assertDontSee('camp-gallery__mobile-carousel', false);
    }

    private function makeCamp(array $overrides = []): Camp
    {
        $user = User::query()->first();
        if (! $user) {
            $this->markTestSkipped('No user available to own a test camp.');
        }

        return Camp::query()->create(array_merge([
            'title' => 'Test Camp Gallery',
            'slug' => 'test-camp-gallery-'.uniqid(),
            'description_camp' => 'Camp description',
            'description_area' => 'Area description',
            'description_fishing' => 'Fishing description',
            'location' => 'Test Location',
            'status' => 'active',
            'user_id' => $user->id,
        ], $overrides));
    }
}
