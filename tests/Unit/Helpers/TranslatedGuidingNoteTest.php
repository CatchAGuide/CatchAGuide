<?php

namespace Tests\Unit\Helpers;

use App\Models\Guiding;
use Illuminate\Support\Facades\Cache;
use Tests\TestCase;

class TranslatedGuidingNoteTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        app()->setLocale('de');
        Cache::flush();
    }

    public function test_same_language_page_keeps_the_guides_wording(): void
    {
        $guiding = new Guiding(['language' => 'de']);
        Cache::forever(translation_cache_key('Ist erforderlich', 'de'), 'SHOULD_NOT_APPEAR');

        $note = translated_guiding_note($guiding, [
            'value' => 'Ist erforderlich',
            'source_value' => 'Ist erforderlich',
        ]);

        $this->assertSame('Ist erforderlich', $note);
    }

    public function test_stored_translation_is_shown_without_translating_again(): void
    {
        $guiding = new Guiding(['language' => 'en']);
        Cache::forever(translation_cache_key('Angelschein ist dabei', 'de'), 'SHOULD_NOT_APPEAR');

        $note = translated_guiding_note($guiding, [
            'value' => 'Angelschein ist dabei',
            'source_value' => 'We got fishing licence for everyone on board.',
        ]);

        $this->assertSame('Angelschein ist dabei', $note);
    }

    public function test_untranslated_source_text_uses_the_locale_cache_on_the_other_language(): void
    {
        $guiding = new Guiding(['language' => 'en']);
        Cache::forever(translation_cache_key('Bring suitable clothing.', 'de'), 'Geeignete Kleidung mitbringen.');

        $note = translated_guiding_note($guiding, [
            'value' => 'Bring suitable clothing.',
            'source_value' => 'Bring suitable clothing.',
        ]);

        $this->assertSame('Geeignete Kleidung mitbringen.', $note);
    }

    public function test_english_note_on_a_german_tour_is_translated_for_the_german_page(): void
    {
        $guiding = new Guiding(['language' => 'de']);
        $source = 'The license will be organized by the guide';
        Cache::forever(translation_cache_key($source, 'de'), 'Die Lizenz wird vom Guide organisiert');

        $note = translated_guiding_note($guiding, [
            'value' => $source,
            'source_value' => $source,
        ]);

        $this->assertSame('Die Lizenz wird vom Guide organisiert', $note);
    }

    public function test_a_failed_translation_is_not_kept_as_the_german_text(): void
    {
        $guiding = new Guiding(['language' => 'de']);
        $source = 'The license will be organized by the guide';
        Cache::forever(translation_cache_key($source, 'de'), $source);

        $note = translated_guiding_note($guiding, [
            'value' => $source,
            'source_value' => $source,
        ]);

        $this->assertSame($source, $note);
        $this->assertNull(Cache::get(translation_cache_key($source, 'de')));
    }

    public function test_empty_note_is_blank(): void
    {
        $guiding = new Guiding(['language' => 'en']);

        $this->assertSame('', translated_guiding_note($guiding, ['value' => '  ', 'source_value' => '  ']));
    }
}
