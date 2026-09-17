<?php

namespace Tests\Unit\Helpers;

use Illuminate\Support\Facades\Cache;
use Tests\TestCase;

class TranslatedCatalogLabelTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        app()->setLocale('en');
        Cache::flush();
    }

    public function test_english_prefers_name_en_from_the_table(): void
    {
        Cache::forever(translation_cache_key('WLAN', 'en'), 'SHOULD_NOT_APPEAR');

        $label = translated_catalog_label([
            'id' => 12,
            'name' => 'WLAN',
            'name_en' => 'WiFi',
        ]);

        $this->assertSame('WiFi', $label);
    }

    public function test_english_translates_table_rows_when_name_en_is_missing(): void
    {
        Cache::forever(translation_cache_key('Kuehlschrank', 'en'), 'Refrigerator');

        $this->assertSame('Refrigerator', translated_catalog_label([
            'id' => 9,
            'name' => 'Kuehlschrank',
        ]));
    }

    public function test_german_uses_table_name_without_translate(): void
    {
        app()->setLocale('de');
        Cache::forever(translation_cache_key('WLAN', 'de'), 'SHOULD_NOT_APPEAR');

        $this->assertSame('WLAN', translated_catalog_label([
            'id' => 12,
            'name' => 'WLAN',
            'name_en' => 'WiFi',
        ]));
    }

    public function test_custom_strings_use_the_global_translate_cache(): void
    {
        Cache::forever(translation_cache_key('Custom reef species', 'en'), 'Cached reef species');

        $this->assertSame('Cached reef species', translated_catalog_label('Custom reef species'));
        $this->assertSame(
            'Cached reef species',
            translated_catalog_label(['id' => null, 'name' => 'Custom reef species'])
        );
    }

    public function test_numeric_strings_are_left_alone(): void
    {
        $this->assertSame('4', translated_catalog_label('4'));
        $this->assertSame('', translated_catalog_label(''));
    }

    public function test_html_entities_from_translate_are_decoded_for_display(): void
    {
        Cache::forever(translation_cache_key('Äsche', 'en'), '&Auml;sche');

        $this->assertSame('Äsche', translated_catalog_label('Äsche'));
        $this->assertSame('Äsche', translated_catalog_label([
            'id' => null,
            'name' => 'Äsche',
        ]));
    }
}
