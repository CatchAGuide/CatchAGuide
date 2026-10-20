<?php

namespace Tests\Feature\Checkout;

use App\Enums\GuideStatus;
use App\Enums\VoucherDesign;
use App\Mail\Guest\VoucherPaidGuestMail;
use App\Mail\Guide\VoucherPaidGuideMail;
use App\Models\FishingType;
use App\Models\Guiding;
use App\Models\User;
use App\Models\VoucherOrder;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\URL;
use Tests\TestCase;

/**
 * The guide confirms a voucher order's payment (emailed link or Profile › Gift vouchers); only
 * then is the voucher emailed to the buyer. Also the per-tour voucher preview in the profile.
 */
class VoucherPaymentTest extends TestCase
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

    private function guide(array $overrides = []): User
    {
        return User::factory()->create(array_merge([
            'firstname' => 'Thomas',
            'lastname' => 'Berger',
            'language' => 'de',
            'is_guide' => 1,
            'guide_status' => GuideStatus::VERIFIED,
        ], $overrides))->fresh();
    }

    private function tour(User $guide): Guiding
    {
        $guiding = new Guiding();
        $guiding->forceFill([
            'title' => 'Payment Tour '.uniqid(),
            'slug' => 'payment-tour-'.uniqid(),
            'location' => 'Speyer',
            'status' => 1,
            'max_guests' => 3,
            'duration' => 6,
            'duration_type' => 'hours',
            'price' => 0,
            'price_type' => 'per_person',
            'prices' => json_encode([['person' => 1, 'amount' => 449], ['person' => 2, 'amount' => 898]]),
            'pricing_extra' => json_encode([]),
            'fishing_type_id' => FishingType::query()->value('id'),
            'is_boat' => 1,
            'user_id' => $guide->id,
        ])->save();

        return $guiding->fresh();
    }

    /**
     * An order placed through the voucher checkout, so token, PDF and snapshots are real.
     */
    private function order(?User $guide = null): VoucherOrder
    {
        $guiding = $this->tour($guide ?? $this->guide());

        $this->postJson(route('checkout.voucher.store', ['slug' => $guiding->slug]), [
            'persons' => 2,
            'recipient_names' => 'Max & Anna',
            'sender_name' => 'Lena',
            'personal_message' => 'Happy birthday!',
            'first_name' => 'Lena',
            'last_name' => 'Schmidt',
            'email' => 'lena@example.com',
            'country_code' => '+49',
            'phone' => '151 23456789',
        ])->assertOk();

        return VoucherOrder::where('guiding_id', $guiding->id)->firstOrFail();
    }

    public function test_new_order_is_unpaid_with_a_payment_token_and_no_voucher_sent(): void
    {
        Mail::fake();
        $order = $this->order();

        $this->assertNull($order->paid_at);
        $this->assertMatchesRegularExpression('/^[A-Za-z0-9]{32}$/', $order->payment_token);
        Mail::assertNotSent(VoucherPaidGuestMail::class);
    }

    public function test_payment_link_shows_the_order_and_a_confirm_button_without_confirming(): void
    {
        Mail::fake();
        $order = $this->order();

        $this->get(route('voucher-payment.show', ['token' => $order->payment_token]))
            ->assertOk()
            ->assertHeader('X-Robots-Tag', 'noindex, nofollow')
            ->assertSee($order->voucher_code)
            ->assertSee(route('voucher-payment.confirm', ['token' => $order->payment_token]), false);

        $this->assertNull($order->fresh()->paid_at, 'Opening the link (mail scanners do) must not confirm.');
        $this->get(route('voucher-payment.show', ['token' => str_repeat('x', 32)]))->assertNotFound();
    }

    public function test_confirming_sends_the_voucher_to_the_buyer_and_a_confirmation_to_the_guide_once(): void
    {
        Mail::fake();
        $order = $this->order();
        $url = route('voucher-payment.confirm', ['token' => $order->payment_token]);

        $this->post($url)->assertRedirect(route('voucher-payment.show', ['token' => $order->payment_token]));

        $order->refresh();
        $this->assertNotNull($order->paid_at);
        $this->assertTrue($order->hasPdf());
        Mail::assertSent(VoucherPaidGuestMail::class, fn (VoucherPaidGuestMail $mail) => $mail->hasTo('lena@example.com')
            && $mail->order->is($order)
            && $mail->locale === $order->language);
        Mail::assertSent(VoucherPaidGuideMail::class, fn (VoucherPaidGuideMail $mail) => $mail->hasTo($order->guide->email)
            && $mail->hasBcc('ops@example.com')
            && $mail->locale === 'de');

        // A second click changes nothing and sends nothing.
        $paidAt = $order->paid_at;
        $this->post($url)->assertRedirect();
        $this->assertEquals($paidAt, $order->fresh()->paid_at);
        Mail::assertSent(VoucherPaidGuestMail::class, 1);
        Mail::assertSent(VoucherPaidGuideMail::class, 1);

        $this->get(route('voucher-payment.show', ['token' => $order->payment_token]))
            ->assertOk()
            ->assertSee(__('checkout.voucher.payment.already_title', [], $order->language));
    }

    public function test_confirming_regenerates_a_missing_standard_pdf(): void
    {
        Mail::fake();
        $order = $this->order();
        Storage::disk('local')->delete($order->pdf_path);
        $order->forceFill(['pdf_path' => null])->save();

        $this->post(route('voucher-payment.confirm', ['token' => $order->payment_token]))->assertRedirect();

        $order->refresh();
        $this->assertTrue($order->hasPdf());
        Storage::disk('local')->assertExists($order->pdf_path);
    }

    public function test_guide_confirms_their_own_order_from_the_profile_but_not_someone_elses(): void
    {
        Mail::fake();
        $guide = $this->guide();
        $order = $this->order($guide);

        $this->actingAs($this->guide())
            ->post(route('profile.vouchers.orders.paid', $order))
            ->assertNotFound();
        $this->assertNull($order->fresh()->paid_at);

        $this->actingAs($guide)->get(route('profile.vouchers'))
            ->assertOk()
            ->assertSee($order->voucher_code)
            ->assertSee(route('profile.vouchers.orders.paid', $order), false);

        $this->actingAs($guide)
            ->post(route('profile.vouchers.orders.paid', $order))
            ->assertRedirect(route('profile.vouchers'))
            ->assertSessionHas('message');
        $this->assertNotNull($order->fresh()->paid_at);
        Mail::assertSent(VoucherPaidGuestMail::class, 1);
    }

    public function test_own_design_order_is_confirmed_without_a_pdf(): void
    {
        Mail::fake();
        $order = $this->order($this->guide(['voucher_design' => VoucherDesign::GuideOwn->value]));

        $this->post(route('voucher-payment.confirm', ['token' => $order->payment_token]))->assertRedirect();

        $order->refresh();
        $this->assertNotNull($order->paid_at);
        $this->assertFalse($order->hasPdf());
        Mail::assertSent(VoucherPaidGuestMail::class, fn (VoucherPaidGuestMail $mail) => $mail->hasTo('lena@example.com'));
    }

    public function test_guide_previews_the_voucher_pdf_per_own_tour(): void
    {
        $guide = $this->guide();
        $tour = $this->tour($guide);

        $this->actingAs($guide)->get(route('profile.vouchers'))
            ->assertOk()
            ->assertSee(route('profile.vouchers.preview', $tour->id), false);

        $response = $this->actingAs($guide)->get(route('profile.vouchers.preview', $tour->id));
        $response->assertOk()->assertHeader('Content-Type', 'application/pdf');
        $this->assertStringStartsWith('%PDF', $response->getContent());

        $this->actingAs($this->guide())->get(route('profile.vouchers.preview', $tour->id))->assertNotFound();
    }

    public function test_preview_link_is_hidden_for_guides_with_their_own_design(): void
    {
        $guide = $this->guide(['voucher_design' => VoucherDesign::GuideOwn->value]);
        $tour = $this->tour($guide);

        $this->actingAs($guide)->get(route('profile.vouchers'))
            ->assertOk()
            ->assertDontSee(route('profile.vouchers.preview', $tour->id), false);
    }
}
