<?php

namespace Tests\Unit\Seo;

use App\Services\Seo\LocalePathMapper;
use PHPUnit\Framework\TestCase;

class LocalePathMapperTest extends TestCase
{
    private LocalePathMapper $mapper;

    protected function setUp(): void
    {
        parent::setUp();
        $this->mapper = new LocalePathMapper();
    }

    public function test_magazine_prefix_by_language(): void
    {
        $this->assertSame('fishing-magazine', $this->mapper->magazinePrefix('en'));
        $this->assertSame('angelmagazin', $this->mapper->magazinePrefix('de'));
    }

    public function test_maps_magazine_hub_en_to_de(): void
    {
        $this->assertSame(
            'angelmagazin',
            $this->mapper->mapPath('fishing-magazine', 'en', 'de')
        );
    }

    public function test_maps_magazine_article_de_to_en(): void
    {
        $this->assertSame(
            'fishing-magazine/hecht-guiding',
            $this->mapper->mapPath('angelmagazin/hecht-guiding', 'de', 'en')
        );
    }

    public function test_same_path_surfaces_are_unchanged(): void
    {
        $this->assertSame('guidings/12/foo', $this->mapper->mapPath('guidings/12/foo', 'en', 'de'));
        $this->assertSame('guidings/offer/sea-trout', $this->mapper->mapPath('guidings/offer/sea-trout', 'en', 'de'));
        $this->assertSame('guidings/alloffers', $this->mapper->mapPath('guidings/alloffers', 'de', 'en'));
        $this->assertSame('vacations/trips', $this->mapper->mapPath('vacations/trips', 'de', 'en'));
        $this->assertSame('faq', $this->mapper->mapPath('faq', 'en', 'de'));
    }

    public function test_normalizes_destinationen_hub(): void
    {
        $this->assertSame('destination', $this->mapper->mapPath('destinationen', 'de', 'en'));
        $this->assertSame('destination', $this->mapper->mapPath('destinationen', 'en', 'de'));
    }

    public function test_alternate_url_builds_absolute_loc(): void
    {
        $url = $this->mapper->alternateUrl(
            'https://www.catchaguide.de',
            'fishing-magazine/foo',
            'en',
            'de'
        );

        $this->assertSame('https://www.catchaguide.de/angelmagazin/foo', $url);
    }

    public function test_home_path_maps_to_base(): void
    {
        $url = $this->mapper->alternateUrl('https://www.catchaguide.com', '', 'de', 'en');
        $this->assertSame('https://www.catchaguide.com', $url);
    }
}
