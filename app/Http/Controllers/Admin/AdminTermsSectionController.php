<?php

namespace App\Http\Controllers\Admin;

use App\Helpers\TranslationHelper;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreTermsSectionRequest;
use App\Http\Requests\Admin\UpdateTermsSectionRequest;
use App\Models\TermsSection;
use App\Models\TermsSectionTranslation;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class AdminTermsSectionController extends Controller
{
    private array $languages = ['de', 'en'];

    public function index()
    {
        $sections = TermsSection::with('translations')
            ->ordered()
            ->get();

        return view('admin.pages.terms.index', compact('sections'));
    }

    public function create()
    {
        return view('admin.pages.terms.form', [
            'section' => null,
            'translation' => null,
            'language' => 'de',
            'route' => route('admin.terms.store'),
            'method' => 'POST',
            'pageTitle' => 'T&C Section erstellen',
        ]);
    }

    public function store(StoreTermsSectionRequest $request)
    {
        $data = $request->validated();

        try {
            DB::beginTransaction();

            $sortOrder = $data['sort_order'] ?? ((int) TermsSection::max('sort_order') + 1);

            $section = TermsSection::create([
                'sort_order' => $sortOrder,
                'is_active' => $data['is_active'] ?? true,
            ]);

            TermsSectionTranslation::create([
                'terms_section_id' => $section->id,
                'language' => $data['language'],
                'title' => $data['title'],
                'content' => $data['content'],
            ]);

            $this->autoTranslate($section, $data['language'], $data['title'], $data['content']);

            DB::commit();

            return redirect()
                ->route('admin.terms.index')
                ->with('success', 'T&C section created successfully.');
        } catch (Exception $e) {
            DB::rollBack();
            Log::error('Terms section store failed', ['message' => $e->getMessage()]);

            return redirect()
                ->back()
                ->withInput()
                ->withErrors(['message' => 'Something went wrong. Please try again.']);
        }
    }

    public function edit(TermsSection $termsSection)
    {
        $language = request('language', 'de');
        $translation = $termsSection->translationFor($language);

        return view('admin.pages.terms.form', [
            'section' => $termsSection,
            'translation' => $translation,
            'language' => $language,
            'route' => route('admin.terms.update', $termsSection),
            'method' => 'PUT',
            'pageTitle' => 'T&C Section bearbeiten',
        ]);
    }

    public function update(UpdateTermsSectionRequest $request, TermsSection $termsSection)
    {
        $data = $request->validated();

        try {
            DB::beginTransaction();

            $termsSection->update([
                'is_active' => $data['is_active'] ?? $termsSection->is_active,
                'sort_order' => $data['sort_order'] ?? $termsSection->sort_order,
            ]);

            TermsSectionTranslation::updateOrCreate(
                [
                    'terms_section_id' => $termsSection->id,
                    'language' => $data['language'],
                ],
                [
                    'title' => $data['title'],
                    'content' => $data['content'],
                ]
            );

            DB::commit();

            return redirect()
                ->route('admin.terms.edit', ['termsSection' => $termsSection, 'language' => $data['language']])
                ->with('success', 'T&C section updated successfully.');
        } catch (Exception $e) {
            DB::rollBack();
            Log::error('Terms section update failed', ['message' => $e->getMessage()]);

            return redirect()
                ->back()
                ->withInput()
                ->withErrors(['message' => 'Something went wrong. Please try again.']);
        }
    }

    public function destroy(TermsSection $termsSection)
    {
        $termsSection->delete();

        return back()->with('success', 'T&C section deleted successfully.');
    }

    public function reorder(Request $request)
    {
        $request->validate([
            'id' => ['required', 'integer', 'exists:terms_sections,id'],
            'direction' => ['required', 'string', 'in:up,down'],
        ]);

        $section = TermsSection::findOrFail($request->id);
        $swapWith = TermsSection::ordered()
            ->when($request->direction === 'up', function ($query) use ($section) {
                return $query->where('sort_order', '<', $section->sort_order)
                    ->orderByDesc('sort_order');
            }, function ($query) use ($section) {
                return $query->where('sort_order', '>', $section->sort_order)
                    ->orderBy('sort_order');
            })
            ->first();

        if (!$swapWith) {
            return back()->with('success', 'Already at the edge of the list.');
        }

        $currentOrder = $section->sort_order;
        $section->update(['sort_order' => $swapWith->sort_order]);
        $swapWith->update(['sort_order' => $currentOrder]);

        return back()->with('success', 'Order updated.');
    }

    public function getTranslation(Request $request, TermsSection $termsSection)
    {
        $language = $request->input('language', 'de');
        $translation = $termsSection->translationFor($language);

        if (!$translation) {
            return response()->json([
                'exists' => false,
                'language' => $language,
                'title' => '',
                'content' => '',
            ]);
        }

        return response()->json([
            'exists' => true,
            'language' => $language,
            'title' => $translation->title,
            'content' => $translation->content,
        ]);
    }

    private function autoTranslate(TermsSection $section, string $fromLanguage, string $title, string $content): void
    {
        foreach ($this->languages as $toLanguage) {
            if ($toLanguage === $fromLanguage) {
                continue;
            }

            $exists = TermsSectionTranslation::where('terms_section_id', $section->id)
                ->where('language', $toLanguage)
                ->exists();

            if ($exists) {
                continue;
            }

            try {
                $translated = TranslationHelper::batchTranslate(
                    [
                        'title' => $title,
                        'content' => $content,
                    ],
                    $toLanguage,
                    $fromLanguage,
                    'terms'
                );

                TermsSectionTranslation::create([
                    'terms_section_id' => $section->id,
                    'language' => $toLanguage,
                    'title' => $translated['title'] ?? $title,
                    'content' => $translated['content'] ?? $content,
                ]);
            } catch (Exception $e) {
                Log::error('Terms section auto-translate failed', [
                    'section_id' => $section->id,
                    'from' => $fromLanguage,
                    'to' => $toLanguage,
                    'message' => $e->getMessage(),
                ]);

                // Fallback: copy source text so the other language row exists for editing
                TermsSectionTranslation::create([
                    'terms_section_id' => $section->id,
                    'language' => $toLanguage,
                    'title' => $title,
                    'content' => $content,
                ]);
            }
        }
    }
}
