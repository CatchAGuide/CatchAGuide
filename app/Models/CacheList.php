<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CacheList extends Model
{
    protected $fillable = [
        'table',
        'table_id'
    ];

}
