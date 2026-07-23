<?php

namespace App\Services\Email;

use App\Models\Booking;
use App\Models\EmailLog;
use App\Models\User;
use App\Models\VacationBooking;
use Illuminate\Support\Facades\Log;

/**
 * Resolves the real recipient for email_logs rows where the listener stored "0"
 * (Symfony Mailer getTo() returns Address[] with numeric keys).
 *
 * Uses the same rules as CheckEmailLog callers (target + type + DB / payload).
 */
class EmailLogZeroRecipientRepair
{
    public function resolve(EmailLog $log): ?string
    {
        $type = (string) $log->type;
        $target = $log->target;

        $fromDb = $this->resolveFromDatabase($type, $target);
        if ($fromDb !== null && $fromDb !== '') {
            return $this->normalizeEmail($fromDb);
        }

        $payload = $this->payloadData($log);
        $fromPayload = $this->resolveFromPayload($type, $payload);
        if ($fromPayload !== null && $fromPayload !== '') {
            return $this->normalizeEmail($fromPayload);
        }

        Log::warning('EmailLogZeroRecipientRepair: could not resolve recipient', [
            'email_log_id' => $log->id,
            'type' => $type,
            'target' => $target,
        ]);

        return null;
    }

    private function ceoEmail(): string
    {
        $addr = config('mail.admin_email');

        return is_string($addr) && $addr !== '' ? $addr : 'info@catchaguide.com';
    }

    private function normalizeEmail(string $email): ?string
    {
        $email = trim($email);
        if ($email === '' || $email === '0') {
            return null;
        }

        return filter_var($email, FILTER_VALIDATE_EMAIL) ? $email : null;
    }

    /**
     * @return array<string, mixed>
     */
    private function payloadData(EmailLog $log): array
    {
        $decoded = json_decode($log->additional_info ?? '{}', true);
        if (! is_array($decoded)) {
            return [];
        }
        $inner = $decoded['data'] ?? null;

        return is_array($inner) ? $inner : [];
    }

    private function resolveFromDatabase(string $type, ?string $target): ?string
    {
        $ceo = $this->ceoEmail();

        return match ($type) {
            'ceo_booking_notification',
            'booking_accept',
            'booking_expire_to_ceo',
            'booking_expire_mail_to_ceo',
            'vacation_booking_notification',
            'vacation_booking_admin_mail',
            'guiding_request_mail',
            'contact_mail',
            'mailtoceo',
            'booking_cancel',
            'booking_reject_mail_to_ceo' => $ceo,

            'guest_booking_request' => $this->bookingGuestEmail($this->regularBookingId($target)),

            'guide_booking_request' => $this->guideUserEmailFromGuideBookingTarget($target),

            'guide_reminder',
            'guide_booking_accepted_mail',
            'guide_booking_expired',
            'guide_booking_invoice',
            'guide_reminder_upcoming_tour' => $this->guideEmailFromBookingTarget($target),

            'guest_tour_reminder',
            'booking_accept_mail',
            'booking_reject_mail' => $this->bookingGuestEmail($this->regularBookingId($target)),

            'guest_review' => $this->guestReviewEmail($this->regularBookingId($target)),

            'guest_booking_expired' => $this->guestBookingExpiredEmail($this->regularBookingId($target)),

            'guest_vacation_booking_notification' => $this->vacationGuestEmail($this->regularBookingId($target)),

            default => null,
        };
    }

    /**
     * Second pass: dot-paths and heuristics on serialized MessageSent data.
     *
     * @param  array<string, mixed>  $payload
     */
    private function resolveFromPayload(string $type, array $payload): ?string
    {
        $pathsByType = [
            'search_request_user_mail' => ['mailData.email', 'mailData.user.email'],
            'rating_confirmation' => ['guide.email'],
            'guide_reminder_12hrs' => ['guide.email'],
            'guide_reminder_upcoming_tour' => ['guide.user.email'],
            'customer_contact_mail' => ['email'],
            'vacation_booking_customer_mail' => ['email'],
            'customer_newsletter_mail' => ['email'],
            'customerguidesmail' => ['email'],
            'newsletter' => ['email'],
            'registration_verification' => ['user.email'],
            'automatic_registration_mail' => ['user.email'],
            'booking_confirmation_mail' => ['user.email', 'booking.email'],
            'booking_confirmation_mail_guest' => ['user.email', 'booking.email'],
        ];

        foreach ($pathsByType[$type] ?? [] as $path) {
            $v = $this->dotGet($payload, $path);
            if (is_string($v) && $v !== '') {
                return $v;
            }
        }

        foreach (['guide.email', 'user.email', 'booking.email', 'email'] as $path) {
            $v = $this->dotGet($payload, $path);
            if (is_string($v) && $v !== '') {
                return $v;
            }
        }

        return null;
    }

    /**
     * @param  array<string, mixed>  $data
     */
    private function dotGet(array $data, string $path): mixed
    {
        $cursor = $data;
        foreach (explode('.', $path) as $segment) {
            if (! is_array($cursor) || ! array_key_exists($segment, $cursor)) {
                return null;
            }
            $cursor = $cursor[$segment];
        }

        return $cursor;
    }

    private function regularBookingId(?string $target): ?int
    {
        if ($target === null || $target === '') {
            return null;
        }
        if (preg_match('/^booking_(\d+)$/', $target, $m)) {
            return (int) $m[1];
        }

        return null;
    }

    private function guideUserEmailFromGuideBookingTarget(?string $target): ?string
    {
        if ($target === null || $target === '') {
            return null;
        }
        if (! preg_match('/^guide_(\d+)_booking_(\d+)$/', $target, $m)) {
            return null;
        }
        $userId = (int) $m[1];

        return User::query()->whereKey($userId)->value('email');
    }

    private function guideEmailFromBookingTarget(?string $target): ?string
    {
        $id = $this->regularBookingId($target);
        if ($id === null) {
            return null;
        }
        $booking = Booking::query()
            ->with('guiding.user')
            ->find($id);

        return $booking?->guiding?->user?->email;
    }

    private function bookingGuestEmail(?int $bookingId): ?string
    {
        if ($bookingId === null) {
            return null;
        }
        $booking = Booking::query()->find($bookingId);
        if (! $booking) {
            return null;
        }

        if ($booking->email) {
            return $booking->email;
        }

        return $booking->user?->email;
    }

    private function guestReviewEmail(?int $bookingId): ?string
    {
        if ($bookingId === null) {
            return null;
        }
        $booking = Booking::query()->find($bookingId);

        return $booking?->user?->email;
    }

    private function guestBookingExpiredEmail(?int $bookingId): ?string
    {
        if ($bookingId === null) {
            return null;
        }
        $booking = Booking::query()->find($bookingId);
        if (! $booking) {
            return null;
        }

        $user = User::query()->whereKey($booking->user_id)->first();

        return $user?->email ?? $booking->email;
    }

    private function vacationGuestEmail(?int $vacationBookingId): ?string
    {
        if ($vacationBookingId === null) {
            return null;
        }

        return VacationBooking::query()->whereKey($vacationBookingId)->value('email');
    }
}
