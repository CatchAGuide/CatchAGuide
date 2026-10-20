<?php

namespace App\Models;

use App\Enums\VoucherDesign;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphOne;

/**
 * A tour bought as a gift voucher (see the voucher_orders migrations). CaG takes no payment:
 * the guide collects it and confirms it (paid_at, via the payment_token link or the profile);
 * CaG then emails the voucher to the buyer (VoucherPaymentService).
 */
class VoucherOrder extends Model
{
    protected $fillable = [
        'voucher_code',
        'payment_token',
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
        'paid_at',
        'language',
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
        'paid_at' => 'datetime',
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

    public function buyerName(): string
    {
        return trim($this->buyer_first_name.' '.$this->buyer_last_name);
    }

    public function buyerPhone(): string
    {
        return trim($this->buyer_phone_country_code.' '.$this->buyer_phone);
    }

    public function isPaid(): bool
    {
        return $this->paid_at !== null;
    }

    public function hasPdf(): bool
    {
        return $this->design_type?->generatesPdf() && $this->pdf_path !== null;
    }
}
