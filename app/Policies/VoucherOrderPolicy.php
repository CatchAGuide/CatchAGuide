<?php

namespace App\Policies;

use App\Enums\VoucherOrderKind;
use App\Models\User;
use App\Models\VoucherOrder;

/**
 * Guide Area voucher actions (page and API): only the guide the tour order went to.
 * Landing-page requests have no guide and are never reachable here.
 */
class VoucherOrderPolicy
{
    public function view(User $user, VoucherOrder $order): bool
    {
        return $order->kind === VoucherOrderKind::Order && (int) $order->guide_id === (int) $user->id;
    }

    public function update(User $user, VoucherOrder $order): bool
    {
        return $this->view($user, $order);
    }
}
