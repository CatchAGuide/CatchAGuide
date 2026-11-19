<?php

namespace App\Models;

use App\Enums\CertificateStatus;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class GuideCertificate extends Model
{
    protected $fillable = [
        'user_id',
        'type',
        'title',
        'file_disk',
        'file_path',
        'original_name',
        'mime',
        'size',
        'valid_until',
        'status',
        'reviewed_by',
        'reviewed_at',
        'rejection_reason',
    ];

    protected $casts = [
        'status' => CertificateStatus::class,
        'valid_until' => 'date',
        'reviewed_at' => 'datetime',
        'size' => 'integer',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function scopeInReview(Builder $query): Builder
    {
        return $query->where('status', CertificateStatus::InReview->value);
    }

    public function label(): string
    {
        if ($this->type === 'other' && filled($this->title)) {
            return (string) $this->title;
        }

        return __('guide_area.certificates.types.'.$this->type);
    }

    public function isExpired(): bool
    {
        return $this->valid_until !== null && $this->valid_until->endOfDay()->isPast();
    }
}
