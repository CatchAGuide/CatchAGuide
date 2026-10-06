<?php

namespace Tests\Feature\Sales;

use App\Enums\Sales\SalesDocumentOutput;
use App\Enums\Sales\SalesEventType;
use App\Http\Livewire\Admin\SalesDocumentBuilder;
use App\Mail\Sales\SalesDocumentMail;
use App\Models\Accommodation;
use App\Models\SalesDocument;
use App\Models\SalesTextTemplate;
use App\Services\Accommodation\AccommodationDataProcessor;
use App\Services\Sales\SalesCatalog;
use App\Services\Sales\SalesDocumentSender;
use App\Services\Sales\SalesTexts;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Livewire\Livewire;
use Mockery;
use Symfony\Component\Mailer\Exception\TransportException;
use Tests\TestCase;

/**
 * Items of the implementation plan added after the first review: /{lang}/offer URLs (§8.2),
 * host cancellation policies (OQ5), no product subtotals on the customer page (§5.4),
 * editable texts (§8.5), email_bounced (§6.3), autosave (§4.7) and the accommodation
 * price unit (§6.4).
 */
class SalesPlanComplianceTest extends TestCase
{
    use DatabaseTransactions;
    use SalesFixtures;

    protected function setUp(): void
    {
        parent::setUp();
        $this->setUpSales();
        Mail::fake();
    }

    public function test_customer_link_carries_the_language_and_old_links_redirect(): void
    {
        $document = $this->savedDocument();

        $url = (new SalesDocumentMail($document, SalesDocumentOutput::Offer))->render();
        $this->assertStringContainsString('/de/offer/'.$document->public_token, $url);

        $this->get('/offer/'.$document->public_token)->assertRedirect('/de/offer/'.$document->public_token);
        $this->get('/en/offer/'.$document->public_token)->assertRedirect('/de/offer/'.$document->public_token);
        $this->get('/de/offer/'.$document->public_token)->assertOk();
    }

    public function test_accept_box_shows_the_hosts_cancellation_policy_and_no_product_subtotals(): void
    {
        $trip = $this->makeTrip();
        $trip->forceFill(['cancellation_policy' => 'Bis 30 Tage vor Anreise kostenlos.'])->save();
        $tour = $this->makeTour();
        $document = $this->savedDocument([
            $this->tourCard($tour),
            [
                'key' => 'c2', 'type' => 'trip', 'listing_id' => $trip->id,
                'product' => app(SalesCatalog::class)->trip($trip->id, 'de'),
                'date' => now()->addMonths(2)->toDateString(), 'persons' => 2, 'override' => '',
            ],
        ]);
        app(SalesDocumentSender::class)->send($document, SalesDocumentOutput::Offer, $this->employee);

        $this->get('/de/offer/'.$document->public_token)
            ->assertOk()
            ->assertSee('Stornobedingungen des Gastgebers')
            ->assertSee('Stornobedingungen – Dorsch &amp; Heilbutt-Woche Hitra', false)
            ->assertSee('Bis 30 Tage vor Anreise kostenlos.')
            ->assertDontSee('Zwischensumme');

        // The confirmation email keeps its per-product subtotals.
        $this->assertStringContainsString('Zwischensumme', (new SalesDocumentMail($document, SalesDocumentOutput::Confirmation))->render());
    }

    public function test_without_any_host_policy_the_terms_apply(): void
    {
        $document = $this->savedDocument();
        app(SalesDocumentSender::class)->send($document, SalesDocumentOutput::Offer, $this->employee);

        $this->get('/de/offer/'.$document->public_token)->assertSee('Für diese Leistungen gelten die Stornobedingungen aus unseren AGB.');
    }

    public function test_edited_texts_replace_the_defaults_and_empty_restores_them(): void
    {
        $this->actingAs($this->employee, 'employees');

        $this->get(route('admin.sales.texts.index'))->assertOk()->assertSee('Petri Heil und beste Grüße');

        $this->put(route('admin.sales.texts.update'), ['texts' => [
            'signature' => ['de' => 'Kräftige Bisse!', 'en' => ''],
            'mail_offer' => ['de' => 'hier ist Ihr Angebot.', 'en' => ''],
        ]])->assertRedirect(route('admin.sales.texts.index'));

        $this->assertSame(2, SalesTextTemplate::query()->count());

        $document = $this->savedDocument();
        $html = (new SalesDocumentMail($document, SalesDocumentOutput::Offer))->render();
        $this->assertStringContainsString('Kräftige Bisse!', $html);
        $this->assertStringContainsString('hier ist Ihr Angebot.', $html);
        $this->assertSame('Tight lines and best regards', app(SalesTexts::class)->get('signature', 'en'));

        $this->put(route('admin.sales.texts.update'), ['texts' => ['signature' => ['de' => ''], 'mail_offer' => ['de' => '']]]);
        $this->assertSame(0, SalesTextTemplate::query()->count());
    }

    public function test_a_refused_email_is_logged_as_bounced_and_nothing_else_changes(): void
    {
        $document = $this->savedDocument();
        $pending = Mockery::mock();
        $pending->shouldReceive('cc', 'bcc')->andReturnSelf();
        $pending->shouldReceive('send')->andThrow(new TransportException('550 5.1.1 User unknown'));
        Mail::shouldReceive('to')->andReturn($pending);

        try {
            app(SalesDocumentSender::class)->send($document, SalesDocumentOutput::Offer, $this->employee);
            $this->fail('The transport error should be rethrown to the builder.');
        } catch (TransportException) {
        }

        $event = $document->events()->where('type', SalesEventType::EmailBounced)->firstOrFail();
        $this->assertSame('offer', $event->payload['output']);
        $this->assertStringContainsString('User unknown', $event->payload['error']);
        $this->assertSame('bjoern.schulte@example.com', $event->payload['to']);
        $this->assertSame('draft', $document->refresh()->status->value);
        $this->assertSame(0, $document->revisions()->count());
    }

    public function test_autosave_saves_changes_only_once_there_is_something_to_save(): void
    {
        $this->actingAs($this->employee, 'employees');

        $component = Livewire::test(SalesDocumentBuilder::class)->call('autosave')->assertSet('documentId', null);

        $component->set('header.email', 'auto@example.com')->call('autosave');
        $documentId = $component->get('documentId');
        $this->assertNotNull($documentId);
        $this->assertNotNull($component->get('autosavedAt'));

        $updatedAt = SalesDocument::find($documentId)->updated_at;
        $this->travel(1)->minutes();
        $component->call('autosave');
        $this->assertEquals($updatedAt, SalesDocument::find($documentId)->updated_at);
    }

    public function test_accommodation_price_unit_is_saved_from_the_editor(): void
    {
        $data = app(AccommodationDataProcessor::class)->processRequestData(Request::create('/', 'POST', ['price_unit' => 'per_person_night']));
        $this->assertSame('per_person_night', $data['price_unit']);

        $fallback = app(AccommodationDataProcessor::class)->processRequestData(Request::create('/', 'POST', ['price_unit' => 'bogus']));
        $this->assertSame('per_night', $fallback['price_unit']);

        $accommodation = (new Accommodation)->forceFill(['price_unit' => 'per_person_night']);
        $this->assertSame('per_person_night', app(AccommodationDataProcessor::class)->prepareEditFormData($accommodation)['price_unit']);
    }
}
