<?php

namespace App\Services\Vacation;

use App\Models\Camp;
use App\Models\Destination;
use App\Models\Trip;
use App\Domain\Destination\DestinationCategoryType;

class VacationSitemapContributor
{
    /**
     * @return array<int, array{loc: string, changefreq: string, priority: float}>
     */
    public function entries(string $baseUrl): array
    {
        $baseUrl = rtrim($baseUrl, '/');
        $entries = [];

        $static = [
            '/vacations' => ['changefreq' => 'weekly', 'priority' => 0.9],
            '/vacations/trips' => ['changefreq' => 'weekly', 'priority' => 0.8],
            '/vacations/camps' => ['changefreq' => 'weekly', 'priority' => 0.8],
        ];

        foreach ($static as $path => $meta) {
            $entries[] = ['loc' => $baseUrl . $path, ...$meta];
        }

        $countrySlugs = Destination::query()
            ->where('language', app()->getLocale())
            ->whereIn('type', [DestinationCategoryType::VACATIONS, DestinationCategoryType::TRIPS])
            ->pluck('slug')
            ->merge(
                Camp::query()->where('status', 'active')->whereNotNull('country')->pluck('country')
            )
            ->merge(
                Trip::query()->where('status', 'active')->whereNotNull('country')->pluck('country')
            )
            ->unique()
            ->filter();

        foreach ($countrySlugs as $slug) {
            $entries[] = [
                'loc' => $baseUrl . '/vacations/' . $slug,
                'changefreq' => 'weekly',
                'priority' => 0.75,
            ];
        }

        foreach (Trip::query()->where('status', 'active')->whereNotNull('slug')->where('slug', '!=', '')->get(['slug']) as $trip) {
            $entries[] = [
                'loc' => $baseUrl . '/vacations/trips/' . $trip->slug,
                'changefreq' => 'monthly',
                'priority' => 0.7,
            ];
        }

        foreach (Camp::query()->where('status', 'active')->whereNotNull('slug')->where('slug', '!=', '')->get(['slug']) as $camp) {
            $entries[] = [
                'loc' => $baseUrl . '/vacations/camps/' . $camp->slug,
                'changefreq' => 'monthly',
                'priority' => 0.7,
            ];
        }

        return $entries;
    }
}
