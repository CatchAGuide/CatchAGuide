<?php

namespace Tests\Feature\Guide\Area;

use App\Enums\DoneReason;
use App\Enums\GuideStatus;
use App\Enums\VoucherDesign;
use App\Enums\VoucherOrderKind;
use App\Http\Livewire\Guide\VoucherSettings;
use App\Http\Livewire\Guide\Vouchers;
use App\Models\FishingType;
use App\Models\Guiding;
use App\Models\User;
use App\Models\VoucherOrder;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\URL;
use Laravel\Sanctum\Sanctum;
use Livewire\Livewire;
use Tests\TestCase;

/**
 * Guide Area › Vouchers: orders with Paid / Sent (page + /api/guide/voucher-orders) and the
 * settings page (+ /api/guide/voucher-settings).
 */
class GuideVouchersTest extends TestCase
{
    use DatabaseTransactions;

    protected function setUp(): void
    {
        parent::setUp();

        config(['app.url' => 'http://localhost', 'guide_area.enabled' => true]);
        URL::forceRootUrl('http://localhost');
    }

    private function guide(bool $beta = true): User
    {
        $guide = User::factory()->create(['is_guide' => 1, 'guide_status' => GuideStatus::VERIFIED, 'language' => 'en']);
        $guide->forceFill(['guide_area_beta' => $beta])->save();

        return $guide->fresh();
    }

    private function order(User $guide, string $buyer = 'Buyer'): VoucherOrder
    {
        return VoucherOrder::create([
            'voucher_code' => 'CAG-V-'.strtoupper(substr(md5(uniqid('', true)), 0, 6)),
            'status' => VoucherOrder::STATUS_OPEN,
            'guiding_id' => 0,
            'guide_id' => $guide->id,
            'tour_title_snapshot' => 'Pike on the lake',
            'number_of_guests' => 2,
            'price_total' => 400,
            'currency' => 'EUR',
            'recipient_names' => 'Max',
            'buyer_first_name' => 'Bea',
            'buyer_last_name' => $buyer,
            'buyer_email' => strtolower($buyer).'@example.com',
            'buyer_phone_country_code' => '+49',
            'buyer_phone' => '151 2345678',
            'design_type' => VoucherDesign::GuideOwn,
            'language' => 'en',
            'voucher_language' => 'en',
            'valid_until' => now()->addYears(3)->endOfYear(),
        ]);
    }

    private function tour(User $guide, bool $voucher): Guiding
    {
        $guiding = new Guiding;
        $guiding->forceFill([
            'title' => 'Tour '.uniqid(), 'slug' => 'tour-'.uniqid(), 'location' => 'Lake', 'status' => 1,
            'user_id' => $guide->id, 'voucher_enabled' => $voucher, 'max_guests' => 4, 'duration' => 4,
            'fishing_type_id' => FishingType::query()->value('id') ?? 1,
        ])->save();

        return $guiding;
    }

    public function test_new_orders_get_the_next_public_id(): void
    {
        $guide = $this->guide();
        $first = $this->order($guide);
        $second = $this->order($guide);

        $this->assertMatchesRegularExpression('/^G-\d{4,}$/', (string) $first->fresh()->public_id);
        $this->assertSame((int) substr($first->fresh()->public_id, 2) + 1, (int) substr($second->fresh()->public_id, 2));
    }

    public function test_requests_get_no_public_id(): void
    {
        $request = VoucherOrder::create([
            'voucher_code' => 'CAG-R-'.uniqid(), 'kind' => VoucherOrderKind::Request, 'price_total' => 100,
            'buyer_first_name' => 'A', 'buyer_last_name' => 'B', 'buyer_email' => 'a@example.com',
            'buyer_phone_country_code' => '+49', 'buyer_phone' => '151 2345678', 'language' => 'en',
        ]);

        $this->assertNull($request->fresh()->public_id);
    }

    public function test_page_lists_only_own_orders(): void
    {
        $guide = $this->guide();
        $mine = $this->order($guide, 'Mine');
        $other = $this->order($this->guide(), 'Theirs');

        $this->actingAs($guide)->get(route('guide.area.vouchers'))
            ->assertOk()
            ->assertSee($mine->fresh()->public_id)
            ->assertSee('mine@example.com')
            ->assertDontSee('theirs@example.com');
    }

    public function test_paid_and_sent_move_the_admin_status_along(): void
    {
        $guide = $this->guide();
        $order = $this->order($guide);

        $page = Livewire::actingAs($guide)->test(Vouchers::class)->call('togglePaid', $order->id);
        $order->refresh();
        $this->assertTrue($order->is_paid);
        $this->assertNotNull($order->paid_at);
        $this->assertSame(VoucherOrder::STATUS_IN_PROCESS, $order->status);

        $page->call('toggleSent', $order->id);
        $order->refresh();
        $this->assertSame(VoucherOrder::STATUS_DONE, $order->status);
        $this->assertSame(DoneReason::Booked, $order->doneReason());
        $this->assertSame(2, $order->statusChanges()->count(), 'Both changes are in the admin history.');

        $page->call('togglePaid', $order->id)->call('toggleSent', $order->id);
        $order->refresh();
        $this->assertFalse($order->is_paid);
        $this->assertNull($order->paid_at);
        $this->assertSame(VoucherOrder::STATUS_OPEN, $order->status);
    }

    public function test_filters_and_counts(): void
    {
        $guide = $this->guide();
        $open = $this->order($guide, 'Open');
        $done = $this->order($guide, 'Done');
        $done->forceFill(['is_paid' => true, 'is_sent' => true])->save();

        Livewire::actingAs($guide)->test(Vouchers::class)
            ->assertViewHas('counts', ['all' => 2, 'todo' => 1, 'done' => 1])
            ->call('setFilter', 'done')
            ->assertSee('done@example.com')
            ->assertDontSee('open@example.com');
    }

    public function test_guides_cannot_mark_other_guides_orders(): void
    {
        $guide = $this->guide();
        $foreign = $this->order($this->guide());

        Sanctum::actingAs($guide);
        $this->patchJson(route('api.guide.voucher-orders.update', $foreign), ['is_paid' => true])->assertForbidden();
        $this->getJson(route('api.guide.voucher-orders.show', $foreign))->assertForbidden();
        $this->assertFalse((bool) $foreign->fresh()->is_paid);

        $this->expectException(ModelNotFoundException::class);
        Livewire::actingAs($guide)->test(Vouchers::class)->call('togglePaid', $foreign->id);
    }

    public function test_api_lists_and_updates_orders(): void
    {
        $guide = $this->guide();
        $order = $this->order($guide);

        Sanctum::actingAs($guide);
        $this->getJson(route('api.guide.voucher-orders.index'))
            ->assertOk()
            ->assertJsonPath('data.0.id', $order->id)
            ->assertJsonPath('data.0.is_paid', false)
            ->assertJsonPath('meta.counts.todo', 1);

        $this->patchJson(route('api.guide.voucher-orders.update', $order), ['is_paid' => true, 'is_sent' => true])
            ->assertOk()
            ->assertJsonPath('data.is_paid', true)
            ->assertJsonPath('data.is_sent', true);
        $this->assertSame(VoucherOrder::STATUS_DONE, $order->fresh()->status);
    }

    public function test_settings_page_saves_design_and_tours(): void
    {
        $guide = $this->guide();
        $on = $this->tour($guide, true);
        $off = $this->tour($guide, false);
        $foreign = $this->tour($this->guide(), false);

        $this->actingAs($guide)->get(route('guide.area.vouchers.settings'))->assertOk()->assertSee($on->title);

        Livewire::actingAs($guide)->test(VoucherSettings::class)
            ->assertSet('enabled', [$on->id])
            ->set('design', VoucherDesign::GuideOwn->value)
            ->set('enabled', [(string) $off->id])
            ->call('save')
            ->assertHasNoErrors();

        $this->assertSame(VoucherDesign::GuideOwn, $guide->fresh()->voucherDesign());
        $this->assertFalse((bool) $on->fresh()->voucher_enabled);
        $this->assertTrue((bool) $off->fresh()->voucher_enabled);

        Livewire::actingAs($guide)->test(VoucherSettings::class)
            ->set('enabled', [$foreign->id])
            ->call('save')
            ->assertHasErrors('enabled.0');
        $this->assertFalse((bool) $foreign->fresh()->voucher_enabled);
    }

    public function test_api_settings_round_trip(): void
    {
        $guide = $this->guide();
        $tour = $this->tour($guide, false);

        Sanctum::actingAs($guide);
        $this->putJson(route('api.guide.voucher-settings.update'), ['design' => 'cag_standard', 'enabled_tour_ids' => [$tour->id]])
            ->assertOk()
            ->assertJsonPath('data.design', 'cag_standard')
            ->assertJsonPath('data.tours.0.voucher_enabled', true);
    }

    public function test_old_voucher_settings_redirect_beta_guides(): void
    {
        $this->actingAs($this->guide())->get(route('profile.vouchers'))->assertRedirect(route('guide.area.vouchers.settings'));
    }
}
