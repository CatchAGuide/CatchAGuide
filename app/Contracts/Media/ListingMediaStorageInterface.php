<?php

namespace App\Contracts\Media;

interface ListingMediaStorageInterface
{
    public function listingKey(): string;

    public function baseDirectory(): string;

    /**
     * @param  \Illuminate\Http\UploadedFile|string  $source
     */
    public function upload(
        mixed $source,
        ?string $filename = null,
        int $quality = 75,
        ?int $id = null,
        ?string $directory = null
    ): string;

    public function delete(string $path): bool;

    public function exists(string $path): bool;

    public function read(string $path): string;

    public function url(string $path): string;
}
