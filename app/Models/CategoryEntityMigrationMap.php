<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CategoryEntityMigrationMap extends Model
{
    protected $table = 'category_entity_migration_map';

    protected $fillable = [
        'old_table',
        'old_id',
        'new_id',
    ];
}
