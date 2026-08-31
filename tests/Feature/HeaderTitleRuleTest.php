<?php

namespace Tests\Feature;

use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Facades\View;
use Tests\TestCase;

class HeaderTitleRuleTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        config(['app.url' => 'http://localhost']);
        URL::forceRootUrl('http://localhost');
    }

    public function test_dark_headers_render_title_rule_when_subtitle_is_present(): void
    {
        $vacations = View::make('pages.vacations.partials.catalog-header', [
            'listingTitle' => 'Your Fishing Holiday',
            'listingSubtitle' => 'Flexible camps or organised trips',
        ])->render();

        $this->assertStringContainsString('cag-title-rule--dark', $vacations);
        $this->assertStringContainsString('Your Fishing Holiday', $vacations);
        $this->assertStringContainsString('Flexible camps or organised trips', $vacations);

        $hero = View::make('pages.category.partials.hero-header', [
            'listingTitle' => 'Fishing tours',
            'listingSubtitle' => 'Find a guide',
            'breadcrumbItems' => [
                ['label' => 'Tours', 'url' => null],
            ],
        ])->render();

        $this->assertStringContainsString('cag-title-rule--dark', $hero);
        $this->assertStringContainsString('Find a guide', $hero);
    }

    public function test_hero_header_omits_title_rule_when_subtitle_is_empty(): void
    {
        $html = View::make('pages.category.partials.hero-header', [
            'listingTitle' => 'All Fishing Tours',
            'listingSubtitle' => '',
            'breadcrumbItems' => [
                ['label' => 'Tours', 'url' => null],
            ],
        ])->render();

        $this->assertStringNotContainsString('cag-title-rule', $html);
    }

    public function test_section_headers_omit_title_rule(): void
    {
        $heading = View::make('components.vacation.section-heading', [
            'title' => 'Camps or trips',
            'subtitle' => 'Pick your style',
        ])->render();

        $this->assertStringNotContainsString('cag-title-rule', $heading);
        $this->assertStringContainsString('Camps or trips', $heading);
        $this->assertStringContainsString('Pick your style', $heading);

        $rail = Blade::render(
            '<x-vacation.country-slider title="Popular destinations" subtitle="From Denmark to the Ebro" slider-id="test-rail"></x-vacation.country-slider>'
        );

        $this->assertStringNotContainsString('cag-title-rule', $rail);
        $this->assertStringContainsString('Popular destinations', $rail);
        $this->assertStringContainsString('From Denmark to the Ebro', $rail);

        $species = View::make('pages.home.partials.target-species', [
            'targetSpecies' => collect([
                [
                    'url' => 'http://localhost/targets/pike',
                    'thumbnail' => 'http://localhost/pike.webp',
                    'name' => 'Pike',
                ],
            ]),
        ])->render();

        $this->assertStringNotContainsString('cag-title-rule', $species);
        $this->assertStringContainsString('cag-home-section__title', $species);

        $slider = Blade::render(
            '<x-vacation.card-slider title="Most booked tours" subtitle="Hand-picked by our guests" slider-id="test-slider"></x-vacation.card-slider>'
        );

        $this->assertStringNotContainsString('cag-title-rule', $slider);
        $this->assertStringContainsString('Most booked tours', $slider);
        $this->assertStringContainsString('Hand-picked by our guests', $slider);
    }

    public function test_desktop_styles_hide_title_rule_and_keep_header_copy_on_one_line(): void
    {
        $helpers = (string) file_get_contents(resource_path('sass/settings/_helpers.scss'));

        $this->assertStringContainsString('.cag-title-rule', $helpers);
        $this->assertMatchesRegularExpression(
            '/\.cag-title-rule \{[\s\S]*@media \(min-width: 768px\) \{\s*display: none;/',
            $helpers
        );
        $this->assertStringContainsString('@mixin cag-desktop-nowrap', $helpers);
        $this->assertStringContainsString('white-space: nowrap', $helpers);
        $this->assertStringContainsString('.vacations-page-header__copy', $helpers);
        $this->assertStringContainsString('.offers-page-header__title', $helpers);
        $this->assertStringContainsString('.vacation-country-rail__title', $helpers);
        $this->assertStringContainsString('.cag-home-section__title', $helpers);
        $this->assertStringContainsString('.cag-home-hero__title', $helpers);
        $this->assertStringContainsString('.cag-home-hero__sub', $helpers);
        $this->assertStringContainsString('.cag-home-hero__rule', $helpers);

        $home = (string) file_get_contents(resource_path('sass/page/home.scss'));
        $this->assertMatchesRegularExpression(
            '/\.cag-home-hero__title \{[\s\S]*@include cag-desktop-nowrap;/',
            $home
        );
        $this->assertMatchesRegularExpression(
            '/\.cag-home-hero__sub \{[\s\S]*@include cag-desktop-nowrap;/',
            $home
        );
        $this->assertMatchesRegularExpression(
            '/\.cag-home-hero__rule \{[\s\S]*@media \(min-width: 768px\) \{\s*display: none;/',
            $home
        );

        $vacations = (string) file_get_contents(resource_path('sass/page/_vacations-header.scss'));
        $this->assertMatchesRegularExpression(
            '/&__copy \{\s*@media \(max-width: 767\.98px\) \{\s*max-width: 48rem;/',
            $vacations
        );
        $this->assertStringContainsString('@include cag-desktop-nowrap;', $vacations);

        $offers = (string) file_get_contents(resource_path('sass/page/offers.scss'));
        $this->assertMatchesRegularExpression(
            '/&__copy \{\s*@media \(max-width: 767\.98px\) \{\s*max-width: 48rem;/',
            $offers
        );
        $this->assertStringContainsString('@include cag-desktop-nowrap;', $offers);
    }
}
