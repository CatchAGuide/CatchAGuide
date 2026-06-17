<?php

namespace App\Contracts\Media;

use App\Contracts\Storage\ObjectStorageInterface;

interface MediaProcessorInterface
{
    /**
     * Process an image source, persist it on the given storage, and return the relative path.
     *
     * @param  \Illuminate\Http\UploadedFile|string  $source  Uploaded file or URL
     */
    public function process(
        mixed $source,
        string $directory,
        ?string $filename,
        int $quality,
        ?int $id,
        ObjectStorageInterface $storage
    ): string;
}
