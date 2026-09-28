<?php

namespace Tests\Feature\Recaptcha;

use App\Rules\Recaptcha;
use App\Services\Recaptcha\RecaptchaVerifier;
use Illuminate\Support\Facades\Blade;
use Mockery;
use ReCaptcha\Response;
use Tests\TestCase;

class RecaptchaSwitchTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        config([
            'recaptcha.api_site_key' => 'checkbox-site',
            'recaptcha.api_secret_key' => 'checkbox-secret',
            'recaptcha.invisible_site_key' => '',
            'recaptcha.invisible_secret_key' => '',
        ]);
    }

    public function test_inactive_recaptcha_renders_nothing_and_requires_nothing(): void
    {
        config(['recaptcha.active' => false]);

        $this->assertSame([], Recaptcha::production());
        $this->assertStringNotContainsString('data-recaptcha', Blade::render('<x-recaptcha id="x" />'));
    }

    public function test_active_recaptcha_renders_the_checkbox_widget(): void
    {
        config(['recaptcha.active' => true]);

        $html = Blade::render('<x-recaptcha id="x" />');

        $this->assertCount(1, Recaptcha::production());
        $this->assertStringContainsString('data-sitekey="checkbox-site"', $html);
        $this->assertStringNotContainsString('data-size="invisible"', $html);
    }

    public function test_invisible_widget_falls_back_to_the_checkbox_without_an_invisible_key(): void
    {
        config(['recaptcha.active' => true]);

        $html = Blade::render('<x-recaptcha id="x" invisible />');

        $this->assertStringContainsString('data-sitekey="checkbox-site"', $html);
        $this->assertStringNotContainsString('data-size="invisible"', $html);
    }

    public function test_invisible_widget_uses_the_invisible_key_pair_when_configured(): void
    {
        config([
            'recaptcha.active' => true,
            'recaptcha.invisible_site_key' => 'invisible-site',
            'recaptcha.invisible_secret_key' => 'invisible-secret',
        ]);

        $html = Blade::render('<x-recaptcha id="x" invisible />');
        $this->assertStringContainsString('data-sitekey="invisible-site"', $html);
        $this->assertStringContainsString('data-size="invisible"', $html);

        $verifier = Mockery::mock(RecaptchaVerifier::class);
        $verifier->shouldReceive('verify')->once()->with('token', Mockery::any(), 'invisible-secret')->andReturn(new Response(true));

        $failed = false;
        (new Recaptcha($verifier, invisible: true))->validate('g-recaptcha-response', 'token', function () use (&$failed) {
            $failed = true;
        });

        $this->assertFalse($failed);
    }

    public function test_checkbox_forms_keep_verifying_with_the_regular_secret(): void
    {
        config([
            'recaptcha.active' => true,
            'recaptcha.invisible_site_key' => 'invisible-site',
            'recaptcha.invisible_secret_key' => 'invisible-secret',
        ]);

        $verifier = Mockery::mock(RecaptchaVerifier::class);
        $verifier->shouldReceive('verify')->once()->with('token', Mockery::any(), 'checkbox-secret')->andReturn(new Response(true));

        (new Recaptcha($verifier))->validate('g-recaptcha-response', 'token', fn () => $this->fail('should pass'));

        $this->addToAssertionCount(1);
    }
}
