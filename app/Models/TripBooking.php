<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TripBooking extends Model
{
    use HasFactory;

    public const STATUS_OPEN = 'open';
    public const STATUS_IN_PROCESS = 'in_process';
    public const STATUS_DONE = 'done';

    public const SOURCE_TRIP = 'trip';

    protected $table = 'trip_bookings';

    protected $fillable = [
        'source_type',
        'source_id',
        'preferred_date',
        'number_of_persons',
        'name',
        'email',
        'phone_country_code',
        'phone',
        'message',
        'status',
    ];

    protected $casts = [
        'preferred_date' => 'date',
        'number_of_persons' => 'integer',
    ];

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
            self::SOURCE_TRIP => Trip::find($this->source_id),
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
            self::SOURCE_TRIP => $model->slug ? route('trips.show', $model->slug) : null,
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
}

