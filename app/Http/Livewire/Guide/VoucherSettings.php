<?php

namespace App\Http\Livewire\Guide;

use App\Enums\VoucherDesign;
use App\Models\Guiding;
use App\Services\Guide\GuideVoucherSettingsService;
use Illuminate\Validation\Rule;
use Livewire\Attributes\Layout;
use Livewire\Component;

/**
 * Guide Area › Vouchers › Settings (/guide/vouchers/settings): the old /profile/vouchers page in
 * the new shell. Staged: changes apply on Save. GuideVoucherSettingsService does the work.
 */
#[Layout('layouts.guide-area')]
class VoucherSettings extends Component
{
    public string $design = '';

    /** @var list<int> */
    public array $enabled = [];

    public function mount(GuideVoucherSettingsService $settings): void
    {
        $guide = auth()->user();
        $this->design = $guide->voucherDesign()->value;

        $tours = $settings->tours($guide);
        $this->enabled = $tours['published']->concat($tours['unpublished'])
            ->filter(fn (Guiding $tour) => (bool) $tour->voucher_enabled)
            ->map(fn (Guiding $tour) => (int) $tour->id)
            ->values()
            ->all();
    }

    public function setAll(bool $on, GuideVoucherSettingsService $settings): void
    {
        $tours = $settings->tours(auth()->user());
        $this->enabled = $on
            ? $tours['published']->concat($tours['unpublished'])->map(fn (Guiding $t) => (int) $t->id)->values()->all()
            : [];
    }

    public function save(GuideVoucherSettingsService $settings): void
    {
        $guide = auth()->user();

        $this->validate([
            'design' => ['required', Rule::enum(VoucherDesign::class)],
            'enabled' => ['array'],
            'enabled.*' => ['integer', Rule::exists('guidings', 'id')->where('user_id', $guide->id)],
        ]);

        $settings->update($guide, VoucherDesign::from($this->design), array_map('intval', $this->enabled));
        $this->dispatch('guide-area-toast', message: __('profile.vouchers.saved'));
    }

    public function render(GuideVoucherSettingsService $settings)
    {
        return view('livewire.guide.voucher-settings', [
            'tours' => $settings->tours(auth()->user()),
            'designs' => VoucherDesign::cases(),
            'savedDesign' => auth()->user()->voucherDesign(),
        ])->title(__('profile.vouchers.title'));
    }
}
