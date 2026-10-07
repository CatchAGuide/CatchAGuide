<?php

namespace Tests\Feature\Sales;

use App\Models\Accommodation;
use App\Models\Camp;
use App\Models\Employee;
use App\Models\Guiding;
use App\Models\RentalBoat;
use App\Models\SalesDocument;
use App\Models\Trip;
use App\Models\User;
use App\Services\Sales\SalesCatalog;
use App\Services\Sales\SalesDocumentWriter;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Str;

/**
 * Listings and documents for the sales builder feature tests (run inside DatabaseTransactions).
 */
trait SalesFixtures
{
    protected Employee $employee;

    protected User $partner;

    protected function setUpSales(): void
    {
        config(['app.url' => 'http://cag.local', 'sales_documents.language_domains' => false]);
        URL::forceRootUrl('http://cag.local');

        $this->employee = Employee::factory()->create(['name' => 'Jonas Tusek', 'email' => 'jonas+'.Str::random(6).'@example.com']);
        $this->partner = User::factory()->create([
            'firstname' => 'Tobias',
            'lastname' => 'Brandt',
            'email' => 'tobias+'.Str::random(6).'@example.com',
            'phone' => '+49 170 000 0000',
        ]);
    }

    protected function makeTour(array $attributes = []): Guiding
    {
        $template = Guiding::query()->first();
        if (! $template) {
            $this->markTestSkipped('No guiding available to template a test tour from.');
        }

        $guiding = $template->replicate();
        $guiding->forceFill(array_merge([
            'title' => 'Zander-Guiding am Rhein',
            'slug' => 'zander-test-'.Str::random(8),
            'language' => 'de',
            'status' => 1,
            'city' => 'Düsseldorf',
            'country' => 'Deutschland',
            'duration' => 6,
            'duration_type' => 'half_day',
            'max_guests' => 4,
            'price_type' => 'per_person',
            'price' => 0,
            'prices' => json_encode([
                ['person' => 1, 'amount' => 240], ['person' => 2, 'amount' => 290],
                ['person' => 3, 'amount' => 340], ['person' => 4, 'amount' => 390],
            ]),
            'pricing_extra' => json_encode([
                ['name' => 'Angelerlaubnis', 'price' => 15, 'unit' => 'per_person'],
                ['name' => 'Leihausrüstung', 'price' => 25, 'unit' => 'per_person'],
                ['name' => 'Wathose', 'price' => 10, 'unit' => 'per_item'],
            ]),
            'user_id' => $this->partner->id,
        ], $attributes));
        $guiding->save();

        return $guiding;
    }

    protected function makeCamp(): Camp
    {
        $camp = Camp::query()->create([
            'title' => 'Mörrum Lodge',
            'slug' => 'moerrum-test-'.Str::random(8),
            'description_camp' => 'Camp',
            'description_area' => 'Area',
            'description_fishing' => 'Fishing',
            'location' => 'Mörrum',
            'city' => 'Mörrum',
            'country' => 'schweden',
            'status' => 'active',
            'user_id' => $this->partner->id,
        ]);

        $accommodation = (new Accommodation)->forceFill([
            'user_id' => $this->partner->id, 'title' => 'Lodge Pool 15', 'slug' => 'lodge-'.Str::random(8), 'status' => 'active',
            'location' => 'Mörrum', 'city' => 'Mörrum', 'country' => 'Schweden', 'region' => 'Blekinge', 'accommodation_type' => 'cabin',
            'max_occupancy' => 4,
            'per_person_pricing' => json_encode(['t1' => ['person_count' => 1, 'price_per_night' => 116, 'price_per_week' => null]]),
        ]);
        $accommodation->save();

        $boat = (new RentalBoat)->forceFill([
            'user_id' => $this->partner->id, 'title' => 'Aluminium boat 15 hp', 'slug' => 'boat-'.Str::random(8), 'status' => 'active',
            'location' => 'Mörrum', 'city' => 'Mörrum', 'country' => 'Schweden', 'boat_type' => 'aluminium', 'desc_of_boat' => 'Boat',
            'price_type' => 'per_day', 'prices' => json_encode(['per_day' => 65]), 'max_persons' => 3,
        ]);
        $boat->save();

        $camp->accommodations()->attach($accommodation->id);
        $camp->rentalBoats()->attach($boat->id);

        return $camp;
    }

    protected function makeTrip(): Trip
    {
        $trip = (new Trip)->forceFill([
            'title' => 'Dorsch & Heilbutt-Woche Hitra',
            'slug' => 'hitra-test-'.Str::random(8),
            'location' => 'Hitra',
            'city' => 'Hitra',
            'country' => 'Norwegen',
            'duration_nights' => 7,
            'group_size_min' => 2,
            'group_size_max' => 6,
            'price_per_person' => 1290,
            'included' => [['id' => null, 'name' => 'Ferienhaus'], ['id' => null, 'name' => 'Boot']],
            'status' => 'active',
            'user_id' => $this->partner->id,
        ]);
        $trip->save();

        return $trip;
    }

    /**
     * @return array<string, mixed>
     */
    protected function header(array $overrides = []): array
    {
        return array_merge([
            'recipient_mode' => 'contact',
            'customer_id' => null,
            'first_name' => 'Björn',
            'last_name' => 'Schulte',
            'email' => 'bjoern.schulte@example.com',
            'phone' => '',
            'travellers' => 'Leo, Max',
            'language' => 'de',
            'valid_until' => now()->addDays(14)->toDateString(),
            'intro_offer' => '',
            'intro_confirmation' => '',
            'good_to_know' => 'Treffpunkt am Bootsanleger.',
            'not_included' => ['An- und Abreise', ''],
            'payment_note' => 'Zahlung vor Ort.',
        ], $overrides);
    }

    /**
     * @return array<string, mixed>
     */
    protected function tourCard(Guiding $guiding, array $overrides = []): array
    {
        return array_merge([
            'key' => 'c1',
            'type' => 'tour',
            'listing_id' => $guiding->id,
            'product' => app(SalesCatalog::class)->tour($guiding->id, 'de'),
            'date' => now()->addMonth()->toDateString(),
            'persons' => 3,
            'override' => '',
            'extras' => ['e0' => ['qty' => 3, 'follow' => true]],
            'subs' => [],
        ], $overrides);
    }

    protected function savedDocument(?array $cards = null, array $header = []): SalesDocument
    {
        $cards ??= [$this->tourCard($this->makeTour())];

        return app(SalesDocumentWriter::class)->save(new SalesDocument, $this->header($header), $cards, $this->employee);
    }
}
