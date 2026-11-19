<?php

namespace Tests\Unit\Guide\Area;

use App\Contracts\Storage\ObjectStorageInterface;
use App\Enums\GuideStatus;
use App\Models\User;
use App\Services\Guide\GuideProfilePhotoService;
use App\Services\Media\GuideProfilePhotoUploadService;
use App\Services\Media\MediaWriteStorageResolver;
use App\Services\Media\WebpMediaProcessor;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Http\UploadedFile;
use Tests\TestCase;

class GuideProfilePhotoUploadServiceTest extends TestCase
{
    use DatabaseTransactions;

    private ObjectStorageInterface $storage;

    protected function setUp(): void
    {
        parent::setUp();

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

    public function test_store_replace_and_remove(): void
    {
        $user = User::factory()->create(['is_guide' => 1, 'guide_status' => GuideStatus::VERIFIED]);
        $user->forceFill(['profil_image' => 'legacy_photo.jpg'])->save();
        $service = app(GuideProfilePhotoUploadService::class);

        $first = $service->store($user, UploadedFile::fake()->image('me.jpg', 600, 600));
        $this->assertStringStartsWith('guide-profiles/'.$user->id.'/', $first);
        $this->assertStringEndsWith('.webp', $first);
        $this->assertSame($first, $user->fresh()->profil_image);
        $this->assertSame('https://cdn.test/'.$first, app(GuideProfilePhotoService::class)->storedProfileUrl($user->fresh()));

        // Replacing deletes the previous upload; the legacy file was never ours to delete.
        $second = $service->store($user->fresh(), UploadedFile::fake()->image('me2.png', 500, 500));
        $this->assertArrayNotHasKey($first, $this->storage->files);
        $this->assertArrayHasKey($second, $this->storage->files);

        $service->remove($user->fresh());
        $this->assertNull($user->fresh()->profil_image);
        $this->assertSame([], $this->storage->files);
    }
}
