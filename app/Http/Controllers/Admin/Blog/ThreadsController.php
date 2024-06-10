<?php

namespace App\Http\Controllers\Admin\Blog;

use Intervention\Image\Facades\Image;
use Illuminate\Support\Facades\Storage;


use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\Blog\StoreThreadRequest;
use App\Http\Requests\Admin\Blog\UpdateThreadRequest;
use App\Models\Category;
use App\Models\Thread;
use Illuminate\Http\Request;

class ThreadsController extends Controller
{
    public function index()
    {
        return view('admin.pages.blog.threads.index', [
            'threads' => Thread::all()
        ]);
    }

    public function create()
    {
        return view('admin.pages.blog.threads.create', [
            'categories' => Category::all()
        ]);
    }

    public function store(Request $request)
    {   
        $webp_path = null;
        if($request->threadImage){
            $thumbnail_path = $request->threadImage->store('public');

            $imagePath = Storage::disk()->path($thumbnail_path);

            $image = Image::make($imagePath);
            
            $webpImageName = pathinfo($thumbnail_path, PATHINFO_FILENAME) . '.webp';

            $webpImage = $image->encode('webp', 75);
            Storage::disk('public')->put('blog/' . $webpImageName, $webpImage->encoded);
            $webpImage->save(public_path('blog/' . $webpImageName));
            $webp_path = 'blog/'.$webpImageName;
    
        }

        Thread::create([
            'language' => $request->lang,
            'title' => $request->title,
            'excerpt' => $request->excerpt,
            'body' => $request->body,
            'author' => $request->author,
            'category_id' => $request->category_id,
            'thumbnail_path' => $webp_path
        ]);

        return redirect()->route('admin.blog.threads.index');
    }

    public function update($id, Request $request)
    {
        $webp_path = null;
        
        if($request->threadImage)
        {
            $thumbnail_path = $request->threadImage->store('public');

            $imagePath = Storage::disk()->path($thumbnail_path);

            $image = Image::make($imagePath);
            
            $webpImageName = pathinfo($thumbnail_path, PATHINFO_FILENAME) . '.webp';

            $webpImage = $image->encode('webp', 75);
            Storage::disk('public')->put('blog/' . $webpImageName, $webpImage->encoded);
            $webpImage->save(public_path('blog/' . $webpImageName));

            $webp_path = 'blog/'.$webpImageName;
    
        }   

        $thread = Thread::find($id);

        if ($webp_path !== null) {
            $thread->update([
                'language' => $request->lang,
                'title' => $request->title,
                'excerpt' => $request->excerpt,
                'body' => $request->body,
                'author' => $request->author,
                'category_id' => $request->category_id,
                'thumbnail_path' => $webp_path,
            ]);
        } else {
            $thread->update([
                'language' => $request->lang,
                'title' => $request->title,
                'excerpt' => $request->excerpt,
                'body' => $request->body,
                'author' => $request->author,
                'category_id' => $request->category_id,
            ]);
        }

        return back();
    }

    public function show(Thread $thread)
    {
        //
    }

    public function edit(Thread $thread)
    {
        return view('admin.pages.blog.threads.edit', [
            'categories' => Category::all(),
            'thread' => $thread
        ]);
    }

    public function updatethreads(UpdateThreadRequest $request, $thread)
    {
        $data = $request->validated();

        $thread->update($data);

        return redirect()->back()->with('message', 'Der Beitrag wurde erfolgreich bearbeitet');
    }

    public function destroy(Thread $thread)
    {
        $thread->delete();

        return redirect()->route('admin.blog.threads');
    }

    public function delete(Thread $thread)
    {
        $thread->delete();

        return redirect()->route('admin.blog.threads.index');
    }

}
