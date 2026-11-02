<?php

use App\Domain\CategoryPage\CategoryPageEntityType;
use App\Domain\CategoryPage\CategoryPageScope;
use App\Models\Faq;
use App\Models\Language;
use Illuminate\Database\Migrations\Migration;

/**
 * The region/city `global` copy (/destination/{country}/{region}/{city}) was seeded from the
 * `tours` copy (/guidings/...) by the since-removed category-pages:copy-geo-tours-to-global
 * command. Two URLs with the same text is duplicate content, so all of it is removed; admins
 * write the global copy by hand from now on. The tours copy is untouched.
 */
return new class extends Migration
{
    private const ENTITY_TYPES = [CategoryPageEntityType::GEO_REGION, CategoryPageEntityType::GEO_CITY];

    public function up(): void
    {
        Language::query()
            ->whereIn('type', self::ENTITY_TYPES)
            ->where('scope', CategoryPageScope::GLOBAL)
            ->delete();

        Faq::query()
            ->whereIn('page', array_map(CategoryPageEntityType::faqPageKey(...), self::ENTITY_TYPES))
            ->where('scope', CategoryPageScope::GLOBAL)
            ->delete();
    }

    public function down(): void
    {
        // Deleted copy is not restored; it was a duplicate of the tours copy.
    }
};
