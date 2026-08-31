<?php

namespace App\Http\Controllers\Admin\Category;

use App\Domain\CategoryPage\CategoryPageDimension;
use App\Domain\CategoryPage\CategoryPageEntityType;
use App\Domain\CategoryPage\CategoryPageScope;
use App\Http\Controllers\Admin\Category\Concerns\HandlesScopedCategoryContent;
use App\Http\Controllers\Controller;
use App\Models\CategoryEntity;
use App\Models\DestinationFishChart;
use App\Models\DestinationFishSizeLimit;
use App\Models\DestinationFishTimeLimit;
use App\Models\Faq;
use App\Models\Language;
use App\Services\CategoryPage\CategoryPageContentService;
use DB;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Intervention\Image\Facades\Image;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\Rule;

class AdminCategoryCountryController extends Controller
{
    use HandlesScopedCategoryContent;

    public function __construct(
        private CategoryPageContentService $content,
    ) {
    }

    public function index()
    {
        $rows = CategoryEntity::countries()->paginate(25);
        $languagesByEntity = $this->languagesByEntity($rows->pluck('id'), CategoryPageEntityType::GEO_COUNTRY);

        $data = compact('rows', 'languagesByEntity');
        return view('admin.pages.category.country', $data);
    }

    public function create()
    {
        $form = 'Country';
        $route = route('admin.category.country.store');
        $method = '';
        $language = old('language');
        $countrycode = old('countrycode');
        $name = old('name');
        $thumbnail = 'https://place-hold.it/300x300';
        $title = old('title');
        $sub_title = old('sub_title');
        $introduction = old('introduction');
        $body = old('body');

        $filter = old('filters');
        $place = $filter['place'] ?? '';
        $placeLat = $filter['placeLat'] ?? '';
        $placeLng = $filter['placeLng'] ?? '';
        $country = $filter['country'] ?? '';
        $city = $filter['city'] ?? '';
        $region = $filter['region'] ?? '';

        $fish_chart = old('fish_chart');
        $fish_avail_title = old('fish_avail_title');
        $fish_avail_intro = old('fish_avail_intro');

        $fish_size_limit = old('fish_size_limit');
        $size_limit_title = old('size_limit_title');
        $size_limit_intro = old('size_limit_intro');

        $fish_time_limit = old('fish_time_limit');
        $time_limit_title = old('time_limit_title');
        $time_limit_intro = old('time_limit_intro');

        $faq = old('faq');
        $faq_title = old('faq_title');

        $data = compact('form', 'route', 'method', 'language', 'countrycode', 'name', 'thumbnail', 'title', 'sub_title', 'introduction', 'body', 'place', 'placeLat', 'placeLng', 'country',
            'fish_chart', 'fish_avail_title', 'fish_avail_intro',
            'fish_size_limit', 'size_limit_title', 'size_limit_intro',
            'fish_time_limit', 'time_limit_title', 'time_limit_intro',
            'faq', 'faq_title', 'city', 'region'
        );

        return view('admin.pages.category.form', $data);
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|max:255',
            'title' => 'required|max:255',
            'sub_title' => 'required|max:255',
            'filters' => 'required',
            'language' => 'required|max:255'
        ]);

        try {
            DB::beginTransaction();

            $slug = $this->slug_format($request->name);

            $webp_path = null;
            if ($request->has('thumbnailImage')) {
                $webp_path = $this->upload_thumbnail($request->thumbnailImage);
            }

            $country = CategoryEntity::countries()->firstOrCreate([
                'slug' => $slug,
            ], [
                'type' => 'country',
                'countrycode' => $request->countrycode ?? null,
                'filters' => $request->filters,
                'thumbnail_path' => $webp_path,
                'name' => $request->name,
            ]);

            $this->content->upsertEntity(
                CategoryPageEntityType::GEO_COUNTRY,
                $country->id,
                CategoryPageScope::GLOBAL,
                $request->language,
                $this->legacyContentFields($request),
            );
            $this->content->replaceFaqsForEntity(
                CategoryPageEntityType::GEO_COUNTRY,
                $country->id,
                CategoryPageScope::TOURS,
                $request->language,
                collect($request->input('faq', []))->values()->all(),
            );

            DB::commit();

            return redirect()->back()->with('success', 'Country Successfully Added!');
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Exception', ['message' => $e->getMessage()]);
            return redirect()->back()->withErrors(['message' => 'Ooops Something went wrong. Please reload the page.']);
        } catch (\Illuminate\Database\QueryException $e) {
            DB::rollBack();
            Log::error('Query Exception', ['message' => $e->getMessage()]);
            return redirect()->back()->withErrors(['message' => 'Ooops Something went wrong. Please reload the page.']);
        }
    }

    public function edit($id)
    {
        $country = CategoryEntity::countries()->find($id);

        if (is_null($country)) {
            return redirect()->back();
        }

        $legacy = $this->content->findForEntity(CategoryPageEntityType::GEO_COUNTRY, $country->id, CategoryPageScope::GLOBAL, 'de')
            ?? $this->content->findForEntity(CategoryPageEntityType::GEO_COUNTRY, $country->id, CategoryPageScope::GLOBAL, 'en');

        $form = 'Country';
        $route = route('admin.category.country.update', $id);
        $method = 'PUT';
        $language = $legacy->language ?? 'de';
        $countrycode = $country->countrycode;
        $name = $country->name;
        $thumbnail = $country->getThumbnailPath();
        $title = $legacy->title ?? '';
        $sub_title = $legacy->sub_title ?? '';
        $introduction = $legacy->introduction ?? '';
        $body = $legacy->content ?? '';

        $filter = $country->filters ?? [];

        $place = $filter['place'] ?? '';
        $placeLat = $filter['placeLat'] ?? '';
        $placeLng = $filter['placeLng'] ?? '';
        $filterCountry = $filter['country'] ?? '';
        $city = $filter['city'] ?? '';
        $filterRegion = $filter['region'] ?? '';

        $fishDataId = $country->legacyId() ?? $country->id;

        $fish_chart = DestinationFishChart::where('destination_id', $fishDataId)->get();
        $fish_avail_title = $legacy->fish_avail_title ?? '';
        $fish_avail_intro = $legacy->fish_avail_intro ?? '';

        $fish_size_limit = DestinationFishSizeLimit::where('destination_id', $fishDataId)->get();
        $size_limit_title = $legacy->size_limit_title ?? '';
        $size_limit_intro = $legacy->size_limit_intro ?? '';

        $fish_time_limit = DestinationFishTimeLimit::where('destination_id', $fishDataId)->get();
        $time_limit_title = $legacy->time_limit_title ?? '';
        $time_limit_intro = $legacy->time_limit_intro ?? '';

        $faq = $this->content->faqsForEntity(CategoryPageEntityType::GEO_COUNTRY, $country->id, CategoryPageScope::TOURS, $language);
        $faq_title = $legacy->faq_title ?? '';

        $data = compact('form', 'route', 'method', 'language', 'countrycode', 'name', 'thumbnail', 'title', 'sub_title', 'introduction', 'body', 'place', 'placeLat', 'placeLng', 'filterCountry',
            'fish_chart', 'fish_avail_title', 'fish_avail_intro',
            'fish_size_limit', 'size_limit_title', 'size_limit_intro',
            'fish_time_limit', 'time_limit_title', 'time_limit_intro',
            'faq', 'faq_title', 'city', 'filterRegion', 'country'
        );

        $scopes = CategoryPageScope::forDimension(CategoryPageDimension::COUNTRY);
        $scoped = $this->scopedEditorPayload(
            $this->content,
            CategoryPageEntityType::GEO_COUNTRY,
            $country->id,
            $scopes,
            CategoryPageScope::GLOBAL,
        );

        return view('admin.pages.category.form', array_merge($data, $scoped, [
            'scopedEditorEnabled' => true,
            'languageDataUrl' => route('admin.category.country.language-data', $country->id),
            'autosaveUrl' => route('admin.category.country.autosave', $country->id),
        ]));
    }

    public function getLanguageData($id)
    {
        $country = CategoryEntity::countries()->find($id);

        if ($country === null) {
            return response()->json(['error' => 'Country not found'], 404);
        }

        $scopes = CategoryPageScope::forDimension(CategoryPageDimension::COUNTRY);
        $scope = request('scope', CategoryPageScope::GLOBAL);
        if (! in_array($scope, $scopes, true)) {
            $scope = CategoryPageScope::GLOBAL;
        }

        return response()->json(
            $this->scopedLanguageDataResponse($this->content, CategoryPageEntityType::GEO_COUNTRY, $country->id, $scope)
        );
    }

    public function autosave(Request $request, $id)
    {
        $country = CategoryEntity::countries()->find($id);

        if ($country === null) {
            return response()->json(['error' => 'Country not found'], 404);
        }

        return $this->autosaveScopedContent(
            $request,
            $this->content,
            CategoryPageEntityType::GEO_COUNTRY,
            $country->id,
            CategoryPageScope::forDimension(CategoryPageDimension::COUNTRY),
        );
    }

    public function getTranslation(Request $request, $id)
    {
        $language = $request->input('language');
        $country = CategoryEntity::countries()->find($id);

        if (!$country) {
            return response()->json(['error' => 'Country not found'], 404);
        }

        $translation = $this->content->findForEntity(CategoryPageEntityType::GEO_COUNTRY, $country->id, CategoryPageScope::GLOBAL, $language);

        if (!$translation) {
            return response()->json([
                'exists' => false,
                'language' => $language,
                'title' => '',
                'sub_title' => '',
                'introduction' => '',
                'content' => '',
                'fish_avail_title' => '',
                'fish_avail_intro' => '',
                'size_limit_title' => '',
                'size_limit_intro' => '',
                'time_limit_title' => '',
                'time_limit_intro' => '',
                'faq_title' => '',
                'fish_chart' => [],
                'fish_size_limit' => [],
                'fish_time_limit' => [],
                'faq' => []
            ]);
        }

        $fishDataId = $country->legacyId() ?? $country->id;
        $fish_chart = DestinationFishChart::where('destination_id', $fishDataId)->get()->toArray();
        $fish_size_limit = DestinationFishSizeLimit::where('destination_id', $fishDataId)->get()->toArray();
        $fish_time_limit = DestinationFishTimeLimit::where('destination_id', $fishDataId)->get()->toArray();
        $faq = $this->content->faqsForEntity(CategoryPageEntityType::GEO_COUNTRY, $country->id, CategoryPageScope::TOURS, $language)->toArray();

        return response()->json([
            'exists' => true,
            'language' => $language,
            'title' => $translation->title,
            'sub_title' => $translation->sub_title,
            'introduction' => $translation->introduction,
            'content' => $translation->content,
            'fish_avail_title' => $translation->fish_avail_title,
            'fish_avail_intro' => $translation->fish_avail_intro,
            'size_limit_title' => $translation->size_limit_title,
            'size_limit_intro' => $translation->size_limit_intro,
            'time_limit_title' => $translation->time_limit_title,
            'time_limit_intro' => $translation->time_limit_intro,
            'faq_title' => $translation->faq_title,
            'fish_chart' => $fish_chart,
            'fish_size_limit' => $fish_size_limit,
            'fish_time_limit' => $fish_time_limit,
            'faq' => $faq
        ]);
    }

    public function update(Request $request, $id)
    {
        $scopes = CategoryPageScope::forDimension(CategoryPageDimension::COUNTRY);

        $rules = [
            'name' => 'required|max:255',
            'filters' => 'required',
            'language' => 'required|max:255',
        ];

        if ($request->filled('content_scope')) {
            $rules['content_scope'] = ['required', Rule::in($scopes)];
            $rules['languageSwitch'] = ['required', Rule::in(config('app.locales'))];
            $rules['title'] = 'required|max:255';
            $rules['sub_title'] = 'required|max:255';
        } else {
            $rules['title'] = 'required|max:255';
            $rules['sub_title'] = 'required|max:255';
        }

        $request->validate($rules);

        try {
            DB::beginTransaction();

            $country = CategoryEntity::countries()->findOrFail($id);

            $country->update([
                'name' => $request->name,
                'slug' => $this->slug_format($request->name),
                'countrycode' => $request->countrycode ?? null,
                'filters' => $request->filters,
            ]);

            if ($request->has('thumbnailImage')) {
                $webp_path = $this->upload_thumbnail($request->thumbnailImage);
                $country->update(['thumbnail_path' => $webp_path]);
            }

            if (! $request->filled('content_scope')) {
                $this->content->upsertEntity(
                    CategoryPageEntityType::GEO_COUNTRY,
                    $country->id,
                    CategoryPageScope::GLOBAL,
                    $request->language,
                    $this->legacyContentFields($request),
                );
                $this->content->replaceFaqsForEntity(
                    CategoryPageEntityType::GEO_COUNTRY,
                    $country->id,
                    CategoryPageScope::TOURS,
                    $request->language,
                    collect($request->input('faq', []))->values()->all(),
                );
            }

            $this->saveScopedContent(
                $request,
                $this->content,
                CategoryPageEntityType::GEO_COUNTRY,
                $country->id,
                $scopes,
            );

            $countryId = $country->legacyId() ?? $country->id;

            if ($request->has('fish_chart')) {
                foreach ($request->fish_chart as $key => $value) {
                    $value['language'] = $request->language;
                    if (isset($value['id']) && $value['id'] == 0) {
                        $value['destination_id'] = $countryId;
                        $value['destination_type'] = 'country';
                        unset($value['id']);
                        DestinationFishChart::create($value);
                    } elseif (isset($value['id'])) {
                        DestinationFishChart::whereId($value['id'])->update($value);
                    } else {
                        $value['destination_id'] = $countryId;
                        $value['destination_type'] = 'country';
                        DestinationFishChart::create($value);
                    }
                }
            }

            if ($request->has('fish_size_limit')) {
                foreach ($request->fish_size_limit as $key => $value) {
                    $value['language'] = $request->language;
                    if (isset($value['id']) && $value['id'] == 0) {
                        $value['destination_id'] = $countryId;
                        $value['destination_type'] = 'country';
                        unset($value['id']);
                        DestinationFishSizeLimit::create($value);
                    } elseif (isset($value['id'])) {
                        DestinationFishSizeLimit::whereId($value['id'])->update($value);
                    } else {
                        $value['destination_id'] = $countryId;
                        $value['destination_type'] = 'country';
                        DestinationFishSizeLimit::create($value);
                    }
                }
            }

            if ($request->has('fish_time_limit')) {
                foreach ($request->fish_time_limit as $key => $value) {
                    $value['language'] = $request->language;
                    if (isset($value['id']) && $value['id'] == 0) {
                        $value['destination_id'] = $countryId;
                        $value['destination_type'] = 'country';
                        unset($value['id']);
                        DestinationFishTimeLimit::create($value);
                    } elseif (isset($value['id'])) {
                        DestinationFishTimeLimit::whereId($value['id'])->update($value);
                    } else {
                        $value['destination_id'] = $countryId;
                        $value['destination_type'] = 'country';
                        DestinationFishTimeLimit::create($value);
                    }
                }
            }

            DB::commit();

            return redirect()->back()->with('success', 'Country Successfully Updated!');
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Exception', ['message' => $e->getMessage()]);
            return redirect()->back()->withErrors(['message' => 'Ooops Something went wrong. Please reload the page.']);
        } catch (\Illuminate\Database\QueryException $e) {
            DB::rollBack();
            Log::error('Query Exception', ['message' => $e->getMessage()]);
            return redirect()->back()->withErrors(['message' => 'Ooops Something went wrong. Please reload the page.']);
        }
    }

    public function show()
    {
        //
    }

    public function destroy($id)
    {
        try {
            DB::beginTransaction();

            $country = CategoryEntity::countries()->findOrFail($id);
            $fishDataId = $country->legacyId() ?? $id;

            $this->purgeContent(CategoryPageEntityType::GEO_COUNTRY, $id);
            DestinationFishChart::where('destination_id', $fishDataId)->delete();
            DestinationFishSizeLimit::where('destination_id', $fishDataId)->delete();
            DestinationFishTimeLimit::where('destination_id', $fishDataId)->delete();

            $country->delete();

            DB::commit();
            return redirect()->back()->with('success', 'Country Successfully Deleted!');
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Exception', ['message' => $e->getMessage()]);
            return redirect()->back()->withErrors(['message' => 'Ooops Something went wrong. Please reload the page.']);
        }
    }

    public function upload_thumbnail($thumbnailImage)
    {
        $thumbnail_path = $thumbnailImage->store('public');
        $imagePath = Storage::disk()->path($thumbnail_path);

        $image = Image::make($imagePath);
        $webpImageName = pathinfo($thumbnail_path, PATHINFO_FILENAME) . '.webp';
        $webpImage = $image->encode('webp', 75);

        $webp_path = 'blog/country/';

        if (!Storage::disk('public_path')->exists($webp_path)) {
            Storage::disk('public_path')->makeDirectory($webp_path);
        }

        $webp_path .= $webpImageName;

        Storage::disk('public_path')->put($webp_path, $webpImage->encoded);
        $webpImage->save(public_path($webp_path));

        return $webp_path;
    }

    public function slug_format($value)
    {
        return str_replace(' ', '-', strtolower($value));
    }

    /**
     * @return array{title: string, sub_title: string, introduction: string, content: string, faq_title: string, fish_avail_title: string, fish_avail_intro: string, size_limit_title: string, size_limit_intro: string, time_limit_title: string, time_limit_intro: string}
     */
    private function legacyContentFields(Request $request): array
    {
        return [
            'title' => $request->input('title', ''),
            'sub_title' => $request->input('sub_title', ''),
            'introduction' => $request->input('introduction', ''),
            'content' => $request->input('body', ''),
            'faq_title' => $request->input('faq_title', ''),
            'fish_avail_title' => $request->input('fish_avail_title', ''),
            'fish_avail_intro' => $request->input('fish_avail_intro', ''),
            'size_limit_title' => $request->input('size_limit_title', ''),
            'size_limit_intro' => $request->input('size_limit_intro', ''),
            'time_limit_title' => $request->input('time_limit_title', ''),
            'time_limit_intro' => $request->input('time_limit_intro', ''),
        ];
    }

    private function purgeContent(string $entityType, int|string $sourceId): void
    {
        Language::query()
            ->where('source_id', (string) $sourceId)
            ->where('type', $entityType)
            ->delete();

        Faq::query()
            ->where('source_id', $sourceId)
            ->where('page', CategoryPageEntityType::faqPageKey($entityType))
            ->delete();
    }
}
