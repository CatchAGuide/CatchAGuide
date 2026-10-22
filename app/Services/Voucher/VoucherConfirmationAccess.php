<?php

namespace App\Services\Voucher;

use App\Models\User;
use App\Models\VoucherOrder;
use Illuminate\Contracts\Session\Session;

/**
 * Decides who may open a voucher order's confirmation page. Order ids are sequential, so only
 * the browser session that just placed the order, or the signed-in user who placed it, gets
 * through (same rule as BookingConfirmationAccess for tours).
 */
class VoucherConfirmationAccess
{
    private const SESSION_KEY = 'checkout.confirmed_voucher_order_ids';

    private const MAX_REMEMBERED = 10;

    public function __construct(
        private readonly Session $session,
    ) {}

    public function grant(VoucherOrder $order): void
    {
        $ids = array_values(array_diff($this->rememberedIds(), [(int) $order->getKey()]));
        $ids[] = (int) $order->getKey();

        $this->session->put(self::SESSION_KEY, array_slice($ids, -self::MAX_REMEMBERED));
    }

    public function allows(VoucherOrder $order, ?User $user): bool
    {
        if (in_array((int) $order->getKey(), $this->rememberedIds(), true)) {
            return true;
        }

        return $user !== null
            && $order->user_id !== null
            && (int) $order->user_id === (int) $user->getKey();
    }

    /**
     * @return list<int>
     */
    private function rememberedIds(): array
    {
        return array_values(array_map('intval', (array) $this->session->get(self::SESSION_KEY, [])));
    }
}
