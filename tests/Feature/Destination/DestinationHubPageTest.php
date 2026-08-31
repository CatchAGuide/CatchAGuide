<?php

namespace Tests\Feature\Destination;

use App\Domain\CategoryPage\CategoryPageEntityType;
use App\Domain\CategoryPage\CategoryPageScope;
use App\Models\Faq;
use App\Models\Language;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\URL;
use Tests\TestCase;

class DestinationHubPageTest extends TestCase
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

    public function test_destination_index_falls_back_to_lang_title(): void
    {
        Language::query()
            ->where('type', CategoryPageEntityType::DESTINATION_HUB)
            ->where('source_id', CategoryPageEntityType::DESTINATION_HUB_SOURCE_ID)
            ->delete();

        $response = $this->get(route('destination'));

        $response->assertOk();
        $response->assertSee(__('destination.title'), false);
        $response->assertSee(__('destination.header_sub_title'), false);
        $response->assertSee('cag-site-nav', false);
        $response->assertSee('cag-site-nav-shell', false);
        $response->assertSee('cag-site-nav--overlay', false);
        $response->assertSee('data-category-header-shell', false);
        $response->assertSee('offers-page-header__hero', false);
        $response->assertSee('categoryHeroSearchPlace', false);
        $response->assertSee('data-category-header-search', false);
        $response->assertSee(__('destination.breadcrumb'), false);
        $response->assertDontSee('navbar-custom short-header long-header', false);
    }

    public function test_destination_index_renders_cms_hub_content(): void
    {
        $locale = app()->getLocale();

        Language::query()->create([
            'source_id' => CategoryPageEntityType::DESTINATION_HUB_SOURCE_ID,
            'type' => CategoryPageEntityType::DESTINATION_HUB,
            'scope' => CategoryPageScope::GLOBAL,
            'language' => $locale,
            'title' => 'CMS Fishing Tours Across Europe',
            'sub_title' => 'CMS destination subtitle',
            'introduction' => 'CMS destination introduction copy.',
            'content' => '<p>CMS destination body content.</p>',
            'faq_title' => 'CMS Destination FAQ',
        ]);

        Faq::query()->create([
            'source_id' => CategoryPageEntityType::DESTINATION_HUB_SOURCE_ID,
            'page' => CategoryPageEntityType::DESTINATION_HUB,
            'scope' => CategoryPageScope::GLOBAL,
            'language' => $locale,
            'question' => 'CMS hub FAQ question?',
            'answer' => 'CMS hub FAQ answer.',
        ]);

        $response = $this->get(route('destination'));

        $response->assertOk();
        $response->assertSee('CMS Fishing Tours Across Europe', false);
        $response->assertSee('CMS destination subtitle', false);
        $response->assertSee('CMS destination introduction copy.', false);
        $response->assertSee('CMS destination body content.', false);
        $response->assertSee('CMS Destination FAQ', false);
        $response->assertSee('CMS hub FAQ question?', false);
        $response->assertSee('CMS hub FAQ answer.', false);
        $response->assertDontSee(__('destination.title'), false);
        $response->assertSee('data-category-header-shell', false);
        $response->assertDontSee('navbar-custom short-header long-header', false);
    }
}
