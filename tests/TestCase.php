<?php

namespace Tests;

use App\Services\Translation\FreeGoogleTranslateClient;
use Illuminate\Foundation\Testing\TestCase as BaseTestCase;

abstract class TestCase extends BaseTestCase
{
    use CreatesApplication;

    protected function setUp(): void
    {
        parent::setUp();

        // The translate() helper called Google on every page a test rendered: the suite hit
        // 429s, and assertions on source text failed whenever a translation came back.
        // Tests that need a translation bind their own client.
        $this->app->instance(FreeGoogleTranslateClient::class, new class extends FreeGoogleTranslateClient {
            public function translate(string $text, string $locale, array $httpOptions): string
            {
                return $text;
            }
        });
    }
}
