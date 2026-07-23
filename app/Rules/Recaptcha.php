<?php

namespace App\Rules;

use App\Services\Recaptcha\RecaptchaVerifier;
use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class Recaptcha implements ValidationRule
{
    public function __construct(
        private ?RecaptchaVerifier $verifier = null,
    ) {
        $this->verifier ??= app(RecaptchaVerifier::class);
    }

    /**
     * Production-only rule list for form validation arrays.
     *
     * @return list<self>
     */
    public static function production(): array
    {
        return app()->environment('production') ? [new self()] : [];
    }

    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if ((string) config('recaptcha.api_secret_key', '') === '') {
            $fail($this->message());

            return;
        }

        $ip = request()?->ip();
        $skip = (array) config('recaptcha.skip_ip', []);

        if ($ip && in_array($ip, $skip, true)) {
            return;
        }

        $response = $this->verifier->verify(is_string($value) ? $value : null, $ip);

        if (!$response->isSuccess()) {
            $fail($this->message());
        }
    }

    private function message(): string
    {
        return __(config('recaptcha.error_message_key', 'validation.recaptcha'));
    }
}
