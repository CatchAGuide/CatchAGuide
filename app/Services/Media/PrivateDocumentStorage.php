<?php

namespace App\Services\Media;

use Illuminate\Contracts\Filesystem\Filesystem;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\StreamedResponse;

/**
 * Private documents (guide certificates). Files go to media_storage.private_disk with private
 * visibility and are only handed out through authorized controller routes.
 */
class PrivateDocumentStorage
{
    public function diskName(): string
    {
        return (string) config('media_storage.private_disk', 'local');
    }

    public function store(UploadedFile $file, string $directory): string
    {
        $extension = strtolower($file->getClientOriginalExtension() ?: ($file->guessExtension() ?: 'bin'));
        $name = Str::uuid()->toString().'.'.$extension;

        $path = $this->disk()->putFileAs(trim($directory, '/'), $file, $name, ['visibility' => 'private']);
        if ($path === false) {
            throw new \RuntimeException('Failed to store private document.');
        }

        return $path;
    }

    public function delete(string $disk, string $path): void
    {
        Storage::disk($disk)->delete($path);
    }

    public function download(string $disk, string $path, string $downloadName, ?string $mime = null): StreamedResponse
    {
        $storage = Storage::disk($disk);
        abort_unless($storage->exists($path), 404);

        $headers = ['X-Content-Type-Options' => 'nosniff', 'Cache-Control' => 'private, no-store'];
        if ($mime) {
            $headers['Content-Type'] = $mime;
        }

        return $storage->download($path, $downloadName, $headers);
    }

    private function disk(): Filesystem
    {
        return Storage::disk($this->diskName());
    }
}
