<?php

namespace App\Contracts\Booking;

use App\Enums\DoneReason;

/**
 * A request whose "done" status says how it ended (booked / no reply). Implemented with
 * App\Traits\HasDoneReason; needs a nullable `done_reason` column.
 */
interface HasDoneReasonContract extends HasStatusHistoryContract
{
    public function doneReason(): ?DoneReason;
}
