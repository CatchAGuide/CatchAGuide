<?php

namespace Tests\Unit\Camp;

use App\Services\Camp\CampBestTravelTimesParser;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class CampBestTravelTimesParserTest extends TestCase
{
    private CampBestTravelTimesParser $parser;

    protected function setUp(): void
    {
        parent::setUp();
        $this->parser = new CampBestTravelTimesParser();
    }

    #[Test]
    public function it_splits_same_line_title_and_description(): void
    {
        $text = "Frühling (April – Mai): Mit dem Aufbrechen des Eises erwacht der See.\n\n"
            ."Sommer (Juni – August): Die Hochsaison mit langen Tagen.";

        $parsed = $this->parser->parse($text);

        $this->assertCount(2, $parsed);
        $this->assertSame('Frühling (April – Mai)', $parsed[0]['title']);
        $this->assertSame('Mit dem Aufbrechen des Eises erwacht der See.', $parsed[0]['description']);
        $this->assertSame('Sommer (Juni – August)', $parsed[1]['title']);
        $this->assertSame('Die Hochsaison mit langen Tagen.', $parsed[1]['description']);
    }

    #[Test]
    public function it_keeps_title_then_description_on_following_lines(): void
    {
        $text = "Frühling (April – Mai):\nWenn das Eis schmilzt.\n\nSommer (Juni – August):\nLange helle Tage.";

        $parsed = $this->parser->parse($text);

        $this->assertCount(2, $parsed);
        $this->assertSame('Frühling (April – Mai)', $parsed[0]['title']);
        $this->assertSame('Wenn das Eis schmilzt.', $parsed[0]['description']);
        $this->assertSame('Sommer (Juni – August)', $parsed[1]['title']);
        $this->assertSame('Lange helle Tage.', $parsed[1]['description']);
    }

    #[Test]
    public function it_pairs_alternating_title_and_body_blocks(): void
    {
        $text = "Spring (April – June)\n\n"
            ."The most productive period for halibut and cod, both feeding actively in shallower coastal areas after winter.\n\n"
            ."Summer (July – August)\n\n"
            ."Peak season with long bright evenings, active coalfish, and almost endless daylight on the water.";

        $parsed = $this->parser->parse($text);

        $this->assertCount(2, $parsed);
        $this->assertSame('Spring (April – June)', $parsed[0]['title']);
        $this->assertStringContainsString('halibut', $parsed[0]['description']);
        $this->assertSame('Summer (July – August)', $parsed[1]['title']);
        $this->assertStringContainsString('Peak season', $parsed[1]['description']);
    }

    #[Test]
    public function it_recognizes_structured_month_note_lists(): void
    {
        $this->assertTrue($this->parser->isStructuredMonthNoteList([
            ['month' => 'Mar', 'note' => 'Pike active'],
        ]));
        $this->assertFalse($this->parser->isStructuredMonthNoteList([
            'Spring: good fishing',
        ]));
    }
}
