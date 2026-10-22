<?php

namespace Tests\Feature\Admin;

use App\Enums\VoucherDesign;
use App\Models\Employee;
use App\Models\User;
use App\Models\VoucherOrder;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\URL;
use Tests\TestCase;

class VoucherOrdersAdminTest extends TestCase
{
    use DatabaseTransactions;

    protected function setUp(): void
    {
        parent::setUp();

        config(['app.url' => 'http://cag.local']);
        URL::forceRootUrl('http://cag.local');
    }

    private function actingAsEmployee(): void
    {
        $employee = Employee::query()->first();
        if (! $employee) {
            $this->markTestSkipped('No employee available for admin auth.');
        }

        $this->actingAs($employee, 'employees');
    }

    private function order(string $buyerLastName, VoucherDesign $design = VoucherDesign::GuideOwn): VoucherOrder
    {
        $guide = User::factory()->create(['firstname' => 'Gina', 'lastname' => 'Guide']);

        return VoucherOrder::create([
            'voucher_code' => 'CAG-V-'.strtoupper(substr(md5(uniqid('', true)), 0, 6)),
            'guiding_id' => 0,
            'guide_id' => $guide->id,
            'tour_title_snapshot' => 'Admin List Tour',
            'number_of_guests' => 2,
            'price_total' => 400,
            'currency' => 'EUR',
            'recipient_names' => 'Max',
            'buyer_first_name' => 'Bea',
            'buyer_last_name' => $buyerLastName,
            'buyer_email' => strtolower($buyerLastName).'@example.com',
            'buyer_phone_country_code' => '+49',
            'buyer_phone' => '151 2345678',
            'design_type' => $design,
            'language' => 'de',
            'voucher_language' => 'de',
            'valid_until' => now()->addYears(3)->endOfYear(),
            'commission_rate' => 0.12,
            'commission_amount' => 48,
        ]);
    }

    public function test_guests_cannot_see_the_list(): void
    {
        $this->get(route('admin.voucher-orders.index'))->assertRedirect();
    }

    public function test_admin_sees_voucher_orders(): void
    {
        $this->actingAsEmployee();
        $order = $this->order('Vouchbuyer');

        $this->get(route('admin.voucher-orders.index'))
            ->assertOk()
            ->assertSee($order->voucher_code)
            ->assertSee('Bea Vouchbuyer')
            ->assertSee('Gina Guide')
            ->assertSee(route('admin.voucher-orders.index'), false);
    }

    public function test_search_filters_the_list(): void
    {
        $this->actingAsEmployee();
        $match = $this->order('Findme');
        $other = $this->order('Hideme');

        $this->get(route('admin.voucher-orders.index', ['q' => 'Bea Findme']))
            ->assertOk()
            ->assertSee($match->voucher_code)
            ->assertDontSee($other->voucher_code);
    }

    public function test_pdf_link_only_for_orders_with_the_cag_design(): void
    {
        $this->actingAsEmployee();
        $own = $this->order('Ownbuyer', VoucherDesign::GuideOwn);
        $cag = $this->order('Cagbuyer', VoucherDesign::CagStandard);

        $this->get(route('admin.voucher-orders.index'))
            ->assertOk()
            ->assertSee(route('admin.voucher-orders.pdf', $cag), false)
            ->assertDontSee(route('admin.voucher-orders.pdf', $own), false);
    }

    public function test_pdf_for_an_order_without_one_redirects_back(): void
    {
        $this->actingAsEmployee();
        $own = $this->order('Nopdf', VoucherDesign::GuideOwn);

        $this->get(route('admin.voucher-orders.pdf', $own))
            ->assertRedirect(route('admin.voucher-orders.index'))
            ->assertSessionHas('error');
    }
}
