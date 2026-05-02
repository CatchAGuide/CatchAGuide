<?php

namespace App\Providers;

use App\Contracts\Assistant\AssistantUiEnvelopeParserInterface;
use App\Contracts\Assistant\AssistantUiPayloadNormalizerInterface;
use App\Contracts\Assistant\AssistantVisibleReplySanitizerInterface;
use App\Contracts\Assistant\LLMClientInterface;
use App\Http\Resources\EventResource;
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
use App\Services\Recaptcha\RecaptchaVerifier;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Validator;
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

        Validator::extend('recaptcha', function (string $attribute, mixed $value, array $parameters, $validator): bool {
            // In case config caching or env is misconfigured, fail closed.
            if (config('recaptcha.api_secret_key', '') === '') {
                return false;
            }

            $ip = request()?->ip();
            $skip = (array) config('recaptcha.skip_ip', []);
            if ($ip && in_array($ip, $skip, true)) {
                return true;
            }

            $resp = app(RecaptchaVerifier::class)->verify(is_string($value) ? $value : null, $ip);

            return $resp->isSuccess();
        }, trans(config('recaptcha.error_message_key', 'validation.recaptcha')));


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
