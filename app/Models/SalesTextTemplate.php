<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * An admin-edited customer text of the offer builder for one language (see SalesTexts).
 */
class SalesTextTemplate extends Model
{
    protected $fillable = ['key', 'language', 'body', 'updated_by'];
}
