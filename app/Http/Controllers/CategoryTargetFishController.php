<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\CategoryPage;
use App\Models\Guiding;
use Illuminate\Support\Facades\Session;

class CategoryTargetFishController extends Controller
{
    public function index()
    {
        $language = app()->getLocale();
        $allData = CategoryPage::where('type', 'Targets')
            ->get()
            ->map(function($item) use ($language) {
                $item->language = $item->language($language);
                return $item;
            })
            ->filter(function($item) {
                return $item->language !== null;
            });

        $favories = $allData->filter(function($item) {
            return $item->is_favorite === true || $item->is_favorite === 1;
        });

        $allTargets = $allData->filter(function($item) {
            return $item->is_favorite === false || $item->is_favorite === 0;
        });
        
        $introduction = __('vacations.introduction');
        $title = __('vacations.title');
        $route = 'target-fish.targets';

        $data = compact('favories', 'allTargets', 'introduction', 'title', 'route');
        return view('pages.category.category-index', $data);
    }

    public function targets($slug, Request $request)
    {
        $language = app()->getLocale();
        $row_data = CategoryPage::whereSlug($slug)->whereType('Targets')->with('language')->first();
        $row_data->language = $row_data->language($language);

        if (is_null($row_data)) {
            abort(404);
        }

        $title = 'Target Fish';

        $randomSeed = Session::get('random_seed');
        if (!$randomSeed) {
            $randomSeed = rand();
            Session::put('random_seed', $randomSeed);
        }

        $query = Guiding::where('status', 1);
        
        $query->where(function($query) use ($row_data) {
            $query->whereJsonContains('target_fish', (int)$row_data->source_id);
        });

        // Build title based on filters
        if($request->has('page')){
            $title .= __('vacations.Page') . ' ' . $request->page . ' - ';
        }

        if($request->has('num_guests') && !empty($request->get('num_guests'))){
            $title .= __('vacations.Guest') . ' ' . $request->num_guests . ' | ';
        }
            
        $hasOnlyPageParam = count(array_diff(array_keys($request->all()), ['page'])) === 0;

        // Apply consistent ordering based on sort parameter or default to ID
        if ($request->has('sortby') && !empty($request->get('sortby'))) {
            // Apply sorting based on user selection
            switch ($request->get('sortby')) {
                case 'newest':
                    $query->orderBy('created_at', 'desc');
                    break;
                case 'price-asc':
                    $query->orderBy('id', 'asc');
                    break;
                case 'price-desc':
                    $query->orderBy('id', 'desc');
                    break;
                case 'long-duration':
                    $query->orderBy('id', 'desc');
                    break;
                case 'short-duration':
                    $query->orderBy('id', 'asc');
                    break;
                default:
                    $query->orderBy('id', 'asc');
            }
        } else if ($hasOnlyPageParam) {
            $query->orderByRaw("RAND($randomSeed)");
        } else {
            $query->orderBy('id', 'asc');
        }

        $searchMessage = '';

        $category_total = $query->count();
       
        // Use select distinct on id to ensure no duplicates
        $query->select('guidings.*')->distinct('id');

        $allGuidings = $query->with(['target_fish', 'methods', 'water_types', 'boatType'])->get();

        // Apply pagination - use a smaller number like 5 for testing
        $guides = $query->paginate(10)->appends(request()->except('page'));

        $data = compact('row_data', 'guides', 'title', 'category_total', 'allGuidings');

        return view('pages.category.category-show', $data);
    }
}
