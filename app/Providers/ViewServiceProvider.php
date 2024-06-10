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
            $allguidings = Guiding::orderBy('title','asc')->get();
            $view->with('allguidings',$allguidings);
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
            $activeFishing = Guiding::whereHas('fishingTypes',function($query){
                $query->where('id',1);
            })
            ->where('status', 1)
            ->inRandomOrder('123')
            ->limit(6)
            ->get();

            $view->with('activeFishing', $activeFishing );
        });

        View::composer('*', function ($view) {
            $seaFishing = Guiding::whereHas('guidingWaters', function ($query) {
                $query->where('water_id', 2);
            })
            ->where('status', 1)
            ->inRandomOrder()
            ->limit(6)
            ->get();

            $view->with('seaFishing', $seaFishing );
        });


        View::composer('*', function ($view) {
            $boatFishing = Guiding::whereHas('guidingMethods', function ($query) {
                $query->where('method_id', 3);
            })
            ->where('status', 1)
            ->inRandomOrder()
            ->limit(6)
            ->get();

            $view->with('boatFishing', $boatFishing );
        });

        View::composer('*', function ($view) {
            $familyAdventures = Guiding::where('max_guests','>=', 4)
            ->where('status', 1)
            ->inRandomOrder()
            ->limit(6)
            ->get();

            $view->with('familyAdventures', $familyAdventures);
        });

        View::composer('*', function ($view) {
            $flyshings = Guiding::whereHas('guidingMethods', function ($query) {
                $query->where('method_id', 4);
            })
            ->where('status', 1)
            ->inRandomOrder()
            ->limit(6)
            ->get();

            $view->with('flyshings', $flyshings);
        });


        View::composer('*', function ($view) {
            $shoreFishings = Guiding::whereHas('fishingFrom', function ($query) {
                $query->where('id', 2);
            })
            ->where('status', 1)
            ->inRandomOrder()
            ->limit(6)
            ->get();

            $view->with('shoreFishings', $shoreFishings );
        });

        View::composer('*', function ($view) {
            $multidayfishings = Guiding::where('duration','>=', 24)
            ->where('status', 1)
            ->inRandomOrder()
            ->limit(6)
            ->get();

            $view->with('multidayfishings', $multidayfishings);
        });

        View::composer('*', function ($view) {
            $filterRated = Rating::where('rating','>',1)->groupBy('guide_id')->pluck('guide_id');

            $ratedGuidings = Guiding::whereIn('user_id', $filterRated)->limit(6)
            ->get();

            $view->with('ratedGuidings', $ratedGuidings);
        });

        View::composer('*', function ($view) {
            $latestThreads = Thread::where('language', app()->getLocale())->limit(3)->latest()->get();
            $view->with('latestThreads', $latestThreads);
        });

        View::composer('*', function ($view) {
            $bookedGuidings = Guiding::withCount('bookings')
            ->where('status', 1)
            ->orderBy('bookings_count', 'desc')
            ->limit(8)
            ->get();
            $view->with('bookedGuidings', $bookedGuidings);
        });

        View::composer('*', function ($view) {
            $newGuidings = Guiding::where('status', 1)->orderBy('created_at', 'desc')->where('status', 1)->limit(8)->get();

            $view->with('newGuidings', $newGuidings);
        });

        View::composer('*', function ($view) {
            $faqs = Faq::all();
            $view->with('faqs', $faqs);
        });

        View::composer('*', function ($view) {
            $locale = app()->getLocale();
            $host = request()->getHost();
            if($locale == 'en'){
                $url = env('APP_URL').'/'.request()->path();
            }else{
                $url = env('APP_URL').'/'.request()->path();
            }

            $attributes = PageAttribute::where('domain','=',$host)->where('uri',request()->path())->get();


                $view->with('attributes', $attributes);


        });




    }
}
