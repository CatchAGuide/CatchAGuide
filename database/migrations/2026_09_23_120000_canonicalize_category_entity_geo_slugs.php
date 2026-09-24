<?php

use App\Domain\Vacation\CountrySlug;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * Country/region/city slugs are URL segments. Rows stored with an uppercase slug
 * ("Österreich", "Ägypten") made every template that links via $entity->slug emit a URL that
 * only exists to 301 to the lowercase form. Lowercase them in place; lookups already match
 * either casing, so existing links keep resolving.
 */
return new class extends Migration
{
    public function up(): void
    {
        $rows = DB::table('category_entities')
            ->whereIn('type', ['country', 'region', 'city'])
            ->whereNotNull('slug')
            ->get(['id', 'type', 'slug', 'country_id', 'region_id']);

        foreach ($rows as $row) {
            $canonical = CountrySlug::canonicalize($row->slug);
            if ($canonical === null || $canonical === $row->slug) {
                continue;
            }

            // Never merge two entities into one slug — leave a colliding row for manual review.
            $collides = $rows->contains(fn ($other) => $other->id !== $row->id
                && $other->type === $row->type
                && $other->country_id === $row->country_id
                && $other->region_id === $row->region_id
                && $other->slug === $canonical);

            if (! $collides) {
                DB::table('category_entities')->where('id', $row->id)->update(['slug' => $canonical]);
            }
        }
    }

    public function down(): void
    {
        // Lossy by design: the original casing isn't needed to resolve any URL.
    }
};
