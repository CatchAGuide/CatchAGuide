<?php

namespace App\Enums;

use Illuminate\Support\Collection;

/**
 * How a request that was set to "done" ended: the guest booked, or never answered.
 * Stored in the request's `done_reason` column and as the `reason` of its status change.
 */
enum DoneReason: string
{
    case Booked = 'booked';
    case NoReply = 'no_reply';

    public function label(): string
    {
        return __('admin.done_reason.'.$this->value);
    }

    public function hint(): string
    {
        return __('admin.done_reason.'.$this->value.'_hint');
    }

    public function icon(): string
    {
        return match ($this) {
            self::Booked => 'fa-check-circle',
            self::NoReply => 'fa-comment-slash',
        };
    }

    /**
     * Booked / no-reply counts of a list of requests and the share that booked.
     *
     * @param  Collection<int, \Illuminate\Database\Eloquent\Model>  $requests
     * @return array{booked: int, no_reply: int, rate: int|null}
     */
    public static function breakdown(Collection $requests): array
    {
        $booked = $requests->where('status', 'done')->where('done_reason', self::Booked->value)->count();
        $noReply = $requests->where('status', 'done')->where('done_reason', self::NoReply->value)->count();
        $decided = $booked + $noReply;

        return [
            'booked' => $booked,
            'no_reply' => $noReply,
            'rate' => $decided > 0 ? (int) round($booked / $decided * 100) : null,
        ];
    }
}
