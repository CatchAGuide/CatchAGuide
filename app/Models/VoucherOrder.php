<?php

namespace App\Models;

use App\Contracts\Booking\HasDoneReasonContract;
use App\Enums\VoucherDesign;
use App\Enums\VoucherOrderKind;
use App\Traits\HasDoneReason;
use App\Traits\HasStatusHistory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphOne;

/**
 * A tour bought as a gift voucher (see the voucher_orders migrations). CaG takes no payment:
 * the guide gets the order (with the CaG voucher PDF, if they use it), collects the payment and
 * sends the voucher to the buyer. language is the buyer's checkout language, voucher_language
 * the guide's, in which the voucher is written.
 *
 * Requests from the /guidings/voucher landing page live here too (kind Request): a destination
 * (request_country/region/city) and an amount (price_total), but no tour, guide, design or
 * commission. CaG contacts the buyer and issues the voucher, and moves the request through
 * open → in process → done (booked / no reply) in Admin › Bookings › Vouchers; every change is
 * logged (HasStatusHistory). Orders have no status.
 */
class VoucherOrder extends Model implements HasDoneReasonContract
{
    use HasDoneReason, HasStatusHistory;

    public const STATUS_OPEN = 'open';
    public const STATUS_IN_PROCESS = 'in_process';
    public const STATUS_DONE = 'done';

    protected $fillable = [
        'voucher_code',
        'kind',
        'status',
        'done_reason',
        'guiding_id',
        'guide_id',
        'user_id',
        'tour_title_snapshot',
        'request_country',
        'request_region',
        'request_city',
        'number_of_guests',
        'price_total',
        'currency',
        'recipient_names',
        'sender_name',
        'personal_message',
        'buyer_first_name',
        'buyer_last_name',
        'buyer_email',
        'buyer_phone_country_code',
        'buyer_phone',
        'design_type',
        'pdf_path',
        'language',
        'voucher_language',
        'valid_until',
        'commission_rate',
        'commission_amount',
        'admin_comment',
    ];

    protected $casts = [
        'kind' => VoucherOrderKind::class,
        'design_type' => VoucherDesign::class,
        'number_of_guests' => 'integer',
        'price_total' => 'decimal:2',
        'commission_rate' => 'decimal:4',
        'commission_amount' => 'decimal:2',
        'valid_until' => 'date',
    ];

    public function guiding(): BelongsTo
    {
        return $this->belongsTo(Guiding::class);
    }

    public function guide(): BelongsTo
    {
        return $this->belongsTo(User::class, 'guide_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function financeItem(): MorphOne
    {
        return $this->morphOne(FinanceItem::class, 'billable');
    }

    /** Same labels as the other admin request lists. */
    public static function statusOptions(): array
    {
        return [
            self::STATUS_OPEN => __('message.contact_request_status.open'),
            self::STATUS_IN_PROCESS => __('message.contact_request_status.in_process'),
            self::STATUS_DONE => __('message.contact_request_status.done'),
        ];
    }

    /** Tour vouchers bought at checkout; finance and the guide's lists only ever see these. */
    public function scopeOrders(Builder $query): Builder
    {
        return $query->where('kind', VoucherOrderKind::Order->value);
    }

    /** Landing page requests (no tour yet). */
    public function scopeRequests(Builder $query): Builder
    {
        return $query->where('kind', VoucherOrderKind::Request->value);
    }

    public function isRequest(): bool
    {
        return $this->kind === VoucherOrderKind::Request;
    }

    /** "Region, Country" (or "City · Region, Country") the request names; '' when left open. */
    public function requestDestination(): string
    {
        $place = implode(', ', array_filter([$this->request_region, $this->request_country]));

        return $this->request_city && $place !== '' ? $this->request_city.' · '.$place : $place;
    }

    /** Matches the voucher code, tour title or buyer name/email; a blank term matches all. */
    public function scopeSearch(Builder $query, string $term): Builder
    {
        if ($term === '') {
            return $query;
        }

        $like = '%'.$term.'%';

        return $query->where(fn (Builder $q) => $q
            ->where('voucher_code', 'like', $like)
            ->orWhere('tour_title_snapshot', 'like', $like)
            ->orWhere('request_region', 'like', $like)
            ->orWhere('request_country', 'like', $like)
            ->orWhere('buyer_email', 'like', $like)
            ->orWhereRaw("CONCAT(buyer_first_name, ' ', buyer_last_name) like ?", [$like]));
    }

    public function buyerName(): string
    {
        return trim($this->buyer_first_name.' '.$this->buyer_last_name);
    }

    public function buyerPhone(): string
    {
        return trim($this->buyer_phone_country_code.' '.$this->buyer_phone);
    }

    /** Locale the voucher (PDF, file name, QR domain) is written in. */
    public function voucherLocale(): string
    {
        return $this->voucher_language ?: ($this->language ?: app()->getLocale());
    }

    public function hasPdf(): bool
    {
        return $this->design_type?->generatesPdf() && $this->pdf_path !== null;
    }
}
