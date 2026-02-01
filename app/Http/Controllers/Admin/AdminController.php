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
        // Total Bookings including guest bookings
        $totalBookings = Booking::count();
        $guestBookings = Booking::where('is_guest', true)->count();
        
        // Monthly statistics
        $thisMonth = now()->startOfMonth();
        $monthlyBookings = Booking::whereMonth('created_at', $thisMonth)->count();
        $monthlyRevenue = Booking::whereMonth('created_at', $thisMonth)
            ->get()
            ->sum(function($booking) {
                return $booking->price + ($booking->total_extra_price ?? 0);
            });
        
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

        // Recent Bookings with guest support
        $recentBookings = Booking::with(['user', 'guiding'])
            ->orderBy('id', 'desc')
            ->take(5)
            ->get()

            ->map(function($booking) {
                return [
                    'id' => $booking->id,
                    'customer' => $booking->is_guest ? 
                        $booking->user?->firstname . ' ' . $booking->user?->lastname : 
                        $booking->user?->full_name,
                    'tour' => $booking->guiding?->title,
                    'price' => $booking->price + ($booking->total_extra_price ?? 0),
                    'status' => $booking->status,
                    'status_color' => $this->getStatusColor($booking->status)
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
        $revenueData = $this->getRevenueChartData();

        // Popular Tours Data
        $popularToursData = $this->getPopularToursData();

        // New statistics
        $bookingsByType = $this->getBookingsByType();
        $topDestinations = $this->getTopDestinations();
        $customerRetentionData = $this->getCustomerRetentionData();
        $seasonalTrends = $this->getSeasonalTrends();
        $vacationStats = $this->getVacationStats();

        return view('admin.pages.index', compact(
            'totalBookings',
            'guestBookings',
            'monthlyRevenue',
            'monthlyBookings',
            'totalCustomers',
            'registeredUsers',
            'guestUsers',
            'activeTours',
            'totalGuides',
            'guidesWithActiveTours',
            'guidesWithoutActiveOrDraftGuidings',
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

    private function getRevenueChartData()
    {
        $labels = [];
        $data = [];
        
        for ($i = 5; $i >= 0; $i--) {
            $month = now()->subMonths($i);
            $labels[] = $month->format('M Y');
            
            $monthlyRevenue = Booking::whereYear('created_at', $month->year)
                ->whereMonth('created_at', $month->month)
                ->get()
                ->sum(function($booking) {
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

    private function getBookingsByType()
    {
        // Regular tour bookings vs vacation bookings
        $regularBookings = Booking::where('is_guest', false)->count();
        $guestBookings = Booking::where('is_guest', true)->count();
        $vacationBookings = VacationBooking::count();
        
        return [
            'labels' => ['Regular Tours', 'Guest Tours', 'Vacation Packages'],
            'data' => [$regularBookings, $guestBookings, $vacationBookings]
        ];
    }

    private function getTopDestinations()
    {
        // Tour destinations with explicit table names
        $tourDestinations = Guiding::select(
                'guidings.city as city',
                'guidings.country as country',
                DB::raw('COUNT(bookings.id) as booking_count')
            )
            ->join('bookings', 'guidings.id', '=', 'bookings.guiding_id')
            ->where('guidings.status', 1)
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

    private function getCustomerRetentionData()
    {
        // Calculate total customers (excluding guides)
        $totalCustomers = User::where('is_guide', false)->orWhereNull('is_guide')->count();
        
        // Calculate repeat customers using a subquery
        $repeatCustomers = User::where('is_guide', false)
            ->orWhereNull('is_guide')
            ->whereExists(function ($query) {
                $query->selectRaw('1')
                      ->from('bookings')
                      ->whereColumn('users.id', 'bookings.user_id')
                      ->groupBy('bookings.user_id')
                      ->havingRaw('COUNT(*) > 1');
            })
            ->count();

        return [
            'labels' => ['One-time Customers', 'Repeat Customers'],
            'data' => [$totalCustomers - $repeatCustomers, $repeatCustomers]
        ];
    }

    private function getSeasonalTrends()
    {
        $trends = [];
        $currentYear = now()->year;

        for ($month = 1; $month <= 12; $month++) {
            $tourBookings = Booking::whereYear('created_at', $currentYear)
                ->whereMonth('created_at', $month)
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
