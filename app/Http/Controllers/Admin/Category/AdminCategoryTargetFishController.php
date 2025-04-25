<?php

namespace App\Http\Controllers\Admin\Category;

use App\Http\Controllers\Controller;
use App\Models\Faq;
use App\Models\Target;
use App\Models\Language;
use App\Models\CategoryPage;
use DB;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Intervention\Image\Facades\Image;
use Exception;
use Illuminate\Support\Facades\Log;

class AdminCategoryTargetFishController extends Controller
{
    protected $language;

    public function __construct()
    {
        $this->language = [
            'en',
            'de'
        ];
    }

    public function index()
    {
        $rows = Target::with(['categoryPage' => function($query) {
                $query->where('type', 'Targets')
                    ->with(['language' => function($q) {
                        $q->select('source_id', 'language')
                          ->orderBy('language');
                    }]);
            }])
            ->paginate(25);

        $rows->getCollection()->transform(function($target) {
            $target->languages = $target->categoryPage 
                ? $target->categoryPage->language->pluck('language')->sort()->values()->toArray()
                : [];
            return $target;
        });
        $data = compact('rows');
        return view('admin.pages.category.target-fish', $data);
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'name' => 'required|max:255|unique:destinations,name,null,null,type,Targets,deleted_at,null',
            'title' => 'required|max:255',
            'sub_title' => 'required|max:255',
            'languageSwitch' => 'required|max:255'
        ]);

        try {
            DB::beginTransaction();
            $data = $request->only(['languageSwitch', 'name', 'title', 'sub_title', 'introduction', 'faq_title']);
            $data['name'] = $request->name;

            if($request->has('thumbnailImage') && ($request->thumbnailImage != null && $request->thumbnailImage != '')) {
                $webp_path = $this->upload_thumbnail($request->thumbnailImage);
                $data['thumbnail_path'] = $webp_path;
            }

            $categoryPage = CategoryPage::where('source_id', $id)->first();
            $isCreate = false;

            if ($categoryPage) {
                $categoryPage->update($data);
                Language::where('source_id', $categoryPage->id)->where('language', $request->languageSwitch)->delete();
            } else {
                $data['type'] = 'Targets';
                $data['source_id'] = $id;
                $data['slug'] = $this->slug_format($request->name);
                $categoryPage = CategoryPage::create($data);
                $isCreate = true;
            }
            
            $language = Language::create(
                [
                    'source_id' => $categoryPage->id,
                    'language' => $request->languageSwitch,
                    'title' => $request->title ?? '',
                    'sub_title' => $request->sub_title ?? '',
                    'introduction' => $request->introduction ?? '',
                    'content' => $request->content ?? '',
                    'faq_title' => $request->faq_title ?? '',
                    'faq' => $request->faq ?? []
                ]
            );
            
            if ($request->has('faq')) {
                Faq::where('page', 'Targets')->where('source_id', $categoryPage->id)->delete();
                foreach ($request->faq as $key => $value) {
                    $valueSave['page'] = 'Targets';
                    $valueSave['language'] = $request->languageSwitch;
                    $valueSave['question'] = $value['question'];
                    $valueSave['answer'] = $value['answer'];
                    $valueSave['source_id'] = $categoryPage->id;
                    Faq::create($valueSave);
                }
                $language->faq = $request->faq;
            }

            if($isCreate){
                $this->translate($language);
            }

            DB::commit();

            return redirect()->back()->with('success', 'Target Successfully Added!');
        } catch (Exception $e) {
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
        $targets = Target::with('categoryPage', 'categoryPage.faq')->find($id);

        if (is_null($targets)) {
            return redirect()->back();
        }

        $row = $targets->categoryPage;
        
        $form = 'Target Fish';
        $route = route('admin.category.target-fish.update', $id);
        $method = 'PUT';
        $language = 'de';
        $name = $targets->name ?? '';
        $thumbnail = $row ? $row->getThumbnailPath() : asset('assets/images/300x300.png');
        $title = '';
        $sub_title = '';
        $introduction = '';
        $content = '';
        $faq_title = '';
        $faq = [];
        
        if ($row) {
            $languageData = $row->language($language);
            if ($languageData) {
                $title = $languageData->title ?? '';
                $sub_title = $languageData->sub_title ?? '';
                $introduction = $languageData->introduction ?? '';
                $content = $languageData->content ?? '';
                $faq_title = $languageData->faq_title ?? '';
                $faq = $row->faq($language) ?? [];
            }
        }
        $allowed_fields = false;

        $data = compact('form', 'route', 'method', 'language', 'name', 'thumbnail', 'title', 'sub_title', 'introduction', 'content', 'faq_title', 'allowed_fields', 'faq');

        return view('admin.pages.category.dynamic-form', $data);
    }

    private function upload_thumbnail($thumbnailImage)
    {
        $thumbnail_path = $thumbnailImage->store('public');
        $imagePath = Storage::disk()->path($thumbnail_path);

        $image = Image::make($imagePath);
        $webpImageName = pathinfo($thumbnail_path, PATHINFO_FILENAME) . '.webp';
        $webpImage = $image->encode('webp', 75);

        $webp_path = 'category/targets/';

        if (!Storage::disk('public_path')->exists($webp_path)) {
            Storage::disk('public_path')->makeDirectory($webp_path);
        }

        $webp_path .= $webpImageName;

        Storage::disk('public_path')->put($webp_path, $webpImage->encoded);
        $webpImage->save(public_path($webp_path));

        return $webp_path;
    }

    private function slug_format($value)
    {
        return str_replace(' ', '-', strtolower($value));
    }

    private function translate($data)
    {
        foreach ($this->language as $language) {
            if ($language !== $data->language) {
                $title = ($data->title && $data->title != '') ? translate($data->title, $language) : '';
                $sub_title = ($data->sub_title && $data->sub_title != '') ? translate($data->sub_title, $language) : '';
                $introduction = ($data->introduction && $data->introduction != '') ? translate($data->introduction, $language) : '';
                $content = ($data->content && $data->content != '') ? translate($data->content, $language) : '';
                $faq_title = ($data->faq_title && $data->faq_title != '') ? translate($data->faq_title, $language) : '';
                
                $languageData = Language::where('source_id', $data->source_id)->where('language', $language)->first();
                if ($languageData) {
                    $languageData->update([
                        'title' => $title,
                        'sub_title' => $sub_title,
                        'introduction' => $introduction,
                        'content' => $content,
                        'faq_title' => $faq_title
                    ]);
                } else {
                    Language::create([
                        'source_id' => $data->source_id,
                        'language' => $language,
                        'title' => $title,
                        'sub_title' => $sub_title,
                        'introduction' => $introduction,
                        'content' => $content,
                        'faq_title' => $faq_title
                    ]);
                }
                
                if ($data->faq) {
                    foreach ($data->faq as $key => $value) {
                        $valueData['language'] = $language;
                        $valueData['page'] = 'Targets';
                        $valueData['source_id'] = $data->source_id;
                        $valueData['question'] = translate($value['question'], $language);
                        $valueData['answer'] = translate($value['answer'], $language);
                        Faq::create($valueData);
                    }
                }
            }
        }
    }

    public function toggleFavorite(Request $request)
    {
        try {
            $target = Target::findOrFail($request->id);
            
            if ($target->categoryPage) {
                $categoryPage = $target->categoryPage;
                $categoryPage->is_favorite = (int)$request->status;
                $categoryPage->save();
            } else {
                $categoryPage = new CategoryPage();
                $categoryPage->name = $target->name;
                $categoryPage->is_favorite = (int)$request->status; 
                $categoryPage->type = 'Targets';
                $categoryPage->target_id = $target->id;
                $categoryPage->save();
            }
            
            return response()->json(['success' => true]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    public function getLanguageData($id)
    {
        $targets = Target::with('categoryPage', 'categoryPage.language', 'categoryPage.faq')->find($id);

        if (is_null($targets)) {
            return response()->json(['error' => 'Target not found'], 404);
        }

        $row = $targets->categoryPage;
        $language = request('language', 'de');
        
        $title = '';
        $sub_title = '';
        $introduction = '';
        $content = '';
        $faq_title = '';
        $faq = [];
        
        if ($row) {
            $languageData = $row->language($language);
            if ($languageData) {
                $title = $languageData->title ?? '';
                $sub_title = $languageData->sub_title ?? '';
                $introduction = $languageData->introduction ?? '';
                $content = $languageData->content ?? '';
                $faq_title = $languageData->faq_title ?? '';
                $faq = $row->faq($language) ?? [];
            }
        }
        
        return response()->json([
            'title' => $title,
            'sub_title' => $sub_title,
            'introduction' => $introduction,
            'content' => $content,
            'faq_title' => $faq_title,
            'faq' => $faq
        ]);
    }
}