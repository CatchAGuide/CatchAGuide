<?php

namespace App\Http\Controllers;

use App\Models\TermsSection;
use App\Models\TermsSectionTranslation;
use Illuminate\Support\Collection;

class TermsController extends Controller
{
    public function show($sectionId = null)
    {
        if (!$this->shouldUseDynamicTerms()) {
            return view('pages.law.agb-static');
        }

        $locale = $this->locale();
        $sections = $this->loadSections();

        if ($sections->isEmpty()) {
            return view('pages.law.agb-static');
        }

        if ($sectionId !== null) {
            $current = $sections->firstWhere('id', (int) $sectionId);
            abort_unless($current, 404);
        } else {
            $current = $sections->first(fn (TermsSection $section) => !$this->isPrivacySection($section))
                ?? $sections->first();
        }

        if ($this->isPrivacySection($current)) {
            return redirect()->route('law.data-protection');
        }

        $navItems = $this->buildNav($sections, $locale, 'section-' . $current->id);
        $activeIndex = collect($navItems)->search(fn ($item) => $item['active']);

        return view('pages.law.agb-dynamic', [
            'locale' => $locale,
            'navItems' => $navItems,
            'translation' => $this->translationFor($current, $locale),
            'sectionNumber' => $activeIndex + 1,
            'prevItem' => $navItems[$activeIndex - 1] ?? null,
            'nextItem' => $navItems[$activeIndex + 1] ?? null,
        ]);
    }

    public function dataProtection()
    {
        $locale = $this->locale();
        $navItems = null;

        if ($this->shouldUseDynamicTerms()) {
            $sections = $this->loadSections();
            if ($sections->isNotEmpty()) {
                $navItems = $this->buildNav($sections, $locale, 'privacy');
            }
        }

        return view('pages.law.data-protection', [
            'locale' => $locale,
            'navItems' => $navItems,
        ]);
    }

    private function shouldUseDynamicTerms(): bool
    {
        return (bool) config('terms.dynamic_enabled', false);
    }

    private function locale(): string
    {
        return app()->getLocale() === 'en' ? 'en' : 'de';
    }

    private function loadSections(): Collection
    {
        return TermsSection::active()
            ->ordered()
            ->with('translations')
            ->get()
            ->filter(fn (TermsSection $section) => $section->translations->isNotEmpty())
            ->values();
    }

    /**
     * Prefer the current locale's translation, fall back to whichever exists
     * so a missing translation never produces an empty page.
     */
    private function translationFor(TermsSection $section, string $locale): TermsSectionTranslation
    {
        return $section->translations->firstWhere('language', $locale)
            ?? $section->translations->first();
    }

    private function isPrivacySection(TermsSection $section): bool
    {
        return $section->translations->contains(function (TermsSectionTranslation $translation) {
            $title = mb_strtolower($translation->title);

            return str_contains($title, 'datenschutz')
                || str_contains($title, 'privacy')
                || str_contains($title, 'data protection');
        });
    }

    private function buildNav(Collection $sections, string $locale, string $activeKey): array
    {
        return $sections->map(function (TermsSection $section) use ($locale, $activeKey) {
            $isPrivacy = $this->isPrivacySection($section);
            $key = $isPrivacy ? 'privacy' : 'section-' . $section->id;

            return [
                'key' => $key,
                'title' => $this->translationFor($section, $locale)->title,
                'url' => $isPrivacy
                    ? route('law.data-protection')
                    : route('law.agb', ['section' => $section->id]),
                'active' => $key === $activeKey,
            ];
        })->values()->all();
    }
}
