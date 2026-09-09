<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProductReport extends Model
{
    use HasFactory;

    public const STATUS_OPEN = 'open';
    public const STATUS_IN_PROCESS = 'in_process';
    public const STATUS_DONE = 'done';

    public const SOURCE_GUIDING = 'guiding';
    public const SOURCE_TRIP = 'trip';
    public const SOURCE_CAMP = 'camp';

    public const REASON_FRAUD = 'fraud';
    public const REASON_STOLEN_LISTING = 'stolen_listing';
    public const REASON_INACCURATE = 'inaccurate';
    public const REASON_COPYRIGHT = 'copyright';
    public const REASON_OTHER = 'other';

    protected $fillable = [
        'name',
        'email',
        'phone',
        'reason',
        'description',
        'reported_url',
        'source_type',
        'source_id',
        'status',
        'admin_comment',
        'locale',
        'ip',
    ];

    public static function statusOptions(): array
    {
        return [
            self::STATUS_OPEN => __('message.contact_request_status.open'),
            self::STATUS_IN_PROCESS => __('message.contact_request_status.in_process'),
            self::STATUS_DONE => __('message.contact_request_status.done'),
        ];
    }

    public static function reasonOptions(): array
    {
        return [
            self::REASON_FRAUD => __('notice-takedown.reasons.fraud'),
            self::REASON_STOLEN_LISTING => __('notice-takedown.reasons.stolen_listing'),
            self::REASON_INACCURATE => __('notice-takedown.reasons.inaccurate'),
            self::REASON_COPYRIGHT => __('notice-takedown.reasons.copyright'),
            self::REASON_OTHER => __('notice-takedown.reasons.other'),
        ];
    }

    public static function reasonKeys(): array
    {
        return array_keys(self::reasonOptions());
    }

    public static function sourceTypeLabel(?string $type): string
    {
        if (empty($type)) {
            return '—';
        }

        $key = 'message.contact_request_source.' . strtolower($type);
        $label = __($key);

        return $label !== $key ? $label : $type;
    }

    public function getReasonLabelAttribute(): string
    {
        return self::reasonOptions()[$this->reason] ?? $this->reason;
    }

    public function getSourceModel(): ?Model
    {
        if (empty($this->source_type) || empty($this->source_id)) {
            return null;
        }

        return match (strtolower($this->source_type)) {
            self::SOURCE_GUIDING => Guiding::find($this->source_id),
            self::SOURCE_CAMP => Camp::find($this->source_id),
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

    public function getSourceTitle(): ?string
    {
        $model = $this->getSourceModel();
        if (!$model) {
            return null;
        }

        return $model->title ?? $model->slug ?? ('#' . $this->source_id);
    }

    public function getSourceFrontUrl(): ?string
    {
        if (!empty($this->reported_url)) {
            return $this->reported_url;
        }

        $model = $this->getSourceModel();
        if (!$model) {
            return null;
        }

        return match (strtolower((string) $this->source_type)) {
            self::SOURCE_GUIDING => $model->slug
                ? $model->publicShowUrl()
                : null,
            self::SOURCE_CAMP => $model->slug
                ? route('vacations.camps.show', $model->slug)
                : null,
            self::SOURCE_TRIP => $model->slug
                ? route('vacations.trips.show', $model->slug)
                : null,
            default => null,
        };
    }

    public function getSourceAdminUrl(): ?string
    {
        $model = $this->getSourceModel();
        if (!$model) {
            return null;
        }

        try {
            return match (strtolower((string) $this->source_type)) {
                self::SOURCE_GUIDING => route('admin.guidings.edit', $model),
                self::SOURCE_CAMP => route('admin.camps.edit', $model),
                self::SOURCE_TRIP => route('admin.trips.edit', $model),
                default => null,
            };
        } catch (\Throwable $e) {
            return null;
        }
    }

    public static function resolveSourceTitle(?string $sourceType, $sourceId): ?string
    {
        if (empty($sourceType) || empty($sourceId)) {
            return null;
        }

        return (new self([
            'source_type' => $sourceType,
            'source_id' => (int) $sourceId,
        ]))->getSourceTitle();
    }
}
