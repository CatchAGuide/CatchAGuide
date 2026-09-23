<?php

namespace Tests\Feature\Seo;

use App\Models\GuideThread;
use App\Models\Thread;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\URL;
use Tests\TestCase;

/**
 * Hreflang must only point at pages that exist on the other domain. Magazine and guide articles
 * are written per language with unrelated slugs; declaring a same-path alternate sent Google to
 * redirects and 404s on the other domain.
 */
class HreflangAlternatesTest extends TestCase
{
    use DatabaseTransactions;

    protected function setUp(): void
    {
        parent::setUp();

        config(['app.url' => 'http://localhost']);
        URL::forceRootUrl('http://localhost');
        app()->setLocale('de');
        $this->withoutMiddleware([
            \Illuminate\Routing\Middleware\ThrottleRequests::class,
            \App\Http\Middleware\DDoSProtectionMiddleware::class,
        ]);
    }

    public function test_shared_pages_declare_both_language_alternates(): void
    {
        $response = $this->get('/faq');

        $response->assertOk();
        $response->assertSee('hreflang="en"', false);
        $response->assertSee('hreflang="de"', false);
        $response->assertSee('hreflang="x-default"', false);
    }

    public function test_magazine_article_declares_no_alternates(): void
    {
        $thread = new Thread();
        $thread->forceFill([
            'language' => 'de',
            'title' => 'Hechtangeln im Herbst',
            'slug' => 'hechtangeln-im-herbst-'.uniqid(),
            'body' => '<p>Text</p>',
            'excerpt' => 'Kurz',
            'author' => 'Redaktion',
            'thumbnail_path' => '',
        ])->save();

        $response = $this->get('/angelmagazin/'.$thread->slug);

        $response->assertOk();
        $response->assertDontSee('hreflang=', false);
    }

    public function test_root_guide_article_declares_no_alternates(): void
    {
        $guide = GuideThread::query()->forceCreate([
            'language' => 'de',
            'title' => 'Angeln in Hamburg',
            'slug' => 'angeln-in-hamburg-test-'.uniqid(),
            'body' => '<p>Text</p>',
        ]);

        $response = $this->get('/'.$guide->slug);

        $response->assertOk();
        $response->assertDontSee('hreflang=', false);
    }
}
