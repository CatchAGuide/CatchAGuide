<?php

namespace Tests\Unit\Sales;

use App\Enums\Sales\SalesDocumentStatus;
use App\Models\SalesDocument;
use App\Services\Sales\SalesDocumentStatusFlow;
use App\Services\Sales\SalesFormat;
use Carbon\CarbonImmutable;
use Tests\TestCase;

class SalesDocumentStatusFlowTest extends TestCase
{
    private function document(SalesDocumentStatus $status, ?string $validUntil = null): SalesDocument
    {
        return (new SalesDocument)->forceFill(['status' => $status, 'valid_until' => $validUntil]);
    }

    public function test_sending_the_offer_is_at_least_sent_and_keeps_later_stages(): void
    {
        $flow = new SalesDocumentStatusFlow;

        $this->assertSame(SalesDocumentStatus::Sent, $flow->afterOfferSent(SalesDocumentStatus::Draft));
        $this->assertSame(SalesDocumentStatus::Sent, $flow->afterOfferSent(SalesDocumentStatus::Expired));
        $this->assertSame(SalesDocumentStatus::Sent, $flow->afterOfferSent(SalesDocumentStatus::Cancelled));
        $this->assertSame(SalesDocumentStatus::Viewed, $flow->afterOfferSent(SalesDocumentStatus::Viewed));
        $this->assertSame(SalesDocumentStatus::Accepted, $flow->afterOfferSent(SalesDocumentStatus::Accepted));
        $this->assertSame(SalesDocumentStatus::Confirmed, $flow->afterOfferSent(SalesDocumentStatus::Confirmed));
    }

    public function test_confirmation_is_possible_from_anything_but_cancelled(): void
    {
        $flow = new SalesDocumentStatusFlow;

        foreach (SalesDocumentStatus::cases() as $status) {
            $this->assertSame($status !== SalesDocumentStatus::Cancelled, $flow->canConfirm($this->document($status)), $status->value);
        }
    }

    public function test_only_open_offers_within_validity_can_be_accepted_or_expire(): void
    {
        $flow = new SalesDocumentStatusFlow;
        $today = CarbonImmutable::parse('2026-10-06');

        $this->assertTrue($flow->canAccept($this->document(SalesDocumentStatus::Sent, '2026-10-06'), $today));
        $this->assertTrue($flow->canAccept($this->document(SalesDocumentStatus::Viewed, null), $today));
        $this->assertFalse($flow->canAccept($this->document(SalesDocumentStatus::Viewed, '2026-10-05'), $today));
        $this->assertFalse($flow->canAccept($this->document(SalesDocumentStatus::Draft, '2026-12-01'), $today));
        $this->assertFalse($flow->canAccept($this->document(SalesDocumentStatus::Confirmed, '2026-12-01'), $today));

        $this->assertTrue($flow->shouldExpire($this->document(SalesDocumentStatus::Sent, '2026-10-05'), $today));
        $this->assertFalse($flow->shouldExpire($this->document(SalesDocumentStatus::Accepted, '2026-10-05'), $today));
        $this->assertFalse($flow->shouldExpire($this->document(SalesDocumentStatus::Sent, '2026-10-06'), $today));
    }

    public function test_amounts_typed_by_employees_parse_in_both_notations(): void
    {
        $this->assertSame(340.0, SalesFormat::amount('340'));
        $this->assertSame(340.5, SalesFormat::amount('340,50'));
        $this->assertSame(1290.0, SalesFormat::amount('1.290,00'));
        $this->assertSame(1290.25, SalesFormat::amount('1290.25 €'));
        $this->assertNull(SalesFormat::amount(''));
        $this->assertNull(SalesFormat::amount('abc'));
        $this->assertNull(SalesFormat::amount('-5'));
    }

    public function test_money_and_dates_follow_the_document_language(): void
    {
        $this->assertSame('1.290,00 €', SalesFormat::money(1290, 'de'));
        $this->assertSame('€1,290.00', SalesFormat::money(1290, 'en'));
        $this->assertSame('14.11.2026', SalesFormat::date('2026-11-14', 'de'));
        $this->assertSame('14 Nov 2026', SalesFormat::date('2026-11-14', 'en'));
        $this->assertSame('–', SalesFormat::date(null, 'de'));
    }
}
