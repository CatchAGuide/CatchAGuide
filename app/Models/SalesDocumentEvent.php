<?php

namespace App\Models;

use App\Enums\Sales\SalesEventType;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Status log entry of a sales document.
 */
class SalesDocumentEvent extends Model
{
    public const UPDATED_AT = null;

    public const ACTOR_EMPLOYEE = 'employee';

    public const ACTOR_CUSTOMER = 'customer';

    public const ACTOR_SYSTEM = 'system';

    protected $fillable = ['document_id', 'type', 'employee_id', 'actor', 'payload'];

    protected $casts = [
        'type' => SalesEventType::class,
        'payload' => 'array',
    ];

    public function document(): BelongsTo
    {
        return $this->belongsTo(SalesDocument::class, 'document_id');
    }

    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class, 'employee_id');
    }
}
