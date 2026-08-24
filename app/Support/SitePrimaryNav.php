<?php

namespace App\Support;

use App\Repositories\Vacation\VacationDestinationRepository;
use Illuminate\Http\Request;

final class SitePrimaryNav
{
    /**
     * Desktop header links (catalog sections only).
     *
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

    /**
     * Burger / mobile browse links: desktop catalog plus Destinations.
     *
     * @return list<array{key: string, label: string, url: string, active: bool, icon: string}>
     */
    public static function browseLinks(?Request $request = null): array
    {
        $section = static::activeSection($request);
        $links = static::links($request);
        $links[] = [
            'key' => 'destinations',
            'label' => __('homepage.footer_destinations'),
            'url' => route('destination'),
            'active' => $section === 'destinations',
        ];

        foreach ($links as $index => $link) {
            $links[$index]['icon'] = static::iconFor($link['key']);
        }

        return $links;
    }

    /**
     * Fixed mobile bottom bar: browse links plus login/profile.
     *
     * @return list<array{key: string, label: string, url: string, active: bool, icon: string, opens_login: bool}>
     */
    public static function bottomNavLinks(?Request $request = null): array
    {
        $request ??= request();
        $links = [];

        foreach (static::browseLinks($request) as $link) {
            $link['opens_login'] = false;
            $links[] = $link;
        }

        $authenticated = auth()->check();
        $links[] = [
            'key' => 'account',
            'label' => $authenticated ? __('homepage.header-profile') : __('homepage.header-login'),
            'url' => $authenticated ? route('profile.index') : '#',
            'active' => $authenticated && $request->routeIs('profile.*'),
            'icon' => static::iconFor('account'),
            'opens_login' => ! $authenticated,
        ];

        return $links;
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

        if ($request->routeIs('destination', 'destination.*') || $request->is('destination*')) {
            return 'destinations';
        }

        return null;
    }

    /**
     * Catalog / homepage pages render site-nav inside a hero shell.
     * Layouts must not add a second solid header on these routes.
     */
    public static function usesOverlayHeader(?Request $request = null): bool
    {
        $request ??= request();

        if (static::isHomepage($request)) {
            return true;
        }

        return $request->routeIs(
            'offers.*',
            'vacations.index',
            'vacations.country',
            'vacations.all-offers',
            'vacations.trips.index',
            'vacations.camps.index',
            'vacations.trips.show',
            'vacations.camps.show',
            'vacations.show',
            'vacations.v2',
            'vacations.targets',
            'destination',
            'destination.country',
            'destination.legacy-geo',
            'targets.index',
            'targets.show',
            'guidings.landing',
            'guidings.index',
            'guidings.destination',
            'guidings.countries',
            'guidings.methods',
            'guidings.methods.show',
            'guidings.targets',
            'guidings.targets.index',
            'guidings.show',
            'trips.show',
            'category.types',
            'category.targets',
            'additional.partner',
        );
    }

    public static function isHomepage(?Request $request = null): bool
    {
        $request ??= request();

        return $request->routeIs('welcome') || $request->is('/');
    }

    /**
     * Solid header in the layout (every public page that does not own an overlay nav).
     */
    public static function usesLayoutNav(?Request $request = null): bool
    {
        return ! static::usesOverlayHeader($request);
    }

    public static function usesVacationLoadingOverlay(?Request $request = null): bool
    {
        $request ??= request();

        return $request->routeIs(
            'vacations.index',
            'vacations.country',
            'vacations.all-offers',
            'vacations.trips.index',
            'vacations.camps.index',
            'vacations.trips.show',
            'vacations.camps.show',
            'vacations.show',
            'vacations.v2',
            'trips.show',
        );
    }

    /**
     * Mobile catalog bar on public pages. Product detail and checkout stay clear.
     */
    public static function usesLayoutBottomNav(?Request $request = null): bool
    {
        $request ??= request();

        return ! static::isCheckoutPage($request) && ! static::isProductDetailPage($request);
    }

    public static function isCheckoutPage(?Request $request = null): bool
    {
        $request ??= request();

        return $request->routeIs('checkout', 'checkout.*') || $request->is('checkout*');
    }

    public static function isProductDetailPage(?Request $request = null): bool
    {
        $request ??= request();

        if ($request->routeIs(
            'guidings.show',
            'guidings.show.legacy',
            'trips.show',
            'vacations.v2',
            'vacations.show',
        )) {
            return true;
        }

        if (! $request->routeIs('vacations.trips.show', 'vacations.camps.show')) {
            return false;
        }

        $slug = strtolower((string) $request->route('slug'));
        if ($slug === '') {
            return true;
        }

        $pillar = $request->routeIs('vacations.camps.show') ? 'camps' : 'trips';

        return ! app(VacationDestinationRepository::class)->isKnownCountrySlug($slug, $pillar);
    }

    /**
     * Inner pages keep a slate title/search band under the overlay nav
     * (the old dark header). Checkout stays a solid bar only.
     */
    public static function usesLayoutPageHeader(?Request $request = null): bool
    {
        $request ??= request();

        return static::usesLayoutNav($request)
            && ! static::isCheckoutPage($request);
    }

    private static function iconFor(string $key): string
    {
        return match ($key) {
            'offers' => 'fas fa-th-large',
            'tours' => 'fas fa-ship',
            'vacations' => 'fas fa-suitcase-rolling',
            'destinations' => 'fas fa-map-marker-alt',
            'account' => 'far fa-user',
            default => 'far fa-circle',
        };
    }
}
