<?php

namespace App\Models;

use App\Enums\Sales\SalesDocumentStatus;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

/**
 * The record behind an offer and its booking confirmation (Admin › Sales › Offers).
 *
 * @property SalesDocumentStatus $status
 */
class SalesDocument extends Model
{
    protected $fillable = [
        'status',
        'customer_id',
        'first_name',
        'last_name',
        'email',
        'phone',
        'traveller_names',
        'language',
        'valid_until',
        'intro_text_offer',
        'intro_text_confirmation',
        'good_to_know',
        'not_included',
        'payment_note',
        'travel_from',
        'travel_to',
        'total_amount',
        'offer_sent_at',
        'confirmation_sent_at',
        'viewed_at',
        'accepted_at',
        'acceptance_seen_at',
        'legacy_custom_camp_offer_id',
        'source_type',
        'source_id',
        'created_by',
        'updated_by',
    ];

    protected $casts = [
        'status' => SalesDocumentStatus::class,
        'traveller_names' => 'array',
        'not_included' => 'array',
        'valid_until' => 'date',
        'travel_from' => 'date',
        'travel_to' => 'date',
        'total_amount' => 'decimal:2',
        'offer_sent_at' => 'datetime',
        'confirmation_sent_at' => 'datetime',
        'viewed_at' => 'datetime',
        'accepted_at' => 'datetime',
        'acceptance_seen_at' => 'datetime',
    ];

    protected $attributes = [
        'status' => 'draft',
        'language' => 'de',
    ];

    protected static function booted(): void
    {
        static::creating(function (self $document) {
            $document->public_token ??= Str::random(32);
        });

        // The number derives from the id, so it is unique without a counter table or locking.
        static::created(function (self $document) {
            if ($document->number === null) {
                $document->number = self::formatNumber($document->id, $document->created_at?->year ?? now()->year);
                $document->saveQuietly();
            }
        });
    }

    public static function formatNumber(int $id, int $year): string
    {
        return sprintf('CAG-%d-%05d', $year, $id);
    }

    public function items(): HasMany
    {
        return $this->hasMany(SalesDocumentItem::class, 'document_id')->orderBy('sort_order')->orderBy('id');
    }

    public function cards(): HasMany
    {
        return $this->items()->whereNull('parent_item_id');
    }

    public function revisions(): HasMany
    {
        return $this->hasMany(SalesDocumentRevision::class, 'document_id')->orderBy('revision_no');
    }

    public function events(): HasMany
    {
        return $this->hasMany(SalesDocumentEvent::class, 'document_id')->orderByDesc('created_at')->orderByDesc('id');
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'customer_id');
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(Employee::class, 'created_by');
    }

    public function updater(): BelongsTo
    {
        return $this->belongsTo(Employee::class, 'updated_by');
    }

    public function scopeAwaitingCustomer(Builder $query): Builder
    {
        return $query->whereIn('status', [SalesDocumentStatus::Sent->value, SalesDocumentStatus::Viewed->value]);
    }

    public const SOURCE_CAMP_REQUEST = 'camp_request';

    public const SOURCE_TRIP_REQUEST = 'trip_request';

    /**
     * The camp or trip request this document was created from, if any.
     */
    public function sourceRequest(): CampVacationBooking|TripBooking|null
    {
        return match ($this->source_type) {
            self::SOURCE_CAMP_REQUEST => CampVacationBooking::query()->find($this->source_id),
            self::SOURCE_TRIP_REQUEST => TripBooking::query()->find($this->source_id),
            default => null,
        };
    }

    /**
     * Document numbers by request id for one request type (request lists' "open offer" buttons).
     *
     * @param  list<int>  $ids
     * @return array<int, array{id: int, number: string}>
     */
    public static function forSources(string $sourceType, array $ids): array
    {
        if ($ids === []) {
            return [];
        }

        return self::query()
            ->where('source_type', $sourceType)
            ->whereIn('source_id', $ids)
            ->orderBy('id')
            ->get(['id', 'number', 'source_id'])
            ->mapWithKeys(fn (self $document) => [(int) $document->source_id => ['id' => $document->id, 'number' => (string) $document->number]])
            ->all();
    }

    public function fullName(): string
    {
        return trim($this->first_name.' '.$this->last_name);
    }

    /**
     * First names for the greeting: the recipient, then the further travellers.
     *
     * @return list<string>
     */
    public function greetingNames(): array
    {
        return array_values(array_filter(array_map(
            fn ($name) => trim((string) $name),
            [$this->first_name, ...($this->traveller_names ?? [])],
        ), fn (string $name) => $name !== ''));
    }

    public function locale(): string
    {
        return in_array($this->language, ['de', 'en'], true) ? $this->language : 'de';
    }

    public function hasUnseenAcceptance(): bool
    {
        return $this->accepted_at !== null && $this->acceptance_seen_at === null;
    }
}
