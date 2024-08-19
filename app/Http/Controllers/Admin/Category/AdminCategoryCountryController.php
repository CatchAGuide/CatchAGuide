<?php

namespace App\Http\Controllers\Admin\Category;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\Blog\StoreCategoryRequest;
use App\Http\Requests\Admin\Blog\UpdateCategoryRequest;
use App\Models\Category;
use Illuminate\Http\Request;

class AdminCategoryCountryController extends Controller
{
    public function index()
    {
        $rows = [];
        $data = compact('rows');
        return view('admin.pages.category.country.index', $data);
    }

    public function create()
    {
        $title = '';
        $data = compact('title');
        return view('admin.pages.category.country.form', $data);
    }

    public function store(Request $request)
    {
        dd($request->all());
        //$data = $request->validated();
        //Category::create($data);

        ///return redirect()->back();
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
