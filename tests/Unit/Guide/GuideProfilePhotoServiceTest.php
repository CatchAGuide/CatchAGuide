<?php

namespace Tests\Unit\Guide;

use App\Enums\GuideStatus;
use App\Models\FishingType;
use App\Models\Guiding;
use App\Models\User;
use App\Services\Guide\GuideProfilePhotoService;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class GuideProfilePhotoServiceTest extends TestCase
{
    use DatabaseTransactions;

    private GuideProfilePhotoService $service;

    /** @var array<int, string> */
    private array $tempFiles = [];

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = app(GuideProfilePhotoService::class);
    }

    protected function tearDown(): void
    {
        foreach ($this->tempFiles as $path) {
            if (is_file($path)) {
                @unlink($path);
            }
        }

        parent::tearDown();
    }

    public function test_empty_profile_uses_listing_thumbnail(): void
    {
        $user = $this->makeGuide(['profil_image' => null]);
        $listingPath = 'guidings/9101/profile-fallback-'.uniqid().'.webp';
        $guiding = $this->makeTour($user, ['thumbnail_path' => $listingPath]);

        $url = $this->service->url($user, $guiding);

        $this->assertStringContainsString($listingPath, $url);
        $this->assertStringNotContainsString('placeholder_guide', $url);
    }

    public function test_empty_profile_uses_gallery_image_when_thumbnail_missing(): void
    {
        $user = $this->makeGuide(['profil_image' => '']);
        $galleryPath = 'guidings/9102/gallery-fallback-'.uniqid().'.webp';
        $guiding = $this->makeTour($user, [
            'thumbnail_path' => null,
            'gallery_images' => json_encode([$galleryPath]),
        ]);

        $url = $this->service->url($user, $guiding);

        $this->assertStringContainsString($galleryPath, $url);
    }

    public function test_missing_profile_file_falls_back_to_listing_image(): void
    {
        $user = $this->makeGuide(['profil_image' => 'missing-guide-'.uniqid().'.jpg']);
        $listingPath = 'guidings/9103/missing-file-fallback-'.uniqid().'.webp';
        $guiding = $this->makeTour($user, ['thumbnail_path' => $listingPath]);

        $url = $this->service->url($user, $guiding);

        $this->assertStringContainsString($listingPath, $url);
    }

    public function test_local_images_folder_profile_wins_over_listing(): void
    {
        $filename = 'guide-local-'.uniqid().'.jpg';
        $this->writePublicFile('images/'.$filename, 'avatar');

        $user = $this->makeGuide(['profil_image' => $filename]);
        $listingPath = 'guidings/9104/should-not-use-'.uniqid().'.webp';
        $guiding = $this->makeTour($user, ['thumbnail_path' => $listingPath]);

        $url = $this->service->url($user, $guiding);

        $this->assertStringContainsString('images/'.$filename, $url);
        $this->assertStringNotContainsString($listingPath, $url);
    }

    public function test_local_uploads_profile_images_folder_is_resolved(): void
    {
        $filename = 'guide-upload-'.uniqid().'.jpg';
        $this->writePublicFile('uploads/profile_images/'.$filename, 'avatar');

        $user = $this->makeGuide(['profil_image' => $filename]);

        $url = $this->service->url($user);

        $this->assertStringContainsString('uploads/profile_images/'.$filename, $url);
    }

    public function test_empty_profile_and_listing_uses_placeholder(): void
    {
        $user = $this->makeGuide(['profil_image' => null]);
        $guiding = $this->makeTour($user, [
            'thumbnail_path' => null,
            'gallery_images' => json_encode([]),
        ]);

        $url = $this->service->url($user, $guiding);

        $this->assertStringContainsString('placeholder_guide', $url);
    }

    public function test_helper_matches_service(): void
    {
        $user = $this->makeGuide(['profil_image' => null]);
        $listingPath = 'guidings/9105/helper-'.uniqid().'.webp';
        $guiding = $this->makeTour($user, ['thumbnail_path' => $listingPath]);

        $this->assertSame(
            $this->service->url($user, $guiding),
            guide_profile_photo_url($user, $guiding)
        );
        $this->assertSame(
            $this->service->url($user, $guiding),
            $user->profilePhotoUrl($guiding)
        );
    }

    /**
     * @param  array<string, mixed>  $attrs
     */
    private function makeGuide(array $attrs = []): User
    {
        return User::factory()->create(array_merge([
            'is_guide' => 1,
            'guide_status' => GuideStatus::VERIFIED,
            'firstname' => 'Johannes',
        ], $attrs));
    }

    /**
     * @param  array<string, mixed>  $overrides
     */
    private function makeTour(User $user, array $overrides = []): Guiding
    {
        $guiding = new Guiding();
        $guiding->forceFill(array_merge([
            'title' => 'Profile Photo Tour '.uniqid(),
            'slug' => 'profile-photo-tour-'.uniqid(),
            'location' => 'Somewhere',
            'status' => 1,
            'max_guests' => 4,
            'duration' => 4,
            'fishing_type_id' => FishingType::query()->value('id'),
            'user_id' => $user->id,
        ], $overrides))->save();

        return $guiding;
    }

    private function writePublicFile(string $relative, string $contents): void
    {
        $full = public_path($relative);
        $dir = dirname($full);
        if (! is_dir($dir)) {
            mkdir($dir, 0777, true);
        }
        file_put_contents($full, $contents);
        $this->tempFiles[] = $full;
    }
}
