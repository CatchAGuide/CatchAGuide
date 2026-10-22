<?php

namespace Tests\Unit\Voucher;

use App\Enums\VoucherDesign;
use App\Models\Guiding;
use App\Models\User;
use App\Models\VoucherOrder;
use App\Services\Voucher\VoucherCodeGenerator;
use App\Services\Voucher\VoucherConfirmationAccess;
use App\Services\Voucher\VoucherValidity;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Session\ArraySessionHandler;
use Illuminate\Session\Store;
use Tests\TestCase;

class VoucherRulesTest extends TestCase
{
    use DatabaseTransactions;

    public function test_validity_runs_three_years_to_the_end_of_the_purchase_year(): void
    {
        $this->assertSame('2029-12-31', VoucherValidity::until(Carbon::parse('2026-10-16'))->toDateString());
        $this->assertSame('2029-12-31', VoucherValidity::until(Carbon::parse('2026-01-01'))->toDateString());
        $this->assertSame('2030-12-31', VoucherValidity::until(Carbon::parse('2027-12-31 23:59'))->toDateString());
    }

    public function test_codes_use_the_prefix_and_an_unambiguous_alphabet(): void
    {
        $codes = collect(range(1, 50))->map(fn () => app(VoucherCodeGenerator::class)->generate());

        foreach ($codes as $code) {
            $this->assertMatchesRegularExpression('/^CAG-V-[ABCDEFGHJKMNPQRSTUVWXYZ2-9]{6}$/', $code);
        }
        $this->assertGreaterThan(45, $codes->unique()->count());
    }

    public function test_unknown_design_values_fall_back_to_the_standard_voucher(): void
    {
        $this->assertSame(VoucherDesign::CagStandard, (new User)->forceFill(['voucher_design' => null])->voucherDesign());
        $this->assertSame(VoucherDesign::CagStandard, (new User)->forceFill(['voucher_design' => 'gold'])->voucherDesign());
        $this->assertSame(VoucherDesign::GuideOwn, (new User)->forceFill(['voucher_design' => 'guide_own'])->voucherDesign());
        $this->assertTrue(VoucherDesign::CagStandard->generatesPdf());
        $this->assertFalse(VoucherDesign::GuideOwn->generatesPdf());
    }

    public function test_tours_accept_vouchers_unless_switched_off(): void
    {
        $this->assertTrue((new Guiding)->acceptsVouchers());
        $this->assertTrue((new Guiding)->forceFill(['voucher_enabled' => 1])->acceptsVouchers());
        $this->assertFalse((new Guiding)->forceFill(['voucher_enabled' => 0])->acceptsVouchers());
    }

    public function test_confirmation_access_is_limited_to_the_ordering_session_or_buyer(): void
    {
        $session = new Store('test', new ArraySessionHandler(10));
        $access = new VoucherConfirmationAccess($session);
        $buyer = (new User)->forceFill(['id' => 501]);
        $stranger = (new User)->forceFill(['id' => 502]);

        $order = (new VoucherOrder)->forceFill(['id' => 9001, 'user_id' => 501]);
        $other = (new VoucherOrder)->forceFill(['id' => 9002, 'user_id' => null]);

        $this->assertFalse($access->allows($other, null));
        $this->assertTrue($access->allows($order, $buyer));
        $this->assertFalse($access->allows($order, $stranger));

        $access->grant($other);
        $this->assertTrue($access->allows($other, null));
    }
}
