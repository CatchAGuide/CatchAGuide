<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class GuideTag extends Model
{
    public const KIND_SPECIALTY = 'specialty';
    public const KIND_GOOD_FOR = 'good_for';

    protected $fillable = ['user_id', 'kind', 'value', 'is_custom', 'position'];

    protected $casts = [
        'is_custom' => 'boolean',
        'position' => 'integer',
    ];

    public static function kinds(): array
    {
        return [self::KIND_SPECIALTY, self::KIND_GOOD_FOR];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /** Display label: the translated suggestion, or the guide's own text for custom tags. */
    public function label(): string
    {
        return $this->is_custom ? $this->value : __('guide_area.tags.'.$this->kind.'.'.$this->value);
    }
}
