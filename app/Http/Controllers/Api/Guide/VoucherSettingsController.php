<?php

namespace App\Http\Controllers\Api\Guide;

use App\Enums\VoucherDesign;
use App\Http\Controllers\Controller;
use App\Models\Guiding;
use App\Services\Guide\GuideVoucherSettingsService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

/**
 * /api/guide/voucher-settings — voucher design and which tours sell vouchers.
 */
class VoucherSettingsController extends Controller
{
    public function __construct(private readonly GuideVoucherSettingsService $settings) {}

    public function show(Request $request): JsonResponse
    {
        return response()->json(['data' => $this->payload($request)]);
    }

    public function update(Request $request): JsonResponse
    {
        $guide = $request->user();
        $data = $request->validate([
            'design' => ['required', Rule::enum(VoucherDesign::class)],
            'enabled_tour_ids' => ['present', 'array'],
            'enabled_tour_ids.*' => ['integer', Rule::exists('guidings', 'id')->where('user_id', $guide->id)],
        ]);

        $this->settings->update($guide, VoucherDesign::from($data['design']), array_map('intval', $data['enabled_tour_ids']));

        return response()->json(['data' => $this->payload($request)]);
    }

    private function payload(Request $request): array
    {
        $guide = $request->user()->refresh();
        $tours = $this->settings->tours($guide);

        return [
            'design' => $guide->voucherDesign()->value,
            'designs' => array_map(fn (VoucherDesign $d) => ['value' => $d->value, 'generates_pdf' => $d->generatesPdf()], VoucherDesign::cases()),
            'tours' => $tours['published']->concat($tours['unpublished'])->map(fn (Guiding $tour) => [
                'id' => (int) $tour->id,
                'title' => (string) $tour->display_title,
                'live' => (int) $tour->status === 1,
                'voucher_enabled' => (bool) $tour->voucher_enabled,
            ])->values(),
        ];
    }
}
