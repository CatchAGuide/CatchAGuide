<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class ImpressumController extends Controller
{
  public function index()
  {
      return view('pages.law.imprint');
  }
}
