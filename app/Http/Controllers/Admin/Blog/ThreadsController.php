<?php

namespace App\Http\Controllers\Admin\Blog;

use Intervention\Image\Facades\Image;
use Illuminate\Support\Facades\Storage;


use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\Blog\StoreThreadRequest;
use App\Http\Requests\Admin\Blog\UpdateThreadRequest;
use App\Models\Cache;
use App\Models\CacheList;
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

        $thread = Thread::create([
            'language' => $request->lang,
            'title' => mb_convert_encoding($request->title, 'UTF-8', 'auto'),
            'excerpt' => $request->excerpt,
            'body' => $request->body,
            'author' => $request->author,
            'category_id' => $request->category_id,
            'thumbnail_path' => $webp_path,
            'cache' => $request->cache
        ]);

        $this->cacheMode($request->cache, 'threads', $thread->id);

        return redirect()->route('admin.blog.threads.index');
    }

    public function cacheMode($cache_val, $table, $id)
    {
        $check = CacheList::whereTable($table)->whereTableId($id)->count();

        if ($cache_val == 1) {
            if ($check <= 0) {
                CacheList::create([
                    'table' => $table,
                    'table_id' => $id
                ]);
            } else {
                $cache_key = $table . ':' . $id . '=' . url('');
                Cache::where('key', $cache_key)->delete();
            }
        } else {
            if ($check >= 1) {
                CacheList::whereTable($table)->whereTableId($id)->delete();
            }
        }

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
                'cache' => $request->cache
            ]);
        } else {
            $thread->update([
                'language' => $request->lang,
                'title' => $request->title,
                'excerpt' => $request->excerpt,
                'body' => $request->body,
                'author' => $request->author,
                'category_id' => $request->category_id,
                'cache' => $request->cache
            ]);
        }

        $this->cacheMode($request->cache, 'threads', $id);

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
