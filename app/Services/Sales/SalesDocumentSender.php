<?php

namespace App\Services\Sales;

use App\Enums\Sales\SalesDocumentOutput;
use App\Enums\Sales\SalesDocumentStatus;
use App\Enums\Sales\SalesEventType;
use App\Mail\Sales\SalesDocumentMail;
use App\Models\Employee;
use App\Models\SalesDocument;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use RuntimeException;
use Symfony\Component\Mailer\Exception\TransportExceptionInterface;

/**
 * Sends a saved document as offer or booking confirmation (spec §4.7): emails the customer,
 * writes a revision and an event and moves the status. Offers BCC the creator; confirmations
 * CC the partners (guides/hosts) and the creator.
 */
class SalesDocumentSender
{
    public function __construct(
        private readonly SalesDocumentStatusFlow $flow,
        private readonly SalesDocumentWriter $writer,
        private readonly SalesDocumentStateMapper $mapper,
        private readonly SalesDocumentCalculator $calculator,
    ) {}

    /**
     * @return array{to: string, cc: list<string>, bcc: list<string>}
     */
    public function recipients(SalesDocument $document, SalesDocumentOutput $output): array
    {
        $creator = $document->creator?->email;

        if ($output === SalesDocumentOutput::Offer) {
            return ['to' => (string) $document->email, 'cc' => [], 'bcc' => array_values(array_filter([$creator]))];
        }

        $quote = $this->calculator->calculate($this->mapper->toCards($document), $document->locale());
        $partners = array_map(fn (array $partner) => $partner['email'], $quote->partners());

        return [
            'to' => (string) $document->email,
            'cc' => array_values(array_unique(array_filter([...$partners, $creator], fn ($email) => filter_var($email, FILTER_VALIDATE_EMAIL) && $email !== $document->email))),
            'bcc' => [],
        ];
    }

    public function send(SalesDocument $document, SalesDocumentOutput $output, ?Employee $actor): SalesDocument
    {
        if ($output === SalesDocumentOutput::Confirmation && ! $this->flow->canConfirm($document)) {
            throw new RuntimeException(__('sales.builder.cannot_confirm_cancelled'));
        }

        $document->loadMissing(['items', 'creator']);
        $recipients = $this->recipients($document, $output);

        $mail = Mail::to($recipients['to'], $document->fullName() ?: null);
        if ($recipients['cc'] !== []) {
            $mail->cc($recipients['cc']);
        }
        if ($recipients['bcc'] !== []) {
            $mail->bcc($recipients['bcc']);
        }
        try {
            $mail->send(new SalesDocumentMail($document, $output));
        } catch (TransportExceptionInterface $exception) {
            // The mail server refused the message (e.g. unknown recipient): log it on the
            // document as email_bounced (spec §6.3); status and revisions stay unchanged.
            $this->writer->event($document, SalesEventType::EmailBounced, $actor, [
                'output' => $output->value,
                'error' => mb_substr($exception->getMessage(), 0, 500),
            ] + $recipients);

            throw $exception;
        }

        return DB::transaction(function () use ($document, $output, $actor, $recipients) {
            $revision = $document->revisions()->create([
                'revision_no' => (int) $document->revisions()->max('revision_no') + 1,
                'output' => $output,
                'snapshot' => [
                    'header' => $this->mapper->toHeader($document),
                    'cards' => $this->mapper->toCards($document),
                    'total' => (string) $document->total_amount,
                    'recipients' => $recipients,
                ],
                'created_by' => $actor?->id,
            ]);

            if ($output === SalesDocumentOutput::Offer) {
                $document->status = $this->flow->afterOfferSent($document->status);
                $document->offer_sent_at = now();
            } else {
                $document->status = SalesDocumentStatus::Confirmed;
                $document->confirmation_sent_at = now();
            }
            $document->updated_by = $actor?->id ?? $document->updated_by;
            $document->save();

            $this->writer->event(
                $document,
                $output === SalesDocumentOutput::Offer ? SalesEventType::OfferSent : SalesEventType::ConfirmationSent,
                $actor,
                ['revision' => $revision->revision_no] + $recipients,
            );

            return $document;
        });
    }
}
