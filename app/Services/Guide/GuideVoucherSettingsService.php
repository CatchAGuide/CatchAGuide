<?php

namespace App\Services\Guide;

use App\Enums\VoucherDesign;
use App\Models\Guiding;
use App\Models\User;
use Illuminate\Database\Eloquent\Collection;

/**
 * A guide's gift voucher settings (profile › Gift vouchers): which of their tours can be bought
 * as a voucher (per tour, on by default) and which voucher design buyers get (per guide).
 * Changes only affect new voucher orders; existing orders keep their snapshot.
 */
class GuideVoucherSettingsService
{
    /**
     * The guide's tours for the settings page: published ones first (the only ones guests can
     * buy), each group sorted by the title as displayed.
     *
     * @return array{published: Collection<int, Guiding>, unpublished: Collection<int, Guiding>}
     */
    public function tours(User $guide): array
    {
        [$published, $unpublished] = Guiding::where('user_id', $guide->id)
            ->get(['id', 'title', 'slug', 'status', 'voucher_enabled'])
            ->each(fn (Guiding $tour) => $tour->setAttribute('display_title', (string) translate($tour->title)))
            ->sortBy('display_title', SORT_NATURAL | SORT_FLAG_CASE)
            ->partition(fn (Guiding $tour) => (int) $tour->status === 1);

        return ['published' => $published->values(), 'unpublished' => $unpublished->values()];
    }

    /**
     * @param  list<int>  $enabledGuidingIds  Tours left ticked; the guide's other tours are switched off.
     */
    public function update(User $guide, VoucherDesign $design, array $enabledGuidingIds): void
    {
        $guide->forceFill(['voucher_design' => $design->value])->save();

        $tours = Guiding::where('user_id', $guide->id);
        $changed = (clone $tours)->whereIn('id', $enabledGuidingIds)->where('voucher_enabled', false)->update(['voucher_enabled' => true])
            + (clone $tours)->whereNotIn('id', $enabledGuidingIds)->where('voucher_enabled', true)->update(['voucher_enabled' => false]);

        // A query update skips model events, so clear the guiding cache like a save would.
        if ($changed > 0) {
            (new Guiding)->clearModelCache();
        }
    }
}
