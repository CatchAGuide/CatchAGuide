<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Booking;
use App\Models\User;
use App\Models\Guiding;
use App\Models\UserGuest;
use App\Models\VacationBooking;
use App\Models\Vacation;
use App\Models\VacationPackage;
use Illuminate\Support\Facades\DB;


class AdminController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $now = now();

        // Exclude internal/test accounts from all booking-based statistics
        $testUserIds = [2, 3];
        $testUserEmails = User::whereIn('id', $testUserIds)
            ->pluck('email')
            ->filter()
            ->toArray();

        $filterTestBookings = function ($query) use ($testUserIds, $testUserEmails) {
            $query->whereNotIn('user_id', $testUserIds);

            if (!empty($testUserEmails)) {
                $query->whereNotIn('email', $testUserEmails);
            }

            return $query;
        };

        // Total Bookings including guest bookings
        $totalBookings = $filterTestBookings(Booking::query())->count();
        $guestBookings = $filterTestBookings(Booking::where('is_guest', true))->count();
        
        // Monthly statistics - bookings created this month (by status)
        $monthlyBookingsQuery = $filterTestBookings(
            Booking::whereYear('created_at', $now->year)
                ->whereMonth('created_at', $now->month)
        );

        $monthlyBookingsTotal = (clone $monthlyBookingsQuery)->count();
        $monthlyBookingsAccepted = (clone $monthlyBookingsQuery)->where('status', 'accepted')->count();
        $monthlyBookingsCancelled = (clone $monthlyBookingsQuery)->where('status', 'cancelled')->count();
        $monthlyBookingsPending = (clone $monthlyBookingsQuery)->where('status', 'pending')->count();

        // Last month statistics - bookings created last month (by status)
        $startOfThisMonth = $now->copy()->startOfMonth();
        $startOfLastMonth = $now->copy()->subMonthNoOverflow()->startOfMonth();

        $lastMonthBookingsQuery = $filterTestBookings(
            Booking::whereYear('created_at', $startOfLastMonth->year)
                ->whereMonth('created_at', $startOfLastMonth->month)
        );

        $lastMonthBookingsTotal = (clone $lastMonthBookingsQuery)->count();
        $lastMonthBookingsAccepted = (clone $lastMonthBookingsQuery)->where('status', 'accepted')->count();
        $lastMonthBookingsCancelled = (clone $lastMonthBookingsQuery)->where('status', 'cancelled')->count();
        $lastMonthBookingsPending = (clone $lastMonthBookingsQuery)->where('status', 'pending')->count();

        // This year statistics - bookings created this year (by status)
        $thisYearBookingsQuery = $filterTestBookings(
            Booking::whereYear('created_at', $now->year)
        );

        $thisYearBookingsTotal = (clone $thisYearBookingsQuery)->count();
        $thisYearBookingsAccepted = (clone $thisYearBookingsQuery)->where('status', 'accepted')->count();
        $thisYearBookingsCancelled = (clone $thisYearBookingsQuery)->where('status', 'cancelled')->count();
        $thisYearBookingsPending = (clone $thisYearBookingsQuery)->where('status', 'pending')->count();

        // Last year statistics - bookings created last year (by status)
        $lastYear = $now->copy()->subYear()->year;
        $lastYearBookingsQuery = $filterTestBookings(
            Booking::whereYear('created_at', $lastYear)
        );

        $lastYearBookingsTotal = (clone $lastYearBookingsQuery)->count();
        $lastYearBookingsAccepted = (clone $lastYearBookingsQuery)->where('status', 'accepted')->count();
        $lastYearBookingsCancelled = (clone $lastYearBookingsQuery)->where('status', 'cancelled')->count();
        $lastYearBookingsPending = (clone $lastYearBookingsQuery)->where('status', 'pending')->count();
        
        // Customer statistics
        $registeredUsers = User::where('is_guide', false)->orWhereNull('is_guide')->count();
        $guestUsers = UserGuest::count();
        $totalCustomers = $registeredUsers + $guestUsers;
        
        // Guide and Tour statistics
        $totalGuides = User::where('is_guide', true)->count();
        $activeTours = Guiding::where('status', 1)->count();
        $guidesWithActiveTours = User::where('is_guide', true)
            ->whereHas('guidings', function($query) {
                $query->where('status', 1);
            })
            ->count();

        // Guides without active or draft guidings (no guidings yet, or only deactivated)
        $guidesWithoutActiveOrDraftGuidings = User::where('is_guide', true)
            ->whereDoesntHave('guidings', function($query) {
                $query->whereIn('status', [1, 2]);
            })
            ->count();

        // Completed + approved bookings (accepted and tour date already passed)
        $completedApprovedBookings = $filterTestBookings(
                Booking::with(['user', 'guiding', 'blocked_event', 'calendar_schedule'])
                    ->where('status', 'accepted')
                    ->orderBy('id', 'desc')
            )
            ->get()
            ->filter(function ($booking) {
                return $booking->isBookingOver();
            });

        $completedApprovedCount = $completedApprovedBookings->count();
        $toBeBilledCount = $completedApprovedBookings->where('is_guide_billed', false)->count();

        // Monthly revenue based on completed + approved bookings whose booking date is in the current month
        $monthlyRevenueBookings = $completedApprovedBookings->filter(function ($booking) use ($now) {
            $date = $booking->getBookingDate();
            return $date && $date->year === $now->year && $date->month === $now->month;
        });

        $monthlyRevenue = $monthlyRevenueBookings->sum(function ($booking) {
            return $booking->price + ($booking->total_extra_price ?? 0);
        });

        $monthlyRevenueUnbilledCount = $monthlyRevenueBookings->where('is_guide_billed', false)->count();

        // Helper to get completed + approved bookings for a given date range (based on booking date)
        $completedBookingsForRange = function ($from, $to) use ($completedApprovedBookings) {
            return $completedApprovedBookings->filter(function ($booking) use ($from, $to) {
                $date = $booking->getBookingDate();
                return $date && $date->gte($from) && $date->lte($to);
            });
        };

        // Date ranges for additional revenue KPIs
        $endOfLastMonth = $startOfThisMonth->copy()->subDay();

        $startOfThisYear = $now->copy()->startOfYear();
        $endOfThisYear = $now->copy()->endOfYear();
        $startOfLastYear = $now->copy()->subYear()->startOfYear();
        $endOfLastYear = $now->copy()->subYear()->endOfYear();

        // Last month revenue + booking stats
        $lastMonthCompletedBookings = $completedBookingsForRange($startOfLastMonth, $endOfLastMonth);
        $lastMonthRevenue = $lastMonthCompletedBookings->sum(function ($booking) {
            return $booking->price + ($booking->total_extra_price ?? 0);
        });
        $lastMonthRevenueUnbilledCount = $lastMonthCompletedBookings->where('is_guide_billed', false)->count();

        // This year revenue + booking stats
        $thisYearCompletedBookings = $completedBookingsForRange($startOfThisYear, $endOfThisYear);
        $thisYearRevenue = $thisYearCompletedBookings->sum(function ($booking) {
            return $booking->price + ($booking->total_extra_price ?? 0);
        });
        $thisYearRevenueUnbilledCount = $thisYearCompletedBookings->where('is_guide_billed', false)->count();

        // Last year revenue + booking stats
        $lastYearCompletedBookings = $completedBookingsForRange($startOfLastYear, $endOfLastYear);
        $lastYearRevenue = $lastYearCompletedBookings->sum(function ($booking) {
            return $booking->price + ($booking->total_extra_price ?? 0);
        });
        $lastYearRevenueUnbilledCount = $lastYearCompletedBookings->where('is_guide_billed', false)->count();

        // Dashboard bookings table now only shows completed + approved bookings
        $recentBookings = $completedApprovedBookings
            ->take(10)
            ->map(function($booking) {
                return [
                    'id' => $booking->id,
                    'customer' => $booking->is_guest ? 
                        $booking->user?->firstname . ' ' . $booking->user?->lastname : 
                        $booking->user?->full_name,
                    'tour' => $booking->guiding?->title,
                    'price' => $booking->price + ($booking->total_extra_price ?? 0),
                    'date' => $booking->getFormattedBookingDate('d.m.Y H:i'),
                    'status' => $booking->status,
                    'status_color' => $this->getStatusColor($booking->status),
                    'is_guide_billed' => (bool) $booking->is_guide_billed,
                ];
            });

        // Upcoming Tours
        $upcomingTours = Guiding::with(['user'])
            ->where('status', 1)
            ->take(5)
            ->get()
            ->map(function($tour) {
                return [
                    'name' => $tour->title,
                    'guide' => $tour->user->full_name,
                    'date' => 'Flexible', // You can modify this based on your booking system
                    'price' => $tour->getLowestPrice()
                ];
            });

        // Revenue Chart Data - Last 6 months
        $revenueData = $this->getRevenueChartData($completedApprovedBookings);

        // Popular Tours Data
        $popularToursData = $this->getPopularToursData();

        // New statistics
        $bookingsByType = $this->getBookingsByType($completedApprovedBookings);
        $topDestinations = $this->getTopDestinations($testUserEmails);
        $customerRetentionData = $this->getCustomerRetentionData($testUserIds, $testUserEmails);
        $seasonalTrends = $this->getSeasonalTrends($completedApprovedBookings);
        $vacationStats = $this->getVacationStats();

        return view('admin.pages.index', compact(
            'totalBookings',
            'guestBookings',
            'monthlyRevenue',
            'monthlyBookingsTotal',
            'monthlyBookingsAccepted',
            'monthlyBookingsCancelled',
            'monthlyBookingsPending',
            'lastMonthBookingsTotal',
            'lastMonthBookingsAccepted',
            'lastMonthBookingsCancelled',
            'lastMonthBookingsPending',
            'thisYearBookingsTotal',
            'thisYearBookingsAccepted',
            'thisYearBookingsCancelled',
            'thisYearBookingsPending',
            'lastYearBookingsTotal',
            'lastYearBookingsAccepted',
            'lastYearBookingsCancelled',
            'lastYearBookingsPending',
            'totalCustomers',
            'registeredUsers',
            'guestUsers',
            'activeTours',
            'totalGuides',
            'guidesWithActiveTours',
            'guidesWithoutActiveOrDraftGuidings',
            'completedApprovedCount',
            'toBeBilledCount',
            'monthlyRevenueUnbilledCount',
            'lastMonthRevenue',
            'lastMonthRevenueUnbilledCount',
            'thisYearRevenue',
            'thisYearRevenueUnbilledCount',
            'lastYearRevenue',
            'lastYearRevenueUnbilledCount',
            'recentBookings',
            'upcomingTours',
            'revenueData',
            'popularToursData',
            'bookingsByType',
            'topDestinations',
            'customerRetentionData',
            'seasonalTrends',
            'vacationStats'
        ));
    }

    private function getStatusColor($status)
    {
        return match ($status) {
            'pending' => 'warning',
            'accepted' => 'success',
            'rejected' => 'danger',
            default => 'secondary'
        };
    }

    private function getRevenueChartData($completedApprovedBookings)
    {
        $labels = [];
        $data = [];
        
        for ($i = 5; $i >= 0; $i--) {
            $month = now()->subMonths($i);
            $labels[] = $month->format('M Y');

            $monthlyRevenue = $completedApprovedBookings
                ->filter(function ($booking) use ($month) {
                    $date = $booking->getBookingDate();
                    return $date && $date->year === $month->year && $date->month === $month->month;
                })
                ->sum(function ($booking) {
                    return $booking->price + ($booking->total_extra_price ?? 0);
                });

            $data[] = $monthlyRevenue;

        }

        return [
            'labels' => $labels,
            'data' => $data
        ];
    }

    private function getPopularToursData()
    {
        $popularTours = Guiding::withCount('bookings')
            ->orderBy('bookings_count', 'desc')
            ->take(5)
            ->get();

        return [
            'labels' => $popularTours->pluck('title')->toArray(),
            'data' => $popularTours->pluck('bookings_count')->toArray()
        ];
    }

    private function getBookingsByType($completedApprovedBookings)
    {
        // Regular tour bookings vs vacation bookings
        $regularBookings = $completedApprovedBookings->where('is_guest', false)->count();
        $guestBookings = $completedApprovedBookings->where('is_guest', true)->count();
        $vacationBookings = VacationBooking::count();
        
        return [
            'labels' => ['Regular Tours', 'Guest Tours', 'Vacation Packages'],
            'data' => [$regularBookings, $guestBookings, $vacationBookings]
        ];
    }

    private function getTopDestinations(array $testUserEmails)
    {
        // Tour destinations with explicit table names
        $tourDestinations = Guiding::select(
                'guidings.city as city',
                'guidings.country as country',
                DB::raw('COUNT(bookings.id) as booking_count')
            )
            ->join('bookings', 'guidings.id', '=', 'bookings.guiding_id')
            ->where('guidings.status', 1)
            ->where('bookings.status', 'accepted')
            ->when(!empty($testUserEmails), function ($query) use ($testUserEmails) {
                $query->whereNotIn('bookings.email', $testUserEmails);
            })
            ->groupBy('guidings.city', 'guidings.country')
            ->orderByDesc('booking_count')
            ->take(5)
            ->get();

        // Vacation destinations with explicit table names
        $vacationDestinations = Vacation::select(
                'vacations.city as city',
                'vacations.country as country',
                DB::raw('COUNT(vacation_bookings.id) as booking_count')
            )
            ->join('vacation_bookings', 'vacations.id', '=', 'vacation_bookings.vacation_id')
            ->where('vacations.status', 1)
            ->groupBy('vacations.city', 'vacations.country')
            ->orderByDesc('booking_count')
            ->take(5)
            ->get();

        return [
            'tours' => $tourDestinations,
            'vacations' => $vacationDestinations
        ];
    }

    private function getCustomerRetentionData(array $testUserIds, array $testUserEmails)
    {
        // Calculate total customers (excluding guides)
        $totalCustomers = User::where('is_guide', false)->orWhereNull('is_guide')->count();
        
        // Calculate repeat customers using a subquery
        $repeatCustomers = User::where('is_guide', false)
            ->orWhereNull('is_guide')
            ->whereExists(function ($query) use ($testUserIds, $testUserEmails) {
                $query->selectRaw('1')
                      ->from('bookings')
                      ->whereColumn('users.id', 'bookings.user_id')
                      ->whereNotIn('bookings.user_id', $testUserIds)
                      ->when(!empty($testUserEmails), function ($q) use ($testUserEmails) {
                          $q->whereNotIn('bookings.email', $testUserEmails);
                      })
                      ->groupBy('bookings.user_id')
                      ->havingRaw('COUNT(*) > 1');
            })
            ->count();

        return [
            'labels' => ['One-time Customers', 'Repeat Customers'],
            'data' => [$totalCustomers - $repeatCustomers, $repeatCustomers]
        ];
    }

    private function getSeasonalTrends($completedApprovedBookings)
    {
        $trends = [];
        $currentYear = now()->year;

        for ($month = 1; $month <= 12; $month++) {
            // Only count completed + approved tour bookings (accepted and booking date has passed)
            $tourBookings = $completedApprovedBookings
                ->filter(function ($booking) use ($currentYear, $month) {
                    $date = $booking->getBookingDate();
                    return $date && $date->year === $currentYear && $date->month === $month;
                })
                ->count();
            
            $vacationBookings = VacationBooking::whereYear('created_at', $currentYear)
                ->whereMonth('created_at', $month)
                ->count();
            
            $trends['labels'][] = date('F', mktime(0, 0, 0, $month, 1));
            $trends['tours'][] = $tourBookings;
            $trends['vacations'][] = $vacationBookings;
        }
        
        return $trends;
    }

    private function getVacationStats()
    {
        return [
            'total_vacations' => Vacation::count(),
            'active_vacations' => Vacation::where('status', 1)->count(),
            'avg_duration' => VacationBooking::avg('duration'),
            'popular_packages' => Vacation::withCount('bookings')
                ->orderByDesc('bookings_count')
                ->take(5)
                ->get()
                ->map(function($vacation) {
                    return [
                        'title' => $vacation->title,
                        'bookings' => $vacation->bookings_count,
                        'price' => $vacation->getLowestPrice()
                    ];
                }),
            'occupancy_rate' => $this->calculateOccupancyRate()
        ];
    }

    private function calculateOccupancyRate()
    {
        $now = now();
        // Get active vacations only
        $activeVacations = Vacation::where('status', 1)->get();
        $totalCapacity = $activeVacations->sum('max_persons');
        
        // Get current bookings
        $currentBookings = VacationBooking::whereDate('start_date', '<=', $now)
            ->whereDate('end_date', '>=', $now)
            ->sum('number_of_persons');
        
        return $totalCapacity > 0 ? round(($currentBookings / $totalCapacity) * 100) : 0;
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        //
    }
}
