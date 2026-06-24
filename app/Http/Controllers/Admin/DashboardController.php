<?php

namespace App\Http\Controllers\Admin;

use App\Enums\BookingStatus;
use App\Http\Controllers\Controller;
use App\Models\Booking;
use App\Support\BookingSummary;
use Carbon\CarbonImmutable;
use Inertia\Inertia;
use Inertia\Response;

class DashboardController extends Controller
{
    public function index(): Response
    {
        $today = CarbonImmutable::today();
        $occupying = BookingStatus::occupyingValues();

        return Inertia::render('admin/dashboard', [
            'stats' => [
                'pending' => Booking::query()
                    ->where('status', BookingStatus::Pending)
                    ->count(),
                'today' => Booking::query()
                    ->whereDate('booking_date', $today->toDateString())
                    ->whereIn('status', $occupying)
                    ->count(),
                'week' => Booking::query()
                    ->whereBetween('booking_date', [
                        $today->startOfWeek()->toDateString(),
                        $today->endOfWeek()->toDateString(),
                    ])
                    ->whereIn('status', $occupying)
                    ->count(),
            ],
            'latest' => Booking::query()
                ->with('sport')
                ->where('status', BookingStatus::Pending)
                ->latest()
                ->take(6)
                ->get()
                ->map(BookingSummary::make(...))
                ->all(),
        ]);
    }
}
