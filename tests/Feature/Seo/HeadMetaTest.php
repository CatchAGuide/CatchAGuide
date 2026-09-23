<?php

namespace Tests\Feature\Seo;

use App\Models\GuideThread;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\URL;
use Tests\TestCase;

/**
 * One <title>, and a meta description taken from the page's own copy: plain text, at most 160
 * characters, no "Catch A Guide - " prefix. Guide articles (layouts.app) used to have none at all.
 */
class HeadMetaTest extends TestCase
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

    private function headOf(string $path): string
    {
        $response = $this->get($path);
        $response->assertOk();

        return explode('</head>', (string) $response->getContent())[0];
    }

    public function test_guide_article_gets_a_capped_plain_text_description(): void
    {
        $guide = GuideThread::query()->forceCreate([
            'language' => 'de',
            'title' => 'Angeln in Hamburg',
            'slug' => 'angeln-in-hamburg-meta-'.uniqid(),
            'excerpt' => '<p>Beim Angeln in Hamburg &amp;uuml;berrascht die Elbe.</p> '.str_repeat('Zander und Barsch im Hafen. ', 12),
            'body' => '<p>Text</p>',
        ]);

        $head = $this->headOf('/'.$guide->slug);

        $this->assertSame(1, substr_count($head, '<title>'));
        $this->assertMatchesRegularExpression('#<meta name="description" content="([^"]+)"#', $head);
        preg_match('#<meta name="description" content="([^"]+)"#', $head, $m);
        $description = html_entity_decode($m[1]);
        $this->assertStringStartsWith('Beim Angeln in Hamburg überrascht die Elbe.', $description);
        $this->assertStringNotContainsString('<p>', $description);
        $this->assertLessThanOrEqual(161, mb_strlen($description));
    }

    public function test_descriptions_carry_no_brand_prefix_and_empty_keywords_are_omitted(): void
    {
        $head = $this->headOf('/offers');

        $this->assertDoesNotMatchRegularExpression('#<meta name="description" content="Catch A Guide - #', $head);
        $this->assertStringNotContainsString('content="Catch A Guide - "', $head);
    }
}
