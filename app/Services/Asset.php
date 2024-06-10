<?php
namespace App\Services;

use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\File;
use Intervention\Image\ImageManager;
use Intervention\Image\ImageManagerStatic as Image;
use Illuminate\Filesystem\Filesystem;
use Illuminate\Support\Facades\Input;
use Auth;

use Illuminate\Database\Eloquent\Model;


use App\Traits\ModelImageTrait;
use App\Product;


class Asset{

    protected $diskStorage = 'local';
    protected $diskThumbnails = 'assets';
    protected $imageFormat = 'webp';
    protected $rootDir;

    public function  __construct(){
        $this->diskStorage = env('SITE_STORAGE');
    }


    public function uploadImageFromUrl(Model $model, $imageName, $url){

        if(!in_array(ModelImageTrait::class, class_uses($model))){
            return;
        }

        $storagePath = $this->rootDir.'assets/'.$model->getTable().'/'.$model->id.'/images';

        if(!Storage::disk($this->diskStorage)->exists($storagePath)) {
            Storage::disk($this->diskStorage)->makeDirectory($storagePath, 0775, true); //creates directory
        }

        $tempFilePath = 'temp-'.date('Y-n-j-H-n-s').'.temp';
        $imageContent = @file_get_contents($url);

        if(!strlen($imageContent)){
            return;
        }

        Storage::disk('local')->put($tempFilePath, $imageContent);
        $mimeType = Storage::disk('local')->mimeType($tempFilePath);


        switch($mimeType){
            case 'image/webp':
                $imageContent = imagecreatefromwebp(Storage::disk('local')->path($tempFilePath));
                break;
            case 'image/png':
                $imageContent = imagecreatefrompng(Storage::disk('local')->path($tempFilePath));
                imagepalettetotruecolor($imageContent);
                break;
            case 'image/gif':
                $imageContent = imagecreatefromgif(Storage::disk('local')->path($tempFilePath));
                imagepalettetotruecolor($imageContent);
                break;
            case 'image/jpeg':
                $imageContent =  File::get(Storage::disk('local')->path($tempFilePath));
                break;
            default:
                return;
        }

        Storage::disk('local')->delete($tempFilePath);

        try{
            $manager = new ImageManager();
            $image = $manager->make($imageContent);
    
            $imageFormat = $image->mime();

            $image->encode($this->imageFormat, 90)->resize(1920, null, function ($constraint) {
                $constraint->aspectRatio();
                $constraint->upsize();
            });
            
        }catch (NotReadableException $e){
            return;
        }catch (Exception $e) {
            return;
        }
        // create main image
        $newImageFilename = $storagePath.'/'.$imageName.'.'.$this->imageFormat;
        $path = Storage::disk($this->diskStorage)->put($newImageFilename, $image);

        // delete image record
        $model->images()->delete();

        // remove thumbnails
        $this->deleteThumbnails($model, $imageName);

        // generate thumbnails
        $this->generateThumbnails($model);
    }

    public function saveImagesFromTempPath(Model $model, $imageNames){

        if(!in_array(ModelImageTrait::class, class_uses($model))){
            return;
        }

        $className = get_class($model);

        $tempPath = $this->rootDir.$this->getTempUploadDir(new $className);

        $tempFiles = Storage::disk($this->diskStorage)->allFiles($tempPath);

        foreach($imageNames as $index => $imageName){


            if(!isset($tempFiles[$index])){
                continue;
            }

            $tempFilePath = $tempFiles[$index];

            $mimeType = Storage::disk('local')->mimeType($tempFilePath);

            switch($mimeType){
                case 'image/webp':
                    $imageContent = imagecreatefromwebp(Storage::disk('local')->path($tempFilePath));
                    break;
                case 'image/png':
                    $imageContent = imagecreatefrompng(Storage::disk('local')->path($tempFilePath));
                    imagepalettetotruecolor($imageContent);
                    break;
                case 'image/gif':
                    $imageContent = imagecreatefromgif(Storage::disk('local')->path($tempFilePath));
                    imagepalettetotruecolor($imageContent);
                    break;
                case 'image/jpeg':
                    $imageContent =  File::get(Storage::disk('local')->path($tempFilePath));
                    break;
                default:
                    return;
            }

            $storagePath = $this->rootDir.'assets/'.$model->getTable().'/'.$model->id.'/images';

            if(!Storage::disk($this->diskStorage)->exists($storagePath)) {
                Storage::disk($this->diskStorage)->makeDirectory($storagePath, 0775, true); //creates directory
            }

            try{
                $manager = new ImageManager();
                $image = $manager->make($imageContent)->encode($this->imageFormat, 90)->resize(1920, NULL,function ($constraint) {
                    $constraint->aspectRatio();
                    $constraint->upsize();
                });
            }catch (NotReadableException $e){
                return;
            }catch (Exception $e) {
                return;
            }
            // create main image
            $newImageFilename = $storagePath.'/'.$imageName.'.'.$this->imageFormat;
            $path = Storage::disk($this->diskStorage)->put($newImageFilename, $image);

            // delete image record
            $model->images()->delete();

            // remove thumbnails
            $this->deleteThumbnails($model, $imageName);

            // generate thumbnails
            $this->generateThumbnails($model);

            Storage::disk('local')->delete($tempFilePath);
        }
    }

   

    public function uploadImage(Model $model, $imageName, $inputImage)
    {
    if (!in_array(ModelImageTrait::class, class_uses($model))) {
        return;
    }

    $storagePath = $this->rootDir . 'assets/' . $model->getTable() . '/' . $model->id . '/images';

    if (!Storage::disk($this->diskStorage)->exists($storagePath)) {
        Storage::disk($this->diskStorage)->makeDirectory($storagePath, 0775, true); // creates directory
    }

    $imageContent = File::get($inputImage->getRealPath());
    $tempPath = $inputImage->getRealPath();
    try {
        $manager = new ImageManager();
        $image = $manager->make($imageContent);

        $imageFormat = $image->mime();
        
        $image->encode($this->imageFormat, 90)->resize(1920, null, function ($constraint) {
            $constraint->aspectRatio();
            $constraint->upsize();
        });
    } catch (NotReadableException $e) {
        return;
    } catch (Exception $e) {
        return;
    }

    // Create main image
    $newImageFilename = $storagePath . '/' . $imageName . '.' . $this->imageFormat;

    $path = Storage::disk($this->diskStorage)->put($newImageFilename, $image);

    File::delete($tempPath);

    // Delete image record
    $model->images()->delete();

    // Remove thumbnails
    $this->deleteThumbnails($model, $imageName);

    // Generate thumbnails (if required)
    $this->generateThumbnails($model);
    }


    public function getImage(Model $model, $imageName = 'image'){
        $sourceImageFs = $this->getImagePath($model, $imageName);

        if(Storage::disk($this->diskStorage)->exists($sourceImageFs)){
            return Storage::disk($this->diskStorage)->url($sourceImageFs);
        }
    }

    public function hasImage(Model $model, $imageName = 'image'){
        return Storage::disk($this->diskStorage)->exists($this->getImagePath($model, $imageName));
    }

    public function hasThumbnails(Model $model, $imageName = 'image'){

        $sourceImageFs = $this->getImagePath($model, $imageName);
        $sizes = $this->getConfig('assets.images.'.$model->getTable().'.'.$imageName);
        if(!is_array($sizes)){
            $sizes = $this->getConfig('assets.images.generic');
        }

        foreach($sizes as $imageInfo){
            $width = $imageInfo['width'];
            $height = $imageInfo['height'];
            $quality = $imageInfo['quality'];

            $thumbnailsPath = $model->getTable().'/'.$model->id.'/thumbnails';
            $thumbFilename = $imageName.'-'.$model->slug.'-'.$width.'-'.$height.'.'.$this->imageFormat;
            $thumbFs = $thumbnailsPath.'/'.$thumbFilename;

            if(Storage::disk($this->diskThumbnails)->exists($thumbFs)){
                return true;
            }
        }
    }

    public function generateThumbnails(Model $model, $imageName = 'image'){

        if(!in_array(ModelImageTrait::class, class_uses($model))){
            return;
        }

        $sourceImageFs = $this->getImagePath($model, $imageName);

        $sizes = $this->getConfig('assets.images.'.$model->getTable().'.'.$imageName);

        if(!is_array($sizes)){
            $sizes = $this->getConfig('assets.images.generic');
        }

        foreach($sizes as $imageInfo){
            $width = $imageInfo['width'];
            $height = $imageInfo['height'];
            $quality = $imageInfo['quality'];

            $thumbnailsPath = $model->getTable().'/'.$model->id.'/thumbnails';
            $thumbFilename = $imageName.'-'.$model->slug.'-'.$width.'-'.$height.'.'.$this->imageFormat;
            $thumbFs = $thumbnailsPath.'/'.$thumbFilename;

            if(Storage::disk($this->diskThumbnails)->exists($thumbFs)){
                continue;
            }

            // check source
            if(!isset($imageContent)){
                if(!Storage::disk($this->diskStorage)->exists($sourceImageFs)){
                    return;
                }
                $imageContent = $this->imageFormat == 'webp'
                    ? imagecreatefromwebp('data://image/webp;base64,' . base64_encode (Storage::disk($this->diskStorage)->get($sourceImageFs)))
                    : Storage::disk($this->diskStorage)->get($sourceImageFs);
            }

            $manager = new ImageManager();
            $image = $manager->make($imageContent);

            if ($image->height() > $image->width()) {
                // Image has longer height, use resize()
                $image->resize($width, $height, function ($constraint) {
                    $constraint->aspectRatio();
                    $constraint->upsize(); // Allow upsizing if needed
                });

                $canvas = Image::canvas($width, $height, '#000000');
                $canvas->insert($image, 'center');

                Storage::disk($this->diskThumbnails)->put($thumbFs, $canvas->encode($this->imageFormat, $quality));

            } else {

                if ($image->height() < $height) {
                    // Apply a different approach for images with height below the minimum

                    $image->fit($width, $height, function ($constraint) {
                        $constraint->upsize(); // Allow upsizing if needed
                    });
            
                    Storage::disk($this->diskThumbnails)->put($thumbFs, $image->encode($this->imageFormat, $quality));
                } else {
                    // Use fit() for images that meet the minimum height
                    $image->resize($width, $height, function ($constraint) {
                        $constraint->aspectRatio();
                        $constraint->upsize(); // Allow upsizing if needed
                    });
            
                    Storage::disk($this->diskThumbnails)->put($thumbFs, $image->encode($this->imageFormat, $quality));
                }
            }
    
          
            
        }

        // update image record
        $image = $model->images($imageName)->first();
        if($image){
            $image->image_exists = 1;
            // $image->has_thumbnails = 1;
            $image->save();
        }
    }

    public function getThumbnail(Model $model, $imageName = 'image', $imageSize = 'thumb'){

        if(!$model->id){
            return;
        }

        if(!in_array(ModelImageTrait::class, class_uses($model))){
            return;
        }

        // defaults
        $defaultSize = $this->getConfig('assets.images.generic.'.$imageSize);
        if(!is_array($defaultSize)){
            return;
        }

        $width = $defaultSize['width'];
        $height = $defaultSize['height'];
        $quality = $defaultSize['quality'];

        $imageInfo = $this->getConfig('assets.images.'.$model->getTable().'.'.$imageName.'.'.$imageSize);

        if(is_array($imageInfo)){
            $width = $imageInfo['width'];
            $height = $imageInfo['height'];
            $quality = $imageInfo['quality'];
        }

        $thumbnailsPath = $model->getTable().'/'.$model->id.'/thumbnails';
        $thumbFilename = $imageName.'-'.$model->slug.'-'.$width.'-'.$height.'.'.$this->imageFormat;
        $thumbFs = $thumbnailsPath.'/'.$thumbFilename;

        $thumbSuf = '?upd='.substr(md5($model->updated_at),0,8);

        if(Storage::disk($this->diskThumbnails)->exists($thumbFs)){
            return url(Storage::disk($this->diskThumbnails)->url($thumbFs)).$thumbSuf;
        }

        $this->generateThumbnails($model, $imageName);

        if(Storage::disk($this->diskThumbnails)->exists($thumbFs)){
            return url(Storage::disk($this->diskThumbnails)->url($thumbFs)).$thumbSuf;
        }

        if(get_class($model)==Product::class){
            if(count($model->variations)){
                foreach($model->variations as $mv){
                    if($this->hasImage($mv, $imageName)){
                        return $this->getThumbnail($mv, $imageName, $imageSize);
                    }
                }
            }

            if($model->parent){
                if($this->hasImage($model->parent, $imageName)){
                    return $this->getThumbnail($model->parent, $imageName, $imageSize);
                }
            }
        }
    }

    // public function getGCImageUrl($model){
    //     if(get_class($model)==Product::class && $model->importAttribute){
    //         if($model->importAttribute->source=='guncritic'){
    //             foreach(['jpg', 'png'] as $imageExt){
    //                 $path = 'assets/products/'.$model->importAttribute->scrapper_product_id.'/images/main-image.'.$imageExt;
    //                 if(Storage::disk('s3')->has($path)){
    //                     $s3 = Storage::disk('s3')->getAdapter()->getClient();
    //                     return $s3->getObjectUrl( env('AWS_BUCKET'), $path);
    //                 }
    //             }
    //         }
    //     }
    // }

    public function deleteThumbnails(Model $model, $imageName = NULL){

        // remove thumbnails
        $thumbnailsPath = $model->getTable().'/'.$model->id.'/thumbnails';
        $thumbnails = Storage::disk($this->diskThumbnails)->allFiles($thumbnailsPath);
        if(strlen($imageName)){
            $imgNameSearch = $thumbnailsPath.'/'.$imageName;
            if(count($thumbnails)){
                $selectedThumbnails = [];
                foreach($thumbnails as $thumbFilename){
                    if (strpos($thumbFilename, $imgNameSearch) !== false) {
                        $selectedThumbnails[] = $thumbFilename;
                    }
                }
                $thumbnails = $selectedThumbnails;
            }
        }

        if(count($thumbnails)){
            Storage::disk($this->diskThumbnails)->delete($thumbnails);
        }

        // delete image record
        $model->images()->delete();
    }

    public function clearTempUploadsDir($model){
        Storage::deleteDirectory($this->getTempUploadDir($model));
    }

    public function getTempUploadDir($model){
        $userId = Auth::check() ? Auth::user()->id : 0;
        return 'tmp/uploads/'.$userId.'/'.$model->getTable().'/'.($model->exists ? $model->id : '0').'/';
    }

    public function uploadTempFile($model, $file){
        $tmpFilename = $this->getTempUploadDir($model);
        return $file->store($tmpFilename);
    }

    public function deleteImage(Model $model, $imageName = NULL){
        $sourceImageFs = $this->getImagePath($model, $imageName);
        if(Storage::disk($this->diskStorage)->exists($sourceImageFs)){
            Storage::disk($this->diskStorage)->delete($sourceImageFs);
        }
    }

    private function getImagePath(Model $model, $imageName = 'image'){
        $storagePath = $this->rootDir.'assets/'.$model->getTable().'/'.$model->id.'/images';
        return $storagePath.'/'.$imageName.'.'.$this->imageFormat;
    }

    public function getConfig($key){
        $keyWithSite = env('SITE_KEY').'_'.$key;
        if(config()->has($keyWithSite)){
            return config($keyWithSite);
        }else{
            return config($key);
        }
    }
}