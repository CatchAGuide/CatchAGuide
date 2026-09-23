<?php

namespace Tests\Feature\Seo;

use Illuminate\Support\Facades\URL;
use Tests\TestCase;

/**
 * Every JSON-LD block must parse and describe real data. /guidings/alloffers used to embed a
 * sample ItemList (fake "John Fisher" guides on forestry.com, one per tour, trailing comma) —
 * Search Console's "Unparsable structured data" on .com.
 */
class StructuredDataTest extends TestCase
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

    public function test_listing_pages_emit_only_valid_json_ld_without_placeholder_data(): void
    {
        foreach (['/guidings/alloffers', '/offers', '/'] as $path) {
            $response = $this->get($path);
            $response->assertOk();

            preg_match_all('#<script[^>]*application/ld\+json[^>]*>(.*?)</script>#s', (string) $response->getContent(), $blocks);
            $this->assertNotEmpty($blocks[1], "No JSON-LD on {$path}");

            foreach ($blocks[1] as $json) {
                $this->assertNotNull(json_decode(trim($json)), "Invalid JSON-LD on {$path}: ".json_last_error_msg());
                $this->assertStringNotContainsString('forestry.com', $json);
                $this->assertStringNotContainsString('John Fisher', $json);
            }
        }
    }
}
