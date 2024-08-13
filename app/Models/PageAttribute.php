<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class PageAttribute extends Model
{

    use HasFactory, SoftDeletes;

    protected $fillable = [
        'page',
        'meta_type',
        'domain',
        'uri',
        'content',
    ];

    public function getDeletedAtFormatAttribute()
    {
        if (!is_null($this->deleted_at)) {
            return date('Y-M-d H:i', strtotime($this->deleted_at));
        }

        return $this->deleted_at;
    }
}
