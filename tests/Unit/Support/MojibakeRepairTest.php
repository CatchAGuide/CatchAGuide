<?php

namespace Tests\Unit\Support;

use App\Support\Text\MojibakeRepair;
use PHPUnit\Framework\TestCase;

class MojibakeRepairTest extends TestCase
{
    public function test_repairs_double_encoded_umlauts_and_punctuation(): void
    {
        $this->assertSame('Möglichkeiten für Flüsse', MojibakeRepair::repair('MÃ¶glichkeiten fÃ¼r FlÃ¼sse'));
        $this->assertSame('kennen sie – die Grundel „so“', MojibakeRepair::repair('kennen sie â€“ die Grundel â€žsoâ€œ'));
    }

    public function test_repairs_triple_encoded_runs(): void
    {
        $this->assertSame('guests pay 75€ p.p.', MojibakeRepair::repair('guests pay 75Ã¢â€šÂ¬ p.p.'));
    }

    public function test_leaves_correct_and_mixed_correct_text_alone(): void
    {
        $this->assertSame('Schön und groß – über', MojibakeRepair::repair('Schön und groß – über'));
        $this->assertSame('Château in Österreich, Größe über 1 m', MojibakeRepair::repair('Château in Österreich, GrÃ¶ÃŸe Ã¼ber 1 m'));
        $this->assertFalse(MojibakeRepair::needsRepair('Château, naïve, São Paulo'));
        $this->assertNull(MojibakeRepair::repair(null));
    }
}
