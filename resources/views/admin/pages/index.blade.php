@extends('admin.layouts.app')

@section('title', 'Dashboard')

@section('content')
<div class="container-fluid px-4 pt-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3 mb-0">Dashboard</h1>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb m-0">
                <li class="breadcrumb-item active">Dashboard</li>
            </ol>
        </nav>
    </div>

    <!-- Statistics Cards -->
    <div class="row g-4 mb-4">
        <div class="col-12 col-sm-6 col-xl-3">
            <div class="card h-100 bg-primary text-white">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="flex-shrink-0">
                            <i class="fa fa-calendar fa-2x"></i>
                        </div>
                        <div class="flex-grow-1 ms-3">
                            <h6 class="mb-0">Total Bookings</h6>
                            <h2 class="mb-0">{{ $totalBookings }}</h2>
                            <small>{{ $guestBookings }} from guests</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-12 col-sm-6 col-xl-3">
            <div class="card h-100 bg-info text-white">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="flex-shrink-0">
                            <i class="fa fa-users fa-2x"></i>
                        </div>
                        <div class="flex-grow-1 ms-3">
                            <h6 class="mb-0">Total Customers</h6>
                            <h2 class="mb-0">{{ $totalCustomers }}</h2>
                            <small>{{ $registeredUsers }} registered, {{ $guestUsers }} guests</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-12 col-sm-6 col-xl-3">
            <div class="card h-100 bg-warning text-white">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="flex-shrink-0">
                            <i class="fa fa-ship fa-2x"></i>
                        </div>
                        <div class="flex-grow-1 ms-3">
                            <h6 class="mb-0">Active Tours</h6>
                            <h2 class="mb-0">{{ $activeTours }}</h2>
                            <small>{{ $totalGuides }} total guides</small> <br>
                            <small><b>{{ $guidesWithActiveTours }}</b> active guides</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-12 col-sm-6 col-xl-3">
            <div class="card h-100 bg-dark text-white">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="flex-shrink-0">
                            <i class="fa fa-file-invoice fa-2x"></i>
                        </div>
                        <div class="flex-grow-1 ms-3">
                            <h6 class="mb-0">Billing Queue</h6>
                            <h2 class="mb-0">{{ $toBeBilledCount }}</h2>
                            <small>{{ $completedApprovedCount }} completed approved bookings</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Revenue Cards -->
    <div class="row g-4 mb-4">
        <!-- This Month Revenue -->
        <div class="col-12 col-sm-6 col-xl-3">
            <div class="card h-100 border-0 shadow-sm bg-success text-white">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="flex-shrink-0">
                            <i class="fa fa-dollar-sign fa-2x"></i>
                        </div>
                        <div class="flex-grow-1 ms-3">
                            <h6 class="mb-0 text-uppercase small">Revenue - This Month</h6>
                            <h2 class="mb-1 fw-semibold">${{ number_format($monthlyRevenue, 2) }}</h2>

                            <div class="small mb-1">
                                <span class="me-1">
                                    <i class="fa fa-calendar-check-o me-1"></i>
                                    {{ $monthlyBookingsTotal }} bookings this month
                                </span>
                            </div>
                            <div class="small d-flex flex-wrap gap-1 mb-1">
                                <span class="badge bg-light text-success" data-bs-toggle="tooltip" data-bs-placement="top" title="Accepted bookings for this month">
                                    <i class="fa fa-check-circle me-1"></i>{{ $monthlyBookingsAccepted }}
                                </span>
                                <span class="badge bg-light text-warning" data-bs-toggle="tooltip" data-bs-placement="top" title="Pending bookings for this month">
                                    <i class="fa fa-clock-o me-1"></i>{{ $monthlyBookingsPending }}
                                </span>
                                <span class="badge bg-light text-danger" data-bs-toggle="tooltip" data-bs-placement="top" title="Cancelled bookings for this month">
                                    <i class="fa fa-times-circle me-1"></i>{{ $monthlyBookingsCancelled }}
                                </span>
                            </div>
                            <div class="small">
                                <i class="fa fa-file-invoice-dollar me-1"></i>
                                {{ $monthlyRevenueUnbilledCount }} completed bookings not yet billed
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Last Month Revenue -->
        <div class="col-12 col-sm-6 col-xl-3">
            <div class="card h-100 border-0 shadow-sm bg-primary text-white">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="flex-shrink-0">
                            <i class="fa fa-calendar-o fa-2x"></i>
                        </div>
                        <div class="flex-grow-1 ms-3">
                            <h6 class="mb-1 text-uppercase small">Revenue - Last Month</h6>
                            <h3 class="mb-1 fw-semibold">${{ number_format($lastMonthRevenue, 2) }}</h3>
                            <div class="small mb-1">
                                <span class="me-1">
                                    <i class="fa fa-calendar-check-o me-1"></i>
                                    {{ $lastMonthBookingsTotal }} bookings last month
                                </span>
                            </div>
                            <div class="small d-flex flex-wrap gap-1 mb-1">
                                <span class="badge bg-light text-success" data-bs-toggle="tooltip" data-bs-placement="top" title="Accepted bookings for last month">
                                    <i class="fa fa-check-circle me-1"></i>{{ $lastMonthBookingsAccepted }}
                                </span>
                                <span class="badge bg-light text-warning" data-bs-toggle="tooltip" data-bs-placement="top" title="Pending bookings for last month">
                                    <i class="fa fa-clock-o me-1"></i>{{ $lastMonthBookingsPending }}
                                </span>
                                <span class="badge bg-light text-danger" data-bs-toggle="tooltip" data-bs-placement="top" title="Cancelled bookings for last month">
                                    <i class="fa fa-times-circle me-1"></i>{{ $lastMonthBookingsCancelled }}
                                </span>
                            </div>
                            <div class="small">
                                <i class="fa fa-file-invoice-dollar me-1"></i>
                                {{ $lastMonthRevenueUnbilledCount }} completed bookings not yet billed
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- This Year Revenue -->
        <div class="col-12 col-sm-6 col-xl-3">
            <div class="card h-100 border-0 shadow-sm bg-info text-white">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="flex-shrink-0">
                            <i class="fa fa-calendar-check-o fa-2x"></i>
                        </div>
                        <div class="flex-grow-1 ms-3">
                            <h6 class="mb-1 text-uppercase small">Revenue - This Year</h6>
                            <h3 class="mb-1 fw-semibold">${{ number_format($thisYearRevenue, 2) }}</h3>
                            <div class="small mb-1">
                                <span class="me-1">
                                    <i class="fa fa-calendar-check-o me-1"></i>
                                    {{ $thisYearBookingsTotal }} bookings this year
                                </span>
                            </div>
                            <div class="small d-flex flex-wrap gap-1 mb-1">
                                <span class="badge bg-light text-success" data-bs-toggle="tooltip" data-bs-placement="top" title="Accepted bookings for this year">
                                    <i class="fa fa-check-circle me-1"></i>{{ $thisYearBookingsAccepted }}
                                </span>
                                <span class="badge bg-light text-warning" data-bs-toggle="tooltip" data-bs-placement="top" title="Pending bookings for this year">
                                    <i class="fa fa-clock-o me-1"></i>{{ $thisYearBookingsPending }}
                                </span>
                                <span class="badge bg-light text-danger" data-bs-toggle="tooltip" data-bs-placement="top" title="Cancelled bookings for this year">
                                    <i class="fa fa-times-circle me-1"></i>{{ $thisYearBookingsCancelled }}
                                </span>
                            </div>
                            <div class="small">
                                <i class="fa fa-file-invoice-dollar me-1"></i>
                                {{ $thisYearRevenueUnbilledCount }} completed bookings not yet billed
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Last Year Revenue -->
        <div class="col-12 col-sm-6 col-xl-3">
            <div class="card h-100 border-0 shadow-sm bg-warning text-white">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="flex-shrink-0">
                            <i class="fa fa-history fa-2x"></i>
                        </div>
                        <div class="flex-grow-1 ms-3">
                            <h6 class="mb-1 text-uppercase small">Revenue - Last Year</h6>
                            <h3 class="mb-1 fw-semibold">${{ number_format($lastYearRevenue, 2) }}</h3>
                            <div class="small mb-1">
                                <span class="me-1">
                                    <i class="fa fa-calendar-check-o me-1"></i>
                                    {{ $lastYearBookingsTotal }} bookings last year
                                </span>
                            </div>
                            <div class="small d-flex flex-wrap gap-1 mb-1">
                                <span class="badge bg-light text-success" data-bs-toggle="tooltip" data-bs-placement="top" title="Accepted bookings for last year">
                                    <i class="fa fa-check-circle me-1"></i>{{ $lastYearBookingsAccepted }}
                                </span>
                                <span class="badge bg-light text-warning" data-bs-toggle="tooltip" data-bs-placement="top" title="Pending bookings for last year">
                                    <i class="fa fa-clock-o me-1"></i>{{ $lastYearBookingsPending }}
                                </span>
                                <span class="badge bg-light text-danger" data-bs-toggle="tooltip" data-bs-placement="top" title="Cancelled bookings for last year">
                                    <i class="fa fa-times-circle me-1"></i>{{ $lastYearBookingsCancelled }}
                                </span>
                            </div>
                            <div class="small">
                                <i class="fa fa-file-invoice-dollar me-1"></i>
                                {{ $lastYearRevenueUnbilledCount }} completed bookings not yet billed
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Guide Analytics Summary -->
    <div class="row g-4 mb-4">
        <div class="col-12">
            <div class="card border-secondary">
                <div class="card-body d-flex align-items-center justify-content-between flex-wrap gap-2">
                    <div class="d-flex align-items-center">
                        <i class="fa fa-chart-line fa-2x text-secondary me-3"></i>
                        <div>
                            <h6 class="mb-0">Guides Without Active or Draft Guidings</h6>
                            <small class="text-muted">Guides who have no active (1) or draft (2) guidings - either none created or all deactivated</small>
                        </div>
                    </div>
                    <div class="d-flex align-items-center gap-3">
                        <h2 class="mb-0 text-secondary">{{ $guidesWithoutActiveOrDraftGuidings }}</h2>
                        <a href="{{ route('admin.guide-analytics.index') }}" class="btn btn-outline-secondary btn-sm">View Full Analytics <i class="fa fa-arrow-right ms-1"></i></a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Charts Row -->
    <div class="row g-4 mb-4">
        <div class="col-12 col-xl-8">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">Revenue Overview</h5>
                </div>
                <div class="card-body" style="height: 400px;">
                    <canvas id="revenueChart"></canvas>
                </div>
            </div>
        </div>
        <div class="col-12 col-xl-4">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">Popular Tours</h5>
                </div>
                <div class="card-body" style="height: 400px;">
                    <canvas id="toursPieChart"></canvas>
                </div>
            </div>
        </div>
    </div>

    <!-- Booking Distribution and Customer Retention -->
    <div class="row g-4 mb-4">
        <!-- Booking Distribution -->
        <div class="col-12 col-xl-6">
            <div class="card h-100">
                <div class="card-header">
                    <h5 class="card-title mb-0">Booking Distribution</h5>
                </div>
                <div class="card-body d-flex justify-content-center align-items-center">
                    <div style="width: 300px; height: 300px;">
                        <canvas id="bookingTypesChart"></canvas>
                    </div>
                </div>
            </div>
        </div>

        <!-- Customer Retention -->
        <div class="col-12 col-xl-6">
            <div class="card h-100">
                <div class="card-header">
                    <h5 class="card-title mb-0">Customer Retention</h5>
                </div>
                <div class="card-body d-flex justify-content-center align-items-center">
                    <div style="width: 300px; height: 300px;">
                        <canvas id="customerRetentionChart"></canvas>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Seasonal Trends -->
    <div class="row g-4 mb-4">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">Seasonal Booking Trends</h5>
                </div>
                <div class="card-body">
                    <div style="height: 400px;">
                        <canvas id="seasonalTrendsChart"></canvas>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Top Destinations -->
    <div class="row g-4 mb-4">
        <div class="col-12 col-xl-6">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">Popular Tour Destinations</h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table">
                            <thead>
                                <tr>
                                    <th>Location</th>
                                    <th>Bookings</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($topDestinations['tours'] as $destination)
                                    <tr>
                                        <td>{{ $destination->city }}, {{ $destination->country }}</td>
                                        <td>{{ $destination->booking_count }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-12 col-xl-6">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">Popular Vacation Destinations</h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table">
                            <thead>
                                <tr>
                                    <th>Location</th>
                                    <th>Bookings</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($topDestinations['vacations'] as $destination)
                                    <tr>
                                        <td>{{ $destination->city }}, {{ $destination->country }}</td>
                                        <td>{{ $destination->booking_count }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Tables Row -->
    <div class="row g-4">
        <div class="col-12 col-xl-6">
            <div class="card h-100">
                <div class="card-header">
                    <h5 class="card-title mb-0">Completed Approved Bookings</h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Customer</th>
                                    <th>Tour</th>
                                    <th>Tour date</th>
                                    <th>Request created</th>
                                    <th>Price</th>
                                    <th>Status</th>
                                    <th>Billing</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($recentBookings as $booking)
                                    <tr>
                                        <td>#{{ $booking['id'] }}</td>
                                        <td>{{ $booking['customer'] }}</td>
                                        <td>{{ $booking['tour'] }}</td>
                                        <td>{{ $booking['date'] }}</td>
                                        <td class="text-muted small">{{ $booking['request_created_at'] ?? '—' }}</td>
                                        <td>${{ number_format($booking['price'], 2) }}</td>
                                        <td>
                                            <span class="badge bg-{{ $booking['status_color'] }}">
                                                {{ ucfirst($booking['status']) }}
                                            </span>
                                        </td>
                                        <td>
                                            @if($booking['is_guide_billed'])
                                                <span class="badge bg-success">Billed</span>
                                            @else
                                                <span class="badge bg-warning text-dark">To be billed</span>
                                            @endif
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="8" class="text-center">No completed approved bookings</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-12 col-xl-6">
            <div class="card h-100">
                <div class="card-header">
                    <h5 class="card-title mb-0">Upcoming Tours</h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>Tour Name</th>
                                    <th>Guide</th>
                                    <th>Date</th>
                                    <th>Price</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($upcomingTours as $tour)
                                    <tr>
                                        <td>{{ $tour['name'] }}</td>
                                        <td>{{ $tour['guide'] }}</td>
                                        <td>{{ $tour['date'] }}</td>
                                        <td>${{ number_format($tour['price'], 2) }}</td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="4" class="text-center">No upcoming tours</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('js_after')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    document.addEventListener('DOMContentLoaded', () => {
        // Enable Bootstrap tooltips
        if (window.bootstrap) {
            const tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
            tooltipTriggerList.forEach(function (tooltipTriggerEl) {
                new bootstrap.Tooltip(tooltipTriggerEl);
            });
        }

        // Revenue Chart
        const revenueData = {!! json_encode($revenueData) !!};
        const popularToursData = {!! json_encode($popularToursData) !!};

        const revenueCtx = document.getElementById('revenueChart').getContext('2d');
        new Chart(revenueCtx, {
            type: 'line',
            data: {
                labels: revenueData.labels,
                datasets: [{
                    label: 'Monthly Revenue',
                    data: revenueData.data,
                    borderColor: 'rgb(75, 192, 192)',
                    tension: 0.1,

                    fill: false
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: true,
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: {
                            callback: function(value) {
                                return '$' + value.toLocaleString();
                            }
                        }
                    }
                }
            }
        });

        // Popular Tours Chart
        const toursCtx = document.getElementById('toursPieChart').getContext('2d');
        new Chart(toursCtx, {
            type: 'pie',
            data: {
                labels: popularToursData.labels,
                datasets: [{
                    data: popularToursData.data,
                    backgroundColor: [
                        'rgb(255, 99, 132)',
                        'rgb(54, 162, 235)',
                        'rgb(255, 205, 86)',
                        'rgb(75, 192, 192)'
                    ]
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: true,
                plugins: {
                    legend: {
                        position: 'bottom'
                    }
                }
            }
        });

        // Booking Types Chart
        new Chart(document.getElementById('bookingTypesChart').getContext('2d'), {
            type: 'doughnut',
            data: {
                labels: {!! json_encode($bookingsByType['labels']) !!},
                datasets: [{
                    data: {!! json_encode($bookingsByType['data']) !!},
                    backgroundColor: ['#4e73df', '#1cc88a', '#36b9cc']
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: 'bottom'
                    }
                }
            }
        });

        // Customer Retention Chart
        new Chart(document.getElementById('customerRetentionChart').getContext('2d'), {
            type: 'pie',
            data: {
                labels: {!! json_encode($customerRetentionData['labels']) !!},
                datasets: [{
                    data: {!! json_encode($customerRetentionData['data']) !!},
                    backgroundColor: ['#e74a3b', '#1cc88a']
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: 'bottom'
                    }
                }
            }
        });

        // Seasonal Trends Chart
        new Chart(document.getElementById('seasonalTrendsChart').getContext('2d'), {
            type: 'line',
            data: {
                labels: {!! json_encode($seasonalTrends['labels']) !!},
                datasets: [{
                    label: 'Tour Bookings',
                    data: {!! json_encode($seasonalTrends['tours']) !!},
                    borderColor: '#4e73df',
                    fill: false,
                    tension: 0.1
                }, {
                    label: 'Vacation Bookings',
                    data: {!! json_encode($seasonalTrends['vacations']) !!},
                    borderColor: '#1cc88a',
                    fill: false,
                    tension: 0.1
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: 'top'
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        grid: {
                            drawBorder: false
                        }
                    },
                    x: {
                        grid: {
                            display: false
                        }
                    }
                }
            }
        });
    });
</script>
@endpush
