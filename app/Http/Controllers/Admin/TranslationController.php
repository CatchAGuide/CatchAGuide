<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class TranslationController extends Controller
{
    public function index()
    {
        // return view('admin.pages.translation.index', [
        //     'faqs' => Faq::all()
        // ]);
    }

    public function create()
    {
        return view('admin.pages.translate.create');
    }

    public function destroy(Faq $faq)
    {
        //
    }
}
