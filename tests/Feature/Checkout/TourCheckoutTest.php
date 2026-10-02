<?php

namespace Tests\Feature\Checkout;

use App\Enums\GuideStatus;
use App\Models\Booking;
use App\Models\FishingType;
use App\Models\Guiding;
use App\Models\User;
use App\Services\Checkout\TourBookingSubmissionService;
use App\Services\Checkout\TourCheckoutQuote;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\URL;
use Mockery;
use Tests\TestCase;

class TourCheckoutTest extends TestCase
{
    use DatabaseTransactions;

    protected function setUp(): void
    {
        parent::setUp();

        config(['app.url' => 'http://localhost']);
        URL::forceRootUrl('http://localhost');

        $this->withoutMiddleware([
            \Illuminate\Routing\Middleware\ThrottleRequests::class,
            \Illuminate\Routing\Middleware\ThrottleRequestsWithRedis::class,
            \App\Http\Middleware\DDoSProtectionMiddleware::class,
        ]);
    }

    private function guiding(array $overrides = []): Guiding
    {
        $guide = User::factory()->create([
            'language' => 'en',
            'bar_allowed' => true,
            'paypal_allowed' => true,
            'is_guide' => 1,
            'guide_status' => GuideStatus::VERIFIED,
        ]);

        $guiding = new Guiding();
        $guiding->forceFill(array_merge([
            'title' => 'Checkout Tour '.uniqid(),
            'slug' => 'checkout-tour-'.uniqid(),
            'location' => 'Speyer',
            'status' => 1,
            'max_guests' => 3,
            'duration' => 2,
            'duration_type' => 'multi_day',
            'price' => 0,
            'price_type' => 'per_person',
            'prices' => json_encode([
                ['person' => 1, 'amount' => 449],
                ['person' => 2, 'amount' => 898],
                ['person' => 3, 'amount' => 1347],
            ]),
            'pricing_extra' => json_encode([
                ['name' => 'Licence', 'price' => 25],
                ['name' => 'Catering', 'price' => 35],
            ]),
            'fishing_type_id' => FishingType::query()->value('id'),
            'user_id' => $guide->id,
        ], $overrides))->save();

        return $guiding;
    }

    private function payload(Guiding $guiding, array $overrides = []): array
    {
        return array_merge([
            'guiding_id' => $guiding->id,
            'persons' => 2,
            'selected_date' => now()->addDays(20)->toDateString(),
            'extras' => [1],
            'first_name' => 'Jonas',
            'last_name' => 'Keller',
            'email' => 'jonas@example.com',
            'country_code' => '+49',
            'phone' => '151 23456789',
        ], $overrides);
    }

    private function expectSubmission(?callable $assert = null): void
    {
        $booking = new Booking();
        $booking->id = 987654;

        $this->mock(TourBookingSubmissionService::class)
            ->shouldReceive('submit')
            ->once()
            ->withArgs(fn (...$args) => $assert === null || $assert(...$args))
            ->andReturn($booking);
    }

    public function test_checkout_without_a_selected_guiding_redirects_to_tours(): void
    {
        $this->get('/checkout')->assertRedirect(route('guidings.index'));
    }

    public function test_checkout_renders_the_tour_summary_and_boot_config(): void
    {
        $guiding = $this->guiding();

        $response = $this->withSession(['guiding_id' => $guiding->id, 'person' => 2])->get('/checkout');

        $response->assertOk()
            ->assertSee('x-data="tourCheckout"', false)
            ->assertSee('id="tour-checkout-config"', false)
            ->assertSee($guiding->title)
            ->assertSee('Licence')
            ->assertSee(__('checkout.tour.payment_cash'))
            ->assertSee('<meta name="robots" content="noindex,nofollow">', false);

        $config = $this->bootConfig($response->getContent());
        $this->assertSame(2, $config['persons']);
        $this->assertSame(3, $config['maxGuests']);
        $this->assertEquals(['1' => 449, '2' => 898, '3' => 1347], $config['pricing']['table']);
        $this->assertSame(route('checkout.store'), $config['submitUrl']);
    }

    public function test_boot_config_cannot_break_out_of_its_script_tag(): void
    {
        $guiding = $this->guiding([
            'pricing_extra' => json_encode([['name' => '</script><script>alert(1)</script>', 'price' => 5]]),
        ]);

        $html = $this->withSession(['guiding_id' => $guiding->id])->get('/checkout')->getContent();

        $this->assertStringNotContainsString('<script>alert(1)</script>', $html);
        $this->assertSame('</script><script>alert(1)</script>', $this->bootConfig($html)['pricing']['extras'][0]['name']);
    }

    public static function unbookableTours(): array
    {
        return [
            'draft' => [['status' => 2], null],
            'deactivated' => [['status' => 0], null],
            'guide not verified' => [[], GuideStatus::PENDING],
        ];
    }

    /**
     * @dataProvider unbookableTours
     */
    public function test_checkout_page_refuses_unpublished_tours(array $tour, ?string $guideStatus): void
    {
        $guiding = $this->guiding($tour);
        if ($guideStatus !== null) {
            $guiding->user->forceFill(['guide_status' => $guideStatus])->save();
        }

        $this->withSession(['guiding_id' => $guiding->id])
            ->get('/checkout')
            ->assertRedirect(route('guidings.index'))
            ->assertSessionMissing('guiding_id');
    }

    /**
     * @dataProvider unbookableTours
     */
    public function test_store_refuses_unpublished_tours(array $tour, ?string $guideStatus): void
    {
        $guiding = $this->guiding($tour);
        if ($guideStatus !== null) {
            $guiding->user->forceFill(['guide_status' => $guideStatus])->save();
        }

        $this->mock(TourBookingSubmissionService::class)->shouldNotReceive('submit');

        $this->postJson(route('checkout.store'), $this->payload($guiding))
            ->assertStatus(422)
            ->assertJsonValidationErrors(['guiding_id' => __('checkout.tour.errors.tour_unavailable')]);
    }

    public function test_store_requires_date_and_contact_details(): void
    {
        $guiding = $this->guiding();

        $this->postJson(route('checkout.store'), ['guiding_id' => $guiding->id, 'persons' => 1])
            ->assertStatus(422)
            ->assertJsonValidationErrors(['selected_date', 'first_name', 'last_name', 'email', 'phone', 'country_code']);
    }

    public function test_store_rejects_guest_counts_above_the_tour_maximum(): void
    {
        $guiding = $this->guiding();

        $this->postJson(route('checkout.store'), $this->payload($guiding, ['persons' => 4]))
            ->assertStatus(422)
            ->assertJsonValidationErrors(['persons']);
    }

    public function test_store_rejects_a_date_the_guide_has_blocked(): void
    {
        $guiding = $this->guiding(['allowed_booking_advance' => 'one_week']);

        $this->postJson(route('checkout.store'), $this->payload($guiding, ['selected_date' => now()->addDays(2)->toDateString()]))
            ->assertStatus(422)
            ->assertJsonValidationErrors(['selected_date']);
    }

    public function test_store_rejects_unknown_extras(): void
    {
        $guiding = $this->guiding();

        $this->postJson(route('checkout.store'), $this->payload($guiding, ['extras' => [5]]))
            ->assertStatus(422)
            ->assertJsonValidationErrors(['extras']);
    }

    public function test_store_quotes_on_the_server_and_ignores_client_prices(): void
    {
        $guiding = $this->guiding();

        $this->expectSubmission(function (Guiding $g, TourCheckoutQuote $quote, string $date, array $contact, ?User $user) use ($guiding) {
            return $g->is($guiding)
                && $quote->persons === 2
                && $quote->total() === 968.0 // 898 + catering 35 × 2
                && $contact['email'] === 'jonas@example.com'
                && $user === null;
        });

        $this->postJson(route('checkout.store'), $this->payload($guiding, ['total_price' => 1, 'price' => 1]))
            ->assertOk()
            ->assertJson(['success' => true, 'redirect_url' => route('checkout.thank-you', [987654])]);
    }

    public function test_store_books_a_signed_in_visitor_as_themselves(): void
    {
        $guiding = $this->guiding();
        $customer = User::factory()->create();

        $this->expectSubmission(fn (Guiding $g, TourCheckoutQuote $q, string $d, array $c, ?User $user) => $user?->is($customer) === true);

        $this->actingAs($customer)
            ->postJson(route('checkout.store'), $this->payload($guiding))
            ->assertOk();
    }

    private function bootConfig(string $html): array
    {
        $this->assertSame(1, preg_match('#<script type="application/json" id="tour-checkout-config">(.*?)</script>#s', $html, $match));

        return json_decode($match[1], true, flags: JSON_THROW_ON_ERROR);
    }

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }
}
