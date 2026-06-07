<?php

namespace App\Services\Media;

use App\Contracts\Media\MediaProcessorInterface;
use App\Contracts\Storage\ObjectStorageInterface;
use Illuminate\Http\UploadedFile;
use Intervention\Image\Facades\Image;
use InvalidArgumentException;

class WebpMediaProcessor implements MediaProcessorInterface
{
    private const MAX_FILE_SIZE_BYTES = 2048 * 1024;

    public function process(
        mixed $source,
        string $directory,
        ?string $filename,
        int $quality,
        ?int $id,
        ObjectStorageInterface $storage
    ): string {
        $image = $this->loadImage($source, $filename);
        $filename = $filename ?: $this->defaultFilename($source);

        $encoded = $image->encode('webp', $quality)->encoded;
        if (strlen($encoded) > self::MAX_FILE_SIZE_BYTES) {
            $ratio = sqrt(self::MAX_FILE_SIZE_BYTES / strlen($encoded));
            $image->resize(
                (int) round($image->width() * $ratio),
                (int) round($image->height() * $ratio)
            );
            $encoded = $image->encode('webp', $quality)->encoded;
        }

        $hashedName = md5(pathinfo($filename, PATHINFO_FILENAME) . time());
        $webpImageName = $hashedName . ($id ? '_' . $id : '') . '.webp';
        $relativePath = trim($directory, '/') . '/' . $webpImageName;

        $storage->makeDirectory($directory);

        if ($storage->exists($relativePath)) {
            $storage->delete($relativePath);
        }

        $storage->write($relativePath, $encoded, [
            'visibility' => config('media_storage.object_visibility', 'public'),
        ]);

        return $relativePath;
    }

    private function loadImage(mixed $source, ?string &$filename)
    {
        if (filter_var($source, FILTER_VALIDATE_URL)) {
            if (! $filename) {
                $filename = basename(parse_url($source, PHP_URL_PATH));
            }

            return Image::make($source);
        }

        if ($source instanceof UploadedFile) {
            if (! $filename) {
                $filename = $source->getClientOriginalName();
            }

            return Image::make($source->getRealPath());
        }

        throw new InvalidArgumentException('Invalid input: must be a URL or an uploaded file');
    }

    private function defaultFilename(mixed $source): string
    {
        if ($source instanceof UploadedFile) {
            return $source->getClientOriginalName();
        }

        if (is_string($source) && filter_var($source, FILTER_VALIDATE_URL)) {
            return basename(parse_url($source, PHP_URL_PATH)) ?: 'image.jpg';
        }

        return 'image.jpg';
    }
}
