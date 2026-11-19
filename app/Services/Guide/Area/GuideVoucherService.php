<?php

namespace App\Services\Guide\Area;

use App\Enums\DoneReason;
use App\Models\User;
use App\Models\VoucherOrder;
use App\Services\Booking\BookingStatusHistory;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;

/**
 * Guide Area › Vouchers (page and API): a guide's gift voucher orders with Paid / Sent flags.
 * The admin status (open / in process / done) follows the flags, so Admin › Bookings › Vouchers
 * keeps showing what's still open: both set → done (booked), one set → in process, none → open.
 */
class GuideVoucherService
{
    public const PER_PAGE = 15;

    public const FILTER_ALL = 'all';
    public const FILTER_TODO = 'todo';
    public const FILTER_DONE = 'done';

    public const FILTERS = [self::FILTER_ALL, self::FILTER_TODO, self::FILTER_DONE];

    public function __construct(private readonly BookingStatusHistory $history) {}

    public function paginate(User $guide, string $filter = self::FILTER_ALL, string $search = '', int $perPage = self::PER_PAGE, string $pageName = 'page'): LengthAwarePaginator
    {
        return $this->filtered($this->query($guide, $search), $filter)
            ->latest()
            ->paginate($perPage, ['*'], $pageName)
            ->withQueryString();
    }

    /** Orders that still need Paid or Sent, oldest first (Bookings › New & open, Overview). */
    public function open(User $guide, ?int $limit = null): Collection
    {
        return $this->filtered($this->query($guide, ''), self::FILTER_TODO)
            ->oldest()
            ->when($limit, fn (Builder $q) => $q->limit($limit))
            ->get();
    }

    /** @return array{all: int, todo: int, done: int} */
    public function counts(User $guide, string $search = ''): array
    {
        $row = $this->query($guide, $search)
            ->selectRaw('COUNT(*) AS total, SUM(CASE WHEN is_paid = 1 AND is_sent = 1 THEN 1 ELSE 0 END) AS finished')
            ->toBase()
            ->first();

        $total = (int) ($row->total ?? 0);
        $done = (int) ($row->finished ?? 0);

        return [self::FILTER_ALL => $total, self::FILTER_TODO => $total - $done, self::FILTER_DONE => $done];
    }

    /**
     * Sets Paid and/or Sent (null leaves a flag as it is) and moves the admin status along.
     */
    public function mark(VoucherOrder $order, ?bool $paid = null, ?bool $sent = null): VoucherOrder
    {
        DB::transaction(function () use ($order, $paid, $sent) {
            $attributes = [];
            if ($paid !== null && $paid !== (bool) $order->is_paid) {
                $attributes += ['is_paid' => $paid, 'paid_at' => $paid ? now() : null];
            }
            if ($sent !== null && $sent !== (bool) $order->is_sent) {
                $attributes += ['is_sent' => $sent, 'sent_at' => $sent ? now() : null];
            }
            if ($attributes === []) {
                return;
            }

            $order->forceFill($attributes)->save();

            $status = match (true) {
                $order->is_paid && $order->is_sent => VoucherOrder::STATUS_DONE,
                $order->is_paid || $order->is_sent => VoucherOrder::STATUS_IN_PROCESS,
                default => VoucherOrder::STATUS_OPEN,
            };
            $this->history->change(
                $order,
                $status,
                null,
                $status === VoucherOrder::STATUS_DONE ? null : 'guide_area',
                $status === VoucherOrder::STATUS_DONE ? DoneReason::Booked : null,
            );
        });

        return $order->refresh();
    }

    private function query(User $guide, string $search): Builder
    {
        $search = trim($search);

        return VoucherOrder::orders()
            ->where('guide_id', $guide->id)
            ->when($search !== '', fn (Builder $q) => $q->where(fn (Builder $w) => $w
                ->where('public_id', $search)
                ->orWhere(fn (Builder $s) => $s->search($search))));
    }

    private function filtered(Builder $query, string $filter): Builder
    {
        return match ($filter) {
            self::FILTER_TODO => $query->where(fn (Builder $q) => $q->where('is_paid', false)->orWhere('is_sent', false)),
            self::FILTER_DONE => $query->where('is_paid', true)->where('is_sent', true),
            default => $query,
        };
    }
}
