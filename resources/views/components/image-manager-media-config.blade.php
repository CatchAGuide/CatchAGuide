@php
    $mediaUsesObjectStorage = app(\App\Services\Media\MediaWriteStorageResolver::class)->usesObjectStorage();
    $mediaCdnBase = $mediaUsesObjectStorage
        ? rtrim((string) config('filesystems.disks.' . config('media_storage.disk', 'do_spaces') . '.url', ''), '/')
        : '';
    $mediaEnvPrefix = $mediaUsesObjectStorage
        ? app(\App\Services\Media\MediaEnvironmentResolver::class)->bucketPrefix()
        : '';
    $mediaLocalBase = rtrim(url('/'), '/');
@endphp
<script>
    window.mediaUsesObjectStorage = @json($mediaUsesObjectStorage);
    window.mediaCdnBase = @json($mediaCdnBase);
    window.mediaEnvPrefix = @json($mediaEnvPrefix);
    window.mediaLocalBase = @json($mediaLocalBase);
</script>
