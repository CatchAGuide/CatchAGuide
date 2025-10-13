<?php

namespace App\Http\Controllers\Admin\Category;

use App\Http\Controllers\Controller;
use App\Models\Country;
use App\Models\CountryTranslation;
use App\Models\DestinationFaq;
use App\Models\DestinationFishChart;
use App\Models\DestinationFishSizeLimit;
use App\Models\DestinationFishTimeLimit;
use DB;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Intervention\Image\Facades\Image;
use Illuminate\Support\Facades\Log;
use App\Helpers\TranslationHelper;

class AdminCategoryCountryController extends Controller
{
    private $language;
    public function __construct()
    {
        $this->language = [
            'en',
            'de'
        ];
    }

    public function index()
    {
        // Get all countries with their translations
        $rows = Country::with('translations')->paginate(25);
        
        $data = compact('rows');
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
            
            // Handle thumbnail upload
            $webp_path = null;
            if($request->has('thumbnailImage')) {
                $webp_path = $this->upload_thumbnail($request->thumbnailImage);
            }

            // Step 1: Create or find base Country record
            $country = Country::firstOrCreate([
                'slug' => $slug,
            ], [
                'name' => $request->name,
                'countrycode' => $request->countrycode ?? null,
                'filters' => $request->filters,
                'thumbnail_path' => $webp_path,
            ]);

            // Step 2: Create translation for the submitted language
            CountryTranslation::updateOrCreate([
                'country_id' => $country->id,
                'language' => $request->language,
            ], [
                'title' => $request->title,
                'sub_title' => $request->sub_title,
                'introduction' => $request->introduction,
                'content' => $request->body,
                'fish_avail_title' => $request->fish_avail_title,
                'fish_avail_intro' => $request->fish_avail_intro,
                'size_limit_title' => $request->size_limit_title,
                'size_limit_intro' => $request->size_limit_intro,
                'time_limit_title' => $request->time_limit_title,
                'time_limit_intro' => $request->time_limit_intro,
                'faq_title' => $request->faq_title,
            ]);

            // Step 3: Auto-translate to other languages
            $this->translateCountry($country, $request);

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
        // $id now refers to country_id, get the country with all translations
        $country = Country::with(['translations'])->find($id);

        if (is_null($country)) {
            return redirect()->back();
        }

        // Get primary language translation (default to German)
        $primaryTranslation = $country->translations->where('language', 'de')->first() 
            ?? $country->translations->first();

        if (!$primaryTranslation) {
            return redirect()->back()->withErrors(['message' => 'No translations found for this country']);
        }

        $form = 'Country';
        $route = route('admin.category.country.update', $id);
        $method = 'PUT';
        $language = $primaryTranslation->language;
        $countrycode = $country->countrycode;
        $name = $country->name;
        $thumbnail = $country->getThumbnailPath();
        $title = $primaryTranslation->title;
        $sub_title = $primaryTranslation->sub_title;
        $introduction = $primaryTranslation->introduction;
        $body = $primaryTranslation->content;

        $filter = $country->filters ?? [];

        $place = $filter['place'] ?? '';
        $placeLat = $filter['placeLat'] ?? '';
        $placeLng = $filter['placeLng'] ?? '';
        $filterCountry = $filter['country'] ?? '';
        $city = $filter['city'] ?? '';
        $filterRegion = $filter['region'] ?? '';

        // Get related data for this country
        $fish_chart = DestinationFishChart::where('destination_id', $country->id)->get();
        $fish_avail_title = $primaryTranslation->fish_avail_title;
        $fish_avail_intro = $primaryTranslation->fish_avail_intro;

        $fish_size_limit = DestinationFishSizeLimit::where('destination_id', $country->id)->get();
        $size_limit_title = $primaryTranslation->size_limit_title;
        $size_limit_intro = $primaryTranslation->size_limit_intro;

        $fish_time_limit = DestinationFishTimeLimit::where('destination_id', $country->id)->get();
        $time_limit_title = $primaryTranslation->time_limit_title;
        $time_limit_intro = $primaryTranslation->time_limit_intro;

        $faq = DestinationFaq::where('destination_id', $country->id)
            ->where('destination_type', 'country')
            ->where('language', $language)
            ->get();
        $faq_title = $primaryTranslation->faq_title;

        $data = compact('form', 'route', 'method', 'language', 'countrycode', 'name', 'thumbnail', 'title', 'sub_title', 'introduction', 'body', 'place', 'placeLat', 'placeLng', 'filterCountry', 
            'fish_chart', 'fish_avail_title', 'fish_avail_intro', 
            'fish_size_limit', 'size_limit_title', 'size_limit_intro', 
            'fish_time_limit', 'time_limit_title', 'time_limit_intro', 
            'faq', 'faq_title', 'city', 'filterRegion', 'country'
        );

        return view('admin.pages.category.form', $data);
    }

    public function getTranslation(Request $request, $id)
    {
        $language = $request->input('language');
        $country = Country::with(['translations'])->find($id);

        if (!$country) {
            return response()->json(['error' => 'Country not found'], 404);
        }

        // Get translation for requested language or create default structure
        $translation = $country->translations->where('language', $language)->first();
        
        if (!$translation) {
            // Return empty structure if translation doesn't exist
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

        // Get language-specific data
        $fish_chart = DestinationFishChart::where('destination_id', $country->id)->get()->toArray();
        $fish_size_limit = DestinationFishSizeLimit::where('destination_id', $country->id)->get()->toArray();
        $fish_time_limit = DestinationFishTimeLimit::where('destination_id', $country->id)->get()->toArray();
        $faq = DestinationFaq::where('destination_id', $country->id)
            ->where('destination_type', 'country')
            ->where('language', $language)
            ->get()
            ->toArray();

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
        $request->validate([
            'name' => 'required|max:255',
            'title' => 'required|max:255',
            'sub_title' => 'required|max:255',
            'filters' => 'required',
            'language' => 'required|max:255'
        ]);

        try {
            DB::beginTransaction();
            
            // Find the country
            $country = Country::findOrFail($id);
            
            // Update base country data
            $country->update([
                'name' => $request->name,
                'slug' => $this->slug_format($request->name),
                'countrycode' => $request->countrycode ?? null,
                'filters' => $request->filters,
            ]);

            // Handle thumbnail upload
            if($request->has('thumbnailImage')) {
                $webp_path = $this->upload_thumbnail($request->thumbnailImage);
                $country->update(['thumbnail_path' => $webp_path]);
            }

            // Update or create translation for the submitted language
            CountryTranslation::updateOrCreate([
                'country_id' => $country->id,
                'language' => $request->language,
            ], [
                'title' => $request->title,
                'sub_title' => $request->sub_title,
                'introduction' => $request->introduction,
                'content' => $request->body,
                'fish_avail_title' => $request->fish_avail_title,
                'fish_avail_intro' => $request->fish_avail_intro,
                'size_limit_title' => $request->size_limit_title,
                'size_limit_intro' => $request->size_limit_intro,
                'time_limit_title' => $request->time_limit_title,
                'time_limit_intro' => $request->time_limit_intro,
                'faq_title' => $request->faq_title,
            ]);

            $countryId = $country->id;

            // Handle fish chart data
            if ($request->has('fish_chart')) {
                foreach ($request->fish_chart as $key => $value) {
                    $value['language'] = $request->language;
                    if (isset($value['id']) && $value['id'] == 0) {
                        $value['destination_id'] = $countryId;
                        unset($value['id']);
                        DestinationFishChart::create($value);
                    } else if (isset($value['id'])) {
                        DestinationFishChart::whereId($value['id'])->update($value);
                    } else {
                        $value['destination_id'] = $countryId;
                        DestinationFishChart::create($value);
                    }
                }
            }

            // Handle fish size limit data
            if ($request->has('fish_size_limit')) {
                foreach ($request->fish_size_limit as $key => $value) {
                    $value['language'] = $request->language;
                    if (isset($value['id']) && $value['id'] == 0) {
                        $value['destination_id'] = $countryId;
                        unset($value['id']);
                        DestinationFishSizeLimit::create($value);
                    } else if (isset($value['id'])) {
                        DestinationFishSizeLimit::whereId($value['id'])->update($value);
                    } else {
                        $value['destination_id'] = $countryId;
                        DestinationFishSizeLimit::create($value);
                    }
                }
            }

            // Handle fish time limit data
            if ($request->has('fish_time_limit')) {
                foreach ($request->fish_time_limit as $key => $value) {
                    $value['language'] = $request->language;
                    if (isset($value['id']) && $value['id'] == 0) {
                        $value['destination_id'] = $countryId;
                        unset($value['id']);
                        DestinationFishTimeLimit::create($value);
                    } else if (isset($value['id'])) {
                        DestinationFishTimeLimit::whereId($value['id'])->update($value);
                    } else {
                        $value['destination_id'] = $countryId;
                        DestinationFishTimeLimit::create($value);
                    }
                }
            }

            // Handle FAQ data
            if ($request->has('faq')) {
                foreach ($request->faq as $key => $value) {
                    $value['language'] = $request->language;
                    if (isset($value['id']) && $value['id'] == 0) {
                        $value['destination_id'] = $countryId;
                        unset($value['id']);
                        DestinationFaq::create($value);
                    } else if (isset($value['id'])) {
                        DestinationFaq::whereId($value['id'])->update($value);
                    } else {
                        $value['destination_id'] = $countryId;
                        DestinationFaq::create($value);
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
            
            $country = Country::findOrFail($id);
            
            // Delete related records first
            DestinationFaq::where('destination_id', $id)->where('destination_type', 'country')->delete();
            DestinationFishChart::where('destination_id', $id)->delete();
            DestinationFishSizeLimit::where('destination_id', $id)->delete();
            DestinationFishTimeLimit::where('destination_id', $id)->delete();
            
            // Delete the country (translations will cascade delete due to foreign key)
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
     * Translate country to other languages
     * TODO: Re-implement auto-translation using TranslationHelper for new structure
     */
    private function translateCountry(Country $country, $request = null)
    {
        // Get the source translation
        $sourceLanguage = $request->language ?? 'de';
        $sourceTranslation = CountryTranslation::where('country_id', $country->id)
            ->where('language', $sourceLanguage)
            ->first();

        if (!$sourceTranslation) {
            return;
        }

        // For now, just create empty translations for other languages
        // TODO: Implement full auto-translation logic
        foreach ($this->language as $toLanguage) {
            if ($toLanguage !== $sourceLanguage) {
                CountryTranslation::firstOrCreate([
                    'country_id' => $country->id,
                    'language' => $toLanguage,
                ], [
                    'title' => $sourceTranslation->title, // Placeholder - should be translated
                    'sub_title' => $sourceTranslation->sub_title,
                    'introduction' => $sourceTranslation->introduction,
                    'content' => $sourceTranslation->content,
                ]);
            }
        }
    }

    /**
     * OLD TRANSLATE METHOD - Keep for reference during migration
     * TODO: Remove after full migration complete
     */
    private function translate_OLD($data, $request = null)
    {
        $texts = [
            "name" => $data->name,
            "title" => $data->title,
            "sub_title" => $data->sub_title,
            "introduction" => $data->introduction,
            "content" => $data->content,
            'fish_avail_title' => $data->fish_avail_title,
            'fish_avail_intro' => $data->fish_avail_intro,
            'size_limit_title' => $data->size_limit_title,
            'size_limit_intro' => $data->size_limit_intro,
            'time_limit_title' => $data->time_limit_title,
            'time_limit_intro' => $data->time_limit_intro,
            'faq_title' => $data->faq_title
        ];

        if ($request && $request->has('fish_chart')) {
            $fishChartTexts = [];
            foreach ($request->fish_chart as $index => $chart) {
                $fishChartTexts[$index] = $chart['fish'];
            }

            $texts['fish_chart'] = $fishChartTexts;
        }

        if ($request && $request->has('fish_size_limit')) {
            $fishSizeLimitTexts = [];
            foreach ($request->fish_size_limit as $index => $limit) {
                $fishSizeLimitTexts[$index] = $limit['fish'];
            }

            $texts['fish_size_limit'] = $fishSizeLimitTexts;
        }

        if ($request && $request->has('fish_time_limit')) {
            $fishTimeLimitTexts = [];
            foreach ($request->fish_time_limit as $index => $limit) {
                $fishTimeLimitTexts[$index] = $limit['fish'];
            }

            $texts['fish_time_limit'] = $fishTimeLimitTexts;
        }

        if ($request && $request->has('faq')) {
            $faqTexts = [];
            foreach ($request->faq as $index => $faq) {
                $faqTexts["question_$index"] = $faq['question'];
                $faqTexts["answer_$index"] = $faq['answer'];
            }

            $texts['faq'] = $faqTexts;
        }

        foreach ($this->language as $toLanguage) {
            if ($toLanguage !== $data->language) {

                $translatedData = $data->replicate();

                // $translatedTexts = TranslationHelper::simpleBatchTranslate(
                //     $texts,
                //     $toLanguage,
                //     $data->language
                // );

                $forTranslatedData = $translatedTexts ?? [];
                unset($forTranslatedData['fish_chart'], $forTranslatedData['fish_size_limit'], $forTranslatedData['fish_time_limit'], $forTranslatedData['faq']);

                foreach ($forTranslatedData as $field => $translation) {
                    $translatedData->$field = $translation;
                }

                $translatedData->save();

                if (isset($translatedTexts['fish_chart']) && $request) {
                    foreach ($request->fish_chart as $index => $originalChart) {
                        $chartData = array_filter($originalChart, function($key) {
                            return $key !== 'fish' && $key !== 'id';
                        }, ARRAY_FILTER_USE_KEY);
                        
                        $translatedFishChart = DestinationFishChart::create([
                            'destination_id' => $translatedData->id,
                            'language' => $toLanguage,
                            'fish' => $translatedTexts['fish_chart'][$index],
                            ...$chartData
                        ]);
                    }
                }

                if (isset($translatedTexts['fish_size_limit']) && $request) {
                    foreach ($request->fish_size_limit as $index => $originalLimit) {
                        $translatedFishSizeLimit = DestinationFishSizeLimit::create([
                            'destination_id' => $translatedData->id,
                            'language' => $toLanguage,
                            'fish' => $translatedTexts['fish_size_limit'][$index],
                            'data' => $originalLimit['data']
                        ]);
                    }
                }

                if (isset($translatedTexts['fish_time_limit']) && $request) {
                    foreach ($request->fish_time_limit as $index => $originalLimit) {
                        $translatedFishTimeLimit = DestinationFishTimeLimit::create([
                            'destination_id' => $translatedData->id,
                            'language' => $toLanguage,
                            'fish' => $translatedTexts['fish_time_limit'][$index],
                            'data' => $originalLimit['data']
                        ]);
                    }
                }
                
                if (isset($translatedTexts['faq']) && $request) {
                    foreach ($request->faq as $index => $faq) {
                        $questionIndex = "question_$index";
                        $answerIndex = "answer_$index";
                        
                        $translatedQuestion = is_object($translatedTexts['faq']) 
                            ? $translatedTexts['faq']->$questionIndex 
                            : $translatedTexts['faq'][$questionIndex];
                            
                        $translatedAnswer = is_object($translatedTexts['faq']) 
                            ? $translatedTexts['faq']->$answerIndex 
                            : $translatedTexts['faq'][$answerIndex];
                            
                        $translatedFaq = DestinationFaq::create([
                            'destination_id' => $translatedData->id,
                            'language' => $toLanguage,
                            'question' => $translatedQuestion,
                            'answer' => $translatedAnswer
                        ]);
                    }
                }
            }
        }
    }

    public function getLanguageData($id)
    {
        // Get country and its translation for requested language
        $country = Country::find($id);
        if (!$country) {
            return response()->json(['error' => 'Country not found'], 404);
        }

        $requestedLanguage = request('language', 'en');
        $translation = CountryTranslation::where('country_id', $country->id)
            ->where('language', $requestedLanguage)
            ->first();

        if (is_null($country)) {
            return response()->json(['error' => 'Country not found'], 404);
        }
        
        return response()->json([
            'title' => $country->title,
            'sub_title' => $country->sub_title,
            'introduction' => $country->introduction,
            'content' => $country->content,
            'fish_avail_title' => $country->fish_avail_title,
            'fish_avail_intro' => $country->fish_avail_intro,
            'size_limit_title' => $country->size_limit_title,
            'size_limit_intro' => $country->size_limit_intro,
            'time_limit_title' => $country->time_limit_title,
            'time_limit_intro' => $country->time_limit_intro,
            'faq_title' => $country->faq_title,
            'fish_chart' => $country->fish_chart,
            'fish_size_limit' => $country->fish_size_limit,
            'fish_time_limit' => $country->fish_time_limit,
            'faq' => $country->faq
        ]);
    }
}