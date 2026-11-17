<?php

namespace App\Support;

use Illuminate\Support\Facades\Route;

/**
 * Sidebar of the Guide Area (/guide/*), same idea as SitePrimaryNav. Pages not rebuilt yet
 * link to their current /profile/* page so the sidebar is complete during the beta.
 */
class GuideAreaNav
{
    /**
     * @return array<int, array{section: ?string, items: array<int, array{key: string, label: string, url: string, icon: string, active: bool}>}>
     */
    public static function sections(): array
    {
        $item = fn (string $key, string $icon, string $route, ?string $activePattern = null) => [
            'key' => $key,
            'label' => __('guide_area.nav.'.$key),
            'url' => Route::has($route) ? route($route) : '#',
            'icon' => $icon,
            'active' => request()->routeIs($activePattern ?? $route),
        ];

        return [
            ['section' => null, 'items' => [
                $item('overview', 'fa-home', 'profile.index'),
                $item('bookings', 'fa-inbox', 'profile.bookings'),
                $item('calendar', 'fa-calendar', 'profile.calendar'),
            ]],
            ['section' => __('guide_area.nav.section_offers'), 'items' => [
                $item('tours', 'fa-fish', 'guide.area.tours'),
                $item('vouchers', 'fa-ticket-alt', 'guide.area.vouchers', 'guide.area.vouchers*'),
            ]],
            ['section' => __('guide_area.nav.section_account'), 'items' => [
                $item('profile', 'fa-user', 'guide.area.profile'),
                $item('account', 'fa-lock', 'profile.settings'),
            ]],
        ];
    }
}
