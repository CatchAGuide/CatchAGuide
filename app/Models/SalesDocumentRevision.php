<?php

namespace App\Models;

use App\Enums\Sales\SalesDocumentOutput;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * What a sales document looked like when it was sent (written on every send).
 */
class SalesDocumentRevision extends Model
{
    public const UPDATED_AT = null;

    protected $fillable = ['document_id', 'revision_no', 'output', 'snapshot', 'created_by'];

    protected $casts = [
        'output' => SalesDocumentOutput::class,
        'snapshot' => 'array',
    ];

    public function document(): BelongsTo
    {
        return $this->belongsTo(SalesDocument::class, 'document_id');
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(Employee::class, 'created_by');
    }
}
