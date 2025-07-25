<?php

namespace App\Http\Controllers\Admin\Category;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\Blog\StoreCategoryRequest;
use App\Http\Requests\Admin\Blog\UpdateCategoryRequest;
use App\Models\Destination;
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

class AdminCategoryRegionController extends Controller
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
        $rows = Destination::whereType('region')->paginate(25);
        $data = compact('rows');
        return view('admin.pages.category.region', $data);
    }

    public function create()
    {
        $form = 'Region';
        $route = route('admin.category.region.store');
        $method = '';
        $language = old('language');
        $country_id = old('country_id');
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

        $countries = Destination::whereType('country')->orderBy('name', 'ASC')->get();

        $data = compact('form', 'route', 'method', 'language', 'country_id', 'name', 'thumbnail', 'title', 'sub_title', 'introduction', 'body', 'place', 'placeLat', 'placeLng', 'country', 
            'fish_chart', 'fish_avail_title', 'fish_avail_intro', 
            'fish_size_limit', 'size_limit_title', 'size_limit_intro', 
            'fish_time_limit', 'time_limit_title', 'time_limit_intro', 
            'faq', 'faq_title', 'city', 'region',
            'countries');

        return view('admin.pages.category.form', $data);
    }

    public function store(Request $request)
    {
        $request->validate([
            'country_id' => [ 'required' ],
            'name' => [
                'required',
                'max:255',
                Rule::unique('destinations')->where('name', $request->name)->where('type', 'region')->where('country_id', $request->country_id)->where('deleted_at', null)
            ],
            'title' => [ 'required', 'max:255' ],
            'sub_title' => [ 'required', 'max:255' ],
            'filters' => [ 'required' ],
        ]);

        try {
            DB::beginTransaction();
            $data = $request->only(['language', 'country_id', 'name', 'title', 'sub_title', 'introduction', 'fish_avail_title', 'fish_avail_intro', 'size_limit_title', 'size_limit_intro', 'time_limit_title', 'time_limit_intro', 'faq_title']);
            $data['type'] = 'region';
            $data['filters'] = json_encode($request->filters);
            $data['content'] = $request->body;
            $data['slug'] = $this->slug_format($request->name);
            $webp_path = null;

            if($request->has('thumbnailImage')) {
                $webp_path = $this->upload_thumbnail($request->thumbnailImage);
            }

            $data['thumbnail_path'] = $webp_path;
            $country = Destination::create($data);

            $this->translate($country, $request);

            DB::commit();

            return redirect()->back()->with('success', 'Region Successfully Added!');
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

        $form = 'Region';
        $route = route('admin.category.region.update', $id);
        $method = 'PUT';
        $language = $row->language;
        $country_id = $row->country_id;
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

        $countries = Destination::whereType('country')->orderBy('name', 'ASC')->get();

        $data = compact('form', 'route', 'method', 'language', 'country_id', 'name', 'thumbnail', 'title', 'sub_title', 'introduction', 'body', 'place', 'placeLat', 'placeLng', 'country', 
            'fish_chart', 'fish_avail_title', 'fish_avail_intro', 
            'fish_size_limit', 'size_limit_title', 'size_limit_intro', 
            'fish_time_limit', 'time_limit_title', 'time_limit_intro', 
            'faq', 'faq_title', 'city', 'region',
            'countries');

        return view('admin.pages.category.form', $data);
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'country_id' => [ 'required' ],
            'name' => [
                'required',
                'max:255',
                Rule::unique('destinations')->where('name', $request->name)->where('type', 'region')->where('country_id', $request->country_id)->where('deleted_at', null)->ignore($id)
            ],
            'title' => [ 'required', 'max:255' ],
            'sub_title' => [ 'required', 'max:255' ],
            'filters' => [ 'required' ]
        ]);

        try {
            DB::beginTransaction();
            $data = $request->only(['language', 'country_id', 'name', 'title', 'sub_title', 'introduction', 'fish_avail_title', 'fish_avail_intro', 'size_limit_title', 'size_limit_intro', 'time_limit_title', 'time_limit_intro', 'faq_title']);
            $data['filters'] = json_encode($request->filters);
            $data['content'] = $request->body;
            $data['slug'] = $this->slug_format($request->name);

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

            return redirect()->back()->with('success', 'Region Successfully Updated!');
        } catch (Exception $e) {
            DB::rollBack();
            return redirect()->back()->withErrors(['message' => 'Ooops Something went wrong. Please reload the page.']);
        } catch (\Illuminate\Database\QueryException $e) {
            DB::rollBack();
            return redirect()->back()->withErrors(['message' => 'Ooops Something went wrong. Please reload the page.']);
        }
    }

    public function show(Destination $destination)
    {
        //
    }

    public function destroy($id)
    {
        Destination::whereId($id)->delete();

        return redirect()->back()->with('success', 'Region Successfully Deleted!');
    }

    public function upload_thumbnail($thumbnailImage)
    {
        $thumbnail_path = $thumbnailImage->store('public');
        $imagePath = Storage::disk()->path($thumbnail_path);

        $image = Image::make($imagePath);
        $webpImageName = pathinfo($thumbnail_path, PATHINFO_FILENAME) . '.webp';
        $webpImage = $image->encode('webp', 75);

        $webp_path = 'blog/region/';

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