<?php

namespace App\Services\Voucher;

use App\Models\EmailLog;
use App\Models\User;

/**
 * Language of everything the guide works with: their emails and the voucher itself. It is the
 * guide's account (communication) language, or the given fallback (the buyer's checkout
 * language) when the guide hasn't set a supported one.
 */
class VoucherLanguage
{
    public static function forGuide(?User $guide, string $fallback): string
    {
        $locale = EmailLog::normalizeLanguage($guide?->language);

        return in_array($locale, array_values(config('app.locales', [])), true) ? $locale : $fallback;
    }
}
