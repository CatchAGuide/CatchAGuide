<?php

namespace Tests\Feature\Checkout;

use App\Models\Accommodation;
use App\Models\Camp;
use App\Models\CampVacationBooking;
use App\Models\RentalBoat;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\URL;
use Tests\TestCase;

class CampCheckoutTest extends TestCase
{
    use DatabaseTransactions;

    protected function setUp(): void
    {
        parent::setUp();

        config(['app.url' => 'http://cag.local', 'recaptcha.active' => false]);
        URL::forceRootUrl('http://cag.local');
    }

    public function test_checkout_page_renders_both_layouts_with_the_camp_options(): void
    {
        [$camp, $accommodation, $boat] = $this->makeCampWithOptions();

        $response = $this->get(route('checkout.camp.show', [
            'slug' => $camp->slug,
            'date' => now()->addDays(10)->toDateString(),
            'persons' => 3,
        ]));

        $response->assertOk();
        $response->assertSee('<meta name="robots" content="noindex,nofollow">', false);
        $response->assertSee('x-data="campCheckout"', false);
        $response->assertSee('class="cc-aside"', false);
        $response->assertSee('class="cc-summary__line-label"', false);
        $response->assertSee('class="cc-summary__line-detail"', false);
        $response->assertSee('class="cc-summary__line-amount"', false);
        $response->assertSee('class="cc-summary__total-label"', false);
        $response->assertSee('class="cc-bar"', false);
        $response->assertSee('class="cc-dock"', false);

        $html = $response->getContent();
        $form = strpos($html, 'class="cc-form"');
        $formEnd = strpos($html, '</form>', $form);
        $summary = strpos($html, 'cc-summary--inline');
        $aside = strpos($html, 'class="cc-aside"');
        $captcha = strpos($html, 'class="cc-captcha"');
        $cta = strpos($html, 'class="cc-cta"');
        $dock = strpos($html, 'class="cc-dock"');
        $bar = strpos($html, 'class="cc-bar"');
        $this->assertNotFalse($form);
        $this->assertNotFalse($formEnd);
        $this->assertNotFalse($summary);
        $this->assertNotFalse($aside);
        $this->assertNotFalse($captcha);
        $this->assertNotFalse($cta);
        $this->assertNotFalse($dock);
        $this->assertNotFalse($bar);
        // Price overview stays in the form. The reCAPTCHA is in the summary card, above its submit button.
        $this->assertLessThan($summary, $form);
        $this->assertLessThan($formEnd, $summary);
        $this->assertLessThan($captcha, $formEnd);
        $this->assertLessThan($captcha, $aside);
        $this->assertLessThan($cta, $captcha);
        $this->assertLessThan($dock, $cta);
        $this->assertLessThan($bar, $dock);
        // Mobile bar drops the generic error when the captcha message is already shown.
        // The desktop submit block keeps its own alert.
        $this->assertStringContainsString('x-show="hasErrors && !errors.captcha"', $html);
        $this->assertStringContainsString('class="cc-cta__alert" x-show="hasErrors"', $html);
        $response->assertSee('id="cc-boat"', false);
        $response->assertDontSee('id="cc-tour"', false);
        $response->assertSee(__('checkout.camp.submit'));
        $response->assertSee('class="cc-date"', false);
        $response->assertDontSee('class="cc-date is-empty"', false);
        $response->assertSee('cc-date__glyph', false);
        $response->assertSee('openArrivalPicker', false);
        $response->assertSee(__('checkout.camp.arrival_placeholder'), false);
        $response->assertSee(
            __('checkout.camp.arrival').' <span class="cc-field__optional">'.__('checkout.camp.optional').'</span>',
            false,
        );
        preg_match('/<input[^>]*id="cc-date"[^>]*>/', $html, $dateInput);
        $this->assertArrayHasKey(0, $dateInput);
        $this->assertDoesNotMatchRegularExpression('/\brequired\b/', $dateInput[0]);

        $config = $this->clientConfig($response->getContent());
        $this->assertSame(3, $config['persons']);
        $this->assertSame(now()->addDays(10)->toDateString(), $config['arrivalDate']);
        $this->assertSame($accommodation->id, $config['accommodationId']);
        $this->assertSame($boat->id, $config['pricing']['boats'][0]['id']);
        $this->assertSame(route('checkout.camp.store', $camp->slug), $config['submitUrl']);
    }

    public function test_checkout_page_ignores_invalid_prefill_values(): void
    {
        [$camp] = $this->makeCampWithOptions();

        $response = $this->get(route('checkout.camp.show', [
            'slug' => $camp->slug,
            'date' => '2001-01-01',
            'persons' => 999,
            'nights' => -4,
        ]));

        $config = $this->clientConfig($response->getContent());
        $this->assertNull($config['arrivalDate']);
        $response->assertSee('class="cc-date is-empty"', false);
        $response->assertSee(__('checkout.camp.arrival_placeholder'), false);
        $this->assertSame(20, $config['persons']);
        $this->assertSame(2, $config['nights']); // accommodation minimum stay
    }

    public function test_draft_camp_checkout_redirects_to_the_camp_catalog(): void
    {
        [$camp] = $this->makeCampWithOptions(['status' => 'draft']);

        $this->get(route('checkout.camp.show', $camp->slug))
            ->assertRedirect(route('vacations.camps.index'));
    }

    public function test_submission_stores_a_camp_request_with_a_server_side_estimate(): void
    {
        [$camp, $accommodation, $boat] = $this->makeCampWithOptions();
        $arrival = now()->addDays(20)->toDateString();

        $response = $this->postJson(route('checkout.camp.store', $camp->slug), $this->payload([
            'arrival_date' => $arrival,
            'nights' => 3,
            'persons' => 2,
            'accommodation_id' => $accommodation->id,
            'rental_boat_id' => $boat->id,
            'message' => 'We bring our own rods.',
            // Anything price-like from the client is ignored.
            'estimated_total' => 1,
            'total' => 1,
        ]));

        $response->assertOk()->assertJson(['success' => true]);

        $booking = CampVacationBooking::query()->latest('id')->firstOrFail();
        $this->assertSame(CampVacationBooking::SOURCE_CAMP, $booking->source_type);
        $this->assertSame($camp->id, (int) $booking->source_id);
        $this->assertSame($arrival, $booking->preferred_date->toDateString());
        $this->assertSame(3, $booking->nights);
        $this->assertSame(2, $booking->number_of_persons);
        $this->assertSame($accommodation->id, (int) $booking->accommodation_id);
        $this->assertSame($boat->id, (int) $booking->rental_boat_id);
        // 3 × 120 (2-guest tier) + 3 × 200 (boat per day)
        $this->assertSame('960.00', (string) $booking->estimated_total);
        $this->assertSame('Anna Fischer', $booking->name);
        $this->assertSame(CampVacationBooking::STATUS_OPEN, $booking->status);
        $this->assertStringContainsString('We bring our own rods.', $booking->message);
        $this->assertStringContainsString(__('checkout.camp.summary.arrival').':', $booking->message);
        $this->assertCount(2, $booking->price_breakdown);

        $response->assertJson(['redirect_url' => route('checkout.camp.thank-you', [$camp->slug, $booking->id])]);
    }

    public function test_submission_accepts_a_request_without_an_arrival_date(): void
    {
        [$camp, $accommodation] = $this->makeCampWithOptions();

        $payload = $this->payload([
            'accommodation_id' => $accommodation->id,
            'arrival_date' => '',
        ]);

        $this->postJson(route('checkout.camp.store', $camp->slug), $payload)
            ->assertOk()
            ->assertJson(['success' => true]);

        $booking = CampVacationBooking::query()->latest('id')->firstOrFail();
        $this->assertNull($booking->preferred_date);
        $this->assertStringNotContainsString(__('checkout.camp.summary.arrival').':', $booking->message);

        $this->get(route('checkout.camp.thank-you', [$camp->slug, $booking->id]))
            ->assertOk()
            ->assertSee(__('checkout.camp.success_text_no_date', [
                'email' => '<strong>'.e($booking->email).'</strong>',
            ]), false);
    }

    public function test_submission_rejects_options_from_another_camp(): void
    {
        [$camp] = $this->makeCampWithOptions();
        [, $foreignAccommodation] = $this->makeCampWithOptions();

        $this->postJson(route('checkout.camp.store', $camp->slug), $this->payload([
            'accommodation_id' => $foreignAccommodation->id,
        ]))->assertStatus(422)->assertJsonValidationErrors(['accommodation_id']);
    }

    public function test_submission_enforces_the_accommodation_minimum_stay(): void
    {
        [$camp, $accommodation] = $this->makeCampWithOptions();

        $this->postJson(route('checkout.camp.store', $camp->slug), $this->payload([
            'accommodation_id' => $accommodation->id,
            'nights' => 1,
        ]))->assertStatus(422)->assertJsonValidationErrors(['nights']);
    }

    public function test_submission_validates_date_and_contact(): void
    {
        [$camp, $accommodation] = $this->makeCampWithOptions();

        $this->postJson(route('checkout.camp.store', $camp->slug), [
            'arrival_date' => now()->subDay()->toDateString(),
            'nights' => 3,
            'persons' => 2,
            'accommodation_id' => $accommodation->id,
            'email' => 'not-an-email',
            'country_code' => '+49',
            'phone' => 'abc',
        ])->assertStatus(422)->assertJsonValidationErrors(['arrival_date', 'first_name', 'last_name', 'email', 'phone']);
    }

    public function test_submission_for_a_draft_camp_is_refused(): void
    {
        [$camp, $accommodation] = $this->makeCampWithOptions(['status' => 'draft']);

        $this->postJson(route('checkout.camp.store', $camp->slug), $this->payload([
            'accommodation_id' => $accommodation->id,
        ]))->assertStatus(410)->assertJson(['success' => false]);

        $this->assertSame(0, CampVacationBooking::query()->where('source_id', $camp->id)->count());
    }

    public function test_thank_you_page_is_only_visible_to_the_session_that_sent_the_request(): void
    {
        [$camp, $accommodation] = $this->makeCampWithOptions();

        $this->postJson(route('checkout.camp.store', $camp->slug), $this->payload([
            'accommodation_id' => $accommodation->id,
        ]))->assertOk();

        $booking = CampVacationBooking::query()->latest('id')->firstOrFail();
        $url = route('checkout.camp.thank-you', [$camp->slug, $booking->id]);

        $this->get($url)
            ->assertOk()
            ->assertSee(__('checkout.camp.success_title'))
            ->assertSee('anna@example.com');

        // A fresh session (someone guessing ids) gets a 404, as does a mismatched camp slug.
        $this->flushSession();
        $this->get($url)->assertNotFound();
    }

    public function test_thank_you_page_404s_for_a_different_camp_slug(): void
    {
        [$camp, $accommodation] = $this->makeCampWithOptions();
        [$otherCamp] = $this->makeCampWithOptions();

        $this->postJson(route('checkout.camp.store', $camp->slug), $this->payload([
            'accommodation_id' => $accommodation->id,
        ]))->assertOk();

        $booking = CampVacationBooking::query()->latest('id')->firstOrFail();

        $this->get(route('checkout.camp.thank-you', [$otherCamp->slug, $booking->id]))->assertNotFound();
    }

    public function test_camp_page_booking_ctas_lead_to_the_checkout(): void
    {
        [$camp] = $this->makeCampWithOptions();

        $html = $this->get(route('vacations.camps.show', $camp->slug))->assertOk()->getContent();

        // @json escapes slashes.
        $this->assertStringContainsString(str_replace('/', '\/', route('checkout.camp.show', $camp->slug)), $html);
        $this->assertStringNotContainsString('id="contactModal"', $html);
        $this->assertStringContainsString('id="campGeneralContactModal"', $html);
    }

    public function test_checkout_lives_under_the_checkout_prefix_and_old_urls_redirect(): void
    {
        [$camp] = $this->makeCampWithOptions();

        $this->assertSame('/checkout/camps/'.$camp->slug, route('checkout.camp.show', $camp->slug, false));
        $this->assertSame('/checkout/camps/'.$camp->slug.'/thank-you/5', route('checkout.camp.thank-you', [$camp->slug, 5], false));

        $this->get('/vacations/camps/'.$camp->slug.'/checkout?persons=3&nights=4')
            ->assertStatus(301)
            ->assertRedirect(route('checkout.camp.show', ['slug' => $camp->slug, 'persons' => 3, 'nights' => 4]));
    }

    /**
     * @return array<string, mixed>
     */
    private function payload(array $overrides = []): array
    {
        return array_merge([
            'arrival_date' => now()->addDays(14)->toDateString(),
            'nights' => 3,
            'persons' => 2,
            'first_name' => 'Anna',
            'last_name' => 'Fischer',
            'email' => 'anna@example.com',
            'country_code' => '+49',
            'phone' => '151 2345678',
        ], $overrides);
    }

    /**
     * @return array<string, mixed>
     */
    private function clientConfig(string $html): array
    {
        $this->assertMatchesRegularExpression('/<script type="application\/json" id="camp-checkout-config">(.*?)<\/script>/s', $html);
        preg_match('/<script type="application\/json" id="camp-checkout-config">(.*?)<\/script>/s', $html, $match);

        return json_decode($match[1], true, flags: JSON_THROW_ON_ERROR);
    }

    /**
     * @return array{0: Camp, 1: Accommodation, 2: RentalBoat}
     */
    private function makeCampWithOptions(array $overrides = []): array
    {
        $user = User::query()->first();
        if (! $user) {
            $this->markTestSkipped('No user available to own a test camp.');
        }

        $camp = Camp::query()->create(array_merge([
            'title' => 'Test Checkout Camp',
            'slug' => 'test-checkout-camp-'.uniqid(),
            'description_camp' => 'Camp description',
            'description_area' => 'Area description',
            'description_fishing' => 'Fishing description',
            'location' => 'Test Location',
            'city' => 'Riba-Roja',
            'region' => 'Catalonia',
            'country' => 'Spain',
            'status' => 'active',
            'user_id' => $user->id,
        ], $overrides));

        $accommodation = Accommodation::query()->create([
            'status' => 'active',
            'user_id' => $user->id,
            'title' => 'Test Apartment '.uniqid(),
            'slug' => 'test-apartment-'.uniqid(),
            'location' => 'Test Location',
            'city' => 'Riba-Roja',
            'country' => 'Spain',
            'region' => 'Catalonia',
            'accommodation_type' => 'apartment',
            'max_occupancy' => 4,
            'minimum_stay_nights' => 2,
            'per_person_pricing' => [
                'tier_1' => ['person_count' => 1, 'price_per_night' => 100, 'price_per_week' => null],
                'tier_2' => ['person_count' => 2, 'price_per_night' => 120, 'price_per_week' => null],
            ],
        ]);
        $camp->accommodations()->attach($accommodation->id);

        $boat = RentalBoat::query()->create([
            'status' => 'active',
            'user_id' => $user->id,
            'title' => 'Test Boat '.uniqid(),
            'slug' => 'test-boat-'.uniqid(),
            'location' => 'Test Location',
            'city' => 'Riba-Roja',
            'country' => 'Spain',
            'region' => 'Catalonia',
            'boat_type' => 'aluminium',
            'desc_of_boat' => 'Aluminium boat',
            'max_persons' => 3,
            'price_type' => 'per_day',
            'prices' => ['per_day' => 200, 'per_week' => 1100],
        ]);
        $camp->rentalBoats()->attach($boat->id);

        return [$camp, $accommodation, $boat];
    }
}
