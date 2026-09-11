<?php

namespace App\Http\Livewire;

use App\Models\Guiding;
use App\Models\Target;
use App\Models\Method;
use App\Models\Water;
use App\Models\Inclussion;
use Livewire\Component;
use Livewire\WithPagination;
use Illuminate\Support\Facades\Session;
use App\Services\GuidingFilterService;
use App\Services\ImageOptimizationService;
use Illuminate\Http\Request;

class GuideThread extends Component
{
    use WithPagination;

    private const BASE_PER_PAGE = 5;

    public $filt;
    public $perPage = self::BASE_PER_PAGE;

    // Filter properties
    public $sortBy;
    public $targetFish = [];
    public $methods = [];
    public $waterTypes = [];
    public $durationTypes = [];
    public $priceMin;
    public $priceMax;
    public $numPersons;

    // Location properties
    public $country;
    public $city;
    public $region;
    public $placeLat;
    public $placeLng;
    public $radius;

    protected $listeners = ['load_more', 'updateFilters'];

    protected $filterService;
    protected $imageOptimizationService;

    public function mount()
    {
        $this->imageOptimizationService = app(ImageOptimizationService::class);

        // Initialize filters from the $filt property if provided
        if ($this->filt) {
            $filter = json_decode($this->filt, true);
            if ($filter) {
                $this->country = $filter['country'] ?? null;
                $this->targetFish = $filter['target_fish'] ?? [];
                $this->methods = $filter['methods'] ?? [];
            }
        }
    }

    private function getFilterService()
    {
        if (!$this->filterService) {
            $this->filterService = new GuidingFilterService();
        }
        return $this->filterService;
    }

    public function render()
    {
        $randomSeed = Session::get('random_seed', rand());
        Session::put('random_seed', $randomSeed);

        $guidings = $this->buildFilteredQuery()->paginate($this->perPage);

        return view('livewire.guide-thread', [
            'guidings' => $guidings,
            'targetsMap' => $this->buildLookupMap($guidings->items(), 'target_fish', Target::class),
            'inclussionsMap' => $this->buildLookupMap($guidings->items(), 'inclusions', Inclussion::class),
        ]);
    }

    private function buildFilteredQuery()
    {
        $query = $this->buildFilteredBaseQuery();
        $this->applySorting($query);

        return $query;
    }

    private function buildFilteredBaseQuery()
    {
        if ($this->hasActiveCheckboxFilters()) {
            $filterData = $this->prepareFilterData();
            $checkboxFilteredIds = $this->getFilterService()->getFilteredGuidingIds($filterData);

            $query = Guiding::with(['boatType', 'user.reviews'])->where('status', 1);

            if (empty($checkboxFilteredIds)) {
                return $query->whereRaw('1 = 0');
            }

            $query->whereIn('id', $checkboxFilteredIds);
            $this->applyLocationFilter($query);

            return $query;
        }

        $query = Guiding::with(['boatType', 'user.reviews'])->where('status', 1);
        $this->applyLocationFilter($query);
        $this->applyBasicFilters($query);

        return $query;
    }

    /**
     * Batch id -> model lookup for the guidings on the current page, so the view's
     * getTargetFishNames()/getInclusionNames() calls don't fall back to a find()-per-item query.
     */
    private function buildLookupMap(array $guidings, string $jsonField, string $modelClass)
    {
        $ids = collect($guidings)
            ->flatMap(fn ($g) => json_decode($g->{$jsonField}, true) ?: [])
            ->unique()
            ->filter()
            ->values();

        return $ids->isNotEmpty() ? $modelClass::whereIn('id', $ids)->get()->keyBy('id') : collect();
    }

    private function applyLocationFilter($query)
    {
        if (!empty($this->country)) {
            $filterTranslated = getLocationDetailsGoogle(null, $this->country);
            if (isset($filterTranslated['country'])) {
                $query->where('country', $filterTranslated['country']);
            }
        }
    }

    private function applyBasicFilters($query)
    {
        // Target fish filter
        if (!empty($this->targetFish)) {
            $query->whereHas('guidingTargets', function ($q) {
                $q->whereIn('target_id', $this->targetFish);
            });
        }

        // Methods filter (fixed the bug from original code)
        if (!empty($this->methods)) {
            $query->whereHas('guidingMethods', function ($q) {
                $q->whereIn('method_id', $this->methods);
            });
        }

        // Water types filter
        if (!empty($this->waterTypes)) {
            $query->whereHas('guidingWaters', function ($q) {
                $q->whereIn('water_id', $this->waterTypes);
            });
        }

        // Duration filter
        if (!empty($this->durationTypes)) {
            $query->whereIn('duration_type', $this->durationTypes);
        }

        // Guest number filter
        if (!empty($this->numPersons)) {
            $query->where('max_guests', '>=', $this->numPersons);
        }

        // Price filter
        if (!empty($this->priceMin) || !empty($this->priceMax)) {
            if (!empty($this->priceMin)) {
                $query->where('price', '>=', $this->priceMin);
            }
            if (!empty($this->priceMax)) {
                $query->where('price', '<=', $this->priceMax);
            }
        }
    }

    private function applySorting($query)
    {
        $randomSeed = Session::get('random_seed', rand());
        
        switch ($this->sortBy) {
            case 'newest':
                $query->orderBy('created_at', 'desc');
                break;
            case 'price-asc':
                $query->orderBy('price', 'asc');
                break;
            case 'price-desc':
                $query->orderBy('price', 'desc');
                break;
            case 'long-duration':
                $query->orderBy('duration', 'desc');
                break;
            case 'short-duration':
                $query->orderBy('duration', 'asc');
                break;
            default:
                // Default random ordering for initial load
                $query->orderByRaw('RAND(?)', [(int) $randomSeed]);
        }
    }

    private function hasActiveCheckboxFilters()
    {
        return !empty($this->targetFish) || 
               !empty($this->methods) || 
               !empty($this->waterTypes) || 
               !empty($this->durationTypes) || 
               !empty($this->numPersons) ||
               !empty($this->priceMin) || 
               !empty($this->priceMax);
    }

    private function prepareFilterData()
    {
        $request = new Request();
        
        if (!empty($this->targetFish)) {
            $request->merge(['target_fish' => $this->targetFish]);
        }
        if (!empty($this->methods)) {
            $request->merge(['methods' => $this->methods]);
        }
        if (!empty($this->waterTypes)) {
            $request->merge(['water' => $this->waterTypes]);
        }
        if (!empty($this->durationTypes)) {
            $request->merge(['duration_types' => $this->durationTypes]);
        }
        if (!empty($this->numPersons)) {
            $request->merge(['num_persons' => $this->numPersons]);
        }
        if (!empty($this->priceMin)) {
            $request->merge(['price_min' => $this->priceMin]);
        }
        if (!empty($this->priceMax)) {
            $request->merge(['price_max' => $this->priceMax]);
        }

        return $request;
    }

    public function loadMore()
    {
        $this->perPage += self::BASE_PER_PAGE;
    }

    /**
     * A changed filter/sort must start back at a fresh first page — resetPage()
     * alone only resets Livewire's page-number pointer, not the window size
     * "Load More" grew, so without this a filter change kept showing however
     * many rows had accumulated under the previous filter.
     */
    private function resetPagination(): void
    {
        $this->perPage = self::BASE_PER_PAGE;
        $this->resetPage();
    }

    public function updateFilters($filters)
    {
        // Update component properties from external filter updates
        $this->targetFish = $filters['target_fish'] ?? [];
        $this->methods = $filters['methods'] ?? [];
        $this->waterTypes = $filters['water'] ?? [];
        $this->durationTypes = $filters['duration_types'] ?? [];
        $this->numPersons = $filters['num_persons'] ?? null;
        $this->priceMin = $filters['price_min'] ?? null;
        $this->priceMax = $filters['price_max'] ?? null;
        $this->sortBy = $filters['sortby'] ?? null;
        $this->country = $filters['country'] ?? null;
        $this->city = $filters['city'] ?? null;
        $this->region = $filters['region'] ?? null;
        $this->placeLat = $filters['placeLat'] ?? null;
        $this->placeLng = $filters['placeLng'] ?? null;
        $this->radius = $filters['radius'] ?? null;
        
        // Reset pagination when filters change
        $this->resetPagination();
    }

    public function updatedSortBy()
    {
        $this->resetPagination();
    }

    public function updatedTargetFish()
    {
        $this->resetPagination();
    }

    public function updatedMethods()
    {
        $this->resetPagination();  
    }

    public function updatedWaterTypes()
    {
        $this->resetPagination();
    }

    public function updatedDurationTypes()
    {
        $this->resetPagination();
    }

    public function updatedNumPersons()
    {
        $this->resetPagination();
    }

    public function updatedPriceMin()
    {
        $this->resetPagination();
    }

    public function updatedPriceMax()
    {
        $this->resetPagination();
    }

    public function removeFilter($filterType, $filterId)
    {
        switch ($filterType) {
            case 'targetFish':
                $this->targetFish = array_diff($this->targetFish, [$filterId]);
                break;
            case 'methods':
                $this->methods = array_diff($this->methods, [$filterId]);
                break;
            case 'waterTypes':
                $this->waterTypes = array_diff($this->waterTypes, [$filterId]);
                break;
            case 'durationTypes':
                $this->durationTypes = array_diff($this->durationTypes, [$filterId]);
                break;
            case 'numPersons':
                $this->numPersons = null;
                break;
            case 'price':
                $this->priceMin = null;
                $this->priceMax = null;
                break;
        }
        
        $this->resetPagination();
    }

    public function resetFilters()
    {
        $this->targetFish = [];
        $this->methods = [];
        $this->waterTypes = [];
        $this->durationTypes = [];
        $this->numPersons = null;
        $this->priceMin = null;
        $this->priceMax = null;
        $this->sortBy = null;
        
        $this->resetPagination();
    }

    /**
     * Get filter options for the template
     */
    public function getFilterOptionsProperty()
    {
        $locale = app()->getLocale();
        $nameField = $locale == 'en' ? 'name_en' : 'name';
        
        return [
            'targets' => Target::select('id', 'name', 'name_en')
                ->orderBy($nameField)
                ->get()
                ->map(function($target) use ($nameField) {
                    return [
                        'id' => $target->id,
                        'name' => $target->$nameField
                    ];
                }),
            'methods' => Method::select('id', 'name', 'name_en')
                ->orderBy($nameField)
                ->get()
                ->map(function($method) use ($nameField) {
                    return [
                        'id' => $method->id,
                        'name' => $method->$nameField
                    ];
                }),
            'waters' => Water::select('id', 'name', 'name_en')
                ->orderBy($nameField)
                ->get()
                ->map(function($water) use ($nameField) {
                    return [
                        'id' => $water->id,
                        'name' => $water->$nameField
                    ];
                })
        ];
    }

    /**
     * Get count of guidings for a specific filter
     */
    public function getFilterCount($filterType, $filterId)
    {
        $query = Guiding::where('status', 1);
        
        // Apply location filter if present
        if (!empty($this->country)) {
            $query->where('country', $this->country);
        }
        
        switch ($filterType) {
            case 'targets':
                return $query->whereHas('guidingTargets', function ($q) use ($filterId) {
                    $q->where('target_id', $filterId);
                })->count();
                
            case 'methods':
                return $query->whereHas('guidingMethods', function ($q) use ($filterId) {
                    $q->where('method_id', $filterId);
                })->count();
                
            case 'waters':
                return $query->whereHas('guidingWaters', function ($q) use ($filterId) {
                    $q->where('water_id', $filterId);
                })->count();
                
            case 'duration':
                return $query->where('duration_type', $filterId)->count();
                
            case 'persons':
                return $query->where('max_guests', '>=', $filterId)->count();
                
            default:
                return 0;
        }
    }
}
