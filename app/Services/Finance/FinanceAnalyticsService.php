<?php

namespace App\Services\Finance;

use App\Models\Booking;
use App\Models\Guiding;
use App\Models\Review;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

/**
 * Aggregates guiding-booking finance KPIs for the admin analytics dashboard.
 * Revenue uses reservation date (calendar / blocked event / book_date), consistent with invoice views.
 */
class FinanceAnalyticsService
{
    private const COUNTRY_PALETTE = [
        ['key' => 'indigo', 'hex' => '#4f46e5'],
        ['key' => 'amber', 'hex' => '#d97706'],
        ['key' => 'emerald', 'hex' => '#059669'],
        ['key' => 'rose', 'hex' => '#e11d48'],
        ['key' => 'sky', 'hex' => '#0284c7'],
        ['key' => 'violet', 'hex' => '#7c3aed'],
        ['key' => 'teal', 'hex' => '#0d9488'],
        ['key' => 'orange', 'hex' => '#ea580c'],
    ];

    /**
     * @return array<string, mixed>
     */
    public function buildPayload(string $dateBasis = 'reservation'): array
    {
        // reservation = booking date (calendar/blocked/book_date), booking = created_at (sales timing)
        $dateBasis = in_array($dateBasis, ['reservation', 'booking'], true) ? $dateBasis : 'reservation';

        $bookings = Booking::query()
            ->where('status', 'accepted')
            ->with(['guiding', 'calendar_schedule', 'blocked_event'])
            ->get();

        $monthBuckets = [];
        $countryBuckets = [];
        $regionDemand = [];
        $guidingBookings = [];
        $unknownCountryLabel = __('admin.finance_analytics.unknown_country');

        foreach ($bookings as $booking) {
            $date = $dateBasis === 'booking' ? $booking->created_at : $booking->getBookingDate();
            if (!$date instanceof Carbon) {
                continue;
            }
            $ym = $date->format('Y-m');
            $price = (float) ($booking->price ?? 0);
            $platform = (float) ($booking->cag_percent ?? 0);
            $guide = max(0, $price - $platform);

            if (!isset($monthBuckets[$ym])) {
                $monthBuckets[$ym] = ['bookings' => 0, 'gmv' => 0.0, 'platform' => 0.0, 'guide' => 0.0];
            }
            $monthBuckets[$ym]['bookings']++;
            $monthBuckets[$ym]['gmv'] += $price;
            $monthBuckets[$ym]['platform'] += $platform;
            $monthBuckets[$ym]['guide'] += $guide;

            $guiding = $booking->guiding;
            $countryKey = $this->countryKey($guiding?->country, $unknownCountryLabel);
            $countryLabel = $this->displayCountry($guiding?->country, $unknownCountryLabel);

            if (!isset($countryBuckets[$countryKey])) {
                $countryBuckets[$countryKey] = [
                    'label' => $countryLabel,
                    'bookings' => 0,
                    'gmv' => 0.0,
                    'platform' => 0.0,
                    'guide' => 0.0,
                    'by_year' => [],
                ];
            }
            $countryBuckets[$countryKey]['bookings']++;
            $countryBuckets[$countryKey]['gmv'] += $price;
            $countryBuckets[$countryKey]['platform'] += $platform;
            $countryBuckets[$countryKey]['guide'] += $guide;
            $y = (string) $date->year;
            if (!isset($countryBuckets[$countryKey]['by_year'][$y])) {
                $countryBuckets[$countryKey]['by_year'][$y] = ['bookings' => 0, 'gmv' => 0.0, 'platform' => 0.0, 'guide' => 0.0];
            }
            $countryBuckets[$countryKey]['by_year'][$y]['bookings']++;
            $countryBuckets[$countryKey]['by_year'][$y]['gmv'] += $price;
            $countryBuckets[$countryKey]['by_year'][$y]['platform'] += $platform;
            $countryBuckets[$countryKey]['by_year'][$y]['guide'] += $guide;

            $region = trim((string) ($guiding?->region ?? ''));
            if ($region !== '') {
                $rk = $countryKey.'|'.$region;
                if (!isset($regionDemand[$rk])) {
                    $regionDemand[$rk] = [
                        'country_key' => $countryKey,
                        'country_label' => $countryLabel,
                        'region' => $region,
                        'bookings' => 0,
                        'gmv' => 0.0,
                    ];
                }
                $regionDemand[$rk]['bookings']++;
                $regionDemand[$rk]['gmv'] += $price;
            }

            $gid = $guiding?->id;
            if ($gid) {
                if (!isset($guidingBookings[$gid])) {
                    $guidingBookings[$gid] = [
                        'guiding_id' => $gid,
                        'title' => (string) ($guiding->title ?? ''),
                        'country_key' => $countryKey,
                        'bookings' => 0,
                        'gmv' => 0.0,
                    ];
                }
                $guidingBookings[$gid]['bookings']++;
                $guidingBookings[$gid]['gmv'] += $price;
            }
        }

        ksort($monthBuckets);
        $months = [];
        foreach ($monthBuckets as $ym => $row) {
            $d = Carbon::createFromFormat('Y-m', $ym)->startOfMonth();
            $months[] = [
                'year' => (int) $d->format('Y'),
                'month' => (int) $d->format('n'),
                'ym' => $ym,
                'label' => $d->translatedFormat('M y'),
                'bookings' => $row['bookings'],
                'gmv' => round($row['gmv'], 2),
                'platform' => round($row['platform'], 2),
                'guide' => round($row['guide'], 2),
            ];
        }

        $years = [];
        foreach ($months as $m) {
            $years[$m['year']] = true;
        }
        $yearList = array_keys($years);
        sort($yearList, SORT_NUMERIC);

        $totalsAll = [
            'bookings' => array_sum(array_column($months, 'bookings')),
            'gmv' => round(array_sum(array_column($months, 'gmv')), 2),
            'platform' => round(array_sum(array_column($months, 'platform')), 2),
            'guide' => round(array_sum(array_column($months, 'guide')), 2),
        ];

        $yearStats = ['all' => ['bookings' => $totalsAll['bookings'], 'gmv' => $totalsAll['gmv']]];
        foreach ($yearList as $y) {
            $yearStats[(string) $y] = ['bookings' => 0, 'gmv' => 0.0];
        }
        foreach ($months as $m) {
            $ys = &$yearStats[(string) $m['year']];
            $ys['bookings'] += $m['bookings'];
            $ys['gmv'] += $m['gmv'];
        }
        foreach ($yearList as $y) {
            $yearStats[(string) $y]['gmv'] = round($yearStats[(string) $y]['gmv'], 2);
        }

        $supplyByCountry = $this->buildSupplyByCountry($unknownCountryLabel);
        $guidingIdsWithBookings = Booking::query()
            ->where('status', 'accepted')
            ->whereNotNull('guiding_id')
            ->distinct()
            ->pluck('guiding_id')
            ->all();
        $withBookingsSet = array_fill_keys($guidingIdsWithBookings, true);

        $countries = [];
        $paletteIndex = 0;
        $allCountryKeys = array_unique(array_merge(array_keys($supplyByCountry), array_keys($countryBuckets)));
        sort($allCountryKeys);
        foreach ($allCountryKeys as $ck) {
            $supply = $supplyByCountry[$ck] ?? [
                'label' => $ck === '__unknown__' ? $unknownCountryLabel : $ck,
                'total' => 0,
                'active' => 0,
                'inactive' => 0,
                'supply_regions' => [],
            ];
            $rev = $countryBuckets[$ck] ?? [
                'label' => $supply['label'],
                'bookings' => 0,
                'gmv' => 0.0,
                'platform' => 0.0,
                'guide' => 0.0,
                'by_year' => [],
            ];
            $color = self::COUNTRY_PALETTE[$paletteIndex % count(self::COUNTRY_PALETTE)];
            $paletteIndex++;

            $toursWithBookings = 0;
            foreach ($supply['guiding_ids'] ?? [] as $gid) {
                if (isset($withBookingsSet[$gid])) {
                    $toursWithBookings++;
                }
            }

            $topTours = collect($guidingBookings)
                ->filter(fn ($t) => $t['country_key'] === $ck)
                ->sortByDesc('bookings')
                ->take(3)
                ->values()
                ->map(function ($t) {
                    $avg = $t['bookings'] > 0 ? round($t['gmv'] / $t['bookings'], 2) : 0;

                    return [
                        'title' => $t['title'] ?: '—',
                        'bookings' => $t['bookings'],
                        'revenue' => round($t['gmv'], 2),
                        'avg_price' => $avg,
                    ];
                })
                ->all();

            $demandRegions = collect($regionDemand)
                ->filter(fn ($r) => $r['country_key'] === $ck)
                ->sortByDesc('bookings')
                ->take(8)
                ->values()
                ->map(function ($r) {
                    $avg = $r['bookings'] > 0 ? round($r['gmv'] / $r['bookings'], 2) : 0;

                    return [
                        'region' => $r['region'],
                        'bookings' => $r['bookings'],
                        'revenue' => round($r['gmv'], 2),
                        'avg_price' => $avg,
                    ];
                })
                ->all();

            $countries[] = [
                'id' => $ck,
                'label' => $rev['label'],
                'color' => $color['hex'],
                'total_tours' => $supply['total'],
                'active_tours' => $supply['active'],
                'inactive_tours' => $supply['inactive'],
                'tours_with_bookings' => $toursWithBookings,
                'tours_without_bookings' => max(0, $supply['total'] - $toursWithBookings),
                'bookings' => $rev['bookings'],
                'gmv' => round($rev['gmv'], 2),
                'platform' => round($rev['platform'], 2),
                'guide' => round($rev['guide'], 2),
                'by_year' => $rev['by_year'],
                'regions' => $demandRegions,
                'supply_regions' => $supply['supply_regions'],
                'top_tours' => $topTours,
            ];
        }

        usort($countries, fn ($a, $b) => $b['gmv'] <=> $a['gmv']);

        foreach ($countries as &$cRow) {
            foreach ($cRow['by_year'] as $yk => &$yRow) {
                $yRow['gmv'] = round((float) $yRow['gmv'], 2);
                $yRow['platform'] = round((float) $yRow['platform'], 2);
                $yRow['guide'] = round((float) $yRow['guide'], 2);
            }
            unset($yRow);
        }
        unset($cRow);

        $mismatch = $this->buildMismatchRegions($regionDemand, $supplyByCountry);
        $priorityRegions = $this->buildPriorityRegions($regionDemand);

        $statusRows = Booking::query()
            ->selectRaw('status, COUNT(*) as c')
            ->groupBy('status')
            ->pluck('c', 'status')
            ->all();

        $totalStatusBookings = array_sum($statusRows);
        $conversionMeta = $this->buildConversionMeta($bookings, $statusRows, $totalStatusBookings);

        $supplyHistory = $this->buildSupplyHistorySeries();

        return [
            'date_basis' => $dateBasis,
            'generated_at' => now()->toIso8601String(),
            'currency' => '€',
            'months' => $months,
            'years' => $yearList,
            'year_stats' => $yearStats,
            'totals_all' => $totalsAll,
            'countries' => $countries,
            'mismatch_regions' => $mismatch,
            'priority_regions' => $priorityRegions,
            'laender_next_step_summary' => $this->buildLaenderNextStepSummary($priorityRegions),
            'booking_status' => $statusRows,
            'booking_status_total' => $totalStatusBookings,
            'conversion' => $conversionMeta,
            'supply_kpis' => $this->buildSupplyKpisSnapshot($supplyHistory),
            'supply_history' => $supplyHistory,
            'traffic_placeholder' => true,
        ];
    }

    private function countryKey(?string $country, string $unknownLabel): string
    {
        $c = trim((string) $country);
        if ($c === '') {
            return '__unknown__';
        }

        $lower = mb_strtolower($c);
        $canonical = match (true) {
            str_contains($lower, 'germany') || str_contains($lower, 'deutschland') || $lower === 'de' => 'germany',
            str_contains($lower, 'netherlands') || str_contains($lower, 'niederlande') || str_contains($lower, 'holland') || $lower === 'nl' => 'netherlands',
            str_contains($lower, 'sweden') || str_contains($lower, 'schweden') || $lower === 'se' => 'sweden',
            str_contains($lower, 'spain') || str_contains($lower, 'spanien') || str_contains($lower, 'españa') || $lower === 'es' => 'spain',
            str_contains($lower, 'portugal') || $lower === 'pt' => 'portugal',
            str_contains($lower, 'croatia') || str_contains($lower, 'kroatien') || $lower === 'hr' => 'croatia',
            str_contains($lower, 'norway') || str_contains($lower, 'norwegen') || $lower === 'no' => 'norway',
            default => 'c_'.md5($lower),
        };

        return $canonical;
    }

    private function displayCountry(?string $country, string $unknownLabel): string
    {
        $c = trim((string) $country);

        return $c !== '' ? $c : $unknownLabel;
    }

    /**
     * @return array<string, array{label: string, total: int, active: int, inactive: int, supply_regions: array<int, array<string, mixed>>, guiding_ids: array<int, int>}>
     */
    private function buildSupplyByCountry(string $unknownCountryLabel): array
    {
        $rows = Guiding::query()
            ->get(['id', 'country', 'region', 'status']);

        $out = [];
        foreach ($rows as $g) {
            $ck = $this->countryKey($g->country, $unknownCountryLabel);
            $label = $this->displayCountry($g->country, $unknownCountryLabel);
            if (!isset($out[$ck])) {
                $out[$ck] = [
                    'label' => $label,
                    'total' => 0,
                    'active' => 0,
                    'inactive' => 0,
                    'supply_regions' => [],
                    'guiding_ids' => [],
                ];
            }
            $out[$ck]['total']++;
            $out[$ck]['guiding_ids'][] = (int) $g->id;
            if ((int) $g->status === 1) {
                $out[$ck]['active']++;
            } else {
                $out[$ck]['inactive']++;
            }

            $reg = trim((string) ($g->region ?? ''));
            if ($reg === '') {
                continue;
            }
            if (!isset($out[$ck]['supply_regions'][$reg])) {
                $out[$ck]['supply_regions'][$reg] = ['region' => $reg, 'tours' => 0, 'active' => 0, 'inactive' => 0];
            }
            $out[$ck]['supply_regions'][$reg]['tours']++;
            if ((int) $g->status === 1) {
                $out[$ck]['supply_regions'][$reg]['active']++;
            } else {
                $out[$ck]['supply_regions'][$reg]['inactive']++;
            }
        }

        foreach ($out as &$block) {
            $regions = array_values($block['supply_regions']);
            usort($regions, fn ($a, $b) => $b['tours'] <=> $a['tours']);
            $block['supply_regions'] = array_slice($regions, 0, 12);
        }
        unset($block);

        return $out;
    }

    /**
     * @param  array<string, array<string, mixed>>  $regionDemand
     * @param  array<string, array<string, mixed>>  $supplyByCountry
     * @return array<int, array<string, mixed>>
     */
    private function buildMismatchRegions(array $regionDemand, array $supplyByCountry): array
    {
        $list = [];
        foreach ($regionDemand as $row) {
            $region = $row['region'];
            $supply = (int) Guiding::query()->where('region', $region)->count();
            $demand = (int) $row['bookings'];
            $list[] = [
                'region' => $row['country_label'].': '.$region,
                'supply' => $supply,
                'demand' => $demand,
                'gap' => $demand > 0 && $supply > 0 ? round($demand / $supply, 2) : null,
            ];
        }
        usort($list, fn ($a, $b) => $b['demand'] <=> $a['demand']);

        return array_slice($list, 0, 12);
    }

    /**
     * Regions where accepted bookings (demand) outweigh published supply — for recruitment & marketing focus.
     *
     * @param  array<string, array<string, mixed>>  $regionDemand
     * @return array<int, array<string, mixed>>
     */
    private function buildPriorityRegions(array $regionDemand): array
    {
        $minDemand = 3;
        $list = [];
        foreach ($regionDemand as $row) {
            $demand = (int) $row['bookings'];
            if ($demand < $minDemand) {
                continue;
            }
            $region = $row['region'];
            $supplyTotal = (int) Guiding::query()->where('region', $region)->count();
            $supplyActive = (int) Guiding::query()->where('region', $region)->where('status', 1)->count();
            $eff = max(1, $supplyActive);
            $pressure = round($demand / $eff, 2);
            $tier = match (true) {
                $pressure >= 6.0 || ($supplyActive <= 1 && $demand >= 8) => 'high',
                $pressure >= 3.0 => 'medium',
                default => 'watch',
            };
            $tierLabel = match ($tier) {
                'high' => (string) __('admin.finance_analytics.priority_tier_high'),
                'medium' => (string) __('admin.finance_analytics.priority_tier_medium'),
                default => (string) __('admin.finance_analytics.priority_tier_watch'),
            };
            $list[] = [
                'country_key' => $row['country_key'],
                'country_label' => $row['country_label'],
                'region' => $region,
                'display' => $row['country_label'].': '.$region,
                'demand' => $demand,
                'supply_total' => $supplyTotal,
                'supply_active' => $supplyActive,
                'pressure' => $pressure,
                'tier' => $tier,
                'tier_label' => $tierLabel,
            ];
        }
        usort($list, function (array $a, array $b) {
            if (($a['pressure'] <=> $b['pressure']) !== 0) {
                return $b['pressure'] <=> $a['pressure'];
            }

            return $b['demand'] <=> $a['demand'];
        });

        return array_values(array_slice($list, 0, 15));
    }

    /**
     * @param  array<int, array<string, mixed>>  $priorityRegions
     */
    private function buildLaenderNextStepSummary(array $priorityRegions): string
    {
        $top = array_slice($priorityRegions, 0, 3);
        if ($top === []) {
            return (string) __('admin.finance_analytics.priority_summary_empty');
        }
        $parts = [];
        foreach ($top as $row) {
            $parts[] = (string) __('admin.finance_analytics.priority_summary_item', [
                'place' => $row['display'],
                'bookings' => $row['demand'],
                'active' => $row['supply_active'],
                'pressure' => $row['pressure'],
            ]);
        }

        return (string) __('admin.finance_analytics.priority_summary_intro').implode(
            (string) __('admin.finance_analytics.priority_summary_join'),
            $parts
        ).(string) __('admin.finance_analytics.priority_summary_outro');
    }

    /**
     * @param  \Illuminate\Support\Collection<int, Booking>  $acceptedBookings
     * @param  array<string|int, int>  $statusRows
     * @return array<string, mixed>
     */
    private function buildConversionMeta($acceptedBookings, array $statusRows, int $totalStatusBookings): array
    {
        $accepted = (int) ($statusRows['accepted'] ?? 0);
        $rejected = (int) ($statusRows['rejected'] ?? 0);
        $cancelled = (int) ($statusRows['cancelled'] ?? 0);
        $pending = (int) ($statusRows['pending'] ?? 0);
        $closed = max(1, $accepted + $rejected + $cancelled);

        $guestAccepted = $acceptedBookings->filter(fn ($b) => (bool) $b->is_guest)->count();
        $guestPct = $accepted > 0 ? round(100 * $guestAccepted / $accepted, 1) : 0;

        $acceptedIds = $acceptedBookings->pluck('id')->all();
        $reviewBookingIds = Review::query()
            ->whereIn('booking_id', $acceptedIds)
            ->whereNotNull('booking_id')
            ->pluck('booking_id')
            ->all();
        $reviewedSet = array_fill_keys($reviewBookingIds, true);
        $reviewedCount = $acceptedBookings->filter(fn ($b) => (int) $b->is_reviewed === 1 || isset($reviewedSet[$b->id]))->count();
        $reviewPct = $accepted > 0 ? round(100 * $reviewedCount / $accepted, 1) : 0;

        return [
            'acceptance_rate_closed' => round(100 * $accepted / $closed, 1),
            'reject_rate_closed' => round(100 * $rejected / $closed, 1),
            'cancel_rate_closed' => round(100 * $cancelled / $closed, 1),
            'pending_open' => $pending,
            'guest_share_accepted_pct' => $guestPct,
            'review_rate_accepted_pct' => $reviewPct,
            'totals' => [
                'accepted' => $accepted,
                'rejected' => $rejected,
                'cancelled' => $cancelled,
                'pending' => $pending,
                'all' => $totalStatusBookings,
            ],
        ];
    }

    /**
     * @return array<string, array<int, int|float>>
     */
    private function buildSupplyHistorySeries(): array
    {
        $activeGuidings = [];
        $activeGuides = [];
        $avgToursPerGuide = [];
        $activationPct = [];

        for ($i = 11; $i >= 0; $i--) {
            $end = now()->subMonths($i)->endOfMonth();
            $endStr = $end->toDateTimeString();

            $activeGuidings[] = (int) Guiding::query()
                ->where('status', 1)
                ->where('created_at', '<=', $endStr)
                ->count();

            $activeGuides[] = (int) Guiding::query()
                ->where('status', 1)
                ->where('created_at', '<=', $endStr)
                ->selectRaw('COUNT(DISTINCT user_id) as c')
                ->value('c');

            $avgRow = DB::query()
                ->fromSub(
                    Guiding::query()
                        ->selectRaw('user_id, COUNT(*) as cnt')
                        ->where('status', 1)
                        ->where('created_at', '<=', $endStr)
                        ->groupBy('user_id'),
                    'guides_per_user'
                )
                ->selectRaw('AVG(cnt) as avg_tours')
                ->value('avg_tours');
            $avgToursPerGuide[] = $avgRow !== null ? round((float) $avgRow, 2) : 0;

            $totalGuides = (int) DB::table('guidings')
                ->where('created_at', '<=', $endStr)
                ->selectRaw('COUNT(DISTINCT user_id) as c')
                ->value('c');
            $actGuides = (int) Guiding::query()
                ->where('status', 1)
                ->where('created_at', '<=', $endStr)
                ->selectRaw('COUNT(DISTINCT user_id) as c')
                ->value('c');
            $activationPct[] = $totalGuides > 0 ? (int) round(100 * $actGuides / $totalGuides) : 0;
        }

        return [
            'active_guidings' => $activeGuidings,
            'active_guides' => $activeGuides,
            'avg_tours_per_guide' => $avgToursPerGuide,
            'guide_activation_pct' => $activationPct,
        ];
    }

    /**
     * @param  array<string, array<int, int|float>>  $history
     * @return array<string, mixed>
     */
    private function buildSupplyKpisSnapshot(array $history): array
    {
        $activeGuidings = (int) Guiding::query()->where('status', 1)->count();
        $activeGuides = (int) Guiding::query()
            ->where('status', 1)
            ->selectRaw('COUNT(DISTINCT user_id) as c')
            ->value('c');
        $totalGuides = (int) DB::table('guidings')->selectRaw('COUNT(DISTINCT user_id) as c')->value('c');
        $avgTours = $activeGuides > 0 ? round($activeGuidings / $activeGuides, 2) : 0;
        $activation = $totalGuides > 0 ? round(100 * $activeGuides / $totalGuides, 1) : 0;

        $last = fn (array $arr) => (float) (count($arr) ? end($arr) : 0);
        $prev = fn (array $arr) => (float) (count($arr) > 1 ? $arr[count($arr) - 2] : 0);
        $trendPct = function (array $arr) use ($last, $prev) {
            $a = $last($arr);
            $b = $prev($arr);
            if ($b == 0) {
                return $a > 0 ? 100.0 : 0.0;
            }

            return round(100 * ($a - $b) / $b, 1);
        };

        return [
            'active_guidings' => [
                'current' => $activeGuidings,
                'target' => max($activeGuidings + 50, 500),
                'history' => $history['active_guidings'],
                'trend_pct' => $trendPct($history['active_guidings']),
            ],
            'avg_tours_per_guide' => [
                'current' => $avgTours,
                'target' => max(5, round($avgTours + 1, 1)),
                'history' => $history['avg_tours_per_guide'],
                'trend_pct' => $trendPct($history['avg_tours_per_guide']),
            ],
            'active_guides' => [
                'current' => $activeGuides,
                'target' => max($activeGuides + 30, 180),
                'history' => $history['active_guides'],
                'trend_pct' => $trendPct($history['active_guides']),
            ],
            'guide_activation_pct' => [
                'current' => $activation,
                'target' => min(95, max(80, $activation + 15)),
                'history' => $history['guide_activation_pct'],
                'trend_pct' => $trendPct($history['guide_activation_pct']),
            ],
        ];
    }
}
