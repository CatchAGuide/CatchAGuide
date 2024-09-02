<?php

namespace App\Http\Controllers\Category;

use App\Http\Controllers\Controller;
use App\Models\Destination;
use Illuminate\Http\Request;

class DestinationCountryController extends Controller
{
    public function index($country)
    {
        $row = Destination::with(['faq', 'fish_chart', 'fish_size_limit', 'fish_time_limit'])->whereName($country)->first();
        $faq = $row->faq;
        $fish_chart = $row->fish_chart;
        $fish_size_limit = $row->fish_size_limit;
        $fish_time_limit = $row->fish_time_limit;

        $data = compact('row', 'faq', 'fish_chart', 'fish_size_limit', 'fish_time_limit');

        return view('pages.category.country', $data);
    }

}
