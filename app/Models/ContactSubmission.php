<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ContactSubmission extends Model
{
    use HasFactory;

    public const STATUS_OPEN = 'open';
    public const STATUS_IN_PROCESS = 'in_process';
    public const STATUS_DONE = 'done';

    public const SOURCE_GUIDING = 'guiding';
    public const SOURCE_VACATION = 'vacation';
    public const SOURCE_CAMP = 'camp';
    public const SOURCE_TRIP = 'trip';

    public static function statusOptions(): array
    {
        return [
            self::STATUS_OPEN => __('message.contact_request_status.open'),
            self::STATUS_IN_PROCESS => __('message.contact_request_status.in_process'),
            self::STATUS_DONE => __('message.contact_request_status.done'),
        ];
    }

    /**
     * Human-readable label for source type.
     */
    public static function sourceTypeLabel(string $type): string
    {
        $key = 'message.contact_request_source.' . strtolower($type);
        $label = __($key);
        return $label !== $key ? $label : ($type ?: '—');
    }

    /**
     * Resolve the source model (Guiding, Camp, Vacation, Trip) from source_type and source_id.
     */
    public function getSourceModel(): ?Model
    {
        if (empty($this->source_type) || empty($this->source_id)) {
            return null;
        }

        return match (strtolower($this->source_type)) {
            self::SOURCE_GUIDING => Guiding::find($this->source_id),
            self::SOURCE_CAMP => Camp::find($this->source_id),
            self::SOURCE_VACATION => Vacation::find($this->source_id),
            self::SOURCE_TRIP => Trip::find($this->source_id),
            default => null,
        };
    }

    /**
     * Human-readable label for the source (e.g. "Guiding #123: Lake Tour").
     */
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

    /**
     * Public/frontend URL where the contact form was submitted (e.g. guiding or trip page).
     */
    public function getSourceFrontUrl(): ?string
    {
        $model = $this->getSourceModel();
        if (!$model) {
            return null;
        }

        return match (strtolower($this->source_type)) {
            self::SOURCE_GUIDING => $model->slug ? route('guidings.show', ['id' => $model->id, 'slug' => $model->slug]) : null,
            self::SOURCE_CAMP => $model->slug ? route('vacations.show', $model->slug) : null,
            self::SOURCE_VACATION => $model->slug ? route('vacations.show', $model->slug) : null,
            self::SOURCE_TRIP => $model->slug ? route('trips.show', $model->slug) : null,
            default => null,
        };
    }

    /**
     * Thumbnail URL for the source (for display in admin). Returns null if no image.
     */
    public function getSourceThumbnailUrl(): ?string
    {
        $model = $this->getSourceModel();
        if (!$model || empty($model->thumbnail_path)) {
            return null;
        }
        $path = trim($model->thumbnail_path);
        if ($path === '') {
            return null;
        }
        if (str_starts_with($path, 'http') || str_starts_with($path, '//')) {
            return $path;
        }
        return asset(ltrim($path, '/'));
    }

    /**
     * Location string for the source (e.g. city, region, country).
     */
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

    /**
     * Title of the source (for display).
     */
    public function getSourceTitle(): ?string
    {
        $model = $this->getSourceModel();
        if (!$model) {
            return null;
        }
        return $model->title ?? $model->slug ?? ('#' . $this->source_id);
    }

    /**
     * Admin URL to edit/view the source (e.g. admin guiding edit page).
     */
    public function getSourceAdminUrl(): ?string
    {
        $model = $this->getSourceModel();
        if (!$model) {
            return null;
        }

        try {
            return match (strtolower($this->source_type)) {
                self::SOURCE_GUIDING => route('admin.guidings.edit', $model),
                self::SOURCE_CAMP => route('admin.camps.edit', $model),
                self::SOURCE_VACATION => route('admin.vacations.edit', $model),
                self::SOURCE_TRIP => route('admin.trips.edit', $model),
                default => null,
            };
        } catch (\Throwable $e) {
            return null;
        }
    }

    protected $fillable = [
        'name',
        'email',
        'phone',
        'description',
        'source_type',
        'source_id',
        'status',
    ];
}
