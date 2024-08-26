<?php

namespace App\Http\Controllers\Blog;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\Blog\UpdateThreadRequest;
use App\Models\Cache;
use App\Models\Category;
use App\Models\Thread;
use Illuminate\Http\Request;

use App\Support\SharedData;

class ThreadsController extends Controller
{

    public function show($slug)
    {
        $locale = app()->getLocale();

        $thread = Thread::where('slug', $slug)->where('language', $locale)->first();

        if(!$thread){   
            return redirect()->route('blog.index');
        }

        $recent = Thread::where('id', '!=', $thread->id)->where('language',$locale)->get();

        $page = Cache::process('threads', $thread->id, 
            'pages.blog.show', [
            'thread' => $thread,
            'recent_threads' => $recent,
            'categories' => Category::all(),
        ]);

        return $page;
        
        /*return view('pages.blog.show', [
            'thread' => $thread,
            'recent_threads' => $recent,
            'categories' => Category::all(),
        ]);*/

    }
}
