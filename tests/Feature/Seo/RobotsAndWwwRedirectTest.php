<?php

namespace Tests\Feature\Seo;

use Illuminate\Http\Request;
use Tests\TestCase;

class RobotsAndWwwRedirectTest extends TestCase
{
    public function test_robots_txt_advertises_only_its_own_domains_sitemap(): void
    {
        $response = $this->get('http://catchaguide.de/robots.txt');

        $response->assertOk();
        $response->assertHeader('Content-Type', 'text/plain; charset=UTF-8');
        $response->assertSee('Sitemap: https://catchaguide.de/sitemap.xml', false);
        $response->assertDontSee('catchaguide.com', false);
        $response->assertSee('Allow: /angelmagazin/', false);
        $response->assertDontSee('Allow: /fishing-magazine/', false);
        $response->assertSee('Disallow: /wishlist/', false);
    }

    public function test_robots_txt_on_com_advertises_com_sitemap_and_english_magazine_path(): void
    {
        $response = $this->get('http://catchaguide.com/robots.txt');

        $response->assertOk();
        $response->assertSee('Sitemap: https://catchaguide.com/sitemap.xml', false);
        $response->assertDontSee('catchaguide.de', false);
        $response->assertSee('Allow: /fishing-magazine/', false);
        $response->assertDontSee('Allow: /angelmagazin/', false);
    }

    public function test_robots_txt_keeps_search_engines_unslowed_and_blocks_training_crawlers(): void
    {
        $robots = $this->get('http://catchaguide.de/robots.txt')->getContent();
        $groups = array_map('trim', preg_split('/\n\s*\n/', $robots));

        // The "*" group (Google, Bing, social previews) has no Crawl-delay.
        $this->assertStringStartsWith('User-agent: *', $groups[0]);
        $this->assertStringNotContainsString('Crawl-delay', $groups[0]);
        $this->assertStringNotContainsString('Googlebot', $robots);
        $this->assertStringNotContainsString('bingbot', $robots);
        $this->assertStringNotContainsString('facebookexternalhit', $robots);

        $slow = $this->groupFor($groups, 'AhrefsBot');
        $this->assertStringContainsString('Crawl-delay: 2', $slow);
        // A named group replaces "*" for that bot, so it must repeat the private disallows.
        $this->assertStringContainsString('Disallow: /admin/', $slow);

        $blocked = $this->groupFor($groups, 'GPTBot');
        $this->assertStringContainsString('User-agent: CCBot', $blocked);
        $this->assertMatchesRegularExpression('#^Disallow: /$#m', $blocked);
        $this->assertStringNotContainsString('OAI-SearchBot', $robots);
    }

    /**
     * @param  list<string>  $groups
     */
    private function groupFor(array $groups, string $agent): string
    {
        foreach ($groups as $group) {
            if (preg_match('/^User-agent: '.preg_quote($agent, '/').'$/m', $group)) {
                return $group;
            }
        }

        $this->fail("No robots.txt group for {$agent}");
    }

    public function test_www_redirects_to_canonical_non_www_host_in_production(): void
    {
        $this->app['env'] = 'production';

        $kernel = $this->app->make(\Illuminate\Contracts\Http\Kernel::class);
        $response = $kernel->handle(Request::create('http://www.catchaguide.com/guidings', 'GET'));

        $this->assertSame(301, $response->getStatusCode());
        $this->assertSame('http://catchaguide.com/guidings', $response->headers->get('Location'));
    }

    public function test_non_www_is_not_redirected_in_production(): void
    {
        $this->app['env'] = 'production';

        $kernel = $this->app->make(\Illuminate\Contracts\Http\Kernel::class);
        $response = $kernel->handle(Request::create('http://catchaguide.com/robots.txt', 'GET'));

        $this->assertNotSame(301, $response->getStatusCode());
    }
}
