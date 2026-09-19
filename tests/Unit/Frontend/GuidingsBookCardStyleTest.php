<?php

namespace Tests\Unit\Frontend;

use PHPUnit\Framework\TestCase;

class GuidingsBookCardStyleTest extends TestCase
{
    public function test_price_breakdown_stays_on_one_line(): void
    {
        $path = dirname(__DIR__, 3)
            . DIRECTORY_SEPARATOR
            . 'resources'
            . DIRECTORY_SEPARATOR
            . 'sass'
            . DIRECTORY_SEPARATOR
            . 'components'
            . DIRECTORY_SEPARATOR
            . '_guidings-book-card.scss';

        $this->assertFileExists($path);

        $source = (string) file_get_contents($path);

        $this->assertStringContainsString('&__price', $source);
        $this->assertStringContainsString('flex-wrap: nowrap', $source);
        $this->assertStringContainsString('white-space: nowrap', $source);
        $this->assertStringContainsString('&__unit', $source);
        $this->assertStringNotContainsString('flex-wrap: wrap', $source);
    }
}
