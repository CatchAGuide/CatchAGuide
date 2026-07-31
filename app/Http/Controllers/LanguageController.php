<?php

namespace App\Http\Controllers;

use App\Services\Seo\LocalePathMapper;
use Illuminate\Http\Request;

class LanguageController extends Controller
{
    public function __construct(
        private readonly LocalePathMapper $localePathMapper,
    ) {}

    public function switchLanguage(Request $request)
    {
        $validatedData = $request->validate([
            'language' => 'required|in:' . implode(',', config('app.locales')),
            'redirect_url' => 'nullable|string',
        ]);

        if (app()->environment('production')) {
            $english = rtrim((string) config('cag.en_app_url', env('EN_APP_URL', 'https://www.catchaguide.com')), '/');
            $german = rtrim((string) config('cag.de_app_url', env('DE_APP_URL', 'https://www.catchaguide.de')), '/');

            if ($request->filled('redirect_url')) {
                $targetPath = (string) $request->input('redirect_url');
            } else {
                $previousUrl = url()->previous();
                $previousUrlComponents = parse_url($previousUrl);
                $targetPath = $previousUrlComponents['path'] ?? '';
            }

            $targetPath = ltrim(parse_url($targetPath, PHP_URL_PATH) ?: $targetPath, '/');
            $currentLang = app()->getLocale();
            $targetLang = $validatedData['language'];
            $mappedPath = $this->localePathMapper->mapPath($targetPath, $currentLang, $targetLang);

            if ($targetLang === 'de') {
                return redirect($german . ($mappedPath !== '' ? '/' . $mappedPath : ''));
            }

            if ($targetLang === 'en') {
                return redirect($english . ($mappedPath !== '' ? '/' . $mappedPath : ''));
            }
        }

        app()->setLocale($validatedData['language']);
        session()->put('locale', $validatedData['language']);

        if ($request->filled('redirect_url')) {
            return redirect($request->input('redirect_url'));
        }

        return redirect()->back();
    }
}
