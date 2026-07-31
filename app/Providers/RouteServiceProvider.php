<?php

namespace App\Providers;

use App\Support\LoginThrottle;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Foundation\Support\Providers\RouteServiceProvider as ServiceProvider;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\Route;

class RouteServiceProvider extends ServiceProvider
{
    /**
     * The path to the "home" route for your application.
     *
     * This is used by Laravel authentication to redirect users after login.
     *
     * @var string
     */
    public const HOME = '/profile';

    /**
     * The controller namespace for the application.
     *
     * When present, controller route declarations will automatically be prefixed with this namespace.
     *
     * @var string|null
     */
    // protected $namespace = 'App\\Http\\Controllers';

    /**
     * Define your route model bindings, pattern filters, etc.
     *
     * @return void
     */
    public function boot()
    {
        $this->configureRateLimiting();

        $this->routes(function () {
            Route::prefix('api')
                ->middleware('api')
                ->namespace($this->namespace)
                ->group(base_path('routes/api.php'));

            Route::middleware('web')
                ->namespace($this->namespace)
                ->group(base_path('routes/app.php'));

            Route::middleware('web')
                ->namespace($this->namespace)
                ->group(base_path('routes/web.php'));
        });
    }

    /**
     * Configure the rate limiters for the application.
     *
     * @return void
     */
    protected function configureRateLimiting()
    {
        RateLimiter::for('api', function (Request $request) {
            return Limit::perMinute(60)->by(optional($request->user())->id ?: $request->ip());
        });

        // Inline "throttle:x,y" middleware keys every route by domain+IP only, so all
        // of those routes share one counter. Named limiters get their own bucket, which
        // keeps page loads and price recalculations from consuming the booking allowance.
        RateLimiter::for('checkout-price', function (Request $request) {
            return Limit::perMinute(60)->by($request->ip());
        });

        RateLimiter::for('checkout-submit', function (Request $request) {
            return Limit::perMinute(5)
                        ->by($request->ip())
                        ->response(function (Request $request, array $headers) {
                            return response()->json([
                                'success' => false,
                                'message' => __('checkout.too_many_requests'),
                                'retry_after' => (int) ($headers['Retry-After'] ?? 60),
                            ], 429, $headers);
                        });
        });

        // Gemini translation specific rate limiting
        RateLimiter::for('gemini-translation', function (Request $request) {
            return Limit::perMinute(5) // 5 requests per minute
                        ->perHour(50)  // 50 requests per hour
                        ->perDay(200)  // 200 requests per day
                        ->by(optional($request->user())->id ?: $request->ip())
                        ->response(function () {
                            return response()->json([
                                'error' => 'Too many translation requests. Please try again later.',
                                'retry_after' => 60
                            ], 429);
                        });
        });

        // Outer safety net against credential stuffing floods. The per-account
        // lockout lives in the login controllers; this only stops one IP from
        // hammering the endpoint with many different email addresses.
        RateLimiter::for('login', function (Request $request) {
            return Limit::perMinute(20)
                        ->by($request->ip())
                        ->response(function (Request $request, array $headers) {
                            return LoginThrottle::response(
                                $request,
                                (int) ($headers['Retry-After'] ?? 60),
                                $headers
                            );
                        });
        });

        RateLimiter::for('booking-assistant', function (Request $request) {
            $perMinute = (int) config('booking_assistant.rate_limit.per_minute', 15);

            return Limit::perMinute(max(1, $perMinute))->by($request->ip());
        });
    }
}
