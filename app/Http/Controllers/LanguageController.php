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

        if (env('APP_ENV') == 'production') {
        
            $english = ENV('EN_APP_URL', 'https://catchaguide.com');
            $german = ENV('DE_APP_URL', 'https://catchaguide.de');
    
            $previousUrl = url()->previous();
            $previousUrlComponents = parse_url($previousUrl);
    
            $previousPath = isset($previousUrlComponents['path']) ? $previousUrlComponents['path'] : '';
            
            if($validatedData['language'] == 'de'){
                return redirect($german.$previousPath);
            }
            if($validatedData['language'] == 'en'){
                return redirect($english.$previousPath);
            }
        }

        app()->setLocale($validatedData['language']);
        session()->put('locale', $validatedData['language']);
        
        return redirect()->back();
    }

}