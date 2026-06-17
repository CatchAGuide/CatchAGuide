<?php

namespace App\Services\Media;

use App\Contracts\Storage\ObjectStorageInterface;

class MediaWriteStorageResolver
{
    public function __construct(
        private readonly ObjectStorageInterface $objectStorage,
        private readonly ObjectStorageInterface $localStorage,
    ) {}

    /**
     * Target storage for new uploads. All listing types use object storage when
     * MEDIA_STORAGE_DISK is not the local disk (set MEDIA_STORAGE_DISK=public for offline dev).
     */
    public function forUploads(): ObjectStorageInterface
    {
        if ($this->usesObjectStorage()) {
            return $this->objectStorage;
        }

        return $this->localStorage;
    }

    public function usesObjectStorage(): bool
    {
        return (string) config('media_storage.disk', 'do_spaces')
            !== (string) config('media_storage.local_disk', 'public');
    }
}
