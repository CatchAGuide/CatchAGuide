<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class LanguageController extends Controller
{
    public function switchLanguage(Request $request)
    {
        $validatedData = $request->validate([
            'language' => 'required|in:' . implode(',', config('app.locales')),
        ]);
        $previousUrl = url()->previous();
        $previousUrlComponents = parse_url($previousUrl);

        $previousPath = isset($previousUrlComponents['path']) ? $previousUrlComponents['path'] : '';

        if($validatedData['language'] == 'de'){
            return redirect('https://catchaguide.de'.$previousPath);
        }
        if($validatedData['language'] == 'en'){
            return redirect('https://catchaguide.com'.$previousPath);
        }
        // app()->setLocale($validatedData['language']);
        // session()->put('locale', $validatedData['language']);
        
        // return redirect()->back();
    }

}