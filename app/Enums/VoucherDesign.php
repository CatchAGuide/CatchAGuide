<?php

namespace App\Enums;

/**
 * What a guide's gift voucher buyers receive (users.voucher_design, snapshotted on
 * voucher_orders.design_type).
 */
enum VoucherDesign: string
{
    /** CaG fills its pre-designed voucher PDF and attaches it to the guide's email. */
    case CagStandard = 'cag_standard';

    /** The guide fills in and sends their own voucher; no PDF is generated. */
    case GuideOwn = 'guide_own';

    public function generatesPdf(): bool
    {
        return $this === self::CagStandard;
    }
}
