<?php

namespace App\Http\Controllers\Blog;

use App\Http\Controllers\Controller;
use App\Models\Thread;
use App\Services\Magazine\MagazineListingService;
use Illuminate\Http\Request;

class BlogController extends Controller
{
    public function __construct(
        private MagazineListingService $magazine
    ) {}

    public function index(Request $request)
    {
        $locale = app()->getLocale();

        if (! in_array($locale, config('app.locales'), true)) {
            abort(404);
        }

        $data = $this->magazine->build(
            $locale,
            $request->query('q')
        );

        return view('pages.blog.index', $data);
    }

    public function redirectToNewFormat($slug)
    {
        $thread = Thread::where('slug', $slug)->first();

        if (! $thread) {
            return redirect()->route('blog.index');
        }

        return redirect(route('blog.thread.show', [$slug]), 301);
    }
}
