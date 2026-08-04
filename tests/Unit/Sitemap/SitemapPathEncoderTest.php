<?php

namespace Tests\Unit\Sitemap;

use App\Services\Sitemap\SitemapPathEncoder;
use PHPUnit\Framework\TestCase;

class SitemapPathEncoderTest extends TestCase
{
    private SitemapPathEncoder $encoder;

    protected function setUp(): void
    {
        parent::setUp();
        $this->encoder = new SitemapPathEncoder();
    }

    public function test_encodes_umlaut_country_segment(): void
    {
        $loc = $this->encoder->join('https://www.catchaguide.com', ['vacations', 'österreich']);
        $this->assertSame('https://www.catchaguide.com/vacations/' . rawurlencode('österreich'), $loc);
        $this->assertStringContainsString('%C3%B6', $loc);
    }

    public function test_from_path_joins_segments(): void
    {
        $loc = $this->encoder->fromPath('https://www.catchaguide.de', 'vacations/trips/schweden');
        $this->assertSame('https://www.catchaguide.de/vacations/trips/schweden', $loc);
    }

    public function test_empty_path_returns_base(): void
    {
        $this->assertSame('https://www.catchaguide.com', $this->encoder->fromPath('https://www.catchaguide.com/', ''));
    }
}
