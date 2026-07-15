<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TermsSectionTranslation extends Model
{
    protected $fillable = [
        'terms_section_id',
        'language',
        'title',
        'content',
    ];

    public function section(): BelongsTo
    {
        return $this->belongsTo(TermsSection::class, 'terms_section_id');
    }
}
