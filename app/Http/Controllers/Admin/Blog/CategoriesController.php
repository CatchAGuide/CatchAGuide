<?php

namespace App\Http\Controllers\Admin\Blog;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\Blog\StoreCategoryRequest;
use App\Http\Requests\Admin\Blog\UpdateCategoryRequest;
use App\Models\Category;
use Illuminate\Http\Request;

class CategoriesController extends Controller
{
    public function index()
    {
        return view('admin.pages.blog.categories.index', [
            'categories' => Category::all()
        ]);
    }

    public function create()
    {
        //
    }

    public function store(StoreCategoryRequest $request)
    {
        $data = $request->validated();
        Category::create($data);

        return redirect()->back();
    }

    public function show(Category $category)
    {
        //
    }

    public function edit(Category $category)
    {
        //
    }

    public function update(UpdateCategoryRequest $request, Category $category)
    {
        $data = $request->validated();

        $category->update($data);

        return redirect()->back();
    }

    public function delete(Category $category)
    {   
        $category->delete();

        return redirect()->back();
    }
}
