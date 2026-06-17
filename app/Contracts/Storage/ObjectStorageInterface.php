<?php

namespace App\Contracts\Storage;

interface ObjectStorageInterface
{
    public function exists(string $path): bool;

    public function read(string $path): string;

    public function url(string $path): string;

    public function write(string $path, string $contents, array $options = []): bool;

    public function delete(string $path): bool;

    /**
     * @param  array<int, string>  $paths
     */
    public function deleteMany(array $paths): void;

    public function makeDirectory(string $path): bool;
}
