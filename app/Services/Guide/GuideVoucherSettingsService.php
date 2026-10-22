<?php

namespace App\Services\Guide;

use App\Enums\VoucherDesign;
use App\Mail\Guest\VoucherOrderGuestMail;
use App\Models\Guiding;
use App\Models\User;
use App\Models\UserInformation;
use App\Models\VoucherOrder;
use App\Services\Voucher\VoucherLanguage;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Traits\Localizable;

/**
 * A guide's gift voucher settings (profile › Gift vouchers): which of their tours can be bought
 * as a voucher (per tour, on by default) and which voucher design buyers get (per guide).
 * Changes only affect new voucher orders; existing orders keep their snapshot.
 */
class GuideVoucherSettingsService
{
    use Localizable;

    /**
     * Unsaved sample order for a voucher preview: placeholder recipient, sender and guide
     * (Mustermann), and the given tour (title, location, duration, fishing type, QR code) or a
     * made-up one, in the guide's language like a real voucher.
     */
    public function sampleOrder(User $guide, ?Guiding $tour = null): VoucherOrder
    {
        $locale = VoucherLanguage::forGuide($guide, app()->getLocale());
        $order = $this->withLocale($locale, fn () => VoucherOrderGuestMail::sample()->order);
        $order->forceFill([
            'recipient_names' => 'Max Mustermann',
            'sender_name' => 'Martina Mustermann',
        ]);

        $sampleGuide = (new User)->forceFill([
            'firstname' => 'Martin',
            'lastname' => 'Mustermann',
            'email' => 'beispiel@beispiel.com',
            'phone_country_code' => '+49',
            'phone' => '1234567890',
        ]);
        $sampleGuide->setRelation('information', new UserInformation);
        $order->setRelation('guide', $sampleGuide);

        if ($tour !== null) {
            $order->setRelation('guiding', $tour);
            $order->tour_title_snapshot = (string) $tour->title;
            $order->number_of_guests = max(1, min(2, (int) $tour->max_guests));
        }

        return $order;
    }

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
