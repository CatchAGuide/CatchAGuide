<?php

namespace App\Traits;

use Illuminate\Support\Str;
use Illuminate\Support\Facades\Schema;

use Route;

use App\Models\ModelImage;

trait ModelImageTrait
{
    public function images()
    {
        return $this->morphMany(ModelImage::class, 'model');
    }

    public function getImageUrl($imageName = 'image', $imageSize = 'thumb')
    {
        if (!($this->id > 0)) {
            return null;
        }

        $image = $this->images->where('image_name', $imageName)->where('image_size', $imageSize)->first();

        if ($image && $image->image_url !== null && $image->image_url !== '') {
            return $image->image_url;
        }

        $imageUrl = app('asset')->getThumbnail($this, $imageName, $imageSize);

        if ($imageUrl !== null && $imageUrl !== '') {
            $this->saveImage(url($imageUrl), $imageName, $imageSize);
            return $imageUrl;
        }

        // save empty
        $this->saveImage('', $imageName, $imageSize);

        // return empty
        return null;
    }

    public function saveImage($imageUrl, $imageName = 'image', $imageSize = 'thumb')
    {
        return ModelImage::updateOrCreate([
            'model_id' => $this->id,
            'model_type' => get_class($this),
            'image_name' => $imageName,
            'image_size' => $imageSize
        ], [
            'image_url' => $imageUrl,
            'image_exists' => ($imageUrl !== null && $imageUrl !== '') ? 1 : 0
        ]);
    }

    public function hasImage($imageName = 'image')
    {
        if (!($this->id > 0)) {
            return false;
        }

        $image = $this->images->where('image_name', $imageName)->first();

        if ($image && $image->image_url !== null && $image->image_url !== '') {
            return $image->image_exists == 1;
        }

        return false;
    }
}