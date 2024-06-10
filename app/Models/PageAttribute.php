<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PageAttribute extends Model
{

    use HasFactory;

    protected $fillable = [
        'page',
        'meta_type',
        'domain',
        'uri',
        'content',
    ];
}
