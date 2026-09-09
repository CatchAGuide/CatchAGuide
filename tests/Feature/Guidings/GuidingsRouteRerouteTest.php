<?php

namespace Tests\Feature\Guidings;

use App\Models\Guiding;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\URL;
use Tests\TestCase;

class GuidingsRouteRerouteTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        config(['app.url' => 'http://localhost']);
        URL::forceRootUrl('http://localhost');

        $this->withoutMiddleware([
            \Illuminate\Routing\Middleware\ThrottleRequests::class,
            \App\Http\Middleware\DDoSProtectionMiddleware::class,
        ]);
    }

    public function test_guidings_hub_and_listing_use_new_paths(): void
    {
        $this->assertSame('/guidings', route('guidings.landing', [], false));
        $this->assertSame('/guidings/alloffers', route('guidings.index', [], false));
        $this->assertSame('/guidings/offer/sea-trout', route('guidings.show', ['slug' => 'sea-trout'], false));
    }

    public function test_legacy_id_slug_url_is_not_captured_as_destination(): void
    {
        $path = '/guidings/164/meerforelle-mit-blinker-wobbler-und-fliege-in-ostholstein-in-ostholstein-23-deutschland';
        $route = app('router')->getRoutes()->match(Request::create($path, 'GET'));

        $this->assertSame('guidings.show.legacy', $route->getName());
        $this->assertSame('164', $route->parameter('id'));
    }

    public function test_legacy_id_slug_url_redirects_permanently_to_offer_slug(): void
    {
        $guiding = Guiding::query()
            ->whereNotNull('slug')
            ->where('slug', '!=', '')
            ->first();

        if (! $guiding) {
            $this->markTestSkipped('No guiding with a slug in the test database.');
        }

        $response = $this->get('/guidings/'.$guiding->id.'/'.$guiding->slug);

        $response->assertRedirect($guiding->publicShowUrl());
        $response->assertStatus(301);
    }

    public function test_legacy_id_with_stale_slug_still_redirects_to_canonical_offer(): void
    {
        $guiding = Guiding::query()
            ->whereNotNull('slug')
            ->where('slug', '!=', '')
            ->first();

        if (! $guiding) {
            $this->markTestSkipped('No guiding with a slug in the test database.');
        }

        $response = $this->get('/guidings/'.$guiding->id.'/stale-slug-from-old-url');

        $response->assertRedirect($guiding->publicShowUrl());
        $response->assertStatus(301);
    }

    public function test_alloffers_is_not_captured_as_destination_country(): void
    {
        $route = app('router')->getRoutes()->match(Request::create('/guidings/alloffers', 'GET'));

        $this->assertSame('guidings.index', $route->getName());
    }

    public function test_offer_slug_route_is_not_captured_as_destination(): void
    {
        $route = app('router')->getRoutes()->match(Request::create('/guidings/offer/sea-trout-tour', 'GET'));

        $this->assertSame('guidings.show', $route->getName());
        $this->assertSame('sea-trout-tour', $route->parameter('slug'));
    }
}
