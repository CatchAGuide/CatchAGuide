<?php

namespace App\Http\Controllers\Blog;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Services\Magazine\MagazineListingService;
use Illuminate\Http\Request;

class CategoriesController extends Controller
{
    public function __construct(
        private MagazineListingService $magazine
    ) {}

    public function show(Request $request, Category $category)
    {
        $locale = app()->getLocale();

        if (! in_array($locale, config('app.locales'), true)) {
            abort(404);
        }

        $data = $this->magazine->build(
            $locale,
            $request->query('q'),
            $category
        );

        return view('pages.blog.index', $data);
    }
}
