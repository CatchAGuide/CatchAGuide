<?php

namespace Tests\Unit\Support;

use App\Support\PiiMask;
use PHPUnit\Framework\TestCase;

class PiiMaskTest extends TestCase
{
    public function test_email_keeps_first_letter_and_domain(): void
    {
        $this->assertSame('j•••••@example.com', PiiMask::email('jonas.keller@example.com'));
        $this->assertSame('j•••••@example.com', PiiMask::email('j@example.com'), 'length is not revealed');
        $this->assertSame('', PiiMask::email(null));
    }

    public function test_phone_keeps_only_the_last_three_digits(): void
    {
        $this->assertSame('•••••789', PiiMask::phone('+49 151 23456789'));
        $this->assertSame('•••••12', PiiMask::phone('12'));
        $this->assertSame('', PiiMask::phone(''));
    }

    public function test_name_shortens_the_last_name_to_an_initial(): void
    {
        $this->assertSame('Jonas K.', PiiMask::name('Jonas', 'Keller'));
        $this->assertSame('Jonas', PiiMask::name('Jonas', ''));
        $this->assertSame('Ä.', PiiMask::name('', 'Ärger'));
    }
}
