<?php

namespace App\Models;

use App\Enums\Sales\SalesItemType;
use App\Enums\Sales\SalesPriceUnit;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * One priced row of a sales document, with the listing data snapshotted when it was added.
 *
 * @property SalesItemType $item_type
 */
class SalesDocumentItem extends Model
{
    protected $fillable = [
        'document_id',
        'parent_item_id',
        'item_type',
        'listing_type',
        'listing_id',
        'partner_id',
        'title_snapshot',
        'location_snapshot',
        'listing_url_snapshot',
        'description',
        'date_from',
        'date_to',
        'persons',
        'persons_follow_parent',
        'days',
        'quantity',
        'unit_label',
        'price_unit',
        'unit_price',
        'calculated_price',
        'line_total',
        'is_adjusted',
        'meta',
        'sort_order',
    ];

    protected $casts = [
        'item_type' => SalesItemType::class,
        'price_unit' => SalesPriceUnit::class,
        'date_from' => 'date',
        'date_to' => 'date',
        'persons_follow_parent' => 'boolean',
        'is_adjusted' => 'boolean',
        'unit_price' => 'decimal:2',
        'calculated_price' => 'decimal:2',
        'line_total' => 'decimal:2',
        'meta' => 'array',
    ];

    public function document(): BelongsTo
    {
        return $this->belongsTo(SalesDocument::class, 'document_id');
    }

    public function parent(): BelongsTo
    {
        return $this->belongsTo(self::class, 'parent_item_id');
    }

    public function children(): HasMany
    {
        return $this->hasMany(self::class, 'parent_item_id')->orderBy('sort_order')->orderBy('id');
    }

    public function partner(): BelongsTo
    {
        return $this->belongsTo(User::class, 'partner_id');
    }
}
