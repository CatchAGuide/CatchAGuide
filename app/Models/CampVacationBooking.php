<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphOne;

class CampVacationBooking extends Model
{
    use HasFactory;

    public const STATUS_OPEN = 'open';
    public const STATUS_IN_PROCESS = 'in_process';
    public const STATUS_DONE = 'done';

    public const SOURCE_CAMP = 'camp';
    public const SOURCE_VACATION = 'vacation';

    protected $table = 'camp_vacation_bookings';

    protected $fillable = [
        'source_type',
        'source_id',
        'preferred_date',
        'nights',
        'accommodation_id',
        'rental_boat_id',
        'guiding_id',
        'special_offer_id',
        'estimated_total',
        'currency',
        'price_breakdown',
        'number_of_persons',
        'name',
        'first_name',
        'last_name',
        'email',
        'user_id',
        'language',
        'phone_country_code',
        'phone',
        'message',
        'status',
    ];

    protected $casts = [
        'preferred_date' => 'date',
        'number_of_persons' => 'integer',
        'nights' => 'integer',
        'estimated_total' => 'decimal:2',
        'price_breakdown' => 'array',
    ];

    public function camp(): BelongsTo
    {
        return $this->belongsTo(Camp::class, 'source_id');
    }

    public function accommodation(): BelongsTo
    {
        return $this->belongsTo(Accommodation::class);
    }

    public function rentalBoat(): BelongsTo
    {
        return $this->belongsTo(RentalBoat::class);
    }

    public function guiding(): BelongsTo
    {
        return $this->belongsTo(Guiding::class);
    }

    public function specialOffer(): BelongsTo
    {
        return $this->belongsTo(SpecialOffer::class);
    }

    public static function statusOptions(): array
    {
        return [
            self::STATUS_OPEN => __('message.contact_request_status.open'),
            self::STATUS_IN_PROCESS => __('message.contact_request_status.in_process'),
            self::STATUS_DONE => __('message.contact_request_status.done'),
        ];
    }

    public static function sourceTypeLabel(string $type): string
    {
        $key = 'message.contact_request_source.' . strtolower($type);
        $label = __($key);
        return $label !== $key ? $label : ($type ?: '—');
    }

    public function getSourceModel(): ?Model
    {
        if (empty($this->source_type) || empty($this->source_id)) {
            return null;
        }

        return match (strtolower($this->source_type)) {
            self::SOURCE_CAMP => Camp::find($this->source_id),
            self::SOURCE_VACATION => Vacation::find($this->source_id),
            default => null,
        };
    }

    public function getSourceLabel(): ?string
    {
        $model = $this->getSourceModel();
        if (!$model) {
            if ($this->source_type && $this->source_id) {
                return self::sourceTypeLabel($this->source_type) . ' #' . $this->source_id;
            }
            return null;
        }

        $title = $model->title ?? $model->slug ?? ('#' . $this->source_id);
        if (is_string($title)) {
            return self::sourceTypeLabel($this->source_type) . ' #' . $this->source_id . ': ' . $title;
        }
        return self::sourceTypeLabel($this->source_type) . ' #' . $this->source_id;
    }

    public function getSourceFrontUrl(): ?string
    {
        $model = $this->getSourceModel();
        if (!$model) {
            return null;
        }

        return match (strtolower($this->source_type)) {
            self::SOURCE_CAMP => $model->slug ? route('vacations.camps.show', $model->slug) : null,
            self::SOURCE_VACATION => $model->slug ? route('vacations.camps.show', $model->slug) : null,
            default => null,
        };
    }

    public function getSourceThumbnailUrl(): ?string
    {
        $model = $this->getSourceModel();
        if (!$model || empty($model->thumbnail_path)) {
            return null;
        }
        $path = trim((string) $model->thumbnail_path);
        if ($path === '') {
            return null;
        }
        if (str_starts_with($path, 'http') || str_starts_with($path, '//')) {
            return $path;
        }
        return asset(ltrim($path, '/'));
    }

    public function getSourceLocation(): ?string
    {
        $model = $this->getSourceModel();
        if (!$model) {
            return null;
        }
        if (!empty($model->location)) {
            return (string) $model->location;
        }
        $parts = array_filter([
            $model->city ?? null,
            $model->region ?? null,
            $model->country ?? null,
        ]);
        return $parts ? implode(', ', $parts) : null;
    }

    public function getSourceTitle(): ?string
    {
        $model = $this->getSourceModel();
        if (!$model) {
            return null;
        }
        return $model->title ?? $model->slug ?? ('#' . $this->source_id);
    }

    public function financeItem(): MorphOne
    {
        return $this->morphOne(FinanceItem::class, 'billable');
    }
}

