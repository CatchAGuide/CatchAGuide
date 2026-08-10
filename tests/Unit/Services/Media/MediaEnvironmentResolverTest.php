<?php

namespace Tests\Unit\Services\Media;

use App\Services\Media\MediaEnvironmentResolver;
use Tests\TestCase;

class MediaEnvironmentResolverTest extends TestCase
{
    public function test_bucket_prefix_override_from_config(): void
    {
        config(['media_storage.bucket_prefix' => 'production']);

        $resolver = new MediaEnvironmentResolver;

        $this->assertSame('production', $resolver->bucketPrefix());
        $this->assertSame('production/guidings/1/a.webp', $resolver->applyBucketPrefix('guidings/1/a.webp'));
    }

    public function test_invalid_override_falls_back_to_environment(): void
    {
        config(['media_storage.bucket_prefix' => 'invalid']);
        app()->detectEnvironment(fn () => 'local');

        $resolver = new MediaEnvironmentResolver;

        $this->assertSame('staging', $resolver->bucketPrefix());
    }
}
