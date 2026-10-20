<?php

namespace Tests\Feature\Checkout;

use App\Enums\GuideStatus;
use App\Enums\VoucherDesign;
use App\Mail\Guest\VoucherOrderGuestMail;
use App\Mail\Guide\VoucherOrderGuideMail;
use App\Models\FishingType;
use App\Models\Guiding;
use App\Models\User;
use App\Models\VoucherOrder;
use App\Services\HelperService;
use App\Services\Voucher\VoucherPdfGenerator;
use App\Services\Voucher\VoucherValidity;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\URL;
use Tests\TestCase;

class VoucherCheckoutTest extends TestCase
{
    use DatabaseTransactions;

    protected function setUp(): void
    {
        parent::setUp();

        config([
            'app.url' => 'http://localhost',
            'recaptcha.active' => false,
            'mail.admin_email' => 'ops@example.com',
            'vouchers.pdf_disk' => 'local',
        ]);
        URL::forceRootUrl('http://localhost');
        Storage::fake('local');

        $this->withoutMiddleware([
            \Illuminate\Routing\Middleware\ThrottleRequests::class,
            \Illuminate\Routing\Middleware\ThrottleRequestsWithRedis::class,
            \App\Http\Middleware\DDoSProtectionMiddleware::class,
        ]);
    }

    private function guiding(array $overrides = [], array $guide = []): Guiding
    {
        $user = User::factory()->create(array_merge([
            'firstname' => 'Thomas',
            'lastname' => 'Berger',
            'language' => 'de',
            'is_guide' => 1,
            'guide_status' => GuideStatus::VERIFIED,
        ], $guide));

        $guiding = new Guiding();
        $guiding->forceFill(array_merge([
            'title' => 'Voucher Tour '.uniqid(),
            'slug' => 'voucher-tour-'.uniqid(),
            'location' => 'Speyer',
            'status' => 1,
            'max_guests' => 3,
            'duration' => 6,
            'duration_type' => 'hours',
            'price' => 0,
            'price_type' => 'per_person',
            'prices' => json_encode([
                ['person' => 1, 'amount' => 449],
                ['person' => 2, 'amount' => 898],
                ['person' => 3, 'amount' => 1347],
            ]),
            'pricing_extra' => json_encode([['name' => 'Licence', 'price' => 25]]),
            'fishing_type_id' => FishingType::query()->value('id'),
            'user_id' => $user->id,
        ], $overrides))->save();

        return $guiding->fresh();
    }

    private function payload(array $overrides = []): array
    {
        return array_merge([
            'persons' => 2,
            'recipient_names' => 'Max & Anna',
            'sender_name' => 'Lena',
            'personal_message' => 'Happy birthday!',
            'first_name' => 'Lena',
            'last_name' => 'Schmidt',
            'email' => 'lena@example.com',
            'country_code' => '+49',
            'phone' => '151 23456789',
        ], $overrides);
    }

    private function bootConfig(string $html): array
    {
        $this->assertSame(1, preg_match('#<script type="application/json" id="voucher-checkout-config">(.*?)</script>#s', $html, $match));

        return json_decode($match[1], true, flags: JSON_THROW_ON_ERROR);
    }

    public function test_start_carries_the_guest_count_to_the_voucher_checkout(): void
    {
        $guiding = $this->guiding();

        $this->post(route('checkout.voucher.start'), ['guiding_id' => $guiding->id, 'person' => 3])
            ->assertRedirect(route('checkout.voucher.show', ['slug' => $guiding->slug, 'persons' => 3]));
    }

    public function test_checkout_renders_the_voucher_form_without_date_or_extras(): void
    {
        $guiding = $this->guiding();

        $response = $this->get(route('checkout.voucher.show', ['slug' => $guiding->slug, 'persons' => 9]));

        $response->assertOk()
            ->assertSee('x-data="voucherCheckout"', false)
            ->assertSee('name="recipient_names"', false)
            ->assertSee($guiding->title)
            ->assertSee(__('checkout.voucher.no_payment_note'))
            ->assertDontSee('Licence')
            ->assertSee('<meta name="robots" content="noindex,nofollow">', false);

        $config = $this->bootConfig($response->getContent());
        $this->assertSame(3, $config['persons'], 'Guest count is clamped to the tour maximum.');
        $this->assertSame([], $config['pricing']['extras']);
        $this->assertSame(route('checkout.voucher.store', ['slug' => $guiding->slug]), $config['submitUrl']);
    }

    public static function unsellableTours(): array
    {
        return [
            'vouchers switched off' => [['voucher_enabled' => false]],
            'draft' => [['status' => 2]],
            'deactivated' => [['status' => 0]],
        ];
    }

    /**
     * @dataProvider unsellableTours
     */
    public function test_checkout_and_store_404_for_tours_not_sold_as_vouchers(array $tour): void
    {
        Mail::fake();
        $guiding = $this->guiding($tour);

        $this->get(route('checkout.voucher.show', ['slug' => $guiding->slug]))->assertNotFound();
        $this->post(route('checkout.voucher.start'), ['guiding_id' => $guiding->id])->assertNotFound();
        $this->postJson(route('checkout.voucher.store', ['slug' => $guiding->slug]), $this->payload())->assertNotFound();

        $this->assertSame(0, VoucherOrder::where('guiding_id', $guiding->id)->count());
        Mail::assertNothingSent();
    }

    public function test_store_validates_voucher_and_contact_fields(): void
    {
        $guiding = $this->guiding();

        $this->postJson(route('checkout.voucher.store', ['slug' => $guiding->slug]), ['persons' => 1])
            ->assertStatus(422)
            ->assertJsonValidationErrors([
                'recipient_names' => __('checkout.voucher.errors.recipients_required'),
                'first_name', 'last_name', 'email', 'phone', 'country_code',
            ]);

        $this->postJson(route('checkout.voucher.store', ['slug' => $guiding->slug]), $this->payload([
            'persons' => 4,
            'personal_message' => str_repeat('x', 101),
        ]))
            ->assertStatus(422)
            ->assertJsonValidationErrors(['persons', 'personal_message' => __('checkout.voucher.errors.message_too_long')]);
    }

    public function test_personal_message_fits_three_lines_on_the_voucher(): void
    {
        Mail::fake();
        $guiding = $this->guiding();
        $url = route('checkout.voucher.store', ['slug' => $guiding->slug]);

        $this->postJson($url, $this->payload(['personal_message' => "One\nTwo\nThree\nFour"]))
            ->assertStatus(422)
            ->assertJsonValidationErrors(['personal_message' => __('checkout.voucher.errors.message_too_long')]);

        $this->postJson($url, $this->payload(['personal_message' => "One\r\nTwo\r\n".str_repeat('x', 90)]))->assertOk();

        $this->assertSame("One\nTwo\n".str_repeat('x', 90), VoucherOrder::where('guiding_id', $guiding->id)->value('personal_message'));
    }

    public function test_voucher_qr_code_opens_the_clean_tour_url_on_the_order_language_domain(): void
    {
        config([
            'sales_documents.language_domains' => true,
            'cag.en_app_url' => 'https://catchaguide.com',
            'cag.de_app_url' => 'https://catchaguide.de',
        ]);
        $guiding = $this->guiding();
        $order = new VoucherOrder(['guiding_id' => $guiding->id]);
        $path = route('guidings.show', ['slug' => $guiding->slug], false);
        $pdfs = app(VoucherPdfGenerator::class);

        $this->assertSame('https://catchaguide.de'.$path, $pdfs->tourUrl($order, 'de'));
        $this->assertSame('https://catchaguide.com'.$path, $pdfs->tourUrl($order, 'en'));
        $this->assertNull($pdfs->tourUrl(new VoucherOrder(), 'de'));
    }

    public function test_store_creates_the_order_from_server_side_prices_and_snapshots(): void
    {
        Mail::fake();
        $guiding = $this->guiding();

        $response = $this->postJson(route('checkout.voucher.store', ['slug' => $guiding->slug]), $this->payload([
            'price_total' => 1, // ignored: never read from the client
        ]));

        $order = VoucherOrder::where('guiding_id', $guiding->id)->firstOrFail();

        $response->assertOk()->assertJson([
            'success' => true,
            'redirect_url' => route('checkout.voucher.thank-you', ['slug' => $guiding->slug, 'orderId' => $order->id]),
        ]);

        $this->assertMatchesRegularExpression('/^CAG-V-[A-HJKMNP-Z2-9]{6}$/', $order->voucher_code);
        $this->assertSame($guiding->user_id, $order->guide_id);
        $this->assertSame($guiding->title, $order->tour_title_snapshot);
        $this->assertSame(2, $order->number_of_guests);
        $this->assertEquals(898, $order->price_total);
        $this->assertEquals(round((float) app(HelperService::class)->calculateRates(898), 2), $order->commission_amount);
        $this->assertSame('Max & Anna', $order->recipient_names);
        $this->assertSame('Lena', $order->sender_name);
        $this->assertSame(VoucherDesign::CagStandard, $order->design_type);
        $this->assertSame(VoucherValidity::until(now())->toDateString(), $order->valid_until->toDateString());
        $this->assertNull($order->user_id);
    }

    public function test_standard_design_stores_a_pdf_and_mails_buyer_and_guide(): void
    {
        Mail::fake();
        $guiding = $this->guiding();

        $this->withSession(['locale' => 'en'])
            ->postJson(route('checkout.voucher.store', ['slug' => $guiding->slug]), $this->payload())
            ->assertOk();

        $order = VoucherOrder::where('guiding_id', $guiding->id)->firstOrFail();

        $this->assertSame('vouchers/'.$order->voucher_code.'.pdf', $order->pdf_path);
        Storage::disk('local')->assertExists($order->pdf_path);
        $this->assertStringStartsWith('%PDF', Storage::disk('local')->get($order->pdf_path));
        // It's emailed to the guide: fonts are subset (full fonts alone were ~1.5 MB).
        $this->assertLessThan(300 * 1024, Storage::disk('local')->size($order->pdf_path));

        Mail::assertSent(VoucherOrderGuestMail::class, fn (VoucherOrderGuestMail $mail) => $mail->hasTo('lena@example.com')
            && $mail->order->is($order)
            && $mail->locale === $order->language);
        Mail::assertSent(VoucherOrderGuideMail::class, fn (VoucherOrderGuideMail $mail) => $mail->hasTo($guiding->user->email)
            && $mail->hasBcc('ops@example.com')
            && $mail->order->is($order)
            && $mail->locale === 'de');
    }

    public function test_own_design_creates_no_pdf(): void
    {
        Mail::fake();
        $guiding = $this->guiding([], ['voucher_design' => VoucherDesign::GuideOwn->value]);

        $this->postJson(route('checkout.voucher.store', ['slug' => $guiding->slug]), $this->payload())->assertOk();

        $order = VoucherOrder::where('guiding_id', $guiding->id)->firstOrFail();
        $this->assertSame(VoucherDesign::GuideOwn, $order->design_type);
        $this->assertNull($order->pdf_path);
        $this->assertFalse($order->hasPdf());
        Storage::disk('local')->assertDirectoryEmpty('vouchers');
        Mail::assertSent(VoucherOrderGuideMail::class);
    }

    public function test_signed_in_buyer_is_linked_to_the_order(): void
    {
        Mail::fake();
        $guiding = $this->guiding();
        $buyer = User::factory()->create();

        $this->actingAs($buyer)
            ->postJson(route('checkout.voucher.store', ['slug' => $guiding->slug]), $this->payload())
            ->assertOk();

        $this->assertSame($buyer->id, VoucherOrder::where('guiding_id', $guiding->id)->value('user_id'));
    }

    public function test_thank_you_page_is_only_shown_to_the_session_that_ordered(): void
    {
        Mail::fake();
        $guiding = $this->guiding();

        $redirect = $this->postJson(route('checkout.voucher.store', ['slug' => $guiding->slug]), $this->payload())
            ->assertOk()
            ->json('redirect_url');
        $order = VoucherOrder::where('guiding_id', $guiding->id)->firstOrFail();

        $this->get($redirect)
            ->assertOk()
            ->assertSee($order->voucher_code)
            ->assertSee(__('checkout.voucher.success_title'))
            ->assertSee('<meta name="robots" content="noindex,nofollow">', false);

        $this->get(route('checkout.voucher.thank-you', ['slug' => 'other-slug', 'orderId' => $order->id]))->assertNotFound();

        $this->flushSession();
        $this->get($redirect)->assertNotFound();
    }
}
