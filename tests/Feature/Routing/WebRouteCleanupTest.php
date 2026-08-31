<?php

namespace Tests\Feature\Routing;

use Illuminate\Http\Request;
use Illuminate\Routing\Route;
use Illuminate\Support\Facades\URL;
use Tests\TestCase;

class WebRouteCleanupTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        config(['app.url' => 'http://localhost']);
        URL::forceRootUrl('http://localhost');
    }

    public function test_critical_named_routes_keep_their_uris(): void
    {
        $expected = [
            'welcome' => '/',
            'guidings.landing' => 'guidings',
            'guidings.index' => 'guidings/alloffers',
            'guidings.show' => 'guidings/offer/{slug}',
            'guidings.show.legacy' => 'guidings/{id}/{slug}',
            'guidings.edit' => 'guidings/{guiding}/edit',
            'guidings.destination' => 'guidings/{country}/{region?}/{city?}',
            'checkout' => 'checkout',
            'checkout.index' => 'checkout',
            'checkout.store' => 'checkouts',
            'booking.accept' => 'booking-accept/{token}',
            'booking.reject' => 'booking-reject/{token}',
            'profile.index' => 'profile',
            'profile.newguiding.store' => 'newguiding',
            'login' => 'login',
            'admin.index' => 'admin',
            'admin.auth.logins' => 'admin/logins',
            'admin.auth.login' => 'admin/login',
            'admin.guidings.search' => 'admin/guidings/search',
            'vacations.index' => 'vacations',
            'vacations.country' => 'vacations/{country}',
            'destination' => 'destination',
            'category.thread' => '{slug?}',
            'product-reports.store' => 'product-reports',
            'assistant.chat' => 'assistant/chat',
            'admin.api.financial-dashboard' => 'api/admin/financial-dashboard',
        ];

        foreach ($expected as $name => $uri) {
            $route = app('router')->getRoutes()->getByName($name);
            $this->assertNotNull($route, "Missing named route [{$name}]");
            $this->assertSame($uri, $route->uri(), "URI changed for [{$name}]");
        }
    }

    public function test_unused_and_unsafe_public_routes_are_gone(): void
    {
        foreach ([
            'ajax-checkout',
            'status.accepted',
            'vacations.create',
            'vacations.store',
            'vacations.edit',
            'vacations.update',
            'vacations.destroy',
        ] as $name) {
            $this->assertNull(
                app('router')->getRoutes()->getByName($name),
                "Dead route [{$name}] should not be registered"
            );
        }

        $this->assertFalse($this->hasUri('template-reject'));
        $this->assertFalse($this->hasUri('booking/status}'));
        $this->assertFalse($this->hasUri('api/queue/run-worker'));
        $this->assertFalse($this->hasUri('api/update/status'));
        $this->assertFalse($this->hasUri('api/run/reminder'));
        $this->assertFalse($this->hasUri('testderoute'));
        $this->assertFalse($this->hasUri('testenroute'));
    }

    public function test_admin_routes_require_employee_auth_except_login(): void
    {
        $this->assertMiddlewareContains('admin.index', 'auth:employees');
        $this->assertMiddlewareContains('admin.guidings.index', 'auth:employees');
        $this->assertMiddlewareContains('admin.bookings.index', 'auth:employees');
        $this->assertMiddlewareContains('admin.api.financial-dashboard', 'auth:employees');
        $this->assertMiddlewareContains('admin.customers.index', 'auth:employees');
        $this->assertMiddlewareContains('admin.settings.scheduled-tasks.index', 'auth:employees');

        $this->assertMiddlewareNotContains('admin.auth.logins', 'auth:employees');
        $this->assertMiddlewareContains('admin.auth.login', 'throttle:login');
    }

    public function test_authenticated_user_flows_keep_web_guard(): void
    {
        $this->assertMiddlewareContains('profile.index', 'auth:web');
        $this->assertMiddlewareContains('profile.bookings', 'auth:web');
        $this->assertMiddlewareContains('guidings.edit', 'auth:web');
        $this->assertMiddlewareContains('guidings.update', 'auth:web');
        $this->assertMiddlewareContains('wishlist.add-or-remove', 'auth:web');
        $this->assertMiddlewareContains('profile.newguiding.store', 'auth:web,employees');
        $this->assertMiddlewareContains('upload', 'auth:web,employees');
        $this->assertMiddlewareContains('upload', 'throttle:30,1');
        $this->assertMiddlewareContains('ical-feeds.index', 'auth:web');
        $this->assertMiddlewareContains('oauth.disconnect', 'auth:web');
        $this->assertMiddlewareContains('oauth.sync', 'auth:web');
    }

    public function test_public_money_and_mail_flows_stay_throttled(): void
    {
        $this->assertMiddlewareContains('checkout', 'throttle:5,1');
        $this->assertMiddlewareContains('checkout', 'ddos:checkout');
        $this->assertMiddlewareContains('checkout.index', 'throttle:10,1,checkout-page:');
        $this->assertMiddlewareContains('checkout.index', 'ddos:checkout');
        $this->assertMiddlewareContains('checkout.store', 'throttle:5,1,checkout-store:');
        $this->assertMiddlewareContains('booking.accept', 'throttle:10,1');
        $this->assertMiddlewareContains('booking.reject', 'throttle:10,1');
        $this->assertMiddlewareContains('booking.rejection', 'throttle:10,1');
        $this->assertMiddlewareContains('booking.reschedule', 'throttle:10,1');
        $this->assertMiddlewareContains('store.request', 'throttle:5,1');
        $this->assertMiddlewareContains('product-reports.store', 'throttle:5,1');
        $this->assertMiddlewareContains('sendcontactmail', 'throttle:10,1');
        $this->assertMiddlewareContains('sendnewsletter', 'throttle:5,1');
        $this->assertMiddlewareContains('assistant.chat', 'throttle:booking-assistant');
        $this->assertMiddlewareContains('assistant.chat', 'booking.assistant.access');
        $this->assertMiddlewareContains('guidings.index', 'ddos:search');
        $this->assertMiddlewareContains('vacations.index', 'ddos:search');
        $this->assertMiddlewareContains('admin.guidings.translate', 'throttle:gemini-translation');
    }

    public function test_specific_catalog_and_edit_paths_are_not_swallowed_by_catch_alls(): void
    {
        $this->assertSame('guidings.index', $this->matchName('GET', '/guidings/alloffers'));
        $this->assertSame('guidings.show', $this->matchName('GET', '/guidings/offer/sea-trout'));
        $this->assertSame('guidings.edit', $this->matchName('GET', '/guidings/12/edit'));
        $this->assertSame('guidings.show.legacy', $this->matchName('GET', '/guidings/164/old-slug'));
        $this->assertSame('guidings.destination', $this->matchName('GET', '/guidings/deutschland/bavaria'));
        $this->assertSame('vacations.country', $this->matchName('GET', '/vacations/spain'));
        $this->assertSame('vacations.trips.index', $this->matchName('GET', '/vacations/trips'));
        $this->assertSame('admin.index', $this->matchName('GET', '/admin'));
        $this->assertSame('profile.index', $this->matchName('GET', '/profile'));
        $this->assertSame('checkout.index', $this->matchName('GET', '/checkout'));
        $this->assertSame('category.thread', $this->matchName('GET', '/some-magazine-thread-slug'));
    }

    public function test_guests_cannot_open_admin_or_profile(): void
    {
        $this->get(route('admin.index'))->assertRedirect(route('admin.auth.logins'));
        $this->get(route('profile.index'))->assertRedirect(route('login'));
        $this->get(route('admin.guidings.index'))->assertRedirect(route('admin.auth.logins'));
    }

    public function test_admin_login_page_stays_public(): void
    {
        $route = app('router')->getRoutes()->getByName('admin.auth.logins');
        $this->assertNotNull($route);
        $this->assertSame('admin/logins', $route->uri());
        $this->assertMiddlewareNotContains('admin.auth.logins', 'auth:employees');
        $this->assertMiddlewareNotContains('admin.auth.logins', 'auth:web');
    }

    /**
     * @return list<string>
     */
    private function middlewareFor(string $name): array
    {
        $route = app('router')->getRoutes()->getByName($name);
        $this->assertNotNull($route, "Missing named route [{$name}]");

        return $route->gatherMiddleware();
    }

    private function assertMiddlewareContains(string $name, string $middleware): void
    {
        $this->assertContains(
            $middleware,
            $this->middlewareFor($name),
            "Route [{$name}] is missing middleware [{$middleware}]"
        );
    }

    private function assertMiddlewareNotContains(string $name, string $middleware): void
    {
        $this->assertNotContains(
            $middleware,
            $this->middlewareFor($name),
            "Route [{$name}] should not use middleware [{$middleware}]"
        );
    }

    private function matchName(string $method, string $path): string
    {
        $route = app('router')->getRoutes()->match(Request::create($path, $method));
        $this->assertInstanceOf(Route::class, $route);

        return (string) $route->getName();
    }

    private function hasUri(string $uri): bool
    {
        foreach (app('router')->getRoutes() as $route) {
            if ($route->uri() === $uri) {
                return true;
            }
        }

        return false;
    }
}
