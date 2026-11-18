<?php

namespace App\Support;

use App\Models\User;
use App\Services\Guide\Area\GuideBookingService;
use App\Services\Guide\Area\GuideEarningsService;
use App\Services\Guide\Area\GuideOptimizationService;
use App\Services\Guide\Area\GuideReviewService;
use App\Services\Guide\Area\GuideVoucherService;
use Illuminate\Support\Facades\Route;

/**
 * Sidebar of the Guide Area (/guide/*), same idea as SitePrimaryNav (spec 2.1). Badges: open
 * bookings (requests + voucher orders not yet paid and sent), open to-dos, NEW reviews, and a
 * dot when a commission invoice is overdue. Account & security still links to the old page.
 */
class GuideAreaNav
{
    /**
     * @return array<int, array{section: ?string, items: array<int, array{key: string, label: string, url: string, icon: string, active: bool, badge: ?int, badge_tone: string, dot: bool}>}>
     */
    public static function sections(?User $guide = null): array
    {
        $guide ??= auth('web')->user();
        $badges = $guide ? self::badges($guide) : [];

        $item = fn (string $key, string $icon, string $route, ?string $activePattern = null) => [
            'key' => $key,
            'label' => __('guide_area.nav.'.$key),
            'url' => Route::has($route) ? route($route) : '#',
            'icon' => $icon,
            'active' => request()->routeIs($activePattern ?? $route),
            'badge' => ($badges[$key] ?? 0) > 0 ? (int) $badges[$key] : null,
            'badge_tone' => $key === 'bookings' ? 'warning' : 'neutral',
            'dot' => $key === 'invoices' && ($badges['invoices_overdue'] ?? false),
        ];

        return [
            ['section' => null, 'items' => [
                $item('overview', 'fa-home', 'guide.area.overview'),
                $item('bookings', 'fa-inbox', 'guide.area.bookings'),
                $item('todos', 'fa-tasks', 'guide.area.todos'),
                $item('calendar', 'fa-calendar', 'guide.area.calendar'),
            ]],
            ['section' => __('guide_area.nav.section_offers'), 'items' => [
                $item('tours', 'fa-fish', 'guide.area.tours'),
                $item('vouchers', 'fa-ticket-alt', 'guide.area.vouchers.settings'),
                $item('reviews', 'fa-star', 'guide.area.reviews'),
            ]],
            ['section' => __('guide_area.nav.section_account'), 'items' => [
                $item('invoices', 'fa-file-invoice', 'guide.area.invoices', 'guide.area.invoices*'),
                $item('profile', 'fa-user', 'guide.area.profile'),
                $item('account', 'fa-lock', 'profile.settings'),
            ]],
        ];
    }

    /** @return array{bookings: int, todos: int, reviews: int, invoices_overdue: bool} */
    private static function badges(User $guide): array
    {
        return [
            'bookings' => app(GuideBookingService::class)->openCount($guide) + app(GuideVoucherService::class)->counts($guide)[GuideVoucherService::FILTER_TODO],
            'todos' => app(GuideOptimizationService::class)->evaluate($guide)['open'],
            'reviews' => app(GuideReviewService::class)->unseenCount($guide),
            'invoices_overdue' => app(GuideEarningsService::class)->hasOverdue($guide),
        ];
    }
}
