<?php

namespace App\Providers;

use App\Http\Resources\EventResource;
use App\Services\AdminNotificationService;
use App\Services\Asset;
use App\Services\GuidingService;
use App\Services\LanguageService;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Str;

class AppServiceProvider extends ServiceProvider
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
        Paginator::useBootstrap();
        EventResource::withoutWrapping();


        $this->app->singleton('language', function(){
            return new LanguageService;
        });

        $this->app->singleton('asset', function(){
            return new Asset;
        });

        $this->app->singleton('guiding', function () {
            return new GuidingService;
        });

        $this->app->singleton(AdminNotificationService::class, function () {
            return new AdminNotificationService();
        });

        View::composer(
            [
                'admin.layouts.partials.header',
                'admin.layouts.partials.sticky-sidebar',
            ],
            function ($view) {
                /** @var AdminNotificationService $service */
                $service = app(AdminNotificationService::class);

                $view->with('adminNotificationCount', $service->unreadCount());
                $view->with('adminNotifications', $service->latestUnread());

                $name = Route::currentRouteName();
                $breadcrumbTitle = 'Dashboard';
                if ($name && Str::startsWith($name, 'admin.')) {
                    $parts = explode('.', $name);
                    array_shift($parts);
                    if (count($parts) > 0 && end($parts) === 'index') {
                        array_pop($parts);
                    }
                    if (count($parts) > 0) {
                        $breadcrumbTitle = Str::title(str_replace(['-', '_'], ' ', implode(' ', $parts)));
                    }
                }
                $view->with('adminBreadcrumbTitle', $breadcrumbTitle);
            }
        );

    }
}
