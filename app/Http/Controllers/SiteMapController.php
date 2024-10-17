<?php

namespace App\Http\Controllers;

use App\Models\Guiding;
use App\Models\Thread;
use Illuminate\Http\Request;

class SiteMapController extends Controller
{

    public function index()
    {
        $guidings = Guiding::where('status', 1)->get();
        $blogs = Thread::get();
        return response()->view('sitemap', compact('guidings', 'blogs'))->header('Content-Type', 'text/xml');
    }

}
