<?php

namespace Tests\Unit\Support;

use PHPUnit\Framework\TestCase;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;

/**
 * A parse error in a lang file only surfaces when a page loads that file in that locale
 * (an unescaped apostrophe in en/admin.php once took down every English admin page).
 */
class LangFilesParseTest extends TestCase
{
    public function test_every_lang_file_parses_and_returns_an_array(): void
    {
        $langPath = dirname(__DIR__, 3).DIRECTORY_SEPARATOR.'resources'.DIRECTORY_SEPARATOR.'lang';
        $files = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($langPath, RecursiveDirectoryIterator::SKIP_DOTS));
        $checked = 0;

        foreach ($files as $file) {
            if ($file->getExtension() !== 'php') {
                continue;
            }

            try {
                $lines = require $file->getPathname();
            } catch (\ParseError $e) {
                $this->fail($file->getPathname().':'.$e->getLine().' '.$e->getMessage());
            }

            $this->assertIsArray($lines, $file->getPathname().' must return an array');
            $checked++;
        }

        $this->assertGreaterThan(0, $checked);
    }
}
