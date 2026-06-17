<?php

namespace App\Http\Controllers;

use App\Models\Guiding;
use App\Services\Media\ListingImageUploadService;
use Illuminate\Http\Request;

class FileUploadController extends Controller
{
    public function upload(Guiding $guiding, Request $request, ListingImageUploadService $uploader)
    {
        $files = $request->allFiles();

        if (empty($files)) {
            abort(422, 'No files were uploaded.');
        }

        $requestKey = array_key_first($files);
        $file = is_array($request->input($requestKey))
            ? $request->file($requestKey)[0]
            : $request->file($requestKey);

        if (! $guiding->exists) {
            return app('asset')->uploadTempFile($guiding, $file);
        }

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
}
