<?php

namespace App\Models;

use App\Enums\VoucherDesign;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphOne;

/**
 * A tour bought as a gift voucher (see the voucher_orders migrations). CaG takes no payment:
 * the guide gets the order (with the CaG voucher PDF, if they use it), collects the payment and
 * sends the voucher to the buyer. language is the buyer's checkout language, voucher_language
 * the guide's, in which the voucher is written.
 */
class VoucherOrder extends Model
{
    protected $fillable = [
        'voucher_code',
        'guiding_id',
        'guide_id',
        'user_id',
        'tour_title_snapshot',
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
    ];

    protected $casts = [
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
