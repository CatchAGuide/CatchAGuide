<?php

namespace App\Http\Controllers;

use App\Models\Faq;
use Illuminate\Http\Request;

class FAQController extends Controller
{
    public function index()
    {
        return view('pages.law.faq', [
            'faqs' => Faq::all()
        ]);
    }
}
