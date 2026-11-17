<?php

namespace Tests\Feature\Profile;

use App\Contracts\Storage\ObjectStorageInterface;
use App\Enums\GuideStatus;
use App\Models\Employee;
use App\Models\User;
use App\Services\Media\MediaWriteStorageResolver;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\URL;
use Tests\TestCase;

/**
 * The old profile pages (/profile/guide-profile and /profile/settings) upload photos through
 * the media storage like /guide/profile does, never into public/images.
 */
class ProfilePhotoStorageTest extends TestCase
{
    use DatabaseTransactions;

    private ObjectStorageInterface $storage;

    protected function setUp(): void
    {
        parent::setUp();

        config(['app.url' => 'http://localhost', 'guide_area.enabled' => true]);
        URL::forceRootUrl('http://localhost');

        $this->storage = new class implements ObjectStorageInterface
        {
            public array $files = [];

            public function exists(string $path): bool { return isset($this->files[$path]); }
            public function read(string $path): string { return $this->files[$path]; }
            public function url(string $path): string { return 'https://cdn.test/'.$path; }
            public function write(string $path, string $contents, array $options = []): bool { $this->files[$path] = $contents; return true; }
            public function delete(string $path): bool { unset($this->files[$path]); return true; }
            public function deleteMany(array $paths): void { foreach ($paths as $p) { unset($this->files[$p]); } }
            public function makeDirectory(string $path): bool { return true; }
        };

        config(['media_storage.disk' => 'do_spaces', 'media_storage.local_disk' => 'public']);
        $this->app->instance(MediaWriteStorageResolver::class, new MediaWriteStorageResolver($this->storage, $this->storage));
    }

    private function publicImages(): array
    {
        return glob(public_path('images').DIRECTORY_SEPARATOR.'*') ?: [];
    }

    private function assertStoredOnMediaStorage(User $user, array $publicImagesBefore): void
    {
        $path = (string) $user->fresh()->profil_image;

        $this->assertStringStartsWith('guide-profiles/'.$user->id.'/', $path);
        $this->assertStringEndsWith('.webp', $path);
        $this->assertArrayHasKey($path, $this->storage->files);
        $this->assertSame($publicImagesBefore, $this->publicImages(), 'Nothing new may land in public/images.');
    }

    public function test_old_guide_profile_form_uploads_to_media_storage(): void
    {
        $guide = User::factory()->create(['is_guide' => 1, 'guide_status' => GuideStatus::VERIFIED, 'language' => 'en']);
        $before = $this->publicImages();

        $this->actingAs($guide)
            ->put(route('profile.guide-profile.update'), [
                'information' => ['languages' => 'German', 'about_me' => 'Hi', 'favorite_fish' => 'Pike', 'fishing_start_year' => 2000],
                'bar_allowed' => 1,
                'profil_image' => UploadedFile::fake()->image('me.jpg', 400, 400),
            ])
            ->assertRedirect(route('profile.guide-profile'));

        $this->assertStoredOnMediaStorage($guide, $before);
    }

    public function test_account_settings_form_uploads_to_media_storage(): void
    {
        $user = User::factory()->create(['language' => 'en']);
        $before = $this->publicImages();

        $this->actingAs($user)
            ->put(route('profile.account'), [
                'firstname' => 'Ana',
                'lastname' => 'Lopez',
                'phone' => '123456',
                'countryCode' => '+49',
                'language' => 'en',
                'image' => UploadedFile::fake()->image('avatar.png', 300, 300),
            ])
            ->assertSessionHasNoErrors();

        $this->assertStoredOnMediaStorage($user, $before);
    }

    public function test_admin_customer_edit_uploads_to_media_storage(): void
    {
        $customer = User::factory()->create(['language' => 'en']);
        $employee = Employee::query()->first() ?? Employee::factory()->create();
        $before = $this->publicImages();
        $uploadsBefore = glob(public_path('uploads/profile_images').DIRECTORY_SEPARATOR.'*') ?: [];

        $this->actingAs($employee, 'employees')
            ->put(route('admin.customers.update', $customer), [
                'profile_image' => UploadedFile::fake()->image('avatar.jpg', 300, 300),
            ])
            ->assertSessionHasNoErrors();

        $this->assertStoredOnMediaStorage($customer, $before);
        $this->assertSame($uploadsBefore, glob(public_path('uploads/profile_images').DIRECTORY_SEPARATOR.'*') ?: []);
    }

    public function test_account_page_shows_a_media_storage_photo(): void
    {
        $user = User::factory()->create(['language' => 'en']);
        $user->forceFill(['profil_image' => 'guide-profiles/'.$user->id.'/profile.webp'])->save();

        $this->actingAs($user)
            ->get(route('profile.settings'))
            ->assertOk()
            ->assertSee('https://cdn.test/guide-profiles/'.$user->id.'/profile.webp', false);
    }
}
