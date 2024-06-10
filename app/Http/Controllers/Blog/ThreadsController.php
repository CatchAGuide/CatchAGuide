<?php

namespace App\Http\Controllers\Blog;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\Blog\UpdateThreadRequest;
use App\Models\Category;
use App\Models\Thread;
use Illuminate\Http\Request;

use App\Support\SharedData;

class ThreadsController extends Controller
{

    public function show($slug)
    {

        $thread = Thread::where('slug', $slug)->where('language',app()->getLocale())->first();

        if(!$thread){   
            return redirect()->route('blog.index');
        }

        $recent = Thread::where('id', '!=', $thread->id)->where('language',app()->getLocale())->get();


        return view('pages.blog.show', [
            'thread' => $thread,
            'recent_threads' => $recent,
            'categories' => Category::all(),
        ]);

    }


}
