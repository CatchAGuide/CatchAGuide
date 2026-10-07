<?php

namespace App\Presenters\Sales;

use App\Enums\Sales\SalesDocumentOutput;
use App\Enums\Sales\SalesDocumentStatus;
use App\Models\SalesDocument;
use App\Services\Sales\SalesDocumentCalculator;
use App\Services\Sales\SalesDocumentStateMapper;
use App\Services\Sales\SalesFormat;
use App\Services\Sales\SalesLinks;
use App\Services\Sales\SalesQuote;
use App\Services\Sales\SalesTexts;
use Carbon\CarbonImmutable;

/**
 * View data for the customer page and the offer/confirmation emails. The builder preview
 * passes its unsaved state through present(), saved documents go through forDocument(), so
 * the preview shows exactly what is sent.
 */
class SalesDocumentPresenter
{
    /**
     * CaG product-type colours, mirroring $category-tour / -camp / -trip in
     * resources/sass/settings/_category-colors.scss (emails need literal values).
     * Custom lines have no product type: neutral slate (coral is reserved for actions).
     */
    public const TYPE_COLORS = [
        'tour' => '#313041',
        'camp' => '#0C8A7F',
        'trip' => '#1F6FA8',
        'custom' => '#5A6478',
    ];

    public const STATE_OPEN = 'open';

    public const STATE_ACCEPTED = 'accepted';

    public const STATE_EXPIRED = 'expired';

    public const STATE_CANCELLED = 'cancelled';

    public const STATE_DRAFT = 'draft';

    public function __construct(
        private readonly SalesDocumentCalculator $calculator,
        private readonly SalesDocumentStateMapper $mapper,
        private readonly SalesLinks $links,
        private readonly SalesTexts $texts,
    ) {}

    /**
     * @return array<string, mixed>
     */
    public function forDocument(SalesDocument $document, SalesDocumentOutput $output): array
    {
        $document->loadMissing(['items', 'creator']);

        return $this->present($this->mapper->toHeader($document), $this->mapper->toCards($document), $output, [
            'status' => $document->status,
            'number' => (string) $document->number,
            'token' => $document->public_token,
            'url' => $this->links->customerUrl($document),
            'creator' => $document->creator?->name,
            'accepted_at' => $document->accepted_at,
        ]);
    }

    /**
     * @param  array<string, mixed>  $header
     * @param  list<array<string, mixed>>  $cards
     * @param  array{status?: SalesDocumentStatus, number?: string, token?: string|null, url?: string|null, creator?: string|null, accepted_at?: mixed}  $context
     * @return array<string, mixed>
     */
    public function present(array $header, array $cards, SalesDocumentOutput $output, array $context = []): array
    {
        $locale = in_array($header['language'] ?? null, ['de', 'en'], true) ? $header['language'] : 'de';
        $quote = $this->calculator->calculate($cards, $locale);
        $isOffer = $output === SalesDocumentOutput::Offer;
        $names = array_values(array_filter([trim((string) ($header['first_name'] ?? '')), ...SalesDocumentStateMapper::travellers((string) ($header['travellers'] ?? ''))]));
        $intro = trim((string) ($header[$isOffer ? 'intro_offer' : 'intro_confirmation'] ?? ''));
        $validUntil = SalesFormat::parse($header['valid_until'] ?? null);
        $status = $context['status'] ?? SalesDocumentStatus::Draft;
        $period = $quote->travelFrom !== null
            ? SalesFormat::date($quote->travelFrom, $locale).($quote->travelTo !== $quote->travelFrom ? ' – '.SalesFormat::date($quote->travelTo, $locale) : '')
            : null;
        $copy = fn (string $key, array $replace = []) => __('sales.customer.'.$key, $replace, $locale);

        return [
            'locale' => $locale,
            'output' => $output->value,
            'isOffer' => $isOffer,
            'title' => $copy($isOffer ? 'title_offer' : 'title_confirmation'),
            'greeting' => $this->greeting($names, $locale),
            // Default texts may be edited in Admin › Sales › Texts (spec §8.5).
            'intro' => $intro !== '' ? $intro : $this->texts->get($isOffer ? 'intro_offer' : 'intro_confirmation', $locale),
            'mailText' => $this->texts->get($isOffer ? 'mail_offer' : 'mail_confirmation', $locale),
            'signatureText' => $this->texts->get('signature', $locale),
            'thanks' => $this->texts->get('thanks', $locale),
            // A card without a priced line yet (no product picked) isn't shown to the customer.
            'groups' => array_values(array_map(
                fn (array $group) => $this->group($group, $locale),
                array_filter($quote->groups, fn (array $group) => $group['lines'] !== []),
            )),
            'total' => SalesFormat::money($quote->total, $locale),
            'totalAmount' => $quote->total,
            'period' => $period,
            'notIncluded' => SalesDocumentStateMapper::lines((array) ($header['not_included'] ?? [])),
            // Hosts' cancellation policies, linked from the acceptance checkbox (spec OQ5).
            'policies' => array_values(array_map(
                fn (array $group) => ['title' => $group['title'], 'text' => $group['cancellation_policy']],
                array_filter($quote->groups, fn (array $group) => filled($group['cancellation_policy'] ?? null) && $group['lines'] !== []),
            )),
            'goodToKnow' => trim((string) ($header['good_to_know'] ?? '')),
            'paymentNote' => trim((string) ($header['payment_note'] ?? '')),
            'partners' => $isOffer ? [] : array_map(fn (array $partner) => $partner + [
                'firstName' => strtok($partner['name'], ' ') ?: $partner['name'],
            ], $quote->partners()),
            'validUntil' => $validUntil !== null ? SalesFormat::date($validUntil->toDateString(), $locale) : null,
            'state' => $this->state($status, $validUntil),
            'signature' => trim((string) ($context['creator'] ?? '')),
            'url' => $context['url'] ?? null,
            'token' => $context['token'] ?? null,
            'subject' => $this->subject($output, $quote, $period, (string) ($context['number'] ?? ''), $locale),
            'quote' => $quote,
        ];
    }

    /**
     * @param  list<string>  $names
     */
    private function greeting(array $names, string $locale): string
    {
        if ($names === []) {
            return __('sales.customer.greeting_fallback', [], $locale);
        }

        $parts = [];
        foreach ($names as $index => $name) {
            $parts[] = __($index === 0 ? 'sales.customer.greeting_first' : 'sales.customer.greeting_next', ['name' => $name], $locale);
        }

        return implode(', ', $parts).',';
    }

    /**
     * @param  array<string, mixed>  $group
     * @return array<string, mixed>
     */
    private function group(array $group, string $locale): array
    {
        return [
            'type' => $group['type'],
            'kind' => __('sales.kind.'.$group['type'], [], $locale),
            'color' => self::TYPE_COLORS[$group['type']] ?? self::TYPE_COLORS['custom'],
            'title' => $group['title'],
            'location' => $group['location'],
            'url' => $group['url'],
            'moreLabel' => in_array($group['type'], ['tour', 'camp', 'trip'], true) ? __('sales.customer.more_'.$group['type'], [], $locale) : null,
            'inclusions' => $group['inclusions'],
            'lines' => array_map(fn (array $line) => $this->line($line, (string) $group['title'], $locale), $group['lines']),
            'subtotal' => count($group['lines']) > 1 ? SalesFormat::money($group['subtotal'], $locale) : null,
        ];
    }

    /**
     * A tour/trip/custom card's own line repeats the card title ("Zander-Guiding · 6 Stunden");
     * under the card heading it only shows what is new ("6 Stunden"), or its detail.
     *
     * @param  array<string, mixed>  $line
     * @return array{title: string, detail: string, extra: bool, amount: string}
     */
    private function line(array $line, string $cardTitle, string $locale): array
    {
        $title = (string) $line['title'];
        $detail = (string) $line['detail'];

        if (! $line['extra'] && $cardTitle !== '' && str_starts_with($title, $cardTitle)) {
            $title = ltrim(substr($title, strlen($cardTitle)), " ·");
            if ($title === '') {
                [$title, $detail] = [$detail, ''];
            }
        }

        return [
            'title' => $title,
            'detail' => $detail,
            'extra' => (bool) $line['extra'],
            'amount' => SalesFormat::money($line['total'], $locale),
        ];
    }

    private function state(SalesDocumentStatus $status, ?CarbonImmutable $validUntil): string
    {
        return match (true) {
            $status === SalesDocumentStatus::Cancelled => self::STATE_CANCELLED,
            in_array($status, [SalesDocumentStatus::Accepted, SalesDocumentStatus::Confirmed], true) => self::STATE_ACCEPTED,
            $status === SalesDocumentStatus::Expired,
            $status === SalesDocumentStatus::Declined,
            $status->isAwaitingCustomer() && $validUntil !== null && $validUntil->lt(CarbonImmutable::today()) => self::STATE_EXPIRED,
            $status === SalesDocumentStatus::Draft => self::STATE_DRAFT,
            default => self::STATE_OPEN,
        };
    }

    private function subject(SalesDocumentOutput $output, SalesQuote $quote, ?string $period, string $number, string $locale): string
    {
        $destinations = $quote->destinations();
        $destination = implode(' · ', array_slice($destinations, 0, 2)).(count($destinations) > 2 ? ' +'.(count($destinations) - 2) : '');
        $destination = $destination !== '' ? $destination : __('sales.customer.subject_fallback', [], $locale);
        $subject = $output === SalesDocumentOutput::Offer
            ? __('sales.customer.subject_offer', ['destination' => $destination], $locale)
            : __('sales.customer.subject_confirmation', ['destination' => $destination, 'period' => $period ?? '–'], $locale);

        return $number !== '' ? $subject.' ('.$number.')' : $subject;
    }
}
