<?php

namespace App\Domain\CategoryPage;

final class CategoryPageScope
{
    public const GLOBAL = 'global';

    public const TOURS = 'tours';

    public const VACATIONS = 'vacations';

    public const TRIPS = 'trips';

    public const CAMPS = 'camps';

    public const LANGUAGE_TYPE = 'category_page';
    public static function all(): array
    {
        return [
            self::GLOBAL,
            self::TOURS,
            self::VACATIONS,
            self::TRIPS,
            self::CAMPS,
        ];
    }

    /** @return list<string> */
    public static function forDimension(string $dimension): array
    {
        return match ($dimension) {
            CategoryPageDimension::TARGETS => [self::GLOBAL, self::TOURS, self::VACATIONS],
            CategoryPageDimension::METHODS => [self::TOURS],
            CategoryPageDimension::COUNTRY => [self::GLOBAL, self::TOURS, self::VACATIONS, self::TRIPS, self::CAMPS],
            CategoryPageDimension::REGION, CategoryPageDimension::CITY => [self::TOURS],
            default => [],
        };
    }

    public static function label(string $scope): string
    {
        return match ($scope) {
            self::GLOBAL => __('admin.category_pages.scopes.global'),
            self::TOURS => __('admin.category_pages.scopes.tours'),
            self::VACATIONS => __('admin.category_pages.scopes.vacations'),
            self::TRIPS => __('admin.category_pages.scopes.trips'),
            self::CAMPS => __('admin.category_pages.scopes.camps'),
            default => $scope,
        };
    }
}
