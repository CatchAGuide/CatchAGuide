<?php

namespace App\Services\Email;

use App\Models\Booking;
use App\Models\EmailLog;
use App\Models\User;
use Illuminate\Support\Facades\Log;
use Throwable;

/**
 * Renders the email that was (or would be) sent for an EmailLog row.
 *
 * Priority:
 * 1. Exact HTML snapshot stored at send time (body_html)
 * 2. Re-render the real template with live models from target / payload
 */
class EmailLogPreviewService
{
    /**
     * @return array{html:?string,source:?string,error:?string}
     */
    public function render(EmailLog $log): array
    {
        $stored = $log->storedBodyHtml();
        if ($stored !== null) {
            return [
                'html' => $stored,
                'source' => 'stored',
                'error' => null,
            ];
        }

        try {
            $html = $this->reRenderFromLiveData($log);
            if ($html === null || trim($html) === '') {
                return [
                    'html' => null,
                    'source' => null,
                    'error' => 'Could not reconstruct this email from stored log data.',
                ];
            }

            return [
                'html' => $html,
                'source' => 'rerendered',
                'error' => null,
            ];
        } catch (Throwable $e) {
            Log::warning('EmailLogPreviewService: re-render failed', [
                'email_log_id' => $log->id,
                'type' => $log->type,
                'target' => $log->target,
                'error' => $e->getMessage(),
            ]);

            return [
                'html' => null,
                'source' => null,
                'error' => $e->getMessage(),
            ];
        }
    }

    private function reRenderFromLiveData(EmailLog $log): ?string
    {
        $locale = $log->normalized_language ?: app()->getLocale();
        if (! in_array($locale, ['de', 'en'], true)) {
            $locale = config('app.locale', 'de');
        }

        $previousLocale = app()->getLocale();
        app()->setLocale($locale);

        try {
            $booking = $this->resolveBooking($log);
            $context = $booking ? $this->bookingContext($booking) : null;

            return match ((string) $log->type) {
                'guest_booking_request' => $this->renderGuestBookingRequest($context),
                'guide_booking_request' => $this->renderSimpleBookingView('mails.guide.guide_booking_request', $context),
                'ceo_booking_notification' => $this->renderSimpleBookingView('mails.ceo.request_mail_to_ceo', $context),
                'booking_accept_mail' => $this->renderSimpleBookingView('mails.guest.accepted_mail', $context, [
                    'userICalFeed' => null,
                ]),
                'booking_accept' => $this->renderSimpleBookingView('mails.ceo.accept_mail_to_ceo', $context),
                'guide_booking_accepted_mail',
                'guide_booking_accepted' => $this->renderSimpleBookingView('mails.guide.guide_accepted_mail', $context, [
                    'userICalFeed' => null,
                ]),
                'booking_reject_mail' => $this->renderGuestRejected($context),
                'booking_reject_mail_to_ceo' => $this->renderSimpleBookingView('mails.ceo.reject_mail_to_ceo', $context),
                'guest_booking_expired' => $this->renderSimpleBookingView('mails.guest.guest_expired_booking', $context),
                'guide_booking_expired' => $this->renderSimpleBookingView('mails.guide.guide_expired_booking', $context),
                'booking_expire_mail_to_ceo',
                'booking_expire_to_ceo' => $this->renderSimpleBookingView('mails.ceo.expire_mail_to_ceo', $context),
                'booking_cancel' => $this->renderSimpleBookingView('mails.ceo.cancel_mail_to_ceo', $context),
                'guide_reminder' => $this->renderGuideReminder('mails.guide.guide_reminder', $context, $locale),
                'guide_reminder_12hrs' => $this->renderGuideReminder('mails.guide.guide_reminder_12hrs', $context, $locale),
                'guide_reminder_upcoming_tour' => $this->renderSimpleBookingView('mails.guide.guide_upcoming_tour', $context),
                'guest_tour_reminder' => $this->renderSimpleBookingView('mails.guest.guest_tour_reminder', $context),
                'guest_review' => $this->renderSimpleBookingView('mails.guest.guest_review', $context),
                'guide_booking_invoice',
                'guide_invoice' => $this->renderSimpleBookingView('mails.guide.guide_invoice', $context),
                'rating_confirmation',
                'guide_review_confirmation' => $this->renderFromPayloadOrFail($log, 'mails.guide.review_confirmation_email'),
                default => $this->renderFromCatalogueOrPayload($log, $context),
            };
        } finally {
            app()->setLocale($previousLocale);
        }
    }

    /**
     * @return array{booking:Booking,user:mixed,guiding:mixed,guide:?User}|null
     */
    private function bookingContext(Booking $booking): ?array
    {
        $booking->loadMissing(['guiding.user', 'registeredUser', 'guestUser']);

        $guiding = $booking->guiding;
        $guide = $guiding?->user;
        $user = $booking->user;

        if (! $guiding || ! $guide) {
            return null;
        }

        return [
            'booking' => $booking,
            'user' => $user,
            'guiding' => $guiding,
            'guide' => $guide,
        ];
    }

    private function resolveBooking(EmailLog $log): ?Booking
    {
        $bookingId = $this->bookingIdFromTarget($log->target);
        if ($bookingId === null) {
            $payload = $log->additionalInfoArray()['data'] ?? [];
            if (is_array($payload)) {
                $bookingId = isset($payload['booking']['id']) ? (int) $payload['booking']['id'] : null;
            }
        }

        if ($bookingId === null) {
            return null;
        }

        return Booking::query()
            ->with(['guiding.user', 'registeredUser', 'guestUser'])
            ->find($bookingId);
    }

    private function bookingIdFromTarget(?string $target): ?int
    {
        if ($target === null || $target === '') {
            return null;
        }

        if (preg_match('/(?:^|_)booking_(\d+)$/', $target, $m)) {
            return (int) $m[1];
        }

        if (preg_match('/^admin_booking_(\d+)$/', $target, $m)) {
            return (int) $m[1];
        }

        return null;
    }

    /**
     * @param  array{booking:Booking,user:mixed,guiding:mixed,guide:?User}|null  $context
     * @param  array<string, mixed>  $extra
     */
    private function renderSimpleBookingView(string $view, ?array $context, array $extra = []): ?string
    {
        if ($context === null || ! view()->exists($view)) {
            return null;
        }

        return view($view, array_merge($context, $extra))->render();
    }

    /**
     * @param  array{booking:Booking,user:mixed,guiding:mixed,guide:?User}|null  $context
     */
    private function renderGuestBookingRequest(?array $context): ?string
    {
        if ($context === null) {
            return null;
        }

        $booking = $context['booking'];
        $guide = $context['guide'];
        $guiding = $context['guiding'];

        $guideName = $guide->firstname;
        $textNote = __('emails.guest_booking_request_text_1');
        $textNote = str_replace('[Guide Name]', $guideName, $textNote);
        $textNote = str_replace('[Date]', date('F j, Y', strtotime((string) $booking->book_date)), $textNote);
        $textNote = str_replace('[Location]', (string) $guiding->location, $textNote);

        $alternativeText = __('emails.guest_booking_request_text_5');
        $alternativeText = str_replace('[Guide Name]', $guideName, $alternativeText);

        $booking->guideName = $guideName;

        return view('mails.guest.guest_booking_request', array_merge($context, [
            'textNote' => $textNote,
            'alternativeText' => $alternativeText,
        ]))->render();
    }

    /**
     * @param  array{booking:Booking,user:mixed,guiding:mixed,guide:?User}|null  $context
     */
    private function renderGuestRejected(?array $context): ?string
    {
        if ($context === null) {
            return null;
        }

        $booking = $context['booking'];
        $guide = $context['guide'];
        $guiding = $context['guiding'];

        $guideName = $guide->firstname;
        $text = __('emails.guest_booking_request_cancelled_text_1');
        $text = str_replace('[Guide Name]', $guideName, $text);
        $text = str_replace('[Date]', date('F j, Y', strtotime((string) $booking->book_date)), $text);
        $text = str_replace('[Location]', (string) $guiding->location, $text);

        $booking->guideName = $guideName;
        $booking->textNote = $text;

        $alternativeText = __('emails.guest_booking_request_cancelled_text_4');
        $alternativeText = str_replace('[Guide Name]', $guideName, $alternativeText);
        $booking->alternativeText = $alternativeText;
        $booking->alternativeDates = json_decode((string) $booking->alternative_dates);

        return view('mails.guest.rejected_mail', $context)->render();
    }

    /**
     * @param  array{booking:Booking,user:mixed,guiding:mixed,guide:?User}|null  $context
     */
    private function renderGuideReminder(string $view, ?array $context, string $locale): ?string
    {
        if ($context === null || ! view()->exists($view)) {
            return null;
        }

        $guide = $context['guide'];

        return view($view, [
            'guide' => $guide,
            'booking' => $context['booking'],
            'guideName' => $guide->name ?? $guide->firstname,
            'language' => $locale,
            'type' => null,
            'target' => null,
        ])->render();
    }

    /**
     * @param  array{booking:Booking,user:mixed,guiding:mixed,guide:?User}|null  $context
     */
    private function renderFromCatalogueOrPayload(EmailLog $log, ?array $context): ?string
    {
        $templateKey = $log->templateKey();
        if ($templateKey) {
            $view = config("email_templates.templates.{$templateKey}.view");
            if (is_string($view) && view()->exists($view) && $context !== null) {
                return view($view, $context)->render();
            }
        }

        return $this->renderFromPayloadOrFail($log, null);
    }

    private function renderFromPayloadOrFail(EmailLog $log, ?string $fallbackView): ?string
    {
        $payload = $log->additionalInfoArray()['data'] ?? null;
        if (! is_array($payload)) {
            return null;
        }

        $mailableClass = $payload['__laravel_mailable'] ?? null;
        if (is_string($mailableClass) && class_exists($mailableClass)) {
            // Prefer live booking re-render via known types; mailable reconstruction
            // from arrays is unreliable. If we still have a catalogue view, use it.
        }

        $view = $fallbackView;
        if ($view === null) {
            $templateKey = $log->templateKey();
            $view = $templateKey
                ? config("email_templates.templates.{$templateKey}.view")
                : null;
        }

        if (! is_string($view) || ! view()->exists($view)) {
            return null;
        }

        // Only pass scalar / array payload keys that look like view data — not queue junk.
        $viewData = array_intersect_key($payload, array_flip([
            'booking', 'user', 'guiding', 'guide', 'email', 'name', 'mailData',
            'textNote', 'alternativeText', 'guideName', 'language', 'rating',
        ]));

        // Arrays from json_encode are not models — skip if booking is only an array
        // and we have no live context; Blade often calls model methods.
        if (isset($viewData['booking']) && is_array($viewData['booking'])) {
            return null;
        }

        return view($view, $viewData)->render();
    }
}
