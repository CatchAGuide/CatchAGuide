<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Storage;

/**
 * App\Models\CamperImage
 *
 * @property int $id
 * @property string $file_path
 * @property int $camper_id
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\Camper $camper
 * @method static \Database\Factories\CamperImageFactory factory(...$parameters)
 * @method static \Illuminate\Database\Eloquent\Builder|CamperImage newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|CamperImage newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|CamperImage query()
 * @method static \Illuminate\Database\Eloquent\Builder|CamperImage whereCamperId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|CamperImage whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|CamperImage whereFilePath($value)
 * @method static \Illuminate\Database\Eloquent\Builder|CamperImage whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|CamperImage whereUpdatedAt($value)
 * @mixin \Eloquent
 */
class CamperImage extends Model
{

    use HasFactory;

    protected $fillable =
        [
            'file_path'
        ];

    /**
     * @return BelongsTo
     */
    public function camper(): BelongsTo
    {
        return $this->belongsTo(Camper::class);
    }

    /**
     *
     */
    public function getImage(): ?string
    {
        return Storage::disk('public')->url($this->file_path);
    }
}
