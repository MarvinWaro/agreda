<?php

namespace App\Http\Controllers\Admin;

use App\Enums\BookingStatus;
use App\Http\Controllers\Controller;
use App\Jobs\NotifyGuestOfDecision;
use App\Models\Booking;
use App\Models\Sport;
use App\Support\BookingSummary;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class BookingController extends Controller
{
    public function index(Request $request): Response
    {
        $status = $request->string('status')->toString() ?: null;
        $sportId = $request->integer('sport_id') ?: null;
        $date = $request->string('date')->toString() ?: null;

        $bookings = Booking::query()
            ->with('sport')
            ->when($status, fn ($query, $value) => $query->where('status', $value))
            ->when($sportId, fn ($query, $value) => $query->where('sport_id', $value))
            ->when($date, fn ($query, $value) => $query->whereDate('booking_date', $value))
            ->orderByDesc('booking_date')
            ->orderBy('start_time')
            ->paginate(15)
            ->withQueryString()
            ->through(BookingSummary::make(...));

        return Inertia::render('admin/bookings', [
            'bookings' => $bookings,
            'sports' => Sport::query()->orderBy('name')->get(['id', 'name']),
            'statuses' => array_map(
                fn (BookingStatus $case): array => ['value' => $case->value, 'label' => $case->label()],
                BookingStatus::cases(),
            ),
            'filters' => [
                'status' => $status,
                'sport_id' => $sportId,
                'date' => $date,
            ],
        ]);
    }

    public function confirm(Booking $booking): RedirectResponse
    {
        return $this->transition($booking, BookingStatus::Confirmed, 'confirmed');
    }

    public function decline(Booking $booking): RedirectResponse
    {
        return $this->transition($booking, BookingStatus::Declined, 'declined');
    }

    private function transition(Booking $booking, BookingStatus $to, string $verb): RedirectResponse
    {
        if ($booking->status !== BookingStatus::Pending) {
            return back()->with('toast', [
                'type' => 'warning',
                'message' => "Booking #{$booking->id} is no longer pending.",
            ]);
        }

        $booking->update(['status' => $to]);

        // Notify the requester back (queued; stubbed Facebook delivery).
        NotifyGuestOfDecision::dispatch($booking);

        return back()->with('toast', [
            'type' => 'success',
            'message' => "Booking #{$booking->id} {$verb}.",
        ]);
    }
}
