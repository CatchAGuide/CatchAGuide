<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\Sales\SalesTexts;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

/**
 * Admin › Sales › Texts: edit the offer builder's fixed customer texts per language (spec §8.5).
 */
class SalesTextsController extends Controller
{
    public function index(SalesTexts $texts): View
    {
        return view('admin.pages.sales.texts', [
            'keys' => SalesTexts::KEYS,
            'languages' => SalesTexts::LANGUAGES,
            'overrides' => $texts->overrides(),
            'texts' => $texts,
        ]);
    }

    public function update(Request $request, SalesTexts $texts): RedirectResponse
    {
        $validated = $request->validate([
            'texts' => ['array'],
            'texts.*' => ['array'],
            'texts.*.*' => ['nullable', 'string', 'max:2000'],
        ]);

        $texts->save($validated['texts'] ?? [], $request->user('employees'));

        return redirect()->route('admin.sales.texts.index')->with('sales_notice', __('sales.texts.saved'));
    }
}
