<?php

namespace App\Http\Controllers\Admin\Category;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\Blog\StoreCategoryRequest;
use App\Http\Requests\Admin\Blog\UpdateCategoryRequest;
use App\Models\Country;
use App\Models\Region;
use App\Models\City;
use App\Models\CityTranslation;
use App\Models\DestinationFaq;
use App\Models\DestinationFishChart;
use App\Models\DestinationFishSizeLimit;
use App\Models\DestinationFishTimeLimit;
use DB;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Storage;
use Intervention\Image\Facades\Image;
use Exception;
use App\Helpers\TranslationHelper;

class AdminCategoryCityController extends Controller
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
        $rows = City::with(['country', 'region', 'translations'])->paginate(25);
        $data = compact('rows');
        return view('admin.pages.category.city', $data);
    }

    public function create()
    {
        $form = 'City';
        $route = route('admin.category.city.store');
        $method = '';
        $language = old('language');
        $country_id = old('country_id');
        $region_id = old('region_id');
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

        // Get countries and regions for dropdowns
        $countries = Country::orderBy('name', 'ASC')->get();
        $regions = Region::orderBy('name', 'ASC')->get();

        $data = compact('form', 'route', 'method', 'language', 'country_id', 'region_id', 'name', 'thumbnail', 'title', 'sub_title', 'introduction', 'body', 'place', 'placeLat', 'placeLng', 'country', 
            'fish_chart', 'fish_avail_title', 'fish_avail_intro', 
            'fish_size_limit', 'size_limit_title', 'size_limit_intro', 
            'fish_time_limit', 'time_limit_title', 'time_limit_intro', 
            'faq', 'faq_title', 'countries', 'regions', 'city', 'region');

        return view('admin.pages.category.form', $data);
    }

    public function store(Request $request)
    {
        $request->validate([
            'country_id' => [ 'required' ],
            'region_id' => [ 'nullable' ],
            'name' => [ 'required', 'max:255' ],
            'title' => [ 'required', 'max:255' ],
            'sub_title' => [ 'required', 'max:255' ],
            'filters' => [ 'required' ],
            'language' => [ 'required' ]
        ]);

        try {
            DB::beginTransaction();
            
            $slug = $this->slug_format($request->name);
            $webp_path = null;

            if($request->has('thumbnailImage')) {
                $webp_path = $this->upload_thumbnail($request->thumbnailImage);
            }

            // Create base city
            $city = City::firstOrCreate([
                'country_id' => $request->country_id,
                'region_id' => $request->region_id,
                'slug' => $slug,
            ], [
                'name' => $request->name,
                'filters' => $request->filters,
                'thumbnail_path' => $webp_path,
            ]);

            // Create translation
            CityTranslation::updateOrCreate([
                'city_id' => $city->id,
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

            DB::commit();

            return redirect()->back()->with('success', 'City Successfully Added!');
        } catch (Exception $e) {
            DB::rollBack();
            return redirect()->back()->withErrors(['message' => 'Ooops Something went wrong. Please reload the page.']);
        } catch (\Illuminate\Database\QueryException $e) {
            DB::rollBack();
            return redirect()->back()->withErrors(['message' => 'Ooops Something went wrong. Please reload the page.']);
        }
    }

    public function edit($id)
    {
        $city = City::with(['translations', 'country', 'region'])->find($id);

        if (is_null($city)) {
            return redirect()->back();
        }

        // Get primary language translation
        $primaryTranslation = $city->translations->where('language', 'de')->first() 
            ?? $city->translations->first();

        if (!$primaryTranslation) {
            return redirect()->back()->withErrors(['message' => 'No translations found']);
        }

        $form = 'City';
        $route = route('admin.category.city.update', $id);
        $method = 'PUT';
        $language = $primaryTranslation->language;
        $country_id = $city->country_id;
        $region_id = $city->region_id;
        $name = $city->name;
        $thumbnail = $city->getThumbnailPath();
        $title = $primaryTranslation->title;
        $sub_title = $primaryTranslation->sub_title;
        $introduction = $primaryTranslation->introduction;
        $body = $primaryTranslation->content;

        $filter = $city->filters ?? [];

        $place = $filter['place'] ?? '';
        $placeLat = $filter['placeLat'] ?? '';
        $placeLng = $filter['placeLng'] ?? '';
        $filterCountry = $filter['country'] ?? '';
        $filterCity = $filter['city'] ?? '';
        $filterRegion = $filter['region'] ?? '';

        $fish_chart = DestinationFishChart::where('destination_id', $city->id)->get();
        $fish_avail_title = $primaryTranslation->fish_avail_title;
        $fish_avail_intro = $primaryTranslation->fish_avail_intro;

        $fish_size_limit = DestinationFishSizeLimit::where('destination_id', $city->id)->get();
        $size_limit_title = $primaryTranslation->size_limit_title;
        $size_limit_intro = $primaryTranslation->size_limit_intro;

        $fish_time_limit = DestinationFishTimeLimit::where('destination_id', $city->id)->get();
        $time_limit_title = $primaryTranslation->time_limit_title;
        $time_limit_intro = $primaryTranslation->time_limit_intro;

        $faq = DestinationFaq::where('destination_id', $city->id)
            ->where('destination_type', 'city')
            ->where('language', $language)
            ->get();
        $faq_title = $primaryTranslation->faq_title;

        // Get countries and regions for dropdowns
        $countries = Country::orderBy('name', 'ASC')->get();
        $regions = Region::orderBy('name', 'ASC')->get();
        
        // Get the country and region objects for the city
        $country = $city->country;
        $region = $city->region;

        $data = compact('form', 'route', 'method', 'language', 'country_id', 'region_id', 'name', 'thumbnail', 'title', 'sub_title', 'introduction', 'body', 'place', 'placeLat', 'placeLng', 'country', 
            'fish_chart', 'fish_avail_title', 'fish_avail_intro', 
            'fish_size_limit', 'size_limit_title', 'size_limit_intro', 
            'fish_time_limit', 'time_limit_title', 'time_limit_intro', 
            'faq', 'faq_title', 
            'countries', 'regions', 'city', 'region');

        return view('admin.pages.category.form', $data);
    }

    public function getTranslation(Request $request, $id)
    {
        $language = $request->input('language');
        $city = City::with(['translations', 'country', 'region'])->find($id);

        if (!$city) {
            return response()->json(['error' => 'City not found'], 404);
        }

        // Get translation for requested language or create default structure
        $translation = $city->translations->where('language', $language)->first();
        
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
        $fish_chart = DestinationFishChart::where('destination_id', $city->id)->get()->toArray();
        $fish_size_limit = DestinationFishSizeLimit::where('destination_id', $city->id)->get()->toArray();
        $fish_time_limit = DestinationFishTimeLimit::where('destination_id', $city->id)->get()->toArray();
        $faq = DestinationFaq::where('destination_id', $city->id)
            ->where('destination_type', 'city')
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
            'country_id' => [ 'required' ],
            'region_id' => [ 'nullable' ],
            'name' => [ 'required', 'max:255' ],
            'title' => [ 'required', 'max:255' ],
            'sub_title' => [ 'required', 'max:255' ],
            'filters' => [ 'required' ],
            'language' => [ 'required' ]
        ]);

        try {
            DB::beginTransaction();
            
            $city = City::findOrFail($id);
            
            // Update base city data
            $city->update([
                'country_id' => $request->country_id,
                'region_id' => $request->region_id,
                'name' => $request->name,
                'slug' => $this->slug_format($request->name),
                'filters' => $request->filters,
            ]);

            if($request->has('thumbnailImage')) {
                $webp_path = $this->upload_thumbnail($request->thumbnailImage);
                $city->update(['thumbnail_path' => $webp_path]);
            }

            // Update translation
            CityTranslation::updateOrCreate([
                'city_id' => $city->id,
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

            $cityId = $city->id;

            if ($request->has('fish_chart')) {
                foreach ($request->fish_chart as $key => $value) {
                    $value['language'] = $request->language;
                    if ($value['id'] == 0) {
                        $value['destination_id'] = $id;
                        unset($value['id']);
                        DestinationFishChart::create($value);
                    } else {
                        DestinationFishChart::whereId($value['id'])->update($value);
                    }
                }
            }

            if ($request->has('fish_size_limit')) {
                foreach ($request->fish_size_limit as $key => $value) {
                    $value['language'] = $request->language;
                    if ($value['id'] == 0) {
                        $value['destination_id'] = $id;
                        unset($value['id']);
                        DestinationFishSizeLimit::create($value);
                    } else {
                        DestinationFishSizeLimit::whereId($value['id'])->update($value);
                    }
                }
            }

            if ($request->has('fish_time_limit')) {
                foreach ($request->fish_time_limit as $key => $value) {
                    $value['language'] = $request->language;
                    if ($value['id'] == 0) {
                        $value['destination_id'] = $id;
                        unset($value['id']);
                        DestinationFishTimeLimit::create($value);
                    } else {
                        DestinationFishTimeLimit::whereId($value['id'])->update($value);
                    }
                }
            }

            if ($request->has('faq')) {
                foreach ($request->faq as $key => $value) {
                    $value['language'] = $request->language;
                    if ($value['id'] == 0) {
                        $value['destination_id'] = $id;
                        unset($value['id']);
                        DestinationFaq::create($value);
                    } else {
                        DestinationFaq::whereId($value['id'])->update($value);
                    }
                }
            }
            DB::commit();

            return redirect()->back()->with('success', 'City Successfully Updated!');
        } catch (Exception $e) {
            DB::rollBack();
            return redirect()->back()->withErrors(['message' => 'Ooops Something went wrong. Please reload the page.']);
        } catch (\Illuminate\Database\QueryException $e) {
            DB::rollBack();
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
            
            $city = City::findOrFail($id);
            
            // Delete related records
            DestinationFaq::where('destination_id', $id)->where('destination_type', 'city')->delete();
            DestinationFishChart::where('destination_id', $id)->delete();
            DestinationFishSizeLimit::where('destination_id', $id)->delete();
            DestinationFishTimeLimit::where('destination_id', $id)->delete();
            
            // Delete city (translations cascade delete)
            $city->delete();
            
            DB::commit();
            return redirect()->back()->with('success', 'City Successfully Deleted!');
        } catch (Exception $e) {
            DB::rollBack();
            return redirect()->back()->withErrors(['message' => 'Error deleting city']);
        }
    }

    public function upload_thumbnail($thumbnailImage)
    {
        $thumbnail_path = $thumbnailImage->store('public');
        $imagePath = Storage::disk()->path($thumbnail_path);

        $image = Image::make($imagePath);
        $webpImageName = pathinfo($thumbnail_path, PATHINFO_FILENAME) . '.webp';
        $webpImage = $image->encode('webp', 75);

        $webp_path = 'blog/city/';

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

    private function translate($data, $request = null)
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

                if (isset($translatedTexts['fish_chart'])) {
                    foreach ($data->fish_chart as $index => $originalChart) {
                        $chartData = [
                            'destination_id' => $translatedData->id,
                            'language' => $toLanguage,
                            'fish' => $translatedTexts['fish_chart'][$index],
                            'jan' => $originalChart->jan,
                            'feb' => $originalChart->feb,
                            'mar' => $originalChart->mar,
                            'apr' => $originalChart->apr,
                            'may' => $originalChart->may,
                            'jun' => $originalChart->jun,
                            'jul' => $originalChart->jul,
                            'aug' => $originalChart->aug,
                            'sep' => $originalChart->sep,
                            'oct' => $originalChart->oct,
                            'nov' => $originalChart->nov,
                            'dec' => $originalChart->dec
                        ];
                        DestinationFishChart::create($chartData);
                    }
                }

                if (isset($translatedTexts['fish_size_limit'])) {
                    foreach ($data->fish_size_limit as $index => $originalLimit) {
                        DestinationFishSizeLimit::create([
                            'destination_id' => $translatedData->id,
                            'language' => $toLanguage,
                            'fish' => $translatedTexts['fish_size_limit'][$index],
                            'data' => $originalLimit->data
                        ]);
                    }
                }

                if (isset($translatedTexts['fish_time_limit'])) {
                    foreach ($data->fish_time_limit as $index => $originalLimit) {
                        DestinationFishTimeLimit::create([
                            'destination_id' => $translatedData->id,
                            'language' => $toLanguage,
                            'fish' => $translatedTexts['fish_time_limit'][$index],
                            'data' => $originalLimit->data
                        ]);
                    }
                }
                
                if (isset($translatedTexts['faq'])) {
                    foreach ($data->faq as $index => $originalFaq) {
                        $questionIndex = "question_$index";
                        $answerIndex = "answer_$index";
                        
                        $translatedQuestion = is_object($translatedTexts['faq']) 
                            ? $translatedTexts['faq']->$questionIndex 
                            : $translatedTexts['faq'][$questionIndex];
                            
                        $translatedAnswer = is_object($translatedTexts['faq']) 
                            ? $translatedTexts['faq']->$answerIndex 
                            : $translatedTexts['faq'][$answerIndex];
                            
                        DestinationFaq::create([
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
}