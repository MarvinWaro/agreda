<?php

namespace App\Http\Controllers;

use App\Http\Requests\AvailabilityRequest;
use App\Models\Court;
use App\Models\Sport;
use App\Services\AvailabilityService;
use Carbon\CarbonImmutable;
use Illuminate\Http\JsonResponse;

class BookingController extends Controller
{
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
}
