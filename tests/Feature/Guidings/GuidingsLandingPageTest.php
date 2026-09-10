<?php

namespace Tests\Feature\Guidings;

use App\Models\CategoryPage;
use App\Models\Method;
use App\Repositories\Guiding\GuidingCategoryAvailabilityRepository;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Cache;
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
        Cache::forget('guiding_category_availability_v1');

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

    public function test_guidings_landing_methods_rail_includes_available_method(): void
    {
        app()->setLocale('de');

        $marker = 'landing-method-'.uniqid();
        $method = new Method();
        $method->forceFill([
            'name' => $marker,
            'name_en' => $marker,
        ])->save();

        CategoryPage::query()->create([
            'name' => $marker,
            'type' => 'Methods',
            'slug' => $marker,
            'source_id' => (string) $method->id,
            'is_favorite' => true,
        ]);

        $this->mock(GuidingCategoryAvailabilityRepository::class, function ($mock) use ($method) {
            $mock->allows('methodIdsWithGuidings')->andReturn([$method->id]);
            $mock->allows('targetIdsWithGuidings')->andReturn([]);
            $mock->allows('hasGuidingsForCountry')->andReturn(true);
            $mock->shouldIgnoreMissing();
        });

        Cache::forget('guidings_landing_methods_v4_de');

        $response = $this->get(route('guidings.landing'));

        $response->assertOk();
        $response->assertSee($marker, false);
        $response->assertSee(route('guidings.methods.show', ['slug' => $marker], false), false);
        $response->assertSee('guidings-landing-methods', false);
        $response->assertSee('vacation-fish-rail__tile', false);
    }
}
