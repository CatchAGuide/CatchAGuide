<?php

namespace Tests\Feature\Sales;

use App\Enums\Sales\SalesDocumentOutput;
use App\Enums\Sales\SalesDocumentStatus;
use App\Enums\Sales\SalesEventType;
use App\Enums\Sales\SalesItemType;
use App\Http\Livewire\Admin\SalesDocumentBuilder;
use App\Mail\Sales\SalesDocumentMail;
use App\Models\CustomCampOffer;
use App\Models\SalesDocument;
use App\Services\Sales\SalesDocumentSender;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Mail;
use Livewire\Livewire;
use Tests\TestCase;

/**
 * Admin › Sales › Offers: the builder (Livewire), the list with its row actions and the
 * import of the former camp offers.
 */
class SalesAdminTest extends TestCase
{
    use DatabaseTransactions;
    use SalesFixtures;

    protected function setUp(): void
    {
        parent::setUp();
        $this->setUpSales();
        Mail::fake();
    }

    public function test_admin_pages_require_an_employee(): void
    {
        $this->get(route('admin.sales.offers.index'))->assertRedirect();
    }

    public function test_builder_page_renders_with_the_product_options(): void
    {
        $tour = $this->makeTour();

        $this->actingAs($this->employee, 'employees')
            ->get(route('admin.sales.offers.create'))
            ->assertOk()
            ->assertSeeLivewire(SalesDocumentBuilder::class)
            ->assertSee('Zander-Guiding am Rhein')
            ->assertSee('"id":'.$tour->id, false);
    }

    public function test_builder_prices_a_tour_live_and_sends_the_offer(): void
    {
        $tour = $this->makeTour();
        $this->actingAs($this->employee, 'employees');

        $component = Livewire::test(SalesDocumentBuilder::class)
            ->set('header.first_name', 'Björn')
            ->set('header.travellers', 'Leo, Max')
            ->set('header.email', 'bjoern@example.com')
            ->call('addCard', 'tour')
            ->assertSet('cards.0.persons', 3)
            ->call('selectProduct', 0, $tour->id)
            ->set('cards.0.date', now()->addMonth()->toDateString())
            ->call('toggleExtra', 0, 'e0')
            ->call('toggleExtra', 0, 'e1')
            ->set('cards.0.extras.e1.qty', 2)
            ->call('stepPersons', 0, 1)
            ->assertSet('cards.0.persons', 4)
            ->assertSet('cards.0.extras.e0.qty', 4)
            ->assertSet('cards.0.extras.e1.qty', 2)
            ->assertSet('cards.0.extras.e1.follow', false);

        // 390 (4 persons) + 4 × 15 + 2 × 25
        $component->assertSee('500,00 €');

        $component->call('sendOffer')->assertSet('previewView', 'mail');

        $document = SalesDocument::query()->where('email', 'bjoern@example.com')->firstOrFail();
        $this->assertSame(SalesDocumentStatus::Sent, $document->status);
        $this->assertSame('500.00', (string) $document->total_amount);
        Mail::assertSent(SalesDocumentMail::class);
    }

    public function test_saving_a_draft_repeatedly_never_duplicates_the_trip(): void
    {
        $tour = $this->makeTour();
        $trip = $this->makeTrip();
        $this->actingAs($this->employee, 'employees');

        $component = Livewire::test(SalesDocumentBuilder::class)
            ->set('header.email', 'trip@example.com')
            ->call('addCard', 'tour')
            ->call('selectProduct', 0, $tour->id)
            ->set('cards.0.date', now()->addMonth()->toDateString())
            ->call('toggleExtra', 0, 'e0')
            ->call('addCard', 'trip')
            ->call('selectProduct', 1, $trip->id)
            ->set('cards.1.date', now()->addMonths(2)->toDateString())
            ->call('saveDraft')
            ->call('saveDraft')
            ->set('cards.1.persons', 4)
            ->call('saveDraft');

        $document = SalesDocument::findOrFail($component->get('documentId'));
        $cards = $document->items()->whereNull('parent_item_id')->get();

        $this->assertSame(['tour', 'trip'], $cards->map(fn ($card) => $card->item_type->value)->sort()->values()->all());
        $this->assertSame(3, $document->items()->count()); // tour + its extra + trip

        // Reopening shows each product once.
        Livewire::test(SalesDocumentBuilder::class, ['document' => $document])->assertCount('cards', 2);
    }

    public function test_builder_blocks_sending_without_email_or_lines(): void
    {
        $this->actingAs($this->employee, 'employees');

        Livewire::test(SalesDocumentBuilder::class)
            ->call('sendOffer')
            ->assertSet('noticeType', 'danger');

        $this->assertSame(0, SalesDocument::query()->where('created_by', $this->employee->id)->count());
        Mail::assertNothingSent();
    }

    public function test_builder_camp_card_adds_options_with_stay_defaults(): void
    {
        $camp = $this->makeCamp();
        $this->actingAs($this->employee, 'employees');

        $component = Livewire::test(SalesDocumentBuilder::class)
            ->call('addCard', 'camp')
            ->call('selectProduct', 0, $camp->id)
            ->call('addSub', 0, 'accommodation')
            ->set('cards.0.subs.0.from', '2026-11-10')
            ->set('cards.0.subs.0.to', '2026-11-14')
            ->call('addSub', 0, 'boat')
            ->assertSet('cards.0.subs.1.days', 5);

        // 4 nights × 116 + 5 days × 65
        $component->assertSee('789,00 €');
    }

    public function test_builder_reopens_a_saved_document_and_switching_language_relocalizes(): void
    {
        $document = $this->savedDocument();
        $this->actingAs($this->employee, 'employees');

        Livewire::test(SalesDocumentBuilder::class, ['document' => $document])
            ->assertSet('documentId', $document->id)
            ->assertSet('header.first_name', 'Björn')
            ->assertSet('cards.0.type', 'tour')
            ->set('header.language', 'en')
            ->assertSet('header.language', 'en')
            ->call('saveDraft')
            ->assertSet('noticeType', 'success');

        $this->assertSame('en', $document->refresh()->language);
    }

    public function test_list_filters_and_searches_by_listing_id(): void
    {
        $document = $this->savedDocument();
        $tourId = $document->items->first()->listing_id;

        $this->actingAs($this->employee, 'employees')
            ->get(route('admin.sales.offers.index', ['q' => (string) $tourId]))
            ->assertOk()
            ->assertSee($document->number)
            ->assertSee('Björn Schulte');

        $this->get(route('admin.sales.offers.index', ['status' => 'confirmed', 'q' => $document->number]))
            ->assertOk()
            ->assertDontSee('Björn Schulte');
    }

    public function test_row_actions_duplicate_decline_and_cancel(): void
    {
        $document = $this->savedDocument();
        app(SalesDocumentSender::class)->send($document, SalesDocumentOutput::Offer, $this->employee);
        $this->actingAs($this->employee, 'employees');

        $this->post(route('admin.sales.offers.duplicate', $document))->assertRedirect();
        $copy = SalesDocument::query()->where('id', '>', $document->id)->latest('id')->firstOrFail();
        $this->assertSame(SalesDocumentStatus::Draft, $copy->status);
        $this->assertNotSame($document->public_token, $copy->public_token);
        $this->assertSame((string) $document->total_amount, (string) $copy->total_amount);

        $this->from(route('admin.sales.offers.index'))->post(route('admin.sales.offers.decline', $document))->assertRedirect(route('admin.sales.offers.index'));
        $this->assertSame(SalesDocumentStatus::Declined, $document->refresh()->status);

        $this->post(route('admin.sales.offers.cancel', $document));
        $this->assertSame(SalesDocumentStatus::Cancelled, $document->refresh()->status);
        $this->assertTrue($document->events()->where('type', SalesEventType::Cancelled)->exists());
    }

    public function test_old_offer_sendout_pages_lead_to_the_new_builder(): void
    {
        $this->actingAs($this->employee, 'employees')
            ->get(route('admin.offer-sendout.index'))
            ->assertRedirect(route('admin.sales.offers.index'));
    }

    public function test_import_moves_custom_camp_offers_once_and_keeps_their_prices(): void
    {
        $camp = $this->makeCamp();
        $accommodationId = $camp->accommodations()->first()->id;
        $offer = CustomCampOffer::query()->create([
            'name' => 'Lena - 2026-02-09',
            'status' => CustomCampOffer::STATUS_ACCEPTED,
            'recipient_type' => 'manual',
            'recipient_email' => 'lena@example.com',
            'recipient_name' => 'Lena Schmidt',
            'camp_ids' => [$camp->id],
            'offers' => [[
                'camp_id' => $camp->id,
                'date_from' => '2026-11-10',
                'date_to' => '2026-11-14',
                'number_of_persons' => '2',
                'additional_info' => 'Hund kommt mit',
                'accommodation_prices' => [['id' => (string) $accommodationId, 'title' => 'Lodge', 'price' => 100, 'qty' => 1, 'days' => 4]],
                'boat_prices' => [['id' => '999999', 'title' => 'Old boat', 'price' => 50]],
                'guiding_prices' => [],
            ]],
            'locale' => 'de',
            'sent_at' => now()->subDays(3),
        ]);

        $this->artisan('sales:import-custom-camp-offers')->assertSuccessful();
        $this->artisan('sales:import-custom-camp-offers')->assertSuccessful();

        $documents = SalesDocument::query()->where('legacy_custom_camp_offer_id', $offer->id)->get();
        $this->assertCount(1, $documents);
        $document = $documents->first();

        $this->assertSame(SalesDocumentStatus::Accepted, $document->status);
        $this->assertFalse($document->hasUnseenAcceptance());
        $this->assertSame('Lena', $document->first_name);
        $this->assertSame('Schmidt', $document->last_name);
        $this->assertSame('450.00', (string) $document->total_amount); // 100 × 4 days + 50
        $this->assertStringContainsString('Hund kommt mit', $document->good_to_know);
        $this->assertSame(1, $document->items()->where('item_type', SalesItemType::Camp)->count());
        $this->assertSame(2, $document->items()->whereNotNull('parent_item_id')->count());
        $this->assertTrue($document->events()->where('type', SalesEventType::Imported)->exists());
    }
}
