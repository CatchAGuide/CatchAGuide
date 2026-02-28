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

    public static function statusOptions(): array
    {
        return [
            self::STATUS_OPEN => __('message.contact_request_status.open'),
            self::STATUS_IN_PROCESS => __('message.contact_request_status.in_process'),
            self::STATUS_DONE => __('message.contact_request_status.done'),
        ];
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
