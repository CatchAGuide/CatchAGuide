<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * Species/method page slugs are URL segments. A row stored as "Äsche" made /targets/Äsche and
 * /targets/äsche two 200 URLs for one page; lowercase them (the uppercase form now 301s).
 */
return new class extends Migration
{
    public function up(): void
    {
        $rows = DB::table('category_pages')->whereNotNull('slug')->get(['id', 'type', 'slug']);

        foreach ($rows as $row) {
            $lower = mb_strtolower($row->slug, 'UTF-8');
            if ($lower === $row->slug) {
                continue;
            }

            $collides = $rows->contains(fn ($other) => $other->id !== $row->id
                && strtolower((string) $other->type) === strtolower((string) $row->type)
                && $other->slug === $lower);

            if (! $collides) {
                DB::table('category_pages')->where('id', $row->id)->update(['slug' => $lower]);
            }
        }
    }

    public function down(): void
    {
        // Lossy by design: the original casing isn't needed to resolve any URL.
    }
};
