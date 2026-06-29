<?php

namespace App\Http\Controllers;

use App\Http\Requests\AvailabilityRequest;
use App\Http\Requests\StoreBookingRequest;
use App\Jobs\NotifyOwnerOfBooking;
use App\Models\Booking;
use App\Models\Court;
use App\Models\Sport;
use App\Services\AvailabilityService;
use App\Services\BookingService;
use Carbon\CarbonImmutable;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response;

class BookingController extends Controller
{
    /**
     * Show the booking page (sport → date → time → details).
     */
    public function create(): Response
    {
        $court = Court::query()->active()->first();

        return Inertia::render('public/book', [
            'sports' => Sport::query()
                ->active()
                ->orderBy('name')
                ->get(['id', 'name', 'slug', 'icon', 'rate_offpeak', 'rate_peak']),
            'court' => $court === null ? null : [
                'id' => $court->id,
                'name' => $court->name,
                'location' => $court->location,
            ],
        ]);
    }

    /**
     * Return the slot availability grid for a sport on a given date.
     */
    public function slots(AvailabilityRequest $request, AvailabilityService $availability): JsonResponse
    {
        $sport = Sport::query()->findOrFail($request->integer('sport_id'));
        $date = CarbonImmutable::parse((string) $request->string('date'));

        // One physical court for now; resolve the active court that offers the sport.
        $court = $sport->courts()->where('is_active', true)->first()
            ?? Court::query()->active()->firstOrFail();

        return response()->json([
            'sport' => [
                'id' => $sport->id,
                'name' => $sport->name,
                'slug' => $sport->slug,
            ],
            ...$availability->forCourtAndDate($court, $date),
        ]);
    }

    /**
     * Store a booking request and redirect to its confirmation page.
     */
    public function store(StoreBookingRequest $request, BookingService $bookings): RedirectResponse
    {
        $data = $request->validated();

        $sport = Sport::query()->findOrFail($request->integer('sport_id'));
        $court = $sport->courts()->where('is_active', true)->first()
            ?? Court::query()->active()->firstOrFail();

        $booking = $bookings->request(
            $sport,
            $court,
            CarbonImmutable::parse($data['date']),
            $data['start_time'],
            $data['end_time'],
            [
                'guest_name' => $data['guest_name'],
                'guest_phone' => $data['guest_phone'],
                'notes' => $data['notes'] ?? null,
            ],
        );

        // Queued so the visitor's submit isn't blocked on Facebook delivery.
        NotifyOwnerOfBooking::dispatch($booking);

        return redirect()->route('booking.done', $booking);
    }

    /**
     * Show the confirmation page for a submitted request.
     */
    public function done(Booking $booking): Response
    {
        $booking->load('sport');

        return Inertia::render('public/booking-confirmation', [
            'booking' => [
                'reference' => $booking->id,
                'sport' => $booking->sport->name,
                'date' => $booking->booking_date->format('M j, Y'),
                'start' => CarbonImmutable::parse($booking->start_time)->format('g:i A'),
                'end' => CarbonImmutable::parse($booking->end_time)->format('g:i A'),
                'guest_name' => $booking->guest_name,
                'status' => $booking->status->value,
                'total_price' => $booking->total_price,
            ],
        ]);
    }
}
