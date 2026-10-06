<?php

namespace Tests\Feature\Sales;

use App\Http\Livewire\Admin\SalesDocumentBuilder;
use App\Models\CampVacationBooking;
use App\Models\SalesDocument;
use App\Models\TripBooking;
use App\Services\Sales\SalesDocumentStateMapper;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Livewire\Livewire;
use Tests\TestCase;

/**
 * "Create offer" from a camp or trip checkout request: a pre-filled draft in the builder.
 */
class SalesFromRequestTest extends TestCase
{
    use DatabaseTransactions;
    use SalesFixtures;

    protected function setUp(): void
    {
        parent::setUp();
        $this->setUpSales();
    }

    private function campRequest(array $overrides = []): CampVacationBooking
    {
        $camp = $this->makeCamp();

        return CampVacationBooking::query()->create(array_merge([
            'source_type' => CampVacationBooking::SOURCE_CAMP,
            'source_id' => $camp->id,
            'preferred_date' => '2026-11-10',
            'nights' => 4,
            'accommodation_id' => $camp->accommodations()->first()->id,
            'rental_boat_id' => $camp->rentalBoats()->first()->id,
            'number_of_persons' => 2,
            'estimated_total' => 724,
            'price_breakdown' => [],
            'name' => 'Lena Schmidt',
            'first_name' => 'Lena',
            'last_name' => 'Schmidt',
            'email' => 'lena@example.com',
            'language' => 'en',
            'phone_country_code' => '+49',
            'phone' => '151 2345678',
            'message' => 'We are two anglers.',
            'status' => CampVacationBooking::STATUS_OPEN,
        ], $overrides));
    }

    public function test_camp_request_becomes_a_prefilled_draft_once(): void
    {
        $request = $this->campRequest();

        $response = $this->actingAs($this->employee, 'employees')
            ->post(route('admin.sales.offers.from-camp-request', $request));

        $document = SalesDocument::query()->where('source_type', SalesDocument::SOURCE_CAMP_REQUEST)->where('source_id', $request->id)->firstOrFail();
        $response->assertRedirect(route('admin.sales.offers.edit', $document));

        $this->assertSame('Lena', $document->first_name);
        $this->assertSame('Schmidt', $document->last_name);
        $this->assertSame('lena@example.com', $document->email);
        $this->assertSame('+49 151 2345678', $document->phone);
        $this->assertSame('en', $document->language);
        $this->assertSame($this->employee->id, $document->created_by);

        $card = app(SalesDocumentStateMapper::class)->toCards($document)[0];
        $this->assertSame('camp', $card['type']);
        $this->assertSame(2, $card['persons']);
        $this->assertSame(['accommodation', 'boat'], array_column($card['subs'], 'kind'));
        $this->assertSame('2026-11-10', $card['subs'][0]['from']);
        $this->assertSame('2026-11-14', $card['subs'][0]['to']);
        $this->assertSame(4, $card['subs'][1]['days']);
        // Current listing prices: 4 nights × 116 + 4 days × 65.
        $this->assertSame('724.00', (string) $document->total_amount);

        $this->assertSame(CampVacationBooking::STATUS_IN_PROCESS, $request->refresh()->status);

        // Converting again opens the same offer.
        $this->post(route('admin.sales.offers.from-camp-request', $request))->assertRedirect(route('admin.sales.offers.edit', $document));
        $this->assertSame(1, SalesDocument::query()->where('source_id', $request->id)->where('source_type', SalesDocument::SOURCE_CAMP_REQUEST)->count());
    }

    public function test_options_the_camp_no_longer_offers_keep_their_requested_price(): void
    {
        $request = $this->campRequest([
            'rental_boat_id' => 99999999,
            'price_breakdown' => [['type' => 'boat', 'id' => 99999999, 'name' => 'Old boat', 'quantity' => 4, 'unit_price' => 50, 'amount' => 200]],
        ]);

        $this->actingAs($this->employee, 'employees')->post(route('admin.sales.offers.from-camp-request', $request));

        $cards = app(SalesDocumentStateMapper::class)->toCards(SalesDocument::query()->where('source_id', $request->id)->where('source_type', SalesDocument::SOURCE_CAMP_REQUEST)->firstOrFail());
        $this->assertSame(['camp', 'custom'], array_column($cards, 'type'));
        $this->assertSame('Old boat', $cards[1]['title']);
        $this->assertSame('200.00', $cards[1]['unit_price']);
    }

    public function test_trip_request_with_a_wish_window_starts_on_its_first_day(): void
    {
        $trip = $this->makeTrip();
        $request = TripBooking::query()->create([
            'source_type' => TripBooking::SOURCE_TRIP,
            'source_id' => $trip->id,
            'preferred_date' => '2027-05-01',
            'preferred_date_to' => '2027-05-20',
            'number_of_persons' => 3,
            'name' => 'Max Muster',
            'email' => 'max@example.com',
            'phone_country_code' => '+49',
            'phone' => '170 1234567',
            'language' => 'de',
            'message' => 'Flexible.',
            'status' => TripBooking::STATUS_OPEN,
        ]);

        $this->actingAs($this->employee, 'employees')->post(route('admin.sales.offers.from-trip-request', $request))->assertRedirect();

        $document = SalesDocument::query()->where('source_type', SalesDocument::SOURCE_TRIP_REQUEST)->where('source_id', $request->id)->firstOrFail();
        $this->assertSame('Max', $document->first_name);
        $this->assertSame('Muster', $document->last_name);
        $card = app(SalesDocumentStateMapper::class)->toCards($document)[0];
        $this->assertSame('trip', $card['type']);
        $this->assertSame('2027-05-01', $card['date']);
        $this->assertSame(3, $card['persons']);
        $this->assertSame('3870.00', (string) $document->total_amount);

        Livewire::test(SalesDocumentBuilder::class, ['document' => $document])
            ->assertSee('Aus Reise-Anfrage #'.$request->id, false)
            ->assertSee('Flexible.');
    }

    public function test_request_lists_offer_create_and_then_open(): void
    {
        $request = $this->campRequest();
        $this->actingAs($this->employee, 'employees');

        $this->get(route('admin.camp-vacation-bookings.index'))
            ->assertOk()
            ->assertSee(route('admin.sales.offers.from-camp-request', $request), false);

        $this->post(route('admin.sales.offers.from-camp-request', $request));
        $document = SalesDocument::query()->where('source_type', SalesDocument::SOURCE_CAMP_REQUEST)->where('source_id', $request->id)->firstOrFail();

        $this->get(route('admin.camp-vacation-bookings.index'))
            ->assertOk()
            ->assertSee($document->number)
            ->assertDontSee(route('admin.sales.offers.from-camp-request', $request), false);

        $this->get(route('admin.trip-bookings.index'))->assertOk();
    }
}
