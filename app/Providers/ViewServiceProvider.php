<?php

namespace App\Providers;

use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;

use App\Models\Guiding;
use App\Models\Water;
use App\Models\Target;
use App\Models\Method;
use App\Models\FishingFrom;
use App\Models\FishingEquipment;
use App\Models\Rating;
use App\Models\Thread;
use App\Models\Faq;
use App\Models\PageAttribute;


class ViewServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        //
    }

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
  public function boot()
    {
        // Using Closure based composers...
        View::composer('*', function ($view) {
            $view->with('authUser', Auth::user());
        });

        View::composer('*', function ($view) {
            $prefix = 'blog';

            if(app()->getLocale() == 'de'){
                $prefix = 'blogde';
            }

            $view->with('blogPrefix', $prefix);
        });

        View::composer('*', function ($view) {
            $mylocale = app()->getLocale();
            $view->with('myCurrentLocale', $mylocale);
        });


        View::composer('*', function ($view) {
            $authUser = auth()->user(); // Get the authenticated user

            if($authUser && $authUser->is_guide){
                $bookings = collect();

                if ($authUser->guidings) {
                    $bookings = $authUser->guidings->flatMap(function ($guiding) {
                        return $guiding->bookings;
                    })->sortByDesc('created_at');
                }

                $view->with('guideBookings', $bookings);


            }
        });

        View::composer('*', function ($view) {
            $allguidings = \Cache::remember('all_guidings', now()->addHours(1), function () {
                return Guiding::orderBy('title','asc')->get();
            });
            $view->with('allguidings', $allguidings);
        });

        View::composer('*', function ($view) {
            $allwaters = Cache::remember('all_waters', now()->addHours(1), function () {
                return Water::orderBy(app()->getLocale() == 'en' ? 'name_en' : 'name', 'asc')->get();
            });
            $view->with('allwaters', $allwaters);
        });

        View::composer('*', function ($view) {
            $alltargets = Cache::remember('all_targets', now()->addHours(1), function () {
                return Target::orderBy(app()->getLocale() == 'en' ? 'name_en' : 'name', 'asc')->get();
            });
            $view->with('alltargets', $alltargets);
        });

        View::composer('*', function ($view) {
            $allmethods = Cache::remember('all_methods', now()->addHours(1), function () {
                return Method::orderBy(app()->getLocale() == 'en' ? 'name_en' : 'name', 'asc')->get();
            });
            $view->with('allmethods', $allmethods);
        });

        View::composer('*', function ($view) {
            $allfishingfrom = Cache::remember('all_fishingFrom', now()->addHours(1), function () {
                return FishingFrom::orderBy(app()->getLocale() == 'en' ? 'name_en' : 'name', 'asc')->get();
            });
            $view->with('allfishingfrom', $allfishingfrom );
        });

        View::composer('*', function ($view) {
            $FishingEquipment = Cache::remember('all_FishingEquipment', now()->addHours(1), function () {
                return FishingEquipment::orderBy(app()->getLocale() == 'en' ? 'name_en' : 'name', 'asc')->get();
            });
            $view->with('allfishingequipment', $FishingEquipment );
        });

        View::composer('*', function ($view) {
            $activeFishing = Cache::remember('active_fishing', now()->addHours(1), function () {
                return Guiding::whereHas('fishingTypes',function($query){
                    $query->where('id',1);
                })
                ->where('status', 1)
                ->inRandomOrder('123')
                ->limit(6)
                ->get();
            });
            $view->with('activeFishing', $activeFishing);
        });

        View::composer('*', function ($view) {
            $seaFishing = Cache::remember('sea_fishing', now()->addHours(1), function () {
                return Guiding::whereHas('guidingWaters', function ($query) {
                    $query->where('water_id', 2);
                })
                ->where('status', 1)
                ->inRandomOrder()
                ->limit(6)
                ->get();
            });
            $view->with('seaFishing', $seaFishing);
        });


        View::composer('*', function ($view) {
            $boatFishing = Cache::remember('boat_fishing', now()->addHours(1), function () {
                return Guiding::whereHas('guidingMethods', function ($query) {
                    $query->where('method_id', 3);
                })
                ->where('status', 1)
                ->inRandomOrder()
                ->limit(6)
                ->get();
            });
            $view->with('boatFishing', $boatFishing);
        });

        View::composer('*', function ($view) {
            $familyAdventures = Cache::remember('family_adventures', now()->addHours(1), function () {
                return Guiding::where('max_guests','>=', 4)
                ->where('status', 1)
                ->inRandomOrder()
                ->limit(6)
                ->get();
            });
            $view->with('familyAdventures', $familyAdventures);
        });

        View::composer('*', function ($view) {
            $flyshings = Cache::remember('flyshings', now()->addHours(1), function () {
                return Guiding::whereHas('guidingMethods', function ($query) {
                    $query->where('method_id', 4);
                })
                ->where('status', 1)
                ->inRandomOrder()
                ->limit(6)
                ->get();
            });
            $view->with('flyshings', $flyshings);
        });


        View::composer('*', function ($view) {
            $shoreFishings = Cache::remember('shore_fishings', now()->addHours(1), function () {
                return Guiding::whereHas('fishingFrom', function ($query) {
                    $query->where('id', 2);
                })
                ->where('status', 1)
                ->inRandomOrder()
                ->limit(6)
                ->get();
            });
            $view->with('shoreFishings', $shoreFishings);
        });

        View::composer('*', function ($view) {
            $multidayfishings = Cache::remember('multiday_fishings', now()->addHours(1), function () {
                return Guiding::where('duration','>=', 24)
                ->where('status', 1)
                ->inRandomOrder()
                ->limit(6)
                ->get();
            });
            $view->with('multidayfishings', $multidayfishings);
        });

        View::composer('*', function ($view) {
            $ratedGuidings = Cache::remember('rated_guidings', now()->addMinutes(30), function () {
                $filterRated = Rating::where('rating','>',1)->groupBy('guide_id')->pluck('guide_id');
                return Guiding::whereIn('user_id', $filterRated)->limit(6)->get();
            });
            $view->with('ratedGuidings', $ratedGuidings);
        });

        View::composer('*', function ($view) {
            $latestThreads = Cache::remember('latest_threads_' . app()->getLocale(), now()->addMinutes(15), function () {
                return Thread::where('language', app()->getLocale())->limit(3)->latest()->get();
            });
            $view->with('latestThreads', $latestThreads);
        });

        View::composer('*', function ($view) {
            $bookedGuidings = Cache::remember('booked_guidings', now()->addMinutes(30), function () {
                return Guiding::withCount('bookings')
                ->where('status', 1)
                ->orderBy('bookings_count', 'desc')
                ->limit(8)
                ->get();
            });
            $view->with('bookedGuidings', $bookedGuidings);
        });

        View::composer('*', function ($view) {
            $newGuidings = Cache::remember('new_guidings', now()->addMinutes(15), function () {
                return Guiding::where('status', 1)
                    ->orderBy('created_at', 'desc')
                    ->limit(8)
                    ->get();
            });
            $view->with('newGuidings', $newGuidings);
        });

        View::composer('*', function ($view) {
            $faqs = Cache::remember('faqs', now()->addHours(24), function () {
                return Faq::all();
            });
            $view->with('faqs', $faqs);
        });

        View::composer('*', function ($view) {
            $locale = app()->getLocale();
            $host = request()->getHost();
            $path = request()->path();
            $cacheKey = "page_attributes_{$host}_{$path}";
            
            $attributes = Cache::remember($cacheKey, now()->addHours(24), function () use ($host, $path) {
                return PageAttribute::where('domain', '=', $host)
                    ->where('uri', $path)
                    ->get();
            });
            
            $view->with('attributes', $attributes);
        });




    }
}
