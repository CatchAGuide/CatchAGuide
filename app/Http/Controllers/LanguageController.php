<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class LanguageController extends Controller
{
    public function switchLanguage(Request $request)
    {
        $validatedData = $request->validate([
            'language' => 'required|in:' . implode(',', config('app.locales')),
            'redirect_url' => 'nullable|string'
        ]);

        if (env('APP_ENV') == 'production') {
        
            $english = ENV('EN_APP_URL', 'https://catchaguide.com');
            $german = ENV('DE_APP_URL', 'https://catchaguide.de');
    
            // Use clean redirect URL if provided, otherwise use previous URL path
            if ($request->has('redirect_url') && !empty($request->redirect_url)) {
                $targetPath = $request->redirect_url;
            } else {
                $previousUrl = url()->previous();
                $previousUrlComponents = parse_url($previousUrl);
                $targetPath = isset($previousUrlComponents['path']) ? $previousUrlComponents['path'] : '';
            }
            
            if($validatedData['language'] == 'de'){
                return redirect($german.$targetPath);
            }
            if($validatedData['language'] == 'en'){
                return redirect($english.$targetPath);
            }
        }

        app()->setLocale($validatedData['language']);
        session()->put('locale', $validatedData['language']);
        
        // If redirect_url is provided, redirect to the clean URL
        if ($request->has('redirect_url') && !empty($request->redirect_url)) {
            return redirect($request->redirect_url);
        }
        
        return redirect()->back();
    }

}