<?php

namespace App\Http\Controllers\Blog;

use App\Http\Controllers\Controller;
use App\Models\Cache;
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

        $page = Cache::process('threads', $thread->id,
            'pages.blog.show', [
                'thread' => $thread,
                'recent_threads' => $this->magazine->relatedThreads($thread, $locale),
                'categories' => $this->magazine->categoriesWithCounts($locale),
            ]);

        return $page;
    }
}
