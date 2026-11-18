<?php

namespace App\Http\Livewire\Guide;

use App\Models\VoucherOrder;
use App\Presenters\Voucher\GuideVoucherOrderPresenter;
use App\Services\Guide\Area\GuideVoucherService;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

/**
 * Guide Area › Bookings › Vouchers tab: the guide's gift voucher orders, marked as paid and
 * sent here (/guide/vouchers redirects there, as in the spec). Thin: GuideVoucherService
 * (shared with /api/guide/voucher-orders).
 */
#[Layout('layouts.guide-area')]
class Vouchers extends Component
{
    use WithPagination;

    /** Rendered inside Bookings (no page header). */
    public bool $embedded = false;

    #[Url(except: 'all')]
    public string $filter = GuideVoucherService::FILTER_ALL;

    #[Url(except: '')]
    public string $search = '';

    public function updatedSearch(): void
    {
        $this->resetPage($this->pageName());
    }

    public function setFilter(string $filter): void
    {
        $this->filter = in_array($filter, GuideVoucherService::FILTERS, true) ? $filter : GuideVoucherService::FILTER_ALL;
        $this->resetPage($this->pageName());
    }

    public function togglePaid(int $id, GuideVoucherService $vouchers): void
    {
        $order = $this->order($id);
        $vouchers->mark($order, paid: ! $order->is_paid);
        $this->dispatch('guide-area-toast', message: __($order->is_paid ? 'guide_area.vouchers.marked_paid' : 'guide_area.vouchers.unmarked_paid'));
    }

    public function toggleSent(int $id, GuideVoucherService $vouchers): void
    {
        $order = $this->order($id);
        $vouchers->mark($order, sent: ! $order->is_sent);
        $this->dispatch('guide-area-toast', message: __($order->is_sent ? 'guide_area.vouchers.marked_sent' : 'guide_area.vouchers.unmarked_sent'));
    }

    public function render(GuideVoucherService $vouchers, GuideVoucherOrderPresenter $presenter)
    {
        $guide = auth()->user();
        $page = $vouchers->paginate($guide, $this->filter, $this->search, GuideVoucherService::PER_PAGE, $this->pageName());
        $page->setCollection($page->getCollection()->map(fn (VoucherOrder $o) => $presenter->present($o)));

        return view('livewire.guide.vouchers', [
            'orders' => $page,
            'counts' => $vouchers->counts($guide, $this->search),
            'filters' => GuideVoucherService::FILTERS,
        ])->title(__('guide_area.vouchers.title'));
    }

    /** Inside Bookings the tours list owns ?page=. */
    private function pageName(): string
    {
        return $this->embedded ? 'vpage' : 'page';
    }

    private function order(int $id): VoucherOrder
    {
        $order = VoucherOrder::orders()->where('guide_id', auth()->id())->findOrFail($id);
        $this->authorize('update', $order);

        return $order;
    }
}
