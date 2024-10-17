<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Guiding;

class FileUploadController extends Controller
{
    public function upload(Guiding $guiding,Request $request)
    {

        $files = $request->allFiles();

        if (empty($files)) {
            abort(422, 'No files were uploaded.');
        }

        $requestKey = array_key_first($files);
        $file = is_array($request->input($requestKey)) ? $request->file($requestKey)[0] : $request->file($requestKey);

        if(!$guiding->exists){
            return app('asset')->uploadTempFile($guiding, $file);
        }


        $images = app('guiding')->getImagesUrl($guiding);
        $imgKey = 'image_0';
        for($i=0;$i<=4;$i++){
            if(!isset($images['image_'.$i])){
                $imgKey = 'image_'.$i;
                break;
            }
        }

        app('asset')->uploadImage($guiding, $imgKey, $file);


    }
}
