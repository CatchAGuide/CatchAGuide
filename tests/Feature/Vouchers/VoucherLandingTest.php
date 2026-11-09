<?php

namespace Tests\Feature\Vouchers;

use App\Enums\VoucherDesign;
use App\Enums\VoucherOrderKind;
use App\Mail\Admin\VoucherRequestAdminMail;
use App\Mail\Guest\VoucherRequestGuestMail;
use App\Models\User;
use App\Models\VoucherOrder;
use App\Services\Finance\FinanceAggregationService;
use App\Services\Voucher\GuideVoucherOrders;
use App\Services\Voucher\VoucherLandingCatalog;
use App\Services\Voucher\VoucherRequestService;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\URL;
use Tests\TestCase;

class VoucherLandingTest extends TestCase
{
    use DatabaseTransactions;

    protected function setUp(): void
    {
        parent::setUp();

        config(['app.url' => 'http://cag.local', 'mail.admin_email' => 'ops@example.com']);
        URL::forceRootUrl('http://cag.local');
        app()->setLocale('de');

        // Fixed inventory, so the tests don't depend on the dev database's tours.
        $this->app->instance(VoucherLandingCatalog::class, new class extends VoucherLandingCatalog
        {
            public function __construct() {}

            public function destinations(): array
            {
                return [
                    ['name' => 'Spanien', 'regions' => [['name' => 'Ebro', 'cities' => ['Mequinenza']]]],
                    ['name' => 'Ungarn', 'regions' => []],
                ];
            }

            public function tiles(): array
            {
                return [['country' => 'Spanien', 'region' => 'Ebro', 'image' => null, 'from_price' => 63, 'species' => 'Wels']];
            }

            public function gallery(): array
            {
                return [];
            }

            public function finalImage(): ?string
            {
                return 'https://cdn.example.com/regions/ebro.webp';
            }

            public function reviews(): array
            {
                return [];
            }
        });
    }

    private function payload(array $overrides = []): array
    {
        return array_merge([
            'country' => 'Spanien',
            'region' => 'Ebro',
            'city' => 'Mequinenza',
            'amount' => '150',
            'recipient' => 'Papa',
            'name' => 'Lena Landing',
            'country_code' => '+49',
            'phone' => '151 2345678',
            'email' => 'lena.landing@example.com',
            'consent' => '1',
        ], $overrides);
    }

    public function test_the_landing_page_renders_the_form_and_live_tiles(): void
    {
        $this->get(route('guidings.voucher'))
            ->assertOk()
            ->assertSee(__('guidings.voucher_landing.hero_title'))
            ->assertSee(route('guidings.voucher.store'), false)
            ->assertSee('voucherLandingSearchPlace', false)
            ->assertSee(__('guidings.voucher_landing.recipient'))
            ->assertSee('Ebro')
            ->assertSee('Angeltouren ab 63 €')
            ->assertSee('https://cdn.example.com/regions/ebro.webp', false)
            ->assertSee('assets/images/vouchers/landing-hero-a.webp', false)
            ->assertDontSee('noindex', false);
    }

    public function test_a_request_is_stored_as_a_requested_voucher_order_and_emailed(): void
    {
        Mail::fake();

        $response = $this->post(route('guidings.voucher.store'), $this->payload());

        $order = VoucherOrder::query()->where('buyer_email', 'lena.landing@example.com')->latest('id')->firstOrFail();
        $response->assertRedirect(route('guidings.voucher.thank-you', ['orderId' => $order->id]).'#gutschein');

        $this->assertSame(VoucherOrderKind::Request, $order->kind);
        $this->assertSame(VoucherOrder::STATUS_OPEN, $order->status);
        $this->assertSame(['Spanien', 'Ebro', 'Mequinenza'], [$order->request_country, $order->request_region, $order->request_city]);
        $this->assertEquals(150, $order->price_total);
        $this->assertSame('Papa', $order->recipient_names);
        $this->assertSame('Lena Landing', $order->buyerName());
        $this->assertNull($order->guiding_id);
        $this->assertNull($order->design_type);
        $this->assertEquals(0, $order->commission_amount);
        $this->assertSame('de', $order->language);

        Mail::assertSent(VoucherRequestGuestMail::class, fn ($mail) => $mail->hasTo('lena.landing@example.com'));
        Mail::assertSent(VoucherRequestAdminMail::class, fn ($mail) => $mail->hasTo('ops@example.com') && $mail->build()->hasReplyTo('lena.landing@example.com'));
    }

    public function test_the_thank_you_page_shows_the_request_and_its_conversion(): void
    {
        Mail::fake();
        config(['services.google_tag_manager.container_id' => 'GTM-TEST']);

        $this->post(route('guidings.voucher.store'), $this->payload(['amount' => 'custom', 'custom_amount' => '220']));
        $order = VoucherOrder::query()->where('buyer_email', 'lena.landing@example.com')->latest('id')->firstOrFail();

        $this->get(route('guidings.voucher.thank-you', ['orderId' => $order->id]))
            ->assertOk()
            ->assertSee(__('guidings.voucher_landing.success_title'))
            ->assertSee('220 €')
            ->assertSee('Angeltour in Ebro, Spanien · Mequinenza')
            ->assertSee('noindex,nofollow', false)
            ->assertSee('"transaction_id":"voucher_request-'.$order->id.'"', false);
    }

    public function test_the_thank_you_page_is_private_to_the_session_that_sent_the_request(): void
    {
        Mail::fake();
        $this->post(route('guidings.voucher.store'), $this->payload());
        $order = VoucherOrder::query()->where('buyer_email', 'lena.landing@example.com')->latest('id')->firstOrFail();

        $this->flushSession();

        $this->get(route('guidings.voucher.thank-you', ['orderId' => $order->id]))->assertNotFound();
    }

    public function test_an_open_destination_is_stored_without_a_place(): void
    {
        Mail::fake();

        $this->post(route('guidings.voucher.store'), $this->payload([
            'country' => VoucherRequestService::OPEN,
            'region' => VoucherRequestService::OPEN,
            'city' => '',
        ]))->assertSessionHasNoErrors();

        $order = VoucherOrder::query()->where('buyer_email', 'lena.landing@example.com')->latest('id')->firstOrFail();
        $this->assertNull($order->request_country);
        $this->assertNull($order->request_region);
        $this->assertSame('', $order->requestDestination());
    }

    public function test_a_google_place_destination_does_not_need_catalog_membership(): void
    {
        Mail::fake();

        $this->post(route('guidings.voucher.store'), $this->payload([
            'country' => 'Schweden',
            'region' => 'Småland',
            'city' => 'Växjö',
            'email' => 'place.one@example.com',
        ]))->assertSessionHasNoErrors();

        $order = VoucherOrder::query()->where('buyer_email', 'place.one@example.com')->latest('id')->firstOrFail();
        $this->assertSame(['Schweden', 'Småland', 'Växjö'], [$order->request_country, $order->request_region, $order->request_city]);

        $this->post(route('guidings.voucher.store'), $this->payload([
            'country' => 'Ungarn',
            'region' => '',
            'city' => '',
            'email' => 'place.two@example.com',
        ]))->assertSessionHasNoErrors();

        $countryOnly = VoucherOrder::query()->where('buyer_email', 'place.two@example.com')->latest('id')->firstOrFail();
        $this->assertSame(['Ungarn', null, null], [$countryOnly->request_country, $countryOnly->request_region, $countryOnly->request_city]);
    }

    public function test_a_destination_is_required_unless_left_open(): void
    {
        $this->post(route('guidings.voucher.store'), $this->payload(['country' => '', 'region' => '', 'city' => '']))
            ->assertSessionHasErrors('country');
    }

    public function test_contact_amount_and_consent_are_required(): void
    {
        $this->post(route('guidings.voucher.store'), $this->payload([
            'name' => '',
            'phone' => 'abc',
            'email' => 'not-an-email',
            'consent' => null,
            'amount' => 'custom',
            'custom_amount' => '10',
        ]))
            ->assertRedirect(route('guidings.voucher').'#gutschein')
            ->assertSessionHasErrors(['name', 'phone', 'email', 'consent', 'custom_amount']);

        $this->post(route('guidings.voucher.store'), $this->payload(['amount' => '999']))
            ->assertSessionHasErrors('amount');

        $this->assertFalse(VoucherOrder::query()->where('buyer_email', 'lena.landing@example.com')->exists());
    }

    public function test_requests_stay_out_of_finance_and_the_guide_lists(): void
    {
        Mail::fake();
        $this->post(route('guidings.voucher.store'), $this->payload());
        $request = VoucherOrder::query()->where('buyer_email', 'lena.landing@example.com')->latest('id')->firstOrFail();

        $guide = User::factory()->create();
        $order = VoucherOrder::create([
            'voucher_code' => 'CAG-V-'.strtoupper(substr(md5(uniqid('', true)), 0, 6)),
            'kind' => VoucherOrderKind::Order,
            'guiding_id' => 0,
            'guide_id' => $guide->id,
            'tour_title_snapshot' => 'Landing Test Tour',
            'number_of_guests' => 2,
            'price_total' => 400,
            'currency' => 'EUR',
            'recipient_names' => 'Max',
            'buyer_first_name' => 'Bea',
            'buyer_last_name' => 'Buyer',
            'buyer_email' => 'bea.buyer@example.com',
            'buyer_phone' => '151 2345678',
            'design_type' => VoucherDesign::GuideOwn,
            'language' => 'de',
            'valid_until' => now()->addYears(3)->endOfYear(),
            'commission_rate' => 0.12,
            'commission_amount' => 48,
        ]);

        $voucherIds = collect(app(FinanceAggregationService::class)->getInvoiceRows(Request::create('/')))
            ->where('source_key', 'voucher')
            ->pluck('id');

        $this->assertTrue($voucherIds->contains($order->id));
        $this->assertFalse($voucherIds->contains($request->id));
        $this->assertSame([$order->id], app(GuideVoucherOrders::class)->forGuide($guide)->pluck('id')->all());
    }

    public function test_both_request_mails_render_in_both_languages(): void
    {
        foreach (['de', 'en'] as $locale) {
            app()->setLocale($locale);

            $guest = VoucherRequestGuestMail::sample();
            $this->assertStringContainsString('150', $guest->render());

            $admin = VoucherRequestAdminMail::sample();
            $this->assertStringContainsString('lena.schmidt@example.com', $admin->render());
        }
    }
}
