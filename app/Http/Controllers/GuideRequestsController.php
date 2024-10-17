<?php

namespace App\Http\Controllers;

use App\Models\User;

class GuideRequestsController extends Controller
{
    public function index()
    {

        $guides = User::where('is_guide', 0)->get();
        return view('admin.pages.guide-requests.index', compact('guides'));
    }
}
