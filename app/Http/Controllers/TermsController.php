<?php

namespace App\Http\Controllers;

use App\Models\TermsSection;

class TermsController extends Controller
{
    public function show()
    {
        if (!$this->shouldUseDynamicTerms()) {
            return view('pages.law.agb-static');
        }

        $locale = app()->getLocale() === 'en' ? 'en' : 'de';

        $sections = TermsSection::active()
            ->ordered()
            ->with(['translations' => function ($query) use ($locale) {
                $query->where('language', $locale);
            }])
            ->get()
            ->filter(function (TermsSection $section) {
                return $section->translations->isNotEmpty();
            })
            ->values();

        if ($sections->isEmpty()) {
            return view('pages.law.agb-static');
        }

        return view('pages.law.agb-dynamic', compact('sections', 'locale'));
    }

    private function shouldUseDynamicTerms(): bool
    {
        return (bool) config('terms.dynamic_enabled', false);
    }
}
