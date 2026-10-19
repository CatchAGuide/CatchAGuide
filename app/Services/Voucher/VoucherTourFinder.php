<?php

namespace App\Services\Voucher;

use App\Models\Guiding;

/**
 * The one rule for which tours can be bought as a gift voucher: published (same visibility as
 * the tour page and the booking checkout) and not switched off by the guide.
 */
class VoucherTourFinder
{
    public function findBySlug(string $slug): ?Guiding
    {
        $guiding = Guiding::publiclyVisible()
            ->with('user.information')
            ->where('slug', $slug)
            ->first();

        return $guiding?->acceptsVouchers() ? $guiding : null;
    }

    public function findById(int $id): ?Guiding
    {
        $guiding = Guiding::publiclyVisible()->with('user.information')->find($id);

        return $guiding?->acceptsVouchers() ? $guiding : null;
    }
}
