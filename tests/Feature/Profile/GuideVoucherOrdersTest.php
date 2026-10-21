<?php

namespace Tests\Feature\Profile;

use App\Enums\GuideStatus;
use App\Enums\VoucherDesign;
use App\Models\User;
use App\Models\VoucherOrder;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\URL;
use Tests\TestCase;

class GuideVoucherOrdersTest extends TestCase
{
    use DatabaseTransactions;

    protected function setUp(): void
    {
        parent::setUp();

        config(['app.url' => 'http://localhost', 'vouchers.pdf_disk' => 'local']);
        URL::forceRootUrl('http://localhost');
        Storage::fake('local');
        $this->withoutMiddleware([
            \Illuminate\Routing\Middleware\ThrottleRequests::class,
            \Illuminate\Routing\Middleware\ThrottleRequestsWithRedis::class,
        ]);
    }

    private function guide(): User
    {
        return User::factory()->create([
            'is_guide' => 1,
            'guide_status' => GuideStatus::VERIFIED,
            'language' => 'en',
        ])->fresh();
    }

    private function order(User $guide, array $overrides = []): VoucherOrder
    {
        return VoucherOrder::create(array_merge([
            'voucher_code' => 'CAG-V-'.strtoupper(substr(uniqid(), -6)),
            'guiding_id' => 0,
            'guide_id' => $guide->id,
            'tour_title_snapshot' => 'Snapshot Tour '.uniqid(),
            'number_of_guests' => 2,
            'price_total' => 380,
            'currency' => 'EUR',
            'recipient_names' => 'Max & Anna',
            'buyer_first_name' => 'Bea',
            'buyer_last_name' => 'Buyer',
            'buyer_email' => 'bea.buyer@example.com',
            'buyer_phone_country_code' => '+49',
            'buyer_phone' => '151 2345678',
            'design_type' => VoucherDesign::CagStandard,
            'language' => 'en',
            'voucher_language' => 'en',
            'valid_until' => now()->addYears(3)->endOfYear(),
            'commission_rate' => 0.1,
            'commission_amount' => 38,
        ], $overrides));
    }

    public function test_bookings_list_only_the_guides_own_voucher_orders(): void
    {
        $guide = $this->guide();
        $own = $this->order($guide);
        $other = $this->order($this->guide());

        $this->actingAs($guide)->get(route('profile.bookings'))
            ->assertOk()
            ->assertSee(__('profile.vouchers.bookings_title'))
            ->assertSee($own->voucher_code)
            ->assertSee($own->tour_title_snapshot)
            ->assertSee('bea.buyer@example.com')
            ->assertSee(route('profile.vouchers.orders.pdf', $own), false)
            ->assertDontSee($other->voucher_code);
    }

    public function test_own_design_orders_have_no_pdf_download(): void
    {
        $guide = $this->guide();
        $order = $this->order($guide, ['design_type' => VoucherDesign::GuideOwn]);

        $this->actingAs($guide)->get(route('profile.bookings'))
            ->assertOk()
            ->assertSee($order->voucher_code)
            ->assertSee(__('profile.vouchers.order_design_own'))
            ->assertDontSee(route('profile.vouchers.orders.pdf', $order), false);
    }

    public function test_bookings_hide_the_voucher_section_without_orders(): void
    {
        $this->actingAs($this->guide())->get(route('profile.bookings'))
            ->assertOk()
            ->assertDontSee('id="voucher-orders"', false);
    }

    public function test_guide_downloads_the_stored_voucher_pdf(): void
    {
        $guide = $this->guide();
        $order = $this->order($guide, ['pdf_path' => 'vouchers/stored.pdf']);
        Storage::disk('local')->put('vouchers/stored.pdf', '%PDF-1.7 stored');

        $response = $this->actingAs($guide)->get(route('profile.vouchers.orders.pdf', $order));

        $response->assertOk()->assertHeader('Content-Type', 'application/pdf');
        $this->assertSame('%PDF-1.7 stored', $response->getContent());
        $this->assertStringContainsString('Gift-voucher-'.$order->voucher_code.'.pdf', $response->headers->get('Content-Disposition'));
    }

    public function test_a_missing_voucher_pdf_is_created_on_download(): void
    {
        $guide = $this->guide();
        $order = $this->order($guide);

        $response = $this->actingAs($guide)->get(route('profile.vouchers.orders.pdf', $order));

        $response->assertOk();
        $this->assertStringStartsWith('%PDF', $response->getContent());
        Storage::disk('local')->assertExists('vouchers/'.$order->voucher_code.'.pdf');
    }

    public function test_own_design_download_redirects_back_with_an_error(): void
    {
        $guide = $this->guide();
        $order = $this->order($guide, ['design_type' => VoucherDesign::GuideOwn]);

        $this->actingAs($guide)->get(route('profile.vouchers.orders.pdf', $order))
            ->assertRedirect(route('profile.bookings'))
            ->assertSessionHas('error', __('profile.vouchers.order_pdf_missing'));
    }

    public function test_guides_cannot_download_other_guides_vouchers(): void
    {
        $order = $this->order($this->guide(), ['pdf_path' => 'vouchers/other.pdf']);
        Storage::disk('local')->put('vouchers/other.pdf', '%PDF-1.7 other');

        $this->actingAs($this->guide())->get(route('profile.vouchers.orders.pdf', $order))->assertNotFound();
    }

    public function test_voucher_settings_page_links_to_the_orders_in_bookings(): void
    {
        $guide = $this->guide();
        $order = $this->order($guide);

        $this->actingAs($guide)->get(route('profile.vouchers'))
            ->assertOk()
            ->assertSee(route('profile.bookings').'#voucher-orders', false)
            ->assertDontSee($order->voucher_code);
    }
}
