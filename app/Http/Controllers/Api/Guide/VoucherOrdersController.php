<?php

namespace App\Http\Controllers\Api\Guide;

use App\Http\Controllers\Controller;
use App\Models\VoucherOrder;
use App\Presenters\Voucher\GuideVoucherOrderPresenter;
use App\Services\Guide\Area\GuideVoucherService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * /api/guide/voucher-orders — the guide's gift voucher orders with Paid / Sent flags.
 */
class VoucherOrdersController extends Controller
{
    public function __construct(
        private readonly GuideVoucherService $vouchers,
        private readonly GuideVoucherOrderPresenter $presenter,
    ) {}

    public function index(Request $request): JsonResponse
    {
        $data = $request->validate([
            'filter' => ['nullable', 'in:'.implode(',', GuideVoucherService::FILTERS)],
            'search' => ['nullable', 'string', 'max:100'],
            'per_page' => ['nullable', 'integer', 'min:1', 'max:50'],
        ]);
        $guide = $request->user();
        $search = (string) ($data['search'] ?? '');

        $page = $this->vouchers->paginate(
            $guide,
            $data['filter'] ?? GuideVoucherService::FILTER_ALL,
            $search,
            (int) ($data['per_page'] ?? GuideVoucherService::PER_PAGE),
        );

        return response()->json([
            'data' => collect($page->items())->map(fn (VoucherOrder $o) => $this->presenter->present($o))->values(),
            'meta' => [
                'counts' => $this->vouchers->counts($guide, $search),
                'current_page' => $page->currentPage(),
                'last_page' => $page->lastPage(),
                'per_page' => $page->perPage(),
                'total' => $page->total(),
            ],
        ]);
    }

    public function show(VoucherOrder $voucherOrder): JsonResponse
    {
        $this->authorize('view', $voucherOrder);

        return response()->json(['data' => $this->presenter->present($voucherOrder)]);
    }

    public function update(Request $request, VoucherOrder $voucherOrder): JsonResponse
    {
        $this->authorize('update', $voucherOrder);

        $data = $request->validate([
            'is_paid' => ['sometimes', 'boolean'],
            'is_sent' => ['sometimes', 'boolean'],
        ]);

        $order = $this->vouchers->mark(
            $voucherOrder,
            array_key_exists('is_paid', $data) ? (bool) $data['is_paid'] : null,
            array_key_exists('is_sent', $data) ? (bool) $data['is_sent'] : null,
        );

        return response()->json(['data' => $this->presenter->present($order)]);
    }
}
