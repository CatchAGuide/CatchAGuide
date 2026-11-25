<?php

namespace App\Support;

use App\Models\User;
use Illuminate\Support\Facades\Route;

/**
 * Sidebar of the customer pages of /profile/* in the Guide Area design: only what an angler
 * who is not a guide can use. Guides who use the Guide Area get GuideAreaNav instead, which
 * links the same customer pages (My bookings, Account & security) from its Account section.
 */
class CustomerAreaNav
{
    /** Sidebar for whoever is signed in: the guide sidebar for Guide Area guides. */
    public static function forUser(?User $user = null): array
    {
        $user ??= auth('web')->user();

        return $user?->usesGuideArea() ? GuideAreaNav::sections($user) : self::sections();
    }

    /**
     * @return array<int, array{section: ?string, items: array<int, array{key: string, label: string, url: string, icon: string, active: bool, badge: ?int, badge_tone: string, dot: bool}>}>
     */
    public static function sections(): array
    {
        $item = fn (string $key, string $icon, string $route, array $active = []) => [
            'key' => $key,
            'label' => __('guide_area.customer.nav.'.$key),
            'url' => Route::has($route) ? route($route) : '#',
            'icon' => $icon,
            'active' => request()->routeIs($active !== [] ? $active : [$route]),
            'badge' => null,
            'badge_tone' => 'neutral',
            'dot' => false,
        ];

        return [
            ['section' => null, 'items' => [
                $item('overview', 'fa-home', 'profile.index'),
                $item('bookings', 'fa-suitcase-rolling', 'profile.bookings', ['profile.bookings', 'profile.my-bookings', 'profile.showbooking']),
                $item('favourites', 'fa-heart', 'profile.favoriteguides'),
            ]],
            ['section' => __('guide_area.nav.section_account'), 'items' => [
                $item('details', 'fa-user', 'profile.settings'),
                $item('password', 'fa-lock', 'profile.password'),
            ]],
        ];
    }

    /** "Become a guide" box under the customer sidebar, or null when it doesn't apply. */
    public static function becomeGuideUrl(?User $user = null): ?string
    {
        $user ??= auth('web')->user();

        if (! $user || $user->usesGuideArea() || ! $user->canApplyAsGuide()) {
            return null;
        }

        return route(config('guide_onboarding.new_onboarding_enabled') ? 'guide.onboarding' : 'profile.becomeguide');
    }
}
