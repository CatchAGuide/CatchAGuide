<?php

namespace Tests\Unit\Translation;

use Tests\TestCase;

class TranslateHelperTest extends TestCase
{
    public function test_null_input_returns_empty_string_without_calling_google(): void
    {
        $this->assertSame('', translate(null));
    }

    public function test_empty_string_input_returns_empty_string_without_calling_google(): void
    {
        $this->assertSame('', translate(''));
    }

    public function test_whitespace_only_input_is_returned_unchanged_without_calling_google(): void
    {
        $this->assertSame('   ', translate('   '));
    }
}
