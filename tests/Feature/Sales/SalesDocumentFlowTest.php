<?php

namespace Tests\Feature\Sales;

use App\Enums\Sales\SalesDocumentOutput;
use App\Enums\Sales\SalesDocumentStatus;
use App\Enums\Sales\SalesEventType;
use App\Enums\Sales\SalesItemType;
use App\Mail\Sales\SalesDocumentMail;
use App\Mail\Sales\SalesOfferAcceptedMail;
use App\Models\SalesDocument;
use App\Services\Sales\SalesCatalog;
use App\Services\Sales\SalesDocumentSender;
use App\Services\Sales\SalesDocumentStateMapper;
use App\Services\Sales\SalesDocumentWriter;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

/**
 * Saving, sending and the customer side of a sales document (spec §4.7, §6, §7, §8).
 */
class SalesDocumentFlowTest extends TestCase
{
    use DatabaseTransactions;
    use SalesFixtures;

    protected function setUp(): void
    {
        parent::setUp();
        $this->setUpSales();
        Mail::fake();
    }

    public function test_catalog_snapshots_a_tour_with_group_prices_extra_units_and_partner(): void
    {
        $tour = $this->makeTour();

        $product = app(SalesCatalog::class)->tour($tour->id, 'de');

        $this->assertSame([1 => 240.0, 2 => 290.0, 3 => 340.0, 4 => 390.0], $product['prices']);
        $this->assertSame(['per_person', 'per_person', 'per_item'], array_column($product['extras'], 'unit'));
        $this->assertSame(['e0', 'e1', 'e2'], array_column($product['extras'], 'key'));
        $this->assertSame('Tobias Brandt', $product['partner']['name']);
        $this->assertStringContainsString('/guidings/offer/'.$tour->slug, $product['url']);
        $this->assertSame('Düsseldorf, Deutschland', $product['location']);
        $this->assertContains(['id' => $tour->id, 'label' => '#'.$tour->id.' · Zander-Guiding am Rhein'], app(SalesCatalog::class)->options('tour'));
    }

    public function test_catalog_snapshots_camp_options_and_trip_details(): void
    {
        $camp = app(SalesCatalog::class)->camp($this->makeCamp()->id, 'de');
        $trip = app(SalesCatalog::class)->trip($this->makeTrip()->id, 'de');

        $this->assertCount(1, $camp['accommodations']);
        $this->assertSame(116.0, array_values($camp['accommodations'])[0]['tiers'][0]['daily']);
        $this->assertSame(65.0, array_values($camp['boats'])[0]['daily']);
        $this->assertSame('Mörrum, Schweden', $camp['location']);
        $this->assertSame(7, $trip['nights']);
        $this->assertSame(1290.0, $trip['price']);
        $this->assertSame(['Ferienhaus', 'Boot'], $trip['inclusions']);
    }

    public function test_saving_writes_number_token_items_totals_and_round_trips_the_state(): void
    {
        $tour = $this->makeTour();
        $cards = [
            $this->tourCard($tour),
            ['key' => 'c2', 'type' => 'custom', 'title' => 'Transfer', 'description' => 'Flughafen', 'date' => '', 'quantity' => 2, 'unit_label' => '', 'unit_price' => '40'],
        ];

        $document = $this->savedDocument($cards);

        $this->assertMatchesRegularExpression('/^CAG-\d{4}-\d{5,}$/', $document->number);
        $this->assertSame(sprintf('CAG-%d-%05d', $document->created_at->year, $document->id), $document->number);
        $this->assertSame(32, strlen($document->public_token));
        $this->assertSame(SalesDocumentStatus::Draft, $document->status);
        $this->assertSame('465.00', (string) $document->total_amount); // 340 + 3×15 + 2×40
        $this->assertSame(['Leo', 'Max'], $document->traveller_names);
        $this->assertSame(['An- und Abreise'], $document->not_included);
        $this->assertSame($this->employee->id, $document->created_by);

        $items = $document->items;
        $this->assertSame([SalesItemType::Tour, SalesItemType::TourExtra, SalesItemType::Custom], $items->pluck('item_type')->all());
        $extra = $items->firstWhere('item_type', SalesItemType::TourExtra);
        $this->assertSame($items->first()->id, $extra->parent_item_id);
        $this->assertTrue($extra->persons_follow_parent);
        $this->assertSame($this->partner->id, $items->first()->partner_id);

        // MySQL's JSON column reorders keys and stores 240.0 as 240; the state is otherwise identical.
        $this->assertEquals($cards, app(SalesDocumentStateMapper::class)->toCards($document));
        $this->assertSame(1, $document->events()->where('type', SalesEventType::Created)->count());
    }

    public function test_manual_price_changes_are_logged(): void
    {
        $tour = $this->makeTour();
        $document = $this->savedDocument([$this->tourCard($tour)]);

        app(SalesDocumentWriter::class)->save($document, $this->header(), [$this->tourCard($tour, ['override' => '300'])], $this->employee);

        $event = $document->events()->where('type', SalesEventType::PriceAdjusted)->first();
        $this->assertNotNull($event);
        $this->assertSame('340.00', $event->payload['calculated']);
        $this->assertSame('300.00', $event->payload['total']);
        $this->assertTrue($document->items()->where('is_adjusted', true)->exists());
    }

    public function test_sending_the_offer_mails_the_customer_bcc_creator_and_marks_it_sent(): void
    {
        $document = $this->savedDocument();

        app(SalesDocumentSender::class)->send($document, SalesDocumentOutput::Offer, $this->employee);

        $document->refresh();
        $this->assertSame(SalesDocumentStatus::Sent, $document->status);
        $this->assertNotNull($document->offer_sent_at);
        $this->assertSame(1, $document->revisions()->count());
        $this->assertSame(SalesDocumentOutput::Offer, $document->revisions()->first()->output);
        $this->assertTrue($document->events()->where('type', SalesEventType::OfferSent)->exists());

        Mail::assertSent(SalesDocumentMail::class, fn (SalesDocumentMail $mail) => $mail->hasTo('bjoern.schulte@example.com')
            && $mail->hasBcc($this->employee->email)
            && ! $mail->hasCc($this->partner->email)
            && $mail->output === SalesDocumentOutput::Offer);
    }

    public function test_sending_the_confirmation_ccs_partners_and_creator_even_without_an_offer(): void
    {
        $document = $this->savedDocument();

        app(SalesDocumentSender::class)->send($document, SalesDocumentOutput::Confirmation, $this->employee);

        $this->assertSame(SalesDocumentStatus::Confirmed, $document->refresh()->status);
        $this->assertNotNull($document->confirmation_sent_at);
        Mail::assertSent(SalesDocumentMail::class, fn (SalesDocumentMail $mail) => $mail->hasCc($this->partner->email)
            && $mail->hasCc($this->employee->email)
            && $mail->output === SalesDocumentOutput::Confirmation);
    }

    public function test_offer_email_renders_in_the_document_language_with_button_and_validity(): void
    {
        $document = $this->savedDocument();

        $html = (new SalesDocumentMail($document, SalesDocumentOutput::Offer))->render();

        $this->assertStringContainsString('Ihr persönliches Angebot', $html);
        $this->assertStringContainsString('Hallo Björn, hallo Leo, hallo Max,', $html);
        $this->assertStringContainsString('Angebot ansehen', $html);
        $this->assertStringContainsString('/'.$document->locale().'/offer/'.$document->public_token, $html);
        $this->assertStringContainsString('Dieses Angebot ist gültig bis', $html);
        $this->assertStringContainsString('Impressum', $html);
        $this->assertStringNotContainsString($document->number, strip_tags(explode('<body', $html)[1]));
    }

    public function test_confirmation_email_lists_products_with_type_extras_and_subtotal(): void
    {
        $document = $this->savedDocument(null, ['language' => 'en']);

        $mail = new SalesDocumentMail($document, SalesDocumentOutput::Confirmation);
        $html = $mail->render();

        $this->assertStringContainsString('Your booking confirmation', $html);
        $this->assertStringContainsString('Your selection &amp; price overview', $html);
        $this->assertStringContainsString('Fishing tour', $html);
        $this->assertStringContainsString('+ Angelerlaubnis', $html);
        $this->assertStringContainsString('Subtotal', $html);
        $this->assertStringContainsString('€385.00', $html);
        $this->assertStringContainsString('View booking', $html);
        $mail->build();
        $this->assertStringContainsString($document->number, $mail->subject);
        $this->assertStringStartsWith('Booking confirmation – Düsseldorf, Deutschland', $mail->subject);
    }

    public function test_customer_page_is_noindex_shows_the_offer_and_marks_the_first_view(): void
    {
        $document = $this->savedDocument();
        app(SalesDocumentSender::class)->send($document, SalesDocumentOutput::Offer, $this->employee);

        $response = $this->get('/'.$document->locale().'/offer/'.$document->public_token);

        $response->assertOk()
            ->assertHeader('X-Robots-Tag', 'noindex, nofollow')
            ->assertSee('noindex, nofollow', false)
            ->assertSee('Ihr persönliches Angebot')
            ->assertSee('Angeltour')
            ->assertSee('An- und Abreise')
            ->assertSee('Treffpunkt am Bootsanleger.')
            ->assertSee('Angebot annehmen')
            ->assertSee('Mehr zur Tour ansehen')
            ->assertDontSee($document->number);

        $this->assertSame(SalesDocumentStatus::Viewed, $document->refresh()->status);
        $this->assertSame(1, $document->events()->where('type', SalesEventType::Viewed)->count());

        $this->get('/'.$document->locale().'/offer/'.$document->public_token)->assertOk();
        $this->assertSame(1, $document->events()->where('type', SalesEventType::Viewed)->count());
    }

    public function test_team_members_opening_the_link_do_not_count_as_viewed(): void
    {
        $document = $this->savedDocument();
        app(SalesDocumentSender::class)->send($document, SalesDocumentOutput::Offer, $this->employee);

        $this->actingAs($this->employee, 'employees')->get('/'.$document->locale().'/offer/'.$document->public_token)->assertOk();

        $this->assertSame(SalesDocumentStatus::Sent, $document->refresh()->status);
    }

    public function test_unknown_tokens_404(): void
    {
        $this->get('/offer/'.str_repeat('a', 32))->assertNotFound();
    }

    public function test_accepting_requires_the_terms_then_records_and_notifies_the_team(): void
    {
        config(['sales_documents.sales_inbox' => 'sales@example.com']);
        $document = $this->savedDocument();
        app(SalesDocumentSender::class)->send($document, SalesDocumentOutput::Offer, $this->employee);

        $this->post('/'.$document->locale().'/offer/'.$document->public_token.'/accept')->assertSessionHasErrors('terms');
        $this->assertSame(SalesDocumentStatus::Sent, $document->refresh()->status);

        $this->post('/'.$document->locale().'/offer/'.$document->public_token.'/accept', ['terms' => '1'])
            ->assertRedirect('/'.$document->locale().'/offer/'.$document->public_token);

        $document->refresh();
        $this->assertSame(SalesDocumentStatus::Accepted, $document->status);
        $this->assertNotNull($document->accepted_at);
        $this->assertTrue($document->hasUnseenAcceptance());
        $event = $document->events()->where('type', SalesEventType::Accepted)->first();
        $this->assertSame(1, $event->payload['revision']);
        Mail::assertSent(SalesOfferAcceptedMail::class, fn ($mail) => $mail->hasTo('sales@example.com') && $mail->hasTo($this->employee->email));

        $this->get('/'.$document->locale().'/offer/'.$document->public_token)->assertSee('Vielen Dank!')->assertDontSee('Verbindlich annehmen');
    }

    public function test_expired_and_cancelled_offers_cannot_be_accepted(): void
    {
        $document = $this->savedDocument(null, ['valid_until' => now()->subDay()->toDateString()]);
        app(SalesDocumentSender::class)->send($document, SalesDocumentOutput::Offer, $this->employee);

        $this->get('/'.$document->locale().'/offer/'.$document->public_token)->assertSee('nicht mehr gültig')->assertDontSee('Verbindlich annehmen');
        $this->post('/'.$document->locale().'/offer/'.$document->public_token.'/accept', ['terms' => '1']);
        $this->assertNotSame(SalesDocumentStatus::Accepted, $document->refresh()->status);

        $this->artisan('sales:expire-offers')->assertSuccessful();
        $this->assertSame(SalesDocumentStatus::Expired, $document->refresh()->status);
        $this->assertTrue($document->events()->where('type', SalesEventType::Expired)->exists());

        $document->forceFill(['status' => SalesDocumentStatus::Cancelled])->save();
        $this->get('/'.$document->locale().'/offer/'.$document->public_token)->assertSee('nicht mehr gültig');
    }

    public function test_a_confirmed_document_shows_the_confirmation_with_the_host_block(): void
    {
        $document = $this->savedDocument();
        app(SalesDocumentSender::class)->send($document, SalesDocumentOutput::Confirmation, $this->employee);

        $this->get('/'.$document->locale().'/offer/'.$document->public_token)
            ->assertOk()
            ->assertSee('Ihre Buchungsbestätigung')
            ->assertSee('Ihr Gastgeber &amp; Guide vor Ort', false)
            ->assertSee('Tobias Brandt')
            ->assertSee('Ab hier übernimmt Tobias')
            ->assertDontSee('Angebot annehmen');
    }

    public function test_cancelled_documents_cannot_be_confirmed(): void
    {
        $document = $this->savedDocument();
        $document->forceFill(['status' => SalesDocumentStatus::Cancelled])->save();

        $this->expectException(\RuntimeException::class);
        app(SalesDocumentSender::class)->send($document, SalesDocumentOutput::Confirmation, $this->employee);
    }
}
