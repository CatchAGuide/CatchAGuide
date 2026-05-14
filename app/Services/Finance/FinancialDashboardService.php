<?php

namespace App\Services\Finance;

use App\Models\Booking;
use App\Models\Guiding;
use App\Models\Target;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;

/**
 * Financial Tracking Dashboard — per requirements doc (May 2026):
 * - Gross / net GMV, cancellation, avg value, # bookings, trend, breakdowns, table: created_at in the month.
 * - Revenue & take rate: accepted bookings with book_date in the month (NULL book_date excluded from revenue).
 * - Take rate denominator = sum of gross (accepted) for that tour-month cohort (GMV of settled tours).
 * - cag_percent is stored as EUR commission in this codebase; capped vs gross via config (doc anomaly handling).
 */
class FinancialDashboardService
{
    public function __construct(
        private readonly CountryNormalizer $countries,
    ) {}

    /**
     * @return array<string, mixed>
     */
    public function buildPayload(Request $request): array
    {
        $filters = $this->parseFilters($request);
        $allTime = $this->isAllTimePeriod($request);

        if ($allTime) {
            $bookingMonthPool = $this->queryBookingsCreatedInMonth(null, null);
            $filteredBookingMonth = $this->applyDimensionFilters($bookingMonthPool, $filters);

            $tourMonthRevenuePool = $this->queryAcceptedBookDateInMonth(null, null);
            $filteredTourMonthRevenue = $this->applyDimensionFilters($tourMonthRevenuePool, $filters);

            $kpis = $this->computeKpis($filteredBookingMonth, $filteredTourMonthRevenue);
            $kpis['prev_month'] = null;

            $trendMonths = (int) $request->query('trend_months', 36);
            $trendMonths = max(12, min(60, $trendMonths));
            $trend = $this->buildTrend(now()->copy()->startOfMonth(), $trendMonths, $filters);

            $year = null;
            $month = null;
        } else {
            [$year, $month] = $this->validateYearMonth($request);

            $monthStart = Carbon::create($year, $month, 1)->startOfDay();
            $monthEnd = $monthStart->copy()->endOfMonth()->endOfDay();

            $bookingMonthPool = $this->queryBookingsCreatedInMonth($monthStart, $monthEnd);
            $filteredBookingMonth = $this->applyDimensionFilters($bookingMonthPool, $filters);

            $tourMonthRevenuePool = $this->queryAcceptedBookDateInMonth($monthStart, $monthEnd);
            $filteredTourMonthRevenue = $this->applyDimensionFilters($tourMonthRevenuePool, $filters);

            $kpis = $this->computeKpis($filteredBookingMonth, $filteredTourMonthRevenue);

            $prevStart = $monthStart->copy()->subMonth()->startOfMonth();
            $prevEnd = $monthStart->copy()->subMonth()->endOfMonth()->endOfDay();
            $kpis['prev_month'] = $this->computeKpis(
                $this->applyDimensionFilters($this->queryBookingsCreatedInMonth($prevStart, $prevEnd), $filters),
                $this->applyDimensionFilters($this->queryAcceptedBookDateInMonth($prevStart, $prevEnd), $filters)
            );

            $trendMonths = (int) $request->query('trend_months', 12);
            $trendMonths = max(6, min(18, $trendMonths));
            $trend = $this->buildTrend($monthStart, $trendMonths, $filters);
        }

        $breakdownSource = $filteredBookingMonth->filter(fn (Booking $b) => $this->inGrossGmvStatuses($b->status));

        $breakdowns = [
            'by_country' => $this->breakdownByCountry($breakdownSource),
            'by_fish' => $this->breakdownByFish($breakdownSource),
            'by_price_tier' => $this->breakdownByPriceTier($breakdownSource),
            'by_product_type' => $this->breakdownByProductType($breakdownSource),
            'by_guide' => $this->breakdownByGuide($breakdownSource),
        ];

        $page = max(1, (int) $request->query('page', 1));
        $perPage = min(100, max(10, (int) $request->query('per_page', 25)));
        $sort = strtolower((string) $request->query('sort', 'created_at'));
        $dir = strtolower((string) $request->query('direction', 'desc')) === 'asc' ? 'asc' : 'desc';

        $tableRows = $this->sortBookingsForTable(
            $filteredBookingMonth->filter(fn (Booking $b) => $this->inGrossGmvStatuses($b->status))->values(),
            $sort,
            $dir
        );

        $paginator = new LengthAwarePaginator(
            $tableRows->forPage($page, $perPage)->values()->all(),
            $tableRows->count(),
            $perPage,
            $page,
            ['path' => $request->url(), 'query' => $request->query()]
        );

        $targetsPage = $this->targetsMapForBookings(collect($paginator->items()));

        return [
            'kpis' => $kpis,
            'trend' => $trend,
            'breakdowns' => $breakdowns,
            'bookings' => [
                'data' => array_map(
                    fn (Booking $b) => $this->serializeBookingRow($b, $targetsPage),
                    $paginator->items()
                ),
                'current_page' => $paginator->currentPage(),
                'per_page' => $paginator->perPage(),
                'total' => $paginator->total(),
                'last_page' => $paginator->lastPage(),
            ],
            'filters_available' => $this->buildFiltersAvailable(),
            'meta' => [
                'period' => $allTime ? 'all' : 'month',
                'year' => $year,
                'month' => $month,
                'trend_months' => $trendMonths,
                'gmv_basis' => 'created_at',
                'revenue_basis' => 'book_date',
                'filters_applied' => array_filter($filters, fn ($v) => $v !== null && $v !== ''),
            ],
        ];
    }

    /**
     * @return Collection<int, Booking>
     */
    public function collectBookingsForExport(Request $request): Collection
    {
        $filters = $this->parseFilters($request);

        if ($this->isAllTimePeriod($request)) {
            $bookingMonthPool = $this->queryBookingsCreatedInMonth(null, null);
        } else {
            [$year, $month] = $this->validateYearMonth($request);
            $monthStart = Carbon::create($year, $month, 1)->startOfDay();
            $monthEnd = $monthStart->copy()->endOfMonth()->endOfDay();
            $bookingMonthPool = $this->queryBookingsCreatedInMonth($monthStart, $monthEnd);
        }

        $filtered = $this->applyDimensionFilters($bookingMonthPool, $filters);

        return $this->sortBookingsForTable(
            $filtered->filter(fn (Booking $b) => $this->inGrossGmvStatuses($b->status))->values(),
            (string) $request->query('sort', 'created_at'),
            strtolower((string) $request->query('direction', 'desc')) === 'asc' ? 'asc' : 'desc'
        );
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    public function buildExportRows(Request $request): array
    {
        $rows = $this->collectBookingsForExport($request);
        $map = $this->targetsMapForBookings($rows);

        return $rows->map(fn (Booking $b) => $this->serializeBookingRow($b, $map))->values()->all();
    }

    /**
     * @return array<string, string|null|int>
     */
    private function parseFilters(Request $request): array
    {
        return [
            'country' => $request->query('country') !== null && $request->query('country') !== ''
                ? (string) $request->query('country')
                : null,
            'product_type' => in_array((string) $request->query('product_type'), ['tour', 'vacation'], true)
                ? (string) $request->query('product_type')
                : null,
            'target_fish_id' => ctype_digit((string) $request->query('target_fish_id'))
                ? (int) $request->query('target_fish_id')
                : null,
            'price_tier' => in_array((string) $request->query('price_tier'), ['budget', 'standard', 'premium'], true)
                ? (string) $request->query('price_tier')
                : null,
            'lead_time' => in_array((string) $request->query('lead_time'), ['short', 'mid', 'long'], true)
                ? (string) $request->query('lead_time')
                : null,
            'guide_id' => ctype_digit((string) $request->query('guide_id'))
                ? (int) $request->query('guide_id')
                : null,
        ];
    }

    /**
     * Bookings placed in the month (doc: GMV / booking counts use created_at).
     * When $monthStart/$monthEnd are null, returns all guiding bookings (all-time mode).
     *
     * @return Collection<int, Booking>
     */
    private function queryBookingsCreatedInMonth(?Carbon $monthStart, ?Carbon $monthEnd): Collection
    {
        $q = Booking::query()
            ->with(['guiding.user', 'calendar_schedule', 'blocked_event'])
            ->whereNotNull('guiding_id');

        if ($monthStart !== null && $monthEnd !== null) {
            $q->whereBetween('created_at', [$monthStart, $monthEnd]);
        }

        return $q->orderBy('created_at')->get();
    }

    /**
     * Accepted bookings whose tour falls in the month by book_date (doc: revenue / take rate).
     * NULL book_date excluded per data-quality section.
     * When $monthStart/$monthEnd are null, returns all accepted with book_date (all-time mode).
     *
     * @return Collection<int, Booking>
     */
    private function queryAcceptedBookDateInMonth(?Carbon $monthStart, ?Carbon $monthEnd): Collection
    {
        $q = Booking::query()
            ->with(['guiding.user', 'calendar_schedule', 'blocked_event'])
            ->whereNotNull('guiding_id')
            ->where('status', 'accepted')
            ->whereNotNull('book_date');

        if ($monthStart !== null && $monthEnd !== null) {
            $from = $monthStart->copy()->startOfDay();
            $to = $monthEnd->copy()->endOfDay();
            $q->whereBetween('book_date', [$from->toDateString(), $to->toDateString()]);
        }

        return $q->orderBy('book_date')->get();
    }

    private function isAllTimePeriod(Request $request): bool
    {
        return strtolower(trim((string) $request->query('period', ''))) === 'all';
    }

    /**
     * @param  Collection<int, Booking>  $bookings
     * @param  array<string, mixed>  $filters
     * @return Collection<int, Booking>
     */
    private function applyDimensionFilters(Collection $bookings, array $filters): Collection
    {
        return $bookings->filter(function (Booking $b) use ($filters) {
            $g = $b->guiding;
            if ($filters['country']) {
                if (!$g || !$this->countries->matchesFilter($g->country, $filters['country'])) {
                    return false;
                }
            }
            if ($filters['product_type']) {
                $t = $g?->type ?: 'tour';
                if ($t !== $filters['product_type']) {
                    return false;
                }
            }
            if ($filters['target_fish_id'] !== null) {
                if (!$this->guidingHasFishId($g, $filters['target_fish_id'])) {
                    return false;
                }
            }
            if ($filters['price_tier']) {
                if ($this->priceTier($b) !== $filters['price_tier']) {
                    return false;
                }
            }
            if ($filters['lead_time']) {
                $lt = $this->leadTimeBucket($b);
                if ($lt === null || $lt !== $filters['lead_time']) {
                    return false;
                }
            }
            if ($filters['guide_id'] !== null) {
                if ((int) ($g?->user_id ?? 0) !== $filters['guide_id']) {
                    return false;
                }
            }

            return true;
        });
    }

    private function guidingHasFishId(?Guiding $guiding, int $fishId): bool
    {
        if (!$guiding) {
            return false;
        }
        $ids = decode_if_json($guiding->target_fish) ?? [];
        foreach ((array) $ids as $id) {
            if ((int) $id === $fishId) {
                return true;
            }
        }

        return false;
    }

    /**
     * @param  Collection<int, Booking>  $bookingMonthBookings  created_at in month, dimension-filtered
     * @param  Collection<int, Booking>  $tourMonthAcceptedBookings  accepted, book_date in month, dimension-filtered
     * @return array<string, mixed>
     */
    private function computeKpis(Collection $bookingMonthBookings, Collection $tourMonthAcceptedBookings): array
    {
        $grossSlice = $bookingMonthBookings->filter(fn (Booking $b) => $this->inGrossGmvStatuses($b->status));
        $netSlice = $bookingMonthBookings->filter(fn (Booking $b) => $b->status === 'accepted');

        $grossGmv = round($grossSlice->sum(fn (Booking $b) => $b->getGrossAmount()), 2);
        $netGmv = round($netSlice->sum(fn (Booking $b) => $b->getGrossAmount()), 2);
        $bookingCount = $netSlice->count();

        $avgBookingValue = $bookingCount > 0 ? round($netGmv / $bookingCount, 2) : 0.0;

        $cancellationRate = $grossGmv > 0
            ? round(100 * ($grossGmv - $netGmv) / $grossGmv, 1)
            : 0.0;

        $gmvSettled = round($tourMonthAcceptedBookings->sum(fn (Booking $b) => $b->getGrossAmount()), 2);
        $revenue = round($tourMonthAcceptedBookings->sum(fn (Booking $b) => $this->effectiveCommissionEur($b)), 2);

        $takeRate = $gmvSettled > 0 ? round(100 * $revenue / $gmvSettled, 1) : 0.0;

        return [
            'gross_gmv' => $grossGmv,
            'net_gmv' => $netGmv,
            'revenue' => $revenue,
            'take_rate' => $takeRate,
            'avg_booking_value' => $avgBookingValue,
            'booking_count' => $bookingCount,
            'cancellation_rate' => $cancellationRate,
        ];
    }

    /**
     * @param  array<string, mixed>  $filters
     * @return array<int, array<string, mixed>>
     */
    private function buildTrend(Carbon $selectedMonthStart, int $monthsBack, array $filters): array
    {
        $out = [];
        for ($i = $monthsBack - 1; $i >= 0; $i--) {
            $ms = $selectedMonthStart->copy()->subMonths($i)->startOfMonth();
            $me = $ms->copy()->endOfMonth()->endOfDay();
            $pool = $this->applyDimensionFilters($this->queryBookingsCreatedInMonth($ms, $me), $filters);

            $gross = round($pool->filter(fn (Booking $b) => $this->inGrossGmvStatuses($b->status))
                ->sum(fn (Booking $b) => $b->getGrossAmount()), 2);
            $net = round($pool->filter(fn (Booking $b) => $b->status === 'accepted')
                ->sum(fn (Booking $b) => $b->getGrossAmount()), 2);

            $out[] = [
                'year' => (int) $ms->format('Y'),
                'month' => (int) $ms->format('n'),
                'gross_gmv' => $gross,
                'net_gmv' => $net,
            ];
        }

        return $out;
    }

    /**
     * @param  Collection<int, Booking>  $rows  gross slice for GMV by booking month (created_at)
     * @return array<int, array{name: string, gmv: float}>
     */
    private function breakdownByCountry(Collection $rows): array
    {
        $map = [];
        foreach ($rows as $b) {
            $label = $this->countries->englishLabel($b->guiding?->country);
            if (!isset($map[$label])) {
                $map[$label] = 0.0;
            }
            $map[$label] += $b->getGrossAmount();
        }
        $list = [];
        foreach ($map as $name => $gmv) {
            $list[] = ['name' => $name, 'gmv' => round($gmv, 2)];
        }
        usort($list, fn ($a, $b) => $b['gmv'] <=> $a['gmv']);

        return $list;
    }

    /**
     * @param  Collection<int, Booking>  $rows
     * @return array<int, array{name: string, gmv: float}>
     */
    private function breakdownByFish(Collection $rows): array
    {
        $targets = Target::query()->get(['id', 'name', 'name_en'])->keyBy('id');
        $map = [];

        foreach ($rows as $b) {
            $ids = array_unique(array_map('intval', (array) (decode_if_json($b->guiding?->target_fish) ?? [])));
            if ($ids === []) {
                $label = __('admin.financial_dashboard.unspecified_fish');
                if (!isset($map[$label])) {
                    $map[$label] = 0.0;
                }
                $map[$label] += $b->getGrossAmount();

                continue;
            }
            $amount = $b->getGrossAmount() / max(1, count($ids));
            foreach ($ids as $fid) {
                $t = $targets->get($fid);
                $name = $t ? (string) ($t->name_en ?: $t->name) : '#'.$fid;
                if (!isset($map[$name])) {
                    $map[$name] = 0.0;
                }
                // Split GMV across species listed on guiding (equal split when multiple).
                $map[$name] += $amount;
            }
        }

        $list = [];
        foreach ($map as $name => $gmv) {
            $list[] = ['name' => $name, 'gmv' => round($gmv, 2)];
        }
        usort($list, fn ($a, $b) => $b['gmv'] <=> $a['gmv']);

        return $list;
    }

    /**
     * @param  Collection<int, Booking>  $rows
     * @return array<int, array{tier: string, count: int, gmv: float}>
     */
    private function breakdownByPriceTier(Collection $rows): array
    {
        $bucket = ['budget' => ['count' => 0, 'gmv' => 0.0], 'standard' => ['count' => 0, 'gmv' => 0.0], 'premium' => ['count' => 0, 'gmv' => 0.0]];
        foreach ($rows as $b) {
            $t = $this->priceTier($b);
            $bucket[$t]['count']++;
            $bucket[$t]['gmv'] += $b->getGrossAmount();
        }

        $out = [];
        foreach (['budget', 'standard', 'premium'] as $tier) {
            $out[] = [
                'tier' => $tier,
                'count' => $bucket[$tier]['count'],
                'gmv' => round($bucket[$tier]['gmv'], 2),
            ];
        }

        return $out;
    }

    /**
     * @param  Collection<int, Booking>  $rows
     * @return array<int, array{type: string, label: string, gmv: float}>
     */
    private function breakdownByProductType(Collection $rows): array
    {
        $map = ['tour' => 0.0, 'vacation' => 0.0];
        foreach ($rows as $b) {
            $t = ($b->guiding?->type ?: 'tour') === 'vacation' ? 'vacation' : 'tour';
            $map[$t] += $b->getGrossAmount();
        }

        return [
            ['type' => 'tour', 'label' => 'Guidings', 'gmv' => round($map['tour'], 2)],
            ['type' => 'vacation', 'label' => 'Vacations', 'gmv' => round($map['vacation'], 2)],
        ];
    }

    /**
     * @param  Collection<int, Booking>  $rows
     * @return array<int, array{id: int|null, name: string, gmv: float, bookings: int}>
     */
    private function breakdownByGuide(Collection $rows): array
    {
        $map = [];
        foreach ($rows as $b) {
            $u = $b->guiding?->user;
            $id = $u?->id !== null ? (int) $u->id : null;
            $key = $id ?? -((int) $b->guiding_id);
            if (!isset($map[$key])) {
                $map[$key] = ['id' => $id, 'name' => $this->guideName($u), 'gmv' => 0.0, 'bookings' => 0];
            }
            $map[$key]['gmv'] += $b->getGrossAmount();
            $map[$key]['bookings']++;
        }

        $list = array_values($map);
        foreach ($list as &$row) {
            $row['gmv'] = round($row['gmv'], 2);
        }
        unset($row);

        usort($list, fn ($a, $b) => $b['gmv'] <=> $a['gmv']);

        return $list;
    }

    /**
     * @return array<string, mixed>
     */
    private function buildFiltersAvailable(): array
    {
        $countryLabels = Guiding::query()
            ->whereNotNull('country')
            ->distinct()
            ->pluck('country')
            ->map(fn ($c) => $this->countries->englishLabel($c))
            ->unique()
            ->sort()
            ->values()
            ->all();

        $fishSpecies = Target::query()
            ->orderByRaw('COALESCE(name_en, name) ASC')
            ->get(['id', 'name', 'name_en'])
            ->map(fn ($t) => [
                'id' => (int) $t->id,
                'name' => (string) ($t->name_en ?: $t->name),
            ])
            ->values()
            ->all();

        $guideIds = Guiding::query()->distinct()->whereNotNull('user_id')->pluck('user_id');
        $guides = User::query()
            ->whereIn('id', $guideIds)
            ->orderBy('firstname')
            ->orderBy('lastname')
            ->get(['id', 'firstname', 'lastname'])
            ->map(function (User $u) {
                $name = trim(($u->firstname ?? '').' '.($u->lastname ?? ''));

                return ['id' => (int) $u->id, 'name' => $name !== '' ? $name : 'User #'.$u->id];
            })
            ->values()
            ->all();

        return [
            'countries' => $countryLabels,
            'fish_species' => $fishSpecies,
            'guides' => $guides,
        ];
    }

    /**
     * @return Collection<int, Booking>
     */
    private function sortBookingsForTable(Collection $rows, string $sort, string $dir): Collection
    {
        $mult = $dir === 'asc' ? 1 : -1;

        return $rows->sort(function (Booking $a, Booking $b) use ($sort, $mult) {
            return match ($sort) {
                'id' => $mult * (($a->id ?? 0) <=> ($b->id ?? 0)),
                'book_date' => $mult * (($a->getBookingDate()?->timestamp ?? 0) <=> ($b->getBookingDate()?->timestamp ?? 0)),
                'price', 'gmv' => $mult * ($a->getGrossAmount() <=> $b->getGrossAmount()),
                'commission' => $mult * ($this->effectiveCommissionEur($a) <=> $this->effectiveCommissionEur($b)),
                'guide' => $mult * strcmp(
                    $this->guideName($a->guiding?->user) ?: '',
                    $this->guideName($b->guiding?->user) ?: ''
                ),
                default => $mult * ($a->created_at?->timestamp <=> $b->created_at?->timestamp),
            };
        })->values();
    }

    /**
     * @param  Collection<int, Target>  $targetsMap
     * @return array<string, mixed>
     */
    private function serializeBookingRow(Booking $b, Collection $targetsMap): array
    {
        $g = $b->guiding;
        $fishIds = decode_if_json($g?->target_fish) ?? [];
        $fishNames = [];
        foreach ((array) $fishIds as $fid) {
            $t = $targetsMap->get((int) $fid);
            $fishNames[] = $t ? (string) ($t->name_en ?: $t->name) : '#'.$fid;
        }

        return [
            'id' => $b->id,
            'booking_date' => optional($b->created_at)->toIso8601String(),
            'tour_date' => optional($b->getBookingDate())->toDateString(),
            'guide_name' => $this->guideName($g?->user),
            'country' => $this->countries->englishLabel($g?->country),
            'target_fish' => implode(', ', $fishNames),
            'product_type' => ($g?->type ?: 'tour') === 'vacation' ? 'vacation' : 'tour',
            'price' => round($b->getGrossAmount(), 2),
            'commission' => round($this->effectiveCommissionEur($b), 2),
            'price_tier' => $this->priceTier($b),
            'lead_time_days' => $this->leadTimeDays($b),
            'lead_time_bucket' => $this->leadTimeBucket($b),
            'status' => $b->status,
        ];
    }

    private function guideName(?User $user): string
    {
        if (!$user) {
            return '—';
        }
        $n = trim(($user->firstname ?? '').' '.($user->lastname ?? ''));

        return $n !== '' ? $n : '—';
    }

    private function effectiveCommissionEur(Booking $booking): float
    {
        $gross = $booking->getGrossAmount();
        if ($gross <= 0) {
            return 0.0;
        }
        $raw = (float) ($booking->cag_percent ?? 0);
        $capRatio = (float) config('finance.commission_max_ratio_of_gross', 0.5);
        $max = $gross * max(0.0, $capRatio);

        return min(max($raw, 0.0), $max);
    }

    /**
     * @return array{0: int, 1: int}
     */
    private function validateYearMonth(Request $request): array
    {
        $year = (int) $request->query('year');
        $month = (int) $request->query('month');
        if ($year < 2000 || $year > 2100 || $month < 1 || $month > 12) {
            abort(422, 'year and month (1–12) are required and must be valid.');
        }

        return [$year, $month];
    }

    /**
     * @param  Collection<int, Booking>  $bookings
     * @return Collection<int, Target>
     */
    private function targetsMapForBookings(Collection $bookings): Collection
    {
        $ids = [];
        foreach ($bookings as $b) {
            foreach ((array) (decode_if_json($b->guiding?->target_fish) ?? []) as $fid) {
                $ids[] = (int) $fid;
            }
        }
        $ids = array_values(array_unique(array_filter($ids)));

        return $ids === []
            ? collect()
            : Target::query()->whereIn('id', $ids)->get(['id', 'name', 'name_en'])->keyBy('id');
    }

    private function inGrossGmvStatuses(?string $status): bool
    {
        return $status !== 'rejected';
    }

    private function priceTier(Booking $b): string
    {
        $guests = max(1, (int) ($b->count_of_users ?? 1));
        $ppp = $b->getGrossAmount() / $guests;

        return match (true) {
            $ppp < 100 => 'budget',
            $ppp < 300 => 'standard',
            default => 'premium',
        };
    }

    private function leadTimeDays(Booking $b): ?int
    {
        if (!$b->book_date || !$b->created_at) {
            return null;
        }
        $start = Carbon::parse($b->created_at)->startOfDay();
        $end = Carbon::parse($b->book_date)->startOfDay();

        return max(0, (int) $start->diffInDays($end, false));
    }

    private function leadTimeBucket(Booking $b): ?string
    {
        $d = $this->leadTimeDays($b);
        if ($d === null) {
            return null;
        }
        if ($d <= 7) {
            return 'short';
        }
        if ($d <= 30) {
            return 'mid';
        }

        return 'long';
    }
}
