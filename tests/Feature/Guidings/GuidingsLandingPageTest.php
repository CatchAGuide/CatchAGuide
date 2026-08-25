<?php

namespace Tests\Feature\Guidings;

use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\URL;
use Tests\TestCase;

class GuidingsLandingPageTest extends TestCase
{
    use DatabaseTransactions;

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

    public function test_guidings_landing_renders_new_hero_and_sections_in_german(): void
    {
        app()->setLocale('de');

        $response = $this->get(route('guidings.landing'));

        $response->assertOk();
        $response->assertViewIs('pages.newhome-latest');
        $response->assertSee('Finde deine nächste Angeltour', false);
        $response->assertSee('So funktioniert es', false);
        $response->assertSee('Buchen bei Catch A Guide', false);
        $response->assertSee('Drei Schritte, keine Vorkasse, kein Risiko.', false);
        $response->assertSee('Beliebte Angelziele', false);
        $response->assertDontSee('cag-home-destinations__tile cag-home-ph', false);
        $response->assertSee('Welche Art von Angeltour suchst du?', false);
        $response->assertSee('Für Guides, Camps und Reiseanbieter', false);
        $response->assertSee('cag-site-nav--overlay', false);
        $response->assertSee('data-category-header-shell', false);
    }

    public function test_guidings_landing_renders_in_english(): void
    {
        app()->setLocale('en');

        $response = $this->get(route('guidings.landing'));

        $response->assertOk();
        $response->assertSee('Find Your Next Fishing Tour', false);
        $response->assertSee('How it works', false);
        $response->assertSee('Booking with Catch A Guide', false);
        $response->assertSee('Three steps, no prepayment, no risk.', false);
        $response->assertSee('For guides, camps and travel providers', false);
    }

    public function test_guidings_landing_search_form_targets_the_catalog(): void
    {
        $response = $this->get(route('guidings.landing'));

        $response->assertOk();
        $response->assertSee('action="'.url('/guidings/alloffers').'"', false);
        $response->assertSee('categoryHeroSearchPlace', false);
        $response->assertSee('data-offers-persons-stepper', false);
    }
}
