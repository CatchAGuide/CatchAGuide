<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Services\EventService;

class TestController extends Controller
{
    public function index()
    {
        return view('test.camp-contact');
    }
}
