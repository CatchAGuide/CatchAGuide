<?php

namespace App\Services\Recaptcha;

use ReCaptcha\ReCaptcha;
use ReCaptcha\Response;

class RecaptchaVerifier
{
    public function verify(?string $token, ?string $ip = null): Response
    {
        $secret = (string) config('recaptcha.api_secret_key', '');

        if ($secret === '') {
            // Fail closed if configured as required.
            return new Response(false, ['missing-input-secret']);
        }

        if (!$token) {
            return new Response(false, ['missing-input-response']);
        }

        $verifier = new ReCaptcha($secret);

        return $verifier->verify($token, $ip);
    }
}

