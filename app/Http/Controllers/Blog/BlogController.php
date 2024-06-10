<?php

namespace App\Http\Controllers\Blog;

use App\Http\Controllers\Controller;
use App\Models\Thread;
use Illuminate\Http\Request;

use App\Support\SharedData;

class BlogController extends Controller
{
    public function index()
    {


        if(in_array(app()->getLocale(),config('app.locales'))){

            $query =  Thread::orderBy('id','desc');
    
            $threads =  $query->where('language',app()->getLocale())->get();
    
            return view('pages.blog.index', [
                'threads' => $threads,
            ]);
        }

        abort(404);

    }

    public function redirectToFishingMagazine(){
       return redirect(route('blog.thread.show',[$slug, $thread->language]), 301);
    }

    public function redirectToNewFormat($slug){

       $thread = Thread::where('slug', $slug)->first();

       if(!$thread){
            return redirect()->route('blog.index');
       }
   
       return redirect(route('blog.thread.show',[$slug]), 301);
    }
}
