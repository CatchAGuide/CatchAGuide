<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\PageAttributeRequest;
use Illuminate\Support\Facades\Validator;

use Illuminate\Http\Request;
use App\Models\PageAttribute;

class PageAttributeController extends Controller
{
    public function index(){
        $locale = 'en';
        $pageattributes = PageAttribute::withTrashed()->where('domain','catchaguide.com')->get();
        return view('admin.pages.page-attribute.index',compact('pageattributes','locale'));
    }

    public function indexDe(){
        $locale = 'de';
        $pageattributes = PageAttribute::withTrashed()->where('domain','catchaguide.de')->get();
        return view('admin.pages.page-attribute.index',compact('pageattributes','locale'));
    }
    public function store(PageAttributeRequest $request){

        //dd($request->all());

        $attributes = new PageAttribute;

        $attributes->whereDomain($request->domain)->whereUri($request->uri)->whereMetaType($request->meta_type)->whereNull('deleted_at')->delete();

        $attributes->create($request->validated());
        return back()->with('success', 'Page Attribute Successfully Added');
    }

    public function update(PageAttribute $attribute, PageAttributeRequest $request){
   
        $attribute->update($request->validated());
        return back()->with('success', 'Page Attribute Successfully updated');
    }

    public function destroy(PageAttribute $attribute){

        $attribute->delete();

        return back()->with('success', 'Page Attribute Successfully Deleted');

    }

}
