<?php

namespace App\Http\Controllers\Admin\Category;

use App\Http\Controllers\Controller;
use App\Models\Destination;
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
        // Get all countries and group by countrycode (or name if countrycode is null), preferring German language first
        $allCountries = Destination::whereType('country')->get();
        
        // Group by countrycode (or slug if countrycode is null) and get the preferred language version
        $uniqueCountries = $allCountries->groupBy(function ($country) {
            // Group by countrycode if available, otherwise by slug to handle same countries with different names
            return $country->countrycode ?: $country->slug;
        })->map(function ($countries) {
            // Sort by language preference: German first, then English, then others
            return $countries->sortBy(function ($country) {
                return $country->language === 'de' ? 1 : ($country->language === 'en' ? 2 : 3);
            })->first();
        })->values();

        // Convert to paginated collection
        $page = request()->get('page', 1);
        $perPage = 25;
        $offset = ($page - 1) * $perPage;
        
        $rows = new \Illuminate\Pagination\LengthAwarePaginator(
            $uniqueCountries->slice($offset, $perPage),
            $uniqueCountries->count(),
            $perPage,
            $page,
            ['path' => request()->url(), 'pageName' => 'page']
        );
        
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
            'name' => 'required|max:255|unique:destinations,name,null,null,type,country,deleted_at,null',
            'title' => 'required|max:255',
            'sub_title' => 'required|max:255',
            'filters' => 'required',
            'language' => 'required|max:255'
        ]);

        try {
            DB::beginTransaction();
            $data = $request->only(['language', 'name', 'title', 'sub_title', 'introduction', 'fish_avail_title', 'fish_avail_intro', 'size_limit_title', 'size_limit_intro', 'time_limit_title', 'time_limit_intro', 'faq_title']);
            $data['type'] = 'country';
            $data['slug'] = $this->slug_format($request->name);
            $data['filters'] = json_encode($request->filters);
            $data['content'] = $request->body;
            $data['countrycode'] = $request->countrycode ?? null;
            $data['language'] = $request->language;
            $webp_path = null;

            if($request->has('thumbnailImage')) {
                $webp_path = $this->upload_thumbnail($request->thumbnailImage);
            }

            $data['thumbnail_path'] = $webp_path;
            $country = Destination::create($data);

            // if ($request->has('fish_chart')) {
            //     foreach ($request->fish_chart as $key => $value) {
            //         $value['destination_id'] = $country->id;
            //         $value['language'] = $request->language;
            //         DestinationFishChart::create($value);
            //     }
            // }

            // if ($request->has('fish_size_limit')) {
            //     foreach ($request->fish_size_limit as $key => $value) {
            //         $value['destination_id'] = $country->id;
            //         $value['language'] = $request->language;
            //         DestinationFishSizeLimit::create($value);
            //     }
            // }

            // if ($request->has('fish_time_limit')) {
            //     foreach ($request->fish_time_limit as $key => $value) {
            //         $value['destination_id'] = $country->id;
            //         $value['language'] = $request->language;
            //         DestinationFishTimeLimit::create($value);
            //     }
            // }

            // if ($request->has('faq')) {
            //     foreach ($request->faq as $key => $value) {
            //         $value['destination_id'] = $country->id;
            //         $value['language'] = $request->language;
            //         DestinationFaq::create($value);
            //     }
            // }

            // Translate to other languages
            $this->translate($country, $request);

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
        $row = Destination::with(['faq', 'fish_chart', 'fish_size_limit', 'fish_time_limit'])->find($id);

        if (is_null($row)) {
            return redirect()->back();
        }

        $form = 'Country';
        $route = route('admin.category.country.update', $id);
        $method = 'PUT';
        $language = $row->language;
        $countrycode = $row->countrycode;
        $name = $row->name;
        $thumbnail = $row->getThumbnailPath();
        $title = $row->title;
        $sub_title = $row->sub_title;
        $introduction = $row->introduction;
        $body = $row->content;

        $filter = json_decode($row->filters);

        $place = $filter->place ?? '';
        $placeLat = $filter->placeLat ?? '';
        $placeLng = $filter->placeLng ?? '';
        $country = $filter->country ?? '';
        $city = $filter->city ?? '';
        $region = $filter->region ?? '';

        $fish_chart = $row->fish_chart;
        $fish_avail_title = $row->fish_avail_title;
        $fish_avail_intro = $row->fish_avail_intro;

        $fish_size_limit = $row->fish_size_limit;
        $size_limit_title = $row->size_limit_title;
        $size_limit_intro = $row->size_limit_intro;

        $fish_time_limit = $row->fish_time_limit;
        $time_limit_title = $row->time_limit_title;
        $time_limit_intro = $row->time_limit_intro;

        $faq = $row->faq;
        $faq_title = $row->faq_title;

        $data = compact('form', 'route', 'method', 'language', 'countrycode', 'name', 'thumbnail', 'title', 'sub_title', 'introduction', 'body', 'place', 'placeLat', 'placeLng', 'country', 
            'fish_chart', 'fish_avail_title', 'fish_avail_intro', 
            'fish_size_limit', 'size_limit_title', 'size_limit_intro', 
            'fish_time_limit', 'time_limit_title', 'time_limit_intro', 
            'faq', 'faq_title', 'city', 'region'
        );

        return view('admin.pages.category.form', $data);
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'name' => 'required|max:255|unique:destinations,name,'.$id.',id,type,country,deleted_at,null',
            'title' => 'required|max:255',
            'sub_title' => 'required|max:255',
            'filters' => 'required',
            'language' => 'required|max:255'
        ]);

        try {
            DB::beginTransaction();
            $data = $request->only(['name', 'title', 'sub_title', 'introduction', 'fish_avail_title', 'fish_avail_intro', 'size_limit_title', 'size_limit_intro', 'time_limit_title', 'time_limit_intro', 'faq_title']);
            $data['slug'] = $this->slug_format($request->name);
            $data['filters'] = json_encode($request->filters);
            $data['content'] = $request->body;
            $data['countrycode'] = $request->countrycode ?? null;
            $data['language'] = $request->language;

            if($request->has('thumbnailImage')) {
                $webp_path = $this->upload_thumbnail($request->thumbnailImage);
                $data['thumbnail_path'] = $webp_path;
            }

            // Check if we already have this country in this language
            $existingCountry = Destination::where('id', '!=', $id)
                ->where('name', $request->name)
                ->where('language', $request->language)
                ->where('type', 'country')
                ->first();

            if ($existingCountry) {
                // Update existing record for this language
                Destination::whereId($existingCountry->id)->update($data);
                $countryId = $existingCountry->id;
            } else {
                // Create new record for this language or update existing one
                $country = Destination::where('id', $id)
                    ->where('language', $request->language)
                    ->first();
                    
                if ($country) {
                    Destination::whereId($id)->update($data);
                    $countryId = $id;
                } else {
                    // Create a new record for this language
                    $data['type'] = 'country';
                    $countryId = Destination::create($data)->id;
                }
            }

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
            
            // Delete related records first
            DestinationFaq::where('destination_id', $id)->delete();
            DestinationFishChart::where('destination_id', $id)->delete();
            DestinationFishSizeLimit::where('destination_id', $id)->delete();
            DestinationFishTimeLimit::where('destination_id', $id)->delete();
            
            // Delete the destination
            Destination::whereId($id)->delete();
            
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

                $translatedTexts = TranslationHelper::simpleBatchTranslate(
                    $texts,
                    $toLanguage,
                    $data->language
                );

                $forTranslatedData = $translatedTexts;
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
        $country = Destination::with(['faq', 'fish_chart', 'fish_size_limit', 'fish_time_limit'])
            ->where('id', $id)
            ->orWhere(function($query) use ($id) {
                $original = Destination::find($id);
                if ($original) {
                    $query->where('name', $original->name)
                          ->where('type', 'country');
                }
            })
            ->where('language', request('language', 'en'))
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