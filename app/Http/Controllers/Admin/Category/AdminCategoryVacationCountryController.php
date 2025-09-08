<?php

namespace App\Http\Controllers\Admin\Category;

use App\Http\Controllers\Controller;
use App\Models\Destination;
use App\Models\DestinationFishChart;
use App\Models\DestinationFishSizeLimit;
use App\Models\DestinationFishTimeLimit;
use App\Models\DestinationFaq;
use DB;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Intervention\Image\Facades\Image;
use Exception;
use App\Helpers\TranslationHelper;

class AdminCategoryVacationCountryController extends Controller
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
        $rows = Destination::whereType('vacations')->paginate(25);
        $data = compact('rows');
        return view('admin.pages.category.vacations-country', $data);
    }

    public function create()
    {
        $form = 'Vacation Country';
        $route = route('admin.category.vacation-country.store');
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

        return view('admin.pages.category.vacations-form', $data);
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|max:255|unique:destinations,name,null,null,type,vacations,deleted_at,null',
            'title' => 'required|max:255',
            'sub_title' => 'required|max:255',
            'filters' => 'required'
        ]);

        try {
            DB::beginTransaction();
            $data = $request->only(['language', 'name', 'title', 'sub_title', 'introduction', 'fish_avail_title', 'fish_avail_intro', 'size_limit_title', 'size_limit_intro', 'time_limit_title', 'time_limit_intro', 'faq_title']);
            $data['type'] = 'vacations';
            $data['slug'] = $this->slug_format($request->name);
            $data['filters'] = json_encode($request->filters);
            $data['content'] = $request->body;
            $data['countrycode'] = $request->countrycode ?? null;
            $webp_path = null;

            if($request->has('thumbnailImage')) {
                $webp_path = $this->upload_thumbnail($request->thumbnailImage);
            }

            $data['thumbnail_path'] = $webp_path;
            $country = Destination::create($data);

            if ($request->has('fish_chart')) {
                foreach ($request->fish_chart as $key => $value) {
                    $value['destination_id'] = $country->id;
                    $value['language'] = $request->language;
                    DestinationFishChart::create($value);
                }
            }

            if ($request->has('fish_size_limit')) {
                foreach ($request->fish_size_limit as $key => $value) {
                    $value['destination_id'] = $country->id;
                    $value['language'] = $request->language;
                    DestinationFishSizeLimit::create($value);
                }
            }

            if ($request->has('fish_time_limit')) {
                foreach ($request->fish_time_limit as $key => $value) {
                    $value['destination_id'] = $country->id;
                    $value['language'] = $request->language;
                    DestinationFishTimeLimit::create($value);
                }
            }

            if ($request->has('faq')) {
                foreach ($request->faq as $key => $value) {
                    $value['destination_id'] = $country->id;
                    $value['language'] = $request->language;
                    DestinationFaq::create($value);
                }
            }

            $this->translate($country);

            DB::commit();

            return redirect()->back()->with('success', 'Country Successfully Added!');
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
        $row = Destination::with(['faq', 'fish_chart', 'fish_size_limit', 'fish_time_limit'])->find($id);

        if (is_null($row)) {
            return redirect()->back();
        }

        $form = 'Vacation Country';
        $route = route('admin.category.vacation-country.update', $id);
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

        return view('admin.pages.category.vacations-form', $data);
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'name' => 'required|max:255|unique:destinations,name,'.$id.',id,type,vacations,deleted_at,null',
            'title' => 'required|max:255',
            'sub_title' => 'required|max:255',
            'filters' => 'required'
        ]);

        try {
            DB::beginTransaction();
            $data = $request->only(['language', 'name', 'title', 'sub_title', 'introduction', 'fish_avail_title', 'fish_avail_intro', 'size_limit_title', 'size_limit_intro', 'time_limit_title', 'time_limit_intro', 'faq_title']);
            $data['slug'] = $this->slug_format($request->name);
            $data['filters'] = json_encode($request->filters);
            $data['content'] = $request->body;
            $data['countrycode'] = $request->countrycode ?? null;

            if($request->has('thumbnailImage')) {
                $webp_path = $this->upload_thumbnail($request->thumbnailImage);
                $data['thumbnail_path'] = $webp_path;
            }

            Destination::whereId($id)->update($data);

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

            return redirect()->back()->with('success', 'Country Successfully Updated!');
        } catch (Exception $e) {
            DB::rollBack();
            dd($e);
            return redirect()->back()->withErrors(['message' => 'Ooops Something went wrong. Please reload the page.']);
        } catch (\Illuminate\Database\QueryException $e) {
            DB::rollBack();
            dd($e);
            return redirect()->back()->withErrors(['message' => 'Ooops Something went wrong. Please reload the page.']);
        }
    }

    public function destroy($id)
    {
        Destination::whereId($id)->delete();

        return redirect()->back()->with('success', 'Country Successfully Deleted!');
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

    private function translate($data)
    {
        $sourceLanguage = $data->language;
        
        foreach ($this->language as $language) {
            if ($language !== $sourceLanguage) {
                // Create translated destination
                $translatedData = $data->replicate();
                $translatedData->language = $language;
                
                // Prepare all texts for batch translation
                $textsToTranslate = [
                    'title' => $data->title,
                    'sub_title' => $data->sub_title,
                    'introduction' => $data->introduction,
                    'content' => $data->content,
                    'fish_avail_title' => $data->fish_avail_title,
                    'fish_avail_intro' => $data->fish_avail_intro,
                    'size_limit_title' => $data->size_limit_title,
                    'size_limit_intro' => $data->size_limit_intro,
                    'time_limit_title' => $data->time_limit_title,
                    'time_limit_intro' => $data->time_limit_intro,
                    'faq_title' => $data->faq_title
                ];

                // Perform batch translation
                $translatedTexts = TranslationHelper::batchTranslate(
                    $textsToTranslate,
                    $language,
                    $sourceLanguage
                );

                // Apply translations
                foreach ($translatedTexts as $field => $translation) {
                    $translatedData->$field = $translation;
                }
                
                $translatedData->save();

                // Handle related translations
                $this->translateRelatedData($data, $translatedData, $language, $sourceLanguage);
            }
        }
    }

    private function translateRelatedData($sourceData, $translatedData, $targetLanguage, $sourceLanguage)
    {
        // Translate fish charts
        if ($sourceData->fish_chart->count() > 0) {
            $fishChartTexts = [];
            foreach ($sourceData->fish_chart as $index => $chart) {
                $fishChartTexts["fish_$index"] = $chart->fish;
            }

            $translatedFishChart = TranslationHelper::batchTranslate(
                $fishChartTexts,
                $targetLanguage,
                $sourceLanguage
            );

            foreach ($sourceData->fish_chart as $index => $chart) {
                DestinationFishChart::create([
                    'destination_id' => $translatedData->id,
                    'language' => $targetLanguage,
                    'fish' => $translatedFishChart["fish_$index"] ?? $chart->fish,
                    'jan' => $chart->jan,
                    'feb' => $chart->feb,
                    'mar' => $chart->mar,
                    'apr' => $chart->apr,
                    'may' => $chart->may,
                    'jun' => $chart->jun,
                    'jul' => $chart->jul,
                    'aug' => $chart->aug,
                    'sep' => $chart->sep,
                    'oct' => $chart->oct,
                    'nov' => $chart->nov,
                    'dec' => $chart->dec
                ]);
            }
        }

        // Translate size limits
        if ($sourceData->fish_size_limit->count() > 0) {
            $sizeLimitTexts = [];
            foreach ($sourceData->fish_size_limit as $index => $limit) {
                $sizeLimitTexts["fish_size_$index"] = $limit->fish;
            }

            $translatedSizeLimits = TranslationHelper::batchTranslate(
                $sizeLimitTexts,
                $targetLanguage,
                $sourceLanguage
            );

            foreach ($sourceData->fish_size_limit as $index => $limit) {
                DestinationFishSizeLimit::create([
                    'destination_id' => $translatedData->id,
                    'language' => $targetLanguage,
                    'fish' => $translatedSizeLimits["fish_size_$index"] ?? $limit->fish,
                    'data' => $limit->data
                ]);
            }
        }

        // Translate time limits
        if ($sourceData->fish_time_limit->count() > 0) {
            $timeLimitTexts = [];
            foreach ($sourceData->fish_time_limit as $index => $limit) {
                $timeLimitTexts["fish_time_$index"] = $limit->fish;
            }

            $translatedTimeLimits = TranslationHelper::batchTranslate(
                $timeLimitTexts,
                $targetLanguage,
                $sourceLanguage
            );

            foreach ($sourceData->fish_time_limit as $index => $limit) {
                DestinationFishTimeLimit::create([
                    'destination_id' => $translatedData->id,
                    'language' => $targetLanguage,
                    'fish' => $translatedTimeLimits["fish_time_$index"] ?? $limit->fish,
                    'data' => $limit->data
                ]);
            }
        }

        // Translate FAQs
        if ($sourceData->faq->count() > 0) {
            $faqTexts = [];
            foreach ($sourceData->faq as $index => $faq) {
                $faqTexts["question_$index"] = $faq->question;
                $faqTexts["answer_$index"] = $faq->answer;
            }

            $translatedFaqs = TranslationHelper::batchTranslate(
                $faqTexts,
                $targetLanguage,
                $sourceLanguage
            );

            foreach ($sourceData->faq as $index => $faq) {
                DestinationFaq::create([
                    'destination_id' => $translatedData->id,
                    'language' => $targetLanguage,
                    'question' => $translatedFaqs["question_$index"] ?? $faq->question,
                    'answer' => $translatedFaqs["answer_$index"] ?? $faq->answer
                ]);
            }
        }
    }
}