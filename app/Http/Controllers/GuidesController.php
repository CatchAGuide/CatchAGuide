<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class GuidesController extends Controller
{
   public function store(Request $request)
   {
       return redirect()->back()->with('message', 'Anfrage wurde gestellt');
   }
}
