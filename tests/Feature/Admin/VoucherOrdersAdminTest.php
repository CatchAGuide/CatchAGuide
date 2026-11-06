<?php

namespace Tests\Feature\Admin;

use App\Enums\VoucherDesign;
use App\Enums\VoucherOrderKind;
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

    public function test_admin_can_save_and_read_an_internal_comment(): void
    {
        $this->actingAsEmployee();
        $order = $this->order('Commentbuyer');

        $this->get(route('admin.voucher-orders.index'))
            ->assertOk()
            ->assertSee(route('admin.voucher-orders.comment.show', $order), false);

        $this->postJson(route('admin.voucher-orders.comment.update', $order), ['admin_comment' => 'Guide confirmed payment'])
            ->assertOk()
            ->assertJson(['success' => true]);

        $this->getJson(route('admin.voucher-orders.comment.show', $order))
            ->assertOk()
            ->assertJson(['id' => $order->id, 'admin_comment' => 'Guide confirmed payment']);

        $this->postJson(route('admin.voucher-orders.comment.update', $order), ['admin_comment' => null])->assertOk();
        $this->assertNull($order->fresh()->admin_comment);
    }

    public function test_guests_cannot_comment(): void
    {
        $order = $this->order('Nocomment');

        $this->post(route('admin.voucher-orders.comment.update', $order), ['admin_comment' => 'x'])->assertRedirect();
        $this->assertNull($order->fresh()->admin_comment);
    }

    public function test_pdf_for_an_order_without_one_redirects_back(): void
    {
        $this->actingAsEmployee();
        $own = $this->order('Nopdf', VoucherDesign::GuideOwn);

        $this->get(route('admin.voucher-orders.pdf', $own))
            ->assertRedirect(route('admin.voucher-orders.index'))
            ->assertSessionHas('error');
    }

    private function landingRequest(string $buyerName): VoucherOrder
    {
        return VoucherOrder::create([
            'voucher_code' => 'CAG-V-'.strtoupper(substr(md5(uniqid('', true)), 0, 6)),
            'kind' => VoucherOrderKind::Request,
            'status' => VoucherOrder::STATUS_OPEN,
            'request_country' => 'Spanien',
            'request_region' => 'Ebro',
            'price_total' => 150,
            'currency' => 'EUR',
            'buyer_first_name' => $buyerName,
            'buyer_email' => 'request@example.com',
            'buyer_phone_country_code' => '+49',
            'buyer_phone' => '151 2345678',
            'language' => 'de',
        ]);
    }

    public function test_landing_page_requests_are_listed_with_their_own_status(): void
    {
        $this->actingAsEmployee();
        $request = $this->landingRequest('Rita Requester');
        $order = $this->order('Orderbuyer');

        $this->get(route('admin.voucher-orders.index'))
            ->assertOk()
            ->assertSee('Rita Requester')
            ->assertSee('Ebro, Spanien')
            ->assertSee(__('admin.voucher_orders.kind_request'))
            ->assertSee(route('admin.voucher-orders.update-status', $request), false)
            ->assertDontSee(route('admin.voucher-orders.update-status', $order), false)
            ->assertSee('Bea Orderbuyer');

        $this->get(route('admin.voucher-orders.index', ['kind' => 'request']))
            ->assertOk()
            ->assertSee($request->voucher_code)
            ->assertDontSee($order->voucher_code);

        $this->get(route('admin.voucher-orders.index', ['kind' => 'order']))
            ->assertOk()
            ->assertSee($order->voucher_code)
            ->assertDontSee($request->voucher_code);
    }

    public function test_admin_moves_a_request_through_its_status_and_every_change_is_logged(): void
    {
        $this->actingAsEmployee();
        $employee = auth('employees')->user();
        $request = $this->landingRequest('Stella Status');

        $this->patchJson(route('admin.voucher-orders.update-status', $request), ['status' => 'in_process'])
            ->assertOk()
            ->assertJson(['success' => true, 'status' => 'in_process'])
            ->assertJsonPath('status_summary', __('admin.booking_status_history.last_changed', [
                'name' => $employee->name,
                'date' => $request->fresh()->latestStatusChange->created_at->format('M j, Y g:i A'),
            ]));

        $this->patchJson(route('admin.voucher-orders.update-status', $request), ['status' => 'done', 'done_reason' => 'booked'])
            ->assertOk()
            ->assertJson(['status' => 'done', 'done_reason' => 'booked']);

        $request->refresh();
        $this->assertSame('done', $request->status);
        $this->assertSame('booked', $request->done_reason);
        $this->assertSame(
            [['in_process', 'done'], ['open', 'in_process']],
            $request->statusChanges->map(fn ($change) => [$change->from_status, $change->to_status])->all(),
        );
        $this->assertTrue($request->statusChanges->every(fn ($change) => $change->employee_id === $employee->id));

        // Newest first, ending with the request itself coming in from the site.
        $items = $this->getJson(route('admin.voucher-orders.status-history', $request))->assertOk()->json('items');
        $this->assertCount(3, $items);
        $this->assertSame('done', $items[0]['to']);
        $this->assertSame($employee->name, $items[0]['by']);
        $this->assertSame(__('admin.booking_status_history.received'), $items[2]['to_label']);

        // Picking the current status again logs nothing.
        $this->patchJson(route('admin.voucher-orders.update-status', $request), ['status' => 'done', 'done_reason' => 'booked'])->assertOk();
        $this->assertCount(2, $request->fresh()->statusChanges);

        $this->get(route('admin.voucher-orders.index', ['kind' => 'request', 'status' => 'done']))
            ->assertOk()
            ->assertSee($request->voucher_code);
        $this->get(route('admin.voucher-orders.index', ['kind' => 'request', 'status' => 'open']))
            ->assertOk()
            ->assertDontSee($request->voucher_code);
    }

    public function test_orders_have_no_request_status(): void
    {
        $this->actingAsEmployee();
        $order = $this->order('Nostatus');

        $this->patchJson(route('admin.voucher-orders.update-status', $order), ['status' => 'done'])->assertNotFound();
        $this->getJson(route('admin.voucher-orders.status-history', $order))->assertNotFound();
        $this->assertNull($order->fresh()->status);
    }

    public function test_invalid_statuses_and_guests_are_refused(): void
    {
        $request = $this->landingRequest('Guarded');

        $this->patchJson(route('admin.voucher-orders.update-status', $request), ['status' => 'done'])->assertUnauthorized();

        $this->actingAsEmployee();
        $this->patchJson(route('admin.voucher-orders.update-status', $request), ['status' => 'archived'])->assertUnprocessable();
        $this->assertSame('open', $request->fresh()->status);
    }

    public function test_the_gift_voucher_faq_page_is_managed_in_admin(): void
    {
        $this->actingAsEmployee();

        $this->get(route('admin.faq.gift-voucher'))->assertOk();
        $this->post(route('admin.faq.store'), [
            'question' => 'Wie lange ist der Gutschein gültig?',
            'answer' => 'Drei Jahre.',
            'page' => 'gift-voucher',
            'language' => 'de',
        ])->assertRedirect(route('admin.faq.gift-voucher'));

        // actingAs() made the employees guard the default; the public page is visited as a guest.
        $this->app['auth']->shouldUse('web');
        app()->setLocale('de');
        $this->get(route('guidings.voucher'))->assertSee('Wie lange ist der Gutschein gültig?');
    }
}
