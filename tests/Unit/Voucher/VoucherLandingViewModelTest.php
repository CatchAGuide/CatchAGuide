<?php

namespace Tests\Unit\Voucher;

use App\Domain\Voucher\ViewModels\VoucherLandingViewModel;
use App\Presenters\Voucher\VoucherOrderPresenter;
use App\Services\Voucher\VoucherLandingCatalog;
use Carbon\CarbonImmutable;
use Tests\TestCase;

class VoucherLandingViewModelTest extends TestCase
{
    private function viewModel(string $today): VoucherLandingViewModel
    {
        return new VoucherLandingViewModel(
            $this->createMock(VoucherLandingCatalog::class),
            app(VoucherOrderPresenter::class),
            null,
            null,
            CarbonImmutable::parse($today),
        );
    }

    public function test_the_christmas_season_runs_from_november_to_christmas_eve(): void
    {
        $this->assertFalse($this->viewModel('2026-10-31')->isChristmasSeason());
        $this->assertTrue($this->viewModel('2026-11-01')->isChristmasSeason());
        $this->assertTrue($this->viewModel('2026-12-24')->isChristmasSeason());
        $this->assertFalse($this->viewModel('2026-12-25')->isChristmasSeason());
    }

    public function test_it_counts_the_days_to_christmas_eve(): void
    {
        $this->assertSame(48, $this->viewModel('2026-11-06 15:30')->daysToChristmasEve());
        $this->assertSame(0, $this->viewModel('2026-12-24')->daysToChristmasEve());
    }

    public function test_money_follows_the_page_language(): void
    {
        app()->setLocale('de');
        $this->assertSame('1.500 €', $this->viewModel('2026-11-06')->money(1500));

        app()->setLocale('en');
        $this->assertSame('€1,500', $this->viewModel('2026-11-06')->money(1500));
    }
}
