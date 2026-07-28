<?php

namespace App\Http\Controllers;

use App\Models\Guiding;
use App\Services\Media\ListingImageUploadService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class FileUploadController extends Controller
{
    private const ALLOWED_MIMES = ['image/jpeg', 'image/png', 'image/webp'];
    private const MAX_BYTES = 10 * 1024 * 1024; // 10 MB

    public function upload(?Guiding $guiding = null, Request $request, ListingImageUploadService $uploader)
    {
        $files = $request->allFiles();

        if (empty($files)) {
            abort(422, 'No files were uploaded.');
        }

        $requestKey = array_key_first($files);
        $file = is_array($request->file($requestKey))
            ? $request->file($requestKey)[0]
            : $request->file($requestKey);

        if (!$file || !$file->isValid()) {
            abort(422, 'No files were uploaded.');
        }

        if ($file->getSize() > self::MAX_BYTES) {
            abort(422, 'File too large. Maximum size is 10MB.');
        }

        $mime = (string) $file->getMimeType();
        if (!in_array($mime, self::ALLOWED_MIMES, true)) {
            abort(422, 'Invalid file type. Only JPG, PNG, and WEBP are allowed.');
        }

        // Temp uploads (new guiding draft) require auth via route middleware.
        if (!$guiding || !$guiding->exists) {
            return app('asset')->uploadTempFile($guiding ?? new Guiding(), $file);
        }

        $this->authorizeGuidingOwnership($guiding);

        $path = $uploader->uploadForListing(
            'guiding',
            $file,
            (int) $guiding->id,
            $guiding->slug . '-' . time()
        );

        $gallery = json_decode($guiding->gallery_images ?? '[]', true) ?? [];
        $gallery[] = $path;
        $guiding->gallery_images = json_encode(array_values($gallery));

        if (empty($guiding->thumbnail_path)) {
            $guiding->thumbnail_path = $path;
        }

        $guiding->save();

        return response()->json([
            'path' => $path,
            'url' => media_url($path),
        ]);
    }

    private function authorizeGuidingOwnership(Guiding $guiding): void
    {
        if (Auth::guard('employees')->check()) {
            return;
        }

        $user = Auth::guard('web')->user();
        if (!$user || (int) $user->id !== (int) $guiding->user_id) {
            abort(403, 'Unauthorized upload.');
        }
    }
}
