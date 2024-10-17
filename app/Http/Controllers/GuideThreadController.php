<?php

namespace App\Http\Controllers;

use App\Models\Guiding;
use App\Models\Category;
use App\Models\GuideThread;
use Illuminate\Http\Request;

class GuideThreadController extends Controller
{
    public function categoryIndex($slug){

        $thread = GuideThread::where('language',app()->getLocale())->where('slug',$slug)->first();
        if(!$thread){
            abort(404);
        }
        $filters = $thread->filters;

        return view('pages.category.show',compact('thread','filters'));

    }

}
