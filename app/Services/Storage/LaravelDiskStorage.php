<?php

namespace App\Services\Storage;

use App\Contracts\Storage\ObjectStorageInterface;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class LaravelDiskStorage implements ObjectStorageInterface
{
    public function __construct(
        private readonly string $diskName,
        private readonly bool $mirrorToPublicPath = false,
    ) {}

    public function exists(string $path): bool
    {
        $path = $this->normalize($path);

        try {
            if (Storage::disk($this->diskName)->exists($path)) {
                return true;
            }
        } catch (\Throwable $e) {
            Log::debug('Storage existence check failed', [
                'disk' => $this->diskName,
                'path' => $path,
                'error' => $e->getMessage(),
            ]);

            if (! $this->mirrorToPublicPath) {
                return false;
            }
        }

        return $this->mirrorToPublicPath && is_file(public_path($path));
    }

    public function read(string $path): string
    {
        $path = $this->normalize($path);

        try {
            if (Storage::disk($this->diskName)->exists($path)) {
                return Storage::disk($this->diskName)->get($path);
            }
        } catch (\Throwable $e) {
            Log::debug('Storage read failed', [
                'disk' => $this->diskName,
                'path' => $path,
                'error' => $e->getMessage(),
            ]);

            if (! $this->mirrorToPublicPath) {
                return '';
            }
        }

        if ($this->mirrorToPublicPath) {
            $publicPath = public_path($path);
            if (is_file($publicPath)) {
                return (string) file_get_contents($publicPath);
            }
        }

        return '';
    }

    public function url(string $path): string
    {
        $path = $this->normalize($path);

        if ($this->mirrorToPublicPath) {
            return url('/' . ltrim($path, '/'));
        }

        return Storage::disk($this->diskName)->url($path);
    }

    public function write(string $path, string $contents, array $options = []): bool
    {
        $path = $this->normalize($path);
        $visibility = (string) ($options['visibility'] ?? 'public');

        $stored = Storage::disk($this->diskName)->put($path, $contents, array_merge($options, [
            'visibility' => $visibility,
        ]));

        if ($stored && ! $this->mirrorToPublicPath && $visibility === 'public') {
            $this->applyPublicVisibility($path);
        }

        if ($stored && $this->mirrorToPublicPath) {
            $publicPath = public_path($path);
            $directory = dirname($publicPath);
            if (! is_dir($directory)) {
                mkdir($directory, 0755, true);
            }
            file_put_contents($publicPath, $contents);
        }

        return $stored !== false;
    }

    /**
     * Set object ACL to public-read (required for browser access on DigitalOcean Spaces).
     */
    public function setPublicVisibility(string $path): bool
    {
        if ($this->mirrorToPublicPath) {
            return is_file(public_path($this->normalize($path)));
        }

        return $this->applyPublicVisibility($this->normalize($path));
    }

    private function applyPublicVisibility(string $path): bool
    {
        try {
            Storage::disk($this->diskName)->setVisibility($path, 'public');

            return true;
        } catch (\Throwable $e) {
            Log::warning('Failed to set public visibility on object storage', [
                'disk' => $this->diskName,
                'path' => $path,
                'error' => $e->getMessage(),
            ]);

            return false;
        }
    }

    public function delete(string $path): bool
    {
        $path = $this->normalize($path);
        $deleted = false;

        if (Storage::disk($this->diskName)->exists($path)) {
            Storage::disk($this->diskName)->delete($path);
            $deleted = true;
        }

        if ($this->mirrorToPublicPath) {
            $publicPath = public_path($path);
            if (is_file($publicPath)) {
                unlink($publicPath);
                $deleted = true;
            }
        }

        return $deleted;
    }

    public function deleteMany(array $paths): void
    {
        foreach ($paths as $path) {
            $this->delete((string) $path);
        }
    }

    public function makeDirectory(string $path): bool
    {
        $path = $this->normalize($path);

        if (Storage::disk($this->diskName)->exists($path)) {
            return true;
        }

        Storage::disk($this->diskName)->makeDirectory($path);

        if ($this->mirrorToPublicPath) {
            $publicDirectory = public_path($path);
            if (! is_dir($publicDirectory)) {
                mkdir($publicDirectory, 0755, true);
            }
        }

        return true;
    }

    private function normalize(string $path): string
    {
        $path = trim(str_replace('//', '/', $path));
        $path = ltrim($path, '/');

        if (str_starts_with($path, 'storage/')) {
            $path = substr($path, 8);
        }

        if (str_starts_with($path, 'public/')) {
            $path = substr($path, 7);
        }

        return $path;
    }
}
