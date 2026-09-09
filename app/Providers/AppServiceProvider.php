<?php

namespace App\Providers;

use App\Contracts\Assistant\AssistantUiEnvelopeParserInterface;
use App\Contracts\Assistant\AssistantUiPayloadNormalizerInterface;
use App\Contracts\Assistant\AssistantVisibleReplySanitizerInterface;
use App\Contracts\Assistant\LLMClientInterface;
use App\Http\Resources\EventResource;
use App\Jobs\TranslateListingJob;
use App\Models\Accommodation;
use App\Models\Camp;
use App\Models\Guiding;
use App\Models\RentalBoat;
use App\Models\SpecialOffer;
use App\Models\Trip;
use App\Models\Vacation;
use App\Services\Assistant\AssistantUiEnvelopeParser;
use App\Services\Assistant\AssistantUiPayloadNormalizer;
use App\Services\Assistant\AssistantVisibleReplySanitizer;
use App\Services\Assistant\BalancedJsonObjectExtractor;
use App\Services\Assistant\GroqHttpClient;
use App\Services\Assistant\UnavailableLLMClient;
use App\Services\AdminNotificationService;
use App\Services\Asset;
use App\Services\GuidingService;
use App\Services\LanguageService;
use App\Services\Translation\ListingTranslationService;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Facades\Cache;
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
        $this->app->bind(LLMClientInterface::class, function () {
            if (!config('booking_assistant.enabled')) {
                return new UnavailableLLMClient();
            }

            $driver = (string) config('booking_assistant.driver', 'groq');
            $groqKey = (string) config('booking_assistant.providers.groq.api_key', '');

            if ($driver === 'groq' && $groqKey !== '') {
                return new GroqHttpClient();
            }

            return new UnavailableLLMClient();
        });

        $this->app->singleton(BalancedJsonObjectExtractor::class);
        $this->app->singleton(AssistantUiPayloadNormalizerInterface::class, AssistantUiPayloadNormalizer::class);
        $this->app->singleton(AssistantUiEnvelopeParserInterface::class, AssistantUiEnvelopeParser::class);
        $this->app->singleton(AssistantVisibleReplySanitizerInterface::class, AssistantVisibleReplySanitizer::class);
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

        $this->registerAutomaticTranslationDispatch();

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

    /**
     * Queue a TranslateListingJob whenever a translatable listing/guiding/vacation is created or
     * updated. Kept deliberately dumb (no "did translatable fields change" check here) — the job
     * itself is the real gatekeeper via each service's content-hash "needs update" check, so a
     * save that didn't touch translatable content costs one cheap DB lookup per job run, not an
     * API call. Registered as closures (rather than Model::observe()) because observe() always
     * re-resolves the observer class fresh from the container by class name, which can't carry a
     * per-model constructor argument (the listing type).
     */
    private function registerAutomaticTranslationDispatch(): void
    {
        $watchedModels = [
            Trip::class => ListingTranslationService::TYPE_TRIP,
            Camp::class => ListingTranslationService::TYPE_CAMP,
            RentalBoat::class => ListingTranslationService::TYPE_RENTAL_BOAT,
            SpecialOffer::class => ListingTranslationService::TYPE_SPECIAL_OFFER,
            Accommodation::class => ListingTranslationService::TYPE_ACCOMMODATION,
            Guiding::class => 'guiding',
            Vacation::class => 'vacation',
        ];

        foreach ($watchedModels as $modelClass => $listingType) {
            $modelClass::saved(function (Model $model) use ($listingType) {
                if (! config('services.translation.auto_translate', true)) {
                    return;
                }

                $listingId = (int) $model->getKey();
                $cooldown = (int) config('services.translation.dispatch_cooldown_seconds', 120);

                // Cache::add is an atomic "set if not already present". It collapses any burst
                // of saves on the same listing — a rapid autosave flurry, or someone hammering
                // the edit form — into at most one queued job per cooldown window, regardless of
                // how many times save() actually fires.
                if ($cooldown > 0 && ! Cache::add("translate-dispatch:{$listingType}:{$listingId}", true, $cooldown)) {
                    return;
                }

                // Delay execution by the same cooldown instead of running immediately: the job
                // re-reads the listing fresh from the database when it runs (it carries only the
                // id, not a field snapshot), so waiting out the full window guarantees every save
                // made during it — e.g. a title edit followed by a separate description edit a
                // few seconds later — is already committed and gets captured in the one job that
                // does run, rather than racing a fast queue worker against a second save.
                TranslateListingJob::dispatch($listingType, $listingId)->delay($cooldown);
            });
        }
    }
}
