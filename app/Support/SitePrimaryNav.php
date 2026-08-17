<?php

namespace App\Support;

use Illuminate\Http\Request;

final class SitePrimaryNav
{
    /**
     * @return list<array{key: string, label: string, url: string, active: bool}>
     */
    public static function links(?Request $request = null): array
    {
        $section = static::activeSection($request);

        return [
            [
                'key' => 'offers',
                'label' => __('offers.nav_label'),
                'url' => route('offers.index'),
                'active' => $section === 'offers',
            ],
            [
                'key' => 'tours',
                'label' => __('homepage.filter-fishing-near-me'),
                'url' => route('guidings.landing'),
                'active' => $section === 'tours',
            ],
            [
                'key' => 'vacations',
                'label' => __('homepage.header-vacations'),
                'url' => route('vacations.index'),
                'active' => $section === 'vacations',
            ],
        ];
    }

    public static function activeSection(?Request $request = null): ?string
    {
        $request ??= request();

        if ($request->routeIs('guidings.*') || $request->is('guidings*')) {
            return 'tours';
        }

        if (
            $request->routeIs('vacations.*', 'trips.show', 'trips.index', 'trips.category')
            || $request->is('vacations*', 'trips*', 'vacations-v2*')
        ) {
            return 'vacations';
        }

        if ($request->routeIs('offers.*') || $request->is('offers*')) {
            return 'offers';
        }

        return null;
    }
}
