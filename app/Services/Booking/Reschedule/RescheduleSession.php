<?php

namespace App\Services\Booking\Reschedule;

use Illuminate\Contracts\Session\Session;

/**
 * Holds the reschedule token server-side once the emailed link has been opened, so the page
 * itself runs on a token-free URL: the secret never reaches analytics, session recordings,
 * browser history sync or Referer headers, and the store request never has to carry it.
 */
class RescheduleSession
{
    private const KEY = 'booking_reschedule';

    public function __construct(
        private readonly Session $session,
    ) {}

    public function remember(string $token, ?string $preferredDate): void
    {
        $this->session->put(self::KEY, [
            'token' => $token,
            'date' => is_string($preferredDate) && preg_match('/^\d{4}-\d{2}-\d{2}$/', $preferredDate) ? $preferredDate : null,
        ]);
        // A new privilege level for this session: rotate the id so a pre-set session can't ride it.
        $this->session->migrate(true);
    }

    public function token(): ?string
    {
        $token = $this->session->get(self::KEY.'.token');

        return is_string($token) && $token !== '' ? $token : null;
    }

    public function preferredDate(): ?string
    {
        return $this->session->get(self::KEY.'.date');
    }

    public function forget(): void
    {
        $this->session->forget(self::KEY);
    }
}
