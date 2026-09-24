<?php

namespace App\Http\Controllers\Blog;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Thread;
use App\Services\Magazine\MagazineListingService;

class ThreadsController extends Controller
{
    public function __construct(
        private MagazineListingService $magazine
    ) {}

    public function show($slug)
    {
        $locale = app()->getLocale();

        $thread = Thread::with('category')
            ->where('slug', $slug)
            ->where('language', $locale)
            ->first();

        if (! $thread) {
            return redirect()->route(app()->getLocale() === 'de' ? 'blogde.index' : 'blog.index');
        }

        // Rendered per request, not from the stored-HTML page cache (App\Models\Cache): a stored
        // copy kept serving an old <head> (meta description, hreflang) for up to a week after a
        // deploy, and carried the first visitor's CSRF token to everyone.
        return view('pages.blog.show', [
            'thread' => $thread,
            'recent_threads' => $this->magazine->relatedThreads($thread, $locale),
            'categories' => $this->magazine->categoriesWithCounts($locale),
        ]);
    }
}
