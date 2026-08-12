<?php

namespace App\Domain\CategoryPage;

final class CategoryPageEntityType
{
    public const CATEGORY_PAGE = 'category_page';

    public const TARGET_FISH = 'target_fish';

    public const METHOD = 'method';

    public const GEO_COUNTRY = 'geo_country';

    public const GEO_REGION = 'geo_region';

    public const GEO_CITY = 'geo_city';

    public const DESTINATION_COUNTRY = 'destination_country';

    public static function faqPageKey(string $entityType): string
    {
        return $entityType;
    }
}
