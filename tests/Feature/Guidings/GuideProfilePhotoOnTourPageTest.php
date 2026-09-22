<?php

namespace Tests\Feature\Guidings;

use App\Enums\GuideStatus;
use App\Models\FishingType;
use App\Models\Guiding;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\URL;
use Tests\TestCase;

class GuideProfilePhotoOnTourPageTest extends TestCase
{
    use DatabaseTransactions;

    protected function setUp(): void
    {
        parent::setUp();

        config(['app.url' => 'http://localhost']);
        URL::forceRootUrl('http://localhost');

        $this->withoutMiddleware([
            \Illuminate\Routing\Middleware\ThrottleRequests::class,
            \App\Http\Middleware\DDoSProtectionMiddleware::class,
        ]);
    }

    public function test_tour_page_uses_listing_image_when_guide_profile_photo_is_empty(): void
    {
        $listingPath = 'guidings/9201/tour-page-fallback-'.uniqid().'.webp';
        $guiding = $this->createPublishedTour([
            'thumbnail_path' => $listingPath,
            'gallery_images' => json_encode([$listingPath]),
        ]);

        $response = $this->get($guiding->publicShowUrl());

        $response->assertOk();
        $html = $response->getContent();
        $this->assertStringContainsString($listingPath, $html);
        $this->assertStringNotContainsString('uploads/profile_images/', $html);
    }

    /**
     * @param  array<string, mixed>  $overrides
     */
    private function createPublishedTour(array $overrides = []): Guiding
    {
        $user = User::factory()->create([
            'firstname' => 'Johannes',
            'is_guide' => 1,
            'guide_status' => GuideStatus::VERIFIED,
            'profil_image' => null,
        ]);
        $user->information->update([
            'about_me' => 'Passionate predator angler',
            'favorite_fish' => 'Perch',
            'languages' => 'German, English',
            'fishing_start_year' => 2010,
        ]);

        $guiding = new Guiding();
        $guiding->forceFill(array_merge([
            'title' => 'Profile Photo Tour '.uniqid(),
            'slug' => 'profile-photo-tour-'.uniqid(),
            'location' => 'Düsseldorf',
            'status' => 1,
            'max_guests' => 4,
            'duration' => 8,
            'price_type' => 'per_person',
            'price' => 150,
            'prices' => json_encode([
                ['person' => 1, 'amount' => 150],
            ]),
            'fishing_type_id' => FishingType::query()->value('id'),
            'user_id' => $user->id,
        ], $overrides))->save();

        return $guiding->fresh(['user.information']);
    }
}
