<?php

namespace App\Rules;

use App\Services\Recaptcha\RecaptchaVerifier;
use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class Recaptcha implements ValidationRule
{
    public function __construct(
        private ?RecaptchaVerifier $verifier = null,
        private bool $invisible = false,
    ) {
        $this->verifier ??= app(RecaptchaVerifier::class);
    }

    /**
     * Rule list for form validation arrays; empty while reCAPTCHA is inactive (config
     * recaptcha.active — production and staging by default).
     *
     * @param  bool  $invisible  The form renders <x-recaptcha invisible />, so verify with the
     *                            invisible key pair when one is configured.
     * @return list<self>
     */
    public static function production(bool $invisible = false): array
    {
        return self::active() ? [new self(invisible: $invisible)] : [];
    }

    public static function active(): bool
    {
        return (bool) config('recaptcha.active', false);
    }

    public static function invisibleConfigured(): bool
    {
        return (string) config('recaptcha.invisible_site_key', '') !== ''
            && (string) config('recaptcha.invisible_secret_key', '') !== '';
    }

    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        $secret = $this->invisible && self::invisibleConfigured()
            ? (string) config('recaptcha.invisible_secret_key')
            : (string) config('recaptcha.api_secret_key', '');

        if ($secret === '') {
            $fail($this->message());

            return;
        }

        $ip = request()?->ip();
        $skip = (array) config('recaptcha.skip_ip', []);

        if ($ip && in_array($ip, $skip, true)) {
            return;
        }

        $response = $this->verifier->verify(is_string($value) ? $value : null, $ip, $secret);

        if (!$response->isSuccess()) {
            $fail($this->message());
        }
    }

    private function message(): string
    {
        return __(config('recaptcha.error_message_key', 'validation.recaptcha'));
    }
}
