<?php

namespace Tests\Feature\Admin;

use App\Enums\VoucherDesign;
use App\Models\Employee;
use App\Models\FinanceItem;
use App\Models\User;
use App\Models\VoucherOrder;
use App\Services\Finance\FinanceAggregationService;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\URL;
use Tests\TestCase;

class VoucherFinanceTest extends TestCase
{
    use DatabaseTransactions;

    protected function setUp(): void
    {
        parent::setUp();

        config(['app.url' => 'http://cag.local']);
        URL::forceRootUrl('http://cag.local');
    }

    private function order(): VoucherOrder
    {
        $guide = User::factory()->create(['firstname' => 'Gina', 'lastname' => 'Guide']);

        return VoucherOrder::create([
            'voucher_code' => 'CAG-V-'.strtoupper(substr(md5(uniqid()), 0, 6)),
            'guiding_id' => 0,
            'guide_id' => $guide->id,
            'tour_title_snapshot' => 'Finance Tour',
            'number_of_guests' => 2,
            'price_total' => 400,
            'currency' => 'EUR',
            'recipient_names' => 'Max',
            'buyer_first_name' => 'Bea',
            'buyer_last_name' => 'Buyer',
            'buyer_email' => 'buyer@example.com',
            'buyer_phone_country_code' => '+49',
            'buyer_phone' => '151 2345678',
            'design_type' => VoucherDesign::CagStandard,
            'language' => 'de',
            'valid_until' => now()->addYears(3)->endOfYear(),
            'commission_rate' => 0.12,
            'commission_amount' => 48,
        ]);
    }

    private function voucherRow(array $query, VoucherOrder $order): ?array
    {
        $rows = app(FinanceAggregationService::class)->getInvoiceRows(Request::create('/', 'GET', $query));

        return collect($rows)->first(fn (array $row) => $row['source_key'] === 'voucher' && $row['id'] === $order->id);
    }

    public function test_voucher_orders_are_listed_with_their_snapshotted_commission(): void
    {
        $order = $this->order();

        $row = $this->voucherRow([], $order);

        $this->assertNotNull($row);
        $this->assertSame($order->voucher_code, $row['booking_no']);
        $this->assertSame('Bea Buyer', $row['guest_name']);
        $this->assertSame('Gina Guide', $row['guide_name']);
        $this->assertEquals(400, $row['price']);
        $this->assertEquals(48, $row['provision']);
        $this->assertSame('unpaid', $row['paid_status']);
        $this->assertFalse($row['invoice_sent']);
    }

    public function test_date_filters_use_the_purchase_date(): void
    {
        $order = $this->order();

        $this->assertNotNull($this->voucherRow(['year' => now()->year, 'date_filter' => 'reservation'], $order));
        $this->assertNull($this->voucherRow(['year' => now()->year - 1], $order));
    }

    public function test_admin_can_mark_a_voucher_order_paid(): void
    {
        $employee = Employee::query()->first();
        if (! $employee) {
            $this->markTestSkipped('No employee available for admin auth.');
        }
        $order = $this->order();

        $this->actingAs($employee, 'employees')
            ->patch(route('admin.finance.update-paid', ['source' => 'voucher', 'id' => $order->id]), ['paid_status' => 'paid'])
            ->assertStatus(302);

        $finance = FinanceItem::query()->where('billable_type', $order->getMorphClass())->where('billable_id', $order->id)->first();
        $this->assertNotNull($finance);
        $this->assertSame('paid', $finance->paid_status);
        $this->assertSame('paid', $this->voucherRow([], $order)['paid_status']);
    }
}
