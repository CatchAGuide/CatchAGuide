<?php

namespace App\Domain\CategoryPage;

final class CategoryPageDimension
{
    public const TARGETS = 'targets';

    public const METHODS = 'methods';

    public const COUNTRY = 'country';

    public const REGION = 'region';

    public const CITY = 'city';

    /** @return list<array{key: string, route: string, label: string, scopes: list<string>, legacy?: bool}> */
    public static function hubCards(): array
    {
        return [
            [
                'key' => self::TARGETS,
                'route' => 'admin.category.target-fish.index',
                'label' => __('admin.category_pages.dimensions.target_fish'),
                'scopes' => CategoryPageScope::forDimension(self::TARGETS),
            ],
            [
                'key' => self::METHODS,
                'route' => 'admin.category.methods.index',
                'label' => __('admin.category_pages.dimensions.methods'),
                'scopes' => CategoryPageScope::forDimension(self::METHODS),
            ],
            [
                'key' => self::COUNTRY,
                'route' => 'admin.category.country.index',
                'label' => __('admin.category_pages.dimensions.countries'),
                'scopes' => CategoryPageScope::forDimension(self::COUNTRY),
            ],
            [
                'key' => self::REGION,
                'route' => 'admin.category.region.index',
                'label' => __('admin.category_pages.dimensions.region'),
                'scopes' => CategoryPageScope::forDimension(self::REGION),
            ],
            [
                'key' => self::CITY,
                'route' => 'admin.category.city.index',
                'label' => __('admin.category_pages.dimensions.city'),
                'scopes' => CategoryPageScope::forDimension(self::CITY),
            ],
        ];
    }
}
