<?php

namespace App\Http\Controllers\Admin\NewBlog;

use App\Models\Thread;
use App\Models\Category;


use App\Models\GuideThread;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Intervention\Image\Facades\Image;
use Illuminate\Support\Facades\Storage;
use App\Http\Requests\Admin\Blog\StoreThreadRequest;
use App\Http\Requests\Admin\Blog\UpdateThreadRequest;

class GuideThreadsController extends Controller
{
    public function index()
    {
        return view('admin.pages.newblog.threads.index', [
            'threads' => GuideThread::all()
        ]);
    }

    public function create()
    {
        return view('admin.pages.newblog.threads.create', [
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
            Storage::disk('public')->put('newblog/' . $webpImageName, $webpImage->encoded);
            $webpImage->save(public_path('newblog/' . $webpImageName));
            $webp_path = 'newblog/'.$webpImageName;

        }

        GuideThread::create([
            'language' => $request->lang,
            'title' => mb_convert_encoding($request->title, 'UTF-8', 'auto'),
            'excerpt' => $request->excerpt,
            'body' => $request->body,
            'author' => $request->author,
            'filters' => json_encode($request->filters),
            'introduction' => $request->introduction,
            'thumbnail_path' => $webp_path
        ]);

        return redirect()->route('admin.newblog.threads.index');
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
            Storage::disk('public')->put('newblog/' . $webpImageName, $webpImage->encoded);
            $webpImage->save(public_path('newblog/' . $webpImageName));

            $webp_path = 'newblog/'.$webpImageName;

        }

        $thread = GuideThread::find($id);

        if ($webp_path !== null) {
            $thread->update([
                'language' => $request->lang,
                'title' => $request->title,
                'excerpt' => $request->excerpt,
                'body' => $request->body,
                'author' => $request->author,
                'filters' => $request->filters,
                'introduction' => $request->introduction,
                'thumbnail_path' => $webp_path,
            ]);
        } else {
            $thread->update([
                'language' => $request->lang,
                'title' => $request->title,
                'excerpt' => $request->excerpt,
                'body' => $request->body,
                'author' => $request->author,
                'introduction' => $request->introduction,
                'filters' => $request->filters,
            ]);
        }


        return back();
    }

    public function show(GuideThread $thread)
    {
        //
    }

    public function edit(GuideThread $thread)
    {
        $filters = json_decode($thread->filters);

        return view('admin.pages.newblog.threads.edit', [
            'categories' => Category::all(),
            'thread' => $thread,
            'filters' => $filters,
        ]);
    }

    public function updatethreads(UpdateGuideThreadRequest $request, $thread)
    {

        $data = $request->validated();

        $thread->update($data);

        return redirect()->back()->with('message', 'Der Beitrag wurde erfolgreich bearbeitet');
    }

    public function destroy(GuideThread $thread)
    {
        $thread->delete();

        return redirect()->route('admin.newblog.threads');
    }

    public function delete(GuideThread $thread)
    {
        $thread->delete();

        return redirect()->route('admin.newblog.threads.index');
    }

}
