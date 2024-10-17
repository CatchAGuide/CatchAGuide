<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Services\EventService;

class TestController extends Controller
{
    public function index()
    {
        (new EventService())->getAvailableEvents(3, '2021-01-27', User::first());
    }
}
