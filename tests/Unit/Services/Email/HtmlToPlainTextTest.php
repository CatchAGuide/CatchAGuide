<?php

namespace Tests\Unit\Services\Email;

use App\Services\Email\HtmlToPlainText;
use PHPUnit\Framework\TestCase;

class HtmlToPlainTextTest extends TestCase
{
    private function convert(string $html): string
    {
        return (new HtmlToPlainText())->convert($html);
    }

    public function test_drops_head_style_and_script(): void
    {
        $html = '<html><head><title>T</title><style>p{color:red}</style></head>'
            . '<body><script>alert(1)</script><p>Hallo Anna</p></body></html>';

        $this->assertSame('Hallo Anna', $this->convert($html));
    }

    public function test_keeps_link_targets(): void
    {
        $text = $this->convert('<p>Bitte <a href="https://catchaguide.de/x?a=1&amp;b=2">hier bestätigen</a>.</p>');

        $this->assertSame('Bitte hier bestätigen (https://catchaguide.de/x?a=1&b=2).', $text);
    }

    public function test_link_whose_label_is_the_url_is_not_duplicated(): void
    {
        $this->assertSame('https://catchaguide.com', $this->convert('<a href="https://catchaguide.com">https://catchaguide.com</a>'));
        $this->assertSame('info@catchaguide.com', $this->convert('<a href="mailto:info@catchaguide.com"></a>'));
    }

    public function test_blocks_become_lines_and_whitespace_collapses(): void
    {
        $html = "<table><tr><td>  Datum: </td><td> 12.10.2026 </td></tr>\n\n\n<tr><td>Gäste:</td><td>2</td></tr></table>"
            . '<p>Zeile&nbsp;1<br>Zeile 2</p><ul><li>Eins</li><li>Zwei</li></ul>';

        $this->assertSame("Datum: 12.10.2026\nGäste: 2\n\nZeile 1\nZeile 2\n\n- Eins\n\n- Zwei", $this->convert($html));
    }
}
