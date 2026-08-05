<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreMonthlyHighlightRequest;
use App\Http\Requests\Admin\UpdateMonthlyHighlightRequest;
use App\Models\CategoryPage;
use App\Models\Country;
use App\Models\MonthlyHighlight;
use App\Models\Target;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Cache;
use Illuminate\View\View;

class MonthlyHighlightController extends Controller
{
    public function index(): View
    {
        $highlights = MonthlyHighlight::query()
            ->orderBy('month')
            ->get();

        return view('admin.pages.monthly-highlights.index', compact('highlights'));
    }

    public function create(): View
    {
        return view('admin.pages.monthly-highlights.form', $this->formData(
            highlight: null,
            route: route('admin.monthly-highlights.store'),
            method: 'POST',
            pageTitle: 'Create Monthly Highlight',
        ));
    }

    public function store(StoreMonthlyHighlightRequest $request): RedirectResponse
    {
        $data = $request->safe()->only([
            'month',
            'title_en',
            'title_de',
            'subtitle_en',
            'subtitle_de',
            'items',
            'is_active',
        ]);
        $data['items'] = array_values(array_slice($data['items'] ?? [], 0, MonthlyHighlight::MAX_ITEMS));
        $highlight = MonthlyHighlight::query()->create($data);
        $this->forgetHomepageSeasonCache((int) $highlight->month);

        return redirect()
            ->route('admin.monthly-highlights.index')
            ->with('success', 'Monthly highlight created.');
    }

    public function edit(MonthlyHighlight $monthlyHighlight): View
    {
        return view('admin.pages.monthly-highlights.form', $this->formData(
            highlight: $monthlyHighlight,
            route: route('admin.monthly-highlights.update', $monthlyHighlight),
            method: 'PUT',
            pageTitle: 'Edit Monthly Highlight',
        ));
    }

    public function update(UpdateMonthlyHighlightRequest $request, MonthlyHighlight $monthlyHighlight): RedirectResponse
    {
        $previousMonth = (int) $monthlyHighlight->month;
        $data = $request->safe()->only([
            'month',
            'title_en',
            'title_de',
            'subtitle_en',
            'subtitle_de',
            'items',
            'is_active',
        ]);
        $data['items'] = array_values(array_slice($data['items'] ?? [], 0, MonthlyHighlight::MAX_ITEMS));
        $monthlyHighlight->update($data);
        $this->forgetHomepageSeasonCache($previousMonth);
        $this->forgetHomepageSeasonCache((int) $monthlyHighlight->month);

        return redirect()
            ->route('admin.monthly-highlights.index')
            ->with('success', 'Monthly highlight updated.');
    }

    public function destroy(MonthlyHighlight $monthlyHighlight): RedirectResponse
    {
        $month = (int) $monthlyHighlight->month;
        $monthlyHighlight->delete();
        $this->forgetHomepageSeasonCache($month);

        return redirect()
            ->route('admin.monthly-highlights.index')
            ->with('success', 'Monthly highlight deleted.');
    }

    private function forgetHomepageSeasonCache(int $month): void
    {
        foreach (['en', 'de'] as $locale) {
            Cache::forget("homepage_season_v2_{$locale}_{$month}");
        }
    }

    /**
     * @return array{
     *     highlight: ?MonthlyHighlight,
     *     route: string,
     *     method: string,
     *     pageTitle: string,
     *     months: array<int, string>,
     *     countryOptions: \Illuminate\Support\Collection,
     *     targetOptions: \Illuminate\Support\Collection,
     *     selectedCountryIds: array<int, int>,
     *     selectedTargetIds: array<int, int>
     * }
     */
    private function formData(?MonthlyHighlight $highlight, string $route, string $method, string $pageTitle): array
    {
        $targetPages = CategoryPage::query()
            ->where('type', 'Targets')
            ->orderBy('name')
            ->get(['id', 'name', 'source_id']);

        $targets = Target::query()
            ->whereIn('id', $targetPages->pluck('source_id')->filter()->unique())
            ->get()
            ->keyBy('id');

        $targetOptions = $targetPages->map(function (CategoryPage $page) use ($targets) {
            $target = $targets->get($page->source_id);

            return [
                'id' => $page->id,
                'label' => $target?->name ?? $page->name,
            ];
        })->values();

        $countryOptions = Country::query()
            ->orderBy('name')
            ->get(['id', 'name'])
            ->map(fn (Country $country) => [
                'id' => $country->id,
                'label' => $country->name,
            ])
            ->values();

        $items = collect($highlight?->items ?? []);

        return [
            'highlight' => $highlight,
            'route' => $route,
            'method' => $method,
            'pageTitle' => $pageTitle,
            'months' => $this->monthLabels(),
            'countryOptions' => $countryOptions,
            'targetOptions' => $targetOptions,
            'selectedCountryIds' => $items
                ->where('type', MonthlyHighlight::ITEM_TYPE_COUNTRY)
                ->pluck('id')
                ->map(fn ($id) => (int) $id)
                ->all(),
            'selectedTargetIds' => $items
                ->where('type', MonthlyHighlight::ITEM_TYPE_TARGET)
                ->pluck('id')
                ->map(fn ($id) => (int) $id)
                ->all(),
        ];
    }

    /**
     * @return array<int, string>
     */
    private function monthLabels(): array
    {
        $labels = [];
        for ($month = 1; $month <= 12; $month++) {
            $labels[$month] = now()->month($month)->translatedFormat('F');
        }

        return $labels;
    }
}
