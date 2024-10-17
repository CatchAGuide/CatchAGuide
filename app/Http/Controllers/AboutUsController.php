<?php

namespace App\Http\Controllers;

class AboutUsController extends Controller
{
    public function index()
    {
        return view('pages.additional.about-us');
    }
}
