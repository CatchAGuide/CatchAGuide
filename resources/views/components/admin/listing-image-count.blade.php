@props(['thumbnailPath' => null, 'gallery' => []])

@php
    $galleryArray = is_array($gallery) ? $gallery : (function ($value) {
        try {
            return decode_if_json($value, true) ?: [];
        } catch (\Throwable $e) {
            return [];
        }
    })($gallery);

    $imageCount = 0;
    $countedPaths = [];
    if (!empty(trim((string) $thumbnailPath))) {
        $imageCount++;
        $countedPaths[$thumbnailPath] = true;
    }
    if (!empty($galleryArray) && is_array($galleryArray)) {
        foreach ($galleryArray as $img) {
            if (!empty($img) && empty($countedPaths[$img])) {
                $imageCount++;
                $countedPaths[$img] = true;
            }
        }
    }
@endphp

@if($imageCount > 0)
    <span class="badge bg-secondary admin-listing-image-count">{{ $imageCount }}</span>
@else
    <span class="admin-listing-image-empty">0</span>
@endif
