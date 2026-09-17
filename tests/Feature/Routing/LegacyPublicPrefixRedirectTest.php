<?php

namespace Tests\Feature\Routing;

use Illuminate\Support\Facades\URL;
use Tests\TestCase;

/**
 * Covers App\Http\Middleware\CustomRedirectMiddleware's /public/ stripping.
 *
 * Confirmed with the hosting provider (SiteGround) via a live curl against production: requests
 * to /public/... 301-loop back to the identical URL. The cause is in this middleware, not
 * .htaccess/CDN/cache (all ruled out server-side). The account's document root is public_html,
 * with requests internally rewritten to public/index.php, so Symfony's SCRIPT_NAME-based base
 * URL detection resolves to /public on that host. The old code built a *relative* redirect
 * target and passed it through the redirect() helper, which re-prepends that detected base URL
 * — turning "test123" back into "/public/test123" and looping forever. Fixed by building an
 * absolute URL (scheme + host + cleaned path) so no base-URL prefixing can happen.
 *
 * test_stray_public_prefixed_url_does_not_loop_when_base_url_resolves_to_public reproduces the
 * exact production condition (SCRIPT_NAME under /public) — this is the one that would have
 * caught the bug; the others pass against both the old and new code because the local test
 * environment's base URL never resolves to /public.
 */
class LegacyPublicPrefixRedirectTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        // APP_URL has no scheme in local .env (cag.local), which makes the test client's
        // url()-based request building mangle the path. Force a clean root for these tests,
        // same as WebRouteCleanupTest does.
        config(['app.url' => 'http://localhost']);
        URL::forceRootUrl('http://localhost');
    }

    public function test_stray_public_prefixed_url_redirects_to_the_clean_path(): void
    {
        $this->get('/public/guidings/488/some-old-slug')
            ->assertRedirect('http://localhost/guidings/488/some-old-slug')
            ->assertStatus(301);
    }

    public function test_stray_public_prefixed_url_keeps_its_query_string(): void
    {
        $response = $this->get('/public/destination/niederlande/rheindelta?sortby=price-asc&page=5');

        $location = (string) $response->headers->get('Location');
        $this->assertStringStartsWith('http://localhost/destination/niederlande/rheindelta?', $location);
        $this->assertStringContainsString('sortby=price-asc', $location);
        $this->assertStringContainsString('page=5', $location);
    }

    public function test_redirect_target_never_still_starts_with_public(): void
    {
        $response = $this->get('/public/guidings/offer/some-slug');

        $location = (string) $response->headers->get('Location');
        $this->assertStringNotContainsString('/public/', $location);
    }

    public function test_stray_public_prefixed_url_does_not_loop_when_base_url_resolves_to_public(): void
    {
        // setUp() forces the URL generator's root to bypass request-based detection (needed
        // because local .env's APP_URL has no scheme) — but that forced root is exactly what
        // masks this bug, since redirect() only re-prepends a detected /public base URL when
        // nothing has forced the root. Un-force it so this test exercises the real code path:
        // request()->root() computed from SCRIPT_NAME/SCRIPT_FILENAME, same as production.
        URL::forceRootUrl(null);

        // Simulates the production host: document root is public_html, requests are internally
        // rewritten to public/index.php, so SCRIPT_NAME/SCRIPT_FILENAME make Symfony resolve the
        // app's base URL as /public.
        $server = [
            'SCRIPT_FILENAME' => '/var/www/html/public/index.php',
            'SCRIPT_NAME' => '/public/index.php',
            'PHP_SELF' => '/public/index.php',
            'HTTP_HOST' => 'localhost',
        ];

        // Pass a fully-qualified URI (rather than a path) so the test client's own
        // url()-based request building — which goes through the same forced-root-less
        // generator — doesn't get mangled by the schemeless local APP_URL.
        $response = $this->call('GET', 'http://localhost/public/test123', [], [], [], $server);

        $response->assertStatus(301);

        $location = (string) $response->headers->get('Location');
        $this->assertSame('http://localhost/test123', $location);
    }
}
